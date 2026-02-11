<?php

namespace App\Http\Controllers;

use App\Models\Generation;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class DashboardController extends Controller
{
    /**
     * Display the user dashboard.
     */
    public function index(Request $request): Response
    {
        $user = $request->user();

        // Get user's completed generations
        $completedGenerations = Generation::where('user_id', $user->id)
            ->where('status', 'completed')
            ->get();

        // Get this month's generations
        $thisMonthGenerations = Generation::where('user_id', $user->id)
            ->where('status', 'completed')
            ->whereYear('completed_at', Carbon::now()->year)
            ->whereMonth('completed_at', Carbon::now()->month)
            ->count();

        // Get last generated template
        $lastGeneration = Generation::where('user_id', $user->id)
            ->where('status', 'completed')
            ->orderBy('completed_at', 'desc')
            ->first();

        // Dashboard statistics
        $stats = [
            'total_templates' => $completedGenerations->count(),
            'templates_this_month' => $thisMonthGenerations,
            'credits_remaining' => $user->credits ?? 0,
            'last_generated' => $lastGeneration?->completed_at?->diffForHumans(),
        ];

        // Recent activity (placeholder)
        $recentActivity = [];

        return Inertia::render('Dashboard/Index', [
            'stats' => $stats,
            'recentActivity' => $recentActivity,
        ]);
    }
}
