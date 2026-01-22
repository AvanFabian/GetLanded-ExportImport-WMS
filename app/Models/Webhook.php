<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use App\Models\Traits\BelongsToTenant;

class Webhook extends Model
{
    use HasFactory, BelongsToTenant;

    protected $fillable = [
        'company_id',
        'url',
        'events',
        'secret',
        'is_active',
    ];

    protected $casts = [
        'events' => 'array',
        'is_active' => 'boolean',
    ];

    const EVENTS = [
        'order.created' => 'Order Created',
        'order.completed' => 'Order Completed',
        'payment.received' => 'Payment Received',
        'shipment.sailing' => 'Shipment Sailing',
        'stock.low' => 'Stock Low',
    ];

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    public function logs(): HasMany
    {
        return $this->hasMany(WebhookLog::class);
    }

    public function supportsEvent(string $event): bool
    {
        return in_array($event, $this->events ?? []);
    }
}
