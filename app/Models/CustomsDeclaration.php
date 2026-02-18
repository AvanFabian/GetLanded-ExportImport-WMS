<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use App\Models\Traits\BelongsToTenant;

class CustomsDeclaration extends Model
{
    use HasFactory, BelongsToTenant;

    protected $fillable = [
        'company_id', 'outbound_shipment_id', 'declaration_number',
        'declaration_type', 'declaration_date', 'customs_office',
        'hs_code', 'declared_value', 'currency_code',
        'duty_rate', 'duty_amount', 'vat_rate', 'vat_amount',
        'pph_rate', 'pph_amount',
        'anti_dumping_rate', 'anti_dumping_amount',
        'fta_scheme',
        'excise_amount', 'total_tax', 'status', 'notes',
    ];

    protected $casts = [
        'declaration_date' => 'date',
        'declared_value' => 'decimal:2',
        'duty_rate' => 'decimal:4',
        'duty_amount' => 'decimal:2',
        'vat_rate' => 'decimal:4',
        'vat_amount' => 'decimal:2',
        'pph_rate' => 'decimal:4',
        'pph_amount' => 'decimal:2',
        'anti_dumping_rate' => 'decimal:4',
        'anti_dumping_amount' => 'decimal:2',
        'excise_amount' => 'decimal:2',
        'total_tax' => 'decimal:2',
    ];

    const STATUSES = [
        'draft' => 'Draft',
        'submitted' => 'Submitted',
        'assessed' => 'Assessed',
        'paid' => 'Paid',
        'cleared' => 'Cleared',
        'rejected' => 'Rejected',
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
        return $this->hasMany(CustomsDeclarationItem::class);
    }

    /**
     * Calculate all duties using DutyCalculationService.
     */
    public function calculateDuty(): void
    {
        $service = app(\App\Services\DutyCalculationService::class);

        $result = $service->calculate(
            declaredValue: (float) $this->declared_value,
            bmRate: (float) $this->duty_rate,
            ppnRate: (float) $this->vat_rate ?: null,
            pphRate: (float) $this->pph_rate ?: null,
            adRate: (float) $this->anti_dumping_rate,
            exciseAmount: (float) $this->excise_amount,
        );

        $this->duty_amount = $result['bm_amount'];
        $this->vat_amount = $result['ppn_amount'];
        $this->pph_amount = $result['pph_amount'];
        $this->anti_dumping_amount = $result['anti_dumping_amount'];
        $this->total_tax = $result['total_tax'];
        $this->save();
    }
}
