<?php

namespace App\Http\Controllers;

use App\Models\SalesOrder;
use App\Models\StockIn;
use App\Models\StockOut;
use App\Services\PdfService;
use Illuminate\Http\Request;

class PdfController extends Controller
{
    protected PdfService $pdfService;

    public function __construct(PdfService $pdfService)
    {
        $this->pdfService = $pdfService;
    }

    /**
     * Download Commercial Invoice PDF.
     * Requires 'invoice.view' permission (finance role).
     */
    public function invoice(SalesOrder $salesOrder)
    {
        $this->authorize('viewInvoice', $salesOrder);
        
        $pdf = $this->pdfService->generateInvoice($salesOrder);
        
        return $pdf->download("invoice-{$salesOrder->so_number}.pdf");
    }

    /**
     * Download Packing List PDF.
     */
    public function packingList(SalesOrder $salesOrder)
    {
        $this->authorize('view', $salesOrder);
        
        if (!$salesOrder->stock_out_id) {
            abort(404, 'No stock out linked to this sales order.');
        }
        
        $stockOut = StockOut::findOrFail($salesOrder->stock_out_id);
        $pdf = $this->pdfService->generatePackingList($stockOut);
        
        return $pdf->download("packing-list-{$stockOut->transaction_code}.pdf");
    }

    /**
     * Download Packing List directly from StockOut.
     */
    public function stockOutPackingList(StockOut $stockOut)
    {
        $pdf = $this->pdfService->generatePackingList($stockOut);
        
        return $pdf->download("packing-list-{$stockOut->transaction_code}.pdf");
    }

    /**
     * Download Warehouse Receipt PDF.
     */
    public function warehouseReceipt(StockIn $stockIn)
    {
        $pdf = $this->pdfService->generateWarehouseReceipt($stockIn);
        
        return $pdf->download("receipt-{$stockIn->transaction_code}.pdf");
    }

    /**
     * Preview PDF in browser (optional).
     * Requires 'invoice.view' permission (finance role).
     */
    public function previewInvoice(SalesOrder $salesOrder)
    {
        $this->authorize('viewInvoice', $salesOrder);
        
        $pdf = $this->pdfService->generateInvoice($salesOrder);
        
        return $pdf->stream("invoice-{$salesOrder->so_number}.pdf");
    }
}
