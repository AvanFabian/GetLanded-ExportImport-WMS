<?php

namespace App\Http\Controllers;

use App\Models\PurchaseOrder;
use App\Models\PurchaseOrderDetail;
use App\Models\Warehouse;
use App\Models\Supplier;
use App\Models\Product;
use App\Models\StockIn;
use App\Models\StockInDetail;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PurchaseOrderController extends Controller
{
    public function index(Request $request)
    {
        $query = PurchaseOrder::with(['warehouse', 'supplier', 'createdBy']);

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('supplier_id')) {
            $query->where('supplier_id', $request->supplier_id);
        }

        if ($request->filled('warehouse_id')) {
            $query->where('warehouse_id', $request->warehouse_id);
        }

        if ($request->filled('q')) {
            $query->where('po_number', 'like', '%' . $request->q . '%');
        }

        $purchaseOrders = $query->latest()->paginate(15);
        $warehouses = Warehouse::where('is_active', true)->get();
        $suppliers = Supplier::all();

        return view('purchase-orders.index', compact('purchaseOrders', 'warehouses', 'suppliers'));
    }

    public function create()
    {
        $warehouses = Warehouse::where('is_active', true)->get();
        $suppliers = Supplier::all();

        return view('purchase-orders.create', compact('warehouses', 'suppliers'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'warehouse_id' => 'required|exists:warehouses,id',
            'supplier_id' => 'required|exists:suppliers,id',
            'order_date' => 'required|date',
            'expected_delivery_date' => 'nullable|date|after_or_equal:order_date',
            'notes' => 'nullable|string',
            'products' => 'required|array|min:1',
            'products.*.product_id' => 'required|exists:products,id',
            'products.*.quantity' => 'required|integer|min:1',
            'products.*.unit_price' => 'required|numeric|min:0',
        ]);

        try {
            DB::beginTransaction();

            $po = PurchaseOrder::create([
                'po_number' => PurchaseOrder::generatePoNumber(),
                'warehouse_id' => $validated['warehouse_id'],
                'supplier_id' => $validated['supplier_id'],
                'order_date' => $validated['order_date'],
                'expected_delivery_date' => $validated['expected_delivery_date'] ?? null,
                'status' => 'draft',
                'notes' => $validated['notes'] ?? null,
                'created_by' => auth()->id(),
            ]);

            $totalAmount = 0;

            foreach ($validated['products'] as $item) {
                $subtotal = $item['quantity'] * $item['unit_price'];
                $totalAmount += $subtotal;

                PurchaseOrderDetail::create([
                    'purchase_order_id' => $po->id,
                    'product_id' => $item['product_id'],
                    'quantity_ordered' => $item['quantity'],
                    'unit_price' => $item['unit_price'],
                    'subtotal' => $subtotal,
                ]);
            }

            $po->update(['total_amount' => $totalAmount]);

            DB::commit();

            return redirect()->route('purchase-orders.show', $po)
                ->with('success', __('purchase_order.create_success'));
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', __('purchase_order.create_error') . $e->getMessage())->withInput();
        }
    }

    public function show(PurchaseOrder $purchaseOrder)
    {
        $purchaseOrder->load(['warehouse', 'supplier', 'details.product', 'createdBy', 'approvedBy']);

        return view('purchase-orders.show', compact('purchaseOrder'));
    }

    public function edit(PurchaseOrder $purchaseOrder)
    {
        if (!$purchaseOrder->canBeEdited()) {
            return back()->with('error', __('purchase_order.edit_error_approved'));
        }

        $warehouses = Warehouse::where('is_active', true)->get();
        $suppliers = Supplier::all();

        return view('purchase-orders.edit', compact('purchaseOrder', 'warehouses', 'suppliers'));
    }

    public function update(Request $request, PurchaseOrder $purchaseOrder)
    {
        if (!$purchaseOrder->canBeEdited()) {
            return back()->with('error', __('purchase_order.edit_error_approved'));
        }

        $validated = $request->validate([
            'warehouse_id' => 'required|exists:warehouses,id',
            'supplier_id' => 'required|exists:suppliers,id',
            'order_date' => 'required|date',
            'expected_delivery_date' => 'nullable|date|after_or_equal:order_date',
            'notes' => 'nullable|string',
            'products' => 'required|array|min:1',
            'products.*.product_id' => 'required|exists:products,id',
            'products.*.quantity' => 'required|integer|min:1',
            'products.*.unit_price' => 'required|numeric|min:0',
        ]);

        try {
            DB::beginTransaction();

            $purchaseOrder->update([
                'warehouse_id' => $validated['warehouse_id'],
                'supplier_id' => $validated['supplier_id'],
                'order_date' => $validated['order_date'],
                'expected_delivery_date' => $validated['expected_delivery_date'] ?? null,
                'notes' => $validated['notes'] ?? null,
            ]);

            // Delete old details
            $purchaseOrder->details()->delete();

            $totalAmount = 0;

            foreach ($validated['products'] as $item) {
                $subtotal = $item['quantity'] * $item['unit_price'];
                $totalAmount += $subtotal;

                PurchaseOrderDetail::create([
                    'purchase_order_id' => $purchaseOrder->id,
                    'product_id' => $item['product_id'],
                    'quantity_ordered' => $item['quantity'],
                    'unit_price' => $item['unit_price'],
                    'subtotal' => $subtotal,
                ]);
            }

            $purchaseOrder->update(['total_amount' => $totalAmount]);

            DB::commit();

            return redirect()->route('purchase-orders.show', $purchaseOrder)
                ->with('success', __('purchase_order.update_success'));
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', __('purchase_order.update_error') . $e->getMessage())->withInput();
        }
    }

    public function destroy(PurchaseOrder $purchaseOrder)
    {
        if (!$purchaseOrder->canBeEdited()) {
            return back()->with('error', __('purchase_order.delete_error_generated'));
        }

        try {
            $purchaseOrder->delete();
            return redirect()->route('purchase-orders.index')
                ->with('success', __('purchase_order.delete_success'));
        } catch (\Exception $e) {
            return back()->with('error', __('purchase_order.delete_error') . $e->getMessage());
        }
    }

    public function submit(PurchaseOrder $purchaseOrder)
    {
        if ($purchaseOrder->status !== 'draft') {
            return back()->with('error', __('purchase_order.submit_error_status'));
        }

        $purchaseOrder->update(['status' => 'pending']);

        return back()->with('success', __('purchase_order.submit_success'));
    }

    public function approve(PurchaseOrder $purchaseOrder)
    {
        if (!$purchaseOrder->canBeApproved()) {
            return back()->with('error', __('purchase_order.approve_error'));
        }

        $purchaseOrder->update([
            'status' => 'approved',
            'approved_by' => auth()->id(),
            'approved_at' => now(),
        ]);

        return back()->with('success', __('purchase_order.approve_success'));
    }

    public function reject(PurchaseOrder $purchaseOrder)
    {
        if (!$purchaseOrder->canBeApproved()) {
            return back()->with('error', __('purchase_order.reject_error'));
        }

        $purchaseOrder->update(['status' => 'cancelled']);

        return back()->with('success', __('purchase_order.reject_success'));
    }

    public function receive(PurchaseOrder $purchaseOrder)
    {
        if (!$purchaseOrder->canBeReceived()) {
            return back()->with('error', __('purchase_order.receive_error'));
        }

        $purchaseOrder->load(['details.product']);

        return view('purchase-orders.receive', compact('purchaseOrder'));
    }

    public function processReceive(Request $request, PurchaseOrder $purchaseOrder)
    {
        if (!$purchaseOrder->canBeReceived()) {
            return back()->with('error', __('purchase_order.receive_error'));
        }

        $validated = $request->validate([
            'received_date' => 'required|date',
            'items' => 'required|array|min:1',
            'items.*.detail_id' => 'required|exists:purchase_order_details,id',
            'items.*.quantity_received' => 'required|integer|min:1',
            'notes' => 'nullable|string',
        ]);

        try {
            DB::beginTransaction();

            // Create Stock In
            $stockIn = StockIn::create([
                'transaction_code' => 'SI-' . now()->format('YmdHis'),
                'warehouse_id' => $purchaseOrder->warehouse_id,
                'supplier_id' => $purchaseOrder->supplier_id,
                'transaction_date' => $validated['received_date'],
                'notes' => __('purchase_order.stock_in_notes', ['po' => $purchaseOrder->po_number]) . ($validated['notes'] ? ' - ' . $validated['notes'] : ''),
                'created_by' => auth()->id(),
            ]);

            $totalAmount = 0;

            foreach ($validated['items'] as $item) {
                $detail = PurchaseOrderDetail::findOrFail($item['detail_id']);
                $qtyReceived = $item['quantity_received'];

                // Validate quantity
                $remaining = $detail->getRemainingQuantity();
                if ($qtyReceived > $remaining) {
                    throw new \Exception(__('purchase_order.receive_error_qty', ['product' => $detail->product->name]));
                }

                // Create Stock In Detail
                $subtotal = $qtyReceived * $detail->unit_price;
                $totalAmount += $subtotal;

                StockInDetail::create([
                    'stock_in_id' => $stockIn->id,
                    'product_id' => $detail->product_id,
                    'quantity' => $qtyReceived,
                    'unit_price' => $detail->unit_price,
                    'subtotal' => $subtotal,
                ]);

                // Update product stock
                $product = Product::where('id', $detail->product_id)
                    ->where('warehouse_id', $purchaseOrder->warehouse_id)
                    ->first();

                if ($product) {
                    $product->increment('stock', $qtyReceived);
                }

                // Update PO detail received quantity
                $detail->increment('quantity_received', $qtyReceived);
            }

            $stockIn->update(['total_amount' => $totalAmount]);

            // Update PO status
            if ($purchaseOrder->isFullyReceived()) {
                $purchaseOrder->update(['status' => 'completed']);
            } else {
                $purchaseOrder->update(['status' => 'partially_received']);
            }

            DB::commit();

            return redirect()->route('purchase-orders.show', $purchaseOrder)
                ->with('success', __('purchase_order.receive_success'));
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', __('purchase_order.receive_record_error') . $e->getMessage())->withInput();
        }
    }
}
