@extends('layouts.app')

@section('content')
   <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
      <div class="flex justify-between items-center mb-6">
         <div>
            <h1 class="text-3xl font-bold text-gray-900">{{ __('Outbound Shipments') }}</h1>
            <p class="mt-1 text-sm text-gray-600">{{ __('Track export shipments and logistics') }}</p>
         </div>
         <a href="{{ route('outbound-shipments.create') }}" class="px-4 py-2 bg-emerald-600 hover:bg-emerald-700 text-white font-semibold rounded-lg transition">+ {{ __('New Shipment') }}</a>
      </div>

      <div class="bg-white rounded-lg shadow-md p-6 mb-6">
         <form method="GET" action="{{ route('outbound-shipments.index') }}" class="flex flex-wrap gap-4 items-end">
            <div class="flex-1 min-w-[200px]">
               <label class="block text-sm font-medium text-gray-700 mb-2">{{ __('Search') }}</label>
               <input type="text" name="search" value="{{ request('search') }}" placeholder="{{ __('Shipment #, BL, vessel...') }}"
                  class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-emerald-500 focus:border-transparent">
            </div>
            <div class="w-40">
               <label class="block text-sm font-medium text-gray-700 mb-2">{{ __('Status') }}</label>
               <select name="status" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-emerald-500 focus:border-transparent">
                  <option value="">{{ __('All') }}</option>
                  @foreach(\App\Models\OutboundShipment::STATUSES as $key => $label)
                     <option value="{{ $key }}" {{ request('status') === $key ? 'selected' : '' }}>{{ $label }}</option>
                  @endforeach
               </select>
            </div>
            <button type="submit" class="px-6 py-2 bg-emerald-600 hover:bg-emerald-700 text-white font-semibold rounded-lg transition">{{ __('Filter') }}</button>
            <a href="{{ route('outbound-shipments.index') }}" class="px-6 py-2 bg-gray-200 hover:bg-gray-300 text-gray-700 font-semibold rounded-lg transition">{{ __('Reset') }}</a>
         </form>
      </div>

      <div class="bg-white rounded-lg shadow-md overflow-hidden">
         <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
               <thead class="bg-gray-50">
                  <tr>
                     <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">{{ __('Shipment #') }}</th>
                     <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">{{ __('Sales Order') }}</th>
                     <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">{{ __('Carrier / Vessel') }}</th>
                     <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">{{ __('Route') }}</th>
                     <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">{{ __('Date') }}</th>
                     <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase">{{ __('Status') }}</th>
                     <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase">{{ __('Actions') }}</th>
                  </tr>
               </thead>
               <tbody class="divide-y divide-gray-200">
                  @forelse($shipments as $shipment)
                     <tr class="hover:bg-gray-50">
                        <td class="px-6 py-4 text-sm">
                           <a href="{{ route('outbound-shipments.show', $shipment) }}" class="text-emerald-600 hover:text-emerald-900 font-medium">{{ $shipment->shipment_number }}</a>
                        </td>
                        <td class="px-6 py-4 text-sm">
                           <a href="{{ route('sales-orders.show', $shipment->salesOrder) }}" class="text-blue-600 hover:text-blue-900">{{ $shipment->salesOrder->so_number }}</a>
                           <div class="text-xs text-gray-500">{{ $shipment->salesOrder->customer->name ?? '' }}</div>
                        </td>
                        <td class="px-6 py-4 text-sm text-gray-900">
                           {{ $shipment->carrier_name ?? '-' }}
                           @if($shipment->vessel_name)
                              <div class="text-xs text-gray-500">{{ $shipment->vessel_name }}</div>
                           @endif
                        </td>
                        <td class="px-6 py-4 text-sm text-gray-700">{{ $shipment->port_of_loading ?? '-' }} → {{ $shipment->port_of_discharge ?? '-' }}</td>
                        <td class="px-6 py-4 text-sm text-gray-900">{{ $shipment->shipment_date->format('d M Y') }}</td>
                        <td class="px-6 py-4 text-center">
                           @php $colors = ['draft'=>'gray','booked'=>'blue','shipped'=>'indigo','in_transit'=>'yellow','arrived'=>'green','delivered'=>'emerald']; @endphp
                           <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-{{ $colors[$shipment->status] ?? 'gray' }}-100 text-{{ $colors[$shipment->status] ?? 'gray' }}-800">
                              {{ \App\Models\OutboundShipment::STATUSES[$shipment->status] ?? ucfirst($shipment->status) }}
                           </span>
                        </td>
                        <td class="px-6 py-4 text-center">
                           <a href="{{ route('outbound-shipments.show', $shipment) }}" class="text-blue-600 hover:text-blue-900">
                              <svg class="w-5 h-5 inline" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                           </a>
                        </td>
                     </tr>
                  @empty
                     <tr><td colspan="7" class="px-6 py-8 text-center text-gray-500">{{ __('No outbound shipments found.') }}</td></tr>
                  @endforelse
               </tbody>
            </table>
         </div>
         @if ($shipments->hasPages())
            <div class="px-6 py-4 border-t">{{ $shipments->links() }}</div>
         @endif
      </div>
   </div>
@endsection
