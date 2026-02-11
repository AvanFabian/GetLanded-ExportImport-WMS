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

    /**
     * Get expense amount converted to IDR using the Currency model's exchange rate.
     */
    public function getAmountInIdr(): float
    {
        if (empty($this->currency_code) || strtoupper($this->currency_code) === 'IDR') {
            return (float) $this->amount;
        }

        $currency = \App\Models\Currency::findByCode($this->currency_code);
        if (!$currency || $currency->is_base) {
            return (float) $this->amount;
        }

        return (float) $this->amount * (float) $currency->exchange_rate;
    }
}
