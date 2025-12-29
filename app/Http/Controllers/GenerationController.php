<?php

namespace App\Http\Controllers;

use App\Models\Generation;
use App\Models\Project;
use App\Services\GeminiService;
use App\Services\McpPromptBuilder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;

class GenerationController extends Controller
{
    protected GeminiService $geminiService;
    protected McpPromptBuilder $mcpPromptBuilder;

    public function __construct(GeminiService $geminiService, McpPromptBuilder $mcpPromptBuilder)
    {
        $this->geminiService = $geminiService;
        $this->mcpPromptBuilder = $mcpPromptBuilder;
    }

    /**
     * Generate template from wizard blueprint
     */
    public function generate(Request $request)
    {
        $validated = $request->validate([
            'blueprint' => 'required|array',
            'project_name' => 'nullable|string|max:255',
        ]);

        $user = Auth::user();
        
        // Auto select model based on available credits
        // If user has credits, use premium model, otherwise use free model
        $isPremium = $user->credits > 0;
        $modelName = $isPremium ? 'gemini-pro' : 'gemini-flash';

        DB::beginTransaction();
        
        try {
            // Create project
            $project = Project::create([
                'user_id' => $user->id,
                'name' => $validated['project_name'] ?? 'Generated Template ' . now()->format('Y-m-d H:i'),
                'blueprint' => $validated['blueprint'],
                'status' => 'generating',
            ]);

            // Build MCP prompt from blueprint
            $mcpPrompt = $this->mcpPromptBuilder->buildPrompt($validated['blueprint']);

            // Create generation record
            $generation = Generation::create([
                'user_id' => $user->id,
                'project_id' => $project->id,
                'model_used' => $modelName,
                'credits_used' => $isPremium ? 1 : 0,
                'status' => 'processing',
                'mcp_prompt' => $mcpPrompt,
                'started_at' => now(),
            ]);

            // Deduct credits if premium
            if ($isPremium) {
                $user->decrement('credits', 1);
            }

            // Generate template using Gemini
            $startTime = microtime(true);
            $result = $this->geminiService->generateTemplate($mcpPrompt, $isPremium);
            $processingTime = (int) ((microtime(true) - $startTime) * 1000); // in milliseconds

            if (!$result['success']) {
                // Update generation as failed
                $generation->update([
                    'status' => 'failed',
                    'error_message' => $result['error'],
                    'processing_time' => $processingTime,
                    'completed_at' => now(),
                ]);

                $project->update(['status' => 'failed']);

                // Refund credits if premium
                if ($isPremium) {
                    $user->increment('credits', 1);
                }

                DB::commit();

                return response()->json([
                    'success' => false,
                    'error' => $result['error'],
                ], 500);
            }

            // Update generation as completed
            $generation->update([
                'status' => 'completed',
                'generated_content' => $result['content'],
                'processing_time' => $processingTime,
                'completed_at' => now(),
            ]);

            $project->update([
                'status' => 'completed',
                'generated_at' => now(),
            ]);

            DB::commit();

            // Redirect to generation show page
            return redirect()->route('generation.show', $generation->id)
                ->with('success', 'Template generated successfully!');

        } catch (\Exception $e) {
            DB::rollBack();

            return response()->json([
                'success' => false,
                'error' => 'An error occurred during generation: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Show generation result
     */
    public function show(Generation $generation)
    {
        $this->authorize('view', $generation);

        $generation->load('project');

        return Inertia::render('Generation/Show', [
            'generation' => $generation,
        ]);
    }
}
