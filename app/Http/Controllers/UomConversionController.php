<?php

namespace App\Http\Controllers;

use App\Models\UomConversion;
use App\Services\UomConversionService;
use Illuminate\Http\Request;
use Inertia\Inertia;

class UomConversionController extends Controller
{
    public function __construct(
        protected UomConversionService $conversionService
    ) {}

    public function index()
    {
        $conversions = UomConversion::where('company_id', auth()->user()->company_id)
            ->with('product')
            ->orderBy('from_unit')
            ->get();

        $commonConversions = UomConversionService::getCommonConversions();

        return Inertia::render('Settings/UomConversions', [
            'conversions' => $conversions,
            'commonConversions' => $commonConversions,
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'from_unit' => 'required|string|max:20',
            'to_unit' => 'required|string|max:20',
            'conversion_factor' => 'required|numeric|min:0.0000000001',
            'product_id' => 'nullable|exists:products,id',
            'is_default' => 'boolean',
        ]);

        // Check uniqueness
        $exists = UomConversion::where('company_id', auth()->user()->company_id)
            ->where('product_id', $validated['product_id'] ?? null)
            ->where('from_unit', strtoupper($validated['from_unit']))
            ->where('to_unit', strtoupper($validated['to_unit']))
            ->exists();

        if ($exists) {
            return back()->withErrors(['from_unit' => 'This conversion already exists.']);
        }

        UomConversion::create([
            'company_id' => auth()->user()->company_id,
            'product_id' => $validated['product_id'] ?? null,
            'from_unit' => strtoupper($validated['from_unit']),
            'to_unit' => strtoupper($validated['to_unit']),
            'conversion_factor' => $validated['conversion_factor'],
            'is_default' => $validated['is_default'] ?? false,
            'is_active' => true,
        ]);

        return back()->with('success', 'Conversion added successfully.');
    }

    public function update(Request $request, UomConversion $conversion)
    {
        $this->authorize('update', $conversion);

        $validated = $request->validate([
            'conversion_factor' => 'required|numeric|min:0.0000000001',
            'is_default' => 'boolean',
            'is_active' => 'boolean',
        ]);

        $conversion->update($validated);

        return back()->with('success', 'Conversion updated.');
    }

    public function destroy(UomConversion $conversion)
    {
        $this->authorize('delete', $conversion);

        $conversion->delete();

        return back()->with('success', 'Conversion deleted.');
    }

    /**
     * Convert units (AJAX endpoint)
     */
    public function convert(Request $request)
    {
        $validated = $request->validate([
            'quantity' => 'required|numeric',
            'from_unit' => 'required|string',
            'to_unit' => 'required|string',
            'product_id' => 'nullable|integer',
        ]);

        try {
            $result = $this->conversionService->convert(
                $validated['quantity'],
                $validated['from_unit'],
                $validated['to_unit'],
                $validated['product_id'] ?? null
            );

            return response()->json($result);
        } catch (\InvalidArgumentException $e) {
            return response()->json(['error' => $e->getMessage()], 400);
        }
    }

    /**
     * Get available units for a product
     */
    public function availableUnits(Request $request)
    {
        $productId = $request->query('product_id');
        
        $units = $this->conversionService->getAvailableUnits($productId);

        return response()->json(['units' => $units]);
    }

    /**
     * Quick-add common conversions
     */
    public function addCommon(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string',
        ]);

        $common = collect(UomConversionService::getCommonConversions())
            ->firstWhere('name', $validated['name']);

        if (!$common) {
            return back()->withErrors(['name' => 'Unknown common conversion.']);
        }

        $this->conversionService->setConversion(
            $common['from'],
            $common['to'],
            $common['factor']
        );

        return back()->with('success', "Added: {$common['name']}");
    }
}
