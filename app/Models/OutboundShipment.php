<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use App\Models\Traits\BelongsToTenant;

class OutboundShipment extends Model
{
    use HasFactory, BelongsToTenant;

    protected $fillable = [
        'company_id', 'sales_order_id', 'shipment_number',
        'shipment_date', 'estimated_arrival', 'actual_arrival',
        'carrier_name', 'vessel_name', 'voyage_number',
        'bill_of_lading', 'booking_number',
        'port_of_loading', 'port_of_discharge', 'destination_country',
        'incoterm', 'freight_cost', 'insurance_cost', 'currency_code',
        'status', 'notes',
    ];

    protected $casts = [
        'shipment_date' => 'date',
        'estimated_arrival' => 'date',
        'actual_arrival' => 'date',
        'freight_cost' => 'decimal:2',
        'insurance_cost' => 'decimal:2',
    ];

    const STATUSES = [
        'draft' => 'Draft',
        'booked' => 'Booked',
        'shipped' => 'Shipped',
        'in_transit' => 'In Transit',
        'arrived' => 'Arrived',
        'delivered' => 'Delivered',
    ];

    const INCOTERMS = [
        'EXW' => 'EXW — Ex Works',
        'FOB' => 'FOB — Free On Board',
        'CIF' => 'CIF — Cost, Insurance & Freight',
        'CFR' => 'CFR — Cost & Freight',
        'CIP' => 'CIP — Carriage & Insurance Paid',
        'DAP' => 'DAP — Delivered at Place',
        'DDP' => 'DDP — Delivered Duty Paid',
    ];

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    public function salesOrder(): BelongsTo
    {
        return $this->belongsTo(SalesOrder::class);
    }

    public function containers(): HasMany
    {
        return $this->hasMany(Container::class);
    }

    public function customsDeclaration(): HasOne
    {
        return $this->hasOne(CustomsDeclaration::class);
    }

    public function expenses(): HasMany
    {
        return $this->hasMany(ShipmentExpense::class);
    }

    public static function generateShipmentNumber(int $companyId): string
    {
        $count = static::where('company_id', $companyId)->count() + 1;
        return sprintf('SHP-%s-%05d', date('Y'), $count);
    }
}
