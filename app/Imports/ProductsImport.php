<?php

namespace App\Imports;

use App\Models\Product;
use App\Models\Category;
use App\Models\Warehouse;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithValidation;
use Maatwebsite\Excel\Concerns\WithChunkReading;
use Maatwebsite\Excel\Concerns\WithBatchInserts;
use Illuminate\Validation\Rule;
use Illuminate\Support\Str;

class ProductsImport implements ToModel, WithHeadingRow, WithValidation, WithChunkReading, WithBatchInserts
{
    private $categories;
    private $defaultWarehouseId;
    private int $companyId;

    public function __construct(?int $companyId = null)
    {
        // Accept explicit company_id for queue context, fallback to auth for HTTP context
        $this->companyId = $companyId ?? auth()->user()?->company_id ?? 0;

        // Cache categories scoped to this tenant
        $this->categories = Category::withoutGlobalScopes()
            ->where('company_id', $this->companyId)
            ->pluck('id', 'name')
            ->toArray();

        // Default to the first active warehouse for this tenant
        $this->defaultWarehouseId = Warehouse::withoutGlobalScopes()
            ->where('company_id', $this->companyId)
            ->first()?->id;
    }

    public function model(array $row)
    {
        // Find or create category — scoped to tenant
        $categoryName = trim($row['category_name'] ?? '');
        if (!isset($this->categories[$categoryName]) && !empty($categoryName)) {
            $category = Category::withoutGlobalScopes()->firstOrCreate(
                ['name' => $categoryName, 'company_id' => $this->companyId],
                ['type' => 'raw_material']
            );
            $this->categories[$categoryName] = $category->id;
        }

        // Build update array DYNAMICALLY — only include fields present in the XLSX.
        // This prevents wiping data when users upload partial files (e.g., only SKU + price).
        $updateData = [
            'name' => trim($row['name']),
        ];

        // Optional fields — only update if the column exists in the file
        if (array_key_exists('description', $row))    $updateData['description'] = $row['description'];
        if (array_key_exists('unit', $row))            $updateData['unit'] = strtolower(trim($row['unit']));
        if (array_key_exists('purchase_price', $row))  $updateData['purchase_price'] = $this->cleanNumeric($row['purchase_price']);
        if (array_key_exists('selling_price', $row))   $updateData['selling_price'] = $this->cleanNumeric($row['selling_price']);
        if (array_key_exists('min_stock', $row))       $updateData['min_stock'] = (int) $row['min_stock'];
        if (array_key_exists('hs_code', $row))         $updateData['hs_code'] = trim($row['hs_code'] ?? '');
        if (array_key_exists('origin_country', $row))  $updateData['origin_country'] = strtoupper(trim($row['origin_country'] ?? ''));
        if (array_key_exists('weight_unit', $row))     $updateData['weight_unit'] = strtoupper(trim($row['weight_unit'] ?? 'KG'));

        // Category — only update if column present
        if (!empty($categoryName)) {
            $updateData['category_id'] = $this->categories[$categoryName] ?? null;
        }

        $updateData['status'] = true;

        // Use updateOrCreate to allow existing SKUs to be updated instead of blocking
        $product = Product::withoutGlobalScopes()->updateOrCreate(
            [
                'company_id' => $this->companyId,
                'code' => trim($row['sku']),
            ],
            $updateData
        );

        return $product;
    }

    public function rules(): array
    {
        return [
            'name' => 'required|string',
            'sku' => 'required|string',
            'category_name' => 'nullable|string',
            'unit' => 'nullable|string',
            'purchase_price' => 'nullable',
            'selling_price' => 'nullable',
        ];
    }

    /**
     * Clean numeric values — handles IDR (Rp 1.500.000) and USD ($1,500.00) formats.
     */
    private function cleanNumeric($value): float
    {
        if (is_numeric($value)) return (float) $value;
        if (empty($value)) return 0;

        $upper = strtoupper((string) $value);

        // IDR format: dots = thousands, comma = decimal (e.g., "Rp 1.500.000,50")
        if (str_contains($upper, 'RP') || str_contains($upper, 'IDR')) {
            $cleaned = str_replace('.', '', $value);   // Remove dots (thousands)
            $cleaned = str_replace(',', '.', $cleaned); // Comma → decimal
            return (float) preg_replace('/[^0-9.\-]/', '', $cleaned);
        }

        // Default/USD: commas = thousands, dot = decimal (e.g., "$1,500.00")
        return (float) preg_replace('/[^0-9.\-]/', '', str_replace(',', '', $value));
    }

    public function chunkSize(): int
    {
        return 500;
    }

    public function batchSize(): int
    {
        return 500;
    }
}
