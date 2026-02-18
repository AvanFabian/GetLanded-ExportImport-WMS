<?php

namespace App\Http\Controllers;

use App\Models\SalesReturn;
use App\Models\SalesOrder;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class SalesReturnController extends Controller
{
    public function index(Request $request)
    {
        $companyId = auth()->user()->company_id;

        $returns = SalesReturn::where('company_id', $companyId)
            ->with(['salesOrder.customer'])
            ->when($request->search, function ($q, $search) {
                $q->where('return_number', 'like', "%{$search}%")
                  ->orWhereHas('salesOrder', fn ($q) => $q->where('so_number', 'like', "%{$search}%"));
            })
            ->when($request->status, fn ($q, $s) => $q->where('status', $s))
            ->latest('return_date')
            ->paginate(20)
            ->withQueryString();

        return view('sales-returns.index', compact('returns'));
    }

    public function create()
    {
        $companyId = auth()->user()->company_id;

        $salesOrders = SalesOrder::where('company_id', $companyId)
            ->whereIn('status', ['delivered', 'confirmed'])
            ->with('customer')
            ->orderBy('order_date', 'desc')
            ->get();

        $products = Product::where('company_id', $companyId)
            ->where('status', true)
            ->orderBy('name')
            ->get();

        return view('sales-returns.create', compact('salesOrders', 'products'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'sales_order_id' => 'required|exists:sales_orders,id',
            'return_date' => 'required|date',
            'reason' => 'required|string',
            'items' => 'required|array|min:1',
            'items.*.product_id' => 'required|exists:products,id',
            'items.*.quantity' => 'required|integer|min:1',
            'items.*.unit_price' => 'required|numeric|min:0',
        ]);

        $companyId = auth()->user()->company_id;
        $creditAmount = collect($validated['items'])->sum(fn ($i) => $i['quantity'] * $i['unit_price']);

        $return = DB::transaction(function () use ($validated, $companyId, $creditAmount) {
            $return = SalesReturn::create([
                'company_id' => $companyId,
                'sales_order_id' => $validated['sales_order_id'],
                'return_number' => 'RET-' . date('Ymd') . '-' . str_pad(SalesReturn::where('company_id', $companyId)->count() + 1, 4, '0', STR_PAD_LEFT),
                'return_date' => $validated['return_date'],
                'credit_amount' => $creditAmount,
                'reason' => $validated['reason'],
                'status' => 'pending',
            ]);

            foreach ($validated['items'] as $item) {
                $return->items()->create([
                    'product_id' => $item['product_id'],
                    'batch_id' => $item['batch_id'] ?? null,
                    'quantity' => $item['quantity'],
                    'unit_price' => $item['unit_price'],
                ]);
            }

            return $return;
        });

        return redirect()->route('sales-returns.show', $return)
            ->with('success', __('Sales return created successfully.'));
    }

    public function show(SalesReturn $salesReturn)
    {
        $salesReturn->load(['salesOrder.customer', 'items.product', 'items.batch', 'approver']);

        return view('sales-returns.show', compact('salesReturn'));
    }

    public function approve(SalesReturn $salesReturn)
    {
        $salesReturn->update([
            'status' => 'approved',
            'approved_by' => auth()->id(),
            'approved_at' => now(),
        ]);

        return back()->with('success', __('Sales return approved.'));
    }

    public function process(SalesReturn $salesReturn)
    {
        if ($salesReturn->status !== 'approved') {
            return back()->with('error', __('Only approved returns can be processed.'));
        }

        $salesReturn->process();

        return back()->with('success', __('Sales return processed. Credit note applied.'));
    }
}
