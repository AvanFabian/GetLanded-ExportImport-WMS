<?php

namespace App\Http\Controllers;

use App\Models\StockTake;
use App\Models\StockTakeItem;
use App\Models\StockLocation;
use App\Models\Warehouse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Facades\DB;

class StockTakeController extends Controller
{
    public function index(Request $request)
    {
        $stockTakes = StockTake::where('company_id', auth()->user()->company_id)
            ->with(['warehouse', 'creator'])
            ->when($request->status, fn($q, $s) => $q->where('status', $s))
            ->latest()
            ->paginate(20);

        return Inertia::render('StockTakes/Index', [
            'stockTakes' => $stockTakes,
            'filters' => $request->only(['status']),
        ]);
    }

    public function create()
    {
        $warehouses = Warehouse::where('company_id', auth()->user()->company_id)->get();

        return Inertia::render('StockTakes/Create', [
            'warehouses' => $warehouses,
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'warehouse_id' => 'required|exists:warehouses,id',
            'is_blind' => 'boolean',
        ]);

        return DB::transaction(function () use ($validated) {
            $stockTake = StockTake::create([
                'company_id' => auth()->user()->company_id,
                'warehouse_id' => $validated['warehouse_id'],
                'take_number' => StockTake::generateTakeNumber(),
                'status' => StockTake::STATUS_IN_PROGRESS,
                'is_blind' => $validated['is_blind'] ?? false,
                'created_by' => auth()->id(),
            ]);

            // Pre-populate items with current stock
            $locations = StockLocation::whereHas('bin.zone.warehouse', fn($q) => 
                $q->where('id', $validated['warehouse_id'])
            )->with('batch')->get();

            foreach ($locations as $location) {
                StockTakeItem::create([
                    'stock_take_id' => $stockTake->id,
                    'batch_id' => $location->batch_id,
                    'bin_id' => $location->bin_id,
                    'system_quantity' => $location->quantity,
                    'counted_quantity' => null,
                    'variance' => null,
                ]);
            }

            return redirect()->route('stock-takes.show', $stockTake)
                ->with('success', 'Stock take started.');
        });
    }

    public function show(StockTake $stockTake)
    {
        $this->authorize('view', $stockTake);

        $items = $stockTake->items()->with(['batch.product', 'bin'])->get();
        
        // In blind mode, hide system quantities until complete
        if ($stockTake->is_blind && $stockTake->status === StockTake::STATUS_IN_PROGRESS) {
            $items = $items->map(function ($item) {
                $item->makeHidden('system_quantity');
                return $item;
            });
        }

        return Inertia::render('StockTakes/Show', [
            'stockTake' => $stockTake->load(['warehouse', 'creator', 'completer']),
            'items' => $items,
        ]);
    }

    public function updateCounts(Request $request, StockTake $stockTake)
    {
        $this->authorize('update', $stockTake);

        if ($stockTake->status !== StockTake::STATUS_IN_PROGRESS) {
            return back()->with('error', 'Cannot update completed stock take.');
        }

        $validated = $request->validate([
            'counts' => 'required|array',
            'counts.*.item_id' => 'required|exists:stock_take_items,id',
            'counts.*.counted_quantity' => 'required|integer|min:0',
        ]);

        foreach ($validated['counts'] as $count) {
            StockTakeItem::where('id', $count['item_id'])->update([
                'counted_quantity' => $count['counted_quantity'],
            ]);
        }

        return back()->with('success', 'Counts saved.');
    }

    public function complete(StockTake $stockTake)
    {
        $this->authorize('update', $stockTake);

        if ($stockTake->status !== StockTake::STATUS_IN_PROGRESS) {
            return back()->with('error', 'Stock take is not in progress.');
        }

        // Check all items have been counted
        $uncounted = $stockTake->items()->whereNull('counted_quantity')->count();
        if ($uncounted > 0) {
            return back()->with('error', "There are {$uncounted} uncounted items.");
        }

        $stockTake->complete(auth()->id());

        return redirect()->route('stock-takes.variance-report', $stockTake)
            ->with('success', 'Stock take completed.');
    }

    public function varianceReport(StockTake $stockTake)
    {
        $this->authorize('view', $stockTake);

        return Inertia::render('StockTakes/VarianceReport', [
            'stockTake' => $stockTake,
            'report' => $stockTake->variance_report,
        ]);
    }
}
