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
    public function receive(Request $request, InboundShipment $inboundShipment)
    {
        $request->validate([
            'items' => 'required|array',
            'items.*' => 'required|integer|min:1',
        ]);

        $inboundShipment->load(['purchaseOrders.details.product', 'expenses']);
        
        // Flatten all PO details for easy access
        $poDetails = collect();
        $poLookup = []; // product_id → PurchaseOrder (for supplier_id)
        foreach ($inboundShipment->purchaseOrders as $po) {
            foreach ($po->details as $detail) {
                $poDetails->put($detail->product_id, $detail);
                $poLookup[$detail->product_id] = $po;
            }
        }

        DB::transaction(function () use ($inboundShipment, $request, $poDetails, $poLookup) {
            
            $inputItems = $request->items;
            $itemsToProcess = [];
            $totalValue = 0;
            $totalQuantity = 0;

            // 1. Validate & Prepare Data
            foreach ($inputItems as $productId => $qtyToReceive) {
                if (!$poDetails->has($productId)) {
                    continue; // Skip items not in this shipment's POs
                }

                $detail = $poDetails->get($productId);
                $remaining = $detail->quantity_ordered - $detail->quantity_received;

                if ($qtyToReceive > $remaining) {
                    throw new \Exception("Cannot receive {$qtyToReceive} for Product #{$productId}. Only {$remaining} remaining.");
                }

                // Convert unit_price to IDR if PO is in foreign currency
                $po = $poLookup[$productId] ?? null;
                $unitPriceIdr = $detail->unit_price;
                if ($po && $po->currency_code && strtoupper($po->currency_code) !== 'IDR') {
                    $unitPriceIdr = $detail->unit_price * (float) ($po->exchange_rate_at_transaction ?? 1);
                }

                $itemsToProcess[] = [
                    'product_id' => $productId,
                    'quantity' => $qtyToReceive,
                    'purchase_price' => $detail->unit_price,       // Original currency
                    'purchase_price_idr' => $unitPriceIdr,         // IDR equivalent
                    'detail_model' => $detail,
                    'total_line_value' => $qtyToReceive * $unitPriceIdr,
                    'unit_price' => $unitPriceIdr,                 // For LandedCostService
                ];

                $totalValue += ($qtyToReceive * $unitPriceIdr);
                $totalQuantity += $qtyToReceive;
            }

            if (empty($itemsToProcess)) {
                throw new \Exception("No valid items to receive.");
            }

            // 2. Create Stock In Header
            $stockIn = \App\Models\StockIn::create([
                'warehouse_id' => $inboundShipment->purchaseOrders->first()->warehouse_id ?? 1,
                'supplier_id' => $inboundShipment->purchaseOrders->first()->supplier_id,
                'date' => now(),
                'status' => 'completed',
                'transaction_code' => 'IN-' . date('YmdHis'),
                'note' => 'Received from Shipment ' . $inboundShipment->shipment_number,
                'created_by' => auth()->id(),
                'total' => $totalValue
            ]);

            // 3. Allocate Landed Costs via Service
            $landedCostService = app(\App\Services\LandedCostService::class);
            $allocations = $landedCostService->allocate($itemsToProcess, $inboundShipment->expenses);

            // 4. Process Items — Create StockInDetail + Batch + Update WAC
            foreach ($itemsToProcess as $item) {
                $pid = $item['product_id'];
                $allocatedCostPerUnit = $allocations[$pid] ?? 0;

                // Create Stock In Detail
                \App\Models\StockInDetail::create([
                    'stock_in_id' => $stockIn->id,
                    'product_id' => $pid,
                    'quantity' => $item['quantity'],
                    'purchase_price' => $item['purchase_price_idr'],
                    'allocated_landed_cost' => $allocatedCostPerUnit,
                    'total' => ($item['purchase_price_idr'] + $allocatedCostPerUnit) * $item['quantity'],
                ]);

                // Create Batch with TRUE cost (purchase price + landed cost)
                $po = $poLookup[$pid] ?? null;
                \App\Models\Batch::create([
                    'company_id' => auth()->user()->company_id,
                    'product_id' => $pid,
                    'batch_number' => $inboundShipment->shipment_number . '-' . $pid,
                    'cost_price' => round($item['purchase_price_idr'] + $allocatedCostPerUnit, 2),
                    'supplier_id' => $po?->supplier_id,
                    'stock_in_id' => $stockIn->id,
                    'status' => 'active',
                    'notes' => 'Landed cost: ' . number_format($allocatedCostPerUnit, 2) . '/unit',
                ]);

                // Update Weighted Average Cost
                $landedCostService->updateWAC($pid, $item['quantity'], $item['purchase_price_idr'] + $allocatedCostPerUnit);

                // Update PO Detail Quantity Received
                $item['detail_model']->increment('quantity_received', $item['quantity']);
                
                // Update Stock in Warehouse (Pivot)
                $warehouseId = $stockIn->warehouse_id;
                $product = \App\Models\Product::find($pid);
                
                $pivot = $product->warehouses()->where('warehouse_id', $warehouseId)->first();
                if ($pivot) {
                    $product->warehouses()->updateExistingPivot($warehouseId, [
                        'stock' => $pivot->pivot->stock + $item['quantity']
                    ]);
                } else {
                    $product->warehouses()->attach($warehouseId, ['stock' => $item['quantity']]);
                }
            }

            // 5. Update PO Statuses
            foreach ($inboundShipment->purchaseOrders as $po) {
                $isFullyReceived = true;
                $isPartiallyReceived = false;

                foreach ($po->details as $detail) {
                    $detail->refresh();
                    if ($detail->quantity_received < $detail->quantity_ordered) {
                        $isFullyReceived = false;
                    }
                    if ($detail->quantity_received > 0) {
                        $isPartiallyReceived = true;
                    }
                }

                if ($isFullyReceived) {
                    $po->update(['status' => 'completed']);
                } elseif ($isPartiallyReceived) {
                    $po->update(['status' => 'partially_received']);
                }
            }
            
            // Mark Shipment as Received
            $inboundShipment->update(['status' => 'received']);
        });

        return redirect()->route('stock-ins.index')
             ->with('success', 'Shipment received! Landed costs allocated and batches created.');
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
