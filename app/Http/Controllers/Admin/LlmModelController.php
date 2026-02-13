<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\LlmModel;
use Illuminate\Http\Request;
use Inertia\Inertia;

/**
 * LLM Model Configuration Controller
 * 
 * Manages the 2 fixed model types (satset, expert)
 * with configurable providers and settings
 */
class LlmModelController extends Controller
{
    /**
     * Display the 2 model configurations
     */
    public function index()
    {
        $models = LlmModel::ordered()->get();

        return Inertia::render('Admin/Models/Index', [
            'models' => $models->map(function ($model) {
                return [
                    'id' => $model->id,
                    'model_type' => $model->model_type,
                    'display_name' => $model->display_name,
                    'description' => $model->description,
                    'provider' => $model->provider,
                    'model_name' => $model->model_name,
                    'base_credits' => $model->base_credits,
                    'is_active' => $model->is_active,
                    // Don't expose API keys and base URLs in list
                ];
            }),
        ]);
    }

    /**
     * Show the form for editing the specified model
     */
    public function edit(LlmModel $model)
    {
        return Inertia::render('Admin/Models/Edit', [
            'model' => [
                'id' => $model->id,
                'model_type' => $model->model_type,
                'display_name' => $model->display_name,
                'description' => $model->description,
                'provider' => $model->provider,
                'model_name' => $model->model_name,
                'base_credits' => $model->base_credits,
                'is_active' => $model->is_active,
                // Don't send actual keys, just indicator
                'has_api_key' => !empty($model->api_key),
                'has_base_url' => !empty($model->base_url),
                'base_url' => $model->base_url,
            ],
        ]);
    }

    /**
     * Update the specified model configuration
     */
    public function update(Request $request, LlmModel $model)
    {
        $validated = $request->validate([
            'provider' => ['required', 'in:gemini,openai'],
            'model_name' => ['required', 'string', 'max:255'],
            'api_key' => ['nullable', 'string'],
            'base_url' => ['nullable', 'string', 'url'],
            'base_credits' => ['required', 'integer', 'min:1'],
            'is_active' => ['required', 'boolean'],
        ]);

        // Only update API key if provided
        if (empty($validated['api_key'])) {
            unset($validated['api_key']);
        }

        // Only update base URL if provided
        if (empty($validated['base_url'])) {
            $validated['base_url'] = null;
        }

        $model->update($validated);

        return redirect()->route('admin.models.index')
            ->with('success', 'Konfigurasi model berhasil diperbarui.');
    }

    /**
     * Toggle model activation status
     */
    public function toggleActive(LlmModel $model)
    {
        $model->update([
            'is_active' => !$model->is_active,
        ]);

        $status = $model->is_active ? 'diaktifkan' : 'dinonaktifkan';

        return redirect()->back()
            ->with('success', "Model {$model->display_name} berhasil {$status}.");
    }
}

