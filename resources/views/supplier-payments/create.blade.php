@extends('layouts.app')

@section('content')
   <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
      <div class="flex justify-between items-center mb-6">
         <div>
            <h1 class="text-3xl font-bold text-gray-900">{{ __('Record Supplier Payment') }}</h1>
            <p class="mt-1 text-sm text-gray-600">{{ __('Track payment obligations to suppliers') }}</p>
         </div>
         <a href="{{ route('supplier-payments.index') }}" class="px-4 py-2 bg-gray-200 hover:bg-gray-300 text-gray-700 font-semibold rounded-lg transition">← {{ __('Back') }}</a>
      </div>

      <div class="bg-white rounded-lg shadow-md p-6">
         <form method="POST" action="{{ route('supplier-payments.store') }}" class="space-y-6">
            @csrf
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
               <div>
                  <label for="supplier_id" class="block text-sm font-medium text-gray-700 mb-2">{{ __('Supplier') }} *</label>
                  <select id="supplier_id" name="supplier_id" required class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-emerald-500 focus:border-transparent">
                     <option value="">{{ __('Select Supplier') }}</option>
                     @foreach ($suppliers as $supplier)
                        <option value="{{ $supplier->id }}" {{ old('supplier_id') == $supplier->id ? 'selected' : '' }}>{{ $supplier->name }}</option>
                     @endforeach
                  </select>
                  @error('supplier_id') <p class="text-red-500 text-sm mt-1">{{ $message }}</p> @enderror
               </div>
               <div>
                  <label for="stock_in_id" class="block text-sm font-medium text-gray-700 mb-2">{{ __('Stock In Record') }} *</label>
                  <select id="stock_in_id" name="stock_in_id" required class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-emerald-500 focus:border-transparent">
                     <option value="">{{ __('Select') }}</option>
                     @foreach ($stockIns as $si)
                        <option value="{{ $si->id }}" {{ old('stock_in_id') == $si->id ? 'selected' : '' }}>
                           {{ $si->reference ?? '#' . $si->id }} — {{ $si->supplier->name ?? '' }} ({{ $si->created_at->format('d M Y') }})
                        </option>
                     @endforeach
                  </select>
                  @error('stock_in_id') <p class="text-red-500 text-sm mt-1">{{ $message }}</p> @enderror
               </div>
            </div>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
               <div>
                  <label for="amount_owed" class="block text-sm font-medium text-gray-700 mb-2">{{ __('Amount Owed') }} *</label>
                  <input type="number" step="0.01" id="amount_owed" name="amount_owed" value="{{ old('amount_owed') }}" required
                     class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-emerald-500 focus:border-transparent">
                  @error('amount_owed') <p class="text-red-500 text-sm mt-1">{{ $message }}</p> @enderror
               </div>
               <div>
                  <label for="amount_paid" class="block text-sm font-medium text-gray-700 mb-2">{{ __('Amount Paid') }}</label>
                  <input type="number" step="0.01" id="amount_paid" name="amount_paid" value="{{ old('amount_paid', 0) }}"
                     class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-emerald-500 focus:border-transparent">
                  @error('amount_paid') <p class="text-red-500 text-sm mt-1">{{ $message }}</p> @enderror
               </div>
               <div>
                  <label for="due_date" class="block text-sm font-medium text-gray-700 mb-2">{{ __('Due Date') }}</label>
                  <input type="date" id="due_date" name="due_date" value="{{ old('due_date') }}"
                     class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-emerald-500 focus:border-transparent">
                  @error('due_date') <p class="text-red-500 text-sm mt-1">{{ $message }}</p> @enderror
               </div>
            </div>

            <h3 class="text-lg font-bold text-gray-900 border-b pb-2">{{ __('Payment Method') }}</h3>
            <div x-data="{ method: '{{ old('payment_method', 'bank_transfer') }}' }" class="space-y-4">
               <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                  <div>
                     <label for="payment_method" class="block text-sm font-medium text-gray-700 mb-2">{{ __('Method') }} *</label>
                     <select id="payment_method" name="payment_method" x-model="method" required class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-emerald-500">
                        @foreach ($paymentMethods as $key => $label)
                           <option value="{{ $key }}" {{ old('payment_method') === $key ? 'selected' : '' }}>{{ $label }}</option>
                        @endforeach
                     </select>
                  </div>
                  <div>
                     <label for="currency_code" class="block text-sm font-medium text-gray-700 mb-2">{{ __('Currency') }}</label>
                     <input type="text" id="currency_code" name="currency_code" value="{{ old('currency_code', 'USD') }}" maxlength="3"
                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-emerald-500 uppercase">
                  </div>
                  <div>
                     <label for="bank_reference" class="block text-sm font-medium text-gray-700 mb-2">{{ __('Bank Reference') }}</label>
                     <input type="text" id="bank_reference" name="bank_reference" value="{{ old('bank_reference') }}"
                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-emerald-500" placeholder="TT ref / Transfer #">
                  </div>
               </div>

               <!-- L/C Fields (shown only when payment method is letter_of_credit) -->
               <div x-show="method === 'letter_of_credit'" x-transition class="grid grid-cols-1 md:grid-cols-3 gap-6 bg-blue-50 p-4 rounded-lg">
                  <div>
                     <label for="lc_number" class="block text-sm font-medium text-gray-700 mb-2">{{ __('L/C Number') }} *</label>
                     <input type="text" id="lc_number" name="lc_number" value="{{ old('lc_number') }}"
                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500" placeholder="LC-2024-001">
                  </div>
                  <div>
                     <label for="lc_expiry_date" class="block text-sm font-medium text-gray-700 mb-2">{{ __('L/C Expiry Date') }}</label>
                     <input type="date" id="lc_expiry_date" name="lc_expiry_date" value="{{ old('lc_expiry_date') }}"
                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
                  </div>
                  <div>
                     <label for="lc_issuing_bank" class="block text-sm font-medium text-gray-700 mb-2">{{ __('Issuing Bank') }}</label>
                     <input type="text" id="lc_issuing_bank" name="lc_issuing_bank" value="{{ old('lc_issuing_bank') }}"
                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500" placeholder="Bank Mandiri">
                  </div>
               </div>

               <div>
                  <label for="payment_notes" class="block text-sm font-medium text-gray-700 mb-2">{{ __('Notes') }}</label>
                  <textarea id="payment_notes" name="payment_notes" rows="2" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-emerald-500">{{ old('payment_notes') }}</textarea>
               </div>
            </div>
            <div class="flex justify-end gap-3 pt-4 border-t">
               <a href="{{ route('supplier-payments.index') }}" class="px-6 py-2 bg-gray-200 hover:bg-gray-300 text-gray-700 font-semibold rounded-lg transition">{{ __('Cancel') }}</a>
               <button type="submit" class="px-6 py-2 bg-emerald-600 hover:bg-emerald-700 text-white font-semibold rounded-lg transition">{{ __('Record Payment') }}</button>
            </div>
         </form>
      </div>
   </div>
@endsection
