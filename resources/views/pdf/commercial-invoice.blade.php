@extends('pdf.layout')

@section('content')
{{-- Customer & Order Info --}}
<div class="info-section">
    <div class="info-box">
        <div class="info-label">Bill To</div>
        <div class="info-value">
            <strong>{{ $order->customer->name ?? 'N/A' }}</strong><br>
            {{ $order->customer->address ?? '' }}<br>
            @if($order->customer->phone ?? false)Tel: {{ $order->customer->phone }}@endif
        </div>
    </div>
    <div class="info-box">
        <div class="info-label">Invoice Details</div>
        <div class="info-value">
            <strong>Date:</strong> {{ $order->order_date?->format('d M Y') ?? now()->format('d M Y') }}<br>
            <strong>Delivery:</strong> {{ $order->delivery_date?->format('d M Y') ?? '-' }}<br>
            <strong>Currency:</strong> {{ $order->currency_code ?? 'USD' }}<br>
            @if($order->incoterms ?? false)<strong>Incoterms:</strong> {{ $order->incoterms }}@endif
        </div>
    </div>
</div>

{{-- Line Items --}}
<table class="items-table">
    <thead>
        <tr>
            <th style="width: 5%;">#</th>
            <th style="width: 40%;">Description</th>
            <th class="text-center" style="width: 10%;">Qty</th>
            <th class="text-right" style="width: 15%;">Unit Price</th>
            <th class="text-right" style="width: 15%;">Subtotal ({{ $order->currency_code ?? 'USD' }})</th>
            <th class="text-right" style="width: 15%;">Subtotal ({{ $company->base_currency_code ?? 'IDR' }})</th>
        </tr>
    </thead>
    <tbody>
        @foreach($order->items as $index => $item)
        <tr>
            <td>{{ $index + 1 }}</td>
            <td>
                <strong>{{ $item->product->name ?? 'Product' }}</strong>
                @if($item->product->hs_code ?? false)
                    <br><span class="text-muted">HS: {{ $item->product->hs_code }}</span>
                @endif
            </td>
            <td class="text-center">{{ number_format($item->quantity) }}</td>
            <td class="text-right">{{ number_format($item->unit_price, 2) }}</td>
            <td class="text-right">{{ number_format($item->subtotal, 2) }}</td>
            <td class="text-right">
                @php
                    $baseAmount = $item->subtotal * ($order->exchange_rate_at_transaction ?? 1);
                @endphp
                {{ number_format($baseAmount, 0) }}
            </td>
        </tr>
        @endforeach
    </tbody>
</table>

{{-- Summary --}}
<div class="summary-section clearfix">
    <table class="summary-table">
        <tr>
            <td>Subtotal</td>
            <td>{{ $order->currency_code ?? 'USD' }} {{ number_format($order->subtotal ?? 0, 2) }}</td>
        </tr>
        @if($order->tax > 0)
        <tr>
            <td>Tax ({{ $company->default_vat_percentage ?? 10 }}%)</td>
            <td>{{ $order->currency_code ?? 'USD' }} {{ number_format($order->tax, 2) }}</td>
        </tr>
        @endif
        @if($order->discount > 0)
        <tr>
            <td>Discount</td>
            <td>- {{ $order->currency_code ?? 'USD' }} {{ number_format($order->discount, 2) }}</td>
        </tr>
        @endif
        @if($order->transaction_fees > 0)
        <tr>
            <td>Transaction Fees</td>
            <td>- {{ $order->fee_currency_code ?? 'USD' }} {{ number_format($order->transaction_fees, 2) }}</td>
        </tr>
        @endif
        <tr class="grand-total">
            <td>Grand Total</td>
            <td>{{ $order->currency_code ?? 'USD' }} {{ number_format($order->total ?? 0, 2) }}</td>
        </tr>
        <tr>
            <td>Equivalent</td>
            <td>
                @php
                    $baseTotal = ($order->total ?? 0) * ($order->exchange_rate_at_transaction ?? 1);
                @endphp
                {{ $company->base_currency_code ?? 'IDR' }} {{ number_format($baseTotal, 0) }}
            </td>
        </tr>
    </table>
</div>

{{-- Notes --}}
@if($order->notes)
<div style="margin-top: 20px; padding: 10px; background: #f9fafb; border: 1px solid #e5e7eb;">
    <div class="info-label">Notes</div>
    <div class="info-value">{{ $order->notes }}</div>
</div>
@endif
@endsection
