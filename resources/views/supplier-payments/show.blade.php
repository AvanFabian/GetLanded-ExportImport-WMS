@extends('layouts.app')

@section('content')
   <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
      <div class="flex justify-between items-center mb-6">
         <div>
            <h1 class="text-3xl font-bold text-gray-900">{{ __('Supplier Payment Detail') }}</h1>
            <p class="mt-1 text-sm text-gray-600">{{ $supplierPayment->supplier->name ?? '-' }}</p>
         </div>
         <a href="{{ route('supplier-payments.index') }}" class="px-4 py-2 bg-gray-200 hover:bg-gray-300 text-gray-700 font-semibold rounded-lg transition">← {{ __('Back') }}</a>
      </div>

      <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
         <div class="bg-white rounded-lg shadow-md p-6">
            <h3 class="text-sm font-medium text-gray-500 uppercase mb-3">{{ __('Payment Info') }}</h3>
            <dl class="space-y-3">
               <div class="flex justify-between"><dt class="text-sm text-gray-600">{{ __('Supplier') }}</dt><dd class="text-sm font-medium text-gray-900">{{ $supplierPayment->supplier->name ?? '-' }}</dd></div>
               <div class="flex justify-between"><dt class="text-sm text-gray-600">{{ __('Stock In') }}</dt><dd class="text-sm font-medium text-gray-900">{{ $supplierPayment->stockIn->reference ?? '#' . $supplierPayment->stock_in_id }}</dd></div>
               @if($supplierPayment->stockIn->warehouse ?? false)
                  <div class="flex justify-between"><dt class="text-sm text-gray-600">{{ __('Warehouse') }}</dt><dd class="text-sm font-medium text-gray-900">{{ $supplierPayment->stockIn->warehouse->name }}</dd></div>
               @endif
               <div class="flex justify-between"><dt class="text-sm text-gray-600">{{ __('Due Date') }}</dt>
                  <dd class="text-sm font-medium {{ $supplierPayment->due_date?->isPast() && $supplierPayment->payment_status !== 'paid' ? 'text-red-600' : 'text-gray-900' }}">
                     {{ $supplierPayment->due_date?->format('d M Y') ?? '-' }}
                  </dd></div>
               <div class="flex justify-between"><dt class="text-sm text-gray-600">{{ __('Payment Method') }}</dt>
                  <dd class="text-sm font-medium text-gray-900">{{ \App\Models\SupplierPayment::PAYMENT_METHODS[$supplierPayment->payment_method] ?? ucfirst($supplierPayment->payment_method ?? 'bank_transfer') }}</dd></div>
               @if($supplierPayment->bank_reference)
                  <div class="flex justify-between"><dt class="text-sm text-gray-600">{{ __('Bank Reference') }}</dt><dd class="text-sm font-mono text-gray-900">{{ $supplierPayment->bank_reference }}</dd></div>
               @endif
            </dl>
         </div>
         <div class="bg-white rounded-lg shadow-md p-6">
            <h3 class="text-sm font-medium text-gray-500 uppercase mb-3">{{ __('Amounts') }}</h3>
            <dl class="space-y-3">
               <div class="flex justify-between"><dt class="text-sm text-gray-600">{{ __('Currency') }}</dt><dd class="text-sm font-medium text-gray-900">{{ $supplierPayment->currency_code ?? 'IDR' }}</dd></div>
               <div class="flex justify-between"><dt class="text-sm text-gray-600">{{ __('Amount Owed') }}</dt>
                  <dd class="text-lg font-bold text-red-700">{{ $supplierPayment->currency_code ?? 'IDR' }} {{ number_format($supplierPayment->amount_owed, 2) }}</dd></div>
               <div class="flex justify-between"><dt class="text-sm text-gray-600">{{ __('Amount Paid') }}</dt>
                  <dd class="text-lg font-bold text-emerald-700">{{ $supplierPayment->currency_code ?? 'IDR' }} {{ number_format($supplierPayment->amount_paid, 2) }}</dd></div>
               <div class="flex justify-between"><dt class="text-sm text-gray-600">{{ __('Outstanding') }}</dt>
                  <dd class="text-lg font-bold text-gray-900">{{ $supplierPayment->currency_code ?? 'IDR' }} {{ number_format($supplierPayment->amount_owed - $supplierPayment->amount_paid, 2) }}</dd></div>
               <div class="flex justify-between"><dt class="text-sm text-gray-600">{{ __('Status') }}</dt>
                  <dd>
                     @if($supplierPayment->payment_status === 'unpaid')
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800">{{ __('Unpaid') }}</span>
                     @elseif($supplierPayment->payment_status === 'partial')
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800">{{ __('Partial') }}</span>
                     @else
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">{{ __('Paid') }}</span>
                     @endif
                  </dd></div>
            </dl>
         </div>
      </div>

      @if($supplierPayment->payment_method === 'letter_of_credit')
         <div class="bg-blue-50 rounded-lg shadow-md p-6 mt-6">
            <h3 class="text-sm font-medium text-blue-800 uppercase mb-3">{{ __('Letter of Credit Details') }}</h3>
            <dl class="grid grid-cols-3 gap-4">
               <div><dt class="text-xs text-blue-600">{{ __('L/C Number') }}</dt><dd class="text-sm font-bold text-gray-900">{{ $supplierPayment->lc_number ?? '-' }}</dd></div>
               <div><dt class="text-xs text-blue-600">{{ __('Expiry Date') }}</dt><dd class="text-sm font-medium text-gray-900">{{ $supplierPayment->lc_expiry_date?->format('d M Y') ?? '-' }}</dd></div>
               <div><dt class="text-xs text-blue-600">{{ __('Issuing Bank') }}</dt><dd class="text-sm font-medium text-gray-900">{{ $supplierPayment->lc_issuing_bank ?? '-' }}</dd></div>
            </dl>
         </div>
      @endif

      @if($supplierPayment->payment_notes)
         <div class="bg-white rounded-lg shadow-md p-6 mt-6">
            <h3 class="text-sm font-medium text-gray-500 uppercase mb-2">{{ __('Notes') }}</h3>
            <p class="text-sm text-gray-700">{{ $supplierPayment->payment_notes }}</p>
         </div>
      @endif
   </div>
@endsection
