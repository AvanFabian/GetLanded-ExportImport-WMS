@extends('layouts.app')

@section('title', __('app.create_transfer'))

@section('content')
   <div class="max-w-6xl mx-auto">
      <h2 class="text-xl font-semibold mb-4">{{ __('app.create_transfer') }}</h2>

      @if ($errors->any())
         <div class="mb-4 p-3 bg-red-50 border border-red-200 text-red-700 rounded">
            <ul class="list-disc list-inside">
               @foreach ($errors->all() as $error)
                  <li>{{ $error }}</li>
               @endforeach
            </ul>
         </div>
      @endif

      <form method="POST" action="{{ route('transfers.store') }}" id="transferForm" class="bg-white p-4 rounded shadow">
         @csrf

         <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-4">
            <div>
               <label class="block text-sm mb-1">{{ __('app.from_warehouse') }} <span class="text-red-500">*</span></label>
               <select name="from_warehouse_id" id="fromWarehouse" class="w-full border rounded px-2 py-1" required
                  onchange="filterProducts()">
                  <option value="">{{ __('app.select_source_warehouse') }}</option>
                  @foreach ($warehouses as $wh)
                     <option value="{{ $wh->id }}" {{ old('from_warehouse_id') == $wh->id ? 'selected' : '' }}>
                        {{ $wh->name }} ({{ $wh->code }})
                     </option>
                  @endforeach
               </select>
            </div>
            <div>
               <label class="block text-sm mb-1">{{ __('app.to_warehouse') }} <span class="text-red-500">*</span></label>
               <select name="to_warehouse_id" id="toWarehouse" class="w-full border rounded px-2 py-1" required>
                  <option value="">{{ __('app.select_destination_warehouse') }}</option>
                  @foreach ($warehouses as $wh)
                     <option value="{{ $wh->id }}" {{ old('to_warehouse_id') == $wh->id ? 'selected' : '' }}>
                        {{ $wh->name }} ({{ $wh->code }})
                     </option>
                  @endforeach
               </select>
            </div>
            <div>
               <label class="block text-sm mb-1">{{ __('app.date') }} <span class="text-red-500">*</span></label>
               <input type="date" name="transfer_date" value="{{ old('transfer_date', date('Y-m-d')) }}"
                  class="w-full border rounded px-2 py-1" required />
            </div>
         </div>

         <div class="mb-4">
            <label class="block text-sm mb-1">{{ __('app.notes') }}</label>
            <textarea name="notes" class="w-full border rounded px-2 py-1" rows="2">{{ old('notes') }}</textarea>
         </div>

         <div class="border-t pt-4">
            <div class="flex items-center justify-between mb-3">
               <h3 class="font-semibold">{{ __('app.transfer_items') }} <span class="text-red-500">*</span></h3>
               <button type="button" onclick="addItemRow()" class="px-3 py-1 bg-success text-white rounded text-sm">+ {{ __('app.add_item') }}</button>
            </div>

            <div class="overflow-x-auto">
               <table class="min-w-full" id="itemTable">
                  <thead class="bg-gray-50">
                     <tr>
                        <th class="text-left p-3">{{ __('app.product') }}</th>
                        <th class="text-left p-2 w-24">{{ __('app.available_stock') }}</th>
                        <th class="text-right p-2 w-24">{{ __('app.qty') }}</th>
                        <th class="text-left p-2">{{ __('app.notes') }}</th>
                        <th class="w-16 p-2"></th>
                     </tr>
                  </thead>
                  <tbody id="itemRows">
                     <!-- Rows will be added dynamically -->
                  </tbody>
               </table>
            </div>
         </div>

         <div class="flex gap-2 mt-6">
            <button type="submit" class="px-4 py-2 bg-primary text-white rounded">{{ __('app.create_transfer') }}</button>
            <a href="{{ route('transfers.index') }}" class="px-4 py-2 border rounded">{{ __('app.cancel') }}</a>
         </div>
      </form>
   </div>

   <script>
      const products = @json($products);
      let rowIndex = 0;
      let availableProducts = [];

      function filterProducts() {
         const fromWarehouseId = document.getElementById('fromWarehouse').value;

         if (fromWarehouseId) {
            availableProducts = products.filter(p => p.warehouse_id == fromWarehouseId && p.stock > 0);
         } else {
            availableProducts = [];
         }

         // Clear existing rows when warehouse changes
         document.getElementById('itemRows').innerHTML = '';
         rowIndex = 0;
      }

      function addItemRow() {
         const fromWarehouseId = document.getElementById('fromWarehouse').value;

         if (!fromWarehouseId) {
            alert('{{ __('app.select_source_warehouse') }}'); // Using simplified message
            return;
         }

         if (availableProducts.length === 0) {
            alert('{{ __('app.no_products_available') }}');
            return;
         }

         const tbody = document.getElementById('itemRows');
         const row = document.createElement('tr');
         row.className = 'border-t';
         row.id = `row-${rowIndex}`;

         row.innerHTML = `
            <td class="p-2">
               <select name="items[${rowIndex}][product_id]" class="w-full border rounded px-2 py-1" onchange="updateStock(this, ${rowIndex})" required>
                  <option value="">{{ __('app.select_product') }}</option>
                  ${availableProducts.map(p => `<option value="${p.id}" data-stock="${p.stock}">${p.code} - ${p.name}</option>`).join('')}
               </select>
            </td>
            <td class="p-2">
               <span id="stock-${rowIndex}" class="font-medium">-</span>
            </td>
            <td class="p-2">
               <input type="number" name="items[${rowIndex}][quantity]" min="1" class="w-full border rounded px-2 py-1 text-right" 
                  id="qty-${rowIndex}" required onchange="validateQuantity(${rowIndex})" />
            </td>
            <td class="p-2">
               <input type="text" name="items[${rowIndex}][notes]" class="w-full border rounded px-2 py-1" />
            </td>
            <td class="p-2 text-center">
               <button type="button" onclick="removeRow(${rowIndex})" class="text-red-600 hover:text-red-800">
                  <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                     <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                  </svg>
               </button>
            </td>
         `;

         tbody.appendChild(row);
         rowIndex++;
      }

      function updateStock(select, index) {
         const selectedOption = select.options[select.selectedIndex];
         const stock = selectedOption.getAttribute('data-stock') || 0;
         document.getElementById(`stock-${index}`).textContent = stock;
         document.getElementById(`qty-${index}`).max = stock;
      }

      function validateQuantity(index) {
         const qtyInput = document.getElementById(`qty-${index}`);
         const maxStock = parseInt(qtyInput.max) || 0;
         const qty = parseInt(qtyInput.value) || 0;

         if (qty > maxStock) {
            alert(`{{ __('app.qty_exceeds_stock') }} (${maxStock})`);
            qtyInput.value = maxStock;
         }
      }

      function removeRow(index) {
         const row = document.getElementById(`row-${index}`);
         if (row) {
            row.remove();
         }
      }

      // Form validation
      document.getElementById('transferForm').addEventListener('submit', function(e) {
         const fromWarehouse = document.getElementById('fromWarehouse').value;
         const toWarehouse = document.getElementById('toWarehouse').value;
         const rows = document.getElementById('itemRows').children.length;

         if (fromWarehouse === toWarehouse) {
            e.preventDefault();
            alert('{{ __('app.source_destination_same') }}');
            return false;
         }

         if (rows === 0) {
            e.preventDefault();
            alert('{{ __('app.add_at_least_one_item') }}');
            return false;
         }
      });

      // Initialize on page load
      document.addEventListener('DOMContentLoaded', function() {
         filterProducts();
      });
   </script>
@endsection
