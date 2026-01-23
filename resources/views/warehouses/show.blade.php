@extends('layouts.app')

@section('title', __('app.warehouse_name') . ' - ' . __('app.details'))

@section('content')
   <div class="max-w-7xl mx-auto">
      <div class="flex items-center justify-between mb-4">
         <h2 class="text-xl font-semibold">{{ __('app.warehouse_name') }} - {{ __('app.details') }}</h2>
         <div class="space-x-2">
            <a href="{{ route('warehouses.edit', $warehouse) }}" class="px-3 py-2 bg-primary text-white rounded">{{ __('app.edit') }}</a>
            <a href="{{ route('warehouses.index') }}" class="px-3 py-2 border rounded">{{ __('app.back') }}</a>
         </div>
      </div>

      <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">
         <div class="bg-white p-4 rounded shadow">
            <div class="text-sm text-gray-500">{{ __('app.total_products') }}</div>
            <div class="text-2xl font-bold">{{ $productCount }}</div>
         </div>
         <div class="bg-white p-4 rounded shadow">
            <div class="text-sm text-gray-500">{{ __('app.inventory_value') }}</div>
            <div class="text-2xl font-bold">Rp {{ number_format($totalStockValue, 0, ',', '.') }}</div>
         </div>
         <div class="bg-white p-4 rounded shadow">
            <div class="text-sm text-gray-500">{{ __('app.total') }} {{ __('app.transactions') }}</div>
            <div class="text-2xl font-bold">{{ $stockInsCount + $stockOutsCount }}</div>
         </div>
      </div>

      <div class="bg-white rounded shadow mb-6">
         <div class="p-4 border-b">
            <h3 class="font-semibold">{{ __('app.po_information') }}</h3>
         </div>
         <div class="p-4">
            <div class="grid grid-cols-2 gap-4">
               <div>
                  <div class="text-sm text-gray-500">{{ __('app.code') }}</div>
                  <div class="font-medium">{{ $warehouse->code }}</div>
               </div>
               <div>
                  <div class="text-sm text-gray-500">{{ __('app.name') }}</div>
                  <div class="font-medium">{{ $warehouse->name }}</div>
               </div>
               <div class="col-span-2">
                  <div class="text-sm text-gray-500">{{ __('app.address') }}</div>
                  <div class="font-medium">{{ $warehouse->address ?? '-' }}</div>
               </div>
               <div>
                  <div class="text-sm text-gray-500">{{ __('app.city') }}</div>
                  <div class="font-medium">{{ $warehouse->city ?? '-' }}</div>
               </div>
               <div>
                  <div class="text-sm text-gray-500">{{ __('app.province') }}</div>
                  <div class="font-medium">{{ $warehouse->province ?? '-' }}</div>
               </div>
               <div>
                  <div class="text-sm text-gray-500">{{ __('app.postal_code') }}</div>
                  <div class="font-medium">{{ $warehouse->postal_code ?? '-' }}</div>
               </div>
               <div>
                  <div class="text-sm text-gray-500">{{ __('app.phone') }}</div>
                  <div class="font-medium">{{ $warehouse->phone ?? '-' }}</div>
               </div>
               <div>
                  <div class="text-sm text-gray-500">{{ __('app.email') }}</div>
                  <div class="font-medium">{{ $warehouse->email ?? '-' }}</div>
               </div>
               <div>
                  <div class="text-sm text-gray-500">{{ __('app.status') }}</div>
                  <div>
                     @if ($warehouse->is_active)
                        <span class="px-2 py-1 text-xs bg-success text-white rounded">{{ __('app.active') }}</span>
                     @else
                        <span class="px-2 py-1 text-xs bg-secondary text-white rounded">{{ __('app.inactive') }}</span>
                     @endif
                     @if ($warehouse->is_default)
                        <span class="ml-2 px-2 py-1 text-xs bg-primary text-white rounded">{{ __('app.default') }}</span>
                     @endif
                  </div>
               </div>
            </div>
         </div>
      </div>

      <div class="bg-white rounded shadow mb-6">
         <div class="p-4 border-b">
            <h3 class="font-semibold">{{ __('app.summary') }}</h3>
         </div>
         <div class="p-4">
            <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
               <div>
                  <div class="text-sm text-gray-500">{{ __('app.stock_in') }}</div>
                  <div class="text-xl font-bold text-success">{{ $stockInsCount }}</div>
               </div>
               <div>
                  <div class="text-sm text-gray-500">{{ __('app.stock_out') }}</div>
                  <div class="text-xl font-bold text-danger">{{ $stockOutsCount }}</div>
               </div>
               <div>
                  <div class="text-sm text-gray-500">{{ __('app.from_warehouse') }}</div>
                  <div class="text-xl font-bold text-warning">{{ $transfersFromCount }}</div>
               </div>
               <div>
                  <div class="text-sm text-gray-500">{{ __('app.to_warehouse') }}</div>
                  <div class="text-xl font-bold text-primary">{{ $transfersToCount }}</div>
               </div>
            </div>
         </div>
      </div>

      <div class="bg-white rounded shadow">
         <div class="p-4 border-b">
            <h3 class="font-semibold">{{ __('app.products') }}</h3>
         </div>
         <div class="overflow-x-auto">
            <table class="min-w-full">
               <thead class="bg-gray-50">
                  <tr>
                     <th class="text-left p-3">{{ __('app.code') }}</th>
                     <th class="text-left p-3">{{ __('app.name') }}</th>
                     <th class="text-left p-3">{{ __('app.category') }}</th>
                     <th class="text-left p-3">{{ __('app.stock') }}</th>
                     <th class="text-left p-3">{{ __('app.unit') }}</th>
                     <th class="text-left p-3">{{ __('app.total') }}</th>
                  </tr>
               </thead>
               <tbody>
                  @forelse($warehouse->products as $product)
                     <tr class="border-t hover:bg-gray-50">
                        <td class="p-3">{{ $product->code }}</td>
                        <td class="p-3">{{ $product->name }}</td>
                        <td class="p-3">{{ $product->category->name ?? '-' }}</td>
                        <td class="p-3">
                           @php
                              $stockInWarehouse = $product->pivot->stock ?? 0;
                              $minStock = $product->pivot->min_stock ?? $product->min_stock;
                           @endphp
                           <span class="@if ($stockInWarehouse <= $minStock) text-red-600 font-bold @endif">
                              {{ $stockInWarehouse }}
                           </span>
                        </td>
                        <td class="p-3">{{ $product->unit }}</td>
                        <td class="p-3">Rp
                           {{ number_format($stockInWarehouse * $product->purchase_price, 0, ',', '.') }}
                        </td>
                     </tr>
                  @empty
                     <tr>
                        <td colspan="6" class="p-8 text-center text-gray-500">
                           {{ __('app.no_products') }}
                        </td>
                     </tr>
                  @endforelse
               </tbody>
            </table>
         </div>
      </div>
   </div>
@endsection
