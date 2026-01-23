@extends('layouts.app')

@section('title', __('app.create_purchase_order'))

@section('content')
   <div class="max-w-7xl mx-auto p-6">
      <div class="mb-6">
         <h2 class="text-2xl font-semibold text-gray-800">{{ __('app.create_purchase_order') }}</h2>
         <p class="text-gray-600">{{ __('app.add_new_po') }}</p>
      </div>

      <form action="{{ route('purchase-orders.store') }}" method="POST" id="poForm">
         @csrf

         <!-- PO Information Card -->
         <div class="bg-white rounded shadow mb-6">
            <div class="px-6 py-4 border-b border-gray-200">
               <h3 class="text-lg font-semibold text-gray-800">{{ __('app.po_information') }}</h3>
            </div>
            <div class="p-6">
               @if (session('error'))
                  <div class="mb-4 p-4 bg-red-100 border border-red-400 text-red-700 rounded">
                     {{ session('error') }}
                  </div>
               @endif

               <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                  <div>
                     <label class="block text-sm font-medium text-gray-700 mb-2">
                        {{ __('app.warehouse') }} <span class="text-red-600">*</span>
                     </label>
                     <select name="warehouse_id"
                        class="w-full border rounded px-3 py-2 @error('warehouse_id') border-red-500 @enderror" required>
                        <option value="">{{ __('app.choose_warehouse') }}</option>
                        @foreach ($warehouses as $warehouse)
                           <option value="{{ $warehouse->id }}"
                              {{ old('warehouse_id') == $warehouse->id ? 'selected' : '' }}>
                              {{ $warehouse->name }}
                           </option>
                        @endforeach
                     </select>
                     @error('warehouse_id')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                     @enderror
                  </div>

                  <div>
                     <label class="block text-sm font-medium text-gray-700 mb-2">
                        {{ __('app.supplier') }} <span class="text-red-600">*</span>
                     </label>
                     <select name="supplier_id"
                        class="w-full border rounded px-3 py-2 @error('supplier_id') border-red-500 @enderror" required>
                        <option value="">{{ __('app.choose_supplier') }}</option>
                        @foreach ($suppliers as $supplier)
                           <option value="{{ $supplier->id }}"
                              {{ old('supplier_id') == $supplier->id ? 'selected' : '' }}>
                              {{ $supplier->name }}
                           </option>
                        @endforeach
                     </select>
                     @error('supplier_id')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                     @enderror
                  </div>

                  <div>
                     <label class="block text-sm font-medium text-gray-700 mb-2">
                        {{ __('app.order_date') }} <span class="text-red-600">*</span>
                     </label>
                     <input type="date" name="order_date"
                        class="w-full border rounded px-3 py-2 @error('order_date') border-red-500 @enderror"
                        value="{{ old('order_date', date('Y-m-d')) }}" required>
                     @error('order_date')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                     @enderror
                  </div>

                  <div>
                     <label class="block text-sm font-medium text-gray-700 mb-2">
                        {{ __('app.expected_delivery_date') }}
                     </label>
                     <input type="date" name="expected_delivery_date"
                        class="w-full border rounded px-3 py-2 @error('expected_delivery_date') border-red-500 @enderror"
                        value="{{ old('expected_delivery_date') }}">
                     @error('expected_delivery_date')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                     @enderror
                  </div>
               </div>

               <div class="mt-4">
                  <label class="block text-sm font-medium text-gray-700 mb-2">{{ __('app.notes') }}</label>
                  <textarea name="notes" class="w-full border rounded px-3 py-2 @error('notes') border-red-500 @enderror" rows="3"
                     placeholder="{{ __('app.additional_notes') }}">{{ old('notes') }}</textarea>
                  @error('notes')
                     <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                  @enderror
               </div>
            </div>
         </div>

         <!-- Product Details Card -->
         <div class="bg-white rounded shadow mb-6">
            <div class="px-6 py-4 border-b border-gray-200 flex justify-between items-center">
               <h3 class="text-lg font-semibold text-gray-800">{{ __('app.product_details') }}</h3>
               <button type="button" class="px-4 py-2 bg-green-600 text-white rounded hover:bg-green-700"
                  id="addProductBtn">
                  <svg class="w-4 h-4 inline-block mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                     <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                  </svg>
                  {{ __('app.add_product_button') }}
               </button>
            </div>
            <div class="p-6">
               <div class="overflow-x-auto">
                  <table class="min-w-full border" id="productsTable">
                     <thead class="bg-gray-50">
                        <tr>
                           <th class="text-left p-3 font-semibold border-b">{{ __('app.product') }}</th>
                           <th class="text-left p-3 font-semibold border-b w-32">{{ __('app.quantity') }}</th>
                           <th class="text-left p-3 font-semibold border-b w-40">{{ __('app.unit_price') }}</th>
                           <th class="text-left p-3 font-semibold border-b w-40">{{ __('app.subtotal') }}</th>
                           <th class="text-left p-3 font-semibold border-b w-20">{{ __('app.actions') }}</th>
                        </tr>
                     </thead>
                     <tbody id="productRows"></tbody>
                     <tfoot class="bg-gray-50">
                        <tr>
                           <td colspan="3" class="text-right p-3 font-bold border-t">{{ __('app.total') }}:</td>
                           <td colspan="2" class="p-3 border-t">
                              <input type="text" id="totalAmount" class="w-full border rounded px-3 py-2 bg-gray-100"
                                 readonly>
                           </td>
                        </tr>
                     </tfoot>
                  </table>
               </div>
               <div class="mt-4 text-sm text-gray-600">
                  <svg class="w-4 h-4 inline-block mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                     <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                  </svg>
                  {{ __('app.add_product_note') }}
               </div>
            </div>
         </div>

         <!-- Action Buttons -->
         <div class="flex gap-3 mb-6">
            <button type="submit" class="px-6 py-2 bg-primary text-white rounded hover:bg-blue-700">
               <svg class="w-4 h-4 inline-block mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                     d="M8 7H5a2 2 0 00-2 2v9a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-3m-1 4l-3 3m0 0l-3-3m3 3V4">
                  </path>
               </svg>
               {{ __('app.save') }}
            </button>
            <a href="{{ route('purchase-orders.index') }}"
               class="px-6 py-2 bg-gray-500 text-white rounded hover:bg-gray-600">
               <svg class="w-4 h-4 inline-block mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18">
                  </path>
               </svg>
               {{ __('app.back') }}
            </a>
         </div>
      </form>
   </div>

   <!-- Product Selection Modal -->
   <div id="productModal" class="hidden fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50">
      <div class="relative top-20 mx-auto p-5 border w-11/12 md:w-3/4 lg:w-2/3 shadow-lg rounded-md bg-white">
         <div class="flex justify-between items-center pb-3 border-b">
            <h3 class="text-xl font-semibold">{{ __('app.select_product_modal') }}</h3>
            <button type="button" class="text-gray-400 hover:text-gray-600" onclick="closeProductModal()">
               <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
               </svg>
            </button>
         </div>
         <div class="mt-4">
            <div class="mb-4">
               <input type="text" class="w-full border rounded px-3 py-2" id="searchProduct"
                  placeholder="{{ __('app.search_by_code_name') }}">
            </div>
            <div class="overflow-x-auto max-h-96">
               <table class="min-w-full">
                  <thead class="bg-gray-50 sticky top-0">
                     <tr>
                        <th class="text-left p-3 font-semibold">{{ __('app.code') }}</th>
                        <th class="text-left p-3 font-semibold">{{ __('app.name') }}</th>
                        <th class="text-left p-3 font-semibold">{{ __('app.category') }}</th>
                        <th class="text-left p-3 font-semibold">{{ __('app.actions') }}</th>
                     </tr>
                  </thead>
                  <tbody id="productList"></tbody>
               </table>
            </div>
         </div>
      </div>
   </div>

@endsection

@push('scripts')
   <script>
      let productIndex = 0;
      let allProducts = [];

      // Load products on page load
      document.addEventListener('DOMContentLoaded', function() {
         loadProducts();
         calculateTotal();
      });

      // Add product button
      document.getElementById('addProductBtn').addEventListener('click', function() {
         document.getElementById('productModal').classList.remove('hidden');
      });

      // Close modal function
      function closeProductModal() {
         document.getElementById('productModal').classList.add('hidden');
      }

      // Load all products
      function loadProducts() {
         fetch('/api/products')
            .then(response => response.json())
            .then(data => {
               allProducts = data;
               displayProducts(allProducts);
            });
      }

      // Display products in modal
      function displayProducts(products) {
         let html = '';
         products.forEach(function(product) {
            html += `
            <tr class="border-t hover:bg-gray-50">
                <td class="p-3">${product.code}</td>
                <td class="p-3">${product.name}</td>
                <td class="p-3">${product.category ? product.category.name : '-'}</td>
                <td class="p-3">
                    <button type="button" class="px-3 py-1 bg-primary text-white rounded hover:bg-blue-700 select-product" 
                        data-id="${product.id}" 
                        data-name="${product.name}"
                        data-price="${product.purchase_price || 0}">
                        Pilih
                    </button>
                </td>
            </tr>
        `;
         });
         document.getElementById('productList').innerHTML = html;
      }

      // Search products
      document.getElementById('searchProduct').addEventListener('keyup', function() {
         let search = this.value.toLowerCase();
         let filtered = allProducts.filter(function(product) {
            return product.code.toLowerCase().includes(search) ||
               product.name.toLowerCase().includes(search);
         });
         displayProducts(filtered);
      });

      // Select product
      document.addEventListener('click', function(e) {
         if (e.target.classList.contains('select-product') || e.target.closest('.select-product')) {
            const btn = e.target.classList.contains('select-product') ? e.target : e.target.closest(
               '.select-product');
            const productId = btn.dataset.id;
            const productName = btn.dataset.name;
            const productPrice = btn.dataset.price;

            // Check if product already added
            const existingProducts = document.querySelectorAll('.product-row input[name*="[product_id]"]');
            let exists = false;
            existingProducts.forEach(input => {
               if (input.value == productId) {
                  exists = true;
               }
            });

            if (exists) {
               alert('{{ __('app.product_already_added') }}');
               return;
            }

            const row = `
            <tr class="product-row border-t">
                <td class="p-3">
                    <input type="hidden" name="products[${productIndex}][product_id]" value="${productId}">
                    <input type="text" class="w-full border rounded px-3 py-2 bg-gray-100" value="${productName}" readonly>
                </td>
                <td class="p-3">
                    <input type="number" name="products[${productIndex}][quantity]" class="w-full border rounded px-3 py-2 quantity" value="1" min="1" required>
                </td>
                <td class="p-3">
                    <input type="number" name="products[${productIndex}][unit_price]" class="w-full border rounded px-3 py-2 unit-price" value="${productPrice}" min="0" step="0.01" required>
                </td>
                <td class="p-3">
                    <input type="text" class="w-full border rounded px-3 py-2 bg-gray-100 subtotal" value="0" readonly>
                </td>
                <td class="p-3">
                    <button type="button" class="px-2 py-1 bg-red-500 text-white rounded hover:bg-red-600 remove-product">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                        </svg>
                    </button>
                </td>
            </tr>
        `;

            document.getElementById('productRows').insertAdjacentHTML('beforeend', row);
            productIndex++;
            calculateTotal();
            closeProductModal();
         }

         // Remove product
         if (e.target.classList.contains('remove-product') || e.target.closest('.remove-product')) {
            const btn = e.target.classList.contains('remove-product') ? e.target : e.target.closest(
               '.remove-product');
            btn.closest('tr').remove();
            calculateTotal();
         }
      });

      // Calculate subtotal when quantity or price changes
      document.addEventListener('input', function(e) {
         if (e.target.classList.contains('quantity') || e.target.classList.contains('unit-price')) {
            const row = e.target.closest('tr');
            const quantity = parseFloat(row.querySelector('.quantity').value) || 0;
            const price = parseFloat(row.querySelector('.unit-price').value) || 0;
            const subtotal = quantity * price;

            row.querySelector('.subtotal').value = formatNumber(subtotal);
            calculateTotal();
         }
      });

      // Calculate total
      function calculateTotal() {
         let total = 0;
         document.querySelectorAll('.product-row').forEach(function(row) {
            const quantity = parseFloat(row.querySelector('.quantity').value) || 0;
            const price = parseFloat(row.querySelector('.unit-price').value) || 0;
            total += quantity * price;
         });
         document.getElementById('totalAmount').value = 'Rp ' + formatNumber(total);
      }

      // Format number
      function formatNumber(number) {
         return number.toFixed(0).replace(/\B(?=(\d{3})+(?!\d))/g, ".");
      }

      // Form validation
      document.getElementById('poForm').addEventListener('submit', function(e) {
         if (document.querySelectorAll('.product-row').length === 0) {
            e.preventDefault();
            alert('{{ __('app.min_one_product') }}');
            return false;
         }
      });
   </script>
@endpush
