@extends('layouts.app')

@section('title', __('app.bins'))

@section('content')
   <div class="max-w-7xl mx-auto">
      <div class="flex items-center justify-between mb-4">
         <h2 class="text-xl font-semibold">{{ __('app.warehouse_bins') }}</h2>
         <a href="{{ route('bins.create') }}"
            class="px-3 py-2 bg-primary text-white rounded">{{ __('app.add_bin') }}</a>
      </div>

      <form method="GET" class="mb-4 flex flex-wrap gap-2">
         <input type="text" name="q" value="{{ request('q') }}" placeholder="{{ __('app.search') }}..."
            class="border rounded px-2 py-1" />
         <select name="rack_id" class="border rounded px-2 py-1">
            <option value="">{{ __('app.all_racks') }}</option>
            @foreach ($racks as $rack)
               <option value="{{ $rack->id }}" {{ request('rack_id') == $rack->id ? 'selected' : '' }}>
                  {{ $rack->zone->warehouse->name ?? '' }} / {{ $rack->zone->name ?? '' }} / {{ $rack->code }}
               </option>
            @endforeach
         </select>
         <select name="pick_priority" class="border rounded px-2 py-1">
            <option value="">{{ __('app.all_priorities') }}</option>
            @foreach (['high', 'medium', 'low'] as $priority)
               <option value="{{ $priority }}" {{ request('pick_priority') == $priority ? 'selected' : '' }}>
                  {{ ucfirst($priority) }}
               </option>
            @endforeach
         </select>
         <button class="px-3 py-1 bg-secondary text-white rounded">{{ __('app.filter') }}</button>
      </form>

      <div class="bg-white rounded shadow overflow-hidden">
         <table class="min-w-full">
            <thead class="bg-gray-50">
               <tr>
                  <th class="text-left p-3">{{ __('app.location') }}</th>
                  <th class="text-left p-3">{{ __('app.code') }}</th>
                  <th class="text-left p-3">{{ __('app.barcode') }}</th>
                  <th class="text-left p-3">{{ __('app.level') }}</th>
                  <th class="text-left p-3">{{ __('app.capacity') }}</th>
                  <th class="text-left p-3">{{ __('app.priority') }}</th>
                  <th class="text-left p-3">{{ __('app.status') }}</th>
                  <th class="text-left p-3">{{ __('app.action') }}</th>
               </tr>
            </thead>
            <tbody>
               @forelse($bins as $bin)
                  <tr class="border-t hover:bg-gray-50">
                     <td class="p-3 text-xs">
                        <span class="text-gray-500">{{ $bin->rack->zone->warehouse->name ?? '' }}</span><br>
                        {{ $bin->rack->zone->name ?? '' }} / {{ $bin->rack->code ?? '' }}
                     </td>
                     <td class="p-3 font-mono font-medium">{{ $bin->code }}</td>
                     <td class="p-3 text-xs font-mono">{{ $bin->barcode ?? '-' }}</td>
                     <td class="p-3">{{ $bin->level }}</td>
                     <td class="p-3">{{ $bin->max_capacity ?? __('app.unlimited') }}</td>
                     <td class="p-3">
                        <span class="px-2 py-1 text-xs rounded
                           @if($bin->pick_priority === 'high') bg-red-100 text-red-800
                           @elseif($bin->pick_priority === 'medium') bg-yellow-100 text-yellow-800
                           @else bg-gray-100 text-gray-800
                           @endif">
                           {{ __('app.' . $bin->pick_priority) }}
                        </span>
                     </td>
                     <td class="p-3">
                        @if ($bin->is_active)
                           <span class="px-2 py-1 text-xs bg-success text-white rounded">{{ __('app.active') }}</span>
                        @else
                           <span class="px-2 py-1 text-xs bg-secondary text-white rounded">{{ __('app.inactive') }}</span>
                        @endif
                     </td>
                     <td class="p-3">
                        <a href="{{ route('bins.show', $bin) }}" class="text-blue-600 mr-1">{{ __('app.view') }}</a>
                        <a href="{{ route('bins.edit', $bin) }}" class="text-blue-600 mr-1">{{ __('app.edit') }}</a>
                        <a href="{{ route('bins.qrcode', $bin) }}" class="text-green-600 mr-1" target="_blank">QR</a>
                        <form action="{{ route('bins.destroy', $bin) }}" method="POST" class="inline-block"
                           onsubmit="return confirm('{{ __('app.confirm_delete') }}')">
                           @csrf
                           @method('DELETE')
                           <button class="text-red-600">{{ __('app.delete') }}</button>
                        </form>
                     </td>
                  </tr>
               @empty
                  <tr>
                     <td colspan="8" class="p-12">
                        <div class="text-center">
                           <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                 d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4" />
                           </svg>
                           <h3 class="mt-2 text-sm font-medium text-gray-900">{{ __('app.no_bins') }}</h3>
                           <p class="mt-1 text-sm text-gray-500">{{ __('app.get_started', ['item' => __('app.bin')]) }}</p>
                           <div class="mt-6">
                              <a href="{{ route('bins.create') }}"
                                 class="inline-flex items-center px-4 py-2 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-primary hover:bg-primary/90">
                                 <svg class="-ml-1 mr-2 h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                                 </svg>
                                 {{ __('app.add_bin') }}
                              </a>
                           </div>
                        </div>
                     </td>
                  </tr>
               @endforelse
            </tbody>
         </table>
      </div>

      <div class="mt-4">{{ $bins->links() }}</div>
   </div>
@endsection
