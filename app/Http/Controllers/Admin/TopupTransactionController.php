<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\TopupTransaction;
use Illuminate\Http\Request;
use Inertia\Inertia;

/**
 * Topup Transaction Monitoring Controller
 *
 * Read-only admin view of all Mayar top-up transactions.
 */
class TopupTransactionController extends Controller
{
    /**
     * Display a paginated list of all top-up transactions with filters.
     */
    public function index(Request $request): \Inertia\Response
    {
        $query = TopupTransaction::query()
            ->with(['user:id,name,email', 'creditPackage:id,name,credits'])
            ->latest();

        if ($request->filled('search')) {
            $search = $request->search;
            $query->whereHas('user', function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%");
            });
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }

        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }

        $transactions = $query->paginate(25)->withQueryString();

        // Summary stats for the current filtered result set (without pagination)
        $statsQuery = TopupTransaction::successful();

        if ($request->filled('date_from')) {
            $statsQuery->whereDate('created_at', '>=', $request->date_from);
        }

        if ($request->filled('date_to')) {
            $statsQuery->whereDate('created_at', '<=', $request->date_to);
        }

        $stats = [
            'total_revenue' => $statsQuery->sum('amount'),
            'total_credits_sold' => $statsQuery->sum('credits_added'),
            'total_transactions' => $statsQuery->count(),
            'pending_count' => TopupTransaction::pending()->count(),
        ];

        return Inertia::render('Admin/TopupTransactions/Index', [
            'transactions' => $transactions,
            'stats' => $stats,
            'filters' => $request->only(['search', 'status', 'date_from', 'date_to']),
        ]);
    }
}
