<?php

namespace App\Jobs;

use App\Models\Generation;
use App\Services\GenerationService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class GenerateSharedLayout implements ShouldQueue
{
    use InteractsWithQueue, Queueable, SerializesModels;

    /**
     * The number of times the job may be attempted.
     */
    public int $tries = 2;

    /**
     * The number of seconds the job can run before timing out.
     */
    public int $timeout = 120;

    public function __construct(
        public readonly int $generationId,
        public readonly array $blueprint,
        public readonly string $modelType,
    ) {}

    public function handle(GenerationService $generationService): void
    {
        $generation = Generation::find($this->generationId);

        if (! $generation instanceof Generation) {
            return;
        }

        // Only proceed if generation is still in progress
        if ($generation->status === 'failed' || $generation->status === 'completed') {
            return;
        }

        $generationService->runSharedLayoutGeneration($generation, $this->blueprint, $this->modelType);
    }
}
