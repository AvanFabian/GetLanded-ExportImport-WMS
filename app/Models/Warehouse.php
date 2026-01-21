<?php

namespace App\Models;

use App\Models\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\DB;

class Warehouse extends Model
{
    use HasFactory, SoftDeletes, BelongsToTenant;

    protected $fillable = [
        'name',
        'code',
        'address',
        'city',
        'province',
        'postal_code',
        'phone',
        'email',
        'is_active',
        'is_default',
        'created_by',
        'updated_by'
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'is_default' => 'boolean',
    ];

    /**
     * Boot the model
     */
    protected static function boot()
    {
        parent::boot();

        // Prevent deletion if warehouse has transactions
        static::deleting(function ($warehouse) {
            if (
                $warehouse->products()->count() > 0 ||
                $warehouse->stockIns()->count() > 0 ||
                $warehouse->stockOuts()->count() > 0 ||
                $warehouse->stockOpnames()->count() > 0 ||
                $warehouse->transfersFrom()->count() > 0 ||
                $warehouse->transfersTo()->count() > 0
            ) {
                throw new \Exception('Cannot delete warehouse with existing transactions or products.');
            }
        });
    }

    /**
     * Relationships
     */

    /**
     * Many-to-Many relationship with Product through pivot table
     * Access: $warehouse->products
     * Pivot data: $warehouse->products->first()->pivot->stock
     */
    public function products()
    {
        return $this->belongsToMany(Product::class, 'product_warehouse')
            ->withPivot(['stock', 'rack_location', 'min_stock'])
            ->withTimestamps();
    }

    public function stockIns()
    {
        return $this->hasMany(StockIn::class);
    }

    public function stockOuts()
    {
        return $this->hasMany(StockOut::class);
    }

    public function stockOpnames()
    {
        return $this->hasMany(StockOpname::class);
    }

    public function transfersFrom()
    {
        return $this->hasMany(InterWarehouseTransfer::class, 'from_warehouse_id');
    }

    public function transfersTo()
    {
        return $this->hasMany(InterWarehouseTransfer::class, 'to_warehouse_id');
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function updater()
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    /**
     * Scopes
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeDefault($query)
    {
        return $query->where('is_default', true);
    }

    /**
     * Helper methods
     */
    public function getTotalStockValue()
    {
        // Sum value of all products in this warehouse
        return $this->products()->get()->sum(function ($product) {
            return $product->pivot->stock * $product->purchase_price;
        });
    }

    public function getProductCount()
    {
        return $this->products()->count();
    }

    /**
     * Get total stock units in this warehouse
     */
    public function getTotalStock()
    {
        return $this->products()->sum('product_warehouse.stock');
    }
}
