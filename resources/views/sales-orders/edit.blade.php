@extends('layouts.app')

@section('content')
   <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
      <!-- Header -->
      <div class="mb-6">
         <h1 class="text-3xl font-bold text-gray-900">{{ __('app.edit_sales_order') }}</h1>
         <p class="mt-1 text-sm text-gray-600">{{ __('app.update_sales_order') }} - {{ $salesOrder->so_number }}</p>
      </div>

      <!-- Error Messages -->
      @if ($errors->any())
         <div class="mb-6 bg-red-50 border border-red-200 text-red-800 px-4 py-3 rounded-lg">
            <div class="font-semibold mb-2">{{ __('app.errors_found') }}:</div>
            <ul class="list-disc list-inside text-sm">
               @foreach ($errors->all() as $error)
                  <li>{{ $error }}</li>
               @endforeach
            </ul>
         </div>
      @endif

      <!-- Form -->
      <form action="{{ route('sales-orders.update', $salesOrder) }}" method="POST" id="salesOrderForm" class="space-y-6">
         @csrf
         @method('PUT')

         <!-- Order Information -->
         <div class="bg-white rounded-lg shadow-md p-6">
            <h2 class="text-lg font-semibold text-gray-900 mb-4">{{ __('app.order_information') }}</h2>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
               <div>
                  <label for="customer_id" class="block text-sm font-medium text-gray-700 mb-2">
                     {{ __('app.customer') }} <span class="text-red-500">*</span>
                  </label>
                  <select id="customer_id" name="customer_id" required
                     class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                     <option value="">{{ __('app.select_customer') }}</option>
                     @foreach ($customers as $customer)
                        <option value="{{ $customer->id }}"
                           {{ old('customer_id', $salesOrder->customer_id) == $customer->id ? 'selected' : '' }}>
                           {{ $customer->name }}
                        </option>
                     @endforeach
                  </select>
               </div>

               <div>
                  <label for="warehouse_id" class="block text-sm font-medium text-gray-700 mb-2">
                     {{ __('app.warehouse') }} <span class="text-red-500">*</span>
                  </label>
                  <select id="warehouse_id" name="warehouse_id" required
                     class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                     <option value="">{{ __('app.select_warehouse') }}</option>
                     @foreach ($warehouses as $warehouse)
                        <option value="{{ $warehouse->id }}"
                           {{ old('warehouse_id', $salesOrder->warehouse_id) == $warehouse->id ? 'selected' : '' }}>
                           {{ $warehouse->name }}
                        </option>
                     @endforeach
                  </select>
               </div>

               <div>
                  <label for="order_date" class="block text-sm font-medium text-gray-700 mb-2">
                     {{ __('app.order_date') }} <span class="text-red-500">*</span>
                  </label>
                  <input type="date" id="order_date" name="order_date"
                     value="{{ old('order_date', $salesOrder->order_date->format('Y-m-d')) }}" required
                     class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
               </div>

               <div>
                  <label for="delivery_date" class="block text-sm font-medium text-gray-700 mb-2">
                     {{ __('app.delivery_date') }}
                  </label>
                  <input type="date" id="delivery_date" name="delivery_date"
                     value="{{ old('delivery_date', $salesOrder->delivery_date?->format('Y-m-d')) }}"
                     class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
               </div>

               <div>
                  <label for="document_language" class="block text-sm font-medium text-gray-700 mb-2">
                     {{ __('app.document_language') }}
                  </label>
                  <select id="document_language" name="document_language"
                     class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                     <option value="id" {{ old('document_language', $salesOrder->document_language) == 'id' ? 'selected' : '' }}>Indonesia</option>
                     <option value="en" {{ old('document_language', $salesOrder->document_language) == 'en' ? 'selected' : '' }}>English</option>
                  </select>
               </div>
            </div>

            <div class="mt-6">
               <label for="notes" class="block text-sm font-medium text-gray-700 mb-2">
                  {{ __('app.notes') }}
               </label>
               <textarea id="notes" name="notes" rows="3"
                  class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                  placeholder="{{ __('app.order_notes') }}">{{ old('notes', $salesOrder->notes) }}</textarea>
            </div>
         </div>

         <!-- Products -->
         <div class="bg-white rounded-lg shadow-md p-6">
            <div class="flex justify-between items-center mb-4">
               <h2 class="text-lg font-semibold text-gray-900">{{ __('app.products') }}</h2>
               <button type="button" onclick="addItem()"
                  class="bg-blue-600 hover:bg-blue-700 text-white text-sm font-semibold px-4 py-2 rounded-lg transition duration-150">
                  + {{ __('app.add_product') }}
               </button>
            </div>

            <div class="overflow-x-auto">
               <table class="min-w-full divide-y divide-gray-200">
                  <thead class="bg-gray-50">
                     <tr>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">{{ __('app.product') }}</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">{{ __('app.qty') }}</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">{{ __('app.unit_price') }}</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">{{ __('app.subtotal') }}</th>
                        <th class="px-4 py-3 text-center text-xs font-medium text-gray-500 uppercase">{{ __('app.actions') }}</th>
                     </tr>
                  </thead>
                  <tbody id="itemsTable" class="bg-white divide-y divide-gray-200">
                     <!-- Items will be added here dynamically -->
                  </tbody>
               </table>
            </div>
         </div>

         <!-- Totals -->
         <div class="bg-white rounded-lg shadow-md p-6">
            <div class="max-w-md ml-auto space-y-3">
               <div class="flex justify-between text-sm">
                  <span class="text-gray-600">{{ __('app.subtotal') }}:</span>
                  <span class="font-semibold text-gray-900" id="displaySubtotal">Rp 0</span>
               </div>

               <div class="flex justify-between text-sm items-center">
                  <label for="discount" class="text-gray-600">{{ __('app.discount') }}:</label>
                  <div class="flex items-center gap-2">
                     <span class="text-gray-500">Rp</span>
                     <input type="number" id="discount" name="discount"
                        value="{{ old('discount', $salesOrder->discount) }}" min="0" step="1"
                        class="w-32 px-3 py-1 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent text-right"
                        onchange="calculateTotals()">
                  </div>
               </div>

               <div class="flex justify-between text-sm">
                  <span class="text-gray-600">{{ __('app.after_discount') }}:</span>
                  <span class="font-semibold text-gray-900" id="displayAfterDiscount">Rp 0</span>
               </div>

               <div class="flex justify-between text-sm">
                  <span class="text-gray-600">{{ __('app.tax_vat') }} 11%:</span>
                  <span class="font-semibold text-gray-900" id="displayTax">Rp 0</span>
               </div>

               <div class="flex justify-between text-lg font-bold border-t pt-3">
                  <span class="text-gray-900">{{ __('app.total') }}:</span>
                  <span class="text-blue-600" id="displayTotal">Rp 0</span>
               </div>
            </div>
         </div>

         <!-- Action Buttons -->
         <div class="flex justify-end gap-3">
            <a href="{{ route('sales-orders.show', $salesOrder) }}"
               class="px-6 py-2 bg-gray-200 hover:bg-gray-300 text-gray-700 font-semibold rounded-lg transition duration-150">
               {{ __('app.cancel') }}
            </a>
            <button type="submit"
               class="px-6 py-2 bg-blue-600 hover:bg-blue-700 text-white font-semibold rounded-lg transition duration-150">
               {{ __('app.update') }}
            </button>
         </div>
      </form>
   </div>

   <script>
      const products = @json($products);
      const existingItems = @json($salesOrder->items);
      let itemIndex = 0;

      function addItem(productId = '', quantity = 1, unitPrice = 0) {
         const tbody = document.getElementById('itemsTable');
         const row = document.createElement('tr');
         row.id = `item-${itemIndex}`;

         row.innerHTML = `
        <td class="px-4 py-3">
            <select name="items[${itemIndex}][product_id]" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent" required onchange="updatePrice(${itemIndex})">
                <option value="">{{ __('app.select_product') }}</option>
                ${products.map(p => `<option value="${p.id}" data-price="${p.selling_price || 0}" ${p.id == productId ? 'selected' : ''}>${p.name} - ${p.category?.name || ''}</option>`).join('')}
            </select>
        </td>
        <td class="px-4 py-3">
            <input type="number" name="items[${itemIndex}][quantity]" min="1" value="${quantity}" required
                class="w-24 px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent text-right"
                oninput="calculateItemSubtotal(${itemIndex})">
        </td>
        <td class="px-4 py-3">
            <input type="number" name="items[${itemIndex}][unit_price]" min="0" step="0.01" value="${unitPrice}" required
                class="w-32 px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent text-right"
                oninput="calculateItemSubtotal(${itemIndex})">
        </td>
        <td class="px-4 py-3">
            <span class="text-sm font-semibold text-gray-900" id="subtotal-${itemIndex}">Rp 0</span>
        </td>
        <td class="px-4 py-3 text-center">
            <button type="button" onclick="removeItem(${itemIndex})" class="text-red-600 hover:text-red-900">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                </svg>
            </button>
        </td>
    `;

         tbody.appendChild(row);
         calculateItemSubtotal(itemIndex);
         itemIndex++;
      }

      function removeItem(index) {
         const row = document.getElementById(`item-${index}`);
         if (row) {
            row.remove();
            calculateTotals();
         }
      }

      function updatePrice(index) {
         const select = document.querySelector(`select[name="items[${index}][product_id]"]`);
         const priceInput = document.querySelector(`input[name="items[${index}][unit_price]"]`);

         if (select && priceInput) {
            const selectedOption = select.options[select.selectedIndex];
            const price = selectedOption.getAttribute('data-price') || 0;
            priceInput.value = price;
            calculateItemSubtotal(index);
         }
      }

      function calculateItemSubtotal(index) {
         const qtyInput = document.querySelector(`input[name="items[${index}][quantity]"]`);
         const priceInput = document.querySelector(`input[name="items[${index}][unit_price]"]`);
         const subtotalSpan = document.getElementById(`subtotal-${index}`);

         if (qtyInput && priceInput && subtotalSpan) {
            const qty = parseFloat(qtyInput.value) || 0;
            const price = parseFloat(priceInput.value) || 0;
            const subtotal = qty * price;

            subtotalSpan.textContent = formatCurrency(subtotal);
            calculateTotals();
         }
      }

      function calculateTotals() {
         let subtotal = 0;

         const rows = document.querySelectorAll('#itemsTable tr');
         rows.forEach((row, idx) => {
            const qtyInput = row.querySelector('input[name*="[quantity]"]');
            const priceInput = row.querySelector('input[name*="[unit_price]"]');

            if (qtyInput && priceInput) {
               const qty = parseFloat(qtyInput.value) || 0;
               const price = parseFloat(priceInput.value) || 0;
               subtotal += qty * price;
            }
         });

         const discount = parseFloat(document.getElementById('discount').value) || 0;
         const afterDiscount = subtotal - discount;
         const tax = afterDiscount * 0.11;
         const total = afterDiscount + tax;

         document.getElementById('displaySubtotal').textContent = formatCurrency(subtotal);
         document.getElementById('displayAfterDiscount').textContent = formatCurrency(afterDiscount);
         document.getElementById('displayTax').textContent = formatCurrency(tax);
         document.getElementById('displayTotal').textContent = formatCurrency(total);
      }

      function formatCurrency(amount) {
         return 'Rp ' + new Intl.NumberFormat('id-ID').format(Math.round(amount));
      }

      // Load existing items on page load
      document.addEventListener('DOMContentLoaded', function() {
         existingItems.forEach(item => {
            addItem(item.product_id, item.quantity, item.unit_price);
         });

         // If no items, add one empty row
         if (existingItems.length === 0) {
            addItem();
         }
      });
   </script>
@endsection
