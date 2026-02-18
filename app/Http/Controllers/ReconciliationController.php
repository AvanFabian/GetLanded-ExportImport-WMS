<?php

namespace App\Http\Controllers;

use App\Models\SupplierPayment;
use Illuminate\Http\Request;

class ReconciliationController extends Controller
{
    /**
     * Display a listing of unreconciled payments.
     */
    public function index(Request $request)
    {
        $companyId = auth()->user()->company_id;

        $unreconciledPayments = SupplierPayment::where('company_id', $companyId)
            ->whereNull('reconciled_at')
            ->where('amount_paid', '>', 0)
            ->with(['supplier', 'stockIn'])
            ->latest()
            ->paginate(20);

        return view('reconciliation.index', compact('unreconciledPayments'));
    }

    /**
     * Mark a payment as reconciled.
     */
    public function reconcile(Request $request, SupplierPayment $payment)
    {
        $this->authorize('update', $payment); // Ensure user owns the payment (via policy or check)

        $validated = $request->validate([
            'bank_statement_ref' => 'nullable|string|max:100',
            'reconciled_at' => 'required|date',
        ]);

        $payment->update([
            'reconciled_at' => $validated['reconciled_at'],
            'bank_statement_ref' => $validated['bank_statement_ref'],
        ]);

        return back()->with('success', __('Payment reconciled successfully.'));
    }

    /**
     * Mark a payment as unreconciled.
     */
    public function unreconcile(SupplierPayment $payment)
    {
        $this->authorize('update', $payment);

        $payment->update([
            'reconciled_at' => null,
            'bank_statement_ref' => null,
        ]);

        return back()->with('success', __('Payment marked as unreconciled.'));
    }
}
