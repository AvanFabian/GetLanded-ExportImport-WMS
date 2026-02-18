<?php

namespace App\Http\Controllers;

use App\Models\SupplierPayment;
use App\Models\Supplier;
use App\Models\StockIn;
use Illuminate\Http\Request;

class SupplierPaymentController extends Controller
{
    public function index(Request $request)
    {
        $companyId = auth()->user()->company_id;

        $payments = SupplierPayment::where('company_id', $companyId)
            ->with(['supplier', 'stockIn'])
            ->when($request->search, function ($q, $search) {
                $q->whereHas('supplier', fn ($q) => $q->where('name', 'like', "%{$search}%"));
            })
            ->when($request->supplier_id, fn ($q, $id) => $q->where('supplier_id', $id))
            ->when($request->payment_status, fn ($q, $s) => $q->where('payment_status', $s))
            ->latest()
            ->paginate(20)
            ->withQueryString();

        $suppliers = Supplier::where('company_id', $companyId)->orderBy('name')->get();

        return view('supplier-payments.index', compact('payments', 'suppliers'));
    }

    public function create()
    {
        $companyId = auth()->user()->company_id;

        $suppliers = Supplier::where('company_id', $companyId)->orderBy('name')->get();
        $stockIns = StockIn::where('company_id', $companyId)
            ->with(['supplier', 'warehouse'])
            ->latest()
            ->limit(100)
            ->get();
        $paymentMethods = SupplierPayment::PAYMENT_METHODS;

        return view('supplier-payments.create', compact('suppliers', 'stockIns', 'paymentMethods'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'stock_in_id' => 'required|exists:stock_ins,id',
            'supplier_id' => 'required|exists:suppliers,id',
            'amount_owed' => 'required|numeric|min:0',
            'amount_paid' => 'required|numeric|min:0',
            'due_date' => 'nullable|date',
            'payment_method' => 'required|in:' . implode(',', array_keys(SupplierPayment::PAYMENT_METHODS)),
            'currency_code' => 'nullable|string|size:3',
            'bank_reference' => 'nullable|string|max:100',
            'lc_number' => 'nullable|required_if:payment_method,letter_of_credit|string|max:100',
            'lc_expiry_date' => 'nullable|date',
            'lc_issuing_bank' => 'nullable|string|max:255',
            'payment_notes' => 'nullable|string',
        ]);

        $validated['company_id'] = auth()->user()->company_id;

        // Determine payment status
        $validated['payment_status'] = match (true) {
            $validated['amount_paid'] >= $validated['amount_owed'] => 'paid',
            $validated['amount_paid'] > 0 => 'partial',
            default => 'unpaid',
        };

        $payment = SupplierPayment::create($validated);

        return redirect()->route('supplier-payments.index')
            ->with('success', __('Supplier payment recorded successfully.'));
    }

    public function show(SupplierPayment $supplierPayment)
    {
        $supplierPayment->load(['supplier', 'stockIn.warehouse']);

        return view('supplier-payments.show', compact('supplierPayment'));
    }
}
