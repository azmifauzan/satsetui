<?php

namespace App\Http\Controllers;

use App\Services\CostTrackingService;
use App\Services\CreditService;
use App\Models\GenerationFailure;
use Illuminate\Http\Request;

class AdminStatisticsController extends Controller
{
    public function __construct(
        private CreditService $creditService,
        private CostTrackingService $costTrackingService
    ) {
        // TODO: Add admin middleware
        // $this->middleware('admin');
    }

    /**
     * Display main statistics dashboard
     */
    public function index(Request $request)
    {
        $days = $request->input('days', 30);

        return inertia('Admin/Statistics/Index', [
            'creditStats' => $this->creditService->getStatistics($days),
            'costStats' => $this->costTrackingService->getStatistics($days),
            'failureStats' => $this->getFailureStatistics($days),
            'days' => $days,
        ]);
    }

    /**
     * Get cost tracking details
     */
    public function costs(Request $request)
    {
        $days = $request->input('days', 30);
        
        return inertia('Admin/Statistics/Costs', [
            'statistics' => $this->costTrackingService->getStatistics($days),
            'expensiveGenerations' => $this->costTrackingService->getMostExpensiveGenerations(20),
            'modelProfitability' => $this->costTrackingService->getModelsByProfitability(),
            'days' => $days,
        ]);
    }

    /**
     * Get credit transactions details
     */
    public function credits(Request $request)
    {
        $days = $request->input('days', 30);
        
        return inertia('Admin/Statistics/Credits', [
            'statistics' => $this->creditService->getStatistics($days),
            'days' => $days,
        ]);
    }

    /**
     * Get failure analysis
     */
    public function failures(Request $request)
    {
        $days = $request->input('days', 30);
        $since = now()->subDays($days);

        $failures = GenerationFailure::with(['user', 'generation'])
            ->where('created_at', '>=', $since)
            ->orderBy('created_at', 'desc')
            ->paginate(50);

        return inertia('Admin/Statistics/Failures', [
            'failures' => $failures,
            'statistics' => $this->getFailureStatistics($days),
            'days' => $days,
        ]);
    }

    /**
     * Calculate failure statistics
     */
    private function getFailureStatistics(int $days): array
    {
        $since = now()->subDays($days);

        $failures = GenerationFailure::where('created_at', '>=', $since);

        return [
            'total_failures' => $failures->count(),
            'total_refunded' => $failures->sum('credits_refunded'),
            'by_type' => GenerationFailure::where('created_at', '>=', $since)
                ->selectRaw('failure_type, COUNT(*) as count, SUM(credits_refunded) as total_refunded')
                ->groupBy('failure_type')
                ->get()
                ->toArray(),
            'by_model' => GenerationFailure::where('created_at', '>=', $since)
                ->selectRaw('model_used, COUNT(*) as count, SUM(credits_refunded) as total_refunded')
                ->groupBy('model_used')
                ->get()
                ->toArray(),
            'top_errors' => GenerationFailure::where('created_at', '>=', $since)
                ->selectRaw('error_message, COUNT(*) as count')
                ->groupBy('error_message')
                ->orderByDesc('count')
                ->limit(10)
                ->get()
                ->toArray(),
        ];
    }
}
