<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\Category;
use App\Models\Warehouse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use App\Exports\ProductsExport;
use Maatwebsite\Excel\Facades\Excel;

class ProductController extends Controller
{
    public function index(Request $request)
    {
        $q = $request->query('q');
        $categoryId = $request->query('category_id');
        $warehouseId = $request->query('warehouse_id');
        $status = $request->query('status');

        $products = Product::with(['category', 'warehouses'])
            ->when($q, fn($query) => $query->where('name', 'like', "%{$q}%")->orWhere('code', 'like', "%{$q}%"))
            ->when($categoryId, fn($query) => $query->where('category_id', $categoryId))
            ->when($warehouseId, fn($query) => $query->whereHas('warehouses', function ($q) use ($warehouseId) {
                $q->where('warehouse_id', $warehouseId);
            }))
            ->when($status !== null, fn($query) => $query->where('status', $status))
            ->orderBy('name')
            ->paginate(20)
            ->withQueryString();

        $categories = Category::where('status', true)->orderBy('name')->get();
        $warehouses = Warehouse::active()->orderBy('name')->get();

        return view('products.index', compact('products', 'categories', 'warehouses', 'q', 'categoryId', 'warehouseId', 'status'));
    }

    public function create()
    {
        $categories = Category::where('status', true)->orderBy('name')->get();
        $warehouses = Warehouse::active()->orderBy('name')->get();
        return view('products.create', compact('categories', 'warehouses'));
    }

    public function store(Request $request)
    {
        $hasVariants = $request->has('has_variants');

        $data = $request->validate([
            'warehouse_id' => 'required|exists:warehouses,id',
            'code' => 'required|string|unique:products,code',
            'name' => 'required|string',
            'description' => 'nullable|string',
            'category_id' => 'nullable|exists:categories,id',
            'unit' => 'required|string',
            'min_stock' => 'required|integer|min:0',
            'purchase_price' => 'required|numeric|min:0',
            'selling_price' => 'required|numeric|min:0',
            'stock' => 'nullable|integer|min:0',
            'rack_location' => 'nullable|string',
            'image' => 'nullable|image|mimes:jpg,jpeg,png,webp|max:2048',
        ]);

        $warehouseId = $data['warehouse_id'];
        $rackLocation = $data['rack_location'] ?? null;
        $initialStock = $data['stock'] ?? 0;

        // Remove warehouse-specific fields from product data
        unset($data['warehouse_id'], $data['rack_location'], $data['stock']);

        // Handle checkboxes - if checked it sends 'on', if unchecked it sends nothing
        $data['status'] = $request->has('status') ? true : false;
        $data['has_variants'] = $request->has('has_variants') ? true : false;

        // Handle image upload
        if ($request->hasFile('image')) {
            $file = $request->file('image');
            $filename = time() . '_' . Str::slug(pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME)) . '.' . $file->getClientOriginalExtension();
            $path = $file->storeAs('products', $filename, 'public');
            $data['image'] = $path;
        }

        // Create product
        $product = Product::create($data);

        // Always attach to warehouse with the provided information
        // For products with variants, this serves as the default/initial warehouse
        // Stock will be 0 for variant products until variants are created
        $product->warehouses()->attach($warehouseId, [
            'stock' => $data['has_variants'] ? 0 : $initialStock,
            'rack_location' => $rackLocation,
            'min_stock' => null // Use global min_stock
        ]);

        // Redirect based on variant status
        if ($data['has_variants']) {
            return redirect()->route('products.variants.index', $product)
                ->with('success', 'Product created! Now add variants for this product.');
        }

        return redirect()->route('products.index')->with('success', 'Produk berhasil dibuat');
    }

    public function show(Product $product)
    {
        $product->load(['category', 'warehouses']);
        return view('products.show', compact('product'));
    }

    public function edit(Product $product)
    {
        $product->load('warehouses');
        $categories = Category::where('status', true)->orderBy('name')->get();
        $warehouses = Warehouse::active()->orderBy('name')->get();
        return view('products.edit', compact('product', 'categories', 'warehouses'));
    }

    public function update(Request $request, Product $product)
    {
        $data = $request->validate([
            'code' => 'required|string|unique:products,code,' . $product->id,
            'name' => 'required|string',
            'description' => 'nullable|string',
            'category_id' => 'nullable|exists:categories,id',
            'unit' => 'required|string',
            'min_stock' => 'required|integer|min:0',
            'purchase_price' => 'required|numeric|min:0',
            'selling_price' => 'required|numeric|min:0',
            'rack_location' => 'nullable|string',
            'image' => 'nullable|image|mimes:jpg,jpeg,png,webp|max:2048',
        ]);

        // Note: rack_location update should be handled separately per warehouse
        // For now, just update the product global fields
        $rackLocation = $data['rack_location'] ?? null;
        unset($data['rack_location']);

        // Handle checkbox - if checked it sends 'on', if unchecked it sends nothing
        $data['status'] = $request->has('status') ? true : false;

        // Handle image upload
        if ($request->hasFile('image')) {
            // Delete old image
            if ($product->image && Storage::disk('public')->exists($product->image)) {
                Storage::disk('public')->delete($product->image);
            }

            $file = $request->file('image');
            $filename = time() . '_' . Str::slug(pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME)) . '.' . $file->getClientOriginalExtension();
            $path = $file->storeAs('products', $filename, 'public');
            $data['image'] = $path;
        }

        $product->update($data);

        // Update rack location for first warehouse (legacy compatibility)
        // TODO: In future, allow updating rack location per warehouse via UI
        if ($product->warehouses()->count() > 0 && $rackLocation !== null) {
            $firstWarehouse = $product->warehouses()->first();
            $product->warehouses()->updateExistingPivot($firstWarehouse->id, [
                'rack_location' => $rackLocation
            ]);
        }

        return redirect()->route('products.index')->with('status', 'Product updated successfully');
    }

    public function destroy(Product $product)
    {
        // Delete image if exists
        if ($product->image && Storage::disk('public')->exists($product->image)) {
            Storage::disk('public')->delete($product->image);
        }

        $product->delete();
        return redirect()->route('products.index')->with('status', 'Product deleted successfully');
    }

    public function bulkDestroy(Request $request)
    {
        if ($request->has('delete_all') && $request->delete_all == '1') {
            $q = $request->input('q');
            $categoryId = $request->input('category_id');
            $warehouseId = $request->input('warehouse_id');
            $status = $request->input('status');

            $query = Product::query();

            // Re-apply filters
            $query->when($q, fn($qBuilder) => $qBuilder->where(function ($sub) use ($q) {
                $sub->where('name', 'like', "%{$q}%")->orWhere('code', 'like', "%{$q}%");
            }))
            ->when($categoryId, fn($qBuilder) => $qBuilder->where('category_id', $categoryId))
            ->when($warehouseId, fn($qBuilder) => $qBuilder->whereHas('warehouses', function ($sub) use ($warehouseId) {
                $sub->where('warehouse_id', $warehouseId);
            }))
            ->when($status !== null, fn($qBuilder) => $qBuilder->where('status', $status));

            $count = $query->count();
            $query->delete();

            return back()->with('status', "All {$count} products selected have been deleted.");
        }

        $validated = $request->validate([
            'ids' => 'required|array',
            'ids.*' => 'exists:products,id',
        ]);

        $count = count($validated['ids']);
        Product::whereIn('id', $validated['ids'])->delete();

        return back()->with('status', "{$count} products deleted successfully");
    }

    public function import(Request $request)
    {
        $request->validate([
            'file' => 'required|mimes:xlsx,csv,xls|max:10240',
        ]);

        try {
            $path = $request->file('file')->store('imports', 'local');

            $stats = app(\App\Services\ImportService::class)->parseFile($path);
            $totalRows = $stats['total_rows'] ?? 0;

            // Create an ImportJob record for tracking
            $job = \App\Models\ImportJob::create([
                'company_id' => auth()->user()->company_id,
                'type' => 'products',
                'file_path' => $path,
                'status' => \App\Models\ImportJob::STATUS_PROCESSING,
                'total_rows' => $totalRows,
                'user_id' => auth()->id(),
            ]);

            // Dispatch to queue — this returns immediately
            \App\Jobs\ProcessImportJob::dispatch($job->id, auth()->user()->company_id);

            return back()->with('success', __('Import started in background. Check the Import Center for progress.'));
        } catch (\Exception $e) {
            return back()->with('error', 'Import Failed: ' . $e->getMessage());
        }
    }

    public function export(Request $request)
    {
        return Excel::download(new ProductsExport($request), 'products-' . date('Y-m-d') . '.xlsx');
    }

    public function getAll(Request $request)
    {
        $warehouseId = $request->query('warehouse_id');

        $query = Product::with('category')
            ->where('status', true)
            ->orderBy('name');

        if ($warehouseId) {
            $query->where('warehouse_id', $warehouseId);
        }

        $products = $query->get();

        return response()->json($products);
    }
}
