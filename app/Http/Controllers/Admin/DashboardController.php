<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\AdminStatisticsService;
use Inertia\Inertia;

/**
 * Admin Dashboard Controller
 * 
 * Displays comprehensive system statistics for administrators.
 */
class DashboardController extends Controller
{
    public function __construct(
        private AdminStatisticsService $statisticsService
    ) {}

    /**
     * Display admin dashboard with statistics
     * 
     * @return \Inertia\Response
     */
    public function index()
    {
        $statistics = $this->statisticsService->getOverallStatistics();
        $generationTrend = $this->statisticsService->getGenerationTrend(7);
        $creditUsageTrend = $this->statisticsService->getCreditUsageTrend(30);

        return Inertia::render('Admin/Index', [
            'statistics' => $statistics,
            'generationTrend' => $generationTrend,
            'creditUsageTrend' => $creditUsageTrend,
        ]);
    }
}
