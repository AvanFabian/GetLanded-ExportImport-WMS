<?php

namespace App\Imports;

use App\Models\Product;
use App\Models\Category;
use App\Models\Warehouse;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithValidation;
use Illuminate\Validation\Rule;

class ProductsImport implements ToModel, WithHeadingRow, WithValidation
{
    private $categories;
    private $defaultWarehouseId;

    public function __construct()
    {
        // Cache categories to avoid N+1 queries
        $this->categories = Category::pluck('id', 'name')->toArray();
        // Default to the first active warehouse if none specified (Enhancement: Allow passing warehouse_id)
        $this->defaultWarehouseId = Warehouse::first()->id ?? null;
    }

    /**
    * @param array $row
    *
    * @return \Illuminate\Database\Eloquent\Model|null
    */
    public function model(array $row)
    {
        // Find or create category
        $categoryId = $this->categories[$row['category_name']] ?? null;
        if (!$categoryId && !empty($row['category_name'])) {
            $category = Category::create(['name' => $row['category_name'], 'type' => 'raw_material']);
            $this->categories[$row['category_name']] = $category->id;
            $categoryId = $category->id;
        }

        $product = Product::create([
            'name' => $row['name'],
            'code' => $row['sku'], // Map SKU to Code
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
            // Note: Weight value (scalar) doesn't have a direct column in products table yet 
            // based on migration 2026_01_20_000004, but we have weight_unit.
            // Assuming we might need to add it or it was missed. 
            // Checking migration again... only weight_unit is there?
            // "add_weight_fields_to_batches" exists. "add_trade_fields_to_products" has weight_unit.
            
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
            'sku' => 'required|unique:products,code', // Unique check
            'unit' => 'required|string',
            'purchase_price' => 'required|numeric|min:0',
            'selling_price' => 'required|numeric|min:0',
        ];
    }
}
