<?php

namespace App\Jobs;

use App\Models\Generation;
use App\Services\GenerationService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class ProcessTemplateGeneration implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $timeout = 1800; // 30 minutes
    public $tries = 1; // Don't retry, too expensive

    /**
     * Create a new job instance.
     */
    public function __construct(
        public Generation $generation,
    ) {}

    /**
     * Execute the job - generate all remaining pages.
     */
    public function handle(GenerationService $generationService): void
    {
        Log::info("Starting background generation for Generation #{$this->generation->id}");
        
        try {
            // Update status to processing
            $this->generation->update([
                'status' => 'processing',
                'current_status' => 'Processing in background...',
            ]);

            // Generate all remaining pages
            while ($this->generation->current_page_index < $this->generation->total_pages) {
                $result = $generationService->generateNextPage($this->generation);

                if (!$result['success']) {
                    Log::error("Background generation failed for Generation #{$this->generation->id}", [
                        'error' => $result['error'],
                        'page' => $result['page'] ?? 'unknown',
                    ]);
                    
                    $this->generation->update([
                        'status' => 'failed',
                        'current_status' => 'Failed: ' . $result['error'],
                    ]);
                    
                    return;
                }

                // Refresh model to get latest data
                $this->generation->refresh();

                if ($result['completed']) {
                    break;
                }
            }

            Log::info("Background generation completed for Generation #{$this->generation->id}");
            
            // Send notification to user
            $this->generation->user->notify(
                new \App\Notifications\TemplateGenerationCompleted($this->generation)
            );

        } catch (\Exception $e) {
            Log::error("Background generation exception for Generation #{$this->generation->id}", [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            $this->generation->update([
                'status' => 'failed',
                'current_status' => 'Failed: ' . $e->getMessage(),
            ]);

            throw $e;
        }
    }

    /**
     * Handle a job failure.
     */
    public function failed(\Throwable $exception): void
    {
        Log::error("Background generation job failed for Generation #{$this->generation->id}", [
            'error' => $exception->getMessage(),
        ]);

        $this->generation->update([
            'status' => 'failed',
            'current_status' => 'Failed: ' . $exception->getMessage(),
        ]);
    }
}
