<?php

namespace App\Models;

use App\Models\Traits\BelongsToTenant;
use App\Traits\LogsActivity;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Builder;

class Batch extends Model
{
    use HasFactory;
    use LogsActivity, BelongsToTenant;

    protected $fillable = [
        'batch_number',
        'product_id',
        'variant_id',
        'supplier_id',
        'manufacture_date',
        'expiry_date',
        'cost_price',
        'status',
        'stock_in_id',
        'notes',
    ];

    protected $casts = [
        'manufacture_date' => 'date',
        'expiry_date' => 'date',
        'cost_price' => 'decimal:2',
    ];

    // ============================================
    // RELATIONSHIPS
    // ============================================

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function variant(): BelongsTo
    {
        return $this->belongsTo(ProductVariant::class);
    }

    public function supplier(): BelongsTo
    {
        return $this->belongsTo(Supplier::class);
    }

    public function stockIn(): BelongsTo
    {
        return $this->belongsTo(StockIn::class);
    }

    public function stockLocations(): HasMany
    {
        return $this->hasMany(StockLocation::class);
    }

    public function movements(): HasMany
    {
        return $this->hasMany(BatchMovement::class);
    }

    // ============================================
    // COMPUTED ATTRIBUTES
    // ============================================

    /**
     * Get total quantity across all bins
     */
    public function getTotalQuantityAttribute(): int
    {
        return $this->stockLocations()->sum('quantity');
    }

    /**
     * Get available quantity (total - reserved)
     */
    public function getAvailableQuantityAttribute(): int
    {
        $total = $this->stockLocations()->sum('quantity');
        $reserved = $this->stockLocations()->sum('reserved_quantity');
        return $total - $reserved;
    }

    /**
     * Check if batch is expired
     */
    public function getIsExpiredAttribute(): bool
    {
        if (!$this->expiry_date) {
            return false;
        }
        return $this->expiry_date->isPast();
    }

    // ============================================
    // SCOPES
    // ============================================

    /**
     * Scope to get only active batches
     */
    public function scopeActive(Builder $query): Builder
    {
        return $query->where('status', 'active');
    }

    /**
     * Scope to exclude expired batches
     */
    public function scopeNotExpired(Builder $query): Builder
    {
        return $query->where(function ($q) {
            $q->whereNull('expiry_date')
              ->orWhere('expiry_date', '>', now());
        });
    }

    /**
     * Scope to get batches with available stock
     */
    public function scopeWithAvailableStock(Builder $query): Builder
    {
        return $query->whereHas('stockLocations', function ($q) {
            $q->whereRaw('quantity > COALESCE(reserved_quantity, 0)');
        });
    }

    /**
     * Scope to order by batch method (FIFO, LIFO, FEFO)
     */
    public function scopeByMethod(Builder $query, string $method): Builder
    {
        return match ($method) {
            'FIFO' => $query->orderBy('created_at', 'asc'),
            'LIFO' => $query->orderBy('created_at', 'desc'),
            'FEFO' => $query->orderByRaw('CASE WHEN expiry_date IS NULL THEN 1 ELSE 0 END, expiry_date ASC'),
            default => $query->orderBy('created_at', 'asc'),
        };
    }

    /**
     * Scope to filter by warehouse (through stock locations and bins)
     */
    public function scopeInWarehouse(Builder $query, int $warehouseId): Builder
    {
        return $query->whereHas('stockLocations', function ($slQuery) use ($warehouseId) {
            $slQuery->whereHas('bin', function ($binQuery) use ($warehouseId) {
                $binQuery->whereHas('rack', function ($rackQuery) use ($warehouseId) {
                    $rackQuery->whereHas('zone', function ($zoneQuery) use ($warehouseId) {
                        $zoneQuery->where('warehouse_id', $warehouseId);
                    });
                });
            });
        });
    }
}
