<?php

namespace App\Http\Controllers;

use App\Models\Generation;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;

class TemplateController extends Controller
{
    use AuthorizesRequests;

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
            'templates' => $templates->through(fn ($generation) => [
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

    /**
     * Rename the template (updates the underlying project name).
     */
    public function rename(Request $request, Generation $generation)
    {
        $this->authorize('update', $generation);

        $request->validate([
            'name' => ['required', 'string', 'max:255'],
        ]);

        $generation->project->update(['name' => $request->name]);

        return back()->with('success', 'Template renamed successfully.');
    }

    /**
     * Delete a template and its associated project.
     */
    public function destroy(Generation $generation)
    {
        $this->authorize('delete', $generation);

        $generation->project->delete();
        $generation->delete();

        return redirect()->route('templates.index')
            ->with('success', 'Template deleted successfully.');
    }
}
