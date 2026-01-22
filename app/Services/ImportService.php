<?php

namespace App\Services;

use App\Models\ImportJob;
use App\Models\Product;
use App\Models\Customer;
use App\Models\Supplier;
use Illuminate\Support\Facades\Storage;
use League\Csv\Reader;
use Illuminate\Support\Facades\DB;

class ImportService
{
    protected array $importers = [
        'products' => 'importProducts',
        'customers' => 'importCustomers',
        'suppliers' => 'importSuppliers',
    ];

    /**
     * Parse CSV file and return headers + sample data
     */
    public function parseFile(string $filePath): array
    {
        $csv = Reader::createFromPath(Storage::path($filePath), 'r');
        $csv->setHeaderOffset(0);

        $headers = $csv->getHeader();
        $sample = [];
        
        foreach ($csv->getRecords() as $index => $record) {
            if ($index >= 5) break;
            $sample[] = $record;
        }

        return [
            'headers' => $headers,
            'sample' => $sample,
            'total_rows' => $csv->count(),
        ];
    }

    /**
     * Process import job with column mapping
     */
    public function process(ImportJob $job): void
    {
        $job->update(['status' => ImportJob::STATUS_PROCESSING]);

        try {
            $csv = Reader::createFromPath(Storage::path($job->file_path), 'r');
            $csv->setHeaderOffset(0);

            $method = $this->importers[$job->type] ?? null;
            
            if (!$method) {
                throw new \Exception("Unknown import type: {$job->type}");
            }

            DB::beginTransaction();

            foreach ($csv->getRecords() as $rowIndex => $record) {
                try {
                    $mappedData = $this->mapColumns($record, $job->column_mapping);
                    $this->$method($mappedData, $job->company_id);
                    $job->incrementProcessed();
                } catch (\Exception $e) {
                    $job->incrementFailed($e->getMessage(), $rowIndex + 2);
                }
            }

            DB::commit();
            $job->complete();

        } catch (\Exception $e) {
            DB::rollBack();
            $job->update([
                'status' => ImportJob::STATUS_FAILED,
                'error_log' => [['error' => $e->getMessage(), 'time' => now()->toISOString()]],
            ]);
        }
    }

    /**
     * Map CSV columns to target fields using mapping config
     */
    protected function mapColumns(array $record, array $mapping): array
    {
        $result = [];
        
        foreach ($mapping as $targetField => $sourceColumn) {
            if ($sourceColumn && isset($record[$sourceColumn])) {
                $result[$targetField] = $record[$sourceColumn];
            }
        }

        return $result;
    }

    protected function importProducts(array $data, int $companyId): void
    {
        Product::updateOrCreate(
            [
                'company_id' => $companyId,
                'sku' => $data['sku'] ?? null,
            ],
            [
                'name' => $data['name'] ?? 'Unknown',
                'description' => $data['description'] ?? null,
                'unit' => $data['unit'] ?? 'pcs',
                'category_id' => $data['category_id'] ?? null,
            ]
        );
    }

    protected function importCustomers(array $data, int $companyId): void
    {
        Customer::updateOrCreate(
            [
                'company_id' => $companyId,
                'email' => $data['email'] ?? null,
            ],
            [
                'name' => $data['name'] ?? 'Unknown',
                'phone' => $data['phone'] ?? null,
                'address' => $data['address'] ?? null,
            ]
        );
    }

    protected function importSuppliers(array $data, int $companyId): void
    {
        Supplier::updateOrCreate(
            [
                'company_id' => $companyId,
                'email' => $data['email'] ?? null,
            ],
            [
                'name' => $data['name'] ?? 'Unknown',
                'phone' => $data['phone'] ?? null,
                'address' => $data['address'] ?? null,
            ]
        );
    }

    /**
     * Get suggested mappings based on header names
     */
    public function suggestMappings(array $headers, string $type): array
    {
        $suggestions = [];
        
        $targetFields = match($type) {
            'products' => ['sku', 'name', 'description', 'unit', 'category_id'],
            'customers' => ['name', 'email', 'phone', 'address'],
            'suppliers' => ['name', 'email', 'phone', 'address'],
            default => [],
        };

        foreach ($targetFields as $field) {
            $suggestions[$field] = $this->findBestMatch($field, $headers);
        }

        return $suggestions;
    }

    protected function findBestMatch(string $field, array $headers): ?string
    {
        $fieldLower = strtolower($field);
        
        foreach ($headers as $header) {
            $headerLower = strtolower($header);
            
            // Exact match
            if ($headerLower === $fieldLower) {
                return $header;
            }
            
            // Partial match
            if (str_contains($headerLower, $fieldLower) || str_contains($fieldLower, $headerLower)) {
                return $header;
            }
        }

        return null;
    }
}
