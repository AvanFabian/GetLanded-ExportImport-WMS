<?php

namespace App\Imports;

use App\Models\ImportJob;
use App\Services\ImportService;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithChunkReading;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Events\BeforeSheet;

class ChunkedImport implements ToCollection, WithChunkReading, WithHeadingRow, WithEvents
{
    protected ImportService $importService;
    protected ImportJob $job;
    protected string $importMethod;
    protected int $processedInBatch = 0;
    protected int $failedInBatch = 0;
    protected array $batchErrors = [];

    public function __construct(ImportJob $job, ImportService $importService, string $importMethod)
    {
        $this->job = $job;
        $this->importService = $importService;
        $this->importMethod = $importMethod;
    }

    /**
     * Process each chunk of rows using BULK operations.
     * Called once per chunk — only `chunkSize()` rows are in memory at a time.
     *
     * Performance: Uses bulk upsert (1 query per chunk) instead of
     * per-row updateOrCreate (2 queries per row).
     * 25K rows: 50 queries instead of 50,000.
     */
    public function collection(Collection $rows): void
    {
        $batchMethod = $this->importMethod . 'Batch';

        // Check if a bulk batch method exists (e.g., importProductsBatch)
        $hasBatchMethod = method_exists($this->importService, $batchMethod);

        try {
            DB::beginTransaction();

            if ($hasBatchMethod) {
                // === FAST PATH: Bulk upsert entire chunk in 1 query ===
                $preparedRows = [];
                foreach ($rows as $rowIndex => $row) {
                    try {
                        $rowArray = $row->toArray();
                        $mappedData = $this->job->column_mapping
                            ? $this->importService->mapColumnsPublic($rowArray, $this->job->column_mapping)
                            : $rowArray;
                        $preparedRows[] = $mappedData;
                    } catch (\Exception $e) {
                        $this->failedInBatch++;
                        if (count($this->batchErrors) < 200) {
                            $this->batchErrors[] = [
                                'row' => $rowIndex + 2,
                                'error' => mb_substr($e->getMessage(), 0, 200),
                                'time' => now()->toISOString(),
                            ];
                        }
                    }
                }

                if (!empty($preparedRows)) {
                    try {
                        // Current offset is the number of rows already processed in the job
                        $offset = $this->job->fresh()->processed_rows ?? 0;
                        $result = $this->importService->$batchMethod($preparedRows, $this->job->company_id, $offset);
                        $this->processedInBatch += $result['processed'] ?? count($preparedRows);
                        $this->failedInBatch += $result['failed'] ?? 0;
                        if (!empty($result['errors'])) {
                            $this->batchErrors = array_merge($this->batchErrors, $result['errors']);
                        }
                    } catch (\Exception $e) {
                        // If bulk fails, the entire chunk is marked as failed
                        $this->failedInBatch += count($preparedRows);
                        if (count($this->batchErrors) < 200) {
                            $this->batchErrors[] = [
                                'row' => 'chunk',
                                'error' => mb_substr($e->getMessage(), 0, 200),
                                'time' => now()->toISOString(),
                            ];
                        }
                    }
                }
            } else {
                // === LEGACY PATH: Per-row processing for types without batch method ===
                foreach ($rows as $rowIndex => $row) {
                    try {
                        $rowArray = $row->toArray();
                        $mappedData = $this->job->column_mapping
                            ? $this->importService->mapColumnsPublic($rowArray, $this->job->column_mapping)
                            : $rowArray;
                        $this->importService->{$this->importMethod}($mappedData, $this->job->company_id);
                        $this->processedInBatch++;
                    } catch (\Exception $e) {
                        $this->failedInBatch++;
                        if (count($this->batchErrors) < 200) {
                            $this->batchErrors[] = [
                                'row' => $rowIndex + 2,
                                'error' => mb_substr($e->getMessage(), 0, 200),
                                'time' => now()->toISOString(),
                            ];
                        }
                    }
                }
            }

            DB::commit();
        } catch (\Throwable $e) {
            try { DB::rollBack(); } catch (\Throwable $rb) {}
            Log::error("ChunkedImport chunk failed: {$e->getMessage()}");
        }

        // Flush progress to DB once per chunk (not per row)
        $this->flushProgress();
    }

    /**
     * Flush accumulated counters to the database.
     * Called once per chunk instead of once per row.
     */
    protected function flushProgress(): void
    {
        try {
            if ($this->processedInBatch > 0) {
                $this->job->increment('processed_rows', $this->processedInBatch);
                $this->processedInBatch = 0;
            }

            if ($this->failedInBatch > 0) {
                $this->job->increment('failed_rows', $this->failedInBatch);
                $this->failedInBatch = 0;
            }

            if (!empty($this->batchErrors)) {
                $existingErrors = $this->job->fresh()->errors ?? [];
                $this->job->update([
                    'errors' => array_merge($existingErrors, $this->batchErrors),
                ]);
                $this->batchErrors = [];
            }
        } catch (\Throwable $e) {
            Log::warning("ChunkedImport: Failed to flush progress — {$e->getMessage()}");
        }
    }

    /**
     * Number of rows per chunk.
     * 1000 rows is optimal for upsert (bulk insert is very fast).
     */
    public function chunkSize(): int
    {
        return 1000;
    }

    /**
     * Register event listeners.
     */
    public function registerEvents(): array
    {
        return [
            BeforeSheet::class => function (BeforeSheet $event) {
                Log::info("ChunkedImport: Starting sheet import for ImportJob #{$this->job->id}");
            },
        ];
    }
}
