<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class StockTakeItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'stock_take_id',
        'batch_id',
        'bin_id',
        'system_quantity',
        'counted_quantity',
        'variance',
    ];

    protected $casts = [
        'system_quantity' => 'integer',
        'counted_quantity' => 'integer',
        'variance' => 'integer',
    ];

    protected $hidden = [];

    public function stockTake(): BelongsTo
    {
        return $this->belongsTo(StockTake::class);
    }

    public function batch(): BelongsTo
    {
        return $this->belongsTo(Batch::class);
    }

    public function bin(): BelongsTo
    {
        return $this->belongsTo(WarehouseBin::class, 'bin_id');
    }

    /**
     * Hide system quantity during blind stock take
     */
    public function toArray(): array
    {
        $array = parent::toArray();
        
        if ($this->stockTake && $this->stockTake->is_blind && $this->stockTake->status === 'in_progress') {
            unset($array['system_quantity']);
        }
        
        return $array;
    }
}
