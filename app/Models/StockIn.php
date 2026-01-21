<?php

namespace App\Models;

use App\Models\Traits\BelongsToTenant;
use App\Traits\LogsActivity;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class StockIn extends Model
{
    use HasFactory, BelongsToTenant;
    use LogsActivity;

    protected $fillable = [
        'warehouse_id', 
        'transaction_code', 
        'date', 
        'supplier_id', 
        'total', 
        'notes',
        'document_uuid',
        'status',
        'approved_by',
        'approved_at',
    ];

    protected $casts = [
        'date' => 'date',
        'approved_at' => 'datetime',
    ];

    /**
     * Boot method to auto-generate document UUID.
     */
    protected static function booted(): void
    {
        static::creating(function ($model) {
            if (empty($model->document_uuid)) {
                $model->document_uuid = \Illuminate\Support\Str::uuid()->toString();
            }
        });
    }

    public function approver()
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public function warehouse()
    {
        return $this->belongsTo(Warehouse::class);
    }

    public function supplier()
    {
        return $this->belongsTo(Supplier::class);
    }

    public function details()
    {
        return $this->hasMany(StockInDetail::class);
    }
}
