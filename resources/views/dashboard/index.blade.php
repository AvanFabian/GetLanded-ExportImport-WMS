@extends('layouts.app')

@section('title', 'Executive Dashboard')

@section('content')
<div class="p-4 md:p-6">
    {{-- Header --}}
    <div class="flex justify-between items-center mb-6">
        <div>
            <h1 class="text-2xl font-bold text-gray-800">Executive Dashboard</h1>
            <p class="text-sm text-gray-500">Commodity Export Operations Overview</p>
        </div>
        <div class="text-xs text-gray-400">
            Data cached for performance • USD Rate: Rp {{ number_format($usdRate ?? 15850, 2) }}
        </div>
    </div>

    {{-- Supply Chain Alerts (Free API) --}}
    @if(!empty($holidayWarnings))
        <div class="mb-6 bg-orange-50 border-l-4 border-orange-500 p-4 rounded-md shadow-sm">
            <div class="flex">
                <div class="flex-shrink-0">
                    <svg class="h-5 w-5 text-orange-400" viewBox="0 0 20 20" fill="currentColor">
                        <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd" />
                    </svg>
                </div>
                <div class="ml-3">
                    <h3 class="text-sm font-medium text-orange-800">Supply Chain Warning: Upcoming Supplier Holidays</h3>
                    <div class="mt-2 text-sm text-orange-700">
                        <ul class="list-disc pl-5 space-y-1">
                            @foreach($holidayWarnings as $holiday)
                                <li>
                                    <strong>{{ \Carbon\Carbon::parse($holiday['date'])->format('d M') }}</strong>: 
                                    {{ $holiday['localName'] ?? $holiday['name'] }} 
                                    <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-orange-100 text-orange-800">
                                        {{ $holiday['country'] == 'CN' ? 'China' : 'Indonesia' }}
                                    </span>
                                </li>
                            @endforeach
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    @endif

    {{-- Summary Widgets --}}
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
        {{-- Widget 1: Total Stock Value --}}
        <div class="bg-gradient-to-br from-blue-600 to-blue-800 rounded-xl shadow-lg p-6 text-white">
            <div class="flex items-center justify-between mb-2">
                <span class="text-blue-200 text-sm font-medium">Total Stock Value</span>
                <div class="w-10 h-10 bg-white bg-opacity-20 rounded-full flex items-center justify-center">
                    <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                        <path d="M4 4a2 2 0 00-2 2v4a2 2 0 002 2V6h10a2 2 0 00-2-2H4z"/>
                        <path fill-rule="evenodd" d="M6 10a2 2 0 012-2h8a2 2 0 012 2v4a2 2 0 01-2 2H8a2 2 0 01-2-2v-4zm2 0v4h8v-4H8z" clip-rule="evenodd"/>
                    </svg>
                </div>
            </div>
            <p class="text-2xl font-bold">Rp {{ number_format($stockValue['idr'] ?? 0, 0, ',', '.') }}</p>
            <p class="text-blue-200 text-sm mt-1">
                ≈ ${{ number_format($stockValue['usd'] ?? 0, 2) }} USD
            </p>
        </div>

        {{-- Widget 2: Active Alerts --}}
        <div class="bg-white rounded-xl shadow-lg p-6 border-l-4 {{ ($activeAlerts['total'] ?? 0) > 0 ? 'border-red-500' : 'border-green-500' }}">
            <div class="flex items-center justify-between mb-2">
                <span class="text-gray-600 text-sm font-medium">Active Alerts</span>
                <div class="w-10 h-10 {{ ($activeAlerts['total'] ?? 0) > 0 ? 'bg-red-100' : 'bg-green-100' }} rounded-full flex items-center justify-center">
                    <svg class="w-5 h-5 {{ ($activeAlerts['total'] ?? 0) > 0 ? 'text-red-600' : 'text-green-600' }}" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/>
                    </svg>
                </div>
            </div>
            <p class="text-3xl font-bold text-gray-900">{{ $activeAlerts['total'] ?? 0 }}</p>
            <div class="flex gap-4 mt-2 text-xs text-gray-500">
                <span class="flex items-center gap-1">
                    <span class="w-2 h-2 bg-yellow-500 rounded-full"></span>
                    Low Stock: {{ $activeAlerts['low_stock'] ?? 0 }}
                </span>
                <span class="flex items-center gap-1">
                    <span class="w-2 h-2 bg-red-500 rounded-full"></span>
                    Expiring: {{ $activeAlerts['expiring'] ?? 0 }}
                </span>
            </div>
        </div>

        {{-- Widget 3: On Water Value (NEW) --}}
        <div class="bg-white rounded-xl shadow-lg p-6 border-l-4 border-cyan-500">
            <div class="flex items-center justify-between mb-2">
                <span class="text-gray-600 text-sm font-medium">On Water Value</span>
                <div class="w-10 h-10 bg-cyan-100 rounded-full flex items-center justify-center">
                    <svg class="w-5 h-5 text-cyan-600" fill="currentColor" viewBox="0 0 20 20">
                        <path d="M8 16.5a1.5 1.5 0 11-3 0 1.5 1.5 0 013 0zM15 16.5a1.5 1.5 0 11-3 0 1.5 1.5 0 013 0z"/>
                        <path d="M3 4a1 1 0 00-1 1v10a1 1 0 001 1h1.05a2.5 2.5 0 014.9 0H10a1 1 0 001-1V5a1 1 0 00-1-1H3zM14 7a1 1 0 00-1 1v6.05A2.5 2.5 0 0115.95 16H17a1 1 0 001-1v-5a1 1 0 00-.293-.707l-2-2A1 1 0 0015 7h-1z"/>
                    </svg>
                </div>
            </div>
            <p class="text-2xl font-bold text-gray-900">Rp {{ number_format($onWaterValue ?? 0, 0, ',', '.') }}</p>
            <p class="text-xs text-gray-500 mt-1">In Transit</p>
        </div>

        {{-- Widget 4: Warehouse Fill Rate --}}
        <div class="bg-white rounded-xl shadow-lg p-6 border-l-4 border-purple-500">
            <div class="flex items-center justify-between mb-2">
                <span class="text-gray-600 text-sm font-medium">Warehouse Fill Rate</span>
                <div class="w-10 h-10 bg-purple-100 rounded-full flex items-center justify-center">
                    <svg class="w-5 h-5 text-purple-600" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M5 2a2 2 0 00-2 2v14l3.5-2 3.5 2 3.5-2 3.5 2V4a2 2 0 00-2-2H5zm2.5 3a1.5 1.5 0 100 3 1.5 1.5 0 000-3zm6.207.293a1 1 0 00-1.414 0l-6 6a1 1 0 101.414 1.414l6-6a1 1 0 000-1.414zM12.5 10a1.5 1.5 0 100 3 1.5 1.5 0 000-3z" clip-rule="evenodd"/>
                    </svg>
                </div>
            </div>
            <div class="flex items-end gap-2">
                <p class="text-3xl font-bold text-gray-900">{{ $fillRate['percentage'] ?? 0 }}%</p>
                <p class="text-sm text-gray-500 pb-1">filled</p>
            </div>
            <p class="text-xs text-gray-500 mt-1">
                {{ $fillRate['occupied_bins'] ?? 0 }} / {{ $fillRate['total_bins'] ?? 0 }} bins occupied
            </p>
        </div>
    </div>

    {{-- Charts Row --}}
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-6">
        {{-- Stock Trends Chart (2/3 width) --}}
        <div class="lg:col-span-2 bg-white rounded-xl shadow-lg p-6">
            <h3 class="text-lg font-semibold text-gray-800 mb-4">Stock Movement Trend (14 Days)</h3>
            <div class="h-64">
                <canvas id="stockTrendsChart"></canvas>
            </div>
        </div>

        {{-- Zone Distribution Chart (1/3 width) --}}
        <div class="bg-white rounded-xl shadow-lg p-6">
            <h3 class="text-lg font-semibold text-gray-800 mb-4">Stock by Zone</h3>
            <div class="h-64">
                <canvas id="zoneDistributionChart"></canvas>
            </div>
        </div>
    </div>

    {{-- Analytics Charts Row (NEW) --}}
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-6">
        {{-- Monthly Profit Chart --}}
        <div class="lg:col-span-2 bg-white rounded-xl shadow-lg p-6">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-lg font-semibold text-gray-800">Revenue vs Net Profit (6 Months)</h3>
                <span class="text-xs text-gray-400">Financial Performance</span>
            </div>
            <div class="h-64">
                <canvas id="monthlyProfitChart"></canvas>
            </div>
        </div>

        {{-- Inventory Aging Chart --}}
        <div class="bg-white rounded-xl shadow-lg p-6">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-lg font-semibold text-gray-800">Inventory Aging</h3>
                <span class="text-xs text-gray-400">Stock Health</span>
            </div>
            <div class="h-64">
                <canvas id="inventoryAgingChart"></canvas>
            </div>
        </div>
    </div>

    {{-- Top Products Chart --}}
    <div class="bg-white rounded-xl shadow-lg p-6 mb-6">
        <div class="flex items-center justify-between mb-4">
            <h3 class="text-lg font-semibold text-gray-800">Top 5 Products by Sales (6 Months)</h3>
            <span class="text-xs text-gray-400">Best Performers</span>
        </div>
        <div class="h-64">
            <canvas id="topProductsChart"></canvas>
        </div>
    </div>

    {{-- Operational Lists --}}
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-6">
        
        {{-- Shipment Map (NEW) --}}
        <div class="lg:col-span-2 bg-white rounded-xl shadow-lg p-6 relative">
            <h3 class="text-lg font-semibold text-gray-800 mb-4 flex items-center gap-2">
                 <svg class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                     <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 20l-5.447-2.724A1 1 0 013 16.382V5.618a1 1 0 011.447-.894L9 7m0 13l6-3m-6 3V7m6 10l4.553 2.276A1 1 0 0121 18.382V7.618a1 1 0 01-.553-.894L15 4m0 13V4m0 0L9 7"></path>
                 </svg>
                 Live Shipment Tracker
            </h3>
            <div id="shipmentMap" class="h-64 rounded-lg bg-gray-100 z-0"></div>
            
            {{-- Leaflet CSS & JS --}}
            <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" integrity="sha256-p4NxAoJBhIIN+hmNHrzRCf9tD/miZyoHS5obTRR9BMY=" crossorigin=""/>
            <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js" integrity="sha256-20nQCchB9co0qIjJZRGuk2/Z9VM+kNiyxNV1lvTlZBo=" crossorigin=""></script>
            <style>.leaflet-container { z-index: 1; }</style>
            
            <script>
            document.addEventListener('DOMContentLoaded', function() {
                // Initialize Map
                var map = L.map('shipmentMap').setView([-2.5489, 118.0149], 4); // Indonesia Center

                L.tileLayer('https://tile.openstreetmap.org/{z}/{x}/{y}.png', {
                    maxZoom: 19,
                    attribution: '&copy; OpenStreetMap'
                }).addTo(map);

                // Add Markers & Paths
                const shipments = @json($mapData ?? []);
                
                // Custom Icons
                var planeIcon = L.divIcon({
                    className: 'plane-icon',
                    html: '<div class="w-8 h-8 bg-blue-500 rounded-full flex items-center justify-center text-white border-2 border-white shadow-lg pulse"><svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20"><path d="M10.894 2.553a1 1 0 00-1.788 0l-7 14a1 1 0 001.169 1.409l5-1.429A1 1 0 009 15.571V11a1 1 0 112 0v4.571a1 1 0 00.725.962l5 1.428a1 1 0 001.17-1.408l-7-14z"></path></svg></div>',
                    iconSize: [32, 32],
                    iconAnchor: [16, 16]
                });

                var shipIcon = L.divIcon({
                    className: 'ship-icon',
                    html: '<div class="w-8 h-8 bg-cyan-600 rounded-full flex items-center justify-center text-white border-2 border-white shadow-lg pulse"><svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm1-12a1 1 0 10-2 0v4a1 1 0 00.293.707l2.828 2.829a1 1 0 101.415-1.415L11 9.586V6z" clip-rule="evenodd"/></svg></div>',
                    iconSize: [32, 32],
                    iconAnchor: [16, 16]
                });

                if (shipments.length > 0) {
                    var bounds = L.latLngBounds();
                    
                    shipments.forEach(function(s) {
                         // 1. Draw Flight Path (Dashed Line)
                         var latlngs = [
                             [s.origin.lat, s.origin.lng],
                             [s.destination.lat, s.destination.lng]
                         ];
                         
                         var polyline = L.polyline(latlngs, {
                             color: '#94a3b8', 
                             weight: 2,
                             dashArray: '10, 10', 
                             opacity: 0.6 
                         }).addTo(map);

                         // 2. Draw Moving Marker at Calculated Position
                         var icon = s.carrier.toLowerCase().includes('air') ? planeIcon : shipIcon;
                         
                         var marker = L.marker([s.position.lat, s.position.lng], {icon: icon}).addTo(map)
                            .bindPopup(`
                                <div class="text-sm">
                                    <div class="font-bold text-gray-800">${s.number}</div>
                                    <div class="text-xs text-gray-500 mb-1">${s.carrier}</div>
                                    <div class="flex justify-between gap-4">
                                        <div><div class="text-xs font-semibold">Origin</div><div>${s.supplier_name}</div></div>
                                        <div class="text-right"><div class="text-xs font-semibold">ETA</div><div>${s.eta}</div></div>
                                    </div>
                                    <div class="mt-2 w-full bg-gray-200 rounded-full h-1.5 dark:bg-gray-700">
                                        <div class="bg-blue-600 h-1.5 rounded-full" style="width: ${s.progress}%"></div>
                                    </div>
                                </div>
                            `);
                         
                         // 3. Add Origin/Dest markers (small dots)
                         L.circleMarker([s.origin.lat, s.origin.lng], {radius: 4, color: '#64748b', fillOpacity: 1}).addTo(map);
                         
                         bounds.extend([s.origin.lat, s.origin.lng]);
                         bounds.extend([s.destination.lat, s.destination.lng]);
                    });
                    
                    map.fitBounds(bounds, {padding: [50, 50]});
                } else {
                     // Default view if no shipments
                     L.marker([-6.1751, 106.8650]).addTo(map)
                        .bindPopup("<b>Central Warehouse</b><br>Jakarta, Indonesia").openPopup();
                }

                // CSS for Pulse Animation
                const style = document.createElement('style');
                style.innerHTML = `
                    @keyframes pulse-ring {
                        0% { transform: scale(0.8); box-shadow: 0 0 0 0 rgba(59, 130, 246, 0.7); }
                        70% { transform: scale(1); box-shadow: 0 0 0 10px rgba(59, 130, 246, 0); }
                        100% { transform: scale(0.8); box-shadow: 0 0 0 0 rgba(59, 130, 246, 0); }
                    }
                    .pulse {
                        animation: pulse-ring 2s infinite;
                    }
                `;
                document.head.appendChild(style);
            });
            </script>
        </div>

        {{-- Incoming Shipments (NEW) --}}
        <div class="lg:col-span-2 bg-white rounded-xl shadow-lg overflow-hidden">
             <div class="px-6 py-4 bg-cyan-50 border-b border-cyan-100">
                <div class="flex justify-between items-center">
                    <h3 class="text-lg font-semibold text-cyan-800 flex items-center gap-2">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 16l-1 1-6-6-6 6-1-1 9-9 9 9z"/> <!-- Ship icon placeholder -->
                             <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16V6a1 1 0 00-1-1H4a1 1 0 00-1 1v10a1 1 0 001 1h1m8-1a1 1 0 01-1 1H9m4-1V8a1 1 0 011-1h2.586a1 1 0 01.707.293l3.414 3.414a1 1 0 01.293.707V16a1 1 0 01-1 1h-1m-6-1a1 1 0 001 1h1M5 17a2 2 0 104 0m-4 0a2 2 0 114 0m6 0a2 2 0 104 0m-4 0a2 2 0 114 0"></path>
                        </svg>
                        Incoming Shipments
                    </h3>
                    <a href="{{ route('inbound-shipments.index') }}" class="text-sm text-cyan-600 hover:text-cyan-800">View All →</a>
                </div>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full text-sm text-left text-gray-500">
                    <thead class="text-xs text-gray-700 uppercase bg-gray-50">
                        <tr>
                            <th scope="col" class="px-6 py-3">Shipment #</th>
                            <th scope="col" class="px-6 py-3">Supplier</th>
                            <th scope="col" class="px-6 py-3">Status</th>
                            <th scope="col" class="px-6 py-3">ETA</th>
                            <th scope="col" class="px-6 py-3 text-right">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($incomingShipments ?? [] as $shipment)
                            <tr class="bg-white border-b hover:bg-gray-50">
                                <td class="px-6 py-4 font-medium text-gray-900">
                                    {{ $shipment->shipment_number }}
                                    <div class="text-xs text-gray-400">{{ $shipment->reference_number ?? '-' }}</div>
                                </td>
                                <td class="px-6 py-4">
                                    {{ $shipment->purchaseOrders->first()->supplier->name ?? 'Multiple' }}
                                </td>
                                <td class="px-6 py-4">
                                    <span class="bg-cyan-100 text-cyan-800 text-xs font-medium px-2.5 py-0.5 rounded capitalize">
                                        {{ str_replace('_', ' ', $shipment->status) }}
                                    </span>
                                </td>
                                <td class="px-6 py-4">
                                    {{ $shipment->estimated_arrival_date ? $shipment->estimated_arrival_date->format('d M Y') : 'TBD' }}
                                </td>
                                <td class="px-6 py-4 text-right">
                                    <a href="{{ route('inbound-shipments.show', $shipment) }}" class="text-blue-600 hover:underline">Manage</a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="px-6 py-4 text-center text-gray-400">No active shipments</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
        
        {{-- Expiring Soon --}}
        <div class="bg-white rounded-xl shadow-lg overflow-hidden">
            <div class="px-6 py-4 bg-orange-50 border-b border-orange-100">
                <div class="flex justify-between items-center">
                    <h3 class="text-lg font-semibold text-orange-800 flex items-center gap-2">
                        <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm1-12a1 1 0 10-2 0v4a1 1 0 00.293.707l2.828 2.829a1 1 0 101.415-1.415L11 9.586V6z" clip-rule="evenodd"/>
                        </svg>
                        Expiring Soon
                    </h3>
                    <a href="{{ route('batches.index', ['expiring' => 30]) }}" class="text-sm text-orange-600 hover:text-orange-800">View All →</a>
                </div>
            </div>
            <div class="divide-y divide-gray-100">
                @forelse($expiringSoon as $batch)
                    @php $daysLeft = now()->diffInDays($batch->expiry_date, false); @endphp
                    <div class="px-6 py-3 flex items-center justify-between hover:bg-gray-50">
                        <div>
                            <a href="{{ route('batches.show', $batch) }}" class="font-medium text-gray-900 hover:text-blue-600">
                                {{ $batch->batch_number }}
                            </a>
                            <p class="text-sm text-gray-500">{{ $batch->product->name ?? '-' }}</p>
                        </div>
                        <div class="text-right">
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium 
                                {{ $daysLeft <= 7 ? 'bg-red-100 text-red-800' : 'bg-orange-100 text-orange-800' }}">
                                {{ $daysLeft }} days
                            </span>
                            <p class="text-xs text-gray-500 mt-1">{{ $batch->expiry_date->format('d M Y') }}</p>
                        </div>
                    </div>
                @empty
                    <div class="px-6 py-8 text-center text-gray-500">
                        <svg class="w-12 h-12 mx-auto text-gray-300 mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                        <p>No batches expiring soon</p>
                    </div>
                @endforelse
            </div>
        </div>

        {{-- Recent Activity --}}
        <div class="bg-white rounded-xl shadow-lg overflow-hidden">
            <div class="px-6 py-4 bg-gray-50 border-b border-gray-100">
                <div class="flex justify-between items-center">
                    <h3 class="text-lg font-semibold text-gray-800 flex items-center gap-2">
                        <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm1-12a1 1 0 10-2 0v4a1 1 0 00.293.707l2.828 2.829a1 1 0 101.415-1.415L11 9.586V6z" clip-rule="evenodd"/>
                        </svg>
                        Recent Activity
                    </h3>
                </div>
            </div>
            <div class="divide-y divide-gray-100 max-h-80 overflow-y-auto">
                @forelse($recentActivity as $log)
                    <div class="px-6 py-3 flex items-start gap-3 hover:bg-gray-50">
                        @switch($log->event)
                            @case('created')
                                <span class="flex-shrink-0 w-6 h-6 rounded-full bg-green-100 flex items-center justify-center">
                                    <span class="w-2 h-2 bg-green-500 rounded-full"></span>
                                </span>
                                @break
                            @case('updated')
                                <span class="flex-shrink-0 w-6 h-6 rounded-full bg-blue-100 flex items-center justify-center">
                                    <span class="w-2 h-2 bg-blue-500 rounded-full"></span>
                                </span>
                                @break
                            @case('deleted')
                                <span class="flex-shrink-0 w-6 h-6 rounded-full bg-red-100 flex items-center justify-center">
                                    <span class="w-2 h-2 bg-red-500 rounded-full"></span>
                                </span>
                                @break
                            @default
                                <span class="flex-shrink-0 w-6 h-6 rounded-full bg-gray-100 flex items-center justify-center">
                                    <span class="w-2 h-2 bg-gray-500 rounded-full"></span>
                                </span>
                        @endswitch
                        <div class="flex-1 min-w-0">
                            <p class="text-sm text-gray-900">
                                <span class="font-medium capitalize">{{ $log->event }}</span>
                                <span class="text-gray-500">{{ class_basename($log->auditable_type) }}</span>
                                @if($log->user)
                                    <span class="text-gray-500">by</span>
                                    <span class="font-medium">{{ $log->user->name }}</span>
                                @endif
                            </p>
                            <p class="text-xs text-gray-500">{{ $log->created_at->diffForHumans() }}</p>
                        </div>
                    </div>
                @empty
                    <div class="px-6 py-8 text-center text-gray-500">
                        <p>No recent activity</p>
                    </div>
                @endforelse
            </div>
        </div>
    </div>

    {{-- Quick Actions --}}
    <div class="bg-white rounded-xl shadow-lg p-6">
        <h3 class="text-lg font-bold text-gray-800 mb-4">Quick Actions</h3>
        <div class="grid grid-cols-2 md:grid-cols-4 lg:grid-cols-6 gap-3">
            <a href="{{ route('batches.index') }}"
                class="flex flex-col items-center justify-center p-4 border-2 border-dashed border-gray-300 rounded-lg hover:border-blue-500 hover:bg-blue-50 transition">
                <svg class="w-8 h-8 text-blue-600 mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/>
                </svg>
                <span class="text-sm font-medium text-gray-700">Batches</span>
            </a>
            <a href="{{ route('stock-ins.create') }}"
                class="flex flex-col items-center justify-center p-4 border-2 border-dashed border-gray-300 rounded-lg hover:border-green-500 hover:bg-green-50 transition">
                <svg class="w-8 h-8 text-green-600 mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16V4m0 0L3 8m4-4l4 4m6 0v12m0 0l4-4m-4 4l-4-4"/>
                </svg>
                <span class="text-sm font-medium text-gray-700">Stock In</span>
            </a>
            <a href="{{ route('sales-orders.create') }}"
                class="flex flex-col items-center justify-center p-4 border-2 border-dashed border-gray-300 rounded-lg hover:border-purple-500 hover:bg-purple-50 transition">
                <svg class="w-8 h-8 text-purple-600 mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"/>
                </svg>
                <span class="text-sm font-medium text-gray-700">New Order</span>
            </a>
            <a href="{{ route('currencies.index') }}"
                class="flex flex-col items-center justify-center p-4 border-2 border-dashed border-gray-300 rounded-lg hover:border-yellow-500 hover:bg-yellow-50 transition">
                <svg class="w-8 h-8 text-yellow-600 mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
                <span class="text-sm font-medium text-gray-700">Currencies</span>
            </a>
            <a href="{{ route('notifications.index') }}"
                class="flex flex-col items-center justify-center p-4 border-2 border-dashed border-gray-300 rounded-lg hover:border-red-500 hover:bg-red-50 transition">
                <svg class="w-8 h-8 text-red-600 mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/>
                </svg>
                <span class="text-sm font-medium text-gray-700">Alerts</span>
            </a>
            <a href="{{ route('reports.index') }}"
                class="flex flex-col items-center justify-center p-4 border-2 border-dashed border-gray-300 rounded-lg hover:border-gray-500 hover:bg-gray-50 transition">
                <svg class="w-8 h-8 text-gray-600 mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                </svg>
                <span class="text-sm font-medium text-gray-700">Reports</span>
            </a>
        </div>
    </div>
</div>

{{-- Chart.js CDN --}}
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Stock Trends Line Chart
    const stockTrendsCtx = document.getElementById('stockTrendsChart').getContext('2d');
    new Chart(stockTrendsCtx, {
        type: 'line',
        data: {
            labels: @json($stockTrends['labels'] ?? []),
            datasets: [
                {
                    label: 'Stock In',
                    data: @json($stockTrends['stockIn'] ?? []),
                    borderColor: 'rgb(34, 197, 94)',
                    backgroundColor: 'rgba(34, 197, 94, 0.1)',
                    fill: true,
                    tension: 0.3,
                },
                {
                    label: 'Stock Out',
                    data: @json($stockTrends['stockOut'] ?? []),
                    borderColor: 'rgb(239, 68, 68)',
                    backgroundColor: 'rgba(239, 68, 68, 0.1)',
                    fill: true,
                    tension: 0.3,
                }
            ]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: { position: 'top' }
            },
            scales: {
                y: { beginAtZero: true }
            }
        }
    });

    // Zone Distribution Doughnut Chart
    const zoneCtx = document.getElementById('zoneDistributionChart').getContext('2d');
    new Chart(zoneCtx, {
        type: 'doughnut',
        data: {
            labels: @json($zoneDistribution['labels'] ?? []),
            datasets: [{
                data: @json($zoneDistribution['data'] ?? []),
                backgroundColor: [
                    'rgb(59, 130, 246)',
                    'rgb(34, 197, 94)',
                    'rgb(249, 115, 22)',
                    'rgb(139, 92, 246)',
                    'rgb(236, 72, 153)',
                    'rgb(20, 184, 166)',
                ],
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: { position: 'bottom', labels: { boxWidth: 12 } }
            }
        }
    });

    // Monthly Profit Line Chart (NEW)
    const profitCtx = document.getElementById('monthlyProfitChart').getContext('2d');
    new Chart(profitCtx, {
        type: 'line',
        data: {
            labels: @json($monthlyProfit['labels'] ?? []),
            datasets: [
                {
                    label: 'Revenue',
                    data: @json($monthlyProfit['revenue'] ?? []),
                    borderColor: 'rgb(59, 130, 246)',
                    backgroundColor: 'rgba(59, 130, 246, 0.1)',
                    fill: true,
                    tension: 0.4,
                    borderWidth: 3,
                },
                {
                    label: 'Net Profit',
                    data: @json($monthlyProfit['netProfit'] ?? []),
                    borderColor: 'rgb(16, 185, 129)',
                    backgroundColor: 'rgba(16, 185, 129, 0.1)',
                    fill: true,
                    tension: 0.4,
                    borderWidth: 3,
                }
            ]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: { position: 'top' },
                tooltip: {
                    callbacks: {
                        label: function(context) {
                            return context.dataset.label + ': Rp ' + context.raw.toLocaleString('id-ID');
                        }
                    }
                }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    ticks: {
                        callback: function(value) {
                            if (value >= 1000000000) return 'Rp ' + (value / 1000000000).toFixed(1) + 'B';
                            if (value >= 1000000) return 'Rp ' + (value / 1000000).toFixed(0) + 'M';
                            return 'Rp ' + value.toLocaleString('id-ID');
                        }
                    }
                }
            }
        }
    });

    // Inventory Aging Doughnut Chart (NEW)
    const agingCtx = document.getElementById('inventoryAgingChart').getContext('2d');
    new Chart(agingCtx, {
        type: 'doughnut',
        data: {
            labels: @json($inventoryAging['labels'] ?? []),
            datasets: [{
                data: @json($inventoryAging['data'] ?? []),
                backgroundColor: @json($inventoryAging['colors'] ?? ['#10B981', '#F59E0B', '#EF4444']),
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: { position: 'bottom', labels: { boxWidth: 12, padding: 15 } }
            }
        }
    });

    // Top Products Bar Chart (NEW)
    const topProductsCtx = document.getElementById('topProductsChart').getContext('2d');
    new Chart(topProductsCtx, {
        type: 'bar',
        data: {
            labels: @json($topProducts['labels'] ?? []),
            datasets: [{
                label: 'Total Sales (Rp)',
                data: @json($topProducts['data'] ?? []),
                backgroundColor: [
                    'rgba(16, 185, 129, 0.8)',
                    'rgba(59, 130, 246, 0.8)',
                    'rgba(139, 92, 246, 0.8)',
                    'rgba(249, 115, 22, 0.8)',
                    'rgba(236, 72, 153, 0.8)',
                ],
                borderColor: [
                    'rgb(16, 185, 129)',
                    'rgb(59, 130, 246)',
                    'rgb(139, 92, 246)',
                    'rgb(249, 115, 22)',
                    'rgb(236, 72, 153)',
                ],
                borderWidth: 2,
                borderRadius: 8,
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            indexAxis: 'y',
            plugins: {
                legend: { display: false },
                tooltip: {
                    callbacks: {
                        title: function(context) {
                            const names = @json($topProducts['names'] ?? []);
                            return names[context[0].dataIndex] || context[0].label;
                        },
                        label: function(context) {
                            return 'Sales: Rp ' + context.raw.toLocaleString('id-ID');
                        }
                    }
                }
            },
            scales: {
                x: {
                    beginAtZero: true,
                    ticks: {
                        callback: function(value) {
                            if (value >= 1000000000) return 'Rp ' + (value / 1000000000).toFixed(1) + 'B';
                            if (value >= 1000000) return 'Rp ' + (value / 1000000).toFixed(0) + 'M';
                            return 'Rp ' + value.toLocaleString('id-ID');
                        }
                    }
                }
            }
        }
    });
});
</script>
@endsection
