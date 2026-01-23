@extends('layouts.app')

@section('title', $bin->full_path)

@section('content')
   <div class="max-w-4xl mx-auto">
      <div class="flex items-center justify-between mb-4">
         <h2 class="text-xl font-semibold font-mono">{{ $bin->full_path }}</h2>
         <div class="flex gap-2">
            <a href="{{ route('bins.qrcode', $bin) }}" target="_blank" class="px-3 py-2 bg-green-600 text-white rounded">QR Code</a>
            <a href="{{ route('bins.edit', $bin) }}" class="px-3 py-2 bg-secondary text-white rounded">{{ __('app.edit') }}</a>
            <a href="{{ route('bins.index') }}" class="px-3 py-2 border rounded text-gray-700">{{ __('app.back_to_list') }}</a>
         </div>
      </div>

      <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
         <!-- Bin Details -->
         <div class="bg-white rounded shadow p-6">
            <h3 class="text-lg font-medium mb-4">{{ __('app.bin_details') }}</h3>
            <dl class="space-y-3">
               <div class="flex justify-between">
                  <dt class="text-gray-600">{{ __('app.warehouse') }}</dt>
                  <dd class="font-medium">{{ $bin->rack->zone->warehouse->name ?? '-' }}</dd>
               </div>
               <div class="flex justify-between">
                  <dt class="text-gray-600">{{ __('app.zone') }}</dt>
                  <dd class="font-medium">{{ $bin->rack->zone->name ?? '-' }}</dd>
               </div>
               <div class="flex justify-between">
                  <dt class="text-gray-600">{{ __('app.rack') }}</dt>
                  <dd class="font-medium">{{ $bin->rack->code ?? '-' }}</dd>
               </div>
               <div class="flex justify-between">
                  <dt class="text-gray-600">{{ __('app.code') }}</dt>
                  <dd class="font-mono font-medium">{{ $bin->code }}</dd>
               </div>
               <div class="flex justify-between">
                  <dt class="text-gray-600">{{ __('app.barcode') }}</dt>
                  <dd class="font-mono text-sm">{{ $bin->barcode ?? '-' }}</dd>
               </div>
               <div class="flex justify-between">
                  <dt class="text-gray-600">{{ __('app.level') }}</dt>
                  <dd>{{ $bin->level }}</dd>
               </div>
               <div class="flex justify-between">
                  <dt class="text-gray-600">{{ __('app.status') }}</dt>
                  <dd>
                     @if ($bin->is_active)
                        <span class="px-2 py-1 text-xs bg-success text-white rounded">{{ __('app.active') }}</span>
                     @else
                        <span class="px-2 py-1 text-xs bg-secondary text-white rounded">{{ __('app.inactive') }}</span>
                     @endif
                  </dd>
               </div>
            </dl>
         </div>

         <!-- Capacity & Priority -->
         <div class="bg-white rounded shadow p-6">
            <h3 class="text-lg font-medium mb-4">{{ __('app.capacity_info') }}</h3>
            <div class="space-y-4">
               <div class="flex justify-between items-center p-4 bg-gray-50 rounded">
                  <span class="text-gray-600">{{ __('app.max_capacity') }}</span>
                  <span class="text-2xl font-bold">{{ $bin->max_capacity ?? __('app.unlimited') }}</span>
               </div>
               <div class="flex justify-between items-center p-4 bg-blue-50 rounded">
                  <span class="text-gray-600">{{ __('app.current_occupancy') }}</span>
                  <span class="text-2xl font-bold text-blue-600">{{ $bin->current_occupancy }}</span>
               </div>
               <div class="flex justify-between items-center p-4 rounded
                  @if($bin->pick_priority === 'high') bg-red-50
                  @elseif($bin->pick_priority === 'medium') bg-yellow-50
                  @else bg-gray-50
                  @endif">
                  <span class="text-gray-600">{{ __('app.pick_priority') }}</span>
                  <span class="px-3 py-1 text-sm rounded
                     @if($bin->pick_priority === 'high') bg-red-100 text-red-800
                     @elseif($bin->pick_priority === 'medium') bg-yellow-100 text-yellow-800
                     @else bg-gray-100 text-gray-800
                     @endif">
                     {{ __('app.' . $bin->pick_priority) }}
                  </span>
               </div>
            </div>
         </div>
      </div>

      <!-- Stock in this Bin (will be populated in Phase B) -->
      <div class="mt-6 bg-white rounded shadow p-6">
         <h3 class="text-lg font-medium mb-4">{{ __('app.stock_in_bin') }}</h3>
         <div class="text-center py-8 text-gray-500">
            <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
               <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                  d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4" />
            </svg>
            <p class="mt-2">{{ __('app.no_stock_in_bin') }}</p>
            <p class="text-sm text-gray-400">{{ __('app.stock_locations_coming_phase_b') }}</p>
         </div>
      </div>
   </div>
@endsection
