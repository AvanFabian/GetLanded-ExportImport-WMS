<?php

namespace App\Http\Controllers;

use App\Models\Container;
use App\Models\ContainerItem;
use App\Models\OutboundShipment;
use App\Models\Product;
use Illuminate\Http\Request;

class ContainerController extends Controller
{
    public function index(Request $request)
    {
        $companyId = auth()->user()->company_id;

        $containers = Container::where('company_id', $companyId)
            ->with(['outboundShipment'])
            ->when($request->search, function ($q, $search) {
                $q->where('container_number', 'like', "%{$search}%")
                  ->orWhere('seal_number', 'like', "%{$search}%");
            })
            ->when($request->status, fn ($q, $s) => $q->where('status', $s))
            ->when($request->container_type, fn ($q, $t) => $q->where('container_type', $t))
            ->latest()
            ->paginate(20)
            ->withQueryString();

        return view('containers.index', compact('containers'));
    }

    public function show(Container $container)
    {
        $container->load(['outboundShipment.salesOrder.customer', 'items.product', 'items.batch']);

        $companyId = auth()->user()->company_id;
        $products = Product::where('company_id', $companyId)->where('is_active', true)->orderBy('name')->get();

        return view('containers.show', compact('container', 'products'));
    }

    public function stuffing(Request $request, Container $container)
    {
        $validated = $request->validate([
            'items' => 'required|array|min:1',
            'items.*.product_id' => 'required|exists:products,id',
            'items.*.batch_id' => 'nullable|exists:batches,id',
            'items.*.quantity' => 'required|integer|min:1',
            'items.*.weight_kg' => 'required|numeric|min:0',
            'items.*.volume_cbm' => 'required|numeric|min:0',
            'items.*.carton_count' => 'nullable|integer|min:0',
            'items.*.remarks' => 'nullable|string|max:255',
        ]);

        foreach ($validated['items'] as $item) {
            $container->items()->create($item);
        }

        $container->update(['status' => 'loading']);

        return back()->with('success', __('Items added to container successfully.'));
    }

    public function seal(Request $request, Container $container)
    {
        $validated = $request->validate([
            'seal_number' => 'required|string|max:255',
        ]);

        $container->update([
            'seal_number' => $validated['seal_number'],
            'status' => 'sealed',
        ]);

        return back()->with('success', __('Container sealed.'));
    }

    public function removeItem(ContainerItem $containerItem)
    {
        $containerItem->delete();

        return back()->with('success', __('Item removed from container.'));
    }
}
