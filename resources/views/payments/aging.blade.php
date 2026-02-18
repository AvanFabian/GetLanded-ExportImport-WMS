@extends('layouts.app')

@section('content')
   <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
      <div class="flex justify-between items-center mb-6">
         <div>
            <h1 class="text-3xl font-bold text-gray-900">{{ __('AR Aging Report') }}</h1>
            <p class="mt-1 text-sm text-gray-600">{{ __('Accounts receivable aging analysis') }}</p>
         </div>
         <a href="{{ route('payments.index') }}" class="px-4 py-2 bg-gray-200 hover:bg-gray-300 text-gray-700 font-semibold rounded-lg transition">
            ← {{ __('Back to Payments') }}
         </a>
      </div>

      <!-- Summary Cards -->
      <div class="grid grid-cols-2 md:grid-cols-5 gap-4 mb-6">
         @php
            $brackets = [
               'current' => ['label' => 'Current', 'color' => 'emerald'],
               '1_30' => ['label' => '1-30 Days', 'color' => 'yellow'],
               '31_60' => ['label' => '31-60 Days', 'color' => 'orange'],
               '61_90' => ['label' => '61-90 Days', 'color' => 'red'],
               'over_90' => ['label' => '90+ Days', 'color' => 'rose'],
            ];
         @endphp

         @foreach ($brackets as $key => $bracket)
            <div class="bg-white rounded-lg shadow-md p-4 border-l-4 border-{{ $bracket['color'] }}-500">
               <p class="text-xs font-medium text-gray-500 uppercase">{{ $bracket['label'] }}</p>
               <p class="text-lg font-bold text-gray-900 mt-1">
                  Rp {{ number_format($aging[$key]['total'], 0, ',', '.') }}
               </p>
               <p class="text-xs text-gray-400 mt-1">{{ $aging[$key]['orders']->count() }} {{ __('orders') }}</p>
            </div>
         @endforeach
      </div>

      <!-- Total Outstanding -->
      <div class="bg-gradient-to-r from-emerald-700 to-emerald-800 rounded-lg shadow-md p-6 mb-6 text-white">
         <div class="flex justify-between items-center">
            <div>
               <p class="text-sm font-medium text-emerald-200">{{ __('Total Outstanding') }}</p>
               <p class="text-3xl font-bold mt-1">Rp {{ number_format($totalOutstanding, 0, ',', '.') }}</p>
            </div>
         </div>
      </div>

      <!-- Detailed Table -->
      @foreach ($brackets as $key => $bracket)
         @if ($aging[$key]['orders']->count() > 0)
            <div class="bg-white rounded-lg shadow-md overflow-hidden mb-6">
               <div class="px-6 py-4 bg-{{ $bracket['color'] }}-50 border-b border-{{ $bracket['color'] }}-200">
                  <h3 class="text-lg font-bold text-gray-900">
                     {{ $bracket['label'] }}
                     <span class="text-sm font-normal text-gray-500 ml-2">
                        ({{ $aging[$key]['orders']->count() }} {{ __('orders') }} — Rp {{ number_format($aging[$key]['total'], 0, ',', '.') }})
                     </span>
                  </h3>
               </div>
               <div class="overflow-x-auto">
                  <table class="min-w-full divide-y divide-gray-200">
                     <thead class="bg-gray-50">
                        <tr>
                           <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">{{ __('SO Number') }}</th>
                           <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">{{ __('Customer') }}</th>
                           <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">{{ __('Due Date') }}</th>
                           <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">{{ __('Total') }}</th>
                           <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">{{ __('Paid') }}</th>
                           <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">{{ __('Outstanding') }}</th>
                           <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase">{{ __('Actions') }}</th>
                        </tr>
                     </thead>
                     <tbody class="divide-y divide-gray-200">
                        @foreach ($aging[$key]['orders'] as $order)
                           @php $outstanding = $order->total - $order->amount_paid - $order->credit_note_amount; @endphp
                           <tr class="hover:bg-gray-50">
                              <td class="px-6 py-4 text-sm">
                                 <a href="{{ route('sales-orders.show', $order) }}" class="text-blue-600 hover:text-blue-900 font-medium">
                                    {{ $order->so_number }}
                                 </a>
                              </td>
                              <td class="px-6 py-4 text-sm text-gray-900">{{ $order->customer->name ?? '-' }}</td>
                              <td class="px-6 py-4 text-sm {{ $order->due_date?->isPast() ? 'text-red-600 font-semibold' : 'text-gray-900' }}">
                                 {{ $order->due_date?->format('d M Y') ?? '-' }}
                              </td>
                              <td class="px-6 py-4 text-sm text-right">Rp {{ number_format($order->total, 0, ',', '.') }}</td>
                              <td class="px-6 py-4 text-sm text-right text-emerald-700">Rp {{ number_format($order->amount_paid, 0, ',', '.') }}</td>
                              <td class="px-6 py-4 text-sm text-right font-bold text-red-700">Rp {{ number_format($outstanding, 0, ',', '.') }}</td>
                              <td class="px-6 py-4 text-center">
                                 <a href="{{ route('payments.create', ['sales_order_id' => $order->id]) }}"
                                    class="inline-flex items-center px-3 py-1 bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-semibold rounded-lg transition">
                                    {{ __('Record Payment') }}
                                 </a>
                              </td>
                           </tr>
                        @endforeach
                     </tbody>
                  </table>
               </div>
            </div>
         @endif
      @endforeach
   </div>
@endsection
