@extends('layouts.app')

@section('content')
   <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
      <div class="flex justify-between items-center mb-6">
         <div>
            <h1 class="text-3xl font-bold text-gray-900">{{ __('Supplier Payments') }}</h1>
            <p class="mt-1 text-sm text-gray-600">{{ __('Track payments owed to suppliers') }}</p>
         </div>
         <div class="flex gap-3">
            <a href="{{ route('reconciliation.index') }}" class="px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white font-semibold rounded-lg transition">{{ __('Reconciliation') }}</a>
            <a href="{{ route('supplier-payments.create') }}" class="px-4 py-2 bg-emerald-600 hover:bg-emerald-700 text-white font-semibold rounded-lg transition">+ {{ __('Record Payment') }}</a>
         </div>
      </div>

      <!-- Filters -->
      <div class="bg-white rounded-lg shadow-md p-6 mb-6">
         <form method="GET" action="{{ route('supplier-payments.index') }}" class="flex flex-wrap gap-4 items-end">
            <div class="flex-1 min-w-[200px]">
               <label class="block text-sm font-medium text-gray-700 mb-2">{{ __('Search') }}</label>
               <input type="text" name="search" value="{{ request('search') }}" placeholder="{{ __('Supplier name...') }}"
                  class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-emerald-500 focus:border-transparent">
            </div>
            <div class="w-48">
               <label class="block text-sm font-medium text-gray-700 mb-2">{{ __('Supplier') }}</label>
               <select name="supplier_id" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-emerald-500 focus:border-transparent">
                  <option value="">{{ __('All') }}</option>
                  @foreach ($suppliers as $supplier)
                     <option value="{{ $supplier->id }}" {{ request('supplier_id') == $supplier->id ? 'selected' : '' }}>{{ $supplier->name }}</option>
                  @endforeach
               </select>
            </div>
            <div class="w-36">
               <label class="block text-sm font-medium text-gray-700 mb-2">{{ __('Status') }}</label>
               <select name="payment_status" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-emerald-500 focus:border-transparent">
                  <option value="">{{ __('All') }}</option>
                  <option value="unpaid" {{ request('payment_status') === 'unpaid' ? 'selected' : '' }}>{{ __('Unpaid') }}</option>
                  <option value="partial" {{ request('payment_status') === 'partial' ? 'selected' : '' }}>{{ __('Partial') }}</option>
                  <option value="paid" {{ request('payment_status') === 'paid' ? 'selected' : '' }}>{{ __('Paid') }}</option>
               </select>
            </div>
            <button type="submit" class="px-6 py-2 bg-emerald-600 hover:bg-emerald-700 text-white font-semibold rounded-lg transition">{{ __('Filter') }}</button>
            <a href="{{ route('supplier-payments.index') }}" class="px-6 py-2 bg-gray-200 hover:bg-gray-300 text-gray-700 font-semibold rounded-lg transition">{{ __('Reset') }}</a>
         </form>
      </div>

      <!-- Table -->
      <div class="bg-white rounded-lg shadow-md overflow-hidden">
         <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
               <thead class="bg-gray-50">
                  <tr>
                     <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">{{ __('Supplier') }}</th>
                     <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">{{ __('Stock In') }}</th>
                     <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">{{ __('Due Date') }}</th>
                     <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">{{ __('Owed') }}</th>
                     <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">{{ __('Paid') }}</th>
                     <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase">{{ __('Status') }}</th>
                     <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase">{{ __('Actions') }}</th>
                  </tr>
               </thead>
               <tbody class="divide-y divide-gray-200">
                  @forelse($payments as $payment)
                     <tr class="hover:bg-gray-50">
                        <td class="px-6 py-4 text-sm font-medium text-gray-900">{{ $payment->supplier->name ?? '-' }}</td>
                        <td class="px-6 py-4 text-sm text-gray-700">{{ $payment->stockIn->reference ?? '#' . $payment->stock_in_id }}</td>
                        <td class="px-6 py-4 text-sm {{ $payment->due_date?->isPast() && $payment->payment_status !== 'paid' ? 'text-red-600 font-semibold' : 'text-gray-900' }}">
                           {{ $payment->due_date?->format('d M Y') ?? '-' }}
                           @if($payment->due_date?->isPast() && $payment->payment_status !== 'paid')
                              <div class="text-xs text-red-600">{{ __('Overdue') }}</div>
                           @endif
                        </td>
                        <td class="px-6 py-4 text-sm text-right font-semibold">Rp {{ number_format($payment->amount_owed, 0, ',', '.') }}</td>
                        <td class="px-6 py-4 text-sm text-right text-emerald-700">Rp {{ number_format($payment->amount_paid, 0, ',', '.') }}</td>
                        <td class="px-6 py-4 text-center">
                           @if($payment->payment_status === 'unpaid')
                              <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800">{{ __('Unpaid') }}</span>
                           @elseif($payment->payment_status === 'partial')
                              <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800">{{ __('Partial') }}</span>
                           @else
                              <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">{{ __('Paid') }}</span>
                           @endif
                        </td>
                        <td class="px-6 py-4 text-center">
                           <a href="{{ route('supplier-payments.show', $payment) }}" class="text-blue-600 hover:text-blue-900">
                              <svg class="w-5 h-5 inline" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                           </a>
                        </td>
                     </tr>
                  @empty
                     <tr><td colspan="7" class="px-6 py-8 text-center text-gray-500">{{ __('No supplier payments found.') }}</td></tr>
                  @endforelse
               </tbody>
            </table>
         </div>
         @if ($payments->hasPages())
            <div class="px-6 py-4 border-t">{{ $payments->links() }}</div>
         @endif
      </div>
   </div>
@endsection
