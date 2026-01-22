<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use App\Models\Traits\BelongsToTenant;

class StockTransfer extends Model
{
    use HasFactory, BelongsToTenant;

    protected $fillable = [
        'company_id',
        'source_warehouse_id',
        'destination_warehouse_id',
        'transfer_number',
        'status',
        'transfer_date',
        'received_date',
        'created_by',
        'received_by',
    ];

    protected $casts = [
        'transfer_date' => 'date',
        'received_date' => 'date',
    ];

    const STATUS_PENDING = 'pending';
    const STATUS_IN_TRANSIT = 'in_transit';
    const STATUS_RECEIVED = 'received';

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    public function sourceWarehouse(): BelongsTo
    {
        return $this->belongsTo(Warehouse::class, 'source_warehouse_id');
    }

    public function destinationWarehouse(): BelongsTo
    {
        return $this->belongsTo(Warehouse::class, 'destination_warehouse_id');
    }

    public function items(): HasMany
    {
        return $this->hasMany(StockTransferItem::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function receiver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'received_by');
    }

    public static function generateTransferNumber(): string
    {
        $prefix = 'TRF';
        $year = now()->format('Y');
        $count = static::whereYear('created_at', now()->year)->count() + 1;
        return sprintf('%s-%s-%05d', $prefix, $year, $count);
    }

    public function dispatch(): void
    {
        $this->update(['status' => self::STATUS_IN_TRANSIT]);
        
        foreach ($this->items as $item) {
            // Reserve stock at source
            $item->sourceStockLocation->increment('reserved_quantity', $item->quantity);
        }
    }

    public function receive(int $receiverId): void
    {
        $this->update([
            'status' => self::STATUS_RECEIVED,
            'received_date' => now(),
            'received_by' => $receiverId,
        ]);
        
        foreach ($this->items as $item) {
            // Release from source
            $sourceLocation = $item->sourceStockLocation;
            $sourceLocation->decrement('reserved_quantity', $item->quantity);
            $sourceLocation->decrement('quantity', $item->quantity);
            
            // Add to destination
            StockLocation::updateOrCreate(
                [
                    'batch_id' => $item->batch_id,
                    'bin_id' => $item->destination_bin_id,
                ],
                []
            )->increment('quantity', $item->quantity);
        }
    }
}
