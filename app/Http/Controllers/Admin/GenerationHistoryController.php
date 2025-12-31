<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Generation;
use App\Models\User;
use Illuminate\Http\Request;
use Inertia\Inertia;

/**
 * Generation History Controller
 * 
 * Monitors and manages all template generations.
 */
class GenerationHistoryController extends Controller
{
    /**
     * Display a listing of all generations with filters
     */
    public function index(Request $request)
    {
        $query = Generation::query()->with('user:id,name,email');

        // Search by user name or email
        if ($request->filled('search')) {
            $search = $request->search;
            $query->whereHas('user', function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%");
            });
        }

        // Filter by status
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        // Filter by model
        if ($request->filled('model')) {
            $query->where('model_used', $request->model);
        }

        // Filter by date range
        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }
        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }

        // Sort
        $sortBy = $request->get('sort_by', 'created_at');
        $sortOrder = $request->get('sort_order', 'desc');
        $query->orderBy($sortBy, $sortOrder);

        // Paginate
        $generations = $query->paginate(25)->withQueryString();

        return Inertia::render('Admin/Generations/Index', [
            'generations' => $generations,
            'filters' => $request->only(['search', 'status', 'model', 'date_from', 'date_to', 'sort_by', 'sort_order']),
        ]);
    }

    /**
     * Display detailed information about a generation
     */
    public function show(Generation $generation)
    {
        $generation->load(['user:id,name,email', 'pageGenerations']);

        return Inertia::render('Admin/Generations/Show', [
            'generation' => $generation,
        ]);
    }

    /**
     * Refund credits for a failed generation
     */
    public function refund(Generation $generation)
    {
        if ($generation->status !== 'failed') {
            return redirect()->back()
                ->with('error', 'Hanya generation yang failed yang dapat di-refund.');
        }

        if ($generation->credits_used <= 0) {
            return redirect()->back()
                ->with('error', 'Tidak ada credits yang digunakan.');
        }

        // Refund credits to user
        $generation->user->credits += $generation->credits_used;
        $generation->user->save();

        // Mark as refunded (you might want to add a 'refunded' column)
        // $generation->update(['refunded' => true]);

        return redirect()->back()
            ->with('success', "{$generation->credits_used} credits berhasil di-refund ke user.");
    }

    /**
     * Retry a failed generation
     */
    public function retry(Generation $generation)
    {
        if ($generation->status !== 'failed') {
            return redirect()->back()
                ->with('error', 'Hanya generation yang failed yang dapat di-retry.');
        }

        // Reset generation status
        $generation->update([
            'status' => 'pending',
            'error_message' => null,
            'current_page_index' => 0,
        ]);

        // Dispatch job to retry (assuming you have a job for this)
        // ProcessTemplateGeneration::dispatch($generation);

        return redirect()->back()
            ->with('success', 'Generation akan di-retry.');
    }
}
