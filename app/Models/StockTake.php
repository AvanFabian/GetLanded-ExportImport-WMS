<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use App\Models\Traits\BelongsToTenant;

class StockTake extends Model
{
    use HasFactory, BelongsToTenant;

    protected $fillable = [
        'company_id',
        'warehouse_id',
        'take_number',
        'status',
        'is_blind',
        'created_by',
        'completed_by',
        'completed_at',
    ];

    protected $casts = [
        'is_blind' => 'boolean',
        'completed_at' => 'datetime',
    ];

    const STATUS_IN_PROGRESS = 'in_progress';
    const STATUS_COMPLETED = 'completed';

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    public function warehouse(): BelongsTo
    {
        return $this->belongsTo(Warehouse::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(StockTakeItem::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function completer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'completed_by');
    }

    public static function generateTakeNumber(): string
    {
        $prefix = 'STK';
        $year = now()->format('Y');
        $count = static::whereYear('created_at', now()->year)->count() + 1;
        return sprintf('%s-%s-%05d', $prefix, $year, $count);
    }

    public function complete(int $userId): void
    {
        // Calculate variances
        foreach ($this->items as $item) {
            $item->update([
                'variance' => $item->counted_quantity - $item->system_quantity
            ]);
        }

        $this->update([
            'status' => self::STATUS_COMPLETED,
            'completed_by' => $userId,
            'completed_at' => now(),
        ]);
    }

    public function getVarianceReportAttribute(): array
    {
        return $this->items->map(fn($item) => [
            'batch' => $item->batch->batch_number,
            'bin' => $item->bin->full_code ?? $item->bin_id,
            'system' => $item->system_quantity,
            'counted' => $item->counted_quantity,
            'variance' => $item->variance,
            'value_impact' => $item->variance * ($item->batch->unit_purchase_price ?? 0),
        ])->toArray();
    }
}
