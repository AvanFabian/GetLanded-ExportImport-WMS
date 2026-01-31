@extends('layouts.app')

@section('title', 'New Inbound Shipment')

@section('content')
<div class="max-w-4xl mx-auto">
    <div class="flex items-center justify-between mb-6">
        <h1 class="text-2xl font-bold text-gray-900">Plan Inbound Shipment</h1>
        <a href="{{ route('inbound-shipments.index') }}" class="text-gray-500 hover:text-gray-700">Cancel</a>
    </div>

    <form action="{{ route('inbound-shipments.store') }}" method="POST" class="bg-white rounded-xl shadow-sm p-6">
        @csrf

        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-8">
            <!-- Logistic Details -->
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Carrier / Vessel</label>
                <input type="text" name="carrier_name" class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500" placeholder="e.g. Maersk">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Container / Tracking No</label>
                <input type="text" name="reference_number" class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500" placeholder="e.g. MSKU1234567">
            </div>
            
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">ETD (Departure)</label>
                <input type="date" name="etd" class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">ETA (Arrival)</label>
                <input type="date" name="eta" class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
            </div>
        </div>

        <div class="border-t pt-6">
            <h3 class="text-lg font-medium text-gray-900 mb-4">Select Purchase Orders to Load</h3>
            <p class="text-sm text-gray-500 mb-4">Only approved POs that are not yet in a shipment are shown.</p>

            <div class="space-y-3 max-h-60 overflow-y-auto bg-gray-50 p-4 rounded-lg">
                @forelse($purchaseOrders as $po)
                <label class="flex items-center space-x-3 p-3 bg-white rounded border border-gray-200 cursor-pointer hover:border-blue-400">
                    <input type="checkbox" name="purchase_order_ids[]" value="{{ $po->id }}" class="h-5 w-5 text-blue-600 rounded">
                    <div class="flex-1 flex justify-between">
                        <span class="font-bold text-gray-800">{{ $po->po_number }}</span>
                        <span class="text-sm text-gray-600">{{ $po->supplier->name }}</span>
                        <span class="text-sm font-mono">{{ $po->total_amount_formatted }}</span>
                    </div>
                </label>
                @empty
                <div class="text-center text-gray-500 py-4">
                    No available Purchase Orders found. <a href="{{ route('purchase-orders.create') }}" class="text-blue-600">Create a PO first.</a>
                </div>
                @endforelse
            </div>
            @error('purchase_order_ids') <p class="text-red-500 text-sm mt-1">{{ $message }}</p> @enderror
        </div>

        <div class="flex justify-end mt-8">
            <button type="submit" class="bg-blue-600 text-white px-6 py-2 rounded-lg hover:bg-blue-700 shadow-lg font-medium">
                Create Shipment
            </button>
        </div>
    </form>
</div>
@endsection
