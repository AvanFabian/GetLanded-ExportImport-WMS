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
        foreach ($inboundShipment->purchaseOrders as $po) {
            foreach ($po->details as $detail) {
                $poDetails->put($detail->product_id, $detail);
            }
        }

        DB::transaction(function () use ($inboundShipment, $request, $poDetails) {
            
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

                $itemsToProcess[] = [
                    'product_id' => $productId,
                    'quantity' => $qtyToReceive,
                    'purchase_price' => $detail->unit_price,
                    'detail_model' => $detail,
                    'total_line_value' => $qtyToReceive * $detail->unit_price
                ];

                $totalValue += ($qtyToReceive * $detail->unit_price);
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
                'status' => 'completed', // Direct complete for now
                'transaction_code' => 'IN-' . date('YmdHis'), // Simple generator
                'note' => 'Received from Shipment ' . $inboundShipment->shipment_number,
                'created_by' => auth()->id(),
                'total' => $totalValue // Base total
            ]);

            $expenses = $inboundShipment->expenses;

            // 3. Process Items
            foreach ($itemsToProcess as $item) {
                
                // Calculate Allocated Cost
                $allocatedCost = 0;
                foreach ($expenses as $expense) {
                    if ($expense->allocation_method === 'value' && $totalValue > 0) {
                        $ratio = $item['total_line_value'] / $totalValue;
                        $allocatedCost += ($expense->amount * $ratio) / $item['quantity'];
                    } elseif ($expense->allocation_method === 'quantity' && $totalQuantity > 0) {
                        $ratio = $item['quantity'] / $totalQuantity;
                        $allocatedCost += ($expense->amount * $ratio) / $item['quantity'];
                    }
                }

                // Create Stock In Detail
                \App\Models\StockInDetail::create([
                    'stock_in_id' => $stockIn->id,
                    'product_id' => $item['product_id'],
                    'quantity' => $item['quantity'],
                    'purchase_price' => $item['purchase_price'],
                    'allocated_landed_cost' => $allocatedCost,
                    'total' => ($item['purchase_price'] * $item['quantity'])
                ]);

                // Update WAC
                $product = \App\Models\Product::find($item['product_id']);
                $currentStock = $product->total_stock; 
                $currentCost = $product->cost;
                $incomingQty = $item['quantity'];
                $incomingCost = $item['purchase_price'] + $allocatedCost; 
                $totalQty = $currentStock + $incomingQty;
                
                if ($totalQty > 0) {
                    $newWAC = (($currentStock * $currentCost) + ($incomingQty * $incomingCost)) / $totalQty;
                    $product->update(['weighted_average_cost' => round($newWAC, 2)]);
                }

                // Update PO Detail Quantity Received
                $item['detail_model']->increment('quantity_received', $item['quantity']);
                
                // Update Stock in Warehouse (Pivot) - Simplified/Assuming Service does this usually
                // For now, let's look for a method explicitly or do raw update if needed.
                // Assuming StockIn creation doesn't auto-update stock in this codebase based on previous view.
                // We need to increment the stock in product_warehouse pivot.
                $warehouseId = $stockIn->warehouse_id;
                $product = \App\Models\Product::find($item['product_id']);
                
                $pivot = $product->warehouses()->where('warehouse_id', $warehouseId)->first();
                if ($pivot) {
                    $product->warehouses()->updateExistingPivot($warehouseId, [
                        'stock' => $pivot->pivot->stock + $item['quantity']
                    ]);
                } else {
                    $product->warehouses()->attach($warehouseId, ['stock' => $item['quantity']]);
                }
            }

            // 4. Update PO Statuses
            foreach ($inboundShipment->purchaseOrders as $po) {
                $isFullyReceived = true;
                $isPartiallyReceived = false;

                foreach ($po->details as $detail) {
                    $detail->refresh(); // getting updated qty
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
            
            // Mark Shipment as Received (Closed)
            $inboundShipment->update(['status' => 'received']);
        });

        return redirect()->route('stock-ins.index')
             ->with('success', 'Shipment processed successfully.');
        
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
