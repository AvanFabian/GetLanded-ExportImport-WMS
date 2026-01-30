<?php

namespace App\Exports;

use App\Models\Product;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Illuminate\Http\Request;

class ProductsExport implements FromQuery, WithHeadings, WithMapping
{
    protected $request;

    public function __construct(Request $request)
    {
        $this->request = $request;
    }

    public function query()
    {
        $q = $this->request->query('q');
        $categoryId = $this->request->query('category_id');
        $warehouseId = $this->request->query('warehouse_id');
        $status = $this->request->query('status');

        return Product::query()
            ->with(['category', 'warehouses'])
            ->when($q, fn($query) => $query->where('name', 'like', "%{$q}%")->orWhere('code', 'like', "%{$q}%"))
            ->when($categoryId, fn($query) => $query->where('category_id', $categoryId))
            ->when($warehouseId, fn($query) => $query->whereHas('warehouses', function ($q) use ($warehouseId) {
                $q->where('warehouse_id', $warehouseId);
            }))
            ->when($status !== null, fn($query) => $query->where('status', $status))
            ->orderBy('name');
    }

    public function headings(): array
    {
        return [
            'Code',
            'Name',
            'Category',
            'Warehouses & Stock',
            'Total Stock',
            'Unit',
            'Min Stock',
            'Purchase Price',
            'Selling Price',
            'Status',
        ];
    }

    public function map($product): array
    {
        // Format warehouses as "Name: Stock (Rack)"
        $warehouseInfo = $product->warehouses->map(function ($wh) {
            $rack = $wh->pivot->rack_location ? " [{$wh->pivot->rack_location}]" : "";
            return "{$wh->name}: {$wh->pivot->stock}{$rack}";
        })->join(",\n");

        return [
            $product->code,
            $product->name,
            $product->category->name ?? '-',
            $warehouseInfo,
            $product->total_stock,
            $product->unit,
            $product->min_stock,
            $product->purchase_price,
            $product->selling_price,
            $product->status ? 'Active' : 'Inactive',
        ];
    }
}
