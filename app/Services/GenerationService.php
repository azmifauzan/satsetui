<?php

namespace App\Services;

use App\Models\Generation;
use App\Models\LlmModel;
use App\Models\Project;
use App\Models\User;
use Illuminate\Support\Facades\DB;

/**
 * Generation Service
 * 
 * Handles progressive template generation per page/feature
 */
class GenerationService
{
    public function __construct(
        private OpenAICompatibleService $llmService,
        private McpPromptBuilder $mcpPromptBuilder
    ) {}

    /**
     * Start progressive generation
     * 
     * @param array $blueprint The template blueprint
     * @param User $user The user initiating generation
     * @param string|null $modelName Optional model name (if null, auto-select)
     * @param string|null $projectName Optional project name
     */
    public function startGeneration(
        array $blueprint, 
        User $user, 
        ?string $modelName = null,
        ?string $projectName = null
    ): array {
        $pages = $blueprint['pages'] ?? ['home'];
        $totalPages = count($pages);
        
        // Auto-select model if not provided
        if (!$modelName) {
            $isPremium = $user->credits > 0;
            $modelName = $isPremium ? 'claude-haiku-4-5' : 'gemini-2.5-flash';
        }

        // Get model details
        $model = $this->llmService->getModel($modelName);
        if (!$model) {
            return [
                'success' => false,
                'error' => 'Model not found or inactive',
            ];
        }

        // Check if user has enough credits for premium models
        if (!$model->is_free) {
            $requiredCredits = $model->estimated_credits_per_generation;
            if ($user->credits < $requiredCredits) {
                return [
                    'success' => false,
                    'error' => 'Insufficient credits. Required: ' . $requiredCredits . ', Available: ' . $user->credits,
                ];
            }
        }

        DB::beginTransaction();
        
        try {
            // Create project
            $project = Project::create([
                'user_id' => $user->id,
                'name' => $projectName ?? 'Generated Template ' . now()->format('Y-m-d H:i'),
                'blueprint' => $blueprint,
                'status' => 'generating',
            ]);

            // Create generation record
            $generation = Generation::create([
                'user_id' => $user->id,
                'project_id' => $project->id,
                'model_used' => $modelName,
                'credits_used' => $model->is_free ? 0 : $model->estimated_credits_per_generation,
                'status' => 'processing',
                'current_status' => 'generating',
                'total_pages' => $totalPages,
                'current_page_index' => 0,
                'progress_data' => $this->initializeProgressData($pages),
                'mcp_prompt' => '', // Will be built per page
                'started_at' => now(),
            ]);

            // Deduct credits upfront if premium model
            if (!$model->is_free) {
                $user->decrement('credits', $model->estimated_credits_per_generation);
            }

            DB::commit();

            return [
                'success' => true,
                'generation_id' => $generation->id,
                'total_pages' => $totalPages,
                'model' => $modelName,
                'credits_charged' => $generation->credits_used,
            ];

        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    /**
     * Generate next page in sequence
     */
    public function generateNextPage(Generation $generation): array
    {
        $progressData = $generation->progress_data;
        $currentIndex = $generation->current_page_index;
        
        if ($currentIndex >= $generation->total_pages) {
            return [
                'success' => true,
                'completed' => true,
                'message' => 'All pages generated',
            ];
        }

        $pages = array_keys($progressData);
        $currentPage = $pages[$currentIndex];
        
        // Update status
        $progressData[$currentPage]['status'] = 'generating';
        $generation->update([
            'progress_data' => $progressData,
            'current_status' => "Generating {$currentPage} page...",
        ]);

        try {
            // Build prompt for current page only
            $project = $generation->project;
            $blueprint = $project->blueprint;
            $blueprint['pages'] = [$currentPage]; // Focus on one page
            
            $mcpPrompt = $this->mcpPromptBuilder->buildFromBlueprint($blueprint);
            
            // Generate with LLM
            $startTime = microtime(true);
            $result = $this->llmService->generateTemplate($mcpPrompt, $generation->model_used);
            $processingTime = (int) ((microtime(true) - $startTime) * 1000);

            if (!$result['success']) {
                // Mark page as failed
                $progressData[$currentPage]['status'] = 'failed';
                $progressData[$currentPage]['error'] = $result['error'];
                $progressData[$currentPage]['processing_time'] = $processingTime;
                
                $generation->update([
                    'progress_data' => $progressData,
                    'processing_time' => ($generation->processing_time ?? 0) + $processingTime,
                ]);

                return [
                    'success' => false,
                    'error' => $result['error'],
                    'page' => $currentPage,
                ];
            }

            // Mark page as completed and store content
            $progressData[$currentPage]['status'] = 'completed';
            $progressData[$currentPage]['content'] = $result['content'];
            $progressData[$currentPage]['processing_time'] = $processingTime;
            
            // Append to generated_content
            $allContent = $generation->generated_content ?? '';
            $separator = $allContent ? "\n\n/* ========== PAGE: {$currentPage} ========== */\n\n" : '';
            $allContent .= $separator . $result['content'];

            $generation->update([
                'generated_content' => $allContent,
                'progress_data' => $progressData,
                'current_page_index' => $currentIndex + 1,
                'processing_time' => ($generation->processing_time ?? 0) + $processingTime,
            ]);

            // Check if all pages are done
            $allCompleted = $currentIndex + 1 >= $generation->total_pages;
            
            if ($allCompleted) {
                $generation->update([
                    'status' => 'completed',
                    'current_status' => 'completed',
                    'completed_at' => now(),
                ]);
                
                $generation->project->update([
                    'status' => 'completed',
                    'generated_at' => now(),
                ]);
            }

            return [
                'success' => true,
                'completed' => $allCompleted,
                'page' => $currentPage,
                'current_index' => $currentIndex + 1,
                'total_pages' => $generation->total_pages,
                'progress_percentage' => round((($currentIndex + 1) / $generation->total_pages) * 100),
            ];

        } catch (\Exception $e) {
            // Mark page as failed
            $progressData[$currentPage]['status'] = 'failed';
            $progressData[$currentPage]['error'] = $e->getMessage();
            
            $generation->update([
                'progress_data' => $progressData,
                'status' => 'failed',
                'current_status' => 'failed',
                'error_message' => $e->getMessage(),
                'completed_at' => now(),
            ]);

            $generation->project->update(['status' => 'failed']);

            // Refund credits if premium model
            $model = $this->llmService->getModel($generation->model_used);
            if ($model && !$model->is_free) {
                // Refund the estimated credits since generation failed
                $generation->user->increment('credits', $generation->credits_used);
            }

            return [
                'success' => false,
                'error' => $e->getMessage(),
                'page' => $currentPage,
            ];
        }
    }

    /**
     * Refine generated content with additional prompt
     */
    public function refineGeneration(Generation $generation, string $refinementPrompt): array
    {
        if ($generation->status !== 'completed') {
            return [
                'success' => false,
                'error' => 'Can only refine completed generations',
            ];
        }

        try {
            // Build refinement prompt
            $fullPrompt = "You have previously generated the following code:\n\n";
            $fullPrompt .= $generation->generated_content;
            $fullPrompt .= "\n\n=== REFINEMENT REQUEST ===\n\n";
            $fullPrompt .= $refinementPrompt;
            $fullPrompt .= "\n\nPlease modify the code above according to the refinement request.";
            $fullPrompt .= " Return ONLY the complete updated code, no explanations.";

            $startTime = microtime(true);
            $result = $this->llmService->generateTemplate($fullPrompt, $generation->model_used);
            $processingTime = (int) ((microtime(true) - $startTime) * 1000);

            if (!$result['success']) {
                return [
                    'success' => false,
                    'error' => $result['error'],
                ];
            }

            // Update with refined content
            $generation->update([
                'generated_content' => $result['content'],
                'processing_time' => $generation->processing_time + $processingTime,
            ]);

            return [
                'success' => true,
                'content' => $result['content'],
                'processing_time' => $processingTime,
            ];

        } catch (\Exception $e) {
            return [
                'success' => false,
                'error' => $e->getMessage(),
            ];
        }
    }

    /**
     * Initialize progress data structure
     */
    private function initializeProgressData(array $pages): array
    {
        $data = [];
        foreach ($pages as $page) {
            $data[$page] = [
                'status' => 'pending', // pending, generating, completed, failed
                'content' => null,
                'error' => null,
                'processing_time' => 0,
            ];
        }
        return $data;
    }
}
