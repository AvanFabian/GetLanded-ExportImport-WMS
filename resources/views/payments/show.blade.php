@extends('layouts.app')

@section('content')
   <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
      <div class="flex justify-between items-center mb-6">
         <div>
            <h1 class="text-3xl font-bold text-gray-900">{{ __('Payment Detail') }}</h1>
            <p class="mt-1 text-sm text-gray-600">{{ $payment->reference ?? '#' . $payment->id }}</p>
         </div>
         <a href="{{ route('payments.index') }}" class="px-4 py-2 bg-gray-200 hover:bg-gray-300 text-gray-700 font-semibold rounded-lg transition">
            ← {{ __('Back') }}
         </a>
      </div>

      <!-- Payment Info Card -->
      <div class="bg-white rounded-lg shadow-md p-6 mb-6">
         <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div>
               <h3 class="text-sm font-medium text-gray-500 uppercase tracking-wider mb-3">{{ __('Payment Information') }}</h3>
               <dl class="space-y-2">
                  <div class="flex justify-between">
                     <dt class="text-sm text-gray-600">{{ __('Date') }}</dt>
                     <dd class="text-sm font-medium text-gray-900">{{ $payment->payment_date->format('d M Y') }}</dd>
                  </div>
                  <div class="flex justify-between">
                     <dt class="text-sm text-gray-600">{{ __('Method') }}</dt>
                     <dd class="text-sm font-medium text-gray-900">
                        {{ \App\Models\Payment::PAYMENT_METHODS[$payment->payment_method] ?? $payment->payment_method }}
                     </dd>
                  </div>
                  <div class="flex justify-between">
                     <dt class="text-sm text-gray-600">{{ __('Reference') }}</dt>
                     <dd class="text-sm font-medium text-gray-900">{{ $payment->reference ?? '-' }}</dd>
                  </div>
                  <div class="flex justify-between">
                     <dt class="text-sm text-gray-600">{{ __('Customer') }}</dt>
                     <dd class="text-sm font-medium text-gray-900">{{ $payment->customer->name ?? '-' }}</dd>
                  </div>
                  @if ($payment->salesOrder)
                     <div class="flex justify-between">
                        <dt class="text-sm text-gray-600">{{ __('Sales Order') }}</dt>
                        <dd class="text-sm font-medium">
                           <a href="{{ route('sales-orders.show', $payment->salesOrder) }}" class="text-blue-600 hover:text-blue-900">
                              {{ $payment->salesOrder->so_number }}
                           </a>
                        </dd>
                     </div>
                  @endif
                  @if ($payment->bankAccount)
                     <div class="flex justify-between">
                        <dt class="text-sm text-gray-600">{{ __('Bank Account') }}</dt>
                        <dd class="text-sm font-medium text-gray-900">
                           {{ $payment->bankAccount->bank_name }} — {{ $payment->bankAccount->account_number }}
                        </dd>
                     </div>
                  @endif
               </dl>
            </div>

            <div>
               <h3 class="text-sm font-medium text-gray-500 uppercase tracking-wider mb-3">{{ __('Amount Details') }}</h3>
               <dl class="space-y-2">
                  <div class="flex justify-between">
                     <dt class="text-sm text-gray-600">{{ __('Amount') }}</dt>
                     <dd class="text-lg font-bold text-emerald-700">
                        {{ $payment->currency_code }} {{ number_format($payment->amount, 2) }}
                     </dd>
                  </div>
                  <div class="flex justify-between">
                     <dt class="text-sm text-gray-600">{{ __('Exchange Rate') }}</dt>
                     <dd class="text-sm font-medium text-gray-900">{{ number_format($payment->exchange_rate, 4) }}</dd>
                  </div>
                  <div class="flex justify-between">
                     <dt class="text-sm text-gray-600">{{ __('Base Currency Amount') }}</dt>
                     <dd class="text-sm font-medium text-gray-900">
                        Rp {{ number_format($payment->base_currency_amount, 0, ',', '.') }}
                     </dd>
                  </div>
                  @if ($payment->bank_fees > 0)
                     <div class="flex justify-between">
                        <dt class="text-sm text-gray-600">{{ __('Bank Fees') }}</dt>
                        <dd class="text-sm font-medium text-red-600">
                           {{ $payment->currency_code }} {{ number_format($payment->bank_fees, 2) }}
                        </dd>
                     </div>
                  @endif
               </dl>
            </div>
         </div>

         @if ($payment->notes)
            <div class="mt-6 pt-4 border-t">
               <h3 class="text-sm font-medium text-gray-500 uppercase tracking-wider mb-2">{{ __('Notes') }}</h3>
               <p class="text-sm text-gray-700">{{ $payment->notes }}</p>
            </div>
         @endif
      </div>

      <!-- Allocations (if deposit) -->
      @if (!$payment->sales_order_id && $payment->allocations->count() > 0)
         <div class="bg-white rounded-lg shadow-md p-6">
            <h3 class="text-lg font-bold text-gray-900 mb-4">{{ __('Payment Allocations') }}</h3>
            <table class="min-w-full divide-y divide-gray-200">
               <thead class="bg-gray-50">
                  <tr>
                     <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">{{ __('Sales Order') }}</th>
                     <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase">{{ __('Amount Allocated') }}</th>
                     <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">{{ __('Date') }}</th>
                  </tr>
               </thead>
               <tbody class="divide-y divide-gray-200">
                  @foreach ($payment->allocations as $alloc)
                     <tr>
                        <td class="px-4 py-3 text-sm">
                           <a href="{{ route('sales-orders.show', $alloc->salesOrder) }}" class="text-blue-600 hover:text-blue-900">
                              {{ $alloc->salesOrder->so_number }}
                           </a>
                        </td>
                        <td class="px-4 py-3 text-sm text-right font-medium">
                           Rp {{ number_format($alloc->amount, 0, ',', '.') }}
                        </td>
                        <td class="px-4 py-3 text-sm text-gray-600">
                           {{ $alloc->created_at->format('d M Y') }}
                        </td>
                     </tr>
                  @endforeach
               </tbody>
            </table>
         </div>
      @endif
   </div>
@endsection
