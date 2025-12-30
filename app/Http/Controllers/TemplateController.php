<?php

namespace App\Http\Controllers;

use App\Models\Generation;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;

class TemplateController extends Controller
{
    /**
     * Display a listing of user's templates.
     */
    public function index(Request $request)
    {
        $user = Auth::user();
        
        $templates = Generation::with(['project', 'pageGenerations'])
            ->where('user_id', $user->id)
            ->latest()
            ->paginate(12);

        return Inertia::render('Templates/Index', [
            'templates' => $templates->through(fn($generation) => [
                'id' => $generation->id,
                'name' => $generation->project->name,
                'status' => $generation->status,
                'current_status' => $generation->current_status,
                'total_pages' => $generation->total_pages,
                'current_page_index' => $generation->current_page_index,
                'progress_percentage' => $generation->total_pages > 0 
                    ? round(($generation->current_page_index / $generation->total_pages) * 100) 
                    : 0,
                'model_used' => $generation->model_used,
                'created_at' => $generation->created_at->diffForHumans(),
                'completed_at' => $generation->completed_at?->diffForHumans(),
                'can_view' => true,
            ]),
            'pagination' => [
                'current_page' => $templates->currentPage(),
                'last_page' => $templates->lastPage(),
                'total' => $templates->total(),
            ],
        ]);
    }
}

