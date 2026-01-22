<?php

namespace App\Http\Controllers;

use App\Models\Claim;
use App\Models\ClaimEvidence;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Facades\Storage;

class ClaimController extends Controller
{
    public function index(Request $request)
    {
        $claims = Claim::where('company_id', auth()->user()->company_id)
            ->with(['salesOrder'])
            ->when($request->status, fn($q, $s) => $q->where('status', $s))
            ->latest()
            ->paginate(20);

        return Inertia::render('Claims/Index', [
            'claims' => $claims,
            'filters' => $request->only(['status']),
        ]);
    }

    public function create(Request $request)
    {
        return Inertia::render('Claims/Create', [
            'claimTypes' => Claim::CLAIM_TYPES,
            'orderId' => $request->order_id,
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'sales_order_id' => 'required|exists:sales_orders,id',
            'claim_type' => 'required|in:' . implode(',', array_keys(Claim::CLAIM_TYPES)),
            'claimed_amount' => 'required|numeric|min:0.01',
            'insurance_policy_number' => 'nullable|string|max:255',
            'description' => 'required|string',
        ]);

        $claim = Claim::create([
            'company_id' => auth()->user()->company_id,
            'sales_order_id' => $validated['sales_order_id'],
            'claim_type' => $validated['claim_type'],
            'claimed_amount' => $validated['claimed_amount'],
            'insurance_policy_number' => $validated['insurance_policy_number'] ?? null,
            'status' => Claim::STATUS_OPEN,
            'description' => $validated['description'],
        ]);

        return redirect()->route('claims.show', $claim)
            ->with('success', 'Claim created successfully.');
    }

    public function show(Claim $claim)
    {
        $this->authorize('view', $claim);

        return Inertia::render('Claims/Show', [
            'claim' => $claim->load(['salesOrder.customer', 'evidences']),
        ]);
    }

    public function uploadEvidence(Request $request, Claim $claim)
    {
        $this->authorize('update', $claim);

        $validated = $request->validate([
            'files' => 'required|array|min:1',
            'files.*' => 'file|mimes:jpg,jpeg,png,pdf|max:5120',
        ]);

        foreach ($request->file('files') as $file) {
            $path = $file->store('claims/' . $claim->id);
            
            ClaimEvidence::create([
                'claim_id' => $claim->id,
                'file_path' => $path,
                'file_type' => $file->getClientMimeType(),
            ]);
        }

        return back()->with('success', 'Evidence uploaded successfully.');
    }

    public function submit(Claim $claim)
    {
        $this->authorize('update', $claim);

        if ($claim->evidences()->count() === 0) {
            return back()->with('error', 'Please upload at least one evidence file before submitting.');
        }

        $claim->update(['status' => Claim::STATUS_SUBMITTED]);

        return back()->with('success', 'Claim submitted to insurance.');
    }

    public function settle(Request $request, Claim $claim)
    {
        $this->authorize('update', $claim);

        $validated = $request->validate([
            'settled_amount' => 'required|numeric|min:0',
            'status' => 'required|in:settled,rejected',
        ]);

        $claim->update([
            'settled_amount' => $validated['settled_amount'],
            'status' => $validated['status'],
        ]);

        return back()->with('success', 'Claim ' . $validated['status'] . '.');
    }
}
