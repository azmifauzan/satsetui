<?php

namespace App\Jobs;

use App\Models\Generation;
use App\Services\ScaffoldGeneratorService;
use App\Services\WorkspaceService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class PrepareWorkspaceDependencies implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $timeout = 900;

    public int $tries = 1;

    public function __construct(
        public int $generationId,
    ) {}

    public function handle(WorkspaceService $workspaceService, ScaffoldGeneratorService $scaffoldGenerator): void
    {
        $generation = Generation::find($this->generationId);
        if (! $generation) {
            return;
        }

        $outputFormat = $generation->project?->blueprint['outputFormat'] ?? 'html-css';
        if (! $scaffoldGenerator->requiresScaffold($outputFormat)) {
            return;
        }

        $prewarmLog = storage_path("logs/preview-prewarm-{$generation->id}.log");

        try {
            file_put_contents($prewarmLog, '['.now()->format('H:i:s')."] Starting parallel dependency prewarm\n", FILE_APPEND);

            $workspaceDir = $workspaceService->createWorkspace($generation);
            file_put_contents($prewarmLog, '['.now()->format('H:i:s')."] Workspace ready: {$workspaceDir}\n", FILE_APPEND);

            $result = $workspaceService->installDependencies($workspaceDir, $prewarmLog);

            if (! ($result['success'] ?? false)) {
                $error = (string) ($result['error'] ?? 'npm install failed');
                file_put_contents($prewarmLog, '['.now()->format('H:i:s')."] ERROR: {$error}\n", FILE_APPEND);
                Log::warning('Parallel dependency prewarm failed', [
                    'generation_id' => $generation->id,
                    'error' => $error,
                ]);

                return;
            }

            file_put_contents($prewarmLog, '['.now()->format('H:i:s')."] Dependencies installed successfully\n", FILE_APPEND);
            Log::info('Parallel dependency prewarm completed', [
                'generation_id' => $generation->id,
                'workspace' => $workspaceDir,
            ]);
        } catch (\Throwable $exception) {
            file_put_contents($prewarmLog, '['.now()->format('H:i:s').'] ERROR: '.$exception->getMessage()."\n", FILE_APPEND);
            Log::error('Parallel dependency prewarm exception', [
                'generation_id' => $generation->id,
                'error' => $exception->getMessage(),
            ]);
        }
    }
}
