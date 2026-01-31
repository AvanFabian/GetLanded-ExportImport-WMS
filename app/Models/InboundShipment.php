<?php

namespace App\Models;

use App\Models\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Models\ShipmentExpense;
use App\Models\Document;

class InboundShipment extends Model
{
    use SoftDeletes, BelongsToTenant;

    protected $fillable = [
        'shipment_number',
        'reference_number',
        'carrier_name',
        'vessel_flight_number',
        'origin_port',
        'destination_port',
        'etd',
        'eta',
        'actual_arrival_date',
        'status',
        'notes',
        'created_by',
        'company_id'
    ];

    protected $casts = [
        'etd' => 'date',
        'eta' => 'date',
        'actual_arrival_date' => 'date',
    ];

    /**
     * Boot method for model events.
     */
    protected static function booted(): void
    {
        static::creating(function (InboundShipment $shipment) {
            if (empty($shipment->shipment_number)) {
                $shipment->shipment_number = static::generateShipmentNumber();
            }
        });
    }

    public function purchaseOrders(): HasMany
    {
        return $this->hasMany(PurchaseOrder::class);
    }

    public function expenses(): HasMany
    {
        return $this->hasMany(ShipmentExpense::class);
    }

    public function documents(): HasMany
    {
        return $this->hasMany(Document::class);
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public static function generateShipmentNumber(): string
    {
        // Format: SHP-202401-0001
        $prefix = 'SHP-' . now()->format('Ym') . '-';
        
        $latest = static::where('shipment_number', 'like', $prefix . '%')
            ->latest('id')
            ->value('shipment_number');

        if ($latest) {
            $number = intval(substr($latest, strlen($prefix))) + 1;
        } else {
            $number = 1;
        }

        return $prefix . str_pad($number, 4, '0', STR_PAD_LEFT);
    }
}
