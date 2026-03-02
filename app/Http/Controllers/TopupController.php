<?php

namespace App\Http\Controllers;

use App\Http\Requests\InitiateTopupRequest;
use App\Models\CreditPackage;
use App\Models\TopupTransaction;
use App\Services\CreditService;
use App\Services\MayarService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use RuntimeException;

/**
 * Topup Controller
 *
 * Handles the user-facing credit top-up flow via Mayar payment gateway.
 */
class TopupController extends Controller
{
    public function __construct(
        private MayarService $mayarService,
        private CreditService $creditService,
    ) {}

    /**
     * Display the top-up package selection page.
     */
    public function index(): \Inertia\Response
    {
        $packages = CreditPackage::active()->ordered()->get()->map(fn (CreditPackage $p) => [
            'id' => $p->id,
            'name' => $p->name,
            'description' => $p->description,
            'credits' => $p->credits,
            'price' => $p->price,
            'formatted_price' => $p->formattedPrice(),
        ]);

        $user = Auth::user();

        return Inertia::render('Credits/Topup', [
            'packages' => $packages,
            'userCredits' => $user->credits,
            'userPhone' => $user->phone,
            'completedTopups' => TopupTransaction::where('user_id', $user->id)
                ->successful()
                ->latest()
                ->limit(5)
                ->get(['id', 'credits_added', 'amount', 'paid_at', 'created_at'])
                ->map(fn ($t) => [
                    'id' => $t->id,
                    'credits_added' => $t->credits_added,
                    'amount' => $t->amount,
                    'formatted_amount' => $t->formattedAmount(),
                    'paid_at' => $t->paid_at?->toDateTimeString(),
                ]),
        ]);
    }

    /**
     * Initiate a top-up: create a Mayar payment link and redirect the user.
     */
    public function initiate(InitiateTopupRequest $request): \Illuminate\Http\RedirectResponse|\Illuminate\Http\Response|\Inertia\Response
    {
        $package = CreditPackage::active()->findOrFail($request->credit_package_id);
        $user = Auth::user();

        // Ensure user has a phone number for Mayar notifications
        if (! $user->phone) {
            return back()->withErrors(['payment' => 'Silakan lengkapi nomor HP di profil terlebih dahulu.']);
        }

        // Create a pending transaction record first
        $transaction = TopupTransaction::create([
            'user_id' => $user->id,
            'credit_package_id' => $package->id,
            'amount' => $package->price,
            'credits_added' => $package->credits,
            'status' => TopupTransaction::STATUS_PENDING,
        ]);

        try {
            $paymentData = $this->mayarService->createPaymentLink([
                'name' => $user->name,
                'email' => $user->email,
                'mobile' => $user->phone,
                'amount' => $package->price,
                'description' => "Top-up {$package->credits} kredit SatsetUI - Paket {$package->name}",
                'reference_id' => (string) $transaction->id,
                'redirect_url' => route('topup.callback', ['transaction' => $transaction->id]),
            ]);

            // Save the Mayar invoice ID, transaction ID, and payment link
            $transaction->update([
                'mayar_transaction_id' => $paymentData['transaction_id'],
                'mayar_payment_link' => $paymentData['link'],
            ]);

            // Return the external URL to frontend for client-side redirect
            // (Inertia XHR cannot follow cross-origin redirects due to CORS)
            return Inertia::location($paymentData['link']);
        } catch (RuntimeException $e) {
            // Clean up the pending record if link creation fails
            $transaction->delete();

            return back()->withErrors(['payment' => $e->getMessage()]);
        }
    }

    /**
     * Display the user's top-up transaction history.
     */
    public function history(Request $request): \Inertia\Response
    {
        $user = Auth::user();

        $transactions = TopupTransaction::where('user_id', $user->id)
            ->with('creditPackage:id,name')
            ->latest()
            ->paginate(15)
            ->through(fn (TopupTransaction $t) => [
                'id' => $t->id,
                'package_name' => $t->creditPackage?->name,
                'credits_added' => $t->credits_added,
                'amount' => $t->amount,
                'formatted_amount' => $t->formattedAmount(),
                'status' => $t->status,
                'mayar_payment_link' => $t->mayar_payment_link,
                'paid_at' => $t->paid_at?->toDateTimeString(),
                'created_at' => $t->created_at->toDateTimeString(),
            ]);

        return Inertia::render('Credits/History', [
            'transactions' => $transactions,
            'userCredits' => $user->credits,
        ]);
    }

    /**
     * Handle the return redirect from Mayar after checkout.
     *
     * Note: This is NOT where credits are added. Credits are added via webhook.
     * This is just a UX landing page after the user returns.
     */
    public function callback(Request $request, TopupTransaction $transaction): \Inertia\Response
    {
        // Safety: ensure the transaction belongs to the authenticated user
        abort_if($transaction->user_id !== Auth::id(), 403);

        $transaction->refresh();

        return Inertia::render('Credits/TopupCallback', [
            'transaction' => [
                'id' => $transaction->id,
                'status' => $transaction->status,
                'credits_added' => $transaction->credits_added,
                'formatted_amount' => $transaction->formattedAmount(),
                'paid_at' => $transaction->paid_at?->toDateTimeString(),
            ],
            'userCredits' => Auth::user()->fresh()->credits,
        ]);
    }
}
