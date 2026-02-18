<?php

namespace App\Http\Controllers;

use App\Models\OutboundShipment;
use App\Models\SalesOrder;
use App\Services\PdfService;
use Illuminate\Http\Request;

class OutboundShipmentController extends Controller
{
    public function __construct(
        protected PdfService $pdfService
    ) {}
    public function index(Request $request)
    {
        $companyId = auth()->user()->company_id;

        $shipments = OutboundShipment::where('company_id', $companyId)
            ->with(['salesOrder.customer'])
            ->when($request->search, function ($q, $search) {
                $q->where('shipment_number', 'like', "%{$search}%")
                  ->orWhere('bill_of_lading', 'like', "%{$search}%")
                  ->orWhere('vessel_name', 'like', "%{$search}%")
                  ->orWhereHas('salesOrder', fn ($q) => $q->where('so_number', 'like', "%{$search}%"));
            })
            ->when($request->status, fn ($q, $s) => $q->where('status', $s))
            ->latest('shipment_date')
            ->paginate(20)
            ->withQueryString();

        return view('outbound-shipments.index', compact('shipments'));
    }

    public function create()
    {
        $companyId = auth()->user()->company_id;

        $salesOrders = SalesOrder::where('company_id', $companyId)
            ->whereIn('status', ['confirmed', 'processing'])
            ->with('customer')
            ->orderBy('order_date', 'desc')
            ->get();

        $incoterms = OutboundShipment::INCOTERMS;

        return view('outbound-shipments.create', compact('salesOrders', 'incoterms'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'sales_order_id' => 'required|exists:sales_orders,id',
            'shipment_date' => 'required|date',
            'estimated_arrival' => 'nullable|date|after:shipment_date',
            'carrier_name' => 'nullable|string|max:255',
            'vessel_name' => 'nullable|string|max:255',
            'voyage_number' => 'nullable|string|max:255',
            'bill_of_lading' => 'nullable|string|max:255',
            'booking_number' => 'nullable|string|max:255',
            'port_of_loading' => 'nullable|string|max:255',
            'port_of_discharge' => 'nullable|string|max:255',
            'destination_country' => 'nullable|string|max:255',
            'incoterm' => 'nullable|string|max:10',
            'freight_cost' => 'nullable|numeric|min:0',
            'insurance_cost' => 'nullable|numeric|min:0',
            'currency_code' => 'nullable|string|size:3',
            'notes' => 'nullable|string',
        ]);

        $companyId = auth()->user()->company_id;
        $validated['company_id'] = $companyId;
        $validated['shipment_number'] = OutboundShipment::generateShipmentNumber($companyId);
        $validated['status'] = 'draft';

        $shipment = OutboundShipment::create($validated);

        return redirect()->route('outbound-shipments.show', $shipment)
            ->with('success', __('Outbound shipment created successfully.'));
    }

    public function show(OutboundShipment $outboundShipment)
    {
        $outboundShipment->load(['salesOrder.customer', 'salesOrder.items.product', 'containers.items.product', 'customsDeclaration', 'expenses']);

        return view('outbound-shipments.show', compact('outboundShipment'));
    }

    public function updateStatus(Request $request, OutboundShipment $outboundShipment)
    {
        $validated = $request->validate([
            'status' => 'required|in:' . implode(',', array_keys(OutboundShipment::STATUSES)),
            'actual_arrival' => 'nullable|date',
        ]);

        $outboundShipment->update($validated);

        return back()->with('success', __('Shipment status updated.'));
    }

    /**
     * Add an expense to an outbound shipment.
     */
    public function addExpense(Request $request, OutboundShipment $outboundShipment)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'amount' => 'required|numeric|min:0.01',
            'currency_code' => 'required|string|size:3',
            'notes' => 'nullable|string',
        ]);

        $outboundShipment->expenses()->create($validated);

        return back()->with('success', __('Expense added.'));
    }

    /**
     * Remove an expense from an outbound shipment.
     */
    public function removeExpense(OutboundShipment $outboundShipment, \App\Models\ShipmentExpense $expense)
    {
        $expense->delete();

        return back()->with('success', __('Expense removed.'));
    }

    /**
     * Download Export Commercial Invoice PDF.
     */
    public function downloadInvoice(OutboundShipment $outboundShipment)
    {
        $pdf = $this->pdfService->generateExportInvoice($outboundShipment);

        return $pdf->download("commercial-invoice-{$outboundShipment->shipment_number}.pdf");
    }

    /**
     * Download Export Packing List PDF.
     */
    public function downloadPackingList(OutboundShipment $outboundShipment)
    {
        $pdf = $this->pdfService->generateExportPackingList($outboundShipment);

        return $pdf->download("packing-list-{$outboundShipment->shipment_number}.pdf");
    }
}
