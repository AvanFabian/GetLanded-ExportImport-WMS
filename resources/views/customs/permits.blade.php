@extends('layouts.app')

@section('content')
   <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
      <div class="flex justify-between items-center mb-6">
         <div>
            <h1 class="text-3xl font-bold text-gray-900">{{ __('Customs Permits & Licenses') }}</h1>
            <p class="mt-1 text-sm text-gray-600">{{ __('Track export/import permits and their expiry dates') }}</p>
         </div>
         <a href="{{ route('customs.index') }}" class="px-4 py-2 bg-gray-200 hover:bg-gray-300 text-gray-700 font-semibold rounded-lg transition">← {{ __('Back to Declarations') }}</a>
      </div>

      <!-- Add Permit Form -->
      <div class="bg-white rounded-lg shadow-md p-6 mb-6">
         <h3 class="text-lg font-bold text-gray-900 mb-4">{{ __('Add Permit') }}</h3>
         <form method="POST" action="{{ route('customs.store-permit') }}" class="space-y-4">
            @csrf
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
               <div>
                  <label class="block text-sm font-medium text-gray-700 mb-1">{{ __('Permit Type') }} *</label>
                  <select name="permit_type" required class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-emerald-500">
                     @foreach(\App\Models\CustomsPermit::PERMIT_TYPES as $key => $label)
                        <option value="{{ $key }}">{{ $key }} — {{ $label }}</option>
                     @endforeach
                  </select>
               </div>
               <div>
                  <label class="block text-sm font-medium text-gray-700 mb-1">{{ __('Permit Number') }} *</label>
                  <input type="text" name="permit_number" required class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-emerald-500">
               </div>
               <div>
                  <label class="block text-sm font-medium text-gray-700 mb-1">{{ __('Issuing Authority') }}</label>
                  <input type="text" name="issuing_authority" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-emerald-500">
               </div>
               <div>
                  <label class="block text-sm font-medium text-gray-700 mb-1">{{ __('Issue Date') }} *</label>
                  <input type="date" name="issue_date" required class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-emerald-500">
               </div>
               <div>
                  <label class="block text-sm font-medium text-gray-700 mb-1">{{ __('Expiry Date') }} *</label>
                  <input type="date" name="expiry_date" required class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-emerald-500">
               </div>
               <div class="flex items-end">
                  <button type="submit" class="px-6 py-2 bg-emerald-600 hover:bg-emerald-700 text-white font-semibold rounded-lg transition">{{ __('Add Permit') }}</button>
               </div>
            </div>
         </form>
      </div>

      <!-- Permits Table -->
      <div class="bg-white rounded-lg shadow-md overflow-hidden">
         <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
               <tr>
                  <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">{{ __('Type') }}</th>
                  <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">{{ __('Number') }}</th>
                  <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">{{ __('Authority') }}</th>
                  <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">{{ __('Issue Date') }}</th>
                  <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">{{ __('Expiry Date') }}</th>
                  <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase">{{ __('Status') }}</th>
               </tr>
            </thead>
            <tbody class="divide-y divide-gray-200">
               @forelse($permits as $permit)
                  @php
                     $isExpired = $permit->expiry_date->isPast();
                     $isExpiringSoon = !$isExpired && $permit->expiry_date->isBetween(now(), now()->addDays(30));
                  @endphp
                  <tr class="{{ $isExpired ? 'bg-red-50' : ($isExpiringSoon ? 'bg-yellow-50' : '') }}">
                     <td class="px-6 py-4 text-sm">
                        <span class="font-bold">{{ $permit->permit_type }}</span>
                        <div class="text-xs text-gray-500">{{ \App\Models\CustomsPermit::PERMIT_TYPES[$permit->permit_type] ?? '' }}</div>
                     </td>
                     <td class="px-6 py-4 text-sm font-mono">{{ $permit->permit_number }}</td>
                     <td class="px-6 py-4 text-sm text-gray-600">{{ $permit->issuing_authority ?? '-' }}</td>
                     <td class="px-6 py-4 text-sm">{{ $permit->issue_date->format('d M Y') }}</td>
                     <td class="px-6 py-4 text-sm font-bold {{ $isExpired ? 'text-red-600' : ($isExpiringSoon ? 'text-yellow-600' : 'text-gray-900') }}">
                        {{ $permit->expiry_date->format('d M Y') }}
                        @if($isExpired) <span class="text-xs">({{ __('EXPIRED') }})</span> @endif
                        @if($isExpiringSoon) <span class="text-xs">({{ $permit->expiry_date->diffForHumans() }})</span> @endif
                     </td>
                     <td class="px-6 py-4 text-center">
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium
                           {{ $isExpired ? 'bg-red-100 text-red-800' : ($isExpiringSoon ? 'bg-yellow-100 text-yellow-800' : 'bg-green-100 text-green-800') }}">
                           {{ $isExpired ? __('Expired') : ($isExpiringSoon ? __('Expiring Soon') : __('Active')) }}
                        </span>
                     </td>
                  </tr>
               @empty
                  <tr><td colspan="6" class="px-6 py-8 text-center text-gray-500">{{ __('No permits found.') }}</td></tr>
               @endforelse
            </tbody>
         </table>
      </div>
   </div>
@endsection
