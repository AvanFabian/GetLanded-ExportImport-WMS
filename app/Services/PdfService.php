<?php

namespace App\Services;

use App\Models\Company;
use App\Models\SalesOrder;
use App\Models\StockIn;
use App\Models\StockOut;
use Barryvdh\DomPDF\Facade\Pdf;
use SimpleSoftwareIO\QrCode\Facades\QrCode;

class PdfService
{
    protected Company $company;

    public function __construct()
    {
        $this->company = auth()->user()->company;
    }

    /**
     * Set company for PDF generation (useful for testing).
     */
    public function setCompany(Company $company): self
    {
        $this->company = $company;
        return $this;
    }

    /**
     * Generate Commercial Invoice PDF.
     */
    public function generateInvoice(SalesOrder $salesOrder): \Barryvdh\DomPDF\PDF
    {
        $salesOrder->load(['customer', 'items.product', 'stockOut.approver']);
        
        $qrCode = $this->generateQrCode($salesOrder->stockOut?->document_uuid ?? $salesOrder->so_number);
        
        $data = [
            'company' => $this->company,
            'order' => $salesOrder,
            'qrCode' => $qrCode,
            'documentType' => 'Commercial Invoice',
            'documentNumber' => $salesOrder->so_number,
            'approver' => $salesOrder->stockOut?->approver,
            'approvedAt' => $salesOrder->stockOut?->approved_at,
            'isVoided' => $salesOrder->stockOut?->status === 'VOIDED',
        ];

        return Pdf::loadView('pdf.commercial-invoice', $data)
            ->setPaper('a4', 'portrait');
    }

    /**
     * Generate Packing List PDF (NO PRICING DATA).
     */
    public function generatePackingList(StockOut $stockOut): \Barryvdh\DomPDF\PDF
    {
        $stockOut->load(['warehouse', 'details.batch.product', 'approver']);
        
        $qrCode = $this->generateQrCode($stockOut->document_uuid);
        
        $data = [
            'company' => $this->company,
            'stockOut' => $stockOut,
            'qrCode' => $qrCode,
            'documentType' => 'Packing List',
            'documentNumber' => $stockOut->transaction_code,
            'approver' => $stockOut->approver,
            'approvedAt' => $stockOut->approved_at,
            'isVoided' => $stockOut->status === 'VOIDED',
        ];

        return Pdf::loadView('pdf.packing-list', $data)
            ->setPaper('a4', 'portrait');
    }

    /**
     * Generate Warehouse Receipt PDF.
     */
    public function generateWarehouseReceipt(StockIn $stockIn): \Barryvdh\DomPDF\PDF
    {
        $stockIn->load(['warehouse', 'supplier', 'details.batch.product', 'approver']);
        
        $qrCode = $this->generateQrCode($stockIn->document_uuid);
        
        $data = [
            'company' => $this->company,
            'stockIn' => $stockIn,
            'qrCode' => $qrCode,
            'documentType' => 'Warehouse Receipt',
            'documentNumber' => $stockIn->transaction_code,
            'approver' => $stockIn->approver,
            'approvedAt' => $stockIn->approved_at,
            'isVoided' => $stockIn->status === 'VOIDED',
        ];

        return Pdf::loadView('pdf.warehouse-receipt', $data)
            ->setPaper('a4', 'portrait');
    }

    /**
     * Generate high-quality QR code as base64 PNG.
     */
    protected function generateQrCode(string $content): string
    {
        return 'data:image/png;base64,' . base64_encode(
            QrCode::format('png')
                ->size(150)
                ->errorCorrection('H')
                ->generate($content)
        );
    }

    /**
     * Check if document contains pricing data (for testing).
     */
    public static function containsPricingData(string $html): bool
    {
        $pricingPatterns = [
            '/\$[\d,]+\.?\d*/i',           // $123.45
            '/USD\s*[\d,]+/i',              // USD 123
            '/IDR\s*[\d,]+/i',              // IDR 123
            '/price/i',
            '/cost/i',
            '/subtotal/i',
            '/total.*amount/i',
            '/unit.*price/i',
        ];

        foreach ($pricingPatterns as $pattern) {
            if (preg_match($pattern, $html)) {
                return true;
            }
        }

        return false;
    }
}
