<?php

namespace App\Services;

use App\Models\ImportJob;
use App\Models\Product;
use App\Models\Customer;
use App\Models\Supplier;
use App\Imports\ChunkedImport;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;
use League\Csv\Reader;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Facades\Excel;

class ImportService
{
    protected array $importers = [
        'products' => 'importProducts',
        'customers' => 'importCustomers',
        'suppliers' => 'importSuppliers',
        'stock' => 'importStock',
    ];

    /**
     * Parse CSV or Excel file and return headers + sample data
     */
    public function parseFile(string $filePath): array
    {
        $extension = pathinfo($filePath, PATHINFO_EXTENSION);

        if ($extension === 'xlsx') {
            return $this->parseExcel($filePath);
        }

        return $this->parseCsv($filePath);
    }

    protected function parseCsv(string $filePath): array
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

    protected function parseExcel(string $filePath): array
    {
        // Only load first 10 rows for preview — avoids OOM on large files
        $fullPath = Storage::path($filePath);
        $reader = \PhpOffice\PhpSpreadsheet\IOFactory::createReaderForFile($fullPath);
        $reader->setReadDataOnly(true);

        // Use a filter to only read first 10 rows for preview
        $filter = new class implements \PhpOffice\PhpSpreadsheet\Reader\IReadFilter {
            public function readCell($columnAddress, $row, $worksheetName = ''): bool
            {
                return $row <= 10;
            }
        };
        $reader->setReadFilter($filter);
        $spreadsheet = $reader->load($fullPath);
        $sheetData = $spreadsheet->getActiveSheet()->toArray();
        $spreadsheet->disconnectWorksheets();
        unset($spreadsheet);

        $headers = array_shift($sheetData) ?? [];
        // Filter out completely null headers
        $headers = array_map(fn($h) => $h ?? '', $headers);

        $sample = [];
        foreach (array_slice($sheetData, 0, 5) as $row) {
            if (count($row) === count($headers)) {
                $sample[] = array_combine($headers, $row);
            }
        }

        // Count total rows using a lightweight counter (no full load)
        $totalRows = $this->countExcelRows($fullPath);

        return [
            'headers' => $headers,
            'sample' => $sample,
            'total_rows' => $totalRows,
        ];
    }

    /**
     * Count rows in an Excel file without loading all data into memory.
     */
    protected function countExcelRows(string $fullPath): int
    {
        try {
            $reader = \PhpOffice\PhpSpreadsheet\IOFactory::createReaderForFile($fullPath);
            $reader->setReadDataOnly(true);
            /** @var \PhpOffice\PhpSpreadsheet\Reader\Xlsx|\PhpOffice\PhpSpreadsheet\Reader\Csv $reader */
            $info = $reader->listWorksheetInfo($fullPath);
            return max(0, ($info[0]['totalRows'] ?? 1) - 1); // Subtract header row
        } catch (\Throwable $e) {
            Log::warning("Could not count Excel rows: {$e->getMessage()}");
            return 0;
        }
    }

    /**
     * Process import job with column mapping
     */
    public function process(ImportJob $job): void
    {
        $job->update(['status' => ImportJob::STATUS_PROCESSING]);

        try {
            $extension = pathinfo($job->file_path, PATHINFO_EXTENSION);
            
            if ($extension === 'xlsx') {
                $this->processExcel($job);
            } else {
                $this->processCsv($job);
            }

            $job->complete();
        } catch (\Exception $e) {
            $job->update([
                'status' => ImportJob::STATUS_FAILED, 
                'errors' => array_merge($job->errors ?? [], [['error' => $e->getMessage(), 'time' => now()->toISOString()]]),
            ]);
        }
    }

    protected function processCsv(ImportJob $job): void
    {
        $csv = Reader::createFromPath(Storage::path($job->file_path), 'r');
        $csv->setHeaderOffset(0);
        $this->iteratorProcess($csv->getRecords(), $job);
    }

    protected function processExcel(ImportJob $job): void
    {
        // Determine which import method to use
        $importMethod = $this->importers[$job->type] ?? null;
        if (!$importMethod) {
            throw new \Exception("Unknown import type: {$job->type}");
        }

        // Use ChunkedImport for memory-efficient processing
        // Only ~500 rows are in memory at any time
        $chunkedImport = new ChunkedImport($job, $this, $importMethod);

        Excel::import(
            $chunkedImport,
            Storage::path($job->file_path)
        );
    }

    protected function iteratorProcess(iterable $records, ImportJob $job): void
    {
        $method = $this->importers[$job->type] ?? null;
        if (!$method) throw new \Exception("Unknown import type: {$job->type}");

        $batchSize = 100;
        $batchCount = 0;
        $inTransaction = false;

        try {
            DB::beginTransaction();
            $inTransaction = true;

            foreach ($records as $rowIndex => $record) {
                try {
                    $mappedData = $job->column_mapping 
                        ? $this->mapColumns($record, $job->column_mapping)
                        : $record;
                    $this->$method($mappedData, $job->company_id);
                    $job->incrementProcessed();

                    $batchCount++;
                    if ($batchCount >= $batchSize) {
                        DB::commit();
                        $inTransaction = false;
                        DB::beginTransaction();
                        $inTransaction = true;
                        $batchCount = 0;
                    }
                } catch (\Exception $e) {
                    // Log per-row errors without crashing the entire import
                    try {
                        $job->incrementFailed($e->getMessage(), $rowIndex + 2);
                    } catch (\Throwable $logError) {
                        // If even error logging fails, don't crash the batch
                        \Illuminate\Support\Facades\Log::warning("Import row error logging failed", [
                            'row' => $rowIndex + 2,
                            'original_error' => $e->getMessage(),
                            'log_error' => $logError->getMessage(),
                        ]);
                    }
                }
            }

            DB::commit();
            $inTransaction = false;
        } catch (\Throwable $e) {
            // Guarantee rollback so we never leave open transactions (which cause DB locks)
            if ($inTransaction) {
                try { DB::rollBack(); } catch (\Throwable $rbError) { /* already failing */ }
            }
            throw $e;
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
                $result[$targetField] = trim($record[$sourceColumn]);
            }
        }

        return $result;
    }

    /**
     * Public wrapper for mapColumns — used by ChunkedImport.
     */
    public function mapColumnsPublic(array $record, array $mapping): array
    {
        return $this->mapColumns($record, $mapping);
    }

    protected array $categoryCache = [];

    public function importProducts(array $data, int $companyId): void
    {
        // Preload cache if empty
        if (empty($this->categoryCache)) {
            $this->categoryCache = \App\Models\Category::withoutGlobalScopes()
                ->where('company_id', $companyId)
                ->pluck('id', 'name')
                ->mapWithKeys(fn($id, $name) => [strtolower($name) => $id])
                ->toArray();
        }

        // Require Name
        if (empty($data['name'])) {
            throw new \Exception("Product Name is required");
        }

        // Generate Code/SKU if missing
        $code = $data['code'] ?? $data['sku'] ?? 'PRD-' . strtoupper(uniqid());

        // Build update array DYNAMICALLY — only include fields present in the data.
        // Prevents wiping data when users import partial spreadsheets.
        $updateData = [
            'name' => trim($data['name']),
        ];

        if (isset($data['description']))    $updateData['description'] = $data['description'];
        if (isset($data['unit']))           $updateData['unit'] = $this->cleanUnit($data['unit']);
        if (isset($data['category']) || isset($data['category_id'])) {
            $updateData['category_id'] = $this->resolveCategory($data['category'] ?? $data['category_id'] ?? null, $companyId);
        }
        if (isset($data['hs_code']))        $updateData['hs_code'] = trim($data['hs_code']);
        if (isset($data['origin_country'])) $updateData['origin_country'] = strtoupper(trim($data['origin_country']));
        if (isset($data['purchase_price'])) $updateData['purchase_price'] = $this->cleanCurrency($data['purchase_price']);
        if (isset($data['selling_price']))  $updateData['selling_price'] = $this->cleanCurrency($data['selling_price']);
        if (isset($data['min_stock']))      $updateData['min_stock'] = $this->cleanWeight($data['min_stock']);

        Product::withoutGlobalScopes()->updateOrCreate(
            [
                'company_id' => $companyId,
                'code' => trim($code),
            ],
            $updateData
        );
    }

    /**
     * BULK import products — processes entire chunk in 1 upsert query.
     *
     * Performance: 1 query per ~1000 rows instead of 2 queries per row.
     * 25K rows: ~25 queries instead of ~50,000 queries.
     *
     * @param array $rows Array of mapped row data
     * @param int $companyId
     * @return array{processed: int, failed: int, errors: array}
     */
    public function importProductsBatch(array $rows, int $companyId): array
    {
        // Preload category cache once for the entire batch
        if (empty($this->categoryCache)) {
            $this->categoryCache = \App\Models\Category::withoutGlobalScopes()
                ->where('company_id', $companyId)
                ->pluck('id', 'name')
                ->mapWithKeys(fn($id, $name) => [strtolower($name) => $id])
                ->toArray();
        }

        $upsertRows = [];
        $failed = 0;
        $errors = [];
        $now = now();

        foreach ($rows as $index => $data) {
            try {
                if (empty($data['name'])) {
                    throw new \Exception("Product Name is required");
                }

                $code = $data['code'] ?? $data['sku'] ?? 'AUT-' . str_pad($index + 1, 6, '0', STR_PAD_LEFT);

                $row = [
                    'company_id' => $companyId,
                    'code' => trim($code),
                    'name' => trim($data['name']),
                    'updated_at' => $now,
                    'created_at' => $now,
                ];

                // Only include fields that exist in the data
                if (isset($data['description']))    $row['description'] = $data['description'];
                if (isset($data['unit']))           $row['unit'] = $this->cleanUnit($data['unit']);
                
                // Flexible Category Mapping
                $catInput = $data['category'] ?? $data['category_id'] ?? $data['category_name'] ?? null;
                if (!empty($catInput)) {
                    $row['category_id'] = $this->resolveCategory($catInput, $companyId);
                }

                if (isset($data['hs_code']))        $row['hs_code'] = trim($data['hs_code']);
                if (isset($data['origin_country'])) $row['origin_country'] = strtoupper(trim($data['origin_country']));
                if (isset($data['purchase_price'])) $row['purchase_price'] = $this->cleanCurrency($data['purchase_price']);
                if (isset($data['selling_price']))  $row['selling_price'] = $this->cleanCurrency($data['selling_price']);
                if (isset($data['min_stock']))      $row['min_stock'] = $this->cleanWeight($data['min_stock']);
                
                // Weight & Dimensions
                if (isset($data['weight_value']))   $row['net_weight'] = $this->cleanWeight($data['weight_value']);
                if (isset($data['net_weight']))     $row['net_weight'] = $this->cleanWeight($data['net_weight']);
                if (isset($data['weight_unit']))    $row['weight_unit'] = $this->cleanUnit($data['weight_unit']);
                
                if (isset($data['cbm_volume']))     $row['cbm_volume'] = $this->cleanWeight($data['cbm_volume']); // Clean number
                if (isset($data['dimension_unit'])) $row['dimension_unit'] = $this->cleanUnit($data['dimension_unit']);

                $upsertRows[] = $row;
            } catch (\Exception $e) {
                $failed++;
                if (count($errors) < 200) {
                    $errors[] = [
                        'row' => $index + 2,
                        'error' => mb_substr($e->getMessage(), 0, 200),
                        'time' => $now->toISOString(),
                    ];
                }
            }
        }

        // Bulk upsert: 1 query for the entire chunk
        // Match on company_id + code (composite unique key)
        // Update all other columns on conflict
        if (!empty($upsertRows)) {
            // Determine which columns to update on conflict
            // Use only columns that appear in at least one row
            $allKeys = [];
            foreach ($upsertRows as $r) {
                $allKeys = array_merge($allKeys, array_keys($r));
            }
            $updateColumns = array_unique(array_diff($allKeys, ['company_id', 'code', 'created_at']));

            // Normalize: ensure all rows have the same keys (null for missing)
            foreach ($upsertRows as &$r) {
                foreach ($updateColumns as $col) {
                    if (!array_key_exists($col, $r)) {
                        // Don't include missing columns — let DB keep existing value
                        // upsert needs consistent columns, so set to existing via raw
                    }
                }
            }
            unset($r);

            Product::withoutGlobalScopes()->upsert(
                $upsertRows,
                ['company_id', 'code'],                    // unique keys
                array_values($updateColumns)               // columns to update on conflict
            );
        }

        return [
            'processed' => count($upsertRows),
            'failed' => $failed,
            'errors' => $errors,
        ];
    }

    public function resolveCategory($value, $companyId)
    {
        if (empty($value)) return null;
        if (is_numeric($value)) return $value;

        $normalized = strtolower(trim($value));

        // Check Cache
        if (isset($this->categoryCache[$normalized])) {
            return $this->categoryCache[$normalized];
        }

        // Create & Cache
        $category = \App\Models\Category::withoutGlobalScopes()->firstOrCreate(
            ['company_id' => $companyId, 'name' => trim($value)],
            ['description' => 'Auto-created from Import']
        );
        
        $this->categoryCache[$normalized] = $category->id;
        return $category->id;
    }

    public function cleanUnit($value)
    {
        $v = strtolower(trim($value));
        if (in_array($v, ['pcs', 'pieces', 'piece', 'buah', 'unit', 'units'])) return 'pcs';
        if (in_array($v, ['kg', 'kgs', 'kilogram', 'kilograms'])) return 'kg';
        if (in_array($v, ['m', 'meter', 'meters'])) return 'm';
        return $value;
    }

    public function cleanWeight($value)
    {
        // Detect Unit
        $lower = strtolower((string)$value);
        $number = (float) preg_replace('/[^0-9.]/', '', $lower);
        
        if (str_contains($lower, 'lb') || str_contains($lower, 'pound')) {
            return $number * 0.453592; // Convert lbs to kg
        }
        if (str_contains($lower, 'oz') || str_contains($lower, 'ounce')) {
            return $number * 0.0283495; // Convert oz to kg
        }
        
        return $number;
    }

    public function cleanCurrency($value)
    {
        if (is_numeric($value)) return (float)$value;
        
        $msg = strtoupper($value);
        // IDR/Rp specific logic: Dots are thousands, Commas are decimals
        if (str_contains($msg, 'RP') || str_contains($msg, 'IDR')) {
            // Remove dots (thousands), replace comma with dot (decimal)
            return (float) preg_replace('/[^0-9.-]/', '', str_replace(',', '.', str_replace('.', '', $value)));
        }

        // Default/USD logic: Commas are thousands, Dots are decimals
        return (float) preg_replace('/[^0-9.-]/', '', str_replace(',', '', $value));
    }

    protected function cleanNumber($value)
    {
        // Deprecated in favor of specific cleaners, currently alias to weight for simple numbers
        return $this->cleanWeight($value);
    }

    public function importCustomers(array $data, int $companyId): void
    {
        if (empty($data['name'])) throw new \Exception("Customer Name is required");

        // Use email as unique key if available, otherwise fall back to name
        $uniqueKey = !empty($data['email'])
            ? ['company_id' => $companyId, 'email' => $data['email']]
            : ['company_id' => $companyId, 'name' => trim($data['name'])];

        Customer::withoutGlobalScopes()->updateOrCreate(
            $uniqueKey,
            [
                'name' => trim($data['name']),
                'phone' => $data['phone'] ?? null,
                'address' => $data['address'] ?? null,
            ]
        );
    }

    public function importSuppliers(array $data, int $companyId): void
    {
        if (empty($data['name'])) throw new \Exception("Supplier Name is required");

        // Use email as unique key if available, otherwise fall back to name
        $uniqueKey = !empty($data['email'])
            ? ['company_id' => $companyId, 'email' => $data['email']]
            : ['company_id' => $companyId, 'name' => trim($data['name'])];

        Supplier::withoutGlobalScopes()->updateOrCreate(
            $uniqueKey,
            [
                'name' => trim($data['name']),
                'phone' => $data['phone'] ?? null,
                'address' => $data['address'] ?? null,
            ]
        );
    }

    public function importStock(array $data, int $companyId): void
    {
        // To be implemented: Batch import logic
        // This requires finding the product and creating a StockIn transaction
    }

    /**
     * Get suggested mappings based on header names and smart aliases
     */
    public function suggestMappings(array $headers, string $type): array
    {
        $suggestions = [];
        $aliases = $this->getAliases($type);

        foreach ($aliases as $targetField => $possibleNames) {
            $suggestions[$targetField] = $this->findBestMatch($possibleNames, $headers);
        }

        return $suggestions;
    }

    /**
     * Define Industry-Standard Aliases for "Smart Matching"
     */
    public function getAliases(string $type): array
    {
        $common = [
            'name' => ['name', 'product name', 'item name', 'description', 'customer name', 'supplier'],
            'email' => ['email', 'mail', 'e-mail', 'contact email'],
            'phone' => ['phone', 'mobile', 'tel', 'telp', 'whatsapp', 'wa', 'contact'],
            'address' => ['address', 'addr', 'location', 'city', 'street'],
        ];

        $products = [
            'code' => ['code', 'sku', 'product code', 'item code', 'part number', 'p/n', 'id'],
            'unit' => ['unit', 'uom', 'measure', 'satuan'],
            'purchase_price' => ['purchase price', 'cost', 'buy price', 'hpp', 'modal', 'cogs'],
            'selling_price' => ['selling price', 'price', 'sell price', 'rp', 'harga', 'retail price'],
            'min_stock' => ['min stock', 'minimum', 'safety stock', 'alert'],
            // Exim Fields
            'hs_code' => ['hs code', 'hscode', 'hs', 'commodity code', 'tariff code', 'pos tarif', 'harmonized'],
            'origin_country' => ['origin', 'country', 'coo', 'made in', 'country of origin'],
            // Enhanced Fields
            'category_name' => ['category name', 'category', 'kategori', 'group'],
            'weight_value' => ['weight value', 'weight', 'net weight', 'berat', 'gross weight'],
            'weight_unit' => ['weight unit', 'weight uom', 'satuan berat'],
            'cbm_volume' => ['cbm volume', 'volume', 'cbm', 'vol', 'm3'],
            'dimension_unit' => ['dimension unit', 'dim unit', 'uom dim'],
        ];

        return match($type) {
            'products' => array_merge($products, ['name' => $common['name']]),
            'customers' => $common,
            'suppliers' => $common,
            'stock' => array_merge($products, [
                'qty' => ['qty', 'quantity', 'stock', 'count', 'pcs', 'pieces', 'amount'],
                'batch' => ['batch', 'lot', 'serial'],
                'expiry' => ['expiry', 'exp', 'expiration', 'best before'],
            ]),
            default => [],
        };
    }

    public function findBestMatch(array $aliases, array $headers): ?string
    {
        // Pre-process headers 
        $normalizedHeaders = array_map(fn($h) => strtolower(trim(preg_replace('/[^a-zA-Z0-9]/', '', $h))), $headers);
        $originalHeaders = array_combine($normalizedHeaders, $headers);

        foreach ($aliases as $alias) {
            $normalizedAlias = strtolower(trim(preg_replace('/[^a-zA-Z0-9]/', '', $alias)));
            
            // 1. Exact Match (Normalized)
            if (isset($originalHeaders[$normalizedAlias])) {
                return $originalHeaders[$normalizedAlias];
            }
            
            // 2. Strict Word Boundary Match (Regex)
            // Prevents "filename" matching "name". Only matches "Product Name", "Name (First)", etc.
            foreach ($originalHeaders as $normHeader => $original) {
                // Check original header for word interaction
                if (preg_match("/\b" . preg_quote($alias, '/') . "\b/i", $original)) {
                    return $original;
                }
            }
        }

        return null;
    }
}
