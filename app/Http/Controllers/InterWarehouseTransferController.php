<?php

namespace App\Http\Controllers;

use App\Models\InterWarehouseTransfer;
use App\Models\InterWarehouseTransferItem;
use App\Models\Warehouse;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class InterWarehouseTransferController extends Controller
{
    public function index(Request $request)
    {
        $q = $request->query('q');
        $status = $request->query('status');
        $warehouseId = $request->query('warehouse_id');
        $dateFrom = $request->query('date_from');
        $dateTo = $request->query('date_to');

        $transfers = InterWarehouseTransfer::with(['fromWarehouse', 'toWarehouse', 'creator'])
            ->when($q, fn($query) => $query->where('transfer_number', 'like', "%{$q}%"))
            ->when($status, fn($query) => $query->where('status', $status))
            ->when($warehouseId, fn($query) => $query->where(function ($q) use ($warehouseId) {
                $q->where('from_warehouse_id', $warehouseId)
                    ->orWhere('to_warehouse_id', $warehouseId);
            }))
            ->when($dateFrom, fn($query) => $query->whereDate('transfer_date', '>=', $dateFrom))
            ->when($dateTo, fn($query) => $query->whereDate('transfer_date', '<=', $dateTo))
            ->orderBy('transfer_date', 'desc')
            ->orderBy('created_at', 'desc')
            ->paginate(20)
            ->withQueryString();

        $warehouses = Warehouse::active()->orderBy('name')->get();

        return view('transfers.index', compact('transfers', 'warehouses', 'q', 'status', 'warehouseId', 'dateFrom', 'dateTo'));
    }

    public function create()
    {
        $warehouses = Warehouse::active()->orderBy('name')->get();
        $products = Product::with('warehouses')->where('status', true)->orderBy('name')->get();

        return view('transfers.create', compact('warehouses', 'products'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'from_warehouse_id' => 'required|exists:warehouses,id',
            'to_warehouse_id' => 'required|exists:warehouses,id|different:from_warehouse_id',
            'transfer_date' => 'required|date',
            'notes' => 'nullable|string',
            'items' => 'required|array|min:1',
            'items.*.product_id' => 'required|exists:products,id',
            'items.*.quantity' => 'required|integer|min:1',
            'items.*.notes' => 'nullable|string',
        ]);

        DB::beginTransaction();
        try {
            // Create transfer
            $transfer = InterWarehouseTransfer::create([
                'from_warehouse_id' => $data['from_warehouse_id'],
                'to_warehouse_id' => $data['to_warehouse_id'],
                'transfer_date' => $data['transfer_date'],
                'notes' => $data['notes'] ?? null,
                'status' => 'pending',
                'created_by' => Auth::id(),
            ]);

            // Create transfer items
            foreach ($data['items'] as $item) {
                // Validate stock availability in pivot table
                $product = Product::findOrFail($item['product_id']);

                if (!$product->warehouses()->where('warehouse_id', $data['from_warehouse_id'])->exists()) {
                    throw new \Exception("Product {$product->name} not found in source warehouse");
                }

                $stock = $product->getStockInWarehouse($data['from_warehouse_id']);
                if ($stock < $item['quantity']) {
                    throw new \Exception("Insufficient stock for product: {$product->name}. Available: {$stock}");
                }

                InterWarehouseTransferItem::create([
                    'transfer_id' => $transfer->id,
                    'product_id' => $item['product_id'],
                    'quantity' => $item['quantity'],
                    'notes' => $item['notes'] ?? null,
                ]);
            }

            DB::commit();
            return redirect()->route('transfers.show', $transfer)->with('status', 'Transfer created successfully');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withInput()->with('error', $e->getMessage());
        }
    }

    public function show(InterWarehouseTransfer $transfer)
    {
        $transfer->load(['fromWarehouse', 'toWarehouse', 'items.product', 'creator', 'approver', 'completer']);
        return view('transfers.show', compact('transfer'));
    }

    public function approve(InterWarehouseTransfer $transfer)
    {
        if (!$transfer->canApprove()) {
            return back()->with('error', 'Transfer cannot be approved');
        }

        $transfer->approve(Auth::id());
        return back()->with('status', 'Transfer approved successfully');
    }

    public function reject(InterWarehouseTransfer $transfer)
    {
        if (!$transfer->canReject()) {
            return back()->with('error', 'Transfer cannot be rejected');
        }

        $transfer->reject();
        return back()->with('status', 'Transfer rejected');
    }

    public function startTransit(InterWarehouseTransfer $transfer)
    {
        if ($transfer->status !== 'approved') {
            return back()->with('error', 'Only approved transfers can be set to in transit');
        }

        $transfer->startTransit();
        return back()->with('status', 'Transfer set to in transit');
    }

    public function complete(InterWarehouseTransfer $transfer)
    {
        if (!$transfer->canComplete()) {
            return back()->with('error', 'Transfer cannot be completed');
        }

        DB::beginTransaction();
        try {
            $transfer->complete(Auth::id());
            DB::commit();
            return back()->with('status', 'Transfer completed successfully');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Failed to complete transfer: ' . $e->getMessage());
        }
    }

    public function destroy(InterWarehouseTransfer $transfer)
    {
        if ($transfer->status !== 'pending') {
            return back()->with('error', 'Only pending transfers can be deleted');
        }

        $transfer->delete();
        return redirect()->route('transfers.index')->with('status', 'Transfer deleted successfully');
    }
}
