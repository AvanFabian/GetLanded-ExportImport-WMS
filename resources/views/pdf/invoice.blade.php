<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <title>Invoice - {{ $order->so_number }}</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }

        body {
            font-family: 'DejaVu Sans', Arial, sans-serif;
            font-size: 10pt;
            line-height: 1.5;
            color: #1f2937;
        }

        .page { padding: 20px; }

        /* ─── Header ─── */
        .header {
            display: table;
            width: 100%;
            margin-bottom: 18px;
            padding-bottom: 14px;
            border-bottom: 3px solid #1e40af;
        }
        .header-left  { display: table-cell; width: 60%; vertical-align: top; }
        .header-right { display: table-cell; width: 40%; vertical-align: top; text-align: right; }

        .company-name  { font-size: 16pt; font-weight: 700; color: #1e40af; margin-bottom: 3px; }
        .company-info  { font-size: 8pt; color: #4b5563; line-height: 1.6; }

        .doc-title  { font-size: 20pt; font-weight: 700; color: #1f2937; margin-bottom: 4px; }
        .doc-number { font-size: 11pt; font-weight: 600; color: #2563eb; }

        @php
            $statusColors = [
                'unpaid'  => ['bg' => '#fef2f2', 'color' => '#dc2626', 'border' => '#fecaca', 'label' => 'UNPAID'],
                'partial' => ['bg' => '#fffbeb', 'color' => '#d97706', 'border' => '#fde68a', 'label' => 'PARTIAL'],
                'paid'    => ['bg' => '#f0fdf4', 'color' => '#16a34a', 'border' => '#bbf7d0', 'label' => 'PAID'],
            ];
            $payStatus = $order->payment_status ?? 'unpaid';
            $payColor  = $statusColors[$payStatus] ?? $statusColors['unpaid'];
        @endphp

        .status-badge {
            display: inline-block;
            padding: 4px 14px;
            border-radius: 4px;
            font-size: 9pt;
            font-weight: 700;
            letter-spacing: 1px;
        }

        /* ─── Info Section (2-column) ─── */
        .info-section {
            display: table;
            width: 100%;
            margin-bottom: 16px;
        }
        .info-box {
            display: table-cell;
            width: 50%;
            vertical-align: top;
            padding: 10px 12px;
            background: #f9fafb;
            border: 1px solid #e5e7eb;
        }
        .info-box:first-child { border-right: none; }
        .info-label {
            font-size: 7.5pt;
            font-weight: 700;
            color: #6b7280;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            margin-bottom: 4px;
        }
        .info-value { font-size: 10pt; color: #1f2937; }
        .info-row   { margin-bottom: 4px; }
        .info-row span.lbl { font-weight: 600; color: #374151; }

        /* ─── Items Table ─── */
        table.items-table {
            width: 100%;
            border-collapse: collapse;
            margin: 12px 0 20px;
        }
        table.items-table thead th {
            background: #1e40af;
            color: #fff;
            font-weight: 600;
            font-size: 8.5pt;
            text-transform: uppercase;
            letter-spacing: 0.4px;
            padding: 9px 8px;
            text-align: left;
            border: 1px solid #1e40af;
        }
        table.items-table thead th.r { text-align: right; }
        table.items-table thead th.c { text-align: center; }
        table.items-table tbody td {
            padding: 7px 8px;
            border: 1px solid #e5e7eb;
            font-size: 9pt;
        }
        table.items-table tbody tr:nth-child(even) td { background: #f9fafb; }
        table.items-table tbody td.r { text-align: right; }
        table.items-table tbody td.c { text-align: center; }

        /* ─── Totals ─── */
        .totals-wrap { width: 100%; overflow: hidden; margin-bottom: 20px; }
        .totals-table {
            float: right;
            width: 300px;
            border-collapse: collapse;
        }
        .totals-table td {
            padding: 6px 10px;
            border: 1px solid #e5e7eb;
            font-size: 9.5pt;
        }
        .totals-table td:first-child { font-weight: 600; background: #f9fafb; }
        .totals-table td:last-child  { text-align: right; }
        .totals-table tr.grand-total td {
            background: #1e40af;
            color: #fff;
            font-weight: 700;
            font-size: 11pt;
            border-color: #1e40af;
        }

        /* ─── Payment Status Banner ─── */
        .payment-banner {
            clear: both;
            padding: 10px 14px;
            border-radius: 4px;
            margin-bottom: 14px;
            font-size: 9pt;
        }

        /* ─── Notes ─── */
        .notes-section {
            clear: both;
            padding: 10px 14px;
            background: #f9fafb;
            border: 1px solid #e5e7eb;
            border-radius: 4px;
            margin-bottom: 14px;
        }
        .notes-label { font-weight: 700; font-size: 8.5pt; color: #374151; margin-bottom: 4px; text-transform: uppercase; }

        /* ─── Signature ─── */
        .signature-section  { display: table; width: 100%; margin-top: 40px; page-break-inside: avoid; }
        .signature-box      { display: table-cell; width: 33%; text-align: center; padding: 10px; }
        .signature-line     { border-bottom: 1px solid #1f2937; width: 140px; margin: 50px auto 8px; }
        .signature-label    { font-size: 8.5pt; font-weight: 600; color: #374151; }

        /* ─── Footer ─── */
        .footer {
            margin-top: 30px;
            padding-top: 10px;
            border-top: 2px solid #2563eb;
            font-size: 7.5pt;
            color: #6b7280;
        }
    </style>
</head>
<body>
<div class="page">

    {{-- ── Header ── --}}
    <div class="header">
        <div class="header-left">
            <div class="company-name">{{ $order->company->name ?? config('app.name') }}</div>
            <div class="company-info">
                @if($order->company->address ?? false){{ $order->company->address }}<br>@endif
                @if($order->company->phone ?? false)Tel: {{ $order->company->phone }}@endif
                @if($order->company->email ?? false) | {{ $order->company->email }}@endif
                @if($order->company->tax_id ?? false)<br>Tax ID / NPWP: {{ $order->company->tax_id }}@endif
            </div>
        </div>
        <div class="header-right">
            <div class="doc-title">INVOICE</div>
            <div class="doc-number">{{ $order->so_number }}</div>
            <div style="margin-top:8px;">
                @php
                    $payStatus = $order->payment_status ?? 'unpaid';
                    $payColors = [
                        'unpaid'  => 'background:#fef2f2; color:#dc2626; border:1px solid #fecaca;',
                        'partial' => 'background:#fffbeb; color:#d97706; border:1px solid #fde68a;',
                        'paid'    => 'background:#f0fdf4; color:#16a34a; border:1px solid #bbf7d0;',
                    ];
                    $payLabels = ['unpaid' => 'UNPAID', 'partial' => 'PARTIAL PAYMENT', 'paid' => 'PAID'];
                    $payStyle  = $payColors[$payStatus] ?? $payColors['unpaid'];
                    $payLabel  = $payLabels[$payStatus] ?? strtoupper($payStatus);
                @endphp
                <span class="status-badge" style="{{ $payStyle }}">{{ $payLabel }}</span>
            </div>
        </div>
    </div>

    {{-- ── Bill To / Invoice Details ── --}}
    <div class="info-section">
        <div class="info-box">
            <div class="info-label">Bill To</div>
            <div class="info-value">
                <div class="info-row"><strong>{{ $order->customer->name ?? '-' }}</strong></div>
                @if($order->customer->address ?? false)
                    <div class="info-row">{{ $order->customer->address }}</div>
                @endif
                @if($order->customer->phone ?? false)
                    <div class="info-row"><span class="lbl">Tel:</span> {{ $order->customer->phone }}</div>
                @endif
                @if($order->customer->email ?? false)
                    <div class="info-row"><span class="lbl">Email:</span> {{ $order->customer->email }}</div>
                @endif
                @if($order->customer->tax_id ?? false)
                    <div class="info-row"><span class="lbl">NPWP:</span> {{ $order->customer->tax_id }}</div>
                @endif
            </div>
        </div>
        <div class="info-box">
            <div class="info-label">Invoice Details</div>
            <div class="info-value">
                <div class="info-row"><span class="lbl">Invoice Date:</span> {{ \Carbon\Carbon::parse($order->order_date)->format('d M Y') }}</div>
                @if($order->delivery_date)
                    <div class="info-row"><span class="lbl">Delivery Date:</span> {{ \Carbon\Carbon::parse($order->delivery_date)->format('d M Y') }}</div>
                @endif
                @if($order->due_date ?? false)
                    <div class="info-row"><span class="lbl">Due Date:</span> {{ \Carbon\Carbon::parse($order->due_date)->format('d M Y') }}</div>
                @endif
                <div class="info-row"><span class="lbl">Currency:</span> {{ $order->currency_code ?? 'IDR' }}</div>
                @if(($order->currency_code ?? 'IDR') !== 'IDR')
                    <div class="info-row"><span class="lbl">Exchange Rate:</span> 1 {{ $order->currency_code }} = {{ number_format($order->exchange_rate_at_transaction ?? 1, 2) }} IDR</div>
                @endif
                <div class="info-row"><span class="lbl">Warehouse:</span> {{ $order->warehouse->name ?? '-' }}</div>
            </div>
        </div>
    </div>

    {{-- ── Items Table ── --}}
    @php
        $currency = $order->currency_code ?? 'IDR';
        $symbol   = $currency === 'IDR' ? 'Rp' : $currency;
    @endphp
    <table class="items-table">
        <thead>
            <tr>
                <th style="width:5%;" class="c">#</th>
                <th style="width:42%;">Product / Description</th>
                <th style="width:8%;" class="c">Unit</th>
                <th style="width:10%;" class="c">Qty</th>
                <th style="width:17.5%;" class="r">Unit Price ({{ $currency }})</th>
                <th style="width:17.5%;" class="r">Subtotal ({{ $currency }})</th>
            </tr>
        </thead>
        <tbody>
            @forelse($order->items as $i => $item)
            <tr>
                <td class="c">{{ $i + 1 }}</td>
                <td>
                    <strong>{{ $item->product->name ?? $item->product_id }}</strong>
                    @if($item->product->code ?? false)
                        <br><span style="font-size:8pt; color:#6b7280;">SKU: {{ $item->product->code }}</span>
                    @endif
                    @if($item->product->hs_code ?? false)
                        <br><span style="font-size:8pt; color:#6b7280;">HS: {{ $item->product->hs_code }}</span>
                    @endif
                </td>
                <td class="c">{{ $item->product->unit ?? '-' }}</td>
                <td class="c">{{ number_format($item->quantity) }}</td>
                <td class="r">{{ number_format($item->unit_price, 2) }}</td>
                <td class="r">{{ number_format($item->subtotal, 2) }}</td>
            </tr>
            @empty
            <tr>
                <td colspan="6" class="c" style="color:#9ca3af; padding:20px;">No items found</td>
            </tr>
            @endforelse
        </tbody>
    </table>

    {{-- ── Totals ── --}}
    <div class="totals-wrap">
        <table class="totals-table">
            <tr>
                <td>Subtotal</td>
                <td>{{ $symbol }} {{ number_format($order->subtotal ?? 0, 2) }}</td>
            </tr>
            @if(($order->discount ?? 0) > 0)
            <tr>
                <td>Discount</td>
                <td style="color:#dc2626;">- {{ $symbol }} {{ number_format($order->discount, 2) }}</td>
            </tr>
            @endif
            @if(($order->tax ?? 0) > 0)
            <tr>
                <td>VAT / PPN (11%)</td>
                <td>{{ $symbol }} {{ number_format($order->tax, 2) }}</td>
            </tr>
            @endif
            <tr class="grand-total">
                <td>TOTAL</td>
                <td>{{ $symbol }} {{ number_format($order->total ?? 0, 2) }}</td>
            </tr>
        </table>
    </div>

    {{-- ── Payment Info Banner ── --}}
    @if($payStatus !== 'unpaid')
    <div class="payment-banner" style="{{ $payStyle }}">
        <strong>Payment Information:</strong>
        Amount Paid: <strong>{{ $symbol }} {{ number_format($order->amount_paid ?? 0, 2) }}</strong>
        @if($payStatus === 'partial')
            | Remaining: <strong>{{ $symbol }} {{ number_format(($order->total ?? 0) - ($order->amount_paid ?? 0), 2) }}</strong>
        @endif
    </div>
    @endif

    {{-- ── Notes ── --}}
    @if($order->notes)
    <div class="notes-section">
        <div class="notes-label">Notes / Remarks</div>
        <div style="font-size:9pt; color:#374151;">{{ $order->notes }}</div>
    </div>
    @endif

    {{-- ── Signature ── --}}
    <div class="signature-section">
        <div class="signature-box">
            <div class="signature-line"></div>
            <div class="signature-label">Authorized Signature</div>
        </div>
        <div class="signature-box">
            <div class="signature-line"></div>
            <div class="signature-label">Finance / Accounting</div>
        </div>
        <div class="signature-box">
            <div class="signature-line"></div>
            <div class="signature-label">Received By (Customer)</div>
        </div>
    </div>

    {{-- ── Footer ── --}}
    <div class="footer">
        <div style="display:table; width:100%;">
            <div style="display:table-cell; width:70%;">
                @if($order->company->bank_name ?? false)
                    <strong>Payment to:</strong> {{ $order->company->bank_name }}
                    @if($order->company->bank_account_number ?? false)
                        | Acc: {{ $order->company->bank_account_number }}
                    @endif
                    @if($order->company->bank_swift_code ?? false)
                        | SWIFT: {{ $order->company->bank_swift_code }}
                    @endif
                    <br>
                @endif
                This document is computer-generated. &mdash; Printed: {{ now()->format('d M Y, H:i') }}
            </div>
            <div style="display:table-cell; width:30%; text-align:right; vertical-align:bottom;">
                <span style="color:#9ca3af;">Page <span class="pagenum"></span></span>
            </div>
        </div>
    </div>

</div>
</body>
</html>
