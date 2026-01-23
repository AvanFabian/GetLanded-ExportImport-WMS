@extends('layouts.app')

@section('content')
   <div class="max-w-7xl mx-auto p-6">
      <div class="mb-6">
         <h1 class="text-3xl font-bold text-gray-800">{{ __('app.receive_goods_from_po') }}</h1>
         <p class="text-gray-600 mt-1">{{ $purchaseOrder->po_number }}</p>
      </div>

      <form action="{{ route('purchase-orders.process-receive', $purchaseOrder) }}" method="POST" id="receiveForm">
         @csrf

         <div class="flex flex-col md:flex-row gap-6">
            <div class="md:w-2/3">
               <div class="bg-white rounded shadow mb-6">
                  <div class="px-6 py-4 border-b border-gray-200">
                     <h3 class="text-lg font-semibold text-gray-800">{{ __('app.receiving_information') }}</h3>
                  </div>
                  <div class="p-6">
                     @if (session('error'))
                        <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4">
                           {{ session('error') }}
                        </div>
                     @endif

                     <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
                        <div>
                           <span class="font-semibold">{{ __('app.po_number') }}:</span> {{ $purchaseOrder->po_number }}
                        </div>
                        <div>
                           <span class="font-semibold">{{ __('app.supplier') }}:</span> {{ $purchaseOrder->supplier->name }}
                        </div>
                     </div>

                     <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                           <label class="block text-sm font-medium text-gray-700 mb-2">
                              {{ __('app.received_date') }} <span class="text-red-600">*</span>
                           </label>
                           <input type="date" name="received_date"
                              class="w-full border rounded px-3 py-2 @error('received_date') border-red-500 @enderror"
                              value="{{ old('received_date', date('Y-m-d')) }}" required>
                           @error('received_date')
                              <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                           @enderror
                        </div>
                        <div>
                           <label class="block text-sm font-medium text-gray-700 mb-2">{{ __('app.receiving_notes') }}</label>
                           <input type="text" name="notes"
                              class="w-full border rounded px-3 py-2 @error('notes') border-red-500 @enderror"
                              value="{{ old('notes') }}" placeholder="{{ __('app.notes') }}...">
                           @error('notes')
                              <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                           @enderror
                        </div>
                     </div>
                  </div>
               </div>

               <div class="bg-white rounded shadow mb-6">
                  <div class="px-6 py-4 border-b border-gray-200">
                     <h3 class="text-lg font-semibold text-gray-800">{{ __('app.received_items_details') }}</h3>
                  </div>
                  <div class="p-6">
                     <div class="bg-blue-100 border border-blue-400 text-blue-700 px-4 py-3 rounded mb-4 flex items-start">
                        <svg class="w-5 h-5 mr-2 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
                           <path fill-rule="evenodd"
                              d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z"
                              clip-rule="evenodd" />
                        </svg>
                        <span>{{ __('app.enter_received_quantity_info') }}</span>
                     </div>

                     <div class="overflow-x-auto">
                        <table class="min-w-full border">
                           <thead class="bg-gray-50">
                              <tr>
                                 <th class="text-left p-3 font-semibold border-b">{{ __('app.product') }}</th>
                                 <th class="text-center p-3 font-semibold border-b w-24">{{ __('app.ordered') }}</th>
                                 <th class="text-center p-3 font-semibold border-b w-24">{{ __('app.already_received') }}</th>
                                 <th class="text-center p-3 font-semibold border-b w-24">{{ __('app.remaining') }}</th>
                                 <th class="text-center p-3 font-semibold border-b w-32">{{ __('app.receive_amount') }}</th>
                              </tr>
                           </thead>
                           <tbody>
                              @foreach ($purchaseOrder->details as $detail)
                                 @php
                                    $remaining = $detail->getRemainingQuantity();
                                 @endphp
                                 @if ($remaining > 0)
                                    <tr class="border-t">
                                       <td class="p-3">
                                          <p class="font-medium">{{ $detail->product->name }}</p>
                                          <p class="text-sm text-gray-500">{{ $detail->product->code }}</p>
                                          <p class="text-sm text-gray-500">{{ __('app.price') }}: Rp
                                             {{ number_format($detail->unit_price, 0, ',', '.') }}</p>
                                       </td>
                                       <td class="p-3 text-center">
                                          <span
                                             class="px-2 py-1 text-xs rounded bg-gray-100 text-gray-700">{{ $detail->quantity_ordered }}</span>
                                       </td>
                                       <td class="p-3 text-center">
                                          <span
                                             class="px-2 py-1 text-xs rounded bg-blue-100 text-blue-700">{{ $detail->quantity_received }}</span>
                                       </td>
                                       <td class="p-3 text-center">
                                          <span
                                             class="px-2 py-1 text-xs rounded bg-yellow-100 text-yellow-700">{{ $remaining }}</span>
                                       </td>
                                       <td class="p-3">
                                          <input type="hidden" name="items[{{ $loop->index }}][detail_id]"
                                             value="{{ $detail->id }}">
                                          <input type="number" name="items[{{ $loop->index }}][quantity_received]"
                                             class="w-full border rounded px-3 py-2 receive-qty @error('items.' . $loop->index . '.quantity_received') border-red-500 @enderror"
                                             min="0" max="{{ $remaining }}"
                                             value="{{ old('items.' . $loop->index . '.quantity_received', $remaining) }}"
                                             data-remaining="{{ $remaining }}" placeholder="0">
                                          @error('items.' . $loop->index . '.quantity_received')
                                             <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                                          @enderror
                                       </td>
                                    </tr>
                                 @endif
                              @endforeach
                           </tbody>
                        </table>
                     </div>
                  </div>
               </div>

               <div class="flex gap-3">
                  <button type="submit"
                     class="bg-blue-600 hover:bg-blue-700 text-white font-medium py-2 px-6 rounded inline-flex items-center"
                     id="submitBtn">
                     <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                     </svg>
                     {{ __('app.process_receive') }}
                  </button>
                  <a href="{{ route('purchase-orders.show', $purchaseOrder) }}"
                     class="bg-gray-600 hover:bg-gray-700 text-white font-medium py-2 px-6 rounded inline-flex items-center">
                     <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                           d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                     </svg>
                     {{ __('app.back') }}
                  </a>
               </div>
            </div>

            <div class="md:w-1/3">
               <div class="bg-white rounded shadow mb-6">
                  <div class="px-6 py-4 border-b border-gray-200">
                     <h3 class="text-lg font-semibold text-gray-800">{{ __('app.receiving_summary') }}</h3>
                  </div>
                  <div class="p-6">
                     <div class="mb-4">
                        <p class="text-sm text-gray-600 mb-1">{{ __('app.destination_warehouse') }}</p>
                        <p class="text-xl font-semibold">{{ $purchaseOrder->warehouse->name }}</p>
                     </div>

                     <div class="border-t border-gray-200 my-4"></div>

                     <div class="mb-4">
                        <p class="text-sm text-gray-600 mb-1">{{ __('app.total_items_remaining') }}</p>
                        <p class="text-2xl font-bold" id="totalItems">
                           {{ $purchaseOrder->details->filter(fn($d) => $d->getRemainingQuantity() > 0)->count() }} {{ __('app.products') }}
                        </p>
                     </div>

                     <div class="mb-4">
                        <p class="text-sm text-gray-600 mb-1">{{ __('app.total_to_receive') }}</p>
                        <p class="text-2xl font-bold text-green-600" id="totalReceiving">0 {{ __('app.unit') }}</p>
                     </div>

                     <div class="border-t border-gray-200 my-4"></div>

                     <div
                        class="bg-yellow-100 border border-yellow-400 text-yellow-800 px-4 py-3 rounded flex items-start">
                        <svg class="w-5 h-5 mr-2 mt-0.5 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                           <path fill-rule="evenodd"
                              d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z"
                              clip-rule="evenodd" />
                        </svg>
                        <div>
                           <p class="font-semibold">{{ __('app.attention') }}:</p>
                           <p class="text-sm">{{ __('app.receive_attention_message') }}</p>
                        </div>
                     </div>
                  </div>
               </div>

               <div class="bg-white rounded shadow">
                  <div class="px-6 py-4 border-b border-gray-200">
                     <h3 class="text-lg font-semibold text-gray-800">{{ __('app.tips') }}</h3>
                  </div>
                  <div class="p-6">
                     <ul class="space-y-2 text-gray-700">
                        <li class="flex items-start">
                           <svg class="w-5 h-5 mr-2 text-blue-600 mt-0.5 flex-shrink-0" fill="currentColor"
                              viewBox="0 0 20 20">
                              <path fill-rule="evenodd"
                                 d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z"
                                 clip-rule="evenodd" />
                           </svg>
                           <span>{{ __('app.tip_check_condition') }}</span>
                        </li>
                        <li class="flex items-start">
                           <svg class="w-5 h-5 mr-2 text-blue-600 mt-0.5 flex-shrink-0" fill="currentColor"
                              viewBox="0 0 20 20">
                              <path fill-rule="evenodd"
                                 d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z"
                                 clip-rule="evenodd" />
                           </svg>
                           <span>{{ __('app.tip_receive_partial') }}</span>
                        </li>
                        <li class="flex items-start">
                           <svg class="w-5 h-5 mr-2 text-blue-600 mt-0.5 flex-shrink-0" fill="currentColor"
                              viewBox="0 0 20 20">
                              <path fill-rule="evenodd"
                                 d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z"
                                 clip-rule="evenodd" />
                           </svg>
                           <span>{{ __('app.tip_verify_count') }}</span>
                        </li>
                        <li class="flex items-start">
                           <svg class="w-5 h-5 mr-2 text-blue-600 mt-0.5 flex-shrink-0" fill="currentColor"
                              viewBox="0 0 20 20">
                              <path fill-rule="evenodd"
                                 d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z"
                                 clip-rule="evenodd" />
                           </svg>
                           <span>{{ __('app.tip_auto_status') }}</span>
                        </li>
                     </ul>
                  </div>
               </div>
            </div>
         </div>
      </form>
   </div>
@endsection

@push('scripts')
   <script>
      document.addEventListener('DOMContentLoaded', function() {
         calculateTotal();

         // Calculate total when quantity changes
         const receiveQtyInputs = document.querySelectorAll('.receive-qty');
         receiveQtyInputs.forEach(input => {
            input.addEventListener('input', function() {
               let value = parseInt(this.value) || 0;
               let max = parseInt(this.getAttribute('max'));

               if (value > max) {
                  this.value = max;
               }

               calculateTotal();
            });
         });

         // Form validation
         const receiveForm = document.getElementById('receiveForm');
         receiveForm.addEventListener('submit', function(e) {
            let hasQuantity = false;

            receiveQtyInputs.forEach(input => {
               if (parseInt(input.value) > 0) {
                  hasQuantity = true;
               }
            });

            if (!hasQuantity) {
               e.preventDefault();
               alert('Minimal harus ada 1 produk yang diterima');
               return false;
            }

            if (!confirm('Proses penerimaan barang? Stok akan bertambah secara otomatis.')) {
               e.preventDefault();
               return false;
            }
         });
      });

      function calculateTotal() {
         let total = 0;
         let itemCount = 0;

         const receiveQtyInputs = document.querySelectorAll('.receive-qty');
         receiveQtyInputs.forEach(input => {
            let qty = parseInt(input.value) || 0;
            if (qty > 0) {
               total += qty;
               itemCount++;
            }
         });

         document.getElementById('totalReceiving').textContent = total + ' Unit';

         // Enable/disable submit button
         document.getElementById('submitBtn').disabled = total === 0;
      }
   </script>
@endpush
