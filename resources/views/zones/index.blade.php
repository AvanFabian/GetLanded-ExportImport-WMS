@extends('layouts.app')

@section('title', __('app.zones'))

@section('content')
   <div class="max-w-7xl mx-auto">
      <div class="flex items-center justify-between mb-4">
         <h2 class="text-xl font-semibold">{{ __('app.warehouse_zones') }}</h2>
         <a href="{{ route('zones.create') }}"
            class="px-3 py-2 bg-primary text-white rounded">{{ __('app.add_zone') }}</a>
      </div>

      <form method="GET" class="mb-4 flex flex-wrap gap-2">
         <input type="text" name="q" value="{{ request('q') }}" placeholder="{{ __('app.search') }}..."
            class="border rounded px-2 py-1" />
         <select name="warehouse_id" class="border rounded px-2 py-1">
            <option value="">{{ __('app.all_warehouses') }}</option>
            @foreach ($warehouses as $warehouse)
               <option value="{{ $warehouse->id }}" {{ request('warehouse_id') == $warehouse->id ? 'selected' : '' }}>
                  {{ $warehouse->name }}
               </option>
            @endforeach
         </select>
         <select name="type" class="border rounded px-2 py-1">
            <option value="">{{ __('app.all_types') }}</option>
            @foreach (['storage', 'receiving', 'shipping', 'quarantine', 'returns'] as $type)
               <option value="{{ $type }}" {{ request('type') == $type ? 'selected' : '' }}>
                  {{ __('app.' . $type) }}
               </option>
            @endforeach
         </select>
         <button class="px-3 py-1 bg-secondary text-white rounded">{{ __('app.filter') }}</button>
      </form>

      <div class="bg-white rounded shadow overflow-hidden">
         <table class="min-w-full">
            <thead class="bg-gray-50">
               <tr>
                  <th class="text-left p-3">{{ __('app.warehouse') }}</th>
                  <th class="text-left p-3">{{ __('app.code') }}</th>
                  <th class="text-left p-3">{{ __('app.name') }}</th>
                  <th class="text-left p-3">{{ __('app.type') }}</th>
                  <th class="text-left p-3">{{ __('app.racks') }}</th>
                  <th class="text-left p-3">{{ __('app.status') }}</th>
                  <th class="text-left p-3">{{ __('app.action') }}</th>
               </tr>
            </thead>
            <tbody>
               @forelse($zones as $zone)
                  <tr class="border-t hover:bg-gray-50">
                     <td class="p-3">{{ $zone->warehouse->name ?? '-' }}</td>
                     <td class="p-3 font-medium">{{ $zone->code }}</td>
                     <td class="p-3">{{ $zone->name }}</td>
                     <td class="p-3">
                        <span class="px-2 py-1 text-xs rounded
                           @if($zone->type === 'storage') bg-blue-100 text-blue-800
                           @elseif($zone->type === 'receiving') bg-green-100 text-green-800
                           @elseif($zone->type === 'shipping') bg-yellow-100 text-yellow-800
                           @elseif($zone->type === 'quarantine') bg-red-100 text-red-800
                           @else bg-gray-100 text-gray-800
                           @endif">
                           {{ __('app.' . $zone->type) }}
                        </span>
                     </td>
                     <td class="p-3">{{ $zone->racks->count() }}</td>
                     <td class="p-3">
                        @if ($zone->is_active)
                           <span class="px-2 py-1 text-xs bg-success text-white rounded">{{ __('app.active') }}</span>
                        @else
                           <span class="px-2 py-1 text-xs bg-secondary text-white rounded">{{ __('app.inactive') }}</span>
                        @endif
                     </td>
                     <td class="p-3">
                        <a href="{{ route('zones.show', $zone) }}" class="text-blue-600 mr-2">{{ __('app.view') }}</a>
                        <a href="{{ route('zones.edit', $zone) }}" class="text-blue-600 mr-2">{{ __('app.edit') }}</a>
                        <a href="{{ route('racks.index', ['zone_id' => $zone->id]) }}" class="text-green-600 mr-2">{{ __('app.racks') }}</a>
                        <form action="{{ route('zones.destroy', $zone) }}" method="POST" class="inline-block"
                           onsubmit="return confirm('{{ __('app.confirm_delete') }}')">
                           @csrf
                           @method('DELETE')
                           <button class="text-red-600">{{ __('app.delete') }}</button>
                        </form>
                     </td>
                  </tr>
               @empty
                  <tr>
                     <td colspan="7" class="p-12">
                        <div class="text-center">
                           <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                 d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10" />
                           </svg>
                           <h3 class="mt-2 text-sm font-medium text-gray-900">{{ __('app.no_zones') }}</h3>
                           <p class="mt-1 text-sm text-gray-500">{{ __('app.get_started', ['item' => __('app.zone')]) }}</p>
                           <div class="mt-6">
                              <a href="{{ route('zones.create') }}"
                                 class="inline-flex items-center px-4 py-2 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-primary hover:bg-primary/90">
                                 <svg class="-ml-1 mr-2 h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                                 </svg>
                                 {{ __('app.add_zone') }}
                              </a>
                           </div>
                        </div>
                     </td>
                  </tr>
               @endforelse
            </tbody>
         </table>
      </div>

      <div class="mt-4">{{ $zones->links() }}</div>
   </div>
@endsection
