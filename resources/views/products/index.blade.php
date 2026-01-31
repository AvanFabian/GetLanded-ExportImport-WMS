@extends('layouts.app')

@section('title', __('app.products'))

@section('content')
   <div class="max-w-7xl mx-auto">
      <div class="flex items-center justify-between mb-4">
         <h2 class="text-xl font-semibold">{{ __('app.products') }}</h2>
         <div class="flex gap-2">
            <button onclick="printSelectedLabels()" class="px-3 py-2 bg-purple-600 text-white rounded hover:bg-purple-700"
               title="{{ __('app.print_label') }}">
               <svg class="w-5 h-5 inline-block mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                     d="M12 4v1m6 11h2m-6 0h-2v4m0-11v3m0 0h.01M12 12h4.01M16 20h4M4 12h4m12 0h.01M5 8h2a1 1 0 001-1V5a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1zm12 0h2a1 1 0 001-1V5a1 1 0 00-1-1h-2a1 1 0 00-1 1v2a1 1 0 001 1zM5 20h2a1 1 0 001-1v-2a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1z">
                  </path>
               </svg>
               {{ __('app.print_label') }}
            </button>
            <a href="{{ route('imports.create') }}" class="px-3 py-2 bg-blue-500 text-white rounded hover:bg-blue-600">
               <svg class="w-5 h-5 inline-block mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"></path></svg>
               Import
            </a>
            <a href="{{ route('products.export') }}"
               class="px-3 py-2 bg-success text-white rounded">{{ __('app.export_excel') }}</a>
            <a href="{{ route('products.create') }}"
               class="px-3 py-2 bg-primary text-white rounded">{{ __('app.add_product') }}</a>
         </div>
      </div>

      <form method="GET" class="mb-4 bg-white p-4 rounded shadow">
         <div class="grid grid-cols-1 md:grid-cols-5 gap-3">
            <div>
               <input type="text" name="q" value="{{ $q ?? '' }}"
                  placeholder="{{ __('app.search_products') }}" class="w-full border rounded px-2 py-1" />
            </div>
            <div>
               <select name="warehouse_id" class="w-full border rounded px-2 py-1">
                  <option value="">{{ __('app.all_warehouses') }}</option>
                  @foreach ($warehouses as $wh)
                     <option value="{{ $wh->id }}" {{ $warehouseId == $wh->id ? 'selected' : '' }}>
                        {{ $wh->name }}</option>
                  @endforeach
               </select>
            </div>
            <div>
               <select name="category_id" class="w-full border rounded px-2 py-1">
                  <option value="">{{ __('app.all_categories') }}</option>
                  @foreach ($categories as $cat)
                     <option value="{{ $cat->id }}" {{ $categoryId == $cat->id ? 'selected' : '' }}>
                        {{ $cat->name }}</option>
                  @endforeach
               </select>
            </div>
            <div>
               <select name="status" class="w-full border rounded px-2 py-1">
                  <option value="">{{ __('app.all_statuses') }}</option>
                  <option value="1" {{ $status === '1' ? 'selected' : '' }}>{{ __('app.active') }}</option>
                  <option value="0" {{ $status === '0' ? 'selected' : '' }}>{{ __('app.inactive') }}</option>
               </select>
            </div>
            <div>
               <button class="w-full px-3 py-1 bg-secondary text-white rounded">{{ __('app.filter') }}</button>
            </div>
         </div>
      </form>

      <div class="bg-white rounded shadow overflow-hidden">
         <div class="overflow-x-auto">
            <table class="min-w-full">
               <thead class="bg-gray-50">
                  <tr>
                     <th class="text-left p-3">
                        <input type="checkbox" id="selectAll" onclick="toggleSelectAll(this)">
                     </th>
                     <th class="text-left p-3">{{ __('app.code') }}</th>
                     <th class="text-left p-3">{{ __('app.name') }}</th>
                     <th class="text-left p-3">{{ __('app.warehouse') }}</th>
                     <th class="text-left p-3">{{ __('app.category') }}</th>
                     <th class="text-left p-3">{{ __('app.stock') }}</th>
                     <th class="text-left p-3">{{ __('app.min_stock') }}</th>
                     <th class="text-left p-3">{{ __('app.unit') }}</th>
                     <th class="text-left p-3">{{ __('app.status') }}</th>
                     <th class="text-left p-3">{{ __('app.action') }}</th>
                  </tr>
               </thead>
               <tbody>
                  @forelse($products as $p)
                     @php
                        $totalStock = $p->warehouses->sum('pivot.stock');
                        $warehouseNames = $p->warehouses->pluck('name')->join(', ');
                     @endphp
                     <tr class="border-t hover:bg-gray-50 {{ $totalStock < $p->min_stock ? 'bg-red-50' : '' }}">
                        <td class="p-3">
                           <input type="checkbox" class="product-checkbox" value="{{ $p->id }}">
                        </td>
                        <td class="p-3">{{ $p->code }}</td>
                        <td class="p-3">{{ $p->name }}</td>
                        <td class="p-3">
                           <span class="text-sm" title="{{ $warehouseNames }}">{{ $warehouseNames ?: '-' }}</span>
                        </td>
                        <td class="p-3">{{ $p->category?->name ?? '-' }}</td>
                        <td class="p-3 font-semibold {{ $totalStock < $p->min_stock ? 'text-red-600' : '' }}">
                           {{ $totalStock }}</td>
                        <td class="p-3">{{ $p->min_stock }}</td>
                        <td class="p-3">{{ $p->unit }}</td>
                        <td class="p-3">
                           <span
                              class="px-2 py-1 text-xs rounded {{ $p->status ? 'bg-green-100 text-green-700' : 'bg-gray-100 text-gray-600' }}">
                              {{ $p->status ? __('app.active') : __('app.inactive') }}
                           </span>
                        </td>
                        <td class="p-3 space-x-2">
                           <a href="{{ route('products.show', $p) }}" class="text-blue-600"
                              title="{{ __('app.view') }}">{{ __('app.view') }}</a>
                           <a href="{{ route('products.edit', $p) }}" class="text-blue-600"
                              title="{{ __('app.edit') }}">{{ __('app.edit') }}</a>
                           <a href="{{ route('products.label', $p) }}" class="text-purple-600"
                              title="{{ __('app.print_label') }}" target="_blank">
                              <svg class="w-4 h-4 inline-block" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                 <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M12 4v1m6 11h2m-6 0h-2v4m0-11v3m0 0h.01M12 12h4.01M16 20h4M4 12h4m12 0h.01M5 8h2a1 1 0 001-1V5a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1zm12 0h2a1 1 0 001-1V5a1 1 0 00-1-1h-2a1 1 0 00-1 1v2a1 1 0 001 1zM5 20h2a1 1 0 001-1v-2a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1z">
                                 </path>
                              </svg>
                           </a>
                           <form action="{{ route('products.destroy', $p) }}" method="POST" class="inline-block"
                              onsubmit="return confirm('{{ __('app.confirm_delete') }}')">
                              @csrf
                              @method('DELETE')
                              <button class="text-red-600" title="{{ __('app.delete') }}">{{ __('app.delete') }}</button>
                           </form>
                        </td>
                     </tr>
                  @empty
                     <tr>
                        <td colspan="10" class="p-12">
                           <div class="text-center">
                              <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" viewBox="0 0 24 24"
                                 stroke="currentColor">
                                 <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4" />
                              </svg>
                              <h3 class="mt-2 text-sm font-medium text-gray-900">{{ __('app.no_products') }}</h3>
                              <p class="mt-1 text-sm text-gray-500">
                                 {{ __('app.get_started', ['item' => __('app.product')]) }}</p>
                              <div class="mt-6">
                                 <a href="{{ route('products.create') }}"
                                    class="inline-flex items-center px-4 py-2 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-primary hover:bg-primary/90 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary">
                                    <svg class="-ml-1 mr-2 h-5 w-5" fill="none" viewBox="0 0 24 24"
                                       stroke="currentColor">
                                       <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                          d="M12 4v16m8-8H4" />
                                    </svg>
                                    {{ __('app.add_product') }}
                                 </a>
                              </div>
                           </div>
                        </td>
                     </tr>
                  @endforelse
               </tbody>
            </table>
         </div>
      </div>

      <div class="mt-4">{{ $products->links() }}</div>
   </div>

   <!-- Hidden form for printing labels -->
   <form id="printLabelsForm" action="{{ route('products.print-labels') }}" method="POST" style="display: none;">
      @csrf
      <div id="selectedProductsContainer"></div>
   </form>

   <script>
      function toggleSelectAll(checkbox) {
         const checkboxes = document.querySelectorAll('.product-checkbox');
         checkboxes.forEach(cb => cb.checked = checkbox.checked);
      }

      function printSelectedLabels() {
         const checkboxes = document.querySelectorAll('.product-checkbox:checked');

         if (checkboxes.length === 0) {
            alert('{{ __('app.select_at_least_one_product') }}');
            return;
         }

         const form = document.getElementById('printLabelsForm');
         const container = document.getElementById('selectedProductsContainer');
         container.innerHTML = '';

         checkboxes.forEach(checkbox => {
            const input = document.createElement('input');
            input.type = 'hidden';
            input.name = 'product_ids[]';
            input.value = checkbox.value;
            container.appendChild(input);
         });

         form.submit();
      }
   </script>
@endsection
