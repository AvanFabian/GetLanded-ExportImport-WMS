@extends('pdf.layout')

{{--
    EXPORT PACKING LIST - Per-container breakdown
    NO PRICING DATA in this document.
--}}

@section('content')
{{-- Shipment Info --}}
<div class="info-section">
    <div class="info-box">
        <div class="info-label">Consignee</div>
        <div class="info-value">
            <strong>{{ $shipment->salesOrder->customer->name ?? 'N/A' }}</strong><br>
            {{ $shipment->salesOrder->customer->address ?? '' }}<br>
            @if($shipment->destination_country)Country: {{ $shipment->destination_country }}@endif
        </div>
    </div>
    <div class="info-box">
        <div class="info-label">Shipment Details</div>
        <div class="info-value">
            <strong>Date:</strong> {{ $shipment->shipment_date?->format('d M Y') ?? now()->format('d M Y') }}<br>
            <strong>Vessel:</strong> {{ $shipment->vessel_name ?? '-' }} / {{ $shipment->voyage_number ?? '-' }}<br>
            <strong>B/L No:</strong> {{ $shipment->bill_of_lading ?? '-' }}<br>
            <strong>Port of Loading:</strong> {{ $shipment->port_of_loading ?? '-' }}<br>
            <strong>Port of Discharge:</strong> {{ $shipment->port_of_discharge ?? '-' }}
        </div>
    </div>
</div>

@php
    $grandTotalQty = 0;
    $grandTotalNetWeight = 0;
    $grandTotalGrossWeight = 0;
    $grandTotalCbm = 0;
@endphp

{{-- Per-Container Breakdown --}}
@if($shipment->containers->count() > 0)
    @foreach($shipment->containers as $container)
    <div style="margin-bottom: 20px;">
        {{-- Container Header --}}
        <div style="padding: 8px 10px; background: #1e40af; color: white; font-weight: 600; font-size: 10pt;">
            📦 Container: {{ $container->container_number }}
            ({{ $container->container_type }})
            @if($container->seal_number) — Seal No: {{ $container->seal_number }} @endif
        </div>

        <table class="items-table" style="margin-bottom: 0;">
            <thead>
                <tr>
                    <th style="width: 5%;">#</th>
                    <th style="width: 25%;">Product</th>
                    <th style="width: 12%;">HS Code</th>
                    <th style="width: 10%;">Origin</th>
                    <th style="width: 12%;">Marks & Nos</th>
                    <th class="text-center" style="width: 8%;">Qty</th>
                    <th class="text-right" style="width: 10%;">Net Wt (kg)</th>
                    <th class="text-right" style="width: 10%;">Gross Wt (kg)</th>
                    <th class="text-right" style="width: 8%;">CBM</th>
                </tr>
            </thead>
            <tbody>
                @php $containerNetWt = 0; $containerGrossWt = 0; $containerCbm = 0; $containerQty = 0; @endphp
                @forelse($container->items as $index => $item)
                @php
                    $product = $item->product ?? null;
                    $netWt = $item->weight_kg ?? ($product->net_weight ?? 0) * $item->quantity;
                    $grossWt = $netWt * 1.05; // Approx packaging weight
                    $cbm = $item->cbm ?? ($product->cbm_volume ?? 0) * $item->quantity;
                    $containerNetWt += $netWt;
                    $containerGrossWt += $grossWt;
                    $containerCbm += $cbm;
                    $containerQty += $item->quantity;
                @endphp
                <tr>
                    <td>{{ $index + 1 }}</td>
                    <td>
                        <strong>{{ $product->name ?? 'Product' }}</strong>
                        @if($product->code ?? false)
                            <br><span class="text-muted">{{ $product->code }}</span>
                        @endif
                    </td>
                    <td>{{ $product->hs_code ?? '-' }}</td>
                    <td>{{ $product->origin_country ?? '-' }}</td>
                    <td>{{ $item->marks_numbers ?? '-' }}</td>
                    <td class="text-center">{{ number_format($item->quantity) }}</td>
                    <td class="text-right">{{ number_format($netWt, 2) }}</td>
                    <td class="text-right">{{ number_format($grossWt, 2) }}</td>
                    <td class="text-right">{{ number_format($cbm, 3) }}</td>
                </tr>
                @empty
                <tr>
                    <td colspan="9" class="text-center text-muted" style="padding: 15px;">No items in this container</td>
                </tr>
                @endforelse
            </tbody>
            <tfoot>
                <tr style="background: #f3f4f6; font-weight: 600;">
                    <td colspan="5">Container Total</td>
                    <td class="text-center">{{ number_format($containerQty) }}</td>
                    <td class="text-right">{{ number_format($containerNetWt, 2) }}</td>
                    <td class="text-right">{{ number_format($containerGrossWt, 2) }}</td>
                    <td class="text-right">{{ number_format($containerCbm, 3) }}</td>
                </tr>
            </tfoot>
        </table>

        {{-- Container Capacity --}}
        <div style="padding: 6px 10px; background: #f0fdf4; border: 1px solid #bbf7d0; font-size: 8pt;">
            <strong>Capacity:</strong>
            Weight: {{ number_format($containerGrossWt, 0) }} / {{ number_format($container->max_weight_kg ?? 28000, 0) }} kg
            ({{ $container->max_weight_kg ? number_format(($containerGrossWt / $container->max_weight_kg) * 100, 0) : '-' }}%)
            &nbsp;|&nbsp;
            Volume: {{ number_format($containerCbm, 1) }} / {{ number_format($container->max_cbm ?? 33.2, 1) }} m³
            ({{ $container->max_cbm ? number_format(($containerCbm / $container->max_cbm) * 100, 0) : '-' }}%)
        </div>
    </div>

    @php
        $grandTotalQty += $containerQty;
        $grandTotalNetWeight += $containerNetWt;
        $grandTotalGrossWeight += $containerGrossWt;
        $grandTotalCbm += $containerCbm;
    @endphp
    @endforeach
@else
    {{-- Fallback: no containers, show SO items directly --}}
    <table class="items-table">
        <thead>
            <tr>
                <th style="width: 5%;">#</th>
                <th style="width: 30%;">Product</th>
                <th style="width: 12%;">HS Code</th>
                <th style="width: 10%;">Origin</th>
                <th class="text-center" style="width: 10%;">Qty</th>
                <th class="text-right" style="width: 12%;">Net Wt (kg)</th>
                <th class="text-right" style="width: 12%;">Gross Wt (kg)</th>
            </tr>
        </thead>
        <tbody>
            @foreach(($shipment->salesOrder->items ?? collect()) as $index => $item)
            @php
                $product = $item->product ?? null;
                $netWt = ($product->net_weight ?? 0) * $item->quantity;
                $grossWt = $netWt * 1.05;
                $grandTotalQty += $item->quantity;
                $grandTotalNetWeight += $netWt;
                $grandTotalGrossWeight += $grossWt;
            @endphp
            <tr>
                <td>{{ $index + 1 }}</td>
                <td>
                    <strong>{{ $product->name ?? 'Product' }}</strong>
                    @if($product->code ?? false)
                        <br><span class="text-muted">{{ $product->code }}</span>
                    @endif
                </td>
                <td>{{ $product->hs_code ?? '-' }}</td>
                <td>{{ $product->origin_country ?? '-' }}</td>
                <td class="text-center">{{ number_format($item->quantity) }}</td>
                <td class="text-right">{{ number_format($netWt, 2) }}</td>
                <td class="text-right">{{ number_format($grossWt, 2) }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>
@endif

{{-- Grand Totals --}}
<div style="margin-top: 10px;">
    <table style="width: 100%;">
        <tr>
            <td style="width: 55%; border: none;"></td>
            <td style="width: 45%; border: none;">
                <table class="items-table" style="margin-bottom: 0;">
                    <tr style="background: #1e40af; color: white; font-weight: 700;">
                        <td style="border-color: #1e40af; color: white;">Grand Total</td>
                        <td style="border-color: #1e40af; color: white;" class="text-right"></td>
                    </tr>
                    <tr>
                        <td style="font-weight: 600; background: #f9fafb;">Total Quantity</td>
                        <td class="text-right">{{ number_format($grandTotalQty) }}</td>
                    </tr>
                    <tr>
                        <td style="font-weight: 600; background: #f9fafb;">Total Containers</td>
                        <td class="text-right">{{ $shipment->containers->count() }}</td>
                    </tr>
                    <tr>
                        <td style="font-weight: 600; background: #f9fafb;">Total Net Weight</td>
                        <td class="text-right">{{ number_format($grandTotalNetWeight, 2) }} kg</td>
                    </tr>
                    <tr>
                        <td style="font-weight: 600; background: #f9fafb;">Total Gross Weight</td>
                        <td class="text-right">{{ number_format($grandTotalGrossWeight, 2) }} kg</td>
                    </tr>
                    @if($grandTotalCbm > 0)
                    <tr>
                        <td style="font-weight: 600; background: #f9fafb;">Total Volume</td>
                        <td class="text-right">{{ number_format($grandTotalCbm, 3) }} m³</td>
                    </tr>
                    @endif
                </table>
            </td>
        </tr>
    </table>
</div>

{{-- Notes --}}
@if($shipment->notes)
<div style="margin-top: 15px; padding: 10px; background: #f9fafb; border: 1px solid #e5e7eb;">
    <div class="info-label">Remarks</div>
    <div class="info-value">{{ $shipment->notes }}</div>
</div>
@endif
@endsection
