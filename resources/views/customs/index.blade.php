@extends('layouts.app')

@section('content')
   <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
      <div class="flex justify-between items-center mb-6">
         <div>
            <h1 class="text-3xl font-bold text-gray-900">{{ __('Customs & Compliance') }}</h1>
            <p class="mt-1 text-sm text-gray-600">{{ __('Manage customs declarations and permits') }}</p>
         </div>
         <div class="flex gap-3">
            <a href="{{ route('customs.permits') }}" class="px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white font-semibold rounded-lg transition">📄 {{ __('Permits') }}</a>
            <a href="{{ route('customs.create') }}" class="px-4 py-2 bg-emerald-600 hover:bg-emerald-700 text-white font-semibold rounded-lg transition">+ {{ __('New Declaration') }}</a>
         </div>
      </div>

      @if($expiringPermits->count() > 0)
         <div class="bg-yellow-50 border-l-4 border-yellow-400 p-4 mb-6 rounded-lg">
            <div class="flex"><div class="flex-shrink-0">⚠️</div>
               <div class="ml-3">
                  <p class="text-sm text-yellow-700 font-bold">{{ __('Permits Expiring Soon') }}</p>
                  @foreach($expiringPermits as $permit)
                     <p class="text-sm text-yellow-600">{{ $permit->permit_type }} — {{ $permit->permit_number }}: {{ __('expires') }} {{ $permit->expiry_date->format('d M Y') }}</p>
                  @endforeach
               </div>
            </div>
         </div>
      @endif

      <div class="bg-white rounded-lg shadow-md p-6 mb-6">
         <form method="GET" action="{{ route('customs.index') }}" class="flex flex-wrap gap-4 items-end">
            <div class="flex-1 min-w-[200px]">
               <label class="block text-sm font-medium text-gray-700 mb-2">{{ __('Search') }}</label>
               <input type="text" name="search" value="{{ request('search') }}" placeholder="{{ __('Declaration #, HS code...') }}"
                  class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-emerald-500">
            </div>
            <div class="w-36">
               <label class="block text-sm font-medium text-gray-700 mb-2">{{ __('Type') }}</label>
               <select name="declaration_type" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-emerald-500">
                  <option value="">{{ __('All') }}</option>
                  <option value="export" {{ request('declaration_type') === 'export' ? 'selected' : '' }}>{{ __('Export') }}</option>
                  <option value="import" {{ request('declaration_type') === 'import' ? 'selected' : '' }}>{{ __('Import') }}</option>
               </select>
            </div>
            <div class="w-36">
               <label class="block text-sm font-medium text-gray-700 mb-2">{{ __('Status') }}</label>
               <select name="status" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-emerald-500">
                  <option value="">{{ __('All') }}</option>
                  @foreach(\App\Models\CustomsDeclaration::STATUSES as $key => $label)
                     <option value="{{ $key }}" {{ request('status') === $key ? 'selected' : '' }}>{{ $label }}</option>
                  @endforeach
               </select>
            </div>
            <button type="submit" class="px-6 py-2 bg-emerald-600 hover:bg-emerald-700 text-white font-semibold rounded-lg transition">{{ __('Filter') }}</button>
         </form>
      </div>

      <div class="bg-white rounded-lg shadow-md overflow-hidden">
         <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
               <tr>
                  <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">{{ __('Declaration #') }}</th>
                  <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">{{ __('Type') }}</th>
                  <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">{{ __('Shipment') }}</th>
                  <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">{{ __('HS Code') }}</th>
                  <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">{{ __('Declared Value') }}</th>
                  <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">{{ __('Total Tax') }}</th>
                  <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase">{{ __('Status') }}</th>
                  <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase">{{ __('Actions') }}</th>
               </tr>
            </thead>
            <tbody class="divide-y divide-gray-200">
               @forelse($declarations as $decl)
                  <tr class="hover:bg-gray-50">
                     <td class="px-6 py-4 text-sm"><a href="{{ route('customs.show', $decl) }}" class="text-emerald-600 hover:text-emerald-900 font-medium">{{ $decl->declaration_number ?? 'Draft' }}</a></td>
                     <td class="px-6 py-4 text-sm"><span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium {{ $decl->declaration_type === 'export' ? 'bg-blue-100 text-blue-800' : 'bg-purple-100 text-purple-800' }}">{{ ucfirst($decl->declaration_type) }}</span></td>
                     <td class="px-6 py-4 text-sm">@if($decl->outboundShipment)<a href="{{ route('outbound-shipments.show', $decl->outboundShipment) }}" class="text-blue-600">{{ $decl->outboundShipment->shipment_number }}</a>@else - @endif</td>
                     <td class="px-6 py-4 text-sm font-mono">{{ $decl->hs_code ?? '-' }}</td>
                     <td class="px-6 py-4 text-sm text-right">{{ $decl->currency_code }} {{ number_format($decl->declared_value, 2) }}</td>
                     <td class="px-6 py-4 text-sm text-right font-bold">{{ $decl->currency_code }} {{ number_format($decl->total_tax, 2) }}</td>
                     <td class="px-6 py-4 text-center">
                        @php $colors = ['draft'=>'gray','submitted'=>'blue','assessed'=>'yellow','paid'=>'indigo','cleared'=>'green','rejected'=>'red']; @endphp
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-{{ $colors[$decl->status] ?? 'gray' }}-100 text-{{ $colors[$decl->status] ?? 'gray' }}-800">{{ \App\Models\CustomsDeclaration::STATUSES[$decl->status] ?? ucfirst($decl->status) }}</span>
                     </td>
                     <td class="px-6 py-4 text-center"><a href="{{ route('customs.show', $decl) }}" class="text-blue-600 hover:text-blue-900"><svg class="w-5 h-5 inline" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg></a></td>
                  </tr>
               @empty
                  <tr><td colspan="8" class="px-6 py-8 text-center text-gray-500">{{ __('No customs declarations found.') }}</td></tr>
               @endforelse
            </tbody>
         </table>
         @if($declarations->hasPages())<div class="px-6 py-4 border-t">{{ $declarations->links() }}</div>@endif
      </div>
   </div>
@endsection
