@extends('layouts.app')

@section('title', __('app.product_details'))

@section('content')
   <div class="max-w-4xl mx-auto">
      <div class="flex items-center justify-between mb-4">
         <h2 class="text-xl font-semibold">{{ __('app.product_details') }}</h2>
         <div class="flex gap-2">
            <a href="{{ route('products.variants.index', $product) }}"
               class="px-3 py-2 {{ $product->has_variants ? 'bg-purple-600' : 'bg-gray-500' }} text-white rounded"
               title="{{ $product->has_variants ? __('app.manage_variants') : __('app.enable_variants') }}">
               {{ __('app.variants') }}
               @if ($product->has_variants)
                  <span class="ml-1 px-1.5 py-0.5 bg-white text-purple-600 rounded-full text-xs font-bold">
                     {{ $product->variants()->count() }}
                  </span>
               @endif
            </a>
            <a href="{{ route('products.edit', $product) }}" class="px-3 py-2 bg-primary text-white rounded">{{ __('app.edit') }}</a>
            <a href="{{ route('products.index') }}" class="px-3 py-2 border rounded">{{ __('app.back') }}</a>
         </div>
      </div>

      <div class="bg-white p-6 rounded shadow">
         <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
            <div class="md:col-span-1">
               @if ($product->image)
                  <img src="{{ asset('storage/' . $product->image) }}" alt="{{ $product->name }}"
                     class="w-full rounded border" />
               @else
                  <div class="w-full h-48 bg-gray-200 rounded flex items-center justify-center text-gray-500">
                     {{ __('app.no_file_chosen') }}
                  </div>
               @endif
            </div>

            <div class="md:col-span-2">
               <table class="w-full">
                  <tr class="border-b">
                     <td class="py-2 text-sm text-slate-600 w-1/3">{{ __('app.product_code') }}</td>
                     <td class="py-2 font-semibold">{{ $product->code }}</td>
                  </tr>
                  <tr class="border-b">
                     <td class="py-2 text-sm text-slate-600">{{ __('app.product_name') }}</td>
                     <td class="py-2 font-semibold">{{ $product->name }}</td>
                  </tr>
                  <tr class="border-b">
                     <td class="py-2 text-sm text-slate-600">{{ __('app.category') }}</td>
                     <td class="py-2">{{ $product->category?->name ?? '-' }}</td>
                  </tr>
                  <tr class="border-b">
                     <td class="py-2 text-sm text-slate-600">{{ __('app.unit') }}</td>
                     <td class="py-2">{{ $product->unit }}</td>
                  </tr>
                  <tr class="border-b">
                     <td class="py-2 text-sm text-slate-600">{{ __('app.total_stock') }}</td>
                     <td class="py-2">
                        @php
                           $totalStock = $product->warehouses->sum('pivot.stock');
                        @endphp
                        <span
                           class="font-semibold {{ $totalStock < $product->min_stock ? 'text-red-600' : 'text-green-600' }}">
                           {{ $totalStock }} {{ $product->unit }}
                        </span>
                        @if ($totalStock < $product->min_stock)
                           <span class="ml-2 px-2 py-1 text-xs bg-red-100 text-red-700 rounded">{{ __('app.low_stock') }}</span>
                        @endif
                     </td>
                  </tr>
                  <tr class="border-b">
                     <td class="py-2 text-sm text-slate-600">{{ __('app.stock_by_warehouse') }}</td>
                     <td class="py-2">
                        @if ($product->warehouses->count() > 0)
                           <div class="space-y-1">
                              @foreach ($product->warehouses as $warehouse)
                                 <div class="text-sm">
                                    <span class="font-medium">{{ $warehouse->name }}:</span>
                                    <span class="ml-1">{{ $warehouse->pivot->stock }} {{ $product->unit }}</span>
                                    @if ($warehouse->pivot->rack_location)
                                       <span
                                          class="text-slate-500 text-xs">({{ $warehouse->pivot->rack_location }})</span>
                                    @endif
                                 </div>
                              @endforeach
                           </div>
                        @else
                           <span class="text-slate-500 text-sm">{{ __('app.no_warehouses') }}</span>
                        @endif
                     </td>
                  </tr>
                  <tr class="border-b">
                     <td class="py-2 text-sm text-slate-600">{{ __('app.min_stock') }}</td>
                     <td class="py-2">{{ $product->min_stock }} {{ $product->unit }}</td>
                  </tr>
                  <tr class="border-b">
                     <td class="py-2 text-sm text-slate-600">{{ __('app.purchase_price') }}</td>
                     <td class="py-2">Rp {{ number_format($product->purchase_price, 0, ',', '.') }}</td>
                  </tr>
                  <tr class="border-b">
                     <td class="py-2 text-sm text-slate-600">{{ __('app.selling_price') }}</td>
                     <td class="py-2">Rp {{ number_format($product->selling_price, 0, ',', '.') }}</td>
                  </tr>

                  <tr class="border-b">
                     <td class="py-2 text-sm text-slate-600">{{ __('app.status') }}</td>
                     <td class="py-2">
                        <span
                           class="px-2 py-1 text-xs rounded {{ $product->status ? 'bg-green-100 text-green-700' : 'bg-gray-100 text-gray-600' }}">
                           {{ $product->status ? __('app.active') : __('app.inactive') }}
                        </span>
                     </td>
                  </tr>
                  <tr>
                     <td class="py-2 text-sm text-slate-600">{{ __('app.created') }}</td>
                     <td class="py-2 text-sm">{{ $product->created_at->format('d M Y H:i') }}</td>
                  </tr>
               </table>
            </div>
         </div>
      </div>
   </div>
@endsection
