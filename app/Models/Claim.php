<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use App\Models\Traits\BelongsToTenant;

class Claim extends Model
{
    use HasFactory, BelongsToTenant;

    protected $fillable = [
        'company_id',
        'sales_order_id',
        'claim_type',
        'claimed_amount',
        'insurance_policy_number',
        'status',
        'settled_amount',
        'description',
    ];

    protected $casts = [
        'claimed_amount' => 'decimal:2',
        'settled_amount' => 'decimal:2',
    ];

    const CLAIM_TYPES = [
        'damage' => 'Damage',
        'shortage' => 'Shortage',
        'delay' => 'Delay',
    ];

    const STATUS_OPEN = 'open';
    const STATUS_SUBMITTED = 'submitted';
    const STATUS_SETTLED = 'settled';
    const STATUS_REJECTED = 'rejected';

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    public function salesOrder(): BelongsTo
    {
        return $this->belongsTo(SalesOrder::class);
    }

    public function evidences(): HasMany
    {
        return $this->hasMany(ClaimEvidence::class);
    }
}
