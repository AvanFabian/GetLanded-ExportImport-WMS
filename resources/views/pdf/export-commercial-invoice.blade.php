@extends('pdf.layout')

@section('content')
{{-- Buyer & Shipment Info --}}
<div class="info-section">
    <div class="info-box">
        <div class="info-label">Consignee (Buyer)</div>
        <div class="info-value">
            <strong>{{ $shipment->salesOrder->customer->name ?? 'N/A' }}</strong><br>
            {{ $shipment->salesOrder->customer->address ?? '' }}<br>
            @if($shipment->salesOrder->customer->phone ?? false)Tel: {{ $shipment->salesOrder->customer->phone }}@endif
            @if($shipment->destination_country)<br>Country: {{ $shipment->destination_country }}@endif
        </div>
    </div>
    <div class="info-box">
        <div class="info-label">Shipment & Trade Details</div>
        <div class="info-value">
            <strong>Invoice Date:</strong> {{ $shipment->shipment_date?->format('d M Y') ?? now()->format('d M Y') }}<br>
            <strong>SO Ref:</strong> {{ $shipment->salesOrder->so_number ?? '-' }}<br>
            <strong>B/L No:</strong> {{ $shipment->bill_of_lading ?? '-' }}<br>
            <strong>Incoterm:</strong> {{ $shipment->incoterm ?? '-' }}<br>
            <strong>Currency:</strong> {{ $shipment->salesOrder->currency_code ?? $shipment->currency_code ?? 'USD' }}
        </div>
    </div>
</div>

{{-- Shipping Route --}}
<div style="margin-bottom: 15px; padding: 10px; background: #eff6ff; border: 1px solid #bfdbfe;">
    <table style="width: 100%; border: none;">
        <tr>
            <td style="border: none; width: 28%;"><strong>Vessel:</strong> {{ $shipment->vessel_name ?? '-' }} / {{ $shipment->voyage_number ?? '-' }}</td>
            <td style="border: none; width: 24%;"><strong>Port of Loading:</strong> {{ $shipment->port_of_loading ?? '-' }}</td>
            <td style="border: none; width: 24%;"><strong>Port of Discharge:</strong> {{ $shipment->port_of_discharge ?? '-' }}</td>
            <td style="border: none; width: 24%;"><strong>Carrier:</strong> {{ $shipment->carrier_name ?? '-' }}</td>
        </tr>
    </table>
</div>

{{-- Container Summary --}}
@if($shipment->containers->count() > 0)
<div style="margin-bottom: 15px; padding: 8px 10px; background: #f0fdf4; border: 1px solid #bbf7d0;">
    <strong>Containers:</strong>
    @foreach($shipment->containers as $container)
        {{ $container->container_number }} ({{ $container->container_type }})@if($container->seal_number) — Seal: {{ $container->seal_number }}@endif{{ !$loop->last ? ' | ' : '' }}
    @endforeach
</div>
@endif

{{-- Line Items --}}
@php
    $currency = $shipment->salesOrder->currency_code ?? $shipment->currency_code ?? 'USD';
    $orderItems = $shipment->salesOrder->items ?? collect();
    $totalFob = 0;
@endphp

<table class="items-table">
    <thead>
        <tr>
            <th style="width: 5%;">#</th>
            <th style="width: 28%;">Description of Goods</th>
            <th style="width: 12%;">HS Code</th>
            <th style="width: 10%;">Origin</th>
            <th class="text-center" style="width: 8%;">Qty</th>
            <th class="text-right" style="width: 10%;">Unit Price</th>
            <th class="text-right" style="width: 12%;">Amount ({{ $currency }})</th>
            <th class="text-right" style="width: 10%;">Net Wt (kg)</th>
        </tr>
    </thead>
    <tbody>
        @foreach($orderItems as $index => $item)
        @php
            $subtotal = $item->quantity * $item->unit_price;
            $totalFob += $subtotal;
            $product = $item->product ?? null;
        @endphp
        <tr>
            <td>{{ $index + 1 }}</td>
            <td>
                <strong>{{ $product->name ?? $item->description ?? 'Product' }}</strong>
                @if($product->description ?? false)
                    <br><span class="text-muted">{{ Str::limit($product->description, 60) }}</span>
                @endif
            </td>
            <td>{{ $product->hs_code ?? '-' }}</td>
            <td>{{ $product->origin_country ?? '-' }}</td>
            <td class="text-center">{{ number_format($item->quantity) }} {{ $product->unit ?? 'pcs' }}</td>
            <td class="text-right">{{ number_format($item->unit_price, 2) }}</td>
            <td class="text-right">{{ number_format($subtotal, 2) }}</td>
            <td class="text-right">{{ number_format(($product->net_weight ?? 0) * $item->quantity, 2) }}</td>
        </tr>
        @endforeach
    </tbody>
</table>

{{-- Financial Summary --}}
<div class="summary-section clearfix">
    <table class="summary-table">
        <tr>
            <td>FOB Value</td>
            <td>{{ $currency }} {{ number_format($totalFob, 2) }}</td>
        </tr>
        @if($shipment->freight_cost > 0)
        <tr>
            <td>Freight</td>
            <td>{{ $currency }} {{ number_format($shipment->freight_cost, 2) }}</td>
        </tr>
        @endif
        @if($shipment->insurance_cost > 0)
        <tr>
            <td>Insurance</td>
            <td>{{ $currency }} {{ number_format($shipment->insurance_cost, 2) }}</td>
        </tr>
        @endif
        <tr class="grand-total">
            <td>Total CIF</td>
            <td>{{ $currency }} {{ number_format($totalFob + ($shipment->freight_cost ?? 0) + ($shipment->insurance_cost ?? 0), 2) }}</td>
        </tr>
        @if($shipment->salesOrder->exchange_rate_at_transaction ?? false)
        <tr>
            <td>Exchange Rate</td>
            <td>{{ number_format($shipment->salesOrder->exchange_rate_at_transaction, 2) }}</td>
        </tr>
        @endif
    </table>
</div>

{{-- Declaration --}}
<div style="margin-top: 20px; padding: 10px; background: #f9fafb; border: 1px solid #e5e7eb;">
    <div class="info-label">Declaration</div>
    <div class="info-value" style="font-size: 8pt;">
        We declare that the goods described above are of {{ $company->country ?? 'Indonesian' }} origin
        and that the information stated herein is true and correct in all particulars.
    </div>
</div>

{{-- Notes --}}
@if($shipment->notes)
<div style="margin-top: 10px; padding: 10px; background: #f9fafb; border: 1px solid #e5e7eb;">
    <div class="info-label">Remarks</div>
    <div class="info-value">{{ $shipment->notes }}</div>
</div>
@endif
@endsection
