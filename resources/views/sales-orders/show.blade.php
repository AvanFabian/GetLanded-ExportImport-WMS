@extends('layouts.app')

@section('content')
   <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
      <!-- Header -->
      <div class="flex justify-between items-start mb-6">
         <div>
            <h1 class="text-3xl font-bold text-gray-900">{{ __('app.sales_order_details') }}</h1>
            <p class="mt-1 text-sm text-gray-600">{{ $salesOrder->so_number }}</p>
         </div>
         <div class="flex gap-2">
            @if ($salesOrder->status === 'draft')
               <a href="{{ route('sales-orders.edit', $salesOrder) }}"
                  class="px-4 py-2 bg-yellow-600 hover:bg-yellow-700 text-white font-semibold rounded-lg transition duration-150">
                  {{ __('app.edit') }}
               </a>
            @endif
            <a href="{{ route('sales-orders.index') }}"
               class="px-4 py-2 bg-gray-200 hover:bg-gray-300 text-gray-700 font-semibold rounded-lg transition duration-150">
               {{ __('app.back') }}
            </a>
         </div>
      </div>

      <!-- Success Message -->
      @if (session('success'))
         <div class="mb-6 bg-green-50 border border-green-200 text-green-800 px-4 py-3 rounded-lg">
            {{ session('success') }}
         </div>
      @endif

      <!-- Error Message -->
      @if (session('error'))
         <div class="mb-6 bg-red-50 border border-red-200 text-red-800 px-4 py-3 rounded-lg">
            {{ session('error') }}
         </div>
      @endif

      <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
         <!-- Main Information -->
         <div class="lg:col-span-2 space-y-6">
            <!-- Order Details Card -->
            <div class="bg-white rounded-lg shadow-md p-6">
               <h2 class="text-lg font-semibold text-gray-900 mb-4">{{ __('app.order_information') }}</h2>

               <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                  <div>
                     <label class="block text-sm font-medium text-gray-500">{{ __('app.so_number') }}</label>
                     <p class="mt-1 text-sm text-gray-900 font-semibold">{{ $salesOrder->so_number }}</p>
                  </div>

                  <div>
                     <label class="block text-sm font-medium text-gray-500">{{ __('app.status') }}</label>
                     <div class="mt-1">
                        @if ($salesOrder->status === 'draft')
                           <span
                              class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-800">{{ __('app.draft') }}</span>
                        @elseif($salesOrder->status === 'confirmed')
                           <span
                              class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800">{{ __('app.approved') }}</span>
                        @elseif($salesOrder->status === 'shipped')
                           <span
                              class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800">{{ __('app.shipped') }}</span>
                        @elseif($salesOrder->status === 'delivered')
                           <span
                              class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">{{ __('app.delivered') }}</span>
                        @else
                           <span
                              class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800">{{ __('app.cancelled') }}</span>
                        @endif
                     </div>
                  </div>

                  <div>
                     <label class="block text-sm font-medium text-gray-500">{{ __('app.customer') }}</label>
                     <p class="mt-1 text-sm text-gray-900">
                        <a href="{{ route('customers.show', $salesOrder->customer) }}"
                           class="text-blue-600 hover:text-blue-800 font-medium">
                           {{ $salesOrder->customer->name }}
                        </a>
                     </p>
                  </div>

                  <div>
                     <label class="block text-sm font-medium text-gray-500">{{ __('app.warehouse') }}</label>
                     <p class="mt-1 text-sm text-gray-900">{{ $salesOrder->warehouse->name }}</p>
                  </div>

                  <div>
                     <label class="block text-sm font-medium text-gray-500">{{ __('app.order_date') }}</label>
                     <p class="mt-1 text-sm text-gray-900">{{ $salesOrder->order_date->format('d M Y') }}</p>
                  </div>

                  <div>
                     <label class="block text-sm font-medium text-gray-500">{{ __('app.delivery_date') }}</label>
                     <p class="mt-1 text-sm text-gray-900">{{ $salesOrder->delivery_date?->format('d M Y') ?? '-' }}</p>
                  </div>

                  <div>
                     <label class="block text-sm font-medium text-gray-500">{{ __('app.payment_status') }}</label>
                     <div class="mt-1">
                        @if ($salesOrder->payment_status === 'unpaid')
                           <span
                              class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800">{{ __('app.unpaid') }}</span>
                        @elseif($salesOrder->payment_status === 'partial')
                           <span
                              class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800">{{ __('app.partial') }}</span>
                        @else
                           <span
                              class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">{{ __('app.paid') }}</span>
                        @endif
                     </div>
                  </div>

                  @if ($salesOrder->stock_out_id)
                     <div>
                        <label class="block text-sm font-medium text-gray-500">{{ __('app.stock_out') }}</label>
                        <p class="mt-1 text-sm text-blue-600 font-medium">
                           <a href="{{ route('stock-outs.show', $salesOrder->stock_out_id) }}"
                              class="hover:text-blue-800">
                              {{ __('app.view_details') }}
                           </a>
                        </p>
                     </div>
                  @endif
               </div>

               @if ($salesOrder->notes)
                  <div class="mt-4 pt-4 border-t border-gray-200">
                     <label class="block text-sm font-medium text-gray-500">{{ __('app.notes') }}</label>
                     <p class="mt-1 text-sm text-gray-900">{{ $salesOrder->notes }}</p>
                  </div>
               @endif
            </div>

            <!-- Products Table -->
            <div class="bg-white rounded-lg shadow-md p-6">
               <h2 class="text-lg font-semibold text-gray-900 mb-4">{{ __('app.products') }}</h2>

               <div class="overflow-x-auto">
                  <table class="min-w-full divide-y divide-gray-200">
                     <thead class="bg-gray-50">
                        <tr>
                           <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">{{ __('app.product') }}</th>
                           <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase">{{ __('app.qty') }}</th>
                           <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase">{{ __('app.unit_price') }}</th>
                           <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase">{{ __('app.subtotal') }}</th>
                        </tr>
                     </thead>
                     <tbody class="bg-white divide-y divide-gray-200">
                        @foreach ($salesOrder->items as $item)
                           <tr>
                              <td class="px-4 py-3">
                                 <div class="text-sm font-medium text-gray-900">{{ $item->product->name }}</div>
                                 <div class="text-xs text-gray-500">SKU: {{ $item->product->sku }}</div>
                              </td>
                              <td class="px-4 py-3 text-right text-sm text-gray-900">{{ number_format($item->quantity) }}
                              </td>
                              <td class="px-4 py-3 text-right text-sm text-gray-900">Rp
                                 {{ number_format($item->unit_price, 0, ',', '.') }}</td>
                              <td class="px-4 py-3 text-right text-sm font-semibold text-gray-900">Rp
                                 {{ number_format($item->subtotal, 0, ',', '.') }}</td>
                        </tr>
                        @endforeach
                     </tbody>
                     <tfoot class="bg-gray-50">
                        {{-- Currency Badge --}}
                        @if($salesOrder->currency_code && $salesOrder->currency_code !== 'IDR')
                           <tr class="bg-blue-50">
                              <td colspan="4" class="px-4 py-2 text-sm text-blue-800">
                                 <div class="flex items-center gap-2">
                                    <x-currency-badge :code="$salesOrder->currency_code" :rate="$salesOrder->exchange_rate_at_transaction" />
                                    <span class="text-xs">Rate locked at transaction time</span>
                                 </div>
                              </td>
                           </tr>
                        @endif
                        <tr>
                           <td colspan="3" class="px-4 py-3 text-right text-sm font-medium text-gray-700">{{ __('app.subtotal') }}:</td>
                           <td class="px-4 py-3 text-right text-sm font-semibold text-gray-900">Rp
                              {{ number_format($salesOrder->subtotal, 0, ',', '.') }}</td>
                        </tr>
                        @if ($salesOrder->discount > 0)
                           <tr>
                              <td colspan="3" class="px-4 py-3 text-right text-sm font-medium text-gray-700">{{ __('app.discount') }}:
                              </td>
                              <td class="px-4 py-3 text-right text-sm font-semibold text-red-600">- Rp
                                 {{ number_format($salesOrder->discount, 0, ',', '.') }}</td>
                           </tr>
                        @endif
                        <tr>
                           <td colspan="3" class="px-4 py-3 text-right text-sm font-medium text-gray-700">{{ __('app.tax_vat') }} 11%:</td>
                           <td class="px-4 py-3 text-right text-sm font-semibold text-gray-900">Rp
                              {{ number_format($salesOrder->tax, 0, ',', '.') }}</td>
                        </tr>
                        <tr class="border-t-2 border-gray-300">
                           <td colspan="3" class="px-4 py-3 text-right text-base font-bold text-gray-900">{{ __('app.total') }}:</td>
                           <td class="px-4 py-3 text-right text-base font-bold text-blue-600">Rp
                              {{ number_format($salesOrder->total, 0, ',', '.') }}</td>
                        </tr>
                        {{-- Transaction Fees & Net Amount --}}
                        @if($salesOrder->transaction_fees > 0)
                           <tr class="bg-yellow-50">
                              <td colspan="3" class="px-4 py-3 text-right text-sm font-medium text-yellow-800">
                                 Transaction Fees
                                 @if($salesOrder->fee_currency_code)
                                    ({{ $salesOrder->fee_currency_code }})
                                 @endif:
                              </td>
                              <td class="px-4 py-3 text-right text-sm font-semibold text-yellow-800">- Rp
                                 {{ number_format($salesOrder->transaction_fees, 0, ',', '.') }}</td>
                           </tr>
                           <tr class="bg-green-50">
                              <td colspan="3" class="px-4 py-3 text-right text-base font-bold text-green-800">{{ __('app.net_amount') }}:</td>
                              <td class="px-4 py-3 text-right text-base font-bold text-green-600">Rp
                                 {{ number_format($salesOrder->net_amount, 0, ',', '.') }}</td>
                           </tr>
                        @endif
                     </tfoot>
                  </table>
               </div>
            </div>

            <!-- Invoice Information -->
            @if ($salesOrder->invoice)
               <div class="bg-white rounded-lg shadow-md p-6">
                  <h2 class="text-lg font-semibold text-gray-900 mb-4">{{ __('app.invoice_information') }}</h2>

                  <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                     <div>
                        <label class="block text-sm font-medium text-gray-500">{{ __('app.invoice_number') }}</label>
                        <p class="mt-1 text-sm text-blue-600 font-semibold">
                           <a href="{{ route('invoices.show', $salesOrder->invoice) }}" class="hover:text-blue-800">
                              {{ $salesOrder->invoice->invoice_number }}
                           </a>
                        </p>
                     </div>

                     <div>
                        <label class="block text-sm font-medium text-gray-500">{{ __('app.invoice_date') }}</label>
                        <p class="mt-1 text-sm text-gray-900">{{ $salesOrder->invoice->invoice_date->format('d M Y') }}
                        </p>
                     </div>

                     <div>
                        <label class="block text-sm font-medium text-gray-500">{{ __('app.due_date') }}</label>
                        <p class="mt-1 text-sm text-gray-900">{{ $salesOrder->invoice->due_date->format('d M Y') }}</p>
                     </div>

                     <div>
                        <label class="block text-sm font-medium text-gray-500">{{ __('app.payment_status') }}</label>
                        <div class="mt-1">
                           @if ($salesOrder->invoice->payment_status === 'unpaid')
                              <span
                                 class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800">{{ __('app.unpaid') }}</span>
                           @elseif($salesOrder->invoice->payment_status === 'partial')
                              <span
                                 class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800">{{ __('app.partial') }}</span>
                           @else
                              <span
                                 class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">{{ __('app.paid') }}</span>
                           @endif
                        </div>
                     </div>
                  </div>
               </div>
            @endif
         </div>

         <!-- Actions Sidebar -->
         <div class="space-y-6">
            <!-- Action Buttons -->
            <div class="bg-white rounded-lg shadow-md p-6">
               <h2 class="text-lg font-semibold text-gray-900 mb-4">{{ __('app.actions') }}</h2>

               <div class="space-y-3">
                  @if ($salesOrder->status === 'draft')
                     <form action="{{ route('sales-orders.confirm', $salesOrder) }}" method="POST"
                        onsubmit="return confirm('{{ __('app.confirm_confirm_order') }}')">
                        @csrf
                        <button type="submit"
                           class="w-full px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white font-semibold rounded-lg transition duration-150">
                           {{ __('app.confirm_order') }}
                        </button>
                     </form>

                     <form action="{{ route('sales-orders.destroy', $salesOrder) }}" method="POST"
                        onsubmit="return confirm('{{ __('app.confirm_delete_order') }}')">
                        @csrf
                        @method('DELETE')
                        <button type="submit"
                           class="w-full px-4 py-2 bg-red-600 hover:bg-red-700 text-white font-semibold rounded-lg transition duration-150">
                           {{ __('app.delete_order') }}
                        </button>
                     </form>
                  @endif

                  @if ($salesOrder->status === 'confirmed')
                     <form action="{{ route('sales-orders.ship', $salesOrder) }}" method="POST"
                        onsubmit="return confirm('{{ __('app.confirm_mark_shipped') }}')">
                        @csrf
                        <button type="submit"
                           class="w-full px-4 py-2 bg-yellow-600 hover:bg-yellow-700 text-white font-semibold rounded-lg transition duration-150">
                           {{ __('app.mark_shipped') }}
                        </button>
                     </form>

                     @if (!$salesOrder->stock_out_id)
                        <form action="{{ route('sales-orders.generate-stock-out', $salesOrder) }}" method="POST"
                           onsubmit="return confirm('{{ __('app.confirm_generate_stock_out') }}')">
                           @csrf
                           <button type="submit"
                              class="w-full px-4 py-2 bg-purple-600 hover:bg-purple-700 text-white font-semibold rounded-lg transition duration-150">
                              {{ __('app.generate_stock_out') }}
                           </button>
                        </form>
                     @endif
                  @endif

                  @if ($salesOrder->status === 'shipped')
                     <form action="{{ route('sales-orders.deliver', $salesOrder) }}" method="POST"
                        onsubmit="return confirm('{{ __('app.confirm_mark_delivered') }}')">
                        @csrf
                        <button type="submit"
                           class="w-full px-4 py-2 bg-green-600 hover:bg-green-700 text-white font-semibold rounded-lg transition duration-150">
                           {{ __('app.mark_delivered') }}
                        </button>
                     </form>
                  @endif

                  @if (in_array($salesOrder->status, ['confirmed', 'shipped']))
                     <a href="{{ route('sales-orders.delivery-order', $salesOrder) }}" target="_blank"
                        class="block w-full px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white font-semibold rounded-lg text-center transition duration-150">
                        {{ __('app.print_delivery_order') }}
                     </a>
                  @endif

                  @if (in_array($salesOrder->status, ['draft', 'confirmed']))
                     <form action="{{ route('sales-orders.cancel', $salesOrder) }}" method="POST"
                        onsubmit="return confirm('{{ __('app.confirm_cancel_order') }}')">
                        @csrf
                        <button type="submit"
                           class="w-full px-4 py-2 bg-gray-600 hover:bg-gray-700 text-white font-semibold rounded-lg transition duration-150">
                           {{ __('app.cancel_order') }}
                        </button>
                     </form>
                  @endif

                  @if ($salesOrder->status === 'delivered' && !$salesOrder->invoice)
                     <a href="{{ route('invoices.create', ['sales_order_id' => $salesOrder->id]) }}"
                        class="block w-full px-4 py-2 bg-teal-600 hover:bg-teal-700 text-white font-semibold rounded-lg text-center transition duration-150">
                        {{ __('app.create_invoice') }}
                     </a>
                  @endif
               </div>
            </div>

            <!-- Audit Info -->
            <div class="bg-white rounded-lg shadow-md p-6">
               <h2 class="text-lg font-semibold text-gray-900 mb-4">{{ __('app.audit_information') }}</h2>

               <div class="space-y-3 text-sm">
                  <div>
                     <label class="block text-xs font-medium text-gray-500">{{ __('app.created_by') }}</label>
                     <p class="mt-1 text-gray-900">{{ $salesOrder->creator->name ?? '-' }}</p>
                     <p class="text-xs text-gray-500">{{ $salesOrder->created_at->format('d M Y H:i') }}</p>
                  </div>

                  @if ($salesOrder->updated_at != $salesOrder->created_at)
                     <div class="pt-3 border-t border-gray-200">
                        <label class="block text-xs font-medium text-gray-500">{{ __('app.updated_by') }}</label>
                        <p class="mt-1 text-gray-900">{{ $salesOrder->updater->name ?? '-' }}</p>
                        <p class="text-xs text-gray-500">{{ $salesOrder->updated_at->format('d M Y H:i') }}</p>
                     </div>
                  @endif
               </div>
            </div>

            <!-- Document Center -->
            <div class="bg-white rounded-lg shadow-md p-6">
               <h2 class="text-lg font-semibold text-gray-900 mb-4 flex items-center gap-2">
                  <svg class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                     <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                  </svg>
                  {{ __('app.document_center') }}
               </h2>
               <p class="text-sm text-gray-500 mb-4">{{ __('app.download_export_docs') }}</p>

               <div class="space-y-2">
                  <a href="{{ route('pdf.invoice', $salesOrder) }}" 
                     class="flex items-center gap-2 w-full px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-lg transition font-medium text-sm">
                     📄 {{ __('app.commercial_invoice') }}
                  </a>
                  
                  @if($salesOrder->stock_out_id)
                  <a href="{{ route('pdf.packing-list', $salesOrder) }}" 
                     class="flex items-center gap-2 w-full px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white rounded-lg transition font-medium text-sm">
                     📦 {{ __('app.packing_list') }}
                  </a>
                  @endif
               </div>
            </div>
         </div>
      </div>
   </div>
@endsection
