<?php

namespace App\Services;

use App\Models\Document;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

/**
 * DocumentService
 * 
 * Handles file uploads with UUID-based tenant-isolated paths.
 * Ensures physical storage separation between tenants.
 * 
 * Path format: uploads/{company_uuid}/{module}/{year}/{month}/{uuid}.{ext}
 */
class DocumentService
{
    protected string $disk;

    public function __construct()
    {
        $this->disk = config('filesystems.default', 'local');
    }

    /**
     * Store a document with tenant-isolated path.
     *
     * @param UploadedFile $file
     * @param string $module e.g., 'documents', 'batches', 'invoices'
     * @param array $metadata Additional document metadata
     * @return Document
     */
    public function store(
        UploadedFile $file,
        string $module = 'documents',
        array $metadata = []
    ): Document {
        $user = auth()->user();
        $company = $user->company;
        
        if (!$company) {
            throw new \RuntimeException('User must belong to a company to upload documents.');
        }

        // Generate UUID-based tenant-isolated path
        $companyUuid = $company->uuid;
        $year = now()->format('Y');
        $month = now()->format('m');
        $fileUuid = Str::uuid();
        $extension = $file->getClientOriginalExtension();
        
        $filePath = "uploads/{$companyUuid}/{$module}/{$year}/{$month}/{$fileUuid}.{$extension}";

        // Store the file
        Storage::disk($this->disk)->put($filePath, file_get_contents($file));

        // Create document record
        return Document::create([
            'company_id' => $company->id,
            'batch_id' => $metadata['batch_id'] ?? null,
            'sales_order_id' => $metadata['sales_order_id'] ?? null,
            'purchase_order_id' => $metadata['purchase_order_id'] ?? null,
            'document_type' => $metadata['document_type'] ?? Document::TYPE_OTHER,
            'title' => $metadata['title'] ?? $file->getClientOriginalName(),
            'file_name' => $file->getClientOriginalName(),
            'file_path' => $filePath,
            'file_disk' => $this->disk,
            'mime_type' => $file->getMimeType(),
            'file_size' => $file->getSize(),
            'issue_date' => $metadata['issue_date'] ?? null,
            'expiry_date' => $metadata['expiry_date'] ?? null,
            'document_number' => $metadata['document_number'] ?? null,
            'notes' => $metadata['notes'] ?? null,
            'uploaded_by' => $user->id,
        ]);
    }

    /**
     * Delete a document and its file.
     *
     * @param Document $document
     * @return bool
     */
    public function delete(Document $document): bool
    {
        // Delete file from storage
        Storage::disk($document->file_disk)->delete($document->file_path);

        // Delete database record
        return $document->delete();
    }

    /**
     * Get download response for a document.
     *
     * @param Document $document
     * @return \Symfony\Component\HttpFoundation\StreamedResponse
     */
    public function download(Document $document)
    {
        $disk = Storage::disk($document->file_disk);

        if (!$disk->exists($document->file_path)) {
            abort(404, 'File not found');
        }

        return $disk->download($document->file_path, $document->file_name);
    }

    /**
     * Get temporary URL for S3 documents.
     *
     * @param Document $document
     * @param int $minutes
     * @return string
     */
    public function getTemporaryUrl(Document $document, int $minutes = 30): string
    {
        $disk = Storage::disk($document->file_disk);

        if ($document->file_disk === 's3') {
            return $disk->temporaryUrl($document->file_path, now()->addMinutes($minutes));
        }

        return $disk->url($document->file_path);
    }
}
