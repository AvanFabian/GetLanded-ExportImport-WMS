<?php

namespace App\Imports;

use App\Models\Product;
use App\Models\Category;
use App\Models\Warehouse;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithValidation;
use Maatwebsite\Excel\Concerns\WithChunkReading;
use Illuminate\Validation\Rule;
use Illuminate\Support\Str;

class ProductsImport implements ToModel, WithHeadingRow, WithValidation, WithChunkReading
{
    private $categories;
    private $defaultWarehouseId;

    public function __construct()
    {
        // Cache categories to avoid N+1 queries
        $this->categories = Category::pluck('id', 'name')->toArray();
        // Default to the first active warehouse if none specified
        $this->defaultWarehouseId = Warehouse::first()->id ?? null;
    }

    public function model(array $row)
    {
        // Find or create category
        $categoryId = $this->categories[$row['category_name']] ?? null;
        if (!$categoryId && !empty($row['category_name'])) {
            $category = Category::firstOrCreate(
                ['name' => $row['category_name']],
                ['type' => 'raw_material']
            );
            $this->categories[$row['category_name']] = $category->id;
            $categoryId = $category->id;
        }

        // Create Product immediately to get ID
        $product = Product::create([
            'name' => $row['name'],
            'code' => $row['sku'],
            'description' => $row['description'] ?? null,
            'category_id' => $categoryId,
            'unit' => $row['unit'],
            'purchase_price' => $row['purchase_price'],
            'selling_price' => $row['selling_price'],
            'min_stock' => $row['min_stock'] ?? 0,
            
            // Real World Import Fields
            'hs_code' => $row['hs_code'] ?? null,
            'origin_country' => $row['origin_country'] ?? null,
            'weight_unit' => $row['weight_unit'] ?? 'KG',
            
            'status' => true,
        ]);

        // Attach to default warehouse with 0 stock
        if ($this->defaultWarehouseId) {
            $product->warehouses()->attach($this->defaultWarehouseId, [
                'stock' => 0,
                'min_stock' => $row['min_stock'] ?? 0
            ]);
        }

        return $product;
    }

    public function rules(): array
    {
        return [
            'name' => 'required|string',
            'sku' => 'required|string|unique:products,code',
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
}
