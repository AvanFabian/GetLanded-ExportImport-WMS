<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ContainerItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'container_id', 'product_id', 'batch_id',
        'quantity', 'weight_kg', 'volume_cbm',
        'carton_count', 'remarks',
    ];

    protected $casts = [
        'weight_kg' => 'decimal:2',
        'volume_cbm' => 'decimal:4',
    ];

    protected static function booted(): void
    {
        static::saved(fn ($item) => $item->container->recalculateUsage());
        static::deleted(fn ($item) => $item->container->recalculateUsage());
    }

    public function container(): BelongsTo
    {
        return $this->belongsTo(Container::class);
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function batch(): BelongsTo
    {
        return $this->belongsTo(Batch::class);
    }
}
