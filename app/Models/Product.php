<?php

namespace App\Models;

use App\Models\Traits\BelongsToTenant;
use App\Traits\LogsActivity;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Product extends Model
{
    use HasFactory;
    use LogsActivity, BelongsToTenant;

    protected $fillable = [
        'company_id',
        'code',
        'name',
        'description',
        'category_id',
        'unit',
        'min_stock',
        'purchase_price',
        'selling_price',
        'image',
        'status',
        'has_variants',
        'enable_batch_tracking',
        'batch_method',
        'weighted_average_cost',
        // Trade / Landed Cost fields
        'hs_code',
        'origin_country',
        'weight_unit',
        'dimension_unit',
        'net_weight',
        'cbm_volume',
    ];

    /**
     * Get the dynamic cost (WAC) or fallback to static purchase price.
     */
    public function getCostAttribute()
    {
        return $this->weighted_average_cost ?? $this->purchase_price;
    }

    protected $casts = [
        'has_variants' => 'boolean',
        'status' => 'boolean',
    ];

    /**
     * Many-to-Many relationship with Warehouse through pivot table
     * Access: $product->warehouses
     * Pivot data: $product->warehouses->first()->pivot->stock
     */
    public function warehouses()
    {
        return $this->belongsToMany(Warehouse::class, 'product_warehouse')
            ->withPivot(['stock', 'rack_location', 'min_stock'])
            ->withTimestamps();
    }

    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    public function stockInDetails()
    {
        return $this->hasMany(StockInDetail::class);
    }

    public function stockOutDetails()
    {
        return $this->hasMany(StockOutDetail::class);
    }

    /**
     * Has many variants
     */
    public function variants()
    {
        return $this->hasMany(ProductVariant::class);
    }

    /**
     * Check if product has variants enabled and variants exist
     */
    public function hasVariants()
    {
        return $this->has_variants && $this->variants()->exists();
    }

    /**
     * Get total stock across all warehouses
     * If product has variants, sum stock from all variants
     * Otherwise, sum stock from direct product-warehouse relationship
     */
    public function getTotalStockAttribute()
    {
        if ($this->has_variants) {
            return $this->variants()
                ->with('warehouses')
                ->get()
                ->sum(function ($variant) {
                    return $variant->warehouses->sum('pivot.stock');
                });
        }

        return $this->warehouses()->sum('product_warehouse.stock');
    }

    /**
     * Get stock in a specific warehouse
     */
    public function getStockInWarehouse($warehouseId)
    {
        $warehouse = $this->warehouses()->where('warehouse_id', $warehouseId)->first();
        return $warehouse ? $warehouse->pivot->stock : 0;
    }

    /**
     * Check if product has stock in a specific warehouse
     */
    public function hasStockInWarehouse($warehouseId, $quantity = 1)
    {
        return $this->getStockInWarehouse($warehouseId) >= $quantity;
    }
}
