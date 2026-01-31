@extends('layouts.app')

@section('title', $inboundShipment->shipment_number)

@section('content')
<div class="max-w-6xl mx-auto">
    <!-- Header -->
    <div class="flex justify-between items-start mb-6">
        <div>
            <div class="flex items-center gap-3">
                <h1 class="text-3xl font-bold text-gray-900">{{ $inboundShipment->shipment_number }}</h1>
                <span class="px-3 py-1 text-sm font-bold rounded-full 
                    @if($inboundShipment->status === 'on_water') bg-blue-100 text-blue-800
                    @elseif($inboundShipment->status === 'arrived') bg-green-100 text-green-800
                    @else bg-gray-100 text-gray-800 @endif">
                    {{ strtoupper(str_replace('_', ' ', $inboundShipment->status)) }}
                </span>
            </div>
            <p class="text-gray-500 mt-1">Ref: {{ $inboundShipment->reference_number ?? 'N/A' }} • Carrier: {{ $inboundShipment->carrier_name ?? 'N/A' }}</p>
        </div>
        
        <div class="flex gap-2">
            @if($inboundShipment->status !== 'received' && $inboundShipment->status !== 'cancelled')
            <form action="{{ route('inbound-shipments.receive', $inboundShipment) }}" method="POST" onsubmit="return confirm('Start receiving for this shipment?');">
                @csrf
                <button type="submit" class="bg-green-600 hover:bg-green-700 text-white px-6 py-3 rounded-lg font-bold shadow-lg flex items-center gap-2">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                    One-Click Receive
                </button>
            </form>
            @endif
        </div>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
        
        <!-- Left Column: Dates & Meta -->
        <div class="md:col-span-1 space-y-6">
             <div class="bg-white rounded-lg shadow-sm p-6">
                <h3 class="font-bold text-gray-900 mb-4 border-b pb-2">Logistics</h3>
                <dl class="space-y-3">
                    <div>
                        <dt class="text-xs text-gray-500 uppercase">Estimated Arrival</dt>
                        <dd class="text-lg font-medium">{{ $inboundShipment->eta ? $inboundShipment->eta->format('M d, Y') : '-' }}</dd>
                    </div>
                    <div>
                        <dt class="text-xs text-gray-500 uppercase">Origin Port</dt>
                        <dd class="text-gray-800">{{ $inboundShipment->origin_port ?? '-' }}</dd>
                    </div>
                    <div>
                        <dt class="text-xs text-gray-500 uppercase">Vessel / Flight</dt>
                        <dd class="text-gray-800">{{ $inboundShipment->vessel_flight_number ?? '-' }}</dd>
                    </div>
                </dl>
            </div>

            <!-- Landed Cost Engine -->
            <div class="bg-white rounded-lg shadow-sm p-6">
                <div class="flex justify-between items-center border-b pb-2 mb-4">
                    <h3 class="font-bold text-gray-900">Landed Costs</h3>
                    <span class="text-xs bg-purple-100 text-purple-800 px-2 py-1 rounded">Phase 2</span>
                </div>

                <!-- Expense List -->
                @if($inboundShipment->expenses->count() > 0)
                    <ul class="space-y-2 mb-4">
                        @foreach($inboundShipment->expenses as $expense)
                        <li class="flex justify-between text-sm">
                            <span class="text-gray-600">{{ $expense->name }} <span class="text-xs text-gray-400">({{ $expense->allocation_method }})</span></span>
                            <span class="font-bold">{{ number_format($expense->amount, 2) }}</span>
                        </li>
                        @endforeach
                        <li class="flex justify-between text-sm border-t pt-2 font-bold text-gray-900">
                            <span>Total Added Cost:</span>
                            <span>{{ number_format($inboundShipment->expenses->sum('amount'), 2) }}</span>
                        </li>
                    </ul>
                @else
                    <p class="text-sm text-gray-500 italic mb-4">No freight or duty costs added yet.</p>
                @endif

                <!-- Add Expense Form -->
                @if($inboundShipment->status !== 'received')
                <form action="{{ route('inbound-shipments.expenses.store', $inboundShipment) }}" method="POST" class="space-y-3">
                    @csrf
                    <div>
                        <input type="text" name="name" placeholder="Expense Name (e.g. Ocean Freight)" class="w-full text-sm rounded border-gray-300">
                    </div>
                    <div class="flex gap-2">
                        <input type="number" step="0.01" name="amount" placeholder="Amount" class="w-2/3 text-sm rounded border-gray-300">
                        <select name="allocation_method" class="w-1/3 text-sm rounded border-gray-300">
                            <option value="value">By Value</option>
                            <option value="quantity">By Qty</option>
                        </select>
                    </div>
                    <button type="submit" class="w-full bg-gray-800 text-white text-sm py-2 rounded hover:bg-gray-700">
                        + Add Landed Cost
                    </button>
                </form>
                @endif
            </div>
            <!-- Digital Vault (Documents) -->
            <div class="bg-white rounded-lg shadow-sm p-6 mt-6 space-y-4">
                <div class="flex justify-between items-center border-b pb-2">
                    <h3 class="font-bold text-gray-900">Digital Vault</h3>
                    <span class="text-xs bg-blue-100 text-blue-800 px-2 py-1 rounded">Phase 3</span>
                </div>

                <!-- File List -->
                 @if($inboundShipment->documents && $inboundShipment->documents->count() > 0)
                    <ul class="divide-y divide-gray-100">
                        @foreach($inboundShipment->documents as $doc)
                        <li class="py-2 flex justify-between items-center text-sm">
                            <div class="flex items-center gap-2">
                                <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                                <div>
                                    <span class="font-medium text-gray-700 block">{{ $doc->title }}</span>
                                    <span class="text-xs text-gray-500">{{ $doc->document_type }} • {{ $doc->file_size_human }}</span>
                                </div>
                            </div>
                            <a href="{{ Storage::url($doc->file_path) }}" target="_blank" class="text-blue-600 hover:text-blue-800 text-xs font-bold">View</a>
                        </li>
                        @endforeach
                    </ul>
                @else
                    <p class="text-xs text-gray-500 italic">No documents attached.</p>
                @endif

                <!-- Upload Form -->
                <form action="{{ route('inbound-shipments.documents.store', $inboundShipment) }}" method="POST" enctype="multipart/form-data" class="space-y-3 pt-2">
                    @csrf
                    <div>
                        <select name="document_type" class="w-full text-xs rounded border-gray-300">
                            <option value="BILL_OF_LADING">Bill of Lading (B/L)</option>
                            <option value="PACKING_LIST">Packing List</option>
                            <option value="COMMERCIAL_INVOICE">Commercial Invoice</option>
                            <option value="COA">Certificate of Analysis</option>
                            <option value="OTHER">Other</option>
                        </select>
                    </div>
                    <div>
                        <input type="file" name="file" class="block w-full text-xs text-gray-500 file:mr-2 file:py-1 file:px-2 file:rounded-full file:border-0 file:text-xs file:font-semibold file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100"/>
                    </div>
                    <div>
                         <input type="text" name="title" placeholder="File Title (Optional)" class="w-full text-xs rounded border-gray-300">
                    </div>
                    <button type="submit" class="w-full bg-white border border-gray-300 text-gray-700 text-xs py-2 rounded hover:bg-gray-50">
                        Upload Document
                    </button>
                </form>
            </div>
        </div>

        <!-- Right Column: Contents -->
        <div class="md:col-span-2">
            <div class="bg-white rounded-lg shadow-sm overflow-hidden">
                <div class="p-6 border-b bg-gray-50 flex justify-between items-center">
                    <h3 class="font-bold text-gray-900">Shipment Contents</h3>
                    <span class="text-sm text-gray-500">{{ $inboundShipment->purchaseOrders->count() }} Purchase Orders</span>
                </div>
                
                <div class="divide-y divide-gray-100">
                    @foreach($inboundShipment->purchaseOrders as $po)
                    <div class="p-6 hover:bg-gray-50 transition">
                        <div class="flex justify-between items-start mb-2">
                            <div>
                                <a href="{{ route('purchase-orders.show', $po) }}" class="font-bold text-blue-600 hover:underline">{{ $po->po_number }}</a>
                                <span class="text-gray-600 mx-2">•</span>
                                <span class="text-gray-800">{{ $po->supplier->name }}</span>
                            </div>
                            <span class="font-mono text-gray-700">{{ number_format($po->total_amount, 2) }}</span>
                        </div>
                        
                        <!-- Item Preview -->
                        <div class="mt-3 bg-gray-50 rounded p-3 text-sm text-gray-600">
                            <ul class="list-disc list-inside">
                                @foreach($po->details->take(3) as $item)
                                    <li>{{ $item->product->name }} ({{ $item->quantity_ordered }} units)</li>
                                @endforeach
                            </ul>
                            @if($po->details->count() > 3)
                                <p class="text-xs text-gray-400 mt-1 pl-4">+ {{ $po->details->count() - 3 }} more items...</p>
                            @endif
                        </div>
                    </div>
                    @endforeach
                </div>
            </div>
        </div>

    </div>
</div>
@endsection
