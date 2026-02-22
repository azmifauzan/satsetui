<?php

namespace App\Jobs;

use App\Models\Generation;
use App\Models\PreviewSession;
use App\Services\WorkspaceService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

/**
 * Async job to set up a preview session.
 *
 * Runs workspace creation, npm install, and dev server boot in the queue
 * so the HTTP request returns immediately. The frontend polls /preview/status.
 */
class SetupPreviewSession implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $timeout = 300; // 5 minutes max

    public int $tries = 1;

    public function __construct(
        public int $sessionId,
        public int $generationId,
    ) {}

    public function handle(WorkspaceService $workspaceService): void
    {
        $session = PreviewSession::find($this->sessionId);
        if (! $session) {
            return;
        }

        $generation = Generation::find($this->generationId);
        if (! $generation) {
            $session->update([
                'status' => PreviewSession::STATUS_ERROR,
                'error_message' => 'Generation not found',
            ]);

            return;
        }

        try {
            $workspaceService->runPreviewSetup($session, $generation);
        } catch (\Throwable $e) {
            $session->update([
                'status' => PreviewSession::STATUS_ERROR,
                'error_message' => $e->getMessage(),
            ]);

            Log::error("SetupPreviewSession job failed for generation {$this->generationId}", [
                'error' => $e->getMessage(),
            ]);
        }
    }
}
