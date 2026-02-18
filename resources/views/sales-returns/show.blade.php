@extends('layouts.app')

@section('content')
   <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
      <div class="flex justify-between items-center mb-6">
         <div>
            <h1 class="text-3xl font-bold text-gray-900">{{ $salesReturn->return_number }}</h1>
            <p class="mt-1 text-sm text-gray-600">{{ __('Sales Return Detail') }}</p>
         </div>
         <a href="{{ route('sales-returns.index') }}" class="px-4 py-2 bg-gray-200 hover:bg-gray-300 text-gray-700 font-semibold rounded-lg transition">← {{ __('Back') }}</a>
      </div>

      <!-- Info Card -->
      <div class="bg-white rounded-lg shadow-md p-6 mb-6">
         <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <dl class="space-y-2">
               <div class="flex justify-between"><dt class="text-sm text-gray-600">{{ __('Sales Order') }}</dt>
                  <dd class="text-sm font-medium"><a href="{{ route('sales-orders.show', $salesReturn->salesOrder) }}" class="text-blue-600 hover:text-blue-900">{{ $salesReturn->salesOrder->so_number }}</a></dd></div>
               <div class="flex justify-between"><dt class="text-sm text-gray-600">{{ __('Customer') }}</dt><dd class="text-sm font-medium text-gray-900">{{ $salesReturn->salesOrder->customer->name ?? '-' }}</dd></div>
               <div class="flex justify-between"><dt class="text-sm text-gray-600">{{ __('Return Date') }}</dt><dd class="text-sm font-medium text-gray-900">{{ $salesReturn->return_date->format('d M Y') }}</dd></div>
            </dl>
            <dl class="space-y-2">
               <div class="flex justify-between"><dt class="text-sm text-gray-600">{{ __('Status') }}</dt>
                  <dd>
                     @if($salesReturn->status === 'pending')
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800">{{ __('Pending') }}</span>
                     @elseif($salesReturn->status === 'approved')
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800">{{ __('Approved') }}</span>
                     @else
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">{{ __('Processed') }}</span>
                     @endif
                  </dd></div>
               <div class="flex justify-between"><dt class="text-sm text-gray-600">{{ __('Credit Amount') }}</dt>
                  <dd class="text-lg font-bold text-emerald-700">Rp {{ number_format($salesReturn->credit_amount, 0, ',', '.') }}</dd></div>
               @if($salesReturn->approver)
                  <div class="flex justify-between"><dt class="text-sm text-gray-600">{{ __('Approved By') }}</dt><dd class="text-sm font-medium text-gray-900">{{ $salesReturn->approver->name }} ({{ $salesReturn->approved_at?->format('d M Y') }})</dd></div>
               @endif
            </dl>
         </div>
         <div class="mt-4 pt-4 border-t">
            <h3 class="text-sm font-medium text-gray-500 uppercase mb-2">{{ __('Reason') }}</h3>
            <p class="text-sm text-gray-700">{{ $salesReturn->reason }}</p>
         </div>
      </div>

      <!-- Items -->
      <div class="bg-white rounded-lg shadow-md overflow-hidden mb-6">
         <div class="px-6 py-4 bg-gray-50 border-b"><h3 class="text-lg font-bold text-gray-900">{{ __('Return Items') }}</h3></div>
         <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
               <tr>
                  <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">{{ __('Product') }}</th>
                  <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">{{ __('Batch') }}</th>
                  <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">{{ __('Qty') }}</th>
                  <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">{{ __('Unit Price') }}</th>
                  <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">{{ __('Total') }}</th>
               </tr>
            </thead>
            <tbody class="divide-y divide-gray-200">
               @foreach($salesReturn->items as $item)
                  <tr>
                     <td class="px-6 py-4 text-sm text-gray-900">{{ $item->product->name ?? '-' }}</td>
                     <td class="px-6 py-4 text-sm text-gray-600">{{ $item->batch->batch_number ?? '-' }}</td>
                     <td class="px-6 py-4 text-sm text-right">{{ $item->quantity }}</td>
                     <td class="px-6 py-4 text-sm text-right">Rp {{ number_format($item->unit_price, 0, ',', '.') }}</td>
                     <td class="px-6 py-4 text-sm text-right font-semibold">Rp {{ number_format($item->quantity * $item->unit_price, 0, ',', '.') }}</td>
                  </tr>
               @endforeach
            </tbody>
         </table>
      </div>

      <!-- Actions -->
      <div class="flex gap-3 justify-end">
         @if($salesReturn->status === 'pending')
            <form method="POST" action="{{ route('sales-returns.approve', $salesReturn) }}">
               @csrf
               <button type="submit" class="px-6 py-2 bg-blue-600 hover:bg-blue-700 text-white font-semibold rounded-lg transition"
                  onclick="return confirm('{{ __('Approve this return?') }}')">
                  ✓ {{ __('Approve') }}
               </button>
            </form>
         @endif
         @if($salesReturn->status === 'approved')
            <form method="POST" action="{{ route('sales-returns.process', $salesReturn) }}">
               @csrf
               <button type="submit" class="px-6 py-2 bg-emerald-600 hover:bg-emerald-700 text-white font-semibold rounded-lg transition"
                  onclick="return confirm('{{ __('Process this return? Credit note will be applied to the sales order.') }}')">
                  ⚡ {{ __('Process & Apply Credit') }}
               </button>
            </form>
         @endif
      </div>
   </div>
@endsection
