<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class UomConversion extends Model
{
    use HasFactory;

    protected $fillable = [
        'company_id',
        'product_id',
        'from_unit',
        'to_unit',
        'conversion_factor',
        'is_default',
        'is_active',
    ];

    protected $casts = [
        'conversion_factor' => 'decimal:10',
        'is_default' => 'boolean',
        'is_active' => 'boolean',
    ];

    /**
     * Company that owns this conversion
     */
    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    /**
     * Product this conversion is specific to (null = global)
     */
    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    /**
     * Scope to active conversions
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope to global (non-product-specific) conversions
     */
    public function scopeGlobal($query)
    {
        return $query->whereNull('product_id');
    }

    /**
     * Scope to specific product or global
     */
    public function scopeForProduct($query, $productId)
    {
        return $query->where(function ($q) use ($productId) {
            $q->where('product_id', $productId)
              ->orWhereNull('product_id');
        });
    }

    /**
     * Get the inverse conversion factor
     */
    public function getInverseFactorAttribute(): float
    {
        return 1 / $this->conversion_factor;
    }

    /**
     * Convert a quantity
     */
    public function convert(float $quantity): float
    {
        return $quantity * $this->conversion_factor;
    }

    /**
     * Convert a quantity in reverse direction
     */
    public function convertReverse(float $quantity): float
    {
        return $quantity / $this->conversion_factor;
    }
}
