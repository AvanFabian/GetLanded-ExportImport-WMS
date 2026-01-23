@extends('layouts.app')

@section('title', $zone->name)

@section('content')
   <div class="max-w-4xl mx-auto">
      <div class="flex items-center justify-between mb-4">
         <h2 class="text-xl font-semibold">{{ $zone->name }}</h2>
         <div class="flex gap-2">
            <a href="{{ route('zones.edit', $zone) }}" class="px-3 py-2 bg-secondary text-white rounded">{{ __('app.edit') }}</a>
            <a href="{{ route('zones.index') }}" class="px-3 py-2 border rounded text-gray-700">{{ __('app.back_to_list') }}</a>
         </div>
      </div>

      <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
         <!-- Zone Details -->
         <div class="bg-white rounded shadow p-6">
            <h3 class="text-lg font-medium mb-4">{{ __('app.zone_details') }}</h3>
            <dl class="space-y-3">
               <div class="flex justify-between">
                  <dt class="text-gray-600">{{ __('app.warehouse') }}</dt>
                  <dd class="font-medium">{{ $zone->warehouse->name ?? '-' }}</dd>
               </div>
               <div class="flex justify-between">
                  <dt class="text-gray-600">{{ __('app.code') }}</dt>
                  <dd class="font-mono font-medium">{{ $zone->code }}</dd>
               </div>
               <div class="flex justify-between">
                  <dt class="text-gray-600">{{ __('app.type') }}</dt>
                  <dd>
                     <span class="px-2 py-1 text-xs rounded
                        @if($zone->type === 'storage') bg-blue-100 text-blue-800
                        @elseif($zone->type === 'receiving') bg-green-100 text-green-800
                        @elseif($zone->type === 'shipping') bg-yellow-100 text-yellow-800
                        @elseif($zone->type === 'quarantine') bg-red-100 text-red-800
                        @else bg-gray-100 text-gray-800
                        @endif">
                        {{ __('app.' . $zone->type) }}
                     </span>
                  </dd>
               </div>
               <div class="flex justify-between">
                  <dt class="text-gray-600">{{ __('app.status') }}</dt>
                  <dd>
                     @if ($zone->is_active)
                        <span class="px-2 py-1 text-xs bg-success text-white rounded">{{ __('app.active') }}</span>
                     @else
                        <span class="px-2 py-1 text-xs bg-secondary text-white rounded">{{ __('app.inactive') }}</span>
                     @endif
                  </dd>
               </div>
               @if($zone->description)
               <div class="pt-2 border-t">
                  <dt class="text-gray-600 mb-1">{{ __('app.description') }}</dt>
                  <dd class="text-sm">{{ $zone->description }}</dd>
               </div>
               @endif
            </dl>
         </div>

         <!-- Statistics -->
         <div class="bg-white rounded shadow p-6">
            <h3 class="text-lg font-medium mb-4">{{ __('app.statistics') }}</h3>
            <div class="grid grid-cols-2 gap-4">
               <div class="text-center p-4 bg-blue-50 rounded">
                  <div class="text-3xl font-bold text-blue-600">{{ $zone->racks->count() }}</div>
                  <div class="text-sm text-gray-600">{{ __('app.racks') }}</div>
               </div>
               <div class="text-center p-4 bg-green-50 rounded">
                  <div class="text-3xl font-bold text-green-600">{{ $zone->bins->count() }}</div>
                  <div class="text-sm text-gray-600">{{ __('app.bins') }}</div>
               </div>
            </div>
            <div class="mt-4">
               <a href="{{ route('racks.create', ['zone_id' => $zone->id]) }}"
                  class="block text-center px-4 py-2 bg-primary text-white rounded hover:bg-primary/90">
                  {{ __('app.add_rack') }}
               </a>
            </div>
         </div>
      </div>

      <!-- Racks Table -->
      <div class="mt-6 bg-white rounded shadow overflow-hidden">
         <div class="p-4 border-b flex justify-between items-center">
            <h3 class="text-lg font-medium">{{ __('app.racks_in_zone') }}</h3>
            <a href="{{ route('racks.index', ['zone_id' => $zone->id]) }}" class="text-blue-600 text-sm">
               {{ __('app.view_all') }} &rarr;
            </a>
         </div>
         <table class="min-w-full">
            <thead class="bg-gray-50">
               <tr>
                  <th class="text-left p-3">{{ __('app.code') }}</th>
                  <th class="text-left p-3">{{ __('app.name') }}</th>
                  <th class="text-left p-3">{{ __('app.levels') }}</th>
                  <th class="text-left p-3">{{ __('app.bins') }}</th>
                  <th class="text-left p-3">{{ __('app.status') }}</th>
                  <th class="text-left p-3">{{ __('app.action') }}</th>
               </tr>
            </thead>
            <tbody>
               @forelse($zone->racks as $rack)
                  <tr class="border-t hover:bg-gray-50">
                     <td class="p-3 font-mono">{{ $rack->code }}</td>
                     <td class="p-3">{{ $rack->name ?? '-' }}</td>
                     <td class="p-3">{{ $rack->levels }}</td>
                     <td class="p-3">{{ $rack->bins->count() }}</td>
                     <td class="p-3">
                        @if ($rack->is_active)
                           <span class="px-2 py-1 text-xs bg-success text-white rounded">{{ __('app.active') }}</span>
                        @else
                           <span class="px-2 py-1 text-xs bg-secondary text-white rounded">{{ __('app.inactive') }}</span>
                        @endif
                     </td>
                     <td class="p-3">
                        <a href="{{ route('racks.show', $rack) }}" class="text-blue-600 mr-2">{{ __('app.view') }}</a>
                        <a href="{{ route('bins.index', ['rack_id' => $rack->id]) }}" class="text-green-600">{{ __('app.bins') }}</a>
                     </td>
                  </tr>
               @empty
                  <tr>
                     <td colspan="6" class="p-6 text-center text-gray-500">
                        {{ __('app.no_racks_in_zone') }}
                     </td>
                  </tr>
               @endforelse
            </tbody>
         </table>
      </div>
   </div>
@endsection
