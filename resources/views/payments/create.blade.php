@extends('layouts.app')

@section('content')
   <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
      <div class="flex justify-between items-center mb-6">
         <div>
            <h1 class="text-3xl font-bold text-gray-900">{{ __('Record Payment') }}</h1>
            <p class="mt-1 text-sm text-gray-600">{{ __('Record a customer payment or deposit') }}</p>
         </div>
         <a href="{{ route('payments.index') }}" class="px-4 py-2 bg-gray-200 hover:bg-gray-300 text-gray-700 font-semibold rounded-lg transition">
            ← {{ __('Back') }}
         </a>
      </div>

      <div class="bg-white rounded-lg shadow-md p-6">
         <form method="POST" action="{{ route('payments.store') }}" class="space-y-6">
            @csrf

            <!-- Sales Order (optional) -->
            <div>
               <label for="sales_order_id" class="block text-sm font-medium text-gray-700 mb-2">
                  {{ __('Sales Order') }} <span class="text-gray-400">({{ __('leave empty for deposit') }})</span>
               </label>
               <select id="sales_order_id" name="sales_order_id"
                  class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-emerald-500 focus:border-transparent">
                  <option value="">-- {{ __('Unallocated Deposit') }} --</option>
                  @foreach ($unpaidOrders as $order)
                     <option value="{{ $order->id }}"
                        {{ (old('sales_order_id', $selectedOrder?->id) == $order->id) ? 'selected' : '' }}
                        data-customer="{{ $order->customer_id }}"
                        data-currency="{{ $order->currency_code }}"
                        data-outstanding="{{ $order->total - $order->amount_paid }}">
                        {{ $order->so_number }} — {{ $order->customer->name ?? '' }}
                        ({{ $order->currency_code ?? 'IDR' }} {{ number_format($order->total - $order->amount_paid, 2) }} outstanding)
                     </option>
                  @endforeach
               </select>
               @error('sales_order_id') <p class="text-red-500 text-sm mt-1">{{ $message }}</p> @enderror
            </div>

            <!-- Customer -->
            <div>
               <label for="customer_id" class="block text-sm font-medium text-gray-700 mb-2">{{ __('Customer') }}</label>
               <select id="customer_id" name="customer_id"
                  class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-emerald-500 focus:border-transparent">
                  <option value="">{{ __('Select Customer') }}</option>
                  @foreach ($customers as $customer)
                     <option value="{{ $customer->id }}" {{ old('customer_id', $selectedOrder?->customer_id) == $customer->id ? 'selected' : '' }}>
                        {{ $customer->name }}
                     </option>
                  @endforeach
               </select>
               @error('customer_id') <p class="text-red-500 text-sm mt-1">{{ $message }}</p> @enderror
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
               <!-- Payment Date -->
               <div>
                  <label for="payment_date" class="block text-sm font-medium text-gray-700 mb-2">{{ __('Payment Date') }} *</label>
                  <input type="date" id="payment_date" name="payment_date" value="{{ old('payment_date', date('Y-m-d')) }}" required
                     class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-emerald-500 focus:border-transparent">
                  @error('payment_date') <p class="text-red-500 text-sm mt-1">{{ $message }}</p> @enderror
               </div>

               <!-- Payment Method -->
               <div>
                  <label for="payment_method" class="block text-sm font-medium text-gray-700 mb-2">{{ __('Payment Method') }} *</label>
                  <select id="payment_method" name="payment_method" required
                     class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-emerald-500 focus:border-transparent">
                     @foreach (\App\Models\Payment::PAYMENT_METHODS as $key => $label)
                        <option value="{{ $key }}" {{ old('payment_method') === $key ? 'selected' : '' }}>{{ $label }}</option>
                     @endforeach
                  </select>
                  @error('payment_method') <p class="text-red-500 text-sm mt-1">{{ $message }}</p> @enderror
               </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
               <!-- Currency -->
               <div>
                  <label for="currency_code" class="block text-sm font-medium text-gray-700 mb-2">{{ __('Currency') }} *</label>
                  <select id="currency_code" name="currency_code" required
                     class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-emerald-500 focus:border-transparent">
                     @foreach ($currencies as $currency)
                        <option value="{{ $currency->code }}"
                           data-rate="{{ $currency->exchange_rate }}"
                           {{ old('currency_code', 'IDR') === $currency->code ? 'selected' : '' }}>
                           {{ $currency->code }} — {{ $currency->name }}
                        </option>
                     @endforeach
                  </select>
                  @error('currency_code') <p class="text-red-500 text-sm mt-1">{{ $message }}</p> @enderror
               </div>

               <!-- Amount -->
               <div>
                  <label for="amount" class="block text-sm font-medium text-gray-700 mb-2">{{ __('Amount') }} *</label>
                  <input type="number" step="0.01" id="amount" name="amount" value="{{ old('amount') }}" required
                     class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-emerald-500 focus:border-transparent">
                  @error('amount') <p class="text-red-500 text-sm mt-1">{{ $message }}</p> @enderror
               </div>

               <!-- Exchange Rate -->
               <div>
                  <label for="exchange_rate" class="block text-sm font-medium text-gray-700 mb-2">{{ __('Exchange Rate') }}</label>
                  <input type="number" step="0.00000001" id="exchange_rate" name="exchange_rate" value="{{ old('exchange_rate', 1) }}"
                     class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-emerald-500 focus:border-transparent">
                  @error('exchange_rate') <p class="text-red-500 text-sm mt-1">{{ $message }}</p> @enderror
               </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
               <!-- Bank Fees -->
               <div>
                  <label for="bank_fees" class="block text-sm font-medium text-gray-700 mb-2">{{ __('Bank Fees') }}</label>
                  <input type="number" step="0.01" id="bank_fees" name="bank_fees" value="{{ old('bank_fees', 0) }}"
                     class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-emerald-500 focus:border-transparent">
                  @error('bank_fees') <p class="text-red-500 text-sm mt-1">{{ $message }}</p> @enderror
               </div>

               <!-- Bank Account -->
               <div>
                  <label for="bank_account_id" class="block text-sm font-medium text-gray-700 mb-2">{{ __('Bank Account') }}</label>
                  <select id="bank_account_id" name="bank_account_id"
                     class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-emerald-500 focus:border-transparent">
                     <option value="">{{ __('Select Bank Account') }}</option>
                     @foreach ($bankAccounts as $account)
                        <option value="{{ $account->id }}" {{ old('bank_account_id') == $account->id ? 'selected' : '' }}>
                           {{ $account->bank_name }} — {{ $account->account_number }}
                        </option>
                     @endforeach
                  </select>
                  @error('bank_account_id') <p class="text-red-500 text-sm mt-1">{{ $message }}</p> @enderror
               </div>
            </div>

            <!-- Reference -->
            <div>
               <label for="reference" class="block text-sm font-medium text-gray-700 mb-2">{{ __('Reference / TT Number') }}</label>
               <input type="text" id="reference" name="reference" value="{{ old('reference') }}"
                  placeholder="{{ __('e.g. TT-2024-001, Bank Ref #...') }}"
                  class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-emerald-500 focus:border-transparent">
               @error('reference') <p class="text-red-500 text-sm mt-1">{{ $message }}</p> @enderror
            </div>

            <!-- Notes -->
            <div>
               <label for="notes" class="block text-sm font-medium text-gray-700 mb-2">{{ __('Notes') }}</label>
               <textarea id="notes" name="notes" rows="3"
                  class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-emerald-500 focus:border-transparent">{{ old('notes') }}</textarea>
               @error('notes') <p class="text-red-500 text-sm mt-1">{{ $message }}</p> @enderror
            </div>

            <!-- Submit -->
            <div class="flex justify-end gap-3 pt-4 border-t">
               <a href="{{ route('payments.index') }}" class="px-6 py-2 bg-gray-200 hover:bg-gray-300 text-gray-700 font-semibold rounded-lg transition">
                  {{ __('Cancel') }}
               </a>
               <button type="submit" class="px-6 py-2 bg-emerald-600 hover:bg-emerald-700 text-white font-semibold rounded-lg transition">
                  {{ __('Record Payment') }}
               </button>
            </div>
         </form>
      </div>
   </div>

   <script>
      // Auto-fill customer when SO is selected
      document.getElementById('sales_order_id').addEventListener('change', function() {
         const selected = this.options[this.selectedIndex];
         if (selected.value) {
            document.getElementById('customer_id').value = selected.dataset.customer || '';
         }
      });

      // Auto-fill exchange rate when currency changes
      document.getElementById('currency_code').addEventListener('change', function() {
         const selected = this.options[this.selectedIndex];
         document.getElementById('exchange_rate').value = selected.dataset.rate || 1;
      });
   </script>
@endsection
