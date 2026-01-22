<?php

namespace App\Http\Controllers;

use App\Models\Payment;
use App\Models\CompanyBankAccount;
use App\Services\WebhookService;
use Illuminate\Http\Request;
use Inertia\Inertia;

class PaymentController extends Controller
{
    public function __construct(
        protected WebhookService $webhookService
    ) {}

    public function index(Request $request)
    {
        $payments = Payment::where('company_id', auth()->user()->company_id)
            ->with(['salesOrder', 'customer', 'bankAccount'])
            ->when($request->search, function ($q, $search) {
                $q->where('reference', 'like', "%{$search}%")
                  ->orWhereHas('salesOrder', fn($q) => $q->where('order_number', 'like', "%{$search}%"));
            })
            ->when($request->customer_id, fn($q, $id) => $q->where('customer_id', $id))
            ->latest('payment_date')
            ->paginate(20);

        return Inertia::render('Payments/Index', [
            'payments' => $payments,
            'filters' => $request->only(['search', 'customer_id']),
        ]);
    }

    public function create()
    {
        return Inertia::render('Payments/Create', [
            'bankAccounts' => CompanyBankAccount::where('company_id', auth()->user()->company_id)->get(),
            'paymentMethods' => Payment::PAYMENT_METHODS,
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'sales_order_id' => 'nullable|exists:sales_orders,id',
            'customer_id' => 'nullable|exists:customers,id',
            'bank_account_id' => 'nullable|exists:company_bank_accounts,id',
            'payment_date' => 'required|date',
            'amount' => 'required|numeric|min:0.01',
            'bank_fees' => 'nullable|numeric|min:0',
            'currency_code' => 'required|string|size:3',
            'exchange_rate' => 'nullable|numeric|min:0',
            'payment_method' => 'required|string',
            'reference' => 'nullable|string|max:255',
            'notes' => 'nullable|string',
        ]);

        $validated['company_id'] = auth()->user()->company_id;
        $validated['bank_fees'] = $validated['bank_fees'] ?? 0;
        
        // Calculate base currency amount
        $rate = $validated['exchange_rate'] ?? 1;
        $validated['base_currency_amount'] = $validated['amount'] * $rate;

        $payment = Payment::create($validated);

        // Dispatch webhook
        $this->webhookService->dispatch(
            $payment->company_id,
            'payment.received',
            ['payment_id' => $payment->id, 'amount' => $payment->base_currency_amount]
        );

        return redirect()->route('payments.index')
            ->with('success', 'Payment recorded successfully.');
    }

    public function show(Payment $payment)
    {
        $this->authorize('view', $payment);

        return Inertia::render('Payments/Show', [
            'payment' => $payment->load(['salesOrder', 'customer', 'bankAccount', 'allocations.salesOrder']),
        ]);
    }

    public function allocate(Request $request, Payment $payment)
    {
        $this->authorize('update', $payment);

        $validated = $request->validate([
            'allocations' => 'required|array',
            'allocations.*.order_id' => 'required|exists:sales_orders,id',
            'allocations.*.amount' => 'required|numeric|min:0.01',
        ]);

        $allocations = [];
        foreach ($validated['allocations'] as $alloc) {
            $allocations[$alloc['order_id']] = $alloc['amount'];
        }

        $payment->allocateToOrders($allocations);

        return back()->with('success', 'Payment allocated successfully.');
    }

    public function agingDashboard()
    {
        $companyId = auth()->user()->company_id;
        
        $aging = Payment::selectRaw("
            CASE 
                WHEN DATEDIFF(CURDATE(), payment_date) <= 30 THEN '0-30'
                WHEN DATEDIFF(CURDATE(), payment_date) <= 60 THEN '31-60'
                WHEN DATEDIFF(CURDATE(), payment_date) <= 90 THEN '61-90'
                ELSE '90+'
            END as bracket,
            SUM(base_currency_amount) as total
        ")
        ->where('company_id', $companyId)
        ->groupBy('bracket')
        ->get();

        return Inertia::render('Payments/AgingDashboard', [
            'aging' => $aging,
        ]);
    }
}
