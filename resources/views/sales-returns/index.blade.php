@extends('layouts.app')

@section('content')
   <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
      <div class="flex justify-between items-center mb-6">
         <div>
            <h1 class="text-3xl font-bold text-gray-900">{{ __('Sales Returns') }}</h1>
            <p class="mt-1 text-sm text-gray-600">{{ __('Manage product returns and credit notes') }}</p>
         </div>
         <a href="{{ route('sales-returns.create') }}"
            class="px-4 py-2 bg-emerald-600 hover:bg-emerald-700 text-white font-semibold rounded-lg transition">
            + {{ __('New Return') }}
         </a>
      </div>

      <!-- Filters -->
      <div class="bg-white rounded-lg shadow-md p-6 mb-6">
         <form method="GET" action="{{ route('sales-returns.index') }}" class="flex flex-wrap gap-4 items-end">
            <div class="flex-1 min-w-[200px]">
               <label class="block text-sm font-medium text-gray-700 mb-2">{{ __('Search') }}</label>
               <input type="text" name="search" value="{{ request('search') }}" placeholder="{{ __('Return #, SO #...') }}"
                  class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-emerald-500 focus:border-transparent">
            </div>
            <div class="w-40">
               <label class="block text-sm font-medium text-gray-700 mb-2">{{ __('Status') }}</label>
               <select name="status" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-emerald-500 focus:border-transparent">
                  <option value="">{{ __('All') }}</option>
                  <option value="pending" {{ request('status') === 'pending' ? 'selected' : '' }}>{{ __('Pending') }}</option>
                  <option value="approved" {{ request('status') === 'approved' ? 'selected' : '' }}>{{ __('Approved') }}</option>
                  <option value="processed" {{ request('status') === 'processed' ? 'selected' : '' }}>{{ __('Processed') }}</option>
               </select>
            </div>
            <button type="submit" class="px-6 py-2 bg-emerald-600 hover:bg-emerald-700 text-white font-semibold rounded-lg transition">{{ __('Filter') }}</button>
            <a href="{{ route('sales-returns.index') }}" class="px-6 py-2 bg-gray-200 hover:bg-gray-300 text-gray-700 font-semibold rounded-lg transition">{{ __('Reset') }}</a>
         </form>
      </div>

      <!-- Table -->
      <div class="bg-white rounded-lg shadow-md overflow-hidden">
         <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
               <thead class="bg-gray-50">
                  <tr>
                     <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">{{ __('Return #') }}</th>
                     <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">{{ __('Sales Order') }}</th>
                     <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">{{ __('Customer') }}</th>
                     <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">{{ __('Date') }}</th>
                     <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">{{ __('Credit Amount') }}</th>
                     <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase">{{ __('Status') }}</th>
                     <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase">{{ __('Actions') }}</th>
                  </tr>
               </thead>
               <tbody class="divide-y divide-gray-200">
                  @forelse($returns as $return)
                     <tr class="hover:bg-gray-50">
                        <td class="px-6 py-4 text-sm">
                           <a href="{{ route('sales-returns.show', $return) }}" class="text-emerald-600 hover:text-emerald-900 font-medium">{{ $return->return_number }}</a>
                        </td>
                        <td class="px-6 py-4 text-sm">
                           <a href="{{ route('sales-orders.show', $return->salesOrder) }}" class="text-blue-600 hover:text-blue-900">{{ $return->salesOrder->so_number }}</a>
                        </td>
                        <td class="px-6 py-4 text-sm text-gray-900">{{ $return->salesOrder->customer->name ?? '-' }}</td>
                        <td class="px-6 py-4 text-sm text-gray-900">{{ $return->return_date->format('d M Y') }}</td>
                        <td class="px-6 py-4 text-sm text-right font-semibold">Rp {{ number_format($return->credit_amount, 0, ',', '.') }}</td>
                        <td class="px-6 py-4 text-center">
                           @if($return->status === 'pending')
                              <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800">{{ __('Pending') }}</span>
                           @elseif($return->status === 'approved')
                              <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800">{{ __('Approved') }}</span>
                           @else
                              <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">{{ __('Processed') }}</span>
                           @endif
                        </td>
                        <td class="px-6 py-4 text-center">
                           <a href="{{ route('sales-returns.show', $return) }}" class="text-blue-600 hover:text-blue-900">
                              <svg class="w-5 h-5 inline" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                           </a>
                        </td>
                     </tr>
                  @empty
                     <tr><td colspan="7" class="px-6 py-8 text-center text-gray-500">{{ __('No sales returns found.') }}</td></tr>
                  @endforelse
               </tbody>
            </table>
         </div>
         @if ($returns->hasPages())
            <div class="px-6 py-4 border-t">{{ $returns->links() }}</div>
         @endif
      </div>
   </div>
@endsection
