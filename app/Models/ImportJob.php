<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Models\Traits\BelongsToTenant;

class ImportJob extends Model
{
    use HasFactory, BelongsToTenant;

    protected $fillable = [
        'company_id',
        'type',
        'file_path',
        'column_mapping',
        'status',
        'total_rows',
        'processed_rows',
        'failed_rows',
        'error_log',
        'created_by',
    ];

    protected $casts = [
        'column_mapping' => 'array',
        'error_log' => 'array',
    ];

    const TYPE_PRODUCTS = 'products';
    const TYPE_CUSTOMERS = 'customers';
    const TYPE_SUPPLIERS = 'suppliers';
    const TYPE_STOCK = 'stock';

    const STATUS_PENDING = 'pending';
    const STATUS_MAPPING = 'mapping';
    const STATUS_PROCESSING = 'processing';
    const STATUS_COMPLETED = 'completed';
    const STATUS_FAILED = 'failed';

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function getProgressPercentageAttribute(): int
    {
        if ($this->total_rows === 0) return 0;
        return (int) (($this->processed_rows / $this->total_rows) * 100);
    }

    public function incrementProcessed(): void
    {
        $this->increment('processed_rows');
    }

    public function incrementFailed(string $error, ?int $row = null): void
    {
        $this->increment('failed_rows');
        $errors = $this->error_log ?? [];
        $errors[] = [
            'row' => $row,
            'error' => $error,
            'time' => now()->toISOString(),
        ];
        $this->update(['error_log' => $errors]);
    }

    public function complete(): void
    {
        $this->update(['status' => self::STATUS_COMPLETED]);
    }

    public function fail(): void
    {
        $this->update(['status' => self::STATUS_FAILED]);
    }
}
