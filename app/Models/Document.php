<?php

namespace App\Models;

use App\Models\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Storage;

/**
 * Document Model
 * 
 * Stores references to files uploaded to S3/local storage.
 * Used for trade documents (COA, Phyto, Lab Reports, BOL).
 */
class Document extends Model
{
    use SoftDeletes, BelongsToTenant;

    // Document types
    public const TYPE_COA = 'COA';
    public const TYPE_PHYTO = 'PHYTO';
    public const TYPE_LAB_REPORT = 'LAB_REPORT';
    public const TYPE_BILL_OF_LADING = 'BILL_OF_LADING';
    public const TYPE_COMMERCIAL_INVOICE = 'COMMERCIAL_INVOICE';
    public const TYPE_PACKING_LIST = 'PACKING_LIST';
    public const TYPE_CERTIFICATE_OF_ORIGIN = 'CERTIFICATE_OF_ORIGIN';
    public const TYPE_OTHER = 'OTHER';

    protected $fillable = [
        'company_id',
        'batch_id',
        'sales_order_id',
        'purchase_order_id',
        'inbound_shipment_id',
        'document_type',
        'title',
        'file_name',
        'file_path',
        'file_disk',
        'mime_type',
        'file_size',
        'issue_date',
        'expiry_date',
        'document_number',
        'notes',
        'uploaded_by',
    ];

    protected $casts = [
        'issue_date' => 'date',
        'expiry_date' => 'date',
        'file_size' => 'integer',
    ];

    // =====================================
    // RELATIONSHIPS
    // =====================================

    public function batch()
    {
        return $this->belongsTo(Batch::class);
    }

    public function salesOrder()
    {
        return $this->belongsTo(SalesOrder::class);
    }

    public function purchaseOrder()
    {
        return $this->belongsTo(PurchaseOrder::class);
    }

    public function inboundShipment()
    {
        return $this->belongsTo(InboundShipment::class);
    }

    public function uploader()
    {
        return $this->belongsTo(User::class, 'uploaded_by');
    }

    // =====================================
    // ACCESSORS
    // =====================================

    /**
     * Get the URL to download/view the file.
     */
    public function getUrlAttribute(): string
    {
        $disk = Storage::disk($this->file_disk);
        
        if ($this->file_disk === 's3') {
            // Generate temporary signed URL for S3
            return $disk->temporaryUrl($this->file_path, now()->addMinutes(30));
        }
        
        return $disk->url($this->file_path);
    }

    /**
     * Get human-readable file size.
     */
    public function getFileSizeHumanAttribute(): string
    {
        $bytes = $this->file_size;
        $units = ['B', 'KB', 'MB', 'GB'];
        
        for ($i = 0; $bytes > 1024 && $i < count($units) - 1; $i++) {
            $bytes /= 1024;
        }
        
        return round($bytes, 2) . ' ' . $units[$i];
    }

    /**
     * Check if document is expired.
     */
    public function getIsExpiredAttribute(): bool
    {
        if (!$this->expiry_date) {
            return false;
        }
        return $this->expiry_date->isPast();
    }

    // =====================================
    // SCOPES
    // =====================================

    public function scopeOfType($query, string $type)
    {
        return $query->where('document_type', $type);
    }

    public function scopeForBatch($query, int $batchId)
    {
        return $query->where('batch_id', $batchId);
    }

    public function scopeNotExpired($query)
    {
        return $query->where(function ($q) {
            $q->whereNull('expiry_date')
              ->orWhere('expiry_date', '>', now());
        });
    }

    // =====================================
    // STATIC HELPERS
    // =====================================

    /**
     * Get available document types.
     */
    public static function getTypes(): array
    {
        return [
            self::TYPE_COA => 'Certificate of Analysis',
            self::TYPE_PHYTO => 'Phytosanitary Certificate',
            self::TYPE_LAB_REPORT => 'Lab Report',
            self::TYPE_BILL_OF_LADING => 'Bill of Lading',
            self::TYPE_COMMERCIAL_INVOICE => 'Commercial Invoice',
            self::TYPE_PACKING_LIST => 'Packing List',
            self::TYPE_CERTIFICATE_OF_ORIGIN => 'Certificate of Origin',
            self::TYPE_OTHER => 'Other',
        ];
    }
}
