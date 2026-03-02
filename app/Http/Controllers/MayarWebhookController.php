<?php

namespace App\Http\Controllers;

use App\Models\CreditTransaction;
use App\Models\TopupTransaction;
use App\Services\CreditService;
use App\Services\MayarService;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

/**
 * Mayar Webhook Controller
 *
 * Handles asynchronous payment notifications from Mayar.
 * This endpoint must be registered in the Mayar dashboard.
 * Route: POST /webhooks/mayar
 */
class MayarWebhookController extends Controller
{
    public function __construct(
        private MayarService $mayarService,
        private CreditService $creditService,
    ) {}

    /**
     * Handle incoming Mayar webhook notification.
     */
    public function handle(Request $request): Response
    {
        $payload = $request->getContent();
        $signature = $request->header('X-Mayar-Signature', '');

        // 1. Verify webhook signature
        if (! $this->mayarService->verifyWebhookSignature($payload, $signature)) {
            Log::warning('Mayar webhook signature mismatch', [
                'ip' => $request->ip(),
                'signature' => $signature,
            ]);

            return response('Signature invalid', 401);
        }

        $data = $request->json()->all();

        Log::info('Mayar webhook received', ['event' => $data['event'] ?? 'unknown', 'id' => $data['id'] ?? null]);

        // 2. Handle only payment success events
        $status = strtoupper($data['status'] ?? '');
        $event = $data['event'] ?? '';

        $isPaid = in_array($status, ['PAID', 'SUCCESS']) || $event === 'payment.success';

        if (! $isPaid) {
            // Acknowledge other events without processing
            return response('OK', 200);
        }

        // 3. Find transaction by Mayar transaction ID or reference ID from metadata
        $mayarTransactionId = $data['id'] ?? null;
        $referenceId = $data['metadata']['reference_id'] ?? $data['referenceId'] ?? null;

        $transaction = null;

        if ($mayarTransactionId) {
            $transaction = TopupTransaction::where('mayar_transaction_id', $mayarTransactionId)->first();
        }

        if (! $transaction && $referenceId) {
            $transaction = TopupTransaction::find($referenceId);
        }

        if (! $transaction) {
            Log::error('Mayar webhook: transaction not found', [
                'mayar_id' => $mayarTransactionId,
                'reference_id' => $referenceId,
            ]);

            // Return 200 to prevent Mayar retrying for unknown transactions
            return response('Transaction not found', 200);
        }

        // 4. Idempotency: skip if already processed
        if (! $transaction->isPending()) {
            Log::info('Mayar webhook: transaction already processed, skipping', [
                'transaction_id' => $transaction->id,
                'status' => $transaction->status,
            ]);

            return response('Already processed', 200);
        }

        // 5. Process credit top-up inside a DB transaction with row lock
        try {
            DB::transaction(function () use ($transaction, $data) {
                // Re-fetch with lock to prevent race conditions
                $locked = TopupTransaction::lockForUpdate()->find($transaction->id);

                if (! $locked || ! $locked->isPending()) {
                    return; // Another request already processed this
                }

                // Mark transaction as successful
                $locked->update([
                    'status' => TopupTransaction::STATUS_SUCCESS,
                    'paid_at' => now(),
                    'mayar_payload' => $data,
                ]);

                // Add credits to the user
                $this->creditService->addCredits(
                    user: $locked->user,
                    amount: $locked->credits_added,
                    type: CreditTransaction::TYPE_TOPUP,
                    description: "Top-up via Mayar - {$locked->credits_added} kredit",
                    metadata: [
                        'topup_transaction_id' => $locked->id,
                        'mayar_transaction_id' => $locked->mayar_transaction_id,
                        'package_id' => $locked->credit_package_id,
                        'amount_paid' => $locked->amount,
                    ]
                );

                Log::info('Credits topped up successfully', [
                    'user_id' => $locked->user_id,
                    'credits' => $locked->credits_added,
                    'transaction_id' => $locked->id,
                ]);
            });
        } catch (\Throwable $e) {
            Log::error('Mayar webhook: failed to process top-up', [
                'transaction_id' => $transaction->id,
                'error' => $e->getMessage(),
            ]);

            // Return 500 so Mayar retries
            return response('Processing error', 500);
        }

        return response('OK', 200);
    }
}
