<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\LlmModel;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Inertia\Inertia;

/**
 * LLM Model Management Controller
 * 
 * Handles CRUD operations for LLM models configuration.
 */
class LlmModelController extends Controller
{
    /**
     * Display a listing of LLM models
     */
    public function index()
    {
        $models = LlmModel::ordered()->get();

        return Inertia::render('Admin/Models/Index', [
            'models' => $models,
        ]);
    }

    /**
     * Show the form for creating a new model
     */
    public function create()
    {
        return Inertia::render('Admin/Models/Create');
    }

    /**
     * Store a newly created model
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255', 'unique:llm_models,name'],
            'display_name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:500'],
            'input_price_per_million' => ['required', 'numeric', 'min:0'],
            'output_price_per_million' => ['required', 'numeric', 'min:0'],
            'estimated_credits_per_generation' => ['required', 'integer', 'min:0'],
            'context_length' => ['required', 'integer', 'min:0'],
            'is_free' => ['required', 'boolean'],
            'is_active' => ['required', 'boolean'],
            'sort_order' => ['nullable', 'integer', 'min:0'],
        ]);

        // Auto-assign sort_order if not provided
        if (!isset($validated['sort_order'])) {
            $maxOrder = LlmModel::max('sort_order') ?? 0;
            $validated['sort_order'] = $maxOrder + 1;
        }

        $model = LlmModel::create($validated);

        return redirect()->route('admin.models.index')
            ->with('success', 'Model LLM berhasil ditambahkan.');
    }

    /**
     * Display the specified model
     */
    public function show(LlmModel $model)
    {
        return Inertia::render('Admin/Models/Show', [
            'model' => $model,
        ]);
    }

    /**
     * Show the form for editing the specified model
     */
    public function edit(LlmModel $model)
    {
        return Inertia::render('Admin/Models/Edit', [
            'model' => $model,
        ]);
    }

    /**
     * Update the specified model
     */
    public function update(Request $request, LlmModel $model)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255', Rule::unique('llm_models')->ignore($model->id)],
            'display_name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:500'],
            'input_price_per_million' => ['required', 'numeric', 'min:0'],
            'output_price_per_million' => ['required', 'numeric', 'min:0'],
            'estimated_credits_per_generation' => ['required', 'integer', 'min:0'],
            'context_length' => ['required', 'integer', 'min:0'],
            'is_free' => ['required', 'boolean'],
            'is_active' => ['required', 'boolean'],
            'sort_order' => ['nullable', 'integer', 'min:0'],
        ]);

        $model->update($validated);

        return redirect()->route('admin.models.index')
            ->with('success', 'Model LLM berhasil diperbarui.');
    }

    /**
     * Remove the specified model
     */
    public function destroy(LlmModel $model)
    {
        // Check if model is being used in any generations
        $usageCount = \App\Models\Generation::where('model_used', $model->name)->count();
        
        if ($usageCount > 0) {
            return redirect()->back()
                ->with('error', "Tidak dapat menghapus model ini karena sudah digunakan dalam {$usageCount} generation.");
        }

        $model->delete();

        return redirect()->route('admin.models.index')
            ->with('success', 'Model LLM berhasil dihapus.');
    }

    /**
     * Reorder models (bulk update sort_order)
     */
    public function reorder(Request $request)
    {
        $validated = $request->validate([
            'models' => ['required', 'array'],
            'models.*.id' => ['required', 'exists:llm_models,id'],
            'models.*.sort_order' => ['required', 'integer', 'min:0'],
        ]);

        foreach ($validated['models'] as $modelData) {
            LlmModel::where('id', $modelData['id'])
                ->update(['sort_order' => $modelData['sort_order']]);
        }

        return redirect()->back()
            ->with('success', 'Urutan model berhasil diperbarui.');
    }
}
