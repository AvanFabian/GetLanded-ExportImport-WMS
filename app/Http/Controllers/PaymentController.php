<?php

namespace App\Http\Controllers;

use App\Models\Payment;
use App\Models\Customer;
use App\Models\SalesOrder;
use App\Models\CompanyBankAccount;
use App\Models\Currency;
use App\Services\WebhookService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PaymentController extends Controller
{
    public function __construct(
        protected WebhookService $webhookService
    ) {}

    /**
     * List payments with filters.
     */
    public function index(Request $request)
    {
        $companyId = auth()->user()->company_id;

        $payments = Payment::where('company_id', $companyId)
            ->with(['salesOrder', 'customer', 'bankAccount'])
            ->when($request->search, function ($q, $search) {
                $q->where('reference', 'like', "%{$search}%")
                  ->orWhereHas('customer', fn($q) => $q->where('name', 'like', "%{$search}%"))
                  ->orWhereHas('salesOrder', fn($q) => $q->where('so_number', 'like', "%{$search}%"));
            })
            ->when($request->customer_id, fn($q, $id) => $q->where('customer_id', $id))
            ->when($request->payment_method, fn($q, $m) => $q->where('payment_method', $m))
            ->latest('payment_date')
            ->paginate(20)
            ->withQueryString();

        $customers = Customer::where('company_id', $companyId)->orderBy('name')->get();

        return view('payments.index', compact('payments', 'customers'));
    }

    /**
     * Show create payment form.
     */
    public function create(Request $request)
    {
        $companyId = auth()->user()->company_id;

        $bankAccounts = CompanyBankAccount::where('company_id', $companyId)->get();
        $customers = Customer::where('company_id', $companyId)->orderBy('name')->get();
        $currencies = Currency::orderBy('code')->get();

        // Pre-select sales order if provided
        $selectedOrder = null;
        if ($request->sales_order_id) {
            $selectedOrder = SalesOrder::with('customer')->find($request->sales_order_id);
        }

        $unpaidOrders = SalesOrder::where('company_id', $companyId)
            ->whereIn('payment_status', ['unpaid', 'partial'])
            ->with('customer')
            ->orderBy('order_date', 'desc')
            ->get();

        return view('payments.create', compact(
            'bankAccounts', 'customers', 'currencies', 'selectedOrder', 'unpaidOrders'
        ));
    }

    /**
     * Store a new payment.
     */
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
            ->with('success', __('Payment recorded successfully.'));
    }

    /**
     * Show payment detail.
     */
    public function show(Payment $payment)
    {
        $payment->load(['salesOrder.customer', 'customer', 'bankAccount', 'allocations.salesOrder']);

        return view('payments.show', compact('payment'));
    }

    /**
     * Allocate an unallocated deposit to orders.
     */
    public function allocate(Request $request, Payment $payment)
    {
        $validated = $request->validate([
            'allocations' => 'required|array|min:1',
            'allocations.*.order_id' => 'required|exists:sales_orders,id',
            'allocations.*.amount' => 'required|numeric|min:0.01',
        ]);

        $allocations = [];
        foreach ($validated['allocations'] as $alloc) {
            $allocations[$alloc['order_id']] = $alloc['amount'];
        }

        $payment->allocateToOrders($allocations);

        return back()->with('success', __('Payment allocated successfully.'));
    }

    /**
     * AR Aging dashboard.
     */
    public function aging()
    {
        $companyId = auth()->user()->company_id;

        // Get outstanding invoices grouped by aging bracket
        $orders = SalesOrder::where('company_id', $companyId)
            ->whereIn('payment_status', ['unpaid', 'partial'])
            ->whereNotNull('due_date')
            ->with('customer')
            ->orderBy('due_date')
            ->get();

        $aging = [
            'current' => ['orders' => collect(), 'total' => 0],
            '1_30' => ['orders' => collect(), 'total' => 0],
            '31_60' => ['orders' => collect(), 'total' => 0],
            '61_90' => ['orders' => collect(), 'total' => 0],
            'over_90' => ['orders' => collect(), 'total' => 0],
        ];

        foreach ($orders as $order) {
            $outstanding = $order->total - $order->amount_paid - $order->credit_note_amount;
            $daysPastDue = max(0, now()->diffInDays($order->due_date, false) * -1);

            $bracket = match (true) {
                $daysPastDue <= 0 => 'current',
                $daysPastDue <= 30 => '1_30',
                $daysPastDue <= 60 => '31_60',
                $daysPastDue <= 90 => '61_90',
                default => 'over_90',
            };

            $aging[$bracket]['orders']->push($order);
            $aging[$bracket]['total'] += $outstanding;
        }

        $totalOutstanding = collect($aging)->sum('total');

        return view('payments.aging', compact('aging', 'totalOutstanding'));
    }
}
