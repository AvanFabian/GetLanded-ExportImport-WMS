@extends('layouts.app')

@section('title', __('app.create_stock_out'))

@section('content')
   <div class="max-w-6xl mx-auto">
      <h2 class="text-xl font-semibold mb-4">{{ __('app.create_stock_out_transaction') }}</h2>

      @if ($errors->any())
         <div class="mb-4 p-3 bg-red-50 border border-red-200 text-red-700 rounded">
            <ul class="list-disc list-inside">
               @foreach ($errors->all() as $error)
                  <li>{{ $error }}</li>
               @endforeach
            </ul>
         </div>
      @endif

      <form method="POST" action="{{ route('stock-outs.store') }}" id="stockOutForm" class="bg-white p-4 rounded shadow">
         @csrf

         <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
            <div>
               <label class="block text-sm mb-1">{{ __('app.transaction_code') }}</label>
               <input type="text" value="{{ $transactionCode }}" class="w-full border rounded px-2 py-1 bg-gray-100"
                  readonly />
            </div>
            <div>
               <label class="block text-sm mb-1">{{ __('app.date') }} <span class="text-red-500">*</span></label>
               <input type="date" name="date" value="{{ old('date', date('Y-m-d')) }}"
                  class="w-full border rounded px-2 py-1" required />
            </div>
            <div>
               <label class="block text-sm mb-1">{{ __('app.warehouse') }} <span class="text-red-500">*</span></label>
               <select name="warehouse_id" id="warehouseSelect" class="w-full border rounded px-2 py-1"
                  onchange="loadWarehouseProducts()" required>
                  <option value="">{{ __('app.choose_warehouse_first') }}</option>
                  @foreach ($warehouses as $warehouse)
                     <option value="{{ $warehouse->id }}" {{ old('warehouse_id') == $warehouse->id ? 'selected' : '' }}>
                        {{ $warehouse->name }}</option>
                  @endforeach
               </select>
            </div>
            <div>
               <label class="block text-sm mb-1">{{ __('app.customer_destination') }}</label>
               <input type="text" name="customer" value="{{ old('customer') }}" class="w-full border rounded px-2 py-1"
                  placeholder="{{ __('app.optional') }}" />
            </div>
         </div>

         <div class="mb-4">
            <label class="block text-sm mb-1">{{ __('app.notes') }}</label>
            <textarea name="notes" class="w-full border rounded px-2 py-1" rows="2">{{ old('notes') }}</textarea>
         </div>

         <div class="border-t pt-4">
            <div class="flex items-center justify-between mb-3">
               <h3 class="font-semibold">{{ __('app.product_items') }} <span class="text-red-500">*</span></h3>
               <button type="button" onclick="addProductRow()" class="px-3 py-1 bg-success text-white rounded text-sm">+
                  {{ __('app.add_item') }}</button>
            </div>

            <div class="overflow-x-auto">
               <table class="min-w-full" id="productTable">
                  <thead class="bg-gray-50">
                     <tr>
                        <th class="text-left p-3">{{ __('app.product') }}</th>
                        <th class="text-right p-2 w-24">{{ __('app.available_stock') }}</th>
                        <th class="text-right p-2 w-24">{{ __('app.qty') }}</th>
                        <th class="text-right p-2 w-32">{{ __('app.selling_price') }}</th>
                        <th class="text-left p-3">{{ __('app.subtotal') }}</th>
                        <th class="w-16 p-2"></th>
                     </tr>
                  </thead>
                  <tbody id="productRows">
                     <!-- Rows will be added dynamically -->
                  </tbody>
                  <tfoot>
                     <tr class="border-t-2">
                        <td colspan="4" class="text-right p-2 font-semibold">{{ __('app.grand_total') }}:</td>
                        <td class="text-right p-2 font-bold" id="grandTotal">Rp 0</td>
                        <td></td>
                     </tr>
                  </tfoot>
               </table>
            </div>
         </div>

         <div class="flex gap-2 mt-6">
            <button type="submit"
               class="px-4 py-2 bg-danger text-white rounded">{{ __('app.save_transaction') }}</button>
            <a href="{{ route('stock-outs.index') }}" class="px-4 py-2 border rounded">{{ __('app.cancel') }}</a>
         </div>
      </form>
   </div>

   <script>
      let products = [];
      let rowIndex = 0;

      function loadWarehouseProducts() {
         const warehouseId = document.getElementById('warehouseSelect').value;
         if (!warehouseId) {
            products = [];
            document.getElementById('productRows').innerHTML = '';
            return;
         }

         fetch(`/warehouses/${warehouseId}/products`)
            .then(response => response.json())
            .then(data => {
               products = data;
               // Clear existing rows
               document.getElementById('productRows').innerHTML = '';
               rowIndex = 0;
               // Add first row
               addProductRow();
            })
            .catch(error => {
               console.error('Error loading products:', error);
               alert('Failed to load products for selected warehouse');
            });
      }

      function addProductRow() {
         const tbody = document.getElementById('productRows');
         const row = document.createElement('tr');
         row.className = 'border-t';
         row.innerHTML = `
        <td class="p-2">
          <select name="products[${rowIndex}][product_id]" class="w-full border rounded px-2 py-1" onchange="updateProductInfo(this, ${rowIndex})" required>
            <option value="">{{ __('app.select_product') }}</option>
            ${products.map(p => `<option value="${p.id}" data-stock="${p.stock}" data-unit="${p.unit}" data-price="${p.selling_price}">${p.code} - ${p.name}</option>`).join('')}
          </select>
        </td>
        <td class="p-2 text-right text-sm" id="available_${rowIndex}">-</td>
        <td class="p-2">
          <input type="number" name="products[${rowIndex}][quantity]" class="w-full border rounded px-2 py-1 text-right" min="1" value="1" max="0" id="qty_${rowIndex}" onchange="calculateRow(${rowIndex})" required />
        </td>
        <td class="p-2">
          <input type="number" step="0.01" name="products[${rowIndex}][selling_price]" class="w-full border rounded px-2 py-1 text-right" min="0" value="0" id="price_${rowIndex}" onchange="calculateRow(${rowIndex})" required />
        </td>
        <td class="p-2 text-right font-semibold" id="subtotal_${rowIndex}">Rp 0</td>
        <td class="p-2 text-center">
          <button type="button" onclick="removeRow(this)" class="text-red-600">×</button>
        </td>
      `;
         tbody.appendChild(row);
         rowIndex++;
      }

      function updateProductInfo(select, index) {
         const option = select.options[select.selectedIndex];
         const stock = option.getAttribute('data-stock') || 0;
         const unit = option.getAttribute('data-unit') || '';
         const price = option.getAttribute('data-price') || 0;

         document.getElementById(`available_${index}`).textContent = stock + ' ' + unit;
         document.getElementById(`qty_${index}`).max = stock;
         document.getElementById(`price_${index}`).value = price;
         calculateRow(index);
      }

      function calculateRow(index) {
         const qtyInput = document.getElementById(`qty_${index}`);
         const qty = parseFloat(qtyInput.value) || 0;
         const max = parseFloat(qtyInput.max) || 0;

         if (qty > max) {
            alert(`{{ __('app.insufficient_stock') }} (${max})`);
            qtyInput.value = max;
         }

         const price = parseFloat(document.getElementById(`price_${index}`).value) || 0;
         const subtotal = qty * price;
         document.getElementById(`subtotal_${index}`).textContent = 'Rp ' + subtotal.toLocaleString('id-ID');
         calculateGrandTotal();
      }

      function calculateGrandTotal() {
         let total = 0;
         document.querySelectorAll('[id^="subtotal_"]').forEach(el => {
            const value = parseFloat(el.textContent.replace(/[^0-9.-]+/g, '')) || 0;
            total += value;
         });
         document.getElementById('grandTotal').textContent = 'Rp ' + total.toLocaleString('id-ID');
      }

      function removeRow(btn) {
         btn.closest('tr').remove();
         calculateGrandTotal();
      }

      // No initial row until warehouse is selected
      document.addEventListener('DOMContentLoaded', () => {
         const warehouseSelect = document.getElementById('warehouseSelect');
         if (warehouseSelect.value) {
            loadWarehouseProducts();
         }
      });
   </script>
@endsection
