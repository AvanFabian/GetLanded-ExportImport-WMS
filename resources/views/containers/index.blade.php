@extends('layouts.app')

@section('content')
   <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
      <div class="flex justify-between items-center mb-6">
         <div>
            <h1 class="text-3xl font-bold text-gray-900">{{ __('Containers') }}</h1>
            <p class="mt-1 text-sm text-gray-600">{{ __('Manage shipping containers and stuffing plans') }}</p>
         </div>
      </div>

      <div class="bg-white rounded-lg shadow-md p-6 mb-6">
         <form method="GET" action="{{ route('containers.index') }}" class="flex flex-wrap gap-4 items-end">
            <div class="flex-1 min-w-[200px]">
               <label class="block text-sm font-medium text-gray-700 mb-2">{{ __('Search') }}</label>
               <input type="text" name="search" value="{{ request('search') }}" placeholder="{{ __('Container #, seal #...') }}"
                  class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-emerald-500 focus:border-transparent">
            </div>
            <div class="w-36">
               <label class="block text-sm font-medium text-gray-700 mb-2">{{ __('Type') }}</label>
               <select name="container_type" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-emerald-500 focus:border-transparent">
                  <option value="">{{ __('All') }}</option>
                  @foreach(\App\Models\Container::TYPES as $key => $spec)
                     <option value="{{ $key }}" {{ request('container_type') === $key ? 'selected' : '' }}>{{ $spec['label'] }}</option>
                  @endforeach
               </select>
            </div>
            <div class="w-36">
               <label class="block text-sm font-medium text-gray-700 mb-2">{{ __('Status') }}</label>
               <select name="status" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-emerald-500 focus:border-transparent">
                  <option value="">{{ __('All') }}</option>
                  @foreach(\App\Models\Container::STATUSES as $key => $label)
                     <option value="{{ $key }}" {{ request('status') === $key ? 'selected' : '' }}>{{ $label }}</option>
                  @endforeach
               </select>
            </div>
            <button type="submit" class="px-6 py-2 bg-emerald-600 hover:bg-emerald-700 text-white font-semibold rounded-lg transition">{{ __('Filter') }}</button>
         </form>
      </div>

      <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
         @forelse($containers as $container)
            <div class="bg-white rounded-lg shadow-md overflow-hidden hover:shadow-lg transition">
               <div class="p-5">
                  <div class="flex justify-between items-start mb-3">
                     <a href="{{ route('containers.show', $container) }}" class="text-lg font-bold text-emerald-600 hover:text-emerald-900">{{ $container->container_number }}</a>
                     <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium
                        {{ $container->status === 'sealed' ? 'bg-blue-100 text-blue-800' : ($container->status === 'shipped' ? 'bg-indigo-100 text-indigo-800' : ($container->status === 'empty' ? 'bg-gray-100 text-gray-800' : 'bg-yellow-100 text-yellow-800')) }}">
                        {{ \App\Models\Container::STATUSES[$container->status] ?? ucfirst($container->status) }}
                     </span>
                  </div>
                  <p class="text-sm text-gray-600 mb-2">{{ \App\Models\Container::TYPES[$container->container_type]['label'] ?? $container->container_type }}</p>
                  @if($container->outboundShipment)
                     <p class="text-xs text-gray-500">🚢 {{ $container->outboundShipment->shipment_number }}</p>
                  @endif
                  @if($container->seal_number)
                     <p class="text-xs text-blue-600 mt-1">🔒 {{ $container->seal_number }}</p>
                  @endif

                  <!-- Utilization bar -->
                  <div class="mt-4">
                     @php $util = $container->utilizationPercent(); @endphp
                     <div class="flex justify-between text-xs text-gray-500 mb-1">
                        <span>{{ __('Utilization') }}</span>
                        <span>{{ number_format($util, 1) }}%</span>
                     </div>
                     <div class="w-full bg-gray-200 rounded-full h-2">
                        <div class="h-2 rounded-full {{ $util > 90 ? 'bg-red-500' : ($util > 70 ? 'bg-yellow-500' : 'bg-emerald-500') }}"
                           style="width: {{ min($util, 100) }}%"></div>
                     </div>
                     <div class="flex justify-between text-xs text-gray-400 mt-1">
                        <span>{{ number_format($container->used_weight_kg) }}kg / {{ number_format($container->max_weight_kg) }}kg</span>
                        <span>{{ number_format($container->used_volume_cbm, 2) }} / {{ number_format($container->max_volume_cbm, 2) }}cbm</span>
                     </div>
                  </div>
               </div>
            </div>
         @empty
            <div class="col-span-full text-center py-8 text-gray-500">{{ __('No containers found.') }}</div>
         @endforelse
      </div>

      @if ($containers->hasPages())
         <div class="mt-6">{{ $containers->links() }}</div>
      @endif
   </div>
@endsection
