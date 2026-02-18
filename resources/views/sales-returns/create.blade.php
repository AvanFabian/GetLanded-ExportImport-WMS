@extends('layouts.app')

@section('content')
   <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
      <div class="flex justify-between items-center mb-6">
         <div>
            <h1 class="text-3xl font-bold text-gray-900">{{ __('Create Sales Return') }}</h1>
            <p class="mt-1 text-sm text-gray-600">{{ __('Process a customer return and issue credit note') }}</p>
         </div>
         <a href="{{ route('sales-returns.index') }}" class="px-4 py-2 bg-gray-200 hover:bg-gray-300 text-gray-700 font-semibold rounded-lg transition">← {{ __('Back') }}</a>
      </div>

      <div class="bg-white rounded-lg shadow-md p-6">
         <form method="POST" action="{{ route('sales-returns.store') }}" class="space-y-6">
            @csrf

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
               <div>
                  <label for="sales_order_id" class="block text-sm font-medium text-gray-700 mb-2">{{ __('Sales Order') }} *</label>
                  <select id="sales_order_id" name="sales_order_id" required
                     class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-emerald-500 focus:border-transparent">
                     <option value="">{{ __('Select Sales Order') }}</option>
                     @foreach ($salesOrders as $order)
                        <option value="{{ $order->id }}" {{ old('sales_order_id') == $order->id ? 'selected' : '' }}>
                           {{ $order->so_number }} — {{ $order->customer->name ?? '' }}
                        </option>
                     @endforeach
                  </select>
                  @error('sales_order_id') <p class="text-red-500 text-sm mt-1">{{ $message }}</p> @enderror
               </div>
               <div>
                  <label for="return_date" class="block text-sm font-medium text-gray-700 mb-2">{{ __('Return Date') }} *</label>
                  <input type="date" id="return_date" name="return_date" value="{{ old('return_date', date('Y-m-d')) }}" required
                     class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-emerald-500 focus:border-transparent">
                  @error('return_date') <p class="text-red-500 text-sm mt-1">{{ $message }}</p> @enderror
               </div>
            </div>

            <div>
               <label for="reason" class="block text-sm font-medium text-gray-700 mb-2">{{ __('Return Reason') }} *</label>
               <textarea id="reason" name="reason" rows="3" required
                  class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-emerald-500 focus:border-transparent">{{ old('reason') }}</textarea>
               @error('reason') <p class="text-red-500 text-sm mt-1">{{ $message }}</p> @enderror
            </div>

            <!-- Return Items -->
            <div>
               <h3 class="text-lg font-bold text-gray-900 mb-4">{{ __('Return Items') }}</h3>
               <div id="items-container">
                  <div class="item-row grid grid-cols-12 gap-3 mb-3 items-end">
                     <div class="col-span-5">
                        <label class="block text-sm font-medium text-gray-700 mb-1">{{ __('Product') }}</label>
                        <select name="items[0][product_id]" required class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm">
                           <option value="">{{ __('Select') }}</option>
                           @foreach ($products as $product)
                              <option value="{{ $product->id }}">{{ $product->name }} ({{ $product->sku }})</option>
                           @endforeach
                        </select>
                     </div>
                     <div class="col-span-2">
                        <label class="block text-sm font-medium text-gray-700 mb-1">{{ __('Qty') }}</label>
                        <input type="number" name="items[0][quantity]" min="1" required class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm">
                     </div>
                     <div class="col-span-3">
                        <label class="block text-sm font-medium text-gray-700 mb-1">{{ __('Unit Price') }}</label>
                        <input type="number" step="0.01" name="items[0][unit_price]" min="0" required class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm">
                     </div>
                     <div class="col-span-2 flex gap-2">
                        <button type="button" onclick="addItemRow()" class="px-3 py-2 bg-emerald-600 hover:bg-emerald-700 text-white rounded-lg text-sm">+</button>
                     </div>
                  </div>
               </div>
            </div>

            <div class="flex justify-end gap-3 pt-4 border-t">
               <a href="{{ route('sales-returns.index') }}" class="px-6 py-2 bg-gray-200 hover:bg-gray-300 text-gray-700 font-semibold rounded-lg transition">{{ __('Cancel') }}</a>
               <button type="submit" class="px-6 py-2 bg-emerald-600 hover:bg-emerald-700 text-white font-semibold rounded-lg transition">{{ __('Create Return') }}</button>
            </div>
         </form>
      </div>
   </div>

   <script>
      let itemIndex = 1;
      function addItemRow() {
         const container = document.getElementById('items-container');
         const row = document.createElement('div');
         row.className = 'item-row grid grid-cols-12 gap-3 mb-3 items-end';
         row.innerHTML = `
            <div class="col-span-5">
               <select name="items[${itemIndex}][product_id]" required class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm">
                  <option value="">{{ __('Select') }}</option>
                  @foreach ($products as $product)
                     <option value="{{ $product->id }}">{{ $product->name }} ({{ $product->sku }})</option>
                  @endforeach
               </select>
            </div>
            <div class="col-span-2">
               <input type="number" name="items[${itemIndex}][quantity]" min="1" required class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm">
            </div>
            <div class="col-span-3">
               <input type="number" step="0.01" name="items[${itemIndex}][unit_price]" min="0" required class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm">
            </div>
            <div class="col-span-2">
               <button type="button" onclick="this.closest('.item-row').remove()" class="px-3 py-2 bg-red-500 hover:bg-red-600 text-white rounded-lg text-sm">×</button>
            </div>`;
         container.appendChild(row);
         itemIndex++;
      }
   </script>
@endsection
