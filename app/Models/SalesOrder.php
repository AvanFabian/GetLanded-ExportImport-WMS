<?php

namespace App\Models;

use App\Models\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class SalesOrder extends Model
{
    use BelongsToTenant, SoftDeletes;
    protected $fillable = [
        'so_number',
        'customer_id',
        'warehouse_id',
        'order_date',
        'delivery_date',
        'status',
        'payment_status',
        'subtotal',
        'tax',
        'discount',
        'total',
        'notes',
        'document_language',
        'stock_out_id',
        'created_by',
        'updated_by',
        // Currency fields
        'currency_code',
        'exchange_rate_at_transaction',
        'transaction_fees',
        'fee_currency_code',
        'net_amount',
    ];

    protected $casts = [
        'order_date' => 'date',
        'delivery_date' => 'date',
        'subtotal' => 'decimal:2',
        'tax' => 'decimal:2',
        'discount' => 'decimal:2',
        'total' => 'decimal:2',
        'exchange_rate_at_transaction' => 'decimal:8',
        'transaction_fees' => 'decimal:2',
        'net_amount' => 'decimal:2',
    ];

    /**
     * Boot method for model events.
     */
    protected static function booted(): void
    {
        // Auto-set exchange rate and calculate net amount on creating
        static::creating(function (SalesOrder $order) {
            static::setExchangeRateAndNetAmount($order);
        });

        // Recalculate net amount on updating
        static::updating(function (SalesOrder $order) {
            static::recalculateNetAmount($order);
        });
    }

    /**
     * Set exchange rate from current currency rate and calculate net amount.
     */
    protected static function setExchangeRateAndNetAmount(SalesOrder $order): void
    {
        // Set exchange rate if currency is provided and rate not already set
        if ($order->currency_code && !$order->exchange_rate_at_transaction) {
            $currency = Currency::findByCode($order->currency_code);
            if ($currency) {
                $order->exchange_rate_at_transaction = $currency->exchange_rate;
            }
        }

        // Calculate net amount
        static::recalculateNetAmount($order);
    }

    /**
     * Recalculate net amount: total - transaction_fees
     */
    protected static function recalculateNetAmount(SalesOrder $order): void
    {
        $total = (float) ($order->total ?? 0);
        $fees = (float) ($order->transaction_fees ?? 0);
        
        $order->net_amount = $total - $fees;
    }

    // Relationships
    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }

    public function warehouse()
    {
        return $this->belongsTo(Warehouse::class);
    }

    public function items()
    {
        return $this->hasMany(SalesOrderItem::class);
    }

    public function stockOut()
    {
        return $this->belongsTo(StockOut::class);
    }

    public function invoice()
    {
        return $this->hasOne(Invoice::class);
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function updater()
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    // Generate SO number
    public static function generateSONumber()
    {
        $lastSO = self::whereDate('created_at', today())->latest()->first();
        $number = $lastSO ? (int)substr($lastSO->so_number, -5) + 1 : 1;

        return 'SO-' . date('Ymd') . '-' . str_pad($number, 5, '0', STR_PAD_LEFT);
    }
}
