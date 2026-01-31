@extends('layouts.app')

@section('title', 'Inbound Shipments')

@section('content')
<div class="max-w-7xl mx-auto">
    <div class="flex justify-between items-center mb-6">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">Inbound Shipments</h1>
            <p class="text-gray-600 text-sm">Track your inventory in transit (The Glass Pipeline)</p>
        </div>
        <a href="{{ route('inbound-shipments.create') }}" class="bg-blue-600 text-white px-4 py-2 rounded-lg hover:bg-blue-700">
            + New Shipment
        </a>
    </div>

    <!-- Status Cards -->
    <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-8">
        <div class="bg-blue-50 p-4 rounded-lg border border-blue-200">
            <h3 class="text-blue-700 font-bold text-sm uppercase">On Water</h3>
            <p class="text-2xl font-bold text-blue-900">{{ $shipments->where('status', 'on_water')->count() }}</p>
        </div>
        <div class="bg-yellow-50 p-4 rounded-lg border border-yellow-200">
            <h3 class="text-yellow-700 font-bold text-sm uppercase">Customs</h3>
            <p class="text-2xl font-bold text-yellow-900">{{ $shipments->where('status', 'customs')->count() }}</p>
        </div>
        <div class="bg-purple-50 p-4 rounded-lg border border-purple-200">
            <h3 class="text-purple-700 font-bold text-sm uppercase">Expected Today</h3>
            <p class="text-2xl font-bold text-purple-900">{{ $shipments->where('eta', now()->toDateString())->count() }}</p>
        </div>
        <div class="bg-green-50 p-4 rounded-lg border border-green-200">
            <h3 class="text-green-700 font-bold text-sm uppercase">Arrived</h3>
            <p class="text-2xl font-bold text-green-900">{{ $shipments->where('status', 'arrived')->count() }}</p>
        </div>
    </div>

    <div class="bg-white rounded-xl shadow-sm overflow-hidden">
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Shipment #</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Container/Ref</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">ETA</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Contents</th>
                    <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
                @forelse($shipments as $shipment)
                <tr class="hover:bg-gray-50">
                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                        <a href="{{ route('inbound-shipments.show', $shipment) }}" class="text-blue-600 hover:text-blue-900">
                            {{ $shipment->shipment_number }}
                        </a>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                        {{ $shipment->reference_number ?? '-' }} <br>
                        <span class="text-xs text-gray-400">{{ $shipment->carrier_name }}</span>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                        @if($shipment->eta)
                            {{ $shipment->eta->format('M d, Y') }} <br>
                            <span class="text-xs {{ $shipment->eta->isPast() && $shipment->status !== 'received' ? 'text-red-500 font-bold' : 'text-gray-400' }}">
                                {{ $shipment->eta->diffForHumans() }}
                            </span>
                        @else
                            -
                        @endif
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap">
                        <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full 
                            @if($shipment->status === 'on_water') bg-blue-100 text-blue-800
                            @elseif($shipment->status === 'customs') bg-yellow-100 text-yellow-800
                            @elseif($shipment->status === 'arrived') bg-green-100 text-green-800
                            @else bg-gray-100 text-gray-800 @endif">
                            {{ strtoupper(str_replace('_', ' ', $shipment->status)) }}
                        </span>
                    </td>
                    <td class="px-6 py-4 text-sm text-gray-500">
                        {{ $shipment->purchaseOrders->count() }} POs <br>
                        <span class="text-xs truncate max-w-xs block">
                            {{ $shipment->purchaseOrders->pluck('po_number')->join(', ') }}
                        </span>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                        <a href="{{ route('inbound-shipments.show', $shipment) }}" class="text-indigo-600 hover:text-indigo-900">Manage</a>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="6" class="px-6 py-10 text-center text-gray-500">
                        No shipments found. <a href="{{ route('inbound-shipments.create') }}" class="text-blue-600 underline">Create your first Inbound Shipment</a>
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div class="mt-4">
        {{ $shipments->links() }}
    </div>
</div>
@endsection
