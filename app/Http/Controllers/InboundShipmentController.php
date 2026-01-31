<?php

namespace App\Http\Controllers;

use App\Models\InboundShipment;
use App\Models\PurchaseOrder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class InboundShipmentController extends Controller
{
    public function index()
    {
        $shipments = InboundShipment::with(['purchaseOrders.supplier'])
            ->latest()
            ->paginate(20);

        return view('inbound_shipments.index', compact('shipments'));
    }

    public function create()
    {
        // Get POs that are NOT yet assigned to a shipment
        $purchaseOrders = PurchaseOrder::whereNull('inbound_shipment_id')
            ->whereIn('status', ['approved', 'pending'])
            ->with('supplier')
            ->get();

        return view('inbound_shipments.create', compact('purchaseOrders'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'reference_number' => 'nullable|string|max:255',
            'carrier_name' => 'nullable|string|max:255',
            'eta' => 'nullable|date',
            'purchase_order_ids' => 'required|array',
            'purchase_order_ids.*' => 'exists:purchase_orders,id'
        ]);

        DB::transaction(function () use ($request) {
            $shipment = InboundShipment::create([
                'reference_number' => $request->reference_number,
                'carrier_name' => $request->carrier_name,
                'vessel_flight_number' => $request->vessel_flight_number,
                'origin_port' => $request->origin_port,
                'destination_port' => $request->destination_port,
                'etd' => $request->etd,
                'eta' => $request->eta,
                'status' => 'planned',
                'notes' => $request->notes,
                'created_by' => auth()->id(),
                'company_id' => auth()->user()->company_id,
            ]);

            // Link POs
            PurchaseOrder::whereIn('id', $request->purchase_order_ids)
                ->update(['inbound_shipment_id' => $shipment->id]);
        });

        return redirect()->route('inbound-shipments.index')
            ->with('success', 'Inbound Shipment created successfully.');
    }

    public function show(InboundShipment $inboundShipment)
    {
        $inboundShipment->load(['purchaseOrders.supplier', 'purchaseOrders.details.product']);
        return view('inbound_shipments.show', compact('inboundShipment'));
    }

    public function update(Request $request, InboundShipment $inboundShipment)
    {
        $request->validate([
            'status' => 'required|in:planned,booked,on_water,customs,arrived,received,cancelled',
            'eta' => 'nullable|date',
        ]);

        $inboundShipment->update($request->only(['status', 'eta', 'reference_number', 'notes']));

        return back()->with('success', 'Shipment updated successfully.');
    }

    // Landed Cost Phase 2
    public function storeExpense(Request $request, InboundShipment $inboundShipment)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'amount' => 'required|numeric|min:0',
            'allocation_method' => 'required|in:value,quantity', // Simple v1
        ]);

        $inboundShipment->expenses()->create($request->only(['name', 'amount', 'allocation_method', 'notes']));

        return back()->with('success', 'Expense added successfully.');
    }

    // Phase 1 Unique Feature: One Click Receive (With Landed Cost Calculation)
    public function receive(InboundShipment $inboundShipment)
    {
        // 1. Collect all items from all POs
        $inboundShipment->load(['purchaseOrders.details', 'expenses']);
        
        $allItems = collect();
        foreach ($inboundShipment->purchaseOrders as $po) {
            foreach ($po->details as $detail) {
                $allItems->push([
                    'product_id' => $detail->product_id,
                    'quantity' => $detail->quantity_ordered, // Assuming full receipt for v1
                    'purchase_price' => $detail->unit_price,
                    'total_line_value' => $detail->quantity_ordered * $detail->unit_price
                ]);
            }
        }

        if ($allItems->isEmpty()) {
            return back()->with('error', 'No items to receive.');
        }

        // 2. Calculate Allocation Ratios
        $totalValue = $allItems->sum('total_line_value');
        $totalQuantity = $allItems->sum('quantity');
        $expenses = $inboundShipment->expenses;

        // 3. Create Draft Stock In
        // In a real app, we'd use a Service for this transaction
        $stockIn = DB::transaction(function () use ($inboundShipment, $allItems, $expenses, $totalValue, $totalQuantity) {
            
            // Create Header
            $stockIn = \App\Models\StockIn::create([
                'warehouse_id' => $inboundShipment->purchaseOrders->first()->warehouse_id ?? 1, // Defaulting for v1
                'supplier_id' => $inboundShipment->purchaseOrders->first()->supplier_id,
                'date' => now(),
                'status' => 'draft', // User must review
                'note' => 'Received from Shipment ' . $inboundShipment->shipment_number,
                'created_by' => auth()->id(),
            ]);

            // Create Details with Cost Allocation
            foreach ($allItems as $item) {
                
                $allocatedCost = 0;

                foreach ($expenses as $expense) {
                    if ($expense->allocation_method === 'value' && $totalValue > 0) {
                        $ratio = $item['total_line_value'] / $totalValue;
                        $allocatedCost += ($expense->amount * $ratio) / $item['quantity']; // Cost PER UNIT
                    } elseif ($expense->allocation_method === 'quantity' && $totalQuantity > 0) {
                        $ratio = $item['quantity'] / $totalQuantity;
                        $allocatedCost += ($expense->amount * $ratio) / $item['quantity'];
                    }
                }

                \App\Models\StockInDetail::create([
                    'stock_in_id' => $stockIn->id,
                    'product_id' => $item['product_id'],
                    'quantity' => $item['quantity'],
                    'purchase_price' => $item['purchase_price'],
                    'allocated_landed_cost' => $allocatedCost,
                    'total' => ($item['purchase_price'] * $item['quantity']) // Base total
                ]);
            }
            
            // Mark Shipment as Received
            $inboundShipment->update(['status' => 'received']);
            
            // Mark POs as Received (Simplified)
            $inboundShipment->purchaseOrders()->update(['status' => 'completed']); // Assuming 'completed' exist

            return $stockIn;
        });
        
        return redirect()->route('stock-ins.show', $stockIn)
             ->with('success', 'Shipment received! Landed Costs have been allocated.');
    }
    // Phase 3 Unique Feature: Digital Vault (Documents)
    public function storeDocument(Request $request, InboundShipment $inboundShipment)
    {
        $request->validate([
            'document_type' => 'required|string',
            'file' => 'required|file|mimes:pdf,jpg,png,doc,docx,xls,xlsx|max:10240', // 10MB
            'title' => 'nullable|string|max:255',
        ]);

        $file = $request->file('file');
        // Store in 'public' disk: storage/app/public/documents/shipments/ID/
        $path = $file->store('documents/shipments/' . $inboundShipment->id, 'public');

        $inboundShipment->documents()->create([
            'company_id' => auth()->user()->company_id,
            'title' => $request->title ?? $file->getClientOriginalName(),
            'document_type' => $request->document_type,
            'file_name' => $file->getClientOriginalName(),
            'file_path' => $path,
            'file_disk' => 'public',
            'mime_type' => $file->getMimeType(),
            'file_size' => $file->getSize(),
            'uploaded_by' => auth()->id(),
            'issue_date' => now(), // Default
        ]);

        return back()->with('success', 'Document uploaded successfully.');
    }
}
