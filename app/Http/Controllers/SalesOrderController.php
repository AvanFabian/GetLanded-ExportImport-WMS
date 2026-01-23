<?php

namespace App\Http\Controllers;

use App\Models\SalesOrder;
use App\Models\SalesOrderItem;
use App\Models\Customer;
use App\Models\Product;
use App\Models\Warehouse;
use App\Models\StockOut;
use App\Models\StockOutDetail;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Barryvdh\DomPDF\Facade\Pdf;

class SalesOrderController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query = SalesOrder::with(['customer', 'warehouse', 'creator']);

        // Search
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('so_number', 'like', "%{$search}%")
                    ->orWhereHas('customer', function ($q) use ($search) {
                        $q->where('name', 'like', "%{$search}%");
                    });
            });
        }

        // Filter by status
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        // Filter by payment status
        if ($request->filled('payment_status')) {
            $query->where('payment_status', $request->payment_status);
        }

        // Filter by customer
        if ($request->filled('customer_id')) {
            $query->where('customer_id', $request->customer_id);
        }

        // Filter by date range
        if ($request->filled('start_date')) {
            $query->whereDate('order_date', '>=', $request->start_date);
        }
        if ($request->filled('end_date')) {
            $query->whereDate('order_date', '<=', $request->end_date);
        }

        $salesOrders = $query->latest()->paginate(10)->withQueryString();
        $customers = Customer::where('is_active', true)->orderBy('name')->get();

        return view('sales-orders.index', compact('salesOrders', 'customers'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(Request $request)
    {
        $customers = Customer::where('is_active', true)->orderBy('name')->get();
        $warehouses = Warehouse::where('is_active', true)->orderBy('name')->get();
        $products = Product::with('category')->where('status', true)->orderBy('name')->get();

        $selectedCustomer = $request->customer_id ? Customer::find($request->customer_id) : null;

        return view('sales-orders.create', compact('customers', 'warehouses', 'products', 'selectedCustomer'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'customer_id' => 'required|exists:customers,id',
            'warehouse_id' => 'required|exists:warehouses,id',
            'order_date' => 'required|date',
            'delivery_date' => 'nullable|date|after_or_equal:order_date',
            'discount' => 'nullable|numeric|min:0',
            'notes' => 'nullable|string',
            'document_language' => 'nullable|in:en,id',
            'items' => 'required|array|min:1',
            'items.*.product_id' => 'required|exists:products,id',
            'items.*.quantity' => 'required|integer|min:1',
            'items.*.unit_price' => 'required|numeric|min:0',
        ]);

        DB::beginTransaction();
        try {
            // Create Sales Order
            $salesOrder = SalesOrder::create([
                'so_number' => SalesOrder::generateSONumber(),
                'customer_id' => $validated['customer_id'],
                'warehouse_id' => $validated['warehouse_id'],
                'order_date' => $validated['order_date'],
                'delivery_date' => $validated['delivery_date'],
                'status' => 'draft',
                'payment_status' => 'unpaid',
                'subtotal' => 0,
                'tax' => 0,
                'discount' => $validated['discount'] ?? 0,
                'total' => 0,
                'notes' => $validated['notes'],
                'document_language' => $validated['document_language'] ?? 'en',
                'created_by' => Auth::id(),
                'updated_by' => Auth::id(),
            ]);

            // Create Sales Order Items and calculate totals
            $subtotal = 0;
            foreach ($validated['items'] as $item) {
                $itemSubtotal = $item['quantity'] * $item['unit_price'];
                $subtotal += $itemSubtotal;

                SalesOrderItem::create([
                    'sales_order_id' => $salesOrder->id,
                    'product_id' => $item['product_id'],
                    'quantity' => $item['quantity'],
                    'unit_price' => $item['unit_price'],
                    'subtotal' => $itemSubtotal,
                ]);
            }

            // Calculate tax (PPN 11%) and total
            $discount = $validated['discount'] ?? 0;
            $afterDiscount = $subtotal - $discount;
            $tax = $afterDiscount * 0.11; // PPN 11%
            $total = $afterDiscount + $tax;

            // Update totals
            $salesOrder->update([
                'subtotal' => $subtotal,
                'tax' => $tax,
                'total' => $total,
            ]);

            DB::commit();

            return redirect()->route('sales-orders.show', $salesOrder)
                ->with('success', __('sales.create_success'));
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withInput()->with('error', __('sales.create_error') . $e->getMessage());
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(SalesOrder $salesOrder)
    {
        $salesOrder->load(['customer', 'warehouse', 'items.product', 'stockOut', 'invoice', 'creator', 'updater']);

        return view('sales-orders.show', compact('salesOrder'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(SalesOrder $salesOrder)
    {
        // Only draft orders can be edited
        if ($salesOrder->status !== 'draft') {
            return redirect()->route('sales-orders.show', $salesOrder)
                ->with('error', __('sales.edit_draft_only'));
        }

        $salesOrder->load(['items.product']);
        $customers = Customer::where('is_active', true)->orderBy('name')->get();
        $warehouses = Warehouse::where('is_active', true)->orderBy('name')->get();
        $products = Product::with('category')->where('status', true)->orderBy('name')->get();

        return view('sales-orders.edit', compact('salesOrder', 'customers', 'warehouses', 'products'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, SalesOrder $salesOrder)
    {
        // Only draft orders can be updated
        if ($salesOrder->status !== 'draft') {
            return redirect()->route('sales-orders.show', $salesOrder)
                ->with('error', __('sales.update_draft_only'));
        }

        $validated = $request->validate([
            'customer_id' => 'required|exists:customers,id',
            'warehouse_id' => 'required|exists:warehouses,id',
            'order_date' => 'required|date',
            'delivery_date' => 'nullable|date|after_or_equal:order_date',
            'discount' => 'nullable|numeric|min:0',
            'notes' => 'nullable|string',
            'document_language' => 'nullable|in:en,id',
            'items' => 'required|array|min:1',
            'items.*.product_id' => 'required|exists:products,id',
            'items.*.quantity' => 'required|integer|min:1',
            'items.*.unit_price' => 'required|numeric|min:0',
        ]);

        DB::beginTransaction();
        try {
            // Delete existing items
            $salesOrder->items()->delete();

            // Update Sales Order
            $salesOrder->update([
                'customer_id' => $validated['customer_id'],
                'warehouse_id' => $validated['warehouse_id'],
                'order_date' => $validated['order_date'],
                'delivery_date' => $validated['delivery_date'],
                'discount' => $validated['discount'] ?? 0,
                'notes' => $validated['notes'],
                'document_language' => $validated['document_language'] ?? 'en',
                'updated_by' => Auth::id(),
            ]);

            // Create new items and calculate totals
            $subtotal = 0;
            foreach ($validated['items'] as $item) {
                $itemSubtotal = $item['quantity'] * $item['unit_price'];
                $subtotal += $itemSubtotal;

                SalesOrderItem::create([
                    'sales_order_id' => $salesOrder->id,
                    'product_id' => $item['product_id'],
                    'quantity' => $item['quantity'],
                    'unit_price' => $item['unit_price'],
                    'subtotal' => $itemSubtotal,
                ]);
            }

            // Calculate tax (PPN 11%) and total
            $discount = $validated['discount'] ?? 0;
            $afterDiscount = $subtotal - $discount;
            $tax = $afterDiscount * 0.11;
            $total = $afterDiscount + $tax;

            // Update totals
            $salesOrder->update([
                'subtotal' => $subtotal,
                'tax' => $tax,
                'total' => $total,
            ]);

            DB::commit();

            return redirect()->route('sales-orders.show', $salesOrder)
                ->with('success', __('sales.update_success'));
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withInput()->with('error', __('sales.update_error') . $e->getMessage());
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(SalesOrder $salesOrder)
    {
        // Only draft orders can be deleted
        if ($salesOrder->status !== 'draft') {
            return redirect()->route('sales-orders.index')
                ->with('error', __('sales.delete_draft_only'));
        }

        $salesOrder->delete();

        return redirect()->route('sales-orders.index')
            ->with('success', __('sales.delete_success'));
    }

    /**
     * Confirm the sales order
     */
    public function confirm(SalesOrder $salesOrder)
    {
        if ($salesOrder->status !== 'draft') {
            return back()->with('error', __('sales.confirm_draft_only'));
        }

        // Validate stock availability in the pivot table
        foreach ($salesOrder->items as $item) {
            $product = Product::find($item->product_id);

            // Get stock from pivot table for the specified warehouse
            $stock = $product->getStockInWarehouse($salesOrder->warehouse_id);

            if ($stock < $item->quantity) {
                return back()->with('error', __('sales.stock_insufficient', ['product' => $product->name, 'stock' => $stock, 'required' => $item->quantity]));
            }
        }

        $salesOrder->update([
            'status' => 'confirmed',
            'updated_by' => Auth::id(),
        ]);

        return back()->with('success', __('sales.confirm_success'));
    }

    /**
     * Ship the sales order
     */
    public function ship(SalesOrder $salesOrder)
    {
        if ($salesOrder->status !== 'confirmed') {
            return back()->with('error', __('sales.ship_confirmed_only'));
        }

        $salesOrder->update([
            'status' => 'shipped',
            'updated_by' => Auth::id(),
        ]);

        return back()->with('success', __('sales.ship_success'));
    }

    /**
     * Deliver the sales order
     */
    public function deliver(SalesOrder $salesOrder)
    {
        if ($salesOrder->status !== 'shipped') {
            return back()->with('error', __('sales.deliver_shipped_only'));
        }

        $salesOrder->update([
            'status' => 'delivered',
            'updated_by' => Auth::id(),
        ]);

        return back()->with('success', __('sales.deliver_success'));
    }

    /**
     * Cancel the sales order
     */
    public function cancel(SalesOrder $salesOrder)
    {
        if (in_array($salesOrder->status, ['delivered', 'cancelled'])) {
            return back()->with('error', __('sales.cancel_error'));
        }

        $salesOrder->update([
            'status' => 'cancelled',
            'updated_by' => Auth::id(),
        ]);

        return back()->with('success', __('sales.cancel_success'));
    }

    /**
     * Generate stock out from sales order
     */
    public function generateStockOut(SalesOrder $salesOrder)
    {
        if ($salesOrder->status !== 'confirmed') {
            return back()->with('error', __('sales.stock_out_confirmed_only'));
        }

        if ($salesOrder->stock_out_id) {
            return back()->with('error', __('sales.stock_out_exists'));
        }

        DB::beginTransaction();
        try {
            // Create Stock Out
            $stockOut = StockOut::create([
                'warehouse_id' => $salesOrder->warehouse_id,
                'date' => now(),
                'reference_number' => $salesOrder->so_number,
                'notes' => "Stok keluar untuk SO: {$salesOrder->so_number}",
                'created_by' => Auth::id(),
                'updated_by' => Auth::id(),
            ]);

            // Create Stock Out Details and update product stock in pivot table
            foreach ($salesOrder->items as $item) {
                StockOutDetail::create([
                    'stock_out_id' => $stockOut->id,
                    'product_id' => $item->product_id,
                    'quantity' => $item->quantity,
                ]);

                // Update product stock in pivot table for the warehouse
                $product = Product::findOrFail($item->product_id);

                if ($product->warehouses()->where('warehouse_id', $salesOrder->warehouse_id)->exists()) {
                    // Decrease stock using DB::raw to prevent race conditions
                    $product->warehouses()->updateExistingPivot($salesOrder->warehouse_id, [
                        'stock' => DB::raw('stock - ' . (int)$item->quantity)
                    ]);
                }
            }

            // Link stock out to sales order
            $salesOrder->update([
                'stock_out_id' => $stockOut->id,
                'updated_by' => Auth::id(),
            ]);

            DB::commit();

            return redirect()->route('stock-outs.show', $stockOut)
                ->with('success', __('sales.stock_out_success'));
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', __('sales.stock_out_error') . $e->getMessage());
        }
    }

    /**
     * Generate delivery order (Surat Jalan) PDF
     */
    public function deliveryOrder(SalesOrder $salesOrder)
    {
        $salesOrder->load(['customer', 'warehouse', 'items.product']);

        $pdf = PDF::loadView('sales-orders.delivery-order-pdf', compact('salesOrder'));

        return $pdf->download("surat-jalan-{$salesOrder->so_number}.pdf");
    }
}
