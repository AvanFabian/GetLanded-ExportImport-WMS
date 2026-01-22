<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Models\Traits\BelongsToTenant;

class OrderExpense extends Model
{
    use HasFactory, BelongsToTenant;

    protected $fillable = [
        'company_id',
        'sales_order_id',
        'category',
        'amount',
        'currency_code',
        'withholding_tax_rate',
        'withholding_tax_amount',
        'description',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'withholding_tax_rate' => 'decimal:2',
        'withholding_tax_amount' => 'decimal:2',
    ];

    const CATEGORIES = [
        'freight' => 'Freight',
        'insurance' => 'Insurance',
        'customs_clearance' => 'Customs Clearance',
        'fumigation' => 'Fumigation',
        'other' => 'Other',
    ];

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    public function salesOrder(): BelongsTo
    {
        return $this->belongsTo(SalesOrder::class);
    }

    public function getNetAmountAttribute(): float
    {
        return $this->amount - $this->withholding_tax_amount;
    }

    protected static function booted(): void
    {
        static::saving(function ($expense) {
            if ($expense->withholding_tax_rate && !$expense->withholding_tax_amount) {
                $expense->withholding_tax_amount = $expense->amount * ($expense->withholding_tax_rate / 100);
            }
        });
    }
}
