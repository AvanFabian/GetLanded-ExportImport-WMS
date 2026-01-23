@extends('layouts.app')

@section('title', __('app.batch_number') . ' ' . $batch->batch_number)

@section('content')
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
    {{-- Header --}}
    <div class="flex justify-between items-start mb-6">
        <div>
            <div class="flex items-center gap-3">
                <h1 class="text-3xl font-bold text-gray-900">{{ $batch->batch_number }}</h1>
                @switch($batch->status)
                    @case('active')
                        <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-green-100 text-green-800">{{ __('app.active') }}</span>
                        @break
                    @case('depleted')
                        <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-gray-100 text-gray-800">{{ __('app.depleted') }}</span>
                        @break
                    @case('quarantine')
                        <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-yellow-100 text-yellow-800">{{ __('app.quarantine') }}</span>
                        @break
                @endswitch
            </div>
            <p class="mt-1 text-sm text-gray-600">{{ $batch->product->name ?? 'Unknown Product' }}</p>
        </div>
        <a href="{{ route('batches.index') }}" class="px-4 py-2 bg-gray-200 hover:bg-gray-300 text-gray-700 font-semibold rounded-lg">
            ← {{ __('app.back_to_list') }}
        </a>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        {{-- Main Content --}}
        <div class="lg:col-span-2 space-y-6">
            {{-- Batch Details Card --}}
            <div class="bg-white rounded-lg shadow-md p-6">
                <h2 class="text-lg font-semibold text-gray-900 mb-4">{{ __('app.details') }}</h2>
                <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                    <div>
                        <label class="block text-xs font-medium text-gray-500 uppercase">{{ __('app.batch_number') }}</label>
                        <p class="mt-1 text-sm font-semibold text-gray-900">{{ $batch->batch_number }}</p>
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-500 uppercase">{{ __('app.product') }}</label>
                        <p class="mt-1 text-sm text-gray-900">{{ $batch->product->name ?? '-' }}</p>
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-500 uppercase">{{ __('app.cost_price') }}</label>
                        <p class="mt-1 text-sm text-gray-900">Rp {{ number_format($batch->cost_price ?? 0, 0, ',', '.') }}</p>
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-500 uppercase">{{ __('app.method') }}</label>
                        @php $method = $batch->product->batch_method ?? 'FIFO'; @endphp
                        <span class="inline-flex items-center px-2 py-0.5 mt-1 rounded text-xs font-medium 
                            {{ $method === 'FEFO' ? 'bg-orange-100 text-orange-800' : ($method === 'LIFO' ? 'bg-purple-100 text-purple-800' : 'bg-blue-100 text-blue-800') }}">
                            {{ $method }}
                        </span>
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-500 uppercase">{{ __('app.manufacturing_date') }}</label>
                        <p class="mt-1 text-sm text-gray-900">{{ $batch->manufactured_date?->format('d M Y') ?? '-' }}</p>
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-500 uppercase">{{ __('app.expiry_date') }}</label>
                        @if($batch->expiry_date)
                            @php $daysLeft = now()->diffInDays($batch->expiry_date, false); @endphp
                            <p class="mt-1 text-sm {{ $daysLeft < 0 ? 'text-red-600 font-bold' : ($daysLeft <= 30 ? 'text-orange-600' : 'text-gray-900') }}">
                                {{ $batch->expiry_date->format('d M Y') }}
                                @if($daysLeft < 0)
                                    <span class="text-red-500">({{ __('app.expired') }})</span>
                                @elseif($daysLeft <= 7)
                                    <span class="text-red-500">({{ $daysLeft }} {{ __('app.days_left') }})</span>
                                @elseif($daysLeft <= 30)
                                    <span class="text-orange-500">({{ $daysLeft }} {{ __('app.days_left') }})</span>
                                @endif
                            </p>
                        @else
                            <p class="mt-1 text-sm text-gray-400">-</p>
                        @endif
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-500 uppercase">{{ __('app.total_stock') }}</label>
                        <p class="mt-1 text-2xl font-bold text-gray-900">{{ number_format($batch->total_quantity) }}</p>
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-500 uppercase">{{ __('app.available') }}</label>
                        <p class="mt-1 text-2xl font-bold text-green-600">{{ number_format($batch->available_quantity) }}</p>
                    </div>
                </div>
                @if($batch->notes)
                    <div class="mt-4 pt-4 border-t border-gray-200">
                        <label class="block text-xs font-medium text-gray-500 uppercase mb-1">{{ __('app.notes') }}</label>
                        <p class="text-sm text-gray-700">{{ $batch->notes }}</p>
                    </div>
                @endif
            </div>

            {{-- Stock Locations Table --}}
            <div class="bg-white rounded-lg shadow-md p-6">
                <h2 class="text-lg font-semibold text-gray-900 mb-4">{{ __('app.stock_locations') }}</h2>
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">{{ __('app.warehouse') }}</th>
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">{{ __('app.zone') }}</th>
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">{{ __('app.rack') }}</th>
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">{{ __('app.bin') }}</th>
                                <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase">{{ __('app.quantity') }}</th>
                                <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase">{{ __('app.reserved') }}</th>
                                <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase">{{ __('app.available') }}</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200">
                            @forelse($batch->stockLocations as $location)
                                @php
                                    $bin = $location->bin;
                                    $rack = $bin?->rack;
                                    $zone = $rack?->zone;
                                    $warehouse = $zone?->warehouse;
                                @endphp
                                <tr class="hover:bg-gray-50">
                                    <td class="px-4 py-3 text-sm text-gray-900">{{ $warehouse?->name ?? '-' }}</td>
                                    <td class="px-4 py-3 text-sm text-gray-900">{{ $zone?->name ?? '-' }}</td>
                                    <td class="px-4 py-3 text-sm text-gray-900">{{ $rack?->code ?? '-' }}</td>
                                    <td class="px-4 py-3 text-sm font-medium text-blue-600">{{ $bin?->code ?? '-' }}</td>
                                    <td class="px-4 py-3 text-sm text-right text-gray-900 font-semibold">{{ number_format($location->quantity) }}</td>
                                    <td class="px-4 py-3 text-sm text-right text-orange-600">{{ number_format($location->reserved_quantity ?? 0) }}</td>
                                    <td class="px-4 py-3 text-sm text-right text-green-600 font-semibold">{{ number_format($location->available) }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="7" class="px-4 py-8 text-center text-gray-500">{{ __('app.no_data') }}</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        {{-- Sidebar: Audit Trail --}}
        <div class="space-y-6">
            <div class="bg-white rounded-lg shadow-md p-6">
                <h2 class="text-lg font-semibold text-gray-900 mb-4">Activity Timeline</h2>
                <x-timeline :items="$auditLogs" />
            </div>
        </div>
    </div>
</div>
@endsection
