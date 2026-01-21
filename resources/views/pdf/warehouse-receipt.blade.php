@extends('pdf.layout')

@section('content')
{{-- Supplier/Farmer Info --}}
<div class="info-section">
    <div class="info-box">
        <div class="info-label">Received From</div>
        <div class="info-value">
            <strong>{{ $stockIn->supplier->name ?? 'Supplier' }}</strong><br>
            {{ $stockIn->supplier->address ?? '' }}<br>
            @if($stockIn->supplier->phone ?? false)Tel: {{ $stockIn->supplier->phone }}@endif
        </div>
    </div>
    <div class="info-box">
        <div class="info-label">Receipt Details</div>
        <div class="info-value">
            <strong>Date:</strong> {{ $stockIn->date?->format('d M Y') ?? now()->format('d M Y') }}<br>
            <strong>Warehouse:</strong> {{ $stockIn->warehouse->name ?? '-' }}<br>
            <strong>Reference:</strong> {{ $stockIn->transaction_code }}
        </div>
    </div>
</div>

{{-- Items Received --}}
<table class="items-table">
    <thead>
        <tr>
            <th style="width: 5%;">#</th>
            <th style="width: 30%;">Product</th>
            <th style="width: 15%;">Batch ID</th>
            <th class="text-center" style="width: 10%;">Qty Received</th>
            <th style="width: 15%;">Grade / Quality</th>
            <th style="width: 15%;">Expiry Date</th>
            <th style="width: 10%;">Status</th>
        </tr>
    </thead>
    <tbody>
        @foreach($stockIn->details as $index => $detail)
        <tr>
            <td>{{ $index + 1 }}</td>
            <td>
                <strong>{{ $detail->batch->product->name ?? $detail->product->name ?? 'Product' }}</strong>
                @if($detail->batch->product->sku ?? false)
                    <br><span class="text-muted">SKU: {{ $detail->batch->product->sku }}</span>
                @endif
            </td>
            <td>{{ $detail->batch->batch_number ?? '-' }}</td>
            <td class="text-center">{{ number_format($detail->quantity) }}</td>
            <td>
                @if($detail->batch->grade ?? false)
                    <strong>{{ $detail->batch->grade }}</strong>
                @else
                    Standard
                @endif
                @if($detail->batch->coa_reference ?? false)
                    <br><span class="text-muted">COA: {{ $detail->batch->coa_reference }}</span>
                @endif
            </td>
            <td>
                @if($detail->batch->expiry_date ?? false)
                    {{ $detail->batch->expiry_date->format('d M Y') }}
                @else
                    N/A
                @endif
            </td>
            <td>
                @php
                    $status = $detail->batch->status ?? 'active';
                    $statusColor = match($status) {
                        'active' => '#059669',
                        'quarantine' => '#d97706',
                        'expired' => '#dc2626',
                        default => '#6b7280'
                    };
                @endphp
                <span style="color: {{ $statusColor }}; font-weight: 600; text-transform: uppercase; font-size: 8pt;">
                    {{ ucfirst($status) }}
                </span>
            </td>
        </tr>
        @endforeach
    </tbody>
</table>

{{-- Summary --}}
<table style="width: 100%; margin-top: 10px;">
    <tr>
        <td style="width: 60%;"></td>
        <td style="width: 40%;">
            <table class="items-table" style="margin-bottom: 0;">
                <tr>
                    <td style="font-weight: 600; background: #f9fafb;">Total Items</td>
                    <td class="text-right">{{ $stockIn->details->count() }}</td>
                </tr>
                <tr>
                    <td style="font-weight: 600; background: #f9fafb;">Total Quantity</td>
                    <td class="text-right">{{ number_format($stockIn->details->sum('quantity')) }}</td>
                </tr>
            </table>
        </td>
    </tr>
</table>

{{-- Notes --}}
@if($stockIn->notes)
<div style="margin-top: 20px; padding: 10px; background: #f9fafb; border: 1px solid #e5e7eb;">
    <div class="info-label">Remarks</div>
    <div class="info-value">{{ $stockIn->notes }}</div>
</div>
@endif

{{-- Receipt Confirmation --}}
<div style="margin-top: 30px; padding: 15px; background: #ecfdf5; border: 1px solid #a7f3d0; border-radius: 4px;">
    <div style="font-weight: 600; color: #059669; margin-bottom: 5px;">✓ Goods Received in Good Condition</div>
    <div style="font-size: 8pt; color: #6b7280;">
        This receipt confirms that the above items have been received and inspected at {{ $stockIn->warehouse->name ?? 'the warehouse' }} 
        on {{ $stockIn->date?->format('d M Y') ?? now()->format('d M Y') }}.
    </div>
</div>
@endsection
