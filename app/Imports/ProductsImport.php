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
        $categoryName = $row['category_name'] ?? '';
        if (!isset($this->categories[$categoryName]) && !empty($categoryName)) {
            $category = Category::withoutGlobalScopes()->firstOrCreate(
                ['name' => $categoryName, 'company_id' => $this->companyId],
                ['type' => 'raw_material']
            );
            $this->categories[$categoryName] = $category->id;
        }

        // Explicitly set company_id — WithBatchInserts bypasses Eloquent events,
        // so the BelongsToTenant boot trait's creating() hook never fires.
        
        // Use updateOrCreate to allow existing SKUs to be updated instead of blocking
        $product = Product::withoutGlobalScopes()->updateOrCreate(
            [
                'company_id' => $this->companyId,
                'code' => $row['sku'],
            ],
            [
                'name' => $row['name'],
                'description' => $row['description'] ?? null,
                'category_id' => $this->categories[$categoryName] ?? null,
                'unit' => $row['unit'],
                'purchase_price' => $row['purchase_price'],
                'selling_price' => $row['selling_price'],
                'min_stock' => $row['min_stock'] ?? 0,
    
                // Exim Fields
                'hs_code' => $row['hs_code'] ?? null,
                'origin_country' => $row['origin_country'] ?? null,
                'weight_unit' => $row['weight_unit'] ?? 'KG',
    
                'status' => true,
            ]
        );

        return $product;
    }

    public function rules(): array
    {
        return [
            'name' => 'required|string',
            // Allow SKU to exist if it belongs to this company (for updates)
            // But we still need to validate it's unique if we were strictly creating.
            // Since we switched to updateOrCreate, we can technically relax this check,
            // OR use a rule that ignores the current ID (which we don't know).
            // SAFEST OPTION: Remove the unique check here and let updateOrCreate handle it.
            // If the user INTENDS to create new, they might typo an existing SKU and overwrite it.
            // But for bulk import, "Upsert" is usually the desired behavior.
            'sku' => 'required|string', 
            'category_name' => 'required|string',
            'unit' => 'required|string',
            'purchase_price' => 'required|numeric|min:0',
            'selling_price' => 'required|numeric|min:0',
        ];
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
