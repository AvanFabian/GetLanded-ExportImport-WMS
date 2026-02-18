@extends('layouts.app')

@section('content')
   <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
      <!-- Header -->
      <div class="flex justify-between items-center mb-6">
         <div>
            <h1 class="text-3xl font-bold text-gray-900">{{ __('Payments') }}</h1>
            <p class="mt-1 text-sm text-gray-600">{{ __('Record and manage customer payments') }}</p>
         </div>
         <div class="flex gap-3">
            <a href="{{ route('payments.aging') }}"
               class="px-4 py-2 bg-amber-600 hover:bg-amber-700 text-white font-semibold rounded-lg transition duration-150">
               📊 {{ __('AR Aging') }}
            </a>
            <a href="{{ route('payments.create') }}"
               class="px-4 py-2 bg-emerald-600 hover:bg-emerald-700 text-white font-semibold rounded-lg transition duration-150">
               + {{ __('Record Payment') }}
            </a>
         </div>
      </div>

      <!-- Filters -->
      <div class="bg-white rounded-lg shadow-md p-6 mb-6">
         <form method="GET" action="{{ route('payments.index') }}" class="space-y-4">
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
               <div>
                  <label for="search" class="block text-sm font-medium text-gray-700 mb-2">{{ __('Search') }}</label>
                  <input type="text" id="search" name="search" value="{{ request('search') }}"
                     placeholder="{{ __('Reference, customer, SO number...') }}"
                     class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-emerald-500 focus:border-transparent">
               </div>
               <div>
                  <label for="customer_id" class="block text-sm font-medium text-gray-700 mb-2">{{ __('Customer') }}</label>
                  <select id="customer_id" name="customer_id"
                     class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-emerald-500 focus:border-transparent">
                     <option value="">{{ __('All Customers') }}</option>
                     @foreach ($customers as $customer)
                        <option value="{{ $customer->id }}" {{ request('customer_id') == $customer->id ? 'selected' : '' }}>
                           {{ $customer->name }}
                        </option>
                     @endforeach
                  </select>
               </div>
               <div>
                  <label for="payment_method" class="block text-sm font-medium text-gray-700 mb-2">{{ __('Method') }}</label>
                  <select id="payment_method" name="payment_method"
                     class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-emerald-500 focus:border-transparent">
                     <option value="">{{ __('All Methods') }}</option>
                     @foreach (\App\Models\Payment::PAYMENT_METHODS as $key => $label)
                        <option value="{{ $key }}" {{ request('payment_method') === $key ? 'selected' : '' }}>{{ $label }}</option>
                     @endforeach
                  </select>
               </div>
            </div>
            <div class="flex gap-3">
               <button type="submit" class="px-6 py-2 bg-emerald-600 hover:bg-emerald-700 text-white font-semibold rounded-lg transition">
                  {{ __('Filter') }}
               </button>
               <a href="{{ route('payments.index') }}" class="px-6 py-2 bg-gray-200 hover:bg-gray-300 text-gray-700 font-semibold rounded-lg transition">
                  {{ __('Reset') }}
               </a>
            </div>
         </form>
      </div>

      <!-- Payments Table -->
      <div class="bg-white rounded-lg shadow-md overflow-hidden">
         <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
               <thead class="bg-gray-50">
                  <tr>
                     <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">{{ __('Date') }}</th>
                     <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">{{ __('Reference') }}</th>
                     <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">{{ __('Customer') }}</th>
                     <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">{{ __('Sales Order') }}</th>
                     <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">{{ __('Method') }}</th>
                     <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">{{ __('Amount') }}</th>
                     <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">{{ __('Base (IDR)') }}</th>
                     <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">{{ __('Actions') }}</th>
                  </tr>
               </thead>
               <tbody class="bg-white divide-y divide-gray-200">
                  @forelse($payments as $payment)
                     <tr class="hover:bg-gray-50">
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                           {{ $payment->payment_date->format('d M Y') }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                           <a href="{{ route('payments.show', $payment) }}" class="text-emerald-600 hover:text-emerald-900 font-medium">
                              {{ $payment->reference ?? '#' . $payment->id }}
                           </a>
                        </td>
                        <td class="px-6 py-4 text-sm text-gray-900">
                           {{ $payment->customer->name ?? '-' }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm">
                           @if ($payment->salesOrder)
                              <a href="{{ route('sales-orders.show', $payment->salesOrder) }}" class="text-blue-600 hover:text-blue-900">
                                 {{ $payment->salesOrder->so_number }}
                              </a>
                           @else
                              <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-purple-100 text-purple-800">
                                 {{ __('Deposit') }}
                              </span>
                           @endif
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">
                           {{ \App\Models\Payment::PAYMENT_METHODS[$payment->payment_method] ?? $payment->payment_method }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-right font-semibold">
                           {{ $payment->currency_code }} {{ number_format($payment->amount, 2) }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-right text-gray-700">
                           Rp {{ number_format($payment->base_currency_amount, 0, ',', '.') }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-center">
                           <a href="{{ route('payments.show', $payment) }}" class="text-blue-600 hover:text-blue-900" title="{{ __('View') }}">
                              <svg class="w-5 h-5 inline" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                 <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                 <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                              </svg>
                           </a>
                        </td>
                     </tr>
                  @empty
                     <tr>
                        <td colspan="8" class="px-6 py-8 text-center text-gray-500">
                           {{ __('No payments recorded yet.') }}
                        </td>
                     </tr>
                  @endforelse
               </tbody>
            </table>
         </div>

         @if ($payments->hasPages())
            <div class="px-6 py-4 border-t border-gray-200">
               {{ $payments->links() }}
            </div>
         @endif
      </div>
   </div>
@endsection
