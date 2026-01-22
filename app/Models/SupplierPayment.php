<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Models\Traits\BelongsToTenant;

class SupplierPayment extends Model
{
    use HasFactory, BelongsToTenant;

    protected $fillable = [
        'company_id',
        'supplier_id',
        'stock_in_id',
        'payment_date',
        'amount',
        'currency_code',
        'exchange_rate',
        'base_currency_amount',
        'payment_method',
        'reference',
        'notes',
    ];

    protected $casts = [
        'payment_date' => 'date',
        'amount' => 'decimal:2',
        'exchange_rate' => 'decimal:8',
        'base_currency_amount' => 'decimal:2',
    ];

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    public function supplier(): BelongsTo
    {
        return $this->belongsTo(Supplier::class);
    }

    public function stockIn(): BelongsTo
    {
        return $this->belongsTo(StockIn::class);
    }

    protected static function booted(): void
    {
        static::saved(function ($payment) {
            // Update supplier outstanding balance
            $totalPaid = static::where('supplier_id', $payment->supplier_id)
                ->sum('base_currency_amount');
            
            $payment->supplier->update(['total_paid' => $totalPaid]);
        });
    }
}
