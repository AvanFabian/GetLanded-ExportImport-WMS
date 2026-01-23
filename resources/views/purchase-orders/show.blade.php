@extends('layouts.app')

@section('title', __('app.purchase_order_detail'))

@section('content')
   <div class="max-w-7xl mx-auto p-6">
      <div class="flex flex-col md:flex-row md:items-center md:justify-between mb-6 gap-4">
         <div>
            <h2 class="text-2xl font-semibold text-gray-800">{{ __('app.purchase_order_detail') }}</h2>
            <p class="text-gray-600 text-lg font-semibold">{{ $purchaseOrder->po_number }}</p>
         </div>
         <div class="flex flex-wrap gap-2">
            <a href="{{ route('purchase-orders.index') }}"
               class="px-4 py-2 bg-gray-500 text-white rounded hover:bg-gray-600">
               <svg class="w-4 h-4 inline-block mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18">
                  </path>
               </svg>
               {{ __('app.back') }}
            </a>

            @if ($purchaseOrder->canBeEdited())
               <a href="{{ route('purchase-orders.edit', $purchaseOrder) }}"
                  class="px-4 py-2 bg-yellow-500 text-white rounded hover:bg-yellow-600">
                  <svg class="w-4 h-4 inline-block mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                     <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z">
                     </path>
                  </svg>
                  {{ __('app.edit') }}
               </a>
            @endif

            @if ($purchaseOrder->status === 'draft')
               <form action="{{ route('purchase-orders.submit', $purchaseOrder) }}" method="POST" class="inline-block"
                  onsubmit="return confirm('{{ __('app.confirm_submit_po') }}')">
                  @csrf
                  <button type="submit" class="px-4 py-2 bg-primary text-white rounded hover:bg-blue-700">
                     <svg class="w-4 h-4 inline-block mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                           d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8"></path>
                     </svg>
                     {{ __('app.submit') }}
                  </button>
               </form>
            @endif

            @if ($purchaseOrder->canBeApproved())
               <form action="{{ route('purchase-orders.approve', $purchaseOrder) }}" method="POST" class="inline-block"
                  onsubmit="return confirm('{{ __('app.confirm_approve_po') }}')">
                  @csrf
                  <button type="submit" class="px-4 py-2 bg-green-600 text-white rounded hover:bg-green-700">
                     <svg class="w-4 h-4 inline-block mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                     </svg>
                     {{ __('app.approve') }}
                  </button>
               </form>
               <form action="{{ route('purchase-orders.reject', $purchaseOrder) }}" method="POST" class="inline-block"
                  onsubmit="return confirm('{{ __('app.confirm_reject_po') }}')">
                  @csrf
                  <button type="submit" class="px-4 py-2 bg-red-600 text-white rounded hover:bg-red-700">
                     <svg class="w-4 h-4 inline-block mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12">
                        </path>
                     </svg>
                     {{ __('app.reject') }}
                  </button>
               </form>
            @endif

            @if ($purchaseOrder->canBeReceived())
               <a href="{{ route('purchase-orders.receive', $purchaseOrder) }}"
                  class="px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-700">
                  <svg class="w-4 h-4 inline-block mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                     <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"></path>
                  </svg>
                  {{ __('app.receive_goods') }}
               </a>
            @endif
         </div>
      </div>

      @if (session('success'))
         <div class="mb-4 p-4 bg-green-100 border border-green-400 text-green-700 rounded">
            {{ session('success') }}
         </div>
      @endif
      @if (session('error'))
         <div class="mb-4 p-4 bg-red-100 border border-red-400 text-red-700 rounded">
            {{ session('error') }}
         </div>
      @endif

      <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
         <div class="lg:col-span-2">
            <div class="bg-white rounded shadow mb-6">
               <div class="px-6 py-4 border-b border-gray-200">
                  <h3 class="text-lg font-semibold text-gray-800">{{ __('app.po_information') }}</h3>
               </div>
               <div class="p-6">
                  <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-4">
                     <div>
                        <p class="text-sm text-gray-600 mb-1">{{ __('app.po_number') }}:</p>
                        <p class="text-xl font-semibold text-blue-600">{{ $purchaseOrder->po_number }}</p>
                     </div>
                     <div>
                        <p class="text-sm text-gray-600 mb-1">{{ __('app.status') }}:</p>
                        @if ($purchaseOrder->status === 'draft')
                           <span class="px-3 py-1 text-sm rounded bg-gray-100 text-gray-700">{{ __('app.draft') }}</span>
                        @elseif($purchaseOrder->status === 'pending')
                           <span
                              class="px-3 py-1 text-sm rounded bg-yellow-100 text-yellow-700">{{ __('app.pending') }}</span>
                        @elseif($purchaseOrder->status === 'approved')
                           <span
                              class="px-3 py-1 text-sm rounded bg-green-100 text-green-700">{{ __('app.approved') }}</span>
                        @elseif($purchaseOrder->status === 'partially_received')
                           <span
                              class="px-3 py-1 text-sm rounded bg-blue-100 text-blue-700">{{ __('app.partially_received') }}</span>
                        @elseif($purchaseOrder->status === 'completed')
                           <span
                              class="px-3 py-1 text-sm rounded bg-purple-100 text-purple-700">{{ __('app.completed') }}</span>
                        @elseif($purchaseOrder->status === 'cancelled')
                           <span
                              class="px-3 py-1 text-sm rounded bg-red-100 text-red-700">{{ __('app.cancelled') }}</span>
                        @endif
                     </div>
                  </div>

                  <div class="border-t pt-4 mb-4"></div>

                  <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-4">
                     <div>
                        <p class="text-sm text-gray-600 mb-1">{{ __('app.warehouse') }}:</p>
                        <p class="font-medium">{{ $purchaseOrder->warehouse->name }}</p>
                     </div>
                     <div>
                        <p class="text-sm text-gray-600 mb-1">{{ __('app.supplier') }}:</p>
                        <p class="font-medium">{{ $purchaseOrder->supplier->name }}</p>
                        <p class="text-sm text-gray-500">{{ $purchaseOrder->supplier->phone }}</p>
                     </div>
                  </div>

                  <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-4">
                     <div>
                        <p class="text-sm text-gray-600 mb-1">{{ __('app.order_date') }}:</p>
                        <p class="font-medium">{{ $purchaseOrder->order_date->format('d/m/Y') }}</p>
                     </div>
                     <div>
                        <p class="text-sm text-gray-600 mb-1">{{ __('app.expected_delivery_date') }}:</p>
                        <p class="font-medium">
                           {{ $purchaseOrder->expected_delivery_date ? $purchaseOrder->expected_delivery_date->format('d/m/Y') : '-' }}
                        </p>
                     </div>
                  </div>

                  @if ($purchaseOrder->notes)
                     <div class="mb-4">
                        <p class="text-sm text-gray-600 mb-1">{{ __('app.notes') }}:</p>
                        <p class="text-gray-700">{{ $purchaseOrder->notes }}</p>
                     </div>
                  @endif

                  <div class="border-t pt-4"></div>

                  <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mt-4">
                     <div>
                        <p class="text-sm text-gray-600 mb-1">{{ __('app.created_by') }}:</p>
                        <p class="font-medium">{{ $purchaseOrder->createdBy->name }}</p>
                        <p class="text-sm text-gray-500">{{ $purchaseOrder->created_at->format('d/m/Y H:i') }}</p>
                     </div>
                     @if ($purchaseOrder->approvedBy)
                        <div>
                           <p class="text-sm text-gray-600 mb-1">{{ __('app.approved_by') }}:</p>
                           <p class="font-medium">{{ $purchaseOrder->approvedBy->name }}</p>
                           <p class="text-sm text-gray-500">{{ $purchaseOrder->approved_at->format('d/m/Y H:i') }}</p>
                        </div>
                     @endif
                  </div>
               </div>
            </div>

            <div class="bg-white rounded shadow">
               <div class="px-6 py-4 border-b border-gray-200">
                  <h3 class="text-lg font-semibold text-gray-800">{{ __('app.product_details') }}</h3>
               </div>
               <div class="p-6">
                  <div class="overflow-x-auto">
                     <table class="min-w-full border">
                        <thead class="bg-gray-50">
                           <tr>
                              <th class="text-left p-3 font-semibold border-b">{{ __('app.product') }}</th>
                              <th class="text-center p-3 font-semibold border-b w-24">{{ __('app.ordered') }}</th>
                              <th class="text-center p-3 font-semibold border-b w-24">{{ __('app.received') }}</th>
                              <th class="text-center p-3 font-semibold border-b w-24">{{ __('app.remaining') }}</th>
                              <th class="text-right p-3 font-semibold border-b w-32">{{ __('app.unit_price') }}</th>
                              <th class="text-right p-3 font-semibold border-b w-32">{{ __('app.subtotal') }}</th>
                           </tr>
                        </thead>
                        <tbody>
                           @foreach ($purchaseOrder->details as $detail)
                              <tr class="border-t">
                                 <td class="p-3">
                                    <p class="font-medium">{{ $detail->product->name }}</p>
                                    <p class="text-sm text-gray-500">{{ $detail->product->code }}</p>
                                 </td>
                                 <td class="p-3 text-center">{{ $detail->quantity_ordered }}</td>
                                 <td class="p-3 text-center">
                                    <span
                                       class="px-2 py-1 text-xs rounded bg-blue-100 text-blue-700">{{ $detail->quantity_received }}</span>
                                 </td>
                                 <td class="p-3 text-center">
                                    @if ($detail->getRemainingQuantity() > 0)
                                       <span
                                          class="px-2 py-1 text-xs rounded bg-yellow-100 text-yellow-700">{{ $detail->getRemainingQuantity() }}</span>
                                    @else
                                       <span class="px-2 py-1 text-xs rounded bg-green-100 text-green-700">0</span>
                                    @endif
                                 </td>
                                 <td class="p-3 text-right">Rp {{ number_format($detail->unit_price, 0, ',', '.') }}</td>
                                 <td class="p-3 text-right">Rp {{ number_format($detail->subtotal, 0, ',', '.') }}</td>
                              </tr>
                           @endforeach
                        </tbody>
                        <tfoot class="bg-gray-50">
                           <tr class="border-t-2">
                              <td colspan="5" class="p-3 text-right font-bold">{{ __('app.total') }}:</td>
                              <td class="p-3 text-right font-bold">Rp
                                 {{ number_format($purchaseOrder->total_amount, 0, ',', '.') }}</td>
                           </tr>
                        </tfoot>
                     </table>
                  </div>
               </div>
            </div>
         </div>

         <div class="md:w-1/3">
            <div class="bg-white rounded shadow mb-6">
               <div class="px-6 py-4 border-b border-gray-200">
                  <h3 class="text-lg font-semibold text-gray-800">{{ __('app.summary') }}</h3>
               </div>
               <div class="p-6">
                  <div class="mb-4">
                     <p class="text-sm text-gray-600 mb-1">{{ __('app.total_items') }}</p>
                     <p class="text-2xl font-bold">{{ $purchaseOrder->details->count() }} {{ __('app.products') }}</p>
                  </div>

                  <div class="mb-4">
                     <p class="text-sm text-gray-600 mb-1">{{ __('app.total_quantity_ordered') }}</p>
                     <p class="text-2xl font-bold">{{ $purchaseOrder->details->sum('quantity_ordered') }}
                        {{ __('app.unit') }}</p>
                  </div>

                  <div class="mb-4">
                     <p class="text-sm text-gray-600 mb-1">{{ __('app.total_received') }}</p>
                     <p class="text-2xl font-bold">{{ $purchaseOrder->details->sum('quantity_received') }}
                        {{ __('app.unit') }}</p>
                  </div>

                  <div class="border-t border-gray-200 my-4"></div>

                  <div class="mb-4">
                     <p class="text-sm text-gray-600 mb-1">{{ __('app.total_po_value') }}</p>
                     <p class="text-3xl font-bold text-blue-600">Rp
                        {{ number_format($purchaseOrder->total_amount, 0, ',', '.') }}</p>
                  </div>

                  @if ($purchaseOrder->canBeReceived())
                     @php
                        $totalOrdered = $purchaseOrder->details->sum('quantity_ordered');
                        $totalReceived = $purchaseOrder->details->sum('quantity_received');
                        $percentage = $totalOrdered > 0 ? ($totalReceived / $totalOrdered) * 100 : 0;
                     @endphp
                     <div class="mb-2">
                        <div class="w-full bg-gray-200 rounded-full h-6 overflow-hidden">
                           <div
                              class="bg-blue-500 h-6 text-white text-xs flex items-center justify-center font-medium rounded-full"
                              style="width: {{ $percentage }}%">
                              {{ number_format($percentage, 1) }}%
                           </div>
                        </div>
                     </div>
                     <p class="text-sm text-gray-600">{{ __('app.receiving_progress') }}</p>
                  @endif
               </div>
            </div>

            @if ($purchaseOrder->canBeEdited())
               <div class="bg-white rounded shadow border-l-4 border-yellow-500">
                  <div class="p-6">
                     <h4 class="text-yellow-600 font-semibold mb-4 flex items-center">
                        <svg class="w-5 h-5 mr-2" fill="currentColor" viewBox="0 0 20 20">
                           <path fill-rule="evenodd"
                              d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z"
                              clip-rule="evenodd" />
                        </svg>
                        {{ __('app.actions') }}
                     </h4>
                     <form action="{{ route('purchase-orders.destroy', $purchaseOrder) }}" method="POST"
                        onsubmit="return confirm('{{ __('app.confirm_delete_po') }}')">
                        @csrf
                        @method('DELETE')
                        <button type="submit"
                           class="w-full bg-red-600 hover:bg-red-700 text-white font-medium py-2 px-4 rounded flex items-center justify-center">
                           <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                 d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                           </svg>
                           {{ __('app.delete_po') }}
                        </button>
                     </form>
                  </div>
               </div>
            @endif
         </div>
      </div>
   </div>
@endsection
