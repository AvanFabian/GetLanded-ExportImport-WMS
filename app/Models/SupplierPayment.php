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
        'amount_owed',
        'amount_paid',
        'due_date',
        'payment_status',
        'payment_method',
        'currency_code',
        'bank_reference',
        'lc_number',
        'lc_expiry_date',
        'lc_issuing_bank',
        'lc_expiry_date',
        'lc_issuing_bank',
        'payment_notes',
        'reconciled_at',
        'bank_statement_ref',
    ];

    const PAYMENT_METHODS = [
        'bank_transfer' => 'Bank Transfer (TT)',
        'letter_of_credit' => 'Letter of Credit (L/C)',
        'advance_payment' => 'Advance Payment (T/T in Advance)',
        'open_account' => 'Open Account',
        'cash' => 'Cash',
    ];

    protected $casts = [
        'due_date' => 'date',
        'lc_expiry_date' => 'date',
        'reconciled_at' => 'datetime',
        'amount_owed' => 'decimal:2',
        'amount_paid' => 'decimal:2',
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
        static::saving(function ($payment) {
            // Auto-calculate payment status
            $payment->payment_status = match (true) {
                $payment->amount_paid >= $payment->amount_owed => 'paid',
                $payment->amount_paid > 0 => 'partial',
                default => 'unpaid',
            };
        });
    }
}
