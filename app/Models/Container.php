<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use App\Models\Traits\BelongsToTenant;

class Container extends Model
{
    use HasFactory, BelongsToTenant;

    protected $fillable = [
        'company_id', 'outbound_shipment_id', 'container_number',
        'container_type', 'max_weight_kg', 'max_volume_cbm',
        'seal_number', 'status', 'used_weight_kg', 'used_volume_cbm',
        'notes',
    ];

    protected $casts = [
        'max_weight_kg' => 'decimal:2',
        'max_volume_cbm' => 'decimal:4',
        'used_weight_kg' => 'decimal:2',
        'used_volume_cbm' => 'decimal:4',
    ];

    const TYPES = [
        '20ft' => ['label' => '20ft Standard', 'max_weight' => 21770, 'max_volume' => 33.2],
        '40ft' => ['label' => '40ft Standard', 'max_weight' => 26680, 'max_volume' => 67.7],
        '40ft_hc' => ['label' => '40ft High Cube', 'max_weight' => 26460, 'max_volume' => 76.3],
        'reefer_20ft' => ['label' => '20ft Reefer', 'max_weight' => 21100, 'max_volume' => 28.3],
        'reefer_40ft' => ['label' => '40ft Reefer', 'max_weight' => 26280, 'max_volume' => 59.3],
    ];

    const STATUSES = [
        'empty' => 'Empty',
        'loading' => 'Loading',
        'sealed' => 'Sealed',
        'shipped' => 'Shipped',
        'arrived' => 'Arrived',
        'returned' => 'Returned',
    ];

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    public function outboundShipment(): BelongsTo
    {
        return $this->belongsTo(OutboundShipment::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(ContainerItem::class);
    }

    public function remainingWeightKg(): float
    {
        return max(0, $this->max_weight_kg - $this->used_weight_kg);
    }

    public function remainingVolumeCbm(): float
    {
        return max(0, $this->max_volume_cbm - $this->used_volume_cbm);
    }

    public function utilizationPercent(): float
    {
        if ($this->max_weight_kg <= 0 && $this->max_volume_cbm <= 0) return 0;
        $weightUtil = $this->max_weight_kg > 0 ? ($this->used_weight_kg / $this->max_weight_kg) * 100 : 0;
        $volumeUtil = $this->max_volume_cbm > 0 ? ($this->used_volume_cbm / $this->max_volume_cbm) * 100 : 0;
        return max($weightUtil, $volumeUtil);
    }

    public function recalculateUsage(): void
    {
        $this->update([
            'used_weight_kg' => $this->items()->sum('weight_kg'),
            'used_volume_cbm' => $this->items()->sum('volume_cbm'),
        ]);
    }
}
