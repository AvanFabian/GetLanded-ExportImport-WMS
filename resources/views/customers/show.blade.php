@extends('layouts.app')

@section('content')
   <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
      <!-- Header -->
      <div class="mb-6 flex justify-between items-center">
         <div>
            <h1 class="text-3xl font-bold text-gray-900">{{ $customer->name }}</h1>
            <p class="mt-1 text-sm text-gray-600">{{ __('app.customer_details') }}</p>
         </div>
         <div class="flex gap-3">
            <a href="{{ route('customers.edit', $customer) }}"
               class="inline-flex items-center px-4 py-2 bg-yellow-500 hover:bg-yellow-600 text-white font-semibold rounded-lg shadow-md transition duration-150">
               <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                     d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z">
                  </path>
               </svg>
               {{ __('app.edit') }}
            </a>
            <a href="{{ route('customers.index') }}"
               class="inline-flex items-center px-4 py-2 bg-gray-200 hover:bg-gray-300 text-gray-700 font-semibold rounded-lg transition duration-150">
               {{ __('app.back') }}
            </a>
         </div>
      </div>

      <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
         <!-- Customer Information -->
         <div class="lg:col-span-1">
            <div class="bg-white rounded-lg shadow-md p-6">
               <h2 class="text-lg font-semibold text-gray-900 mb-4">{{ __('app.customer_information') }}</h2>

               <div class="space-y-4">
                  <div>
                     <div class="text-sm text-gray-500 mb-1">{{ __('app.status') }}</div>
                     @if ($customer->is_active)
                        <span class="px-3 py-1 inline-flex text-sm font-semibold rounded-full bg-green-100 text-green-800">
                           {{ __('app.active') }}
                        </span>
                     @else
                        <span class="px-3 py-1 inline-flex text-sm font-semibold rounded-full bg-red-100 text-red-800">
                           {{ __('app.inactive') }}
                        </span>
                     @endif
                  </div>

                  <div>
                     <div class="text-sm text-gray-500 mb-1">{{ __('app.address') }}</div>
                     <div class="text-sm text-gray-900">{{ $customer->address ?? '-' }}</div>
                  </div>

                  <div>
                     <div class="text-sm text-gray-500 mb-1">{{ __('app.phone') }}</div>
                     <div class="text-sm text-gray-900">{{ $customer->phone ?? '-' }}</div>
                  </div>

                  <div>
                     <div class="text-sm text-gray-500 mb-1">{{ __('app.email') }}</div>
                     <div class="text-sm text-gray-900">{{ $customer->email ?? '-' }}</div>
                  </div>

                  <div>
                     <div class="text-sm text-gray-500 mb-1">{{ __('app.npwp') }}</div>
                     <div class="text-sm text-gray-900">{{ $customer->tax_id ?? '-' }}</div>
                  </div>

                  @if ($customer->notes)
                     <div>
                        <div class="text-sm text-gray-500 mb-1">{{ __('app.notes') }}</div>
                        <div class="text-sm text-gray-900">{{ $customer->notes }}</div>
                     </div>
                  @endif

                  <div class="pt-4 border-t border-gray-200">
                     <div class="text-sm text-gray-500 mb-1">{{ __('app.created') }}</div>
                     <div class="text-sm text-gray-900">
                        {{ $customer->created_at->format('d/m/Y H:i') }}
                        @if ($customer->creator)
                           <span class="text-gray-500">{{ __('app.by') ?? 'oleh' }} {{ $customer->creator->name }}</span>
                        @endif
                     </div>
                  </div>

                  @if ($customer->updated_at != $customer->created_at)
                     <div>
                        <div class="text-sm text-gray-500 mb-1">{{ __('app.updated') }}</div>
                        <div class="text-sm text-gray-900">
                           {{ $customer->updated_at->format('d/m/Y H:i') }}
                           @if ($customer->updater)
                              <span class="text-gray-500">{{ __('app.by') ?? 'oleh' }} {{ $customer->updater->name }}</span>
                           @endif
                        </div>
                     </div>
                  @endif
               </div>
            </div>
         </div>

         <!-- Sales Orders History -->
         <div class="lg:col-span-2">
            <div class="bg-white rounded-lg shadow-md p-6">
               <div class="flex justify-between items-center mb-4">
                  <h2 class="text-lg font-semibold text-gray-900">{{ __('app.sales_order_history') }}</h2>
                  <a href="{{ route('sales-orders.create', ['customer_id' => $customer->id]) }}"
                     class="text-sm text-blue-600 hover:text-blue-800 font-medium">
                     {{ __('app.create_new_order') }}
                  </a>
               </div>

               @if ($customer->salesOrders->count() > 0)
                  <div class="overflow-x-auto">
                     <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                           <tr>
                              <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">{{ __('app.so_number') }}</th>
                              <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">{{ __('app.date') }}</th>
                              <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">{{ __('app.total') }}</th>
                              <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">{{ __('app.status') }}</th>
                              <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">{{ __('app.payment') }}</th>
                              <th class="px-4 py-3 text-center text-xs font-medium text-gray-500 uppercase">{{ __('app.actions') }}</th>
                           </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                           @foreach ($customer->salesOrders as $order)
                              <tr class="hover:bg-gray-50">
                                 <td class="px-4 py-3 text-sm font-medium text-gray-900">
                                    {{ $order->so_number }}
                                 </td>
                                 <td class="px-4 py-3 text-sm text-gray-900">
                                    {{ $order->order_date->format('d/m/Y') }}
                                 </td>
                                 <td class="px-4 py-3 text-sm text-gray-900">
                                    Rp {{ number_format($order->total, 0, ',', '.') }}
                                 </td>
                                 <td class="px-4 py-3">
                                    @php
                                       $statusColors = [
                                           'draft' => 'bg-gray-100 text-gray-800',
                                           'confirmed' => 'bg-blue-100 text-blue-800',
                                           'shipped' => 'bg-purple-100 text-purple-800',
                                           'delivered' => 'bg-green-100 text-green-800',
                                           'cancelled' => 'bg-red-100 text-red-800',
                                       ];
                                       $statusLabels = [
                                           'draft' => __('app.draft'),
                                           'confirmed' => __('app.confirmed'),
                                           'shipped' => __('app.shipped'),
                                           'delivered' => __('app.delivered'),
                                           'cancelled' => __('app.cancelled'),
                                       ];
                                    @endphp
                                    <span
                                       class="px-2 py-1 inline-flex text-xs font-semibold rounded-full {{ $statusColors[$order->status] }}">
                                       {{ $statusLabels[$order->status] }}
                                    </span>
                                 </td>
                                 <td class="px-4 py-3">
                                    @php
                                       $paymentColors = [
                                           'unpaid' => 'bg-red-100 text-red-800',
                                           'partial' => 'bg-yellow-100 text-yellow-800',
                                           'paid' => 'bg-green-100 text-green-800',
                                       ];
                                       $paymentLabels = [
                                           'unpaid' => __('app.unpaid'),
                                           'partial' => __('app.partial'),
                                           'paid' => __('app.paid'),
                                       ];
                                    @endphp
                                    <span
                                       class="px-2 py-1 inline-flex text-xs font-semibold rounded-full {{ $paymentColors[$order->payment_status] }}">
                                       {{ $paymentLabels[$order->payment_status] }}
                                    </span>
                                 </td>
                                 <td class="px-4 py-3 text-center">
                                    <a href="{{ route('sales-orders.show', $order) }}"
                                       class="text-blue-600 hover:text-blue-900 text-sm font-medium">
                                       {{ __('app.view') }}
                                    </a>
                                 </td>
                              </tr>
                           @endforeach
                        </tbody>
                     </table>
                  </div>
               @else
                  <div class="text-center py-8 text-gray-500">
                     <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                           d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z">
                        </path>
                     </svg>
                     <p class="mt-2">{{ __('app.no_sales_order_history') }}</p>
                  </div>
               @endif
            </div>
         </div>
      </div>
   </div>
@endsection
