<?php

namespace App\Services;

use App\Models\User;
use App\Models\Generation;
use App\Models\LlmModel;
use App\Models\CreditTransaction;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Carbon;

/**
 * AdminStatisticsService
 * 
 * Provides comprehensive statistics for the admin dashboard.
 * All statistics are optimized with proper indexing and caching.
 */
class AdminStatisticsService
{
    /**
     * Get overall system statistics for admin dashboard
     * 
     * @return array
     */
    public function getOverallStatistics(): array
    {
        return [
            'users' => $this->getUserStatistics(),
            'generations' => $this->getGenerationStatistics(),
            'credits' => $this->getCreditStatistics(),
            'models' => $this->getModelStatistics(),
            'system' => $this->getSystemHealthStatistics(),
        ];
    }

    /**
     * Get user statistics
     * 
     * @return array
     */
    public function getUserStatistics(): array
    {
        $totalUsers = User::count();
        $premiumUsers = User::where('is_premium', true)->count();
        $freeUsers = $totalUsers - $premiumUsers;
        $activeUsers = User::where('is_active', true)->count();
        $newUsersLast30Days = User::where('created_at', '>=', Carbon::now()->subDays(30))->count();
        $activeUsersLast7Days = User::whereHas('generations', function ($query) {
            $query->where('created_at', '>=', Carbon::now()->subDays(7));
        })->count();

        return [
            'total' => $totalUsers,
            'premium' => $premiumUsers,
            'free' => $freeUsers,
            'active' => $activeUsers,
            'new_30d' => $newUsersLast30Days,
            'active_7d' => $activeUsersLast7Days,
            'premium_percentage' => $totalUsers > 0 ? round(($premiumUsers / $totalUsers) * 100, 2) : 0,
        ];
    }

    /**
     * Get generation statistics
     * 
     * @return array
     */
    public function getGenerationStatistics(): array
    {
        $totalGenerations = Generation::count();
        $completedGenerations = Generation::where('status', 'completed')->count();
        $failedGenerations = Generation::where('status', 'failed')->count();
        $inProgressGenerations = Generation::where('status', 'in_progress')->count();

        // Generations per category
        $perCategory = Generation::select('category', DB::raw('count(*) as count'))
            ->groupBy('category')
            ->orderBy('count', 'desc')
            ->limit(10)
            ->get()
            ->pluck('count', 'category')
            ->toArray();

        // Average processing time (in seconds)
        $avgProcessingTime = Generation::where('status', 'completed')
            ->whereNotNull('processing_time')
            ->avg('processing_time');

        return [
            'total' => $totalGenerations,
            'completed' => $completedGenerations,
            'failed' => $failedGenerations,
            'in_progress' => $inProgressGenerations,
            'success_rate' => $totalGenerations > 0 ? round(($completedGenerations / $totalGenerations) * 100, 2) : 0,
            'per_category' => $perCategory,
            'avg_processing_time' => $avgProcessingTime ? round($avgProcessingTime, 2) : 0,
        ];
    }

    /**
     * Get credit statistics
     * 
     * @return array
     */
    public function getCreditStatistics(): array
    {
        $totalCreditsIssued = User::sum('credits');
        $totalCreditsUsed = Generation::sum('credits_used');
        $totalCreditsRemaining = $totalCreditsIssued;

        // Average credits per generation
        $avgCreditsPerGeneration = Generation::where('credits_used', '>', 0)
            ->avg('credits_used');

        // Credit transactions (if tracked separately)
        $recentTransactions = CreditTransaction::where('created_at', '>=', Carbon::now()->subDays(30))
            ->count();

        // Estimate revenue (assuming 1 credit = Rp 100)
        $estimatedRevenue = $totalCreditsUsed * 100;

        return [
            'total_issued' => $totalCreditsIssued,
            'total_used' => $totalCreditsUsed,
            'total_remaining' => $totalCreditsRemaining,
            'avg_per_generation' => $avgCreditsPerGeneration ? round($avgCreditsPerGeneration, 2) : 0,
            'recent_transactions_30d' => $recentTransactions,
            'estimated_revenue' => $estimatedRevenue,
        ];
    }

    /**
     * Get LLM model statistics
     * 
     * @return array
     */
    public function getModelStatistics(): array
    {
        $totalModels = LlmModel::count();
        $activeModels = LlmModel::where('is_active', true)->count();

        // Most used model
        $mostUsedModel = Generation::select('model_used', DB::raw('count(*) as count'))
            ->groupBy('model_used')
            ->orderBy('count', 'desc')
            ->first();

        // Model usage distribution
        $modelUsage = Generation::select('model_used', DB::raw('count(*) as count'))
            ->groupBy('model_used')
            ->orderBy('count', 'desc')
            ->limit(10)
            ->get()
            ->pluck('count', 'model_used')
            ->toArray();

        return [
            'total' => $totalModels,
            'active' => $activeModels,
            'most_used' => $mostUsedModel ? $mostUsedModel->model_used : null,
            'most_used_count' => $mostUsedModel ? $mostUsedModel->count : 0,
            'usage_distribution' => $modelUsage,
        ];
    }

    /**
     * Get system health statistics
     * 
     * @return array
     */
    public function getSystemHealthStatistics(): array
    {
        // Queue jobs (if using database queue)
        $queueJobsPending = DB::table('jobs')->count();
        $failedJobsLast24h = DB::table('failed_jobs')
            ->where('failed_at', '>=', Carbon::now()->subDay())
            ->count();

        // Error rate (failed generations / total generations in last 24h)
        $generationsLast24h = Generation::where('created_at', '>=', Carbon::now()->subDay())->count();
        $failedLast24h = Generation::where('created_at', '>=', Carbon::now()->subDay())
            ->where('status', 'failed')
            ->count();
        $errorRate = $generationsLast24h > 0 ? round(($failedLast24h / $generationsLast24h) * 100, 2) : 0;

        return [
            'queue_pending' => $queueJobsPending,
            'failed_jobs_24h' => $failedJobsLast24h,
            'error_rate' => $errorRate,
            'generations_24h' => $generationsLast24h,
            'failed_generations_24h' => $failedLast24h,
        ];
    }

    /**
     * Get generation trend data for charts (last 7 days)
     * 
     * @param int $days
     * @return array
     */
    public function getGenerationTrend(int $days = 7): array
    {
        $trend = Generation::select(
            DB::raw('DATE(created_at) as date'),
            DB::raw('count(*) as total'),
            DB::raw("SUM(CASE WHEN status = 'completed' THEN 1 ELSE 0 END) as completed"),
            DB::raw("SUM(CASE WHEN status = 'failed' THEN 1 ELSE 0 END) as failed")
        )
            ->where('created_at', '>=', Carbon::now()->subDays($days))
            ->groupBy('date')
            ->orderBy('date', 'asc')
            ->get();

        return [
            'labels' => $trend->pluck('date')->toArray(),
            'total' => $trend->pluck('total')->toArray(),
            'completed' => $trend->pluck('completed')->toArray(),
            'failed' => $trend->pluck('failed')->toArray(),
        ];
    }

    /**
     * Get credit usage trend for charts (last 30 days)
     * 
     * @param int $days
     * @return array
     */
    public function getCreditUsageTrend(int $days = 30): array
    {
        $trend = Generation::select(
            DB::raw('DATE(created_at) as date'),
            DB::raw('SUM(credits_used) as credits')
        )
            ->where('created_at', '>=', Carbon::now()->subDays($days))
            ->groupBy('date')
            ->orderBy('date', 'asc')
            ->get();

        return [
            'labels' => $trend->pluck('date')->toArray(),
            'credits' => $trend->pluck('credits')->toArray(),
        ];
    }
}
