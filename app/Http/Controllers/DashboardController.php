<?php

namespace App\Http\Controllers;

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

        // Dashboard statistics
        $stats = [
            'total_templates' => 0, // Will be replaced when template generation is implemented
            'templates_this_month' => 0,
            'credits_remaining' => $user->credits ?? 100, // Placeholder for credit system
            'last_generated' => null,
        ];

        // Recent activity (placeholder)
        $recentActivity = [];

        return Inertia::render('Dashboard/Index', [
            'stats' => $stats,
            'recentActivity' => $recentActivity,
        ]);
    }
}
