<!DOCTYPE html>
<html lang="id">

<head>
   <meta charset="UTF-8">
   <meta name="viewport" content="width=device-width, initial-scale=1.0">
   <title>{{ __('app.tax_invoice') }} - {{ $invoice->invoice_number }}</title>
   <style>
      * {
         margin: 0;
         padding: 0;
         box-sizing: border-box;
      }

      body {
         font-family: 'Arial', sans-serif;
         font-size: 10pt;
         line-height: 1.4;
         color: #000;
      }

      .container {
         padding: 20px;
         max-width: 210mm;
         margin: 0 auto;
      }

      .header {
         border-bottom: 3px solid #000;
         padding-bottom: 15px;
         margin-bottom: 20px;
      }

      .company-info {
         text-align: center;
         margin-bottom: 10px;
      }

      .company-name {
         font-size: 18pt;
         font-weight: bold;
         margin-bottom: 5px;
      }

      .company-details {
         font-size: 9pt;
         color: #333;
      }

      .invoice-title {
         text-align: center;
         font-size: 16pt;
         font-weight: bold;
         margin: 20px 0;
         text-transform: uppercase;
         background-color: #f0f0f0;
         padding: 10px;
         border: 2px solid #000;
      }

      .info-section {
         margin-bottom: 20px;
      }

      .info-grid {
         display: table;
         width: 100%;
         margin-bottom: 15px;
      }

      .info-row {
         display: table-row;
      }

      .info-label {
         display: table-cell;
         width: 30%;
         padding: 4px 0;
         font-weight: bold;
      }

      .info-value {
         display: table-cell;
         padding: 4px 0;
      }

      .section-title {
         font-weight: bold;
         font-size: 11pt;
         margin-bottom: 8px;
         padding-bottom: 3px;
         border-bottom: 2px solid #333;
      }

      table.items-table {
         width: 100%;
         border-collapse: collapse;
         margin: 15px 0;
      }

      table.items-table th {
         background-color: #333;
         color: #fff;
         padding: 8px;
         text-align: left;
         font-weight: bold;
         border: 1px solid #000;
      }

      table.items-table th.right {
         text-align: right;
      }

      table.items-table th.center {
         text-align: center;
      }

      table.items-table td {
         padding: 6px 8px;
         border: 1px solid #999;
      }

      table.items-table td.right {
         text-align: right;
      }

      table.items-table td.center {
         text-align: center;
      }

      table.items-table tbody tr:nth-child(even) {
         background-color: #f9f9f9;
      }

      .totals-section {
         float: right;
         width: 50%;
         margin-top: 10px;
      }

      .totals-table {
         width: 100%;
         border-collapse: collapse;
      }

      .totals-table td {
         padding: 6px 10px;
      }

      .totals-table td.label {
         text-align: right;
         font-weight: bold;
         width: 50%;
      }

      .totals-table td.value {
         text-align: right;
         border-bottom: 1px solid #ddd;
      }

      .totals-table tr.total td {
         font-size: 12pt;
         font-weight: bold;
         border-top: 2px solid #000;
         border-bottom: 3px double #000;
         padding: 10px;
      }

      .totals-table tr.total td.value {
         background-color: #f0f0f0;
      }

      .notes-section {
         clear: both;
         margin-top: 30px;
         padding-top: 15px;
         border-top: 1px solid #ddd;
      }

      .notes-title {
         font-weight: bold;
         margin-bottom: 5px;
      }

      .notes-content {
         font-size: 9pt;
         color: #555;
      }

      .footer {
         margin-top: 50px;
         page-break-inside: avoid;
      }

      .signature-section {
         display: table;
         width: 100%;
      }

      .signature-box {
         display: table-cell;
         width: 33%;
         text-align: center;
         padding: 10px;
      }

      .signature-title {
         font-weight: bold;
         margin-bottom: 60px;
      }

      .signature-line {
         border-top: 1px solid #000;
         padding-top: 5px;
         font-weight: bold;
      }

      .tax-info {
         background-color: #ffe;
         border: 1px solid #dd9;
         padding: 10px;
         margin: 15px 0;
         font-size: 9pt;
      }

      .status-badge {
         display: inline-block;
         padding: 4px 12px;
         border-radius: 4px;
         font-size: 9pt;
         font-weight: bold;
      }

      .status-unpaid {
         background-color: #fee;
         color: #c00;
         border: 1px solid #c00;
      }

      .status-partial {
         background-color: #ffc;
         color: #880;
         border: 1px solid #880;
      }

      .status-paid {
         background-color: #efe;
         color: #080;
         border: 1px solid #080;
      }

      .payment-info {
         background-color: #e8f4f8;
         border: 1px solid #4a90a4;
         padding: 10px;
         margin: 15px 0;
      }

      @media print {
         body {
            margin: 0;
            padding: 0;
         }

         .container {
            padding: 0;
         }
      }
   </style>
</head>

<body>
   <div class="container">
      <!-- Header -->
      <div class="header">
         <div class="company-info">
            <div class="company-name">PT. NAMA PERUSAHAAN ANDA</div>
            <div class="company-details">
               Alamat: Jl. Contoh No. 123, Jakarta 12345<br>
               Telp: (021) 1234-5678 | Email: info@perusahaan.com<br>
               NPWP: 01.234.567.8-901.000
            </div>
         </div>
      </div>

      <!-- Invoice Title -->
      <div class="invoice-title">
         {{ __('app.tax_invoice') }}
      </div>

      <!-- Invoice Info -->
      <div class="info-section">
         <div class="info-grid">
            <div class="info-row">
               <div class="info-label">{{ __('app.invoice_number') }}:</div>
               <div class="info-value">{{ $invoice->invoice_number }}</div>
               <div class="info-label" style="text-align: right;">{{ __('app.date') }}:</div>
               <div class="info-value" style="text-align: right;">{{ $invoice->invoice_date->format('d F Y') }}</div>
            </div>
            <div class="info-row">
               <div class="info-label">{{ __('app.so_number') }}:</div>
               <div class="info-value">{{ $invoice->salesOrder->so_number }}</div>
               <div class="info-label" style="text-align: right;">{{ __('app.due_date') }}:</div>
               <div class="info-value" style="text-align: right;">
                  {{ $invoice->due_date->format('d F Y') }}
                  @if ($invoice->due_date->isPast() && $invoice->payment_status !== 'paid')
                     <span style="color: #c00; font-weight: bold;">({{ __('app.overdue') }})</span>
                  @endif
               </div>
            </div>
         </div>
      </div>

      <!-- Customer Info -->
      <div class="info-section">
         <div class="section-title">{{ __('app.dear_customer') }}:</div>
         <div class="info-grid">
            <div class="info-row">
               <div class="info-label">{{ __('app.customer_name') }}:</div>
               <div class="info-value">{{ $invoice->salesOrder->customer->name }}</div>
            </div>
            <div class="info-row">
               <div class="info-label">{{ __('app.address') }}:</div>
               <div class="info-value">{{ $invoice->salesOrder->customer->address ?? '-' }}</div>
            </div>
            <div class="info-row">
               <div class="info-label">{{ __('app.phone') }}:</div>
               <div class="info-value">{{ $invoice->salesOrder->customer->phone ?? '-' }}</div>
            </div>
            @if ($invoice->salesOrder->customer->tax_id)
               <div class="info-row">
                  <div class="info-label">{{ __('app.npwp') }}:</div>
                  <div class="info-value">{{ $invoice->salesOrder->customer->tax_id }}</div>
               </div>
            @endif
         </div>
      </div>

      <!-- Payment Status -->
      <div style="margin: 15px 0;">
         <strong>{{ __('app.payment_status') }}:</strong>
         @if ($invoice->payment_status === 'unpaid')
            <span class="status-badge status-unpaid">{{ __('app.payment_status_unpaid') }}</span>
         @elseif($invoice->payment_status === 'partial')
            <span class="status-badge status-partial">{{ __('app.payment_status_partial') }}</span>
         @else
            <span class="status-badge status-paid">{{ __('app.payment_status_paid') }}</span>
         @endif
      </div>

      @if ($invoice->payment_status !== 'unpaid')
         <div class="payment-info">
            <strong>{{ __('app.payment_information') }}:</strong><br>
            {{ __('app.paid') }}: <strong>Rp {{ number_format($invoice->paid_amount, 0, ',', '.') }}</strong><br>
            @if ($invoice->payment_status === 'partial')
               {{ __('app.remaining') }}: <strong style="color: #c00;">Rp
                  {{ number_format($invoice->remaining_amount, 0, ',', '.') }}</strong><br>
            @endif
            @if ($invoice->payment_date)
               {{ __('app.last_payment') }}: {{ $invoice->payment_date->format('d F Y') }}<br>
            @endif
            @if ($invoice->payment_method)
               {{ __('app.payment_method') }}: {{ ucfirst($invoice->payment_method) }}
            @endif
         </div>
      @endif

      <!-- Items Table -->
      <table class="items-table">
         <thead>
            <tr>
               <th class="center" style="width: 5%;">{{ __('app.number_short') }}</th>
               <th style="width: 45%;">{{ __('app.product_name') }}</th>
               <th class="center" style="width: 10%;">{{ __('app.quantity') }}</th>
               <th class="right" style="width: 18%;">{{ __('app.unit_price') }}</th>
               <th class="right" style="width: 22%;">{{ __('app.subtotal') }}</th>
            </tr>
         </thead>
         <tbody>
            @foreach ($invoice->salesOrder->items as $index => $item)
               <tr>
                  <td class="center">{{ $index + 1 }}</td>
                  <td>
                     <strong>{{ $item->product->name }}</strong><br>
                     <span style="font-size: 8pt; color: #666;">SKU: {{ $item->product->sku }}</span>
                  </td>
                  <td class="center">{{ number_format($item->quantity) }}</td>
                  <td class="right">Rp {{ number_format($item->unit_price, 0, ',', '.') }}</td>
                  <td class="right">Rp {{ number_format($item->subtotal, 0, ',', '.') }}</td>
               </tr>
            @endforeach
         </tbody>
      </table>

      <!-- Totals -->
      <div class="totals-section">
         <table class="totals-table">
            <tr>
               <td class="label">{{ __('app.subtotal') }}:</td>
               <td class="value">Rp {{ number_format($invoice->salesOrder->subtotal, 0, ',', '.') }}</td>
            </tr>
            @if ($invoice->salesOrder->discount > 0)
               <tr>
                  <td class="label">{{ __('app.discount') }}:</td>
                  <td class="value" style="color: #c00;">- Rp
                     {{ number_format($invoice->salesOrder->discount, 0, ',', '.') }}</td>
               </tr>
            @endif
            <tr>
               <td class="label">{{ __('app.tax_vat') }} 11%:</td>
               <td class="value">Rp {{ number_format($invoice->salesOrder->tax, 0, ',', '.') }}</td>
            </tr>
            <tr class="total">
               <td class="label">{{ __('app.total') }}:</td>
               <td class="value">Rp {{ number_format($invoice->total_amount, 0, ',', '.') }}</td>
            </tr>
         </table>
      </div>

      <!-- Tax Info -->
      <div class="tax-info" style="clear: both;">
         <strong>{{ __('app.tax_info_title') }}:</strong><br>
         {{ __('app.tax_info_desc_1') }}<br>
         {{ __('app.tax_info_desc_2') }}
      </div>

      <!-- Notes -->
      @if ($invoice->notes)
         <div class="notes-section">
            <div class="notes-title">{{ __('app.notes') }}:</div>
            <div class="notes-content">{{ $invoice->notes }}</div>
         </div>
      @endif

      <!-- Footer / Signature -->
      <div class="footer">
         <div style="font-size: 9pt; margin-bottom: 20px;">
            <strong>{{ __('app.payment_information') }}:</strong><br>
            {{ __('app.payment_bank') }}: BCA / Mandiri / BNI<br>
            {{ __('app.account_number') }}: 1234567890<br>
            {{ __('app.account_name') }}: PT. Nama Perusahaan Anda
         </div>

         <div class="signature-section">
            <div class="signature-box">
               <div class="signature-title">{{ __('app.regards') }},</div>
               <div class="signature-line">
                  (&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;)
               </div>
            </div>
            <div class="signature-box">
               <div class="signature-title">{{ __('app.approved_by') }},</div>
               <div class="signature-line">
                  (&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;)
               </div>
            </div>
            <div class="signature-box">
               <div class="signature-title">{{ __('app.receiver') }},</div>
               <div class="signature-line">
                  (&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;)
               </div>
            </div>
         </div>
      </div>

      <!-- Document Info -->
      <div
         style="margin-top: 30px; text-align: center; font-size: 8pt; color: #999; border-top: 1px solid #ddd; padding-top: 10px;">
         {{ __('app.printed_on') }} {{ now()->format('d F Y H:i:s') }}<br>
         {{ __('app.generated_by') }}: {{ $invoice->creator->name ?? 'System' }}
      </div>
   </div>
</body>

</html>
