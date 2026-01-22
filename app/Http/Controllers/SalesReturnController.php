<?php

namespace App\Http\Controllers;

use App\Models\SalesReturn;
use App\Models\SalesReturnItem;
use App\Models\SalesOrder;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Facades\DB;

class SalesReturnController extends Controller
{
    public function index(Request $request)
    {
        $returns = SalesReturn::where('company_id', auth()->user()->company_id)
            ->with(['salesOrder', 'approver'])
            ->when($request->status, fn($q, $s) => $q->where('status', $s))
            ->latest()
            ->paginate(20);

        return Inertia::render('SalesReturns/Index', [
            'returns' => $returns,
            'filters' => $request->only(['status']),
        ]);
    }

    public function create(Request $request)
    {
        $order = null;
        if ($request->order_id) {
            $order = SalesOrder::with('items.product', 'items.batch')
                ->findOrFail($request->order_id);
        }

        return Inertia::render('SalesReturns/Create', [
            'order' => $order,
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'sales_order_id' => 'required|exists:sales_orders,id',
            'return_date' => 'required|date',
            'reason' => 'required|string',
            'items' => 'required|array|min:1',
            'items.*.product_id' => 'required|exists:products,id',
            'items.*.batch_id' => 'nullable|exists:batches,id',
            'items.*.quantity' => 'required|integer|min:1',
            'items.*.unit_price' => 'required|numeric|min:0',
        ]);

        return DB::transaction(function () use ($validated) {
            $creditAmount = collect($validated['items'])->sum(fn($i) => $i['quantity'] * $i['unit_price']);

            $return = SalesReturn::create([
                'company_id' => auth()->user()->company_id,
                'sales_order_id' => $validated['sales_order_id'],
                'return_number' => SalesReturn::generateReturnNumber(),
                'return_date' => $validated['return_date'],
                'credit_amount' => $creditAmount,
                'reason' => $validated['reason'],
                'status' => SalesReturn::STATUS_PENDING,
            ]);

            foreach ($validated['items'] as $item) {
                SalesReturnItem::create([
                    'sales_return_id' => $return->id,
                    'product_id' => $item['product_id'],
                    'batch_id' => $item['batch_id'] ?? null,
                    'quantity' => $item['quantity'],
                    'unit_price' => $item['unit_price'],
                ]);
            }

            return redirect()->route('sales-returns.show', $return)
                ->with('success', 'Sales return created successfully.');
        });
    }

    public function show(SalesReturn $salesReturn)
    {
        $this->authorize('view', $salesReturn);

        return Inertia::render('SalesReturns/Show', [
            'return' => $salesReturn->load(['salesOrder.customer', 'items.product', 'items.batch', 'approver']),
        ]);
    }

    public function approve(SalesReturn $salesReturn)
    {
        $this->authorize('update', $salesReturn);

        $salesReturn->update([
            'status' => SalesReturn::STATUS_APPROVED,
            'approved_by' => auth()->id(),
            'approved_at' => now(),
        ]);

        return back()->with('success', 'Sales return approved.');
    }

    public function process(SalesReturn $salesReturn)
    {
        $this->authorize('update', $salesReturn);

        if ($salesReturn->status !== SalesReturn::STATUS_APPROVED) {
            return back()->with('error', 'Return must be approved before processing.');
        }

        $salesReturn->process();

        return back()->with('success', 'Sales return processed. Credit note applied and batches quarantined.');
    }
}
