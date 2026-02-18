<?php

namespace App\Http\Controllers;

use App\Models\Claim;
use App\Models\SalesOrder;
use App\Models\ClaimEvidence;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class ClaimController extends Controller
{
    public function index(Request $request)
    {
        $companyId = auth()->user()->company_id;

        $claims = Claim::where('company_id', $companyId)
            ->with(['salesOrder.customer'])
            ->when($request->search, function ($q, $search) {
                $q->where('description', 'like', "%{$search}%")
                  ->orWhereHas('salesOrder', fn ($q) => $q->where('so_number', 'like', "%{$search}%"));
            })
            ->when($request->status, fn ($q, $s) => $q->where('status', $s))
            ->when($request->claim_type, fn ($q, $t) => $q->where('claim_type', $t))
            ->latest()
            ->paginate(20)
            ->withQueryString();

        return view('claims.index', compact('claims'));
    }

    public function create()
    {
        $companyId = auth()->user()->company_id;

        $salesOrders = SalesOrder::where('company_id', $companyId)
            ->with('customer')
            ->orderBy('order_date', 'desc')
            ->get();

        return view('claims.create', compact('salesOrders'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'sales_order_id' => 'required|exists:sales_orders,id',
            'claim_type' => 'required|in:damage,shortage,delay',
            'claimed_amount' => 'required|numeric|min:0.01',
            'insurance_policy_number' => 'nullable|string|max:255',
            'description' => 'required|string',
        ]);

        $validated['company_id'] = auth()->user()->company_id;
        $validated['status'] = 'open';

        $claim = Claim::create($validated);

        return redirect()->route('claims.show', $claim)
            ->with('success', __('Claim created successfully.'));
    }

    public function show(Claim $claim)
    {
        $claim->load(['salesOrder.customer', 'evidences']);

        return view('claims.show', compact('claim'));
    }

    public function uploadEvidence(Request $request, Claim $claim)
    {
        $request->validate([
            'evidence' => 'required|file|mimes:jpg,jpeg,png,pdf,doc,docx|max:10240',
        ]);

        $path = $request->file('evidence')->store('claim-evidence/' . $claim->id, 'public');

        ClaimEvidence::create([
            'claim_id' => $claim->id,
            'file_path' => $path,
            'file_type' => $request->file('evidence')->getClientMimeType(),
        ]);

        return back()->with('success', __('Evidence uploaded successfully.'));
    }

    public function submit(Claim $claim)
    {
        $claim->update(['status' => 'submitted']);

        return back()->with('success', __('Claim submitted for review.'));
    }

    public function settle(Request $request, Claim $claim)
    {
        $validated = $request->validate([
            'settled_amount' => 'required|numeric|min:0',
        ]);

        $claim->update([
            'status' => 'settled',
            'settled_amount' => $validated['settled_amount'],
        ]);

        return back()->with('success', __('Claim settled.'));
    }

    public function reject(Claim $claim)
    {
        $claim->update(['status' => 'rejected']);

        return back()->with('success', __('Claim rejected.'));
    }
}
