@extends('layouts.app')

@section('content')
   <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
      <div class="flex justify-between items-center mb-6">
         <div>
            <h1 class="text-3xl font-bold text-gray-900">{{ __('Claims') }}</h1>
            <p class="mt-1 text-sm text-gray-600">{{ __('Manage damage, shortage, and delay claims') }}</p>
         </div>
         <a href="{{ route('claims.create') }}" class="px-4 py-2 bg-emerald-600 hover:bg-emerald-700 text-white font-semibold rounded-lg transition">+ {{ __('New Claim') }}</a>
      </div>

      <!-- Filters -->
      <div class="bg-white rounded-lg shadow-md p-6 mb-6">
         <form method="GET" action="{{ route('claims.index') }}" class="flex flex-wrap gap-4 items-end">
            <div class="flex-1 min-w-[200px]">
               <label class="block text-sm font-medium text-gray-700 mb-2">{{ __('Search') }}</label>
               <input type="text" name="search" value="{{ request('search') }}" placeholder="{{ __('Description, SO #...') }}"
                  class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-emerald-500 focus:border-transparent">
            </div>
            <div class="w-36">
               <label class="block text-sm font-medium text-gray-700 mb-2">{{ __('Type') }}</label>
               <select name="claim_type" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-emerald-500 focus:border-transparent">
                  <option value="">{{ __('All') }}</option>
                  <option value="damage" {{ request('claim_type') === 'damage' ? 'selected' : '' }}>{{ __('Damage') }}</option>
                  <option value="shortage" {{ request('claim_type') === 'shortage' ? 'selected' : '' }}>{{ __('Shortage') }}</option>
                  <option value="delay" {{ request('claim_type') === 'delay' ? 'selected' : '' }}>{{ __('Delay') }}</option>
               </select>
            </div>
            <div class="w-36">
               <label class="block text-sm font-medium text-gray-700 mb-2">{{ __('Status') }}</label>
               <select name="status" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-emerald-500 focus:border-transparent">
                  <option value="">{{ __('All') }}</option>
                  <option value="open" {{ request('status') === 'open' ? 'selected' : '' }}>{{ __('Open') }}</option>
                  <option value="submitted" {{ request('status') === 'submitted' ? 'selected' : '' }}>{{ __('Submitted') }}</option>
                  <option value="settled" {{ request('status') === 'settled' ? 'selected' : '' }}>{{ __('Settled') }}</option>
                  <option value="rejected" {{ request('status') === 'rejected' ? 'selected' : '' }}>{{ __('Rejected') }}</option>
               </select>
            </div>
            <button type="submit" class="px-6 py-2 bg-emerald-600 hover:bg-emerald-700 text-white font-semibold rounded-lg transition">{{ __('Filter') }}</button>
            <a href="{{ route('claims.index') }}" class="px-6 py-2 bg-gray-200 hover:bg-gray-300 text-gray-700 font-semibold rounded-lg transition">{{ __('Reset') }}</a>
         </form>
      </div>

      <!-- Table -->
      <div class="bg-white rounded-lg shadow-md overflow-hidden">
         <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
               <thead class="bg-gray-50">
                  <tr>
                     <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">{{ __('ID') }}</th>
                     <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">{{ __('Sales Order') }}</th>
                     <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">{{ __('Type') }}</th>
                     <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">{{ __('Claimed') }}</th>
                     <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">{{ __('Settled') }}</th>
                     <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase">{{ __('Status') }}</th>
                     <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase">{{ __('Actions') }}</th>
                  </tr>
               </thead>
               <tbody class="divide-y divide-gray-200">
                  @forelse($claims as $claim)
                     <tr class="hover:bg-gray-50">
                        <td class="px-6 py-4 text-sm">
                           <a href="{{ route('claims.show', $claim) }}" class="text-emerald-600 hover:text-emerald-900 font-medium">#{{ $claim->id }}</a>
                        </td>
                        <td class="px-6 py-4 text-sm">
                           <a href="{{ route('sales-orders.show', $claim->salesOrder) }}" class="text-blue-600 hover:text-blue-900">{{ $claim->salesOrder->so_number }}</a>
                           <div class="text-xs text-gray-500">{{ $claim->salesOrder->customer->name ?? '' }}</div>
                        </td>
                        <td class="px-6 py-4 text-sm">
                           <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium
                              {{ $claim->claim_type === 'damage' ? 'bg-red-100 text-red-800' : ($claim->claim_type === 'shortage' ? 'bg-orange-100 text-orange-800' : 'bg-blue-100 text-blue-800') }}">
                              {{ ucfirst($claim->claim_type) }}
                           </span>
                        </td>
                        <td class="px-6 py-4 text-sm text-right font-semibold">Rp {{ number_format($claim->claimed_amount, 0, ',', '.') }}</td>
                        <td class="px-6 py-4 text-sm text-right {{ $claim->settled_amount ? 'text-emerald-700 font-semibold' : 'text-gray-400' }}">
                           {{ $claim->settled_amount ? 'Rp ' . number_format($claim->settled_amount, 0, ',', '.') : '-' }}
                        </td>
                        <td class="px-6 py-4 text-center">
                           @php $colors = ['open' => 'yellow', 'submitted' => 'blue', 'settled' => 'green', 'rejected' => 'red']; @endphp
                           <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-{{ $colors[$claim->status] ?? 'gray' }}-100 text-{{ $colors[$claim->status] ?? 'gray' }}-800">
                              {{ ucfirst($claim->status) }}
                           </span>
                        </td>
                        <td class="px-6 py-4 text-center">
                           <a href="{{ route('claims.show', $claim) }}" class="text-blue-600 hover:text-blue-900">
                              <svg class="w-5 h-5 inline" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                           </a>
                        </td>
                     </tr>
                  @empty
                     <tr><td colspan="7" class="px-6 py-8 text-center text-gray-500">{{ __('No claims found.') }}</td></tr>
                  @endforelse
               </tbody>
            </table>
         </div>
         @if ($claims->hasPages())
            <div class="px-6 py-4 border-t">{{ $claims->links() }}</div>
         @endif
      </div>
   </div>
@endsection
