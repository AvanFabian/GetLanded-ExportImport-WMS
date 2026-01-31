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
        'stock' => 'importStock',
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
                $result[$targetField] = trim($record[$sourceColumn]);
            }
        }

        return $result;
    }

    protected function importProducts(array $data, int $companyId): void
    {
        // Require Name
        if (empty($data['name'])) {
            throw new \Exception("Product Name is required");
        }

        // Generate Code/SKU if missing
        $code = $data['code'] ?? $data['sku'] ?? 'PRD-' . strtoupper(uniqid());

        Product::updateOrCreate(
            [
                'company_id' => $companyId,
                'code' => $code,
            ],
            [
                'name' => $data['name'],
                'description' => $data['description'] ?? null,
                'unit' => $data['unit'] ?? 'pcs',
                // Exim Fields
                'hs_code' => $data['hs_code'] ?? null,
                'origin_country' => $data['origin_country'] ?? null,
                'purchase_price' => $data['purchase_price'] ?? 0,
                'selling_price' => $data['selling_price'] ?? 0,
                'min_stock' => $data['min_stock'] ?? 0,
            ]
        );
    }

    protected function importCustomers(array $data, int $companyId): void
    {
        // Require Email or Phone or Name to identify uniqueness
        if (empty($data['name'])) throw new \Exception("Customer Name is required");

        Customer::updateOrCreate(
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

        Supplier::updateOrCreate(
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

    protected function findBestMatch(array $aliases, array $headers): ?string
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
            
            // 2. Contains Match (e.g., "Net Weight (kg)" matches "Weight")
            foreach ($originalHeaders as $normHeader => $original) {
                if (str_contains($normHeader, $normalizedAlias)) {
                    return $original;
                }
            }
        }

        return null;
    }
}
