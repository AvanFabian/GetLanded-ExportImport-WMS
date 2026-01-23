@extends('layouts.app')

@section('content')
   <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
      <!-- Header -->
      <div class="mb-6">
         <h1 class="text-3xl font-bold text-gray-900">{{ __('app.edit_invoice') }}</h1>
         <p class="mt-1 text-sm text-gray-600">{{ $invoice->invoice_number }}</p>
      </div>

      <!-- Error Messages -->
      @if ($errors->any())
         <div class="mb-6 bg-red-50 border border-red-200 text-red-800 px-4 py-3 rounded-lg">
            <div class="font-semibold mb-2">{{ __('app.errors_found') }}</div>
            <ul class="list-disc list-inside text-sm">
               @foreach ($errors->all() as $error)
                  <li>{{ $error }}</li>
               @endforeach
            </ul>
         </div>
      @endif

      <!-- Form -->
      <form action="{{ route('invoices.update', $invoice) }}" method="POST" class="space-y-6">
         @csrf
         @method('PUT')

         <!-- Invoice Information -->
         <div class="bg-white rounded-lg shadow-md p-6">
            <h2 class="text-lg font-semibold text-gray-900 mb-4">{{ __('app.invoice_information') }}</h2>

            <div class="mb-6 pb-6 border-b border-gray-200">
               <div class="grid grid-cols-2 gap-4 text-sm">
                  <div>
                     <span class="text-gray-600">{{ __('app.invoice_number') }}:</span>
                     <span class="ml-2 font-semibold text-gray-900">{{ $invoice->invoice_number }}</span>
                  </div>
                  <div>
                     <span class="text-gray-600">{{ __('app.sales_order') }}:</span>
                     <a href="{{ route('sales-orders.show', $invoice->salesOrder) }}"
                        class="ml-2 font-semibold text-blue-600 hover:text-blue-800">
                        {{ $invoice->salesOrder->so_number }}
                     </a>
                  </div>
                  <div>
                     <span class="text-gray-600">{{ __('app.customer') }}:</span>
                     <span class="ml-2 font-semibold text-gray-900">{{ $invoice->salesOrder->customer->name }}</span>
                  </div>
                  <div>
                     <span class="text-gray-600">{{ __('app.total') }}:</span>
                     <span class="ml-2 font-semibold text-blue-600">Rp
                        {{ number_format($invoice->total_amount, 0, ',', '.') }}</span>
                  </div>
               </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
               <div>
                  <label for="invoice_date" class="block text-sm font-medium text-gray-700 mb-2">
                     {{ __('app.invoice_date') }} <span class="text-red-500">*</span>
                  </label>
                  <input type="date" id="invoice_date" name="invoice_date"
                     value="{{ old('invoice_date', $invoice->invoice_date->format('Y-m-d')) }}" required
                     class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
               </div>

               <div>
                  <label for="due_date" class="block text-sm font-medium text-gray-700 mb-2">
                     {{ __('app.due_date') }} <span class="text-red-500">*</span>
                  </label>
                  <input type="date" id="due_date" name="due_date"
                     value="{{ old('due_date', $invoice->due_date->format('Y-m-d')) }}" required
                     class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
               </div>
            </div>

            <div class="mt-6">
               <label for="notes" class="block text-sm font-medium text-gray-700 mb-2">
                  {{ __('app.notes') }}
               </label>
               <textarea id="notes" name="notes" rows="3"
                  class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                  placeholder="{{ __('app.invoice_notes_placeholder') }}">{{ old('notes', $invoice->notes) }}</textarea>
            </div>
         </div>

         <!-- Action Buttons -->
         <div class="flex justify-end gap-3">
            <a href="{{ route('invoices.show', $invoice) }}"
               class="px-6 py-2 bg-gray-200 hover:bg-gray-300 text-gray-700 font-semibold rounded-lg transition duration-150">
               {{ __('app.cancel') }}
            </a>
            <button type="submit"
               class="px-6 py-2 bg-blue-600 hover:bg-blue-700 text-white font-semibold rounded-lg transition duration-150">
               {{ __('app.update') }}
            </button>
         </div>
      </form>
   </div>
@endsection
