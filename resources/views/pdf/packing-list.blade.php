@extends('pdf.layout')

{{-- 
    PACKING LIST - NO PRICING DATA ALLOWED
    This document must not contain any price, cost, or monetary values.
--}}

@section('content')
{{-- Shipment Info --}}
<div class="info-section">
    <div class="info-box">
        <div class="info-label">Ship To</div>
        <div class="info-value">
            <strong>{{ $stockOut->customer ?? 'Consignee' }}</strong><br>
            {{ $stockOut->delivery_address ?? '' }}
        </div>
    </div>
    <div class="info-box">
        <div class="info-label">Shipment Details</div>
        <div class="info-value">
            <strong>Date:</strong> {{ $stockOut->date?->format('d M Y') ?? now()->format('d M Y') }}<br>
            <strong>Warehouse:</strong> {{ $stockOut->warehouse->name ?? '-' }}<br>
            <strong>Reference:</strong> {{ $stockOut->transaction_code }}
        </div>
    </div>
</div>

{{-- Package Summary --}}
<div style="margin-bottom: 15px; padding: 10px; background: #eff6ff; border: 1px solid #bfdbfe; border-radius: 4px;">
    <strong>Total Packages:</strong> 
    @php
        $totalPackages = 0;
        $totalNetWeight = 0;
        $totalGrossWeight = 0;
        foreach($stockOut->details as $detail) {
            // Handle both batch-based and direct product details
            $product = $detail->batch->product ?? $detail->product ?? null;
            $totalPackages += $detail->batch->package_count ?? 1;
            $totalNetWeight += $detail->batch->net_weight ?? $product->net_weight ?? $detail->quantity;
            $totalGrossWeight += $detail->batch->gross_weight ?? $product->gross_weight ?? $detail->quantity;
        }
    @endphp
    {{ number_format($totalPackages) }} | 
    <strong>Net Weight:</strong> {{ number_format($totalNetWeight, 2) }} kg | 
    <strong>Gross Weight:</strong> {{ number_format($totalGrossWeight, 2) }} kg
</div>

{{-- Items Table (NO PRICES) --}}
<table class="items-table">
    <thead>
        <tr>
            <th style="width: 5%;">#</th>
            <th style="width: 35%;">Product</th>
            <th style="width: 15%;">Batch ID</th>
            <th class="text-center" style="width: 10%;">Qty</th>
            <th class="text-right" style="width: 10%;">Net Wt (kg)</th>
            <th class="text-right" style="width: 10%;">Gross Wt (kg)</th>
            <th style="width: 15%;">HS Code</th>
        </tr>
    </thead>
    <tbody>
        @foreach($stockOut->details as $index => $detail)
        @php
            // Handle both batch-based and direct product details
            $product = $detail->batch->product ?? $detail->product ?? null;
            $batchNumber = $detail->batch->batch_number ?? '-';
            $netWeight = $detail->batch->net_weight ?? $product->net_weight ?? $detail->quantity;
            $grossWeight = $detail->batch->gross_weight ?? $product->gross_weight ?? $detail->quantity;
        @endphp
        <tr>
            <td>{{ $index + 1 }}</td>
            <td>
                <strong>{{ $product->name ?? 'Product' }}</strong>
                @if($product->origin_country ?? false)
                    <br><span class="text-muted">Origin: {{ $product->origin_country }}</span>
                @endif
            </td>
            <td>{{ $batchNumber }}</td>
            <td class="text-center">{{ number_format($detail->quantity) }}</td>
            <td class="text-right">{{ number_format($netWeight, 2) }}</td>
            <td class="text-right">{{ number_format($grossWeight, 2) }}</td>
            <td>{{ $product->hs_code ?? '-' }}</td>
        </tr>
        @endforeach
    </tbody>
</table>

{{-- Totals Summary --}}
<table style="width: 100%; margin-top: 10px;">
    <tr>
        <td style="width: 60%;"></td>
        <td style="width: 40%;">
            <table class="items-table" style="margin-bottom: 0;">
                <tr>
                    <td style="font-weight: 600; background: #f9fafb;">Total Quantity</td>
                    <td class="text-right">{{ number_format($stockOut->details->sum('quantity')) }}</td>
                </tr>
                <tr>
                    <td style="font-weight: 600; background: #f9fafb;">Total Packages</td>
                    <td class="text-right">{{ number_format($totalPackages) }}</td>
                </tr>
                <tr>
                    <td style="font-weight: 600; background: #f9fafb;">Total Net Weight</td>
                    <td class="text-right">{{ number_format($totalNetWeight, 2) }} kg</td>
                </tr>
                <tr>
                    <td style="font-weight: 600; background: #f9fafb;">Total Gross Weight</td>
                    <td class="text-right">{{ number_format($totalGrossWeight, 2) }} kg</td>
                </tr>
            </table>
        </td>
    </tr>
</table>

{{-- Notes --}}
@if($stockOut->notes)
<div style="margin-top: 20px; padding: 10px; background: #f9fafb; border: 1px solid #e5e7eb;">
    <div class="info-label">Remarks</div>
    <div class="info-value">{{ $stockOut->notes }}</div>
</div>
@endif
@endsection
