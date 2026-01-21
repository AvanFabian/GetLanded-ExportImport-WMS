<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <title>{{ $documentType }} - {{ $documentNumber }}</title>
    <style>
        /* Professional sans-serif typography */
        @import url('https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap');
        
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Inter', 'Segoe UI', 'Helvetica Neue', Arial, sans-serif;
            font-size: 10pt;
            line-height: 1.4;
            color: #1f2937;
        }
        
        .page {
            position: relative;
            min-height: 100%;
        }
        
        /* Header Section */
        .header {
            display: table;
            width: 100%;
            margin-bottom: 20px;
            border-bottom: 2px solid #2563eb;
            padding-bottom: 15px;
        }
        
        .header-left {
            display: table-cell;
            width: 60%;
            vertical-align: top;
        }
        
        .header-right {
            display: table-cell;
            width: 40%;
            vertical-align: top;
            text-align: right;
        }
        
        .company-logo {
            max-height: 60px;
            max-width: 180px;
            margin-bottom: 8px;
        }
        
        .company-name {
            font-size: 16pt;
            font-weight: 700;
            color: #1e40af;
            margin-bottom: 4px;
        }
        
        .company-info {
            font-size: 8pt;
            color: #4b5563;
            line-height: 1.5;
        }
        
        .document-title {
            font-size: 18pt;
            font-weight: 700;
            color: #1f2937;
            margin-bottom: 8px;
        }
        
        .document-number {
            font-size: 11pt;
            font-weight: 600;
            color: #2563eb;
        }
        
        .qr-code {
            margin-top: 10px;
        }
        
        .qr-code img {
            width: 80px;
            height: 80px;
        }
        
        /* Info Boxes */
        .info-section {
            display: table;
            width: 100%;
            margin-bottom: 20px;
        }
        
        .info-box {
            display: table-cell;
            width: 50%;
            vertical-align: top;
            padding: 10px;
            background: #f9fafb;
            border: 1px solid #e5e7eb;
        }
        
        .info-box:first-child {
            border-right: none;
        }
        
        .info-label {
            font-size: 8pt;
            font-weight: 600;
            color: #6b7280;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            margin-bottom: 4px;
        }
        
        .info-value {
            font-size: 10pt;
            color: #1f2937;
        }
        
        /* Tables */
        table.items-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }
        
        table.items-table thead th {
            background-color: #1e40af;
            color: white;
            font-weight: 600;
            font-size: 9pt;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            padding: 10px 8px;
            text-align: left;
            border: 1px solid #1e40af;
        }
        
        table.items-table thead th.text-right {
            text-align: right;
        }
        
        table.items-table thead th.text-center {
            text-align: center;
        }
        
        table.items-table tbody td {
            padding: 8px;
            border: 1px solid #e5e7eb;
            font-size: 9pt;
        }
        
        table.items-table tbody tr:nth-child(even) {
            background-color: #f9fafb;
        }
        
        table.items-table tbody td.text-right {
            text-align: right;
        }
        
        table.items-table tbody td.text-center {
            text-align: center;
        }
        
        /* Summary Section */
        .summary-section {
            width: 100%;
            margin-bottom: 20px;
        }
        
        .summary-table {
            float: right;
            width: 280px;
            border-collapse: collapse;
        }
        
        .summary-table td {
            padding: 6px 10px;
            border: 1px solid #e5e7eb;
        }
        
        .summary-table td:first-child {
            font-weight: 500;
            background: #f9fafb;
        }
        
        .summary-table td:last-child {
            text-align: right;
        }
        
        .summary-table tr.grand-total td {
            background: #1e40af;
            color: white;
            font-weight: 700;
            font-size: 11pt;
        }
        
        /* Signature Section */
        .signature-section {
            display: table;
            width: 100%;
            margin-top: 40px;
            page-break-inside: avoid;
        }
        
        .signature-box {
            display: table-cell;
            width: 50%;
            padding: 15px;
            text-align: center;
        }
        
        .signature-line {
            border-bottom: 1px solid #1f2937;
            width: 200px;
            margin: 40px auto 10px;
        }
        
        .signature-label {
            font-size: 9pt;
            font-weight: 600;
            color: #1f2937;
        }
        
        .signature-name {
            font-size: 10pt;
            font-weight: 500;
            color: #2563eb;
            margin-top: 4px;
        }
        
        .signature-date {
            font-size: 8pt;
            color: #6b7280;
        }
        
        .stamp-placeholder {
            width: 80px;
            height: 80px;
            border: 2px dashed #d1d5db;
            border-radius: 50%;
            margin: 10px auto;
            display: flex;
            align-items: center;
            justify-content: center;
            color: #9ca3af;
            font-size: 8pt;
        }
        
        /* Footer Section */
        .footer {
            position: fixed;
            bottom: 0;
            left: 0;
            right: 0;
            border-top: 2px solid #2563eb;
            padding-top: 10px;
            font-size: 8pt;
        }
        
        .footer-bank {
            display: table;
            width: 100%;
            margin-bottom: 8px;
        }
        
        .footer-bank-item {
            display: table-cell;
            width: 33%;
        }
        
        .footer-bank-label {
            font-weight: 600;
            color: #6b7280;
            text-transform: uppercase;
            font-size: 7pt;
        }
        
        .footer-bank-value {
            color: #1f2937;
        }
        
        .footer-terms {
            font-size: 7pt;
            color: #6b7280;
            border-top: 1px solid #e5e7eb;
            padding-top: 8px;
            margin-top: 8px;
        }
        
        .page-number {
            text-align: right;
            font-size: 8pt;
            color: #6b7280;
        }
        
        /* Void Watermark */
        .void-watermark {
            position: fixed;
            top: 40%;
            left: 50%;
            transform: translate(-50%, -50%) rotate(-45deg);
            font-size: 120pt;
            font-weight: 700;
            color: rgba(220, 38, 38, 0.25);
            text-transform: uppercase;
            letter-spacing: 20px;
            z-index: 1000;
            pointer-events: none;
        }
        
        /* Utility */
        .clearfix::after {
            content: "";
            clear: both;
            display: table;
        }
        
        .text-right { text-align: right; }
        .text-center { text-align: center; }
        .font-bold { font-weight: 700; }
        .text-muted { color: #6b7280; }
    </style>
</head>
<body>
    <div class="page">
        @if($isVoided ?? false)
            <div class="void-watermark">VOID</div>
        @endif
        
        {{-- Header --}}
        <div class="header">
            <div class="header-left">
                @if($company->logo_path)
                    <img src="{{ public_path('storage/' . $company->logo_path) }}" alt="{{ $company->name }}" class="company-logo">
                @endif
                <div class="company-name">{{ $company->name }}</div>
                <div class="company-info">
                    @if($company->address){{ $company->address }}<br>@endif
                    @if($company->phone)Tel: {{ $company->phone }}@endif
                    @if($company->email) | {{ $company->email }}@endif
                    @if($company->tax_id)<br>Tax ID: {{ $company->tax_id }}@endif
                </div>
            </div>
            <div class="header-right">
                <div class="document-title">{{ $documentType }}</div>
                <div class="document-number">{{ $documentNumber }}</div>
                @if($qrCode ?? false)
                    <div class="qr-code">
                        <img src="{{ $qrCode }}" alt="QR Code">
                    </div>
                @endif
            </div>
        </div>
        
        {{-- Document Content (yielded by child templates) --}}
        @yield('content')
        
        {{-- Signature Section --}}
        @if($approver ?? false)
        <div class="signature-section">
            <div class="signature-box">
                <div class="stamp-placeholder">
                    <span>STAMP</span>
                </div>
                <div class="signature-line"></div>
                <div class="signature-label">Authorized Signature</div>
                <div class="signature-name">{{ $approver->name }}</div>
                @if($approvedAt ?? false)
                    <div class="signature-date">{{ $approvedAt->format('d M Y, H:i') }}</div>
                @endif
            </div>
            <div class="signature-box">
                <div class="stamp-placeholder">
                    <span>STAMP</span>
                </div>
                <div class="signature-line"></div>
                <div class="signature-label">Received By</div>
            </div>
        </div>
        @endif
        
        {{-- Footer --}}
        <div class="footer">
            @if($company->bank_name || $company->bank_account_number || $company->bank_swift_code)
            <div class="footer-bank">
                @if($company->bank_name)
                <div class="footer-bank-item">
                    <div class="footer-bank-label">Bank Name</div>
                    <div class="footer-bank-value">{{ $company->bank_name }}</div>
                </div>
                @endif
                @if($company->bank_account_number)
                <div class="footer-bank-item">
                    <div class="footer-bank-label">Account Number</div>
                    <div class="footer-bank-value">{{ $company->bank_account_number }}</div>
                </div>
                @endif
                @if($company->bank_swift_code)
                <div class="footer-bank-item">
                    <div class="footer-bank-label">SWIFT/BIC</div>
                    <div class="footer-bank-value">{{ $company->bank_swift_code }}</div>
                </div>
                @endif
            </div>
            @endif
            
            @if($company->invoice_terms ?? false)
            <div class="footer-terms">
                <strong>Terms & Conditions:</strong><br>
                {!! nl2br(e($company->invoice_terms)) !!}
            </div>
            @endif
            
            <div class="page-number">
                Page <span class="pagenum"></span>
            </div>
        </div>
    </div>
    
    <script type="text/php">
        if (isset($pdf)) {
            $text = "Page {PAGE_NUM} of {PAGE_COUNT}";
            $size = 8;
            $font = $fontMetrics->getFont("Inter", "normal");
            $width = $fontMetrics->get_text_width($text, $font, $size) / 2;
            $x = $pdf->get_width() - $width - 35;
            $y = $pdf->get_height() - 28;
            $pdf->page_text($x, $y, $text, $font, $size, array(0.42, 0.45, 0.49));
        }
    </script>
</body>
</html>
