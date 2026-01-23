@extends('layouts.app')

@section('content')
   <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
      <!-- Header -->
      <div class="flex justify-between items-start mb-6">
         <div>
            <h1 class="text-3xl font-bold text-gray-900">{{ __('app.invoice_details') }}</h1>
            <p class="mt-1 text-sm text-gray-600">{{ $invoice->invoice_number }}</p>
         </div>
         <div class="flex gap-2">
            @if ($invoice->payment_status === 'unpaid')
               <a href="{{ route('invoices.edit', $invoice) }}"
                  class="px-4 py-2 bg-yellow-600 hover:bg-yellow-700 text-white font-semibold rounded-lg transition duration-150">
                  {{ __('app.edit') }}
               </a>
            @endif
            <a href="{{ route('invoices.index') }}"
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
            <!-- Invoice Details Card -->
            <div class="bg-white rounded-lg shadow-md p-6">
               <h2 class="text-lg font-semibold text-gray-900 mb-4">{{ __('app.invoice_information') }}</h2>

               <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                  <div>
                     <label class="block text-sm font-medium text-gray-500">{{ __('app.invoice_number') }}</label>
                     <p class="mt-1 text-sm text-gray-900 font-semibold">{{ $invoice->invoice_number }}</p>
                  </div>

                  <div>
                     <label class="block text-sm font-medium text-gray-500">{{ __('app.payment_status') }}</label>
                     <div class="mt-1">
                        @if ($invoice->payment_status === 'unpaid')
                           <span
                              class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800">{{ __('app.payment_status_unpaid') }}</span>
                        @elseif($invoice->payment_status === 'partial')
                           <span
                              class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800">{{ __('app.payment_status_partial') }}</span>
                        @else
                           <span
                              class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">{{ __('app.payment_status_paid') }}</span>
                        @endif
                     </div>
                  </div>

                  <div>
                     <label class="block text-sm font-medium text-gray-500">{{ __('app.sales_order') }}</label>
                     <p class="mt-1 text-sm">
                        <a href="{{ route('sales-orders.show', $invoice->salesOrder) }}"
                           class="text-blue-600 hover:text-blue-800 font-medium">
                           {{ $invoice->salesOrder->so_number }}
                        </a>
                     </p>
                  </div>

                  <div>
                     <label class="block text-sm font-medium text-gray-500">{{ __('app.customer') }}</label>
                     <p class="mt-1 text-sm">
                        <a href="{{ route('customers.show', $invoice->salesOrder->customer) }}"
                           class="text-blue-600 hover:text-blue-800 font-medium">
                           {{ $invoice->salesOrder->customer->name }}
                        </a>
                     </p>
                  </div>

                  <div>
                     <label class="block text-sm font-medium text-gray-500">{{ __('app.invoice_date') }}</label>
                     <p class="mt-1 text-sm text-gray-900">{{ $invoice->invoice_date->format('d M Y') }}</p>
                  </div>

                  <div>
                     <label class="block text-sm font-medium text-gray-500">{{ __('app.due_date') }}</label>
                     <p
                        class="mt-1 text-sm {{ $invoice->due_date->isPast() && $invoice->payment_status !== 'paid' ? 'text-red-600 font-semibold' : 'text-gray-900' }}">
                        {{ $invoice->due_date->format('d M Y') }}
                        @if ($invoice->due_date->isPast() && $invoice->payment_status !== 'paid')
                           <span class="text-xs">({{ __('app.overdue') }})</span>
                        @endif
                     </p>
                  </div>
               </div>

               @if ($invoice->notes)
                  <div class="mt-4 pt-4 border-t border-gray-200">
                     <label class="block text-sm font-medium text-gray-500">{{ __('app.notes') }}</label>
                     <p class="mt-1 text-sm text-gray-900">{{ $invoice->notes }}</p>
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
                           <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase">{{ __('app.quantity') }}</th>
                           <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase">{{ __('app.unit_price') }}</th>
                           <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase">{{ __('app.subtotal') }}</th>
                        </tr>
                     </thead>
                     <tbody class="bg-white divide-y divide-gray-200">
                        @foreach ($invoice->salesOrder->items as $item)
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
                        <tr>
                           <td colspan="3" class="px-4 py-3 text-right text-sm font-medium text-gray-700">{{ __('app.subtotal') }}:</td>
                           <td class="px-4 py-3 text-right text-sm font-semibold text-gray-900">Rp
                              {{ number_format($invoice->salesOrder->subtotal, 0, ',', '.') }}</td>
                        </tr>
                        @if ($invoice->salesOrder->discount > 0)
                           <tr>
                              <td colspan="3" class="px-4 py-3 text-right text-sm font-medium text-gray-700">{{ __('app.discount') }}:
                              </td>
                              <td class="px-4 py-3 text-right text-sm font-semibold text-red-600">- Rp
                                 {{ number_format($invoice->salesOrder->discount, 0, ',', '.') }}</td>
                           </tr>
                        @endif
                        <tr>
                           <td colspan="3" class="px-4 py-3 text-right text-sm font-medium text-gray-700">{{ __('app.tax_vat') }} 11%:</td>
                           <td class="px-4 py-3 text-right text-sm font-semibold text-gray-900">Rp
                              {{ number_format($invoice->salesOrder->tax, 0, ',', '.') }}</td>
                        </tr>
                        <tr class="border-t-2 border-gray-300">
                           <td colspan="3" class="px-4 py-3 text-right text-base font-bold text-gray-900">{{ __('app.total') }}:</td>
                           <td class="px-4 py-3 text-right text-base font-bold text-blue-600">Rp
                              {{ number_format($invoice->total_amount, 0, ',', '.') }}</td>
                        </tr>
                     </tfoot>
                  </table>
               </div>
            </div>

            <!-- Payment History -->
            @if ($invoice->payment_notes)
               <div class="bg-white rounded-lg shadow-md p-6">
                  <h2 class="text-lg font-semibold text-gray-900 mb-4">{{ __('app.payment_history') }}</h2>

                  <div class="space-y-2 text-sm">
                     @foreach (explode("\n", $invoice->payment_notes) as $note)
                        @if (trim($note))
                           <div class="flex items-start gap-2 py-2 border-b border-gray-100 last:border-0">
                              <svg class="w-5 h-5 text-green-600 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor"
                                 viewBox="0 0 24 24">
                                 <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                              </svg>
                              <span class="text-gray-900">{{ $note }}</span>
                           </div>
                        @endif
                     @endforeach
                  </div>
               </div>
            @endif
         </div>

         <!-- Sidebar -->
         <div class="space-y-6">
            <!-- Payment Summary -->
            <div class="bg-white rounded-lg shadow-md p-6">
               <h2 class="text-lg font-semibold text-gray-900 mb-4">{{ __('app.payment_summary') }}</h2>

               <div class="space-y-3">
                  <div class="flex justify-between text-sm">
                     <span class="text-gray-600">{{ __('app.total_invoice') }}:</span>
                     <span class="font-semibold text-gray-900">Rp
                        {{ number_format($invoice->total_amount, 0, ',', '.') }}</span>
                  </div>
                  <div class="flex justify-between text-sm">
                     <span class="text-gray-600">{{ __('app.paid_amount') }}:</span>
                     <span class="font-semibold text-green-600">Rp
                        {{ number_format($invoice->paid_amount, 0, ',', '.') }}</span>
                  </div>
                  <div class="flex justify-between text-base font-bold border-t pt-3">
                     <span class="text-gray-900">{{ __('app.remaining') }}:</span>
                     <span class="text-red-600">Rp {{ number_format($invoice->remaining_amount, 0, ',', '.') }}</span>
                  </div>
               </div>

               @if ($invoice->payment_date)
                  <div class="mt-4 pt-4 border-t border-gray-200 text-sm">
                     <div class="text-gray-600 mb-1">{{ __('app.last_payment') }}:</div>
                     <div class="font-semibold text-gray-900">{{ $invoice->payment_date->format('d M Y') }}</div>
                     @if ($invoice->payment_method)
                        <div class="text-gray-600 text-xs mt-1">
                           {{ __('app.via') }} {{ ucfirst($invoice->payment_method) }}
                        </div>
                     @endif
                  </div>
               @endif
            </div>

            <!-- Record Payment Form (only if not fully paid) -->
            @if ($invoice->payment_status !== 'paid')
               <div class="bg-blue-50 rounded-lg shadow-md p-6 border border-blue-200">
                  <h2 class="text-lg font-semibold text-gray-900 mb-4">{{ __('app.record_payment') }}</h2>

                  <form action="{{ route('invoices.record-payment', $invoice) }}" method="POST" class="space-y-4">
                     @csrf

                     <div>
                        <label for="amount" class="block text-sm font-medium text-gray-700 mb-2">
                           {{ __('app.payment_amount') }} <span class="text-red-500">*</span>
                        </label>
                        <div class="relative">
                           <span class="absolute left-3 top-2 text-gray-500">Rp</span>
                           <input type="number" id="amount" name="amount" min="1"
                              max="{{ $invoice->remaining_amount }}" step="1" required
                              class="w-full pl-10 pr-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                              placeholder="0">
                        </div>
                        <p class="mt-1 text-xs text-gray-600">{{ __('app.max_amount') }}: Rp
                           {{ number_format($invoice->remaining_amount, 0, ',', '.') }}</p>
                     </div>

                     <div>
                        <label for="payment_date" class="block text-sm font-medium text-gray-700 mb-2">
                           {{ __('app.payment_date') }} <span class="text-red-500">*</span>
                        </label>
                        <input type="date" id="payment_date" name="payment_date" value="{{ date('Y-m-d') }}"
                           required
                           class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                     </div>

                     <div>
                        <label for="payment_method" class="block text-sm font-medium text-gray-700 mb-2">
                           {{ __('app.payment_method') }} <span class="text-red-500">*</span>
                        </label>
                        <select id="payment_method" name="payment_method" required
                           class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                           <option value="">{{ __('app.select_method') }}</option>
                           <option value="cash">{{ __('app.cash') }}</option>
                           <option value="transfer">{{ __('app.bank_transfer') }}</option>
                           <option value="check">{{ __('app.check') }}</option>
                           <option value="other">{{ __('app.other') }}</option>
                        </select>
                     </div>

                     <div>
                        <label for="payment_notes" class="block text-sm font-medium text-gray-700 mb-2">
                           {{ __('app.notes') }}
                        </label>
                        <textarea id="payment_notes" name="payment_notes" rows="2"
                           class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                           placeholder="No. referensi, nama bank, dll"></textarea>
                     </div>

                     <button type="submit"
                        class="w-full px-4 py-2 bg-green-600 hover:bg-green-700 text-white font-semibold rounded-lg transition duration-150">
                        {{ __('app.record_payment') }}
                     </button>
                  </form>
               </div>
            @endif

            <!-- Action Buttons -->
            <div class="bg-white rounded-lg shadow-md p-6">
               <h2 class="text-lg font-semibold text-gray-900 mb-4">{{ __('app.actions') }}</h2>

               <div class="space-y-3">
                  <a href="{{ route('invoices.pdf', $invoice) }}" target="_blank"
                     class="block w-full px-4 py-2 bg-purple-600 hover:bg-purple-700 text-white font-semibold rounded-lg text-center transition duration-150">
                     {{ __('app.view_invoice_pdf') }}
                  </a>

                  <a href="{{ route('invoices.download', $invoice) }}"
                     class="block w-full px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white font-semibold rounded-lg text-center transition duration-150">
                     {{ __('app.download_pdf') }}
                  </a>

                  <a href="{{ route('sales-orders.delivery-order', $invoice->salesOrder) }}" target="_blank"
                     class="block w-full px-4 py-2 bg-teal-600 hover:bg-teal-700 text-white font-semibold rounded-lg text-center transition duration-150">
                     {{ __('app.view_delivery_order') }}
                  </a>

                  @if ($invoice->payment_status === 'unpaid')
                     <form action="{{ route('invoices.destroy', $invoice) }}" method="POST" class="mt-4"
                        onsubmit="return confirm('{{ __('app.confirm_delete_invoice') }}')">
                        @csrf
                        @method('DELETE')
                        <button type="submit"
                           class="w-full px-4 py-2 bg-red-600 hover:bg-red-700 text-white font-semibold rounded-lg transition duration-150">
                           {{ __('app.delete_invoice') }}
                        </button>
                     </form>
                  @endif
               </div>
            </div>

            <!-- Audit Info -->
            <div class="bg-white rounded-lg shadow-md p-6">
               <h2 class="text-lg font-semibold text-gray-900 mb-4">{{ __('app.audit_information') }}</h2>

               <div class="space-y-3 text-sm">
                  <div>
                     <label class="block text-xs font-medium text-gray-500">{{ __('app.created_by') }}</label>
                     <p class="mt-1 text-gray-900">{{ $invoice->creator->name ?? '-' }}</p>
                     <p class="text-xs text-gray-500">{{ $invoice->created_at->format('d M Y H:i') }}</p>
                  </div>

                  @if ($invoice->updated_at != $invoice->created_at)
                     <div class="pt-3 border-t border-gray-200">
                        <label class="block text-xs font-medium text-gray-500">{{ __('app.updated_by') }}</label>
                        <p class="mt-1 text-gray-900">{{ $invoice->updater->name ?? '-' }}</p>
                        <p class="text-xs text-gray-500">{{ $invoice->updated_at->format('d M Y H:i') }}</p>
                     </div>
                  @endif
               </div>
            </div>
         </div>
      </div>
   </div>
@endsection
