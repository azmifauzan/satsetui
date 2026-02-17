<?php

namespace App\Services;

use App\Models\Generation;
use App\Models\GenerationFile;
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
        private McpPromptBuilder $mcpPromptBuilder,
        private CreditService $creditService,
        private CostTrackingService $costTrackingService,
        private ScaffoldGeneratorService $scaffoldGenerator
    ) {}

    /**
     * Start progressive generation
     *
     * @param  array  $blueprint  The template blueprint
     * @param  User  $user  The user initiating generation
     * @param  string|null  $modelType  Optional model type ('satset' or 'expert')
     * @param  string|null  $projectName  Optional project name
     */
    public function startGeneration(
        array $blueprint,
        User $user,
        ?string $modelType = null,
        ?string $projectName = null
    ): array {
        // Get all pages to generate (includes regular pages, custom pages, and component showcase pages)
        $pages = $this->mcpPromptBuilder->getPageList($blueprint);
        $totalPages = count($pages);

        // Default to 'satset' if not provided
        if (! $modelType) {
            $modelType = 'satset';
        }

        // Get model details
        $model = $this->llmService->getModelByType($modelType);
        if (! $model) {
            return [
                'success' => false,
                'error' => 'Model type not found or inactive',
            ];
        }

        // Check if user has enough credits
        $requiredCredits = $model->base_credits;
        if ($user->credits < $requiredCredits) {
            return [
                'success' => false,
                'error' => 'Insufficient credits. Required: '.$requiredCredits.', Available: '.$user->credits,
            ];
        }

        DB::beginTransaction();

        try {
            // Create project
            $project = Project::create([
                'user_id' => $user->id,
                'name' => $projectName ?? 'Generated Template '.now()->format('Y-m-d H:i'),
                'blueprint' => $blueprint,
                'status' => 'generating',
            ]);

            // Create generation record
            $generation = Generation::create([
                'user_id' => $user->id,
                'project_id' => $project->id,
                'model_used' => $modelType,
                'credits_used' => $requiredCredits,
                'status' => 'processing',
                'current_status' => 'generating',
                'total_pages' => $totalPages,
                'current_page_index' => 0,
                'progress_data' => $this->initializeProgressData($pages),
                'mcp_prompt' => '', // Will be built per page
                'started_at' => now(),
            ]);

            // Charge credits upfront
            $this->creditService->charge(
                $user,
                $requiredCredits,
                $generation,
                "Template generation: {$projectName}"
            );

            // Generate scaffold files for JS framework outputs
            $outputFormat = $blueprint['outputFormat'] ?? 'html-css';
            if ($this->scaffoldGenerator->requiresScaffold($outputFormat)) {
                $frameworkConfig = $blueprint['frameworkConfig'] ?? [];
                $theme = $blueprint['theme'] ?? [];
                $layout = $blueprint['layout'] ?? [];

                $this->scaffoldGenerator->generateScaffold(
                    $generation,
                    $outputFormat,
                    $frameworkConfig,
                    $pages,
                    $theme,
                    $layout
                );
            }

            DB::commit();

            return [
                'success' => true,
                'generation_id' => $generation->id,
                'total_pages' => $totalPages,
                'model' => $modelType,
                'credits_charged' => $generation->credits_used,
            ];

        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    /**
     * Generate next page in sequence
     *
     * @param  int  $retryCount  Current retry attempt (for handling timeouts)
     */
    public function generateNextPage(Generation $generation, int $retryCount = 0): array
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

        $maxRetries = 3; // Max retry attempts for timeout errors

        // Update status
        $progressData[$currentPage]['status'] = 'generating';
        $generation->update([
            'progress_data' => $progressData,
            'current_status' => "Generating {$currentPage} page...",
        ]);

        try {
            // Get model details
            $model = $this->llmService->getModelByType($generation->model_used);
            $contextLimit = $model ? 200000 : 8192; // Default context or reasonable limit

            // Build prompt for current page using per-page builder
            $project = $generation->project;
            $blueprint = $project->blueprint;
            $outputFormat = $blueprint['outputFormat'] ?? 'html-css';
            $frameworkConfig = $blueprint['frameworkConfig'] ?? [];
            $isFramework = $this->scaffoldGenerator->requiresScaffold($outputFormat);

            $mcpPrompt = $this->mcpPromptBuilder->buildForPage($blueprint, $currentPage, $currentIndex);

            // Add context from previously generated pages
            $previousContext = $this->buildPreviousPageContext($generation, $currentIndex);
            if ($previousContext) {
                $mcpPrompt .= "\n\n=== CONTEXT FROM PREVIOUSLY GENERATED PAGES ===\n\n";
                $mcpPrompt .= $previousContext;
                $mcpPrompt .= "\n\nPlease ensure the {$currentPage} page follows the same style, structure, and naming conventions as the pages above.\n";
            }

            // Add strict instruction to return ONLY code
            $mcpPrompt .= "\n\n=== CRITICAL OUTPUT REQUIREMENTS ===\n";
            $mcpPrompt .= "- Return ONLY the complete, working code\n";
            $mcpPrompt .= "- DO NOT include any explanations, comments, or markdown formatting\n";
            $mcpPrompt .= "- DO NOT wrap code in ```html or ``` blocks\n";
            $mcpPrompt .= "- DO NOT add introductory text like 'Here is...' or 'This code...'\n";

            if ($isFramework) {
                $mcpPrompt .= "- Start directly with the file comment (// src/pages/...)\n";
                $mcpPrompt .= "- End directly with the final export or closing tag\n";
            } else {
                $mcpPrompt .= "- Start directly with <!DOCTYPE html> or the opening tag\n";
                $mcpPrompt .= "- End directly with the closing </html> tag or final closing tag\n";
            }

            // Trim prompt if exceeds context length (reserve 20% for output)
            $maxInputTokens = (int) ($contextLimit * 0.8);
            $mcpPrompt = $this->trimPromptToFit($mcpPrompt, $maxInputTokens);

            // Create PageGeneration record to track prompt/response
            $pageFilePath = $isFramework
                ? $this->scaffoldGenerator->getPageFilePath($currentPage, $outputFormat, $frameworkConfig)
                : null;
            $pageFileType = $isFramework
                ? $this->scaffoldGenerator->getPageFileExtension($outputFormat, $frameworkConfig)
                : 'html';

            $pageGeneration = \App\Models\PageGeneration::create([
                'generation_id' => $generation->id,
                'page_name' => $currentPage,
                'page_type' => str_starts_with($currentPage, 'custom:') ? 'custom' : 'predefined',
                'page_index' => $currentIndex,
                'mcp_prompt' => $mcpPrompt,
                'file_path' => $pageFilePath,
                'file_type' => $pageFileType,
                'status' => 'generating',
                'started_at' => now(),
            ]);

            // Generate with LLM
            $startTime = microtime(true);
            $result = $this->llmService->generateTemplate($mcpPrompt, $generation->model_used);
            $processingTime = (int) ((microtime(true) - $startTime) * 1000);

            if (! $result['success']) {
                // Check if error is timeout (524) and should retry
                $isTimeoutError = str_contains($result['error'], '524') ||
                                  str_contains(strtolower($result['error']), 'timeout');

                if ($isTimeoutError && $retryCount < $maxRetries) {
                    // Log retry attempt
                    \Illuminate\Support\Facades\Log::info("Retrying generation for page '{$currentPage}' (attempt ".($retryCount + 1)."/{$maxRetries})", [
                        'generation_id' => $generation->id,
                        'page' => $currentPage,
                        'error' => $result['error'],
                    ]);

                    // Wait a bit before retry (exponential backoff)
                    sleep(pow(2, $retryCount)); // 1s, 2s, 4s

                    // Retry the generation
                    return $this->generateNextPage($generation, $retryCount + 1);
                }

                // Update PageGeneration record with failure
                $pageGeneration->update([
                    'llm_response' => json_encode($result),
                    'status' => 'failed',
                    'error_message' => $result['error'],
                    'processing_time_ms' => $processingTime,
                    'completed_at' => now(),
                ]);

                // Mark page as failed
                $progressData[$currentPage]['status'] = 'failed';
                $progressData[$currentPage]['error'] = $result['error'];
                $progressData[$currentPage]['processing_time'] = $processingTime;
                $progressData[$currentPage]['retry_count'] = $retryCount;

                $generation->update([
                    'progress_data' => $progressData,
                    'processing_time' => ($generation->processing_time ?? 0) + $processingTime,
                ]);

                return [
                    'success' => false,
                    'error' => $result['error'],
                    'page' => $currentPage,
                    'retry_count' => $retryCount,
                ];
            }

            // Update PageGeneration record with success
            $pageGeneration->update([
                'llm_response' => json_encode($result),
                'generated_content' => $result['content'],
                'status' => 'completed',
                'input_tokens' => $result['usage']['input_tokens'] ?? 0,
                'output_tokens' => $result['usage']['output_tokens'] ?? 0,
                'processing_time_ms' => $processingTime,
                'completed_at' => now(),
            ]);

            // Record cost tracking
            $inputTokens = $result['usage']['input_tokens'] ?? 0;
            $outputTokens = $result['usage']['output_tokens'] ?? 0;
            $creditsChargedThisPage = 0; // Per-page cost calculation if needed

            $this->costTrackingService->recordCost(
                $pageGeneration,
                $generation,
                $generation->user,
                $generation->model_used,
                $this->getProviderName($generation->model_used),
                $inputTokens,
                $outputTokens,
                $creditsChargedThisPage,
                $processingTime,
                $result['raw_request'] ?? null,
                $result['raw_response'] ?? null
            );

            // Mark page as completed and store content
            $progressData[$currentPage]['status'] = 'completed';
            $progressData[$currentPage]['content'] = $result['content'];
            $progressData[$currentPage]['processing_time'] = $processingTime;

            // Create GenerationFile record for framework outputs
            if ($isFramework && $pageFilePath) {
                GenerationFile::create([
                    'generation_id' => $generation->id,
                    'page_generation_id' => $pageGeneration->id,
                    'file_path' => $pageFilePath,
                    'file_content' => $result['content'],
                    'file_type' => $pageFileType,
                    'is_scaffold' => false,
                ]);
            }

            // Append to generated_content
            $allContent = $generation->generated_content ?? '';
            if ($isFramework) {
                $separator = $allContent ? "\n\n/* ========== FILE: {$pageFilePath} ========== */\n\n" : "/* ========== FILE: {$pageFilePath} ========== */\n\n";
            } else {
                $separator = $allContent ? "\n\n/* ========== PAGE: {$currentPage} ========== */\n\n" : '';
            }
            $allContent .= $separator.$result['content'];

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
            // Record failure
            $failure = \App\Models\GenerationFailure::create([
                'generation_id' => $generation->id,
                'user_id' => $generation->user_id,
                'page_generation_id' => $pageGeneration->id ?? null,
                'failure_type' => 'generation_error',
                'error_code' => $e->getCode(),
                'error_message' => $e->getMessage(),
                'error_stack_trace' => $e->getTraceAsString(),
                'model_used' => $generation->model_used,
                'page_name' => $currentPage,
                'page_index' => $currentIndex,
                'attempt_number' => 1,
                'ip_address' => request()->ip(),
                'user_agent' => request()->userAgent(),
                'additional_context' => [
                    'blueprint' => $generation->project->blueprint,
                    'current_page' => $currentPage,
                ],
            ]);

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
            if ($model && ! $model->is_free && $generation->credits_used > 0) {
                $this->creditService->refund(
                    $generation->user,
                    $generation->credits_used,
                    $generation,
                    $failure,
                    "Generation failed at page '{$currentPage}': {$e->getMessage()}"
                );
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
    public function refineGeneration(
        Generation $generation,
        string $refinementPrompt,
        ?string $pageName = null,
        ?string $model = null
    ): array {
        if ($generation->status !== 'completed') {
            return [
                'success' => false,
                'error' => 'Can only refine completed generations',
            ];
        }

        try {
            // Parse page name from prompt if not provided (legacy support)
            if (! $pageName && preg_match('/For the page ["\']([^"\']+)["\']:/', $refinementPrompt, $matches)) {
                $pageName = $matches[1];
            }

            // Store user message
            $generation->refinementMessages()->create([
                'role' => 'user',
                'content' => $refinementPrompt,
                'type' => 'refine',
                'page_name' => $pageName,
            ]);

            // Store status message
            $statusMsg = $pageName
                ? "Refining {$pageName}..."
                : 'Refining generation...';
            $generation->refinementMessages()->create([
                'role' => 'system',
                'content' => $statusMsg,
                'type' => 'status',
                'page_name' => $pageName,
            ]);

            // Get the content to refine
            $progressData = $generation->progress_data;
            $existingContent = null;

            if ($pageName && isset($progressData[$pageName]['content'])) {
                $existingContent = $progressData[$pageName]['content'];
            } elseif (! $pageName) {
                // Refine all content if no specific page
                $existingContent = $generation->generated_content;
            }

            if (! $existingContent) {
                $errorMsg = $pageName
                    ? "Page '{$pageName}' not found"
                    : 'No content to refine';

                $generation->refinementMessages()->create([
                    'role' => 'system',
                    'content' => $errorMsg,
                    'type' => 'error',
                    'page_name' => $pageName,
                ]);

                return [
                    'success' => false,
                    'error' => $errorMsg,
                ];
            }

            // Build refinement prompt
            $fullPrompt = "You have previously generated the following code:\n\n";
            $fullPrompt .= $existingContent;
            $fullPrompt .= "\n\n=== REFINEMENT REQUEST ===\n\n";
            $fullPrompt .= $refinementPrompt;
            $fullPrompt .= "\n\nPlease modify the code above according to the refinement request.";
            $fullPrompt .= ' Return ONLY the complete updated code, no explanations.';

            $startTime = microtime(true);
            $modelToUse = $model ?? $generation->model_used;
            $result = $this->llmService->generateTemplate($fullPrompt, $modelToUse);
            $processingTime = (int) ((microtime(true) - $startTime) * 1000);

            if (! $result['success']) {
                $generation->refinementMessages()->create([
                    'role' => 'system',
                    'content' => $result['error'] ?? 'Refinement failed',
                    'type' => 'error',
                    'page_name' => $pageName,
                ]);

                return [
                    'success' => false,
                    'error' => $result['error'],
                ];
            }

            // Update the appropriate content
            if ($pageName && isset($progressData[$pageName])) {
                // Update specific page in progress_data
                $progressData[$pageName]['content'] = $result['content'];
                $generation->update([
                    'progress_data' => $progressData,
                    'processing_time' => $generation->processing_time + $processingTime,
                ]);
            } else {
                // Update generated_content (legacy)
                $generation->update([
                    'generated_content' => $result['content'],
                    'processing_time' => $generation->processing_time + $processingTime,
                ]);
            }

            // Store success message
            $successMsg = $pageName
                ? "Refinement applied to **{$pageName}**. Preview updated."
                : 'Refinement applied. Preview updated.';
            $generation->refinementMessages()->create([
                'role' => 'assistant',
                'content' => $successMsg,
                'type' => 'refine',
                'page_name' => $pageName,
            ]);

            return [
                'success' => true,
                'content' => $result['content'],
                'page_name' => $pageName,
                'processing_time' => $processingTime,
            ];

        } catch (\Exception $e) {
            // Store error message
            if (isset($generation)) {
                $generation->refinementMessages()->create([
                    'role' => 'system',
                    'content' => $e->getMessage(),
                    'type' => 'error',
                    'page_name' => $pageName ?? null,
                ]);
            }

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

    /**
     * Build context from previously generated pages
     */
    private function buildPreviousPageContext(Generation $generation, int $currentIndex): string
    {
        if ($currentIndex === 0) {
            return ''; // No previous pages
        }

        $context = '';
        $progressData = $generation->progress_data;
        $pages = array_keys($progressData);
        $blueprint = $generation->project->blueprint;
        $outputFormat = $blueprint['outputFormat'] ?? 'html-css';
        $isFramework = $this->scaffoldGenerator->requiresScaffold($outputFormat);

        // Include last 2 pages for context (not all to keep prompt size reasonable)
        $startIndex = max(0, $currentIndex - 2);

        for ($i = $startIndex; $i < $currentIndex; $i++) {
            $pageName = $pages[$i];
            $pageData = $progressData[$pageName];

            if (isset($pageData['content']) && $pageData['status'] === 'completed') {
                $context .= "--- Page: {$pageName} ---\n";
                $content = $pageData['content'];

                if ($isFramework) {
                    // For framework output, extract imports and component structure
                    $lines = explode("\n", $content);
                    $imports = [];
                    $structureLines = [];

                    foreach ($lines as $line) {
                        if (preg_match('/^import\s/', $line)) {
                            $imports[] = $line;
                        }
                    }

                    if (! empty($imports)) {
                        $context .= "Imports:\n".implode("\n", array_slice($imports, 0, 15))."\n\n";
                    }

                    // Extract first 500 chars as structure overview
                    $context .= "Structure preview:\n".substr($content, 0, 500)."...\n";
                } else {
                    // HTML output - extract head/body structure
                    if (preg_match('/<head[^>]*>(.*?)<\/head>/is', $content, $headMatch)) {
                        $context .= "Head section:\n".substr($headMatch[1], 0, 500)."...\n\n";
                    }

                    if (preg_match('/<body[^>]*>(.*?)<\/body>/is', $content, $bodyMatch)) {
                        preg_match_all('/class="([^"]+)"/i', $bodyMatch[1], $classMatches);
                        $classes = array_unique(array_slice($classMatches[1], 0, 10));
                        $context .= 'CSS classes used: '.implode(', ', $classes)."\n";

                        $bodyPreview = strip_tags(substr($bodyMatch[1], 0, 300));
                        $context .= 'Structure preview: '.trim($bodyPreview)."...\n";
                    }
                }

                $context .= "\n";
            }
        }

        return $context;
    }

    /**
     * Trim prompt to fit within context length limit
     *
     * Uses approximate token counting (4 chars = 1 token as rough estimate)
     * Prioritizes keeping instructions and current page info, trims context if needed
     */
    private function trimPromptToFit(string $prompt, int $maxTokens): string
    {
        // Approximate token count (4 chars per token is rough average)
        $estimatedTokens = strlen($prompt) / 4;

        if ($estimatedTokens <= $maxTokens) {
            return $prompt; // Fits within limit
        }

        // Need to trim - extract sections
        $sections = [];

        // Split by major sections
        if (preg_match('/^(.*?)(=== CONTEXT FROM PREVIOUSLY GENERATED PAGES ===.*?)(=== CRITICAL OUTPUT REQUIREMENTS ===.*)$/s', $prompt, $matches)) {
            $sections['core'] = $matches[1]; // Main prompt + requirements
            $sections['context'] = $matches[2]; // Previous pages context
            $sections['output'] = $matches[3]; // Output requirements
        } else {
            // Fallback if pattern doesn't match
            return substr($prompt, 0, $maxTokens * 4); // Simple truncation
        }

        // Calculate token allocation
        $coreTokens = strlen($sections['core']) / 4;
        $outputTokens = strlen($sections['output']) / 4;
        $contextTokens = strlen($sections['context']) / 4;

        // Core + output requirements are essential
        $essentialTokens = $coreTokens + $outputTokens;

        if ($essentialTokens > $maxTokens) {
            // Even without context, we're over limit - trim core
            $allowedChars = (int) ($maxTokens * 4 * 0.9); // 90% for safety

            return substr($sections['core'], 0, $allowedChars).$sections['output'];
        }

        // Calculate available tokens for context
        $availableForContext = $maxTokens - $essentialTokens;

        if ($contextTokens <= $availableForContext) {
            return $prompt; // All fits
        }

        // Trim context to fit
        $allowedContextChars = (int) ($availableForContext * 4);
        $trimmedContext = substr($sections['context'], 0, $allowedContextChars);
        $trimmedContext .= "\n\n[Context truncated to fit model's context window]\n\n";

        return $sections['core'].$trimmedContext.$sections['output'];
    }

    /**
     * Get provider name from model name
     */
    private function getProviderName(string $modelName): string
    {
        // Determine provider from model name
        if (str_contains(strtolower($modelName), 'gpt')) {
            return 'openai';
        }
        if (str_contains(strtolower($modelName), 'claude')) {
            return 'anthropic';
        }
        if (str_contains(strtolower($modelName), 'gemini')) {
            return 'google';
        }

        // Default fallback
        return 'unknown';
    }
}
