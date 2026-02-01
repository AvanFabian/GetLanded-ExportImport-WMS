<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\StockIn;
use App\Models\StockOut;
use App\Models\Category;
use App\Models\Supplier;
use Illuminate\Http\Request;
use Barryvdh\DomPDF\Facade\Pdf;
use Maatwebsite\Excel\Facades\Excel;

class ReportController extends Controller
{
    public function index()
    {
        return view('reports.index');
    }

    // Current Stock Report
    public function stock(Request $request)
    {
        $categoryId = $request->query('category_id');
        $lowStock = $request->query('low_stock');
        $warehouseId = $request->query('warehouse_id');

        $products = Product::with(['category', 'warehouses'])
            ->when($categoryId, fn($query) => $query->where('category_id', $categoryId))
            ->when($warehouseId, fn($query) => $query->whereHas('warehouses', function ($q) use ($warehouseId) {
                $q->where('warehouse_id', $warehouseId);
            }))
            ->orderBy('name')
            ->get();

        // Filter low stock after loading (since stock is in pivot table)
        if ($lowStock) {
            $products = $products->filter(function ($product) {
                $totalStock = $product->warehouses->sum('pivot.stock');
                return $totalStock < $product->min_stock;
            });
        }

        $categories = Category::where('status', true)->orderBy('name')->get();
        $warehouses = \App\Models\Warehouse::active()->orderBy('name')->get();

        if ($request->query('export') === 'pdf') {
            $pdf = Pdf::loadView('reports.stock-pdf', compact('products'));
            return $pdf->download('stock-report-' . date('Y-m-d') . '.pdf');
        }

        return view('reports.stock', compact('products', 'categories', 'warehouses', 'categoryId', 'warehouseId', 'lowStock'));
    }

    // Transaction Reports
    public function transactions(Request $request)
    {
        $type = $request->query('type');
        $dateFrom = $request->query('from', date('Y-m-01'));
        $dateTo = $request->query('to', date('Y-m-d'));
        $supplierId = $request->query('supplier_id');

        // Build the combined query for both stock in and stock out
        $stockIns = StockIn::with(['supplier', 'details'])
            ->whereDate('date', '>=', $dateFrom)
            ->whereDate('date', '<=', $dateTo)
            ->when($supplierId, fn($query) => $query->where('supplier_id', $supplierId))
            ->when($type === 'in', fn($query) => $query)
            ->when(!$type || $type === '', fn($query) => $query)
            ->select('id', 'transaction_code', 'date as transaction_date', 'supplier_id', 'total', 'created_at')
            ->selectRaw("'in' as type")
            ->get();

        $stockOuts = StockOut::with('details')
            ->whereDate('date', '>=', $dateFrom)
            ->whereDate('date', '<=', $dateTo)
            ->when($type === 'out', fn($query) => $query)
            ->when(!$type || $type === '', fn($query) => $query)
            ->select('id', 'transaction_code', 'date as transaction_date', 'customer', 'total', 'created_at')
            ->selectRaw("'out' as type")
            ->get();

        // Combine and sort transactions
        if ($type === 'in') {
            $transactions = $stockIns->sortByDesc('transaction_date')->paginate(20);
        } elseif ($type === 'out') {
            $transactions = $stockOuts->sortByDesc('transaction_date')->paginate(20);
        } else {
            $transactions = $stockIns->merge($stockOuts)
                ->sortByDesc('transaction_date')
                ->values();

            // Manual pagination
            $page = $request->query('page', 1);
            $perPage = 20;
            $transactions = new \Illuminate\Pagination\LengthAwarePaginator(
                $transactions->forPage($page, $perPage),
                $transactions->count(),
                $perPage,
                $page,
                ['path' => $request->url(), 'query' => $request->query()]
            );
        }

        // Calculate stats
        $stats = [
            'total_in' => StockIn::whereDate('date', '>=', $dateFrom)
                ->whereDate('date', '<=', $dateTo)
                ->when($supplierId, fn($query) => $query->where('supplier_id', $supplierId))
                ->count(),
            'total_out' => StockOut::whereDate('date', '>=', $dateFrom)
                ->whereDate('date', '<=', $dateTo)
                ->count(),
            'total_value' => StockIn::whereDate('date', '>=', $dateFrom)
                ->whereDate('date', '<=', $dateTo)
                ->when($supplierId, fn($query) => $query->where('supplier_id', $supplierId))
                ->sum('total') +
                StockOut::whereDate('date', '>=', $dateFrom)
                ->whereDate('date', '<=', $dateTo)
                ->sum('total'),
        ];

        $suppliers = Supplier::orderBy('name')->get();

        if ($request->query('export') === 'pdf') {
            $allTransactions = $stockIns->merge($stockOuts)->sortByDesc('transaction_date');
            $pdf = Pdf::loadView('reports.transactions-pdf', compact('allTransactions', 'stats', 'dateFrom', 'dateTo'));
            return $pdf->download('transactions-report-' . date('Y-m-d') . '.pdf');
        }

        return view('reports.transactions', compact('transactions', 'stats', 'dateFrom', 'dateTo', 'suppliers', 'supplierId'));
    }

    // Inventory Value Report
    public function inventoryValue(Request $request)
    {
        $products = Product::with(['category', 'warehouses'])
            ->where('status', true)
            ->paginate(50);

        $totalValue = Product::with('warehouses')
            ->where('status', true)
            ->get()
            ->sum(function ($product) {
                $totalStock = $product->warehouses->sum('pivot.stock');
                return $totalStock * $product->cost;
            });

        // Get categories with their total values
        $categories = Category::withCount('products')
            ->get()
            ->map(function ($category) {
                $categoryProducts = Product::with('warehouses')
                    ->where('category_id', $category->id)
                    ->where('status', true)
                    ->get();

                $total_value = $categoryProducts->sum(function ($product) {
                    $totalStock = $product->warehouses->sum('pivot.stock');
                    return $totalStock * $product->cost;
                });

                $category->total_value = $total_value;
                return $category;
            })
            ->filter(fn($cat) => $cat->total_value > 0);

        if ($request->query('export') === 'pdf') {
            $allProducts = Product::with(['category', 'warehouses'])
                ->where('status', true)
                ->get();
            $pdf = Pdf::loadView('reports.inventory-value-pdf', compact('allProducts', 'totalValue', 'categories'));
            return $pdf->download('inventory-value-' . date('Y-m-d') . '.pdf');
        }

        return view('reports.inventory-value', compact('products', 'totalValue', 'categories'));
    }

    // Stock Card (Movement History)
    public function stockCard(Request $request)
    {
        $productId = $request->query('product_id');
        $dateFrom = $request->query('date_from', date('Y-m-01'));
        $dateTo = $request->query('date_to', date('Y-m-d'));

        $product = null;
        $movements = collect();

        if ($productId) {
            $product = Product::with(['category', 'warehouses'])->find($productId);

            if ($product) {
                // Get stock in movements
                $stockIns = $product->stockInDetails()
                    ->with('stockIn')
                    ->whereHas('stockIn', function ($query) use ($dateFrom, $dateTo) {
                        $query->whereDate('date', '>=', $dateFrom)
                            ->whereDate('date', '<=', $dateTo);
                    })
                    ->get()
                    ->map(function ($detail) {
                        return [
                            'date' => $detail->stockIn->date,
                            'code' => $detail->stockIn->transaction_code,
                            'type' => 'Stock In',
                            'in' => $detail->quantity,
                            'out' => 0,
                            'notes' => $detail->stockIn->notes,
                        ];
                    });

                // Get stock out movements
                $stockOuts = $product->stockOutDetails()
                    ->with('stockOut')
                    ->whereHas('stockOut', function ($query) use ($dateFrom, $dateTo) {
                        $query->whereDate('date', '>=', $dateFrom)
                            ->whereDate('date', '<=', $dateTo);
                    })
                    ->get()
                    ->map(function ($detail) {
                        return [
                            'date' => $detail->stockOut->date,
                            'code' => $detail->stockOut->transaction_code,
                            'type' => 'Stock Out',
                            'in' => 0,
                            'out' => $detail->quantity,
                            'notes' => $detail->stockOut->notes,
                        ];
                    });

                $movements = $stockIns->merge($stockOuts)->sortBy('date');
            }
        }

        $products = Product::where('status', true)->orderBy('name')->get();

        if ($request->query('export') === 'pdf' && $product) {
            $pdf = Pdf::loadView('reports.stock-card-pdf', compact('product', 'movements', 'dateFrom', 'dateTo'));
            return $pdf->download('stock-card-' . $product->code . '-' . date('Y-m-d') . '.pdf');
        }

        return view('reports.stock-card', compact('products', 'product', 'movements', 'productId', 'dateFrom', 'dateTo'));
    }
}
