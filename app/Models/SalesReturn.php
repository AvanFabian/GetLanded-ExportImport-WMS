<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use App\Models\Traits\BelongsToTenant;

class SalesReturn extends Model
{
    use HasFactory, BelongsToTenant;

    protected $fillable = [
        'company_id',
        'sales_order_id',
        'return_number',
        'return_date',
        'credit_amount',
        'reason',
        'status',
        'approved_by',
        'approved_at',
    ];

    protected $casts = [
        'return_date' => 'date',
        'credit_amount' => 'decimal:2',
        'approved_at' => 'datetime',
    ];

    const STATUS_PENDING = 'pending';
    const STATUS_APPROVED = 'approved';
    const STATUS_PROCESSED = 'processed';

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    public function salesOrder(): BelongsTo
    {
        return $this->belongsTo(SalesOrder::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(SalesReturnItem::class);
    }

    public function approver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public static function generateReturnNumber(): string
    {
        $prefix = 'RET';
        $year = now()->format('Y');
        $count = static::whereYear('created_at', now()->year)->count() + 1;
        return sprintf('%s-%s-%05d', $prefix, $year, $count);
    }

    public function process(): void
    {
        // Quarantine returned batches
        foreach ($this->items as $item) {
            if ($item->batch_id) {
                $item->batch->update([
                    'is_quarantined' => true,
                    'quarantine_reason' => 'Returned from customer: ' . $this->reason
                ]);
            }
        }

        // Apply credit note to sales order
        $this->salesOrder->increment('credit_note_amount', $this->credit_amount);
        
        // Recalculate payment status
        $order = $this->salesOrder;
        $amountDue = $order->total - $order->credit_note_amount;
        $totalSettled = $order->amount_paid + $order->total_bank_fees;
        
        $status = match(true) {
            $totalSettled >= $amountDue => 'paid',
            $totalSettled > 0 => 'partial',
            default => 'unpaid'
        };
        
        $order->update(['payment_status' => $status]);
        
        $this->update(['status' => self::STATUS_PROCESSED]);
    }
}
