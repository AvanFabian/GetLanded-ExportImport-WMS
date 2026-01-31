<?php

namespace App\Models;

use App\Models\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PurchaseOrder extends Model
{
    use SoftDeletes, BelongsToTenant;

    protected $fillable = [
        'po_number',
        'inbound_shipment_id',
        'warehouse_id',
        'supplier_id',
        'order_date',
        'expected_delivery_date',
        'status',
        'total_amount',
        'notes',
        'created_by',
        'approved_by',
        'approved_at',
        // Currency fields
        'currency_code',
        'exchange_rate_at_transaction',
        'transaction_fees',
        'fee_currency_code',
        'net_amount',
    ];

    protected $casts = [
        'order_date' => 'date',
        'expected_delivery_date' => 'date',
        'approved_at' => 'datetime',
        'total_amount' => 'decimal:2',
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
        static::creating(function (PurchaseOrder $order) {
            static::setExchangeRateAndNetAmount($order);
        });

        // Recalculate net amount on updating
        static::updating(function (PurchaseOrder $order) {
            static::recalculateNetAmount($order);
        });
    }

    /**
     * Set exchange rate from current currency rate and calculate net amount.
     */
    protected static function setExchangeRateAndNetAmount(PurchaseOrder $order): void
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
     * Recalculate net amount: total_amount - transaction_fees
     */
    protected static function recalculateNetAmount(PurchaseOrder $order): void
    {
        $total = (float) ($order->total_amount ?? 0);
        $fees = (float) ($order->transaction_fees ?? 0);
        
        $order->net_amount = $total - $fees;
    }

    public function warehouse(): BelongsTo
    {
        return $this->belongsTo(Warehouse::class);
    }

    public function supplier(): BelongsTo
    {
        return $this->belongsTo(Supplier::class);
    }

    public function inboundShipment(): BelongsTo
    {
        return $this->belongsTo(InboundShipment::class);
    }

    public function details(): HasMany
    {
        return $this->hasMany(PurchaseOrderDetail::class);
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function approvedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public function canBeEdited(): bool
    {
        return in_array($this->status, ['draft', 'pending']);
    }

    public function canBeApproved(): bool
    {
        return $this->status === 'pending';
    }

    public function canBeReceived(): bool
    {
        return in_array($this->status, ['approved', 'partially_received']);
    }

    public function getRemainingQuantity(int $productId): int
    {
        $detail = $this->details()->where('product_id', $productId)->first();
        if (!$detail) {
            return 0;
        }
        return $detail->quantity_ordered - $detail->quantity_received;
    }

    public function isFullyReceived(): bool
    {
        foreach ($this->details as $detail) {
            if ($detail->quantity_received < $detail->quantity_ordered) {
                return false;
            }
        }
        return true;
    }

    public static function generatePoNumber(): string
    {
        $date = now()->format('Ymd');
        $lastPo = self::whereDate('created_at', now())->latest()->first();

        if ($lastPo) {
            $lastNumber = (int) substr($lastPo->po_number, -4);
            $newNumber = $lastNumber + 1;
        } else {
            $newNumber = 1;
        }

        return 'PO-' . $date . '-' . str_pad($newNumber, 4, '0', STR_PAD_LEFT);
    }
}
