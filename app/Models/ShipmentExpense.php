<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ShipmentExpense extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'inbound_shipment_id',
        'name',
        'amount',
        'currency_code',
        'allocation_method', // value, weight, volume, quantity
        'notes',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
    ];

    public function shipment(): BelongsTo
    {
        return $this->belongsTo(InboundShipment::class, 'inbound_shipment_id');
    }
}
