<?php

namespace App\Http\Controllers;

use App\Models\CustomsDeclaration;
use App\Models\CustomsPermit;
use App\Models\FtaScheme;
use App\Models\OutboundShipment;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class CustomsDeclarationController extends Controller
{
    public function index(Request $request)
    {
        $companyId = auth()->user()->company_id;

        $declarations = CustomsDeclaration::where('company_id', $companyId)
            ->with(['outboundShipment'])
            ->when($request->search, function ($q, $search) {
                $q->where('declaration_number', 'like', "%{$search}%")
                  ->orWhere('hs_code', 'like', "%{$search}%");
            })
            ->when($request->status, fn ($q, $s) => $q->where('status', $s))
            ->when($request->declaration_type, fn ($q, $t) => $q->where('declaration_type', $t))
            ->latest('declaration_date')
            ->paginate(20)
            ->withQueryString();

        // Expiring permits
        $expiringPermits = CustomsPermit::where('company_id', $companyId)
            ->where('status', 'active')
            ->where('expiry_date', '<=', now()->addDays(30))
            ->orderBy('expiry_date')
            ->get();

        return view('customs.index', compact('declarations', 'expiringPermits'));
    }

    public function create()
    {
        $companyId = auth()->user()->company_id;

        $shipments = OutboundShipment::where('company_id', $companyId)
            ->with('salesOrder.customer')
            ->orderBy('shipment_date', 'desc')
            ->get();

        $products = Product::where('company_id', $companyId)->where('status', true)->orderBy('name')->get();

        $ftaSchemes = FtaScheme::where('company_id', $companyId)->where('is_active', true)->get();

        return view('customs.create', compact('shipments', 'products', 'ftaSchemes'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'outbound_shipment_id' => 'nullable|exists:outbound_shipments,id',
            'declaration_type' => 'required|in:export,import',
            'declaration_date' => 'required|date',
            'customs_office' => 'nullable|string|max:255',
            'hs_code' => 'nullable|string|max:20',
            'declared_value' => 'required|numeric|min:0',
            'currency_code' => 'nullable|string|size:3',
            'duty_rate' => 'nullable|numeric|min:0',
            'vat_rate' => 'nullable|numeric|min:0',
            'pph_rate' => 'nullable|numeric|min:0',
            'anti_dumping_rate' => 'nullable|numeric|min:0',
            'fta_scheme' => 'nullable|string|max:50',
            'excise_amount' => 'nullable|numeric|min:0',
            'notes' => 'nullable|string',
            'items' => 'nullable|array',
            'items.*.product_id' => 'required|exists:products,id',
            'items.*.hs_code' => 'nullable|string|max:20',
            'items.*.description' => 'required|string',
            'items.*.quantity' => 'required|integer|min:1',
            'items.*.unit_value' => 'required|numeric|min:0',
            'items.*.country_of_origin' => 'nullable|string|max:255',
        ]);

        $companyId = auth()->user()->company_id;

        $declaration = DB::transaction(function () use ($validated, $companyId) {
            // Use DutyCalculationService
            $dutyService = app(\App\Services\DutyCalculationService::class);
            $result = $dutyService->calculate(
                declaredValue: (float) $validated['declared_value'],
                bmRate: (float) ($validated['duty_rate'] ?? 0),
                ppnRate: isset($validated['vat_rate']) ? (float) $validated['vat_rate'] : null,
                pphRate: isset($validated['pph_rate']) ? (float) $validated['pph_rate'] : null,
                adRate: (float) ($validated['anti_dumping_rate'] ?? 0),
                exciseAmount: (float) ($validated['excise_amount'] ?? 0),
            );

            $declaration = CustomsDeclaration::create([
                'company_id' => $companyId,
                'outbound_shipment_id' => $validated['outbound_shipment_id'] ?? null,
                'declaration_type' => $validated['declaration_type'],
                'declaration_date' => $validated['declaration_date'],
                'customs_office' => $validated['customs_office'] ?? null,
                'hs_code' => $validated['hs_code'] ?? null,
                'declared_value' => $validated['declared_value'],
                'currency_code' => $validated['currency_code'] ?? 'USD',
                'duty_rate' => $validated['duty_rate'] ?? 0,
                'duty_amount' => $result['bm_amount'],
                'vat_rate' => $validated['vat_rate'] ?? 11,
                'vat_amount' => $result['ppn_amount'],
                'pph_rate' => $validated['pph_rate'] ?? 2.5,
                'pph_amount' => $result['pph_amount'],
                'anti_dumping_rate' => $validated['anti_dumping_rate'] ?? 0,
                'anti_dumping_amount' => $result['anti_dumping_amount'],
                'fta_scheme' => $validated['fta_scheme'] ?? null,
                'excise_amount' => $validated['excise_amount'] ?? 0,
                'total_tax' => $result['total_tax'],
                'status' => 'draft',
                'notes' => $validated['notes'] ?? null,
            ]);

            if (!empty($validated['items'])) {
                foreach ($validated['items'] as $item) {
                    $declaration->items()->create([
                        'product_id' => $item['product_id'],
                        'hs_code' => $item['hs_code'] ?? null,
                        'description' => $item['description'],
                        'quantity' => $item['quantity'],
                        'unit_value' => $item['unit_value'],
                        'total_value' => $item['quantity'] * $item['unit_value'],
                        'country_of_origin' => $item['country_of_origin'] ?? null,
                    ]);
                }
            }

            return $declaration;
        });

        return redirect()->route('customs.show', $declaration)
            ->with('success', __('Customs declaration created.'));
    }

    public function show(CustomsDeclaration $custom)
    {
        $custom->load(['outboundShipment.salesOrder.customer', 'items.product']);

        return view('customs.show', compact('custom'));
    }

    public function updateStatus(Request $request, CustomsDeclaration $custom)
    {
        $validated = $request->validate([
            'status' => 'required|in:' . implode(',', array_keys(CustomsDeclaration::STATUSES)),
            'declaration_number' => 'nullable|string|max:255',
        ]);

        $custom->update($validated);

        return back()->with('success', __('Declaration status updated.'));
    }

    // --- Customs Permits ---

    public function permits()
    {
        $companyId = auth()->user()->company_id;

        $permits = CustomsPermit::where('company_id', $companyId)->orderBy('expiry_date')->get();

        return view('customs.permits', compact('permits'));
    }

    public function storePermit(Request $request)
    {
        $validated = $request->validate([
            'permit_number' => 'required|string|max:255',
            'permit_type' => 'required|string',
            'issue_date' => 'required|date',
            'expiry_date' => 'required|date|after:issue_date',
            'issuing_authority' => 'nullable|string|max:255',
            'notes' => 'nullable|string',
        ]);

        $validated['company_id'] = auth()->user()->company_id;
        $validated['status'] = 'active';

        CustomsPermit::create($validated);

        return redirect()->route('customs.permits')->with('success', __('Customs permit added.'));
    }
}
