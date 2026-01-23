<?php

namespace App\Services;

use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\App;

class PdfService
{
    public function generateInvoice($order)
    {
        // Switch locale to order's document language
        $originalLocale = App::getLocale();
        if ($order->document_language) {
            App::setLocale($order->document_language);
        }

        $pdf = Pdf::loadView('pdf.invoice', compact('order'));
        
        // Build filename
        $filename = 'Invoice-' . $order->so_number . '.pdf';

        // Revert locale
        App::setLocale($originalLocale);

        return $pdf->stream($filename);
    }

    public function generatePackingList($order)
    {
        // Packing List might be internal (Warehouse) so maybe keep user locale?
        // But requirements say "Packing Lists can be generated in English even if the user’s UI is set to Indonesian"
        $originalLocale = App::getLocale();
        if ($order->document_language) {
            App::setLocale($order->document_language);
        }

        $pdf = Pdf::loadView('pdf.packing-list', compact('order'));
        $filename = 'PackingList-' . $order->so_number . '.pdf';

        App::setLocale($originalLocale);

        return $pdf->stream($filename);
    }

    // ... other methods
}
