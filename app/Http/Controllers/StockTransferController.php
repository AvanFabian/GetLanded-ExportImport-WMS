<?php

namespace App\Http\Controllers;

use App\Models\StockTransfer;
use App\Models\StockTransferItem;
use App\Models\Warehouse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Facades\DB;

class StockTransferController extends Controller
{
    public function index(Request $request)
    {
        $transfers = StockTransfer::where('company_id', auth()->user()->company_id)
            ->with(['sourceWarehouse', 'destinationWarehouse', 'creator'])
            ->when($request->status, fn($q, $s) => $q->where('status', $s))
            ->latest()
            ->paginate(20);

        return Inertia::render('StockTransfers/Index', [
            'transfers' => $transfers,
            'filters' => $request->only(['status']),
        ]);
    }

    public function create()
    {
        $warehouses = Warehouse::where('company_id', auth()->user()->company_id)->get();

        return Inertia::render('StockTransfers/Create', [
            'warehouses' => $warehouses,
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'source_warehouse_id' => 'required|exists:warehouses,id',
            'destination_warehouse_id' => 'required|exists:warehouses,id|different:source_warehouse_id',
            'transfer_date' => 'required|date',
            'items' => 'required|array|min:1',
            'items.*.batch_id' => 'required|exists:batches,id',
            'items.*.source_bin_id' => 'required|exists:warehouse_bins,id',
            'items.*.destination_bin_id' => 'required|exists:warehouse_bins,id',
            'items.*.quantity' => 'required|integer|min:1',
        ]);

        return DB::transaction(function () use ($validated) {
            $transfer = StockTransfer::create([
                'company_id' => auth()->user()->company_id,
                'source_warehouse_id' => $validated['source_warehouse_id'],
                'destination_warehouse_id' => $validated['destination_warehouse_id'],
                'transfer_number' => StockTransfer::generateTransferNumber(),
                'status' => StockTransfer::STATUS_PENDING,
                'transfer_date' => $validated['transfer_date'],
                'created_by' => auth()->id(),
            ]);

            foreach ($validated['items'] as $item) {
                StockTransferItem::create([
                    'stock_transfer_id' => $transfer->id,
                    'batch_id' => $item['batch_id'],
                    'source_bin_id' => $item['source_bin_id'],
                    'destination_bin_id' => $item['destination_bin_id'],
                    'quantity' => $item['quantity'],
                ]);
            }

            return redirect()->route('stock-transfers.show', $transfer)
                ->with('success', 'Stock transfer created successfully.');
        });
    }

    public function show(StockTransfer $stockTransfer)
    {
        $this->authorize('view', $stockTransfer);

        return Inertia::render('StockTransfers/Show', [
            'transfer' => $stockTransfer->load([
                'sourceWarehouse', 
                'destinationWarehouse', 
                'items.batch.product',
                'items.sourceBin',
                'items.destinationBin',
                'creator',
                'receiver',
            ]),
        ]);
    }

    public function dispatch(StockTransfer $stockTransfer)
    {
        $this->authorize('update', $stockTransfer);

        if ($stockTransfer->status !== StockTransfer::STATUS_PENDING) {
            return back()->with('error', 'Only pending transfers can be dispatched.');
        }

        $stockTransfer->dispatch();

        return back()->with('success', 'Stock transfer dispatched. Stock has been reserved.');
    }

    public function receive(StockTransfer $stockTransfer)
    {
        $this->authorize('update', $stockTransfer);

        if ($stockTransfer->status !== StockTransfer::STATUS_IN_TRANSIT) {
            return back()->with('error', 'Only in-transit transfers can be received.');
        }

        $stockTransfer->receive(auth()->id());

        return back()->with('success', 'Stock transfer received. Stock has been moved.');
    }
}
