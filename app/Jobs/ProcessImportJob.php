<?php

namespace App\Jobs;

use App\Models\ImportJob;
use App\Services\ImportService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class ProcessImportJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * The number of seconds the job can run before timing out.
     */
    public int $timeout = 600;

    /**
     * The number of times the job may be attempted.
     */
    public int $tries = 3;

    /**
     * The number of seconds to wait before retrying.
     */
    public int $backoff = 30;

    public function __construct(
        public int $importJobId,
        public int $companyId
    ) {}

    /**
     * Execute the job.
     */
    public function handle(ImportService $importService): void
    {
        $importJob = ImportJob::withoutGlobalScopes()->find($this->importJobId);

        if (!$importJob) {
            Log::error("ProcessImportJob: ImportJob #{$this->importJobId} not found.");
            return;
        }

        // Explicitly pass company_id so ImportService can use it
        // without relying on auth() (which is unavailable in queue context)
        $importJob->company_id = $this->companyId;

        try {
            $importService->process($importJob);
        } catch (\Throwable $e) {
            Log::error("ProcessImportJob failed: {$e->getMessage()}", [
                'import_job_id' => $this->importJobId,
                'company_id' => $this->companyId,
            ]);
            throw $e; // Re-throw so Laravel marks the job as failed and retries
        }
    }

    /**
     * Handle a job failure.
     */
    public function failed(?\Throwable $exception): void
    {
        $importJob = ImportJob::withoutGlobalScopes()->find($this->importJobId);

        if ($importJob) {
            $importJob->update([
                'status' => ImportJob::STATUS_FAILED,
                'errors' => array_merge($importJob->errors ?? [], [[
                    'error' => $exception?->getMessage() ?? 'Unknown error',
                    'time' => now()->toISOString(),
                    'context' => 'Queue job permanently failed after all retries.',
                ]]),
            ]);
        }

        Log::error("ProcessImportJob permanently failed for ImportJob #{$this->importJobId}", [
            'error' => $exception?->getMessage(),
        ]);
    }
}
