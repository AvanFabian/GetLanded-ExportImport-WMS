<?php

namespace App\Services;

use App\Models\ImportJob;
use App\Models\Product;
use App\Models\Customer;
use App\Models\Supplier;
use Illuminate\Support\Facades\Storage;
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
        $data = Excel::toArray(new \stdClass(), Storage::path($filePath))[0];
        $headers = array_shift($data) ?? [];
        $sample = [];

        foreach (array_slice($data, 0, 5) as $row) {
            $sample[] = array_combine($headers, $row);
        }

        return [
            'headers' => $headers,
            'sample' => $sample,
            'total_rows' => count($data),
        ];
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
                'error_log' => array_merge($job->error_log ?? [], [['error' => $e->getMessage(), 'time' => now()->toISOString()]]),
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
        $data = Excel::toArray(new \stdClass(), Storage::path($job->file_path))[0];
        $headers = array_shift($data);
        
        $records = collect($data)->map(function($row) use ($headers) {
            return array_combine($headers, $row);
        });

        $this->iteratorProcess($records, $job);
    }

    protected function iteratorProcess(iterable $records, ImportJob $job): void
    {
        $method = $this->importers[$job->type] ?? null;
        if (!$method) throw new \Exception("Unknown import type: {$job->type}");

        $batchSize = 100;
        $batchCount = 0;

        DB::beginTransaction();

        foreach ($records as $rowIndex => $record) {
            try {
                $mappedData = $this->mapColumns($record, $job->column_mapping);
                $this->$method($mappedData, $job->company_id);
                $job->incrementProcessed();

                $batchCount++;
                if ($batchCount >= $batchSize) {
                    DB::commit();
                    DB::beginTransaction();
                    $batchCount = 0;
                }
            } catch (\Exception $e) {
                $job->incrementFailed($e->getMessage(), $rowIndex + 2);
            }
        }

        DB::commit();
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

    protected array $categoryCache = [];

    protected function importProducts(array $data, int $companyId): void
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

        Product::withoutGlobalScopes()->updateOrCreate(
            [
                'company_id' => $companyId,
                'code' => $code,
            ],
            [
                'name' => $data['name'],
                'description' => $data['description'] ?? null,
                'unit' => $this->cleanUnit($data['unit'] ?? 'pcs'),
                'category_id' => $this->resolveCategory($data['category'] ?? $data['category_id'] ?? null, $companyId),
                // Exim Fields
                'hs_code' => $data['hs_code'] ?? null,
                'origin_country' => strtoupper($data['origin_country'] ?? ''),
                'purchase_price' => $this->cleanCurrency($data['purchase_price'] ?? 0),
                'selling_price' => $this->cleanCurrency($data['selling_price'] ?? 0),
                'min_stock' => $this->cleanWeight($data['min_stock'] ?? 0),
            ]
        );
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

    protected function importCustomers(array $data, int $companyId): void
    {
        // Require Email or Phone or Name to identify uniqueness
        if (empty($data['name'])) throw new \Exception("Customer Name is required");

        Customer::withoutGlobalScopes()->updateOrCreate(
            [
                'company_id' => $companyId,
                'email' => $data['email'] ?? null,
            ],
            [
                'name' => $data['name'],
                'phone' => $data['phone'] ?? null,
                'address' => $data['address'] ?? null,
            ]
        );
    }

    protected function importSuppliers(array $data, int $companyId): void
    {
        if (empty($data['name'])) throw new \Exception("Supplier Name is required");

        Supplier::withoutGlobalScopes()->updateOrCreate(
            [
                'company_id' => $companyId,
                'email' => $data['email'] ?? null,
            ],
            [
                'name' => $data['name'],
                'phone' => $data['phone'] ?? null,
                'address' => $data['address'] ?? null,
            ]
        );
    }

    protected function importStock(array $data, int $companyId): void
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
