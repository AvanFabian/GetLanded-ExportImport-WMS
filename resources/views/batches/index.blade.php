@extends('layouts.app')

@section('title', __('app.batch_management'))

@section('content')
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
    {{-- Header --}}
    <div class="flex justify-between items-center mb-6">
        <div>
            <h1 class="text-3xl font-bold text-gray-900">{{ __('app.batch_management') }}</h1>
            <p class="mt-1 text-sm text-gray-600">{{ __('app.batch_management_desc') }}</p>
        </div>
    </div>

    {{-- Filters --}}
    <div class="bg-white rounded-lg shadow-md p-4 mb-6">
        <form method="GET" class="grid grid-cols-1 md:grid-cols-5 gap-4">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">{{ __('app.search') }}</label>
                <input type="text" name="search" value="{{ request('search') }}" 
                    placeholder="{{ __('app.search_code_placeholder') ?? __('app.batch_number') }}..."
                    class="w-full border-gray-300 rounded-lg text-sm">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">{{ __('app.status') }}</label>
                <select name="status" class="w-full border-gray-300 rounded-lg text-sm">
                    <option value="">{{ __('app.all_statuses') }}</option>
                    <option value="active" {{ request('status') === 'active' ? 'selected' : '' }}>{{ __('app.active') }}</option>
                    <option value="depleted" {{ request('status') === 'depleted' ? 'selected' : '' }}>{{ __('app.depleted') }}</option>
                    <option value="quarantine" {{ request('status') === 'quarantine' ? 'selected' : '' }}>{{ __('app.quarantine') }}</option>
                </select>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">{{ __('app.warehouse') }}</label>
                <select name="warehouse" class="w-full border-gray-300 rounded-lg text-sm">
                    <option value="">{{ __('app.all_warehouses') }}</option>
                    @foreach($warehouses as $warehouse)
                        <option value="{{ $warehouse->id }}" {{ request('warehouse') == $warehouse->id ? 'selected' : '' }}>
                            {{ $warehouse->name }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">{{ __('app.expiring_in') }}</label>
                <select name="expiring" class="w-full border-gray-300 rounded-lg text-sm">
                    <option value="">{{ __('app.any') }}</option>
                    <option value="7" {{ request('expiring') === '7' ? 'selected' : '' }}>{{ __('app.days_days', ['days' => 7]) }}</option>
                    <option value="30" {{ request('expiring') === '30' ? 'selected' : '' }}>{{ __('app.days_days', ['days' => 30]) }}</option>
                    <option value="60" {{ request('expiring') === '60' ? 'selected' : '' }}>{{ __('app.days_days', ['days' => 60]) }}</option>
                </select>
            </div>
            <div class="flex items-end gap-2">
                <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700">
                    {{ __('app.filter') }}
                </button>
                <a href="{{ route('batches.index') }}" class="px-4 py-2 bg-gray-200 text-gray-700 rounded-lg hover:bg-gray-300">
                    {{ __('app.reset') }}
                </a>
            </div>
        </form>
    </div>

    {{-- Batch Table --}}
    <div class="bg-white rounded-lg shadow-md overflow-hidden">
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">{{ __('app.batch_number') }}</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">{{ __('app.product') }}</th>
                    <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase">{{ __('app.method') }}</th>
                    <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">{{ __('app.qty') }}</th>
                    <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase">{{ __('app.expiry_date') }}</th>
                    <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase">{{ __('app.status') }}</th>
                    <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase">{{ __('app.actions') }}</th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
                @forelse($batches as $batch)
                    <tr class="hover:bg-gray-50">
                        <td class="px-6 py-4 whitespace-nowrap">
                            <a href="{{ route('batches.show', $batch) }}" class="text-blue-600 hover:text-blue-800 font-medium">
                                {{ $batch->batch_number }}
                            </a>
                        </td>
                        <td class="px-6 py-4">
                            <div class="text-sm font-medium text-gray-900">{{ $batch->product->name ?? '-' }}</div>
                            <div class="text-xs text-gray-500">{{ $batch->product->code ?? '' }}</div>
                        </td>
                        <td class="px-6 py-4 text-center">
                            @php $method = $batch->product->batch_method ?? 'FIFO'; @endphp
                            <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium 
                                {{ $method === 'FEFO' ? 'bg-orange-100 text-orange-800' : ($method === 'LIFO' ? 'bg-purple-100 text-purple-800' : 'bg-blue-100 text-blue-800') }}">
                                {{ $method }}
                            </span>
                        </td>
                        <td class="px-6 py-4 text-right">
                            <span class="text-sm font-semibold text-gray-900">{{ number_format($batch->total_quantity) }}</span>
                            @if($batch->available_quantity < $batch->total_quantity)
                                <span class="text-xs text-gray-500 block">
                                    ({{ number_format($batch->available_quantity) }} {{ __('app.available') }})
                                </span>
                            @endif
                        </td>
                        <td class="px-6 py-4 text-center">
                            @if($batch->expiry_date)
                                @php 
                                    $daysLeft = now()->diffInDays($batch->expiry_date, false);
                                @endphp
                                <span class="text-sm {{ $daysLeft < 0 ? 'text-red-600 font-bold' : ($daysLeft <= 30 ? 'text-orange-600' : 'text-gray-600') }}">
                                    {{ $batch->expiry_date->format('d M Y') }}
                                </span>
                                @if($daysLeft < 0)
                                    <span class="text-xs text-red-500 block">{{ __('app.expired') }}</span>
                                @elseif($daysLeft <= 7)
                                    <span class="text-xs text-red-500 block">{{ $daysLeft }} {{ __('app.days_left') }}</span>
                                @elseif($daysLeft <= 30)
                                    <span class="text-xs text-orange-500 block">{{ $daysLeft }} {{ __('app.days_left') }}</span>
                                @endif
                            @else
                                <span class="text-gray-400">-</span>
                            @endif
                        </td>
                        <td class="px-6 py-4 text-center">
                            @switch($batch->status)
                                @case('active')
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">{{ __('app.active') }}</span>
                                    @break
                                @case('depleted')
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-800">{{ __('app.depleted') }}</span>
                                    @break
                                @case('quarantine')
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800">{{ __('app.quarantine') }}</span>
                                    @break
                                @default
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-600">{{ ucfirst($batch->status) }}</span>
                            @endswitch
                        </td>
                        <td class="px-6 py-4 text-center">
                            <a href="{{ route('batches.show', $batch) }}" 
                                class="text-blue-600 hover:text-blue-800 text-sm font-medium">
                                {{ __('app.view') }}
                            </a>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" class="px-6 py-12 text-center text-gray-500">
                            <svg class="w-12 h-12 mx-auto text-gray-300 mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/>
                            </svg>
                            <p>No batches found</p>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
        
        {{-- Pagination --}}
        @if($batches->hasPages())
            <div class="px-6 py-4 border-t border-gray-200">
                {{ $batches->withQueryString()->links() }}
            </div>
        @endif
    </div>
</div>
@endsection
