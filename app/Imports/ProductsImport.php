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
        // Note: For batch inserts, we ideally shouldn't create relations on the fly 
        // as it breaks the batch purity, but for categories it's acceptable if cached immediately.
        $categoryName = $row['category_name'];
        if (!isset($this->categories[$categoryName]) && !empty($categoryName)) {
            $category = Category::firstOrCreate(
                ['name' => $categoryName],
                ['type' => 'raw_material']
            );
            $this->categories[$categoryName] = $category->id;
        }

        $product = new Product([
            'name' => $row['name'],
            'code' => $row['sku'],
            'description' => $row['description'] ?? null,
            'category_id' => $this->categories[$categoryName] ?? null,
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
        
        // Note: attach() cannot be used directly on the model instance before it's saved.
        // With Maatwebsite batch inserts, listeners or separate handling is often needed for relations.
        // However, standard ToModel saves the model. 
        // For relationships like belongsToMany (warehouses), we need the ID.
        // Optimization: We will handle warehouse attachment in a loop after the batch is imported 
        // OR rely on a simpler 'after import' job if strict batching is needed.
        // For this implementation, we will stick to creating the product, but we need to handle the warehouse relation.
        // Since ToModel with BatchInserts persists the model, we can try using the 'created' event 
        // or just accept that pivot table inserts might be separate queries for now, 
        // or use a closure/hook. 
        // A better approach for bulk high-performance is avoiding Eloquent for the pivot 
        // and using DB::table('product_warehouse')->insert() in bulk events.
        
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
        return 1000;
    }

    public function batchSize(): int
    {
        return 1000;
    }
}
