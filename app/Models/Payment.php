<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use App\Models\Traits\BelongsToTenant;

class Payment extends Model
{
    use HasFactory, BelongsToTenant;

    protected $fillable = [
        'company_id',
        'sales_order_id',
        'customer_id',
        'bank_account_id',
        'payment_date',
        'amount',
        'bank_fees',
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
        'bank_fees' => 'decimal:2',
        'exchange_rate' => 'decimal:8',
        'base_currency_amount' => 'decimal:2',
    ];

    const PAYMENT_METHODS = [
        'bank_transfer' => 'Bank Transfer',
        'cash' => 'Cash',
        'lc' => 'Letter of Credit',
        'tt' => 'Telegraphic Transfer',
    ];

    protected static function booted(): void
    {
        static::saved(function ($payment) {
            if ($payment->sales_order_id) {
                $payment->updateSalesOrderPaymentStatus();
            } else {
                // Unallocated deposit - add to customer credit balance
                if ($payment->customer_id) {
                    $payment->customer->increment('credit_balance', $payment->base_currency_amount);
                }
            }
        });

        static::deleted(function ($payment) {
            if ($payment->sales_order_id) {
                $payment->updateSalesOrderPaymentStatus();
            }
        });
    }

    public function updateSalesOrderPaymentStatus(): void
    {
        $order = $this->salesOrder;
        if (!$order) return;
        
        $totalPaid = $order->payments()->sum('base_currency_amount');
        $totalFees = $order->payments()->sum('bank_fees');
        
        $amountDue = $order->total - $order->credit_note_amount;
        $totalSettled = $totalPaid + $totalFees;
        
        $status = match(true) {
            $totalSettled >= $amountDue => 'paid',
            $totalSettled > 0 => 'partial',
            default => 'unpaid'
        };
        
        $order->update([
            'amount_paid' => $totalPaid,
            'total_bank_fees' => $totalFees,
            'payment_status' => $status,
            'exchange_gain_loss' => $this->calculateExchangeGainLoss($order),
        ]);
    }

    protected function calculateExchangeGainLoss($order): float
    {
        $gainLoss = 0;
        foreach ($order->payments as $payment) {
            if ($payment->currency_code !== $order->currency_code && $payment->exchange_rate) {
                $expectedBase = $payment->amount * ($order->exchange_rate_at_transaction ?? 1);
                $actualBase = $payment->base_currency_amount;
                $gainLoss += ($actualBase - $expectedBase);
            }
        }
        return $gainLoss;
    }

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    public function salesOrder(): BelongsTo
    {
        return $this->belongsTo(SalesOrder::class);
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public function bankAccount(): BelongsTo
    {
        return $this->belongsTo(CompanyBankAccount::class, 'bank_account_id');
    }

    public function allocations(): HasMany
    {
        return $this->hasMany(PaymentAllocation::class);
    }

    public function allocateToOrders(array $allocations): void
    {
        foreach ($allocations as $orderId => $amount) {
            $order = SalesOrder::find($orderId);
            if (!$order) continue;
            
            $order->increment('amount_paid', $amount);
            $this->customer->decrement('credit_balance', $amount);
            
            PaymentAllocation::create([
                'payment_id' => $this->id,
                'sales_order_id' => $orderId,
                'amount' => $amount,
            ]);
            
            // Recalculate order payment status
            $amountDue = $order->total - $order->credit_note_amount;
            $status = $order->amount_paid >= $amountDue ? 'paid' : 'partial';
            $order->update(['payment_status' => $status]);
        }
    }
}
