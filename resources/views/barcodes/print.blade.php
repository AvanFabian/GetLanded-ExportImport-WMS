<!DOCTYPE html>
<html>

<head>
   <meta charset="utf-8">
   <title>Product Labels</title>
   <style>
      @page {
         margin: 10mm;
      }

      body {
         font-family: Arial, sans-serif;
         margin: 0;
         padding: 0;
      }

      .label-container {
         display: inline-block;
         width: 90mm;
         height: 50mm;
         border: 1px solid #ddd;
         padding: 5mm;
         margin: 2mm;
         page-break-inside: avoid;
         vertical-align: top;
      }

      .product-name {
         font-size: 11pt;
         font-weight: bold;
         margin-bottom: 2mm;
         text-align: center;
         overflow: hidden;
         text-overflow: ellipsis;
         white-space: nowrap;
      }

      .product-code {
         font-size: 9pt;
         text-align: center;
         margin-bottom: 2mm;
         font-family: 'Courier New', monospace;
      }

      .barcode-section {
         text-align: center;
         margin: 3mm 0;
         overflow: hidden;
      }

      .barcode-section img {
         max-width: 100%;
         height: 30px;
         object-fit: contain;
      }

      .qr-section {
         text-align: center;
         margin: 1mm 0;
         padding: 0;
         overflow: hidden;
         display: flex;
         justify-content: center;
         align-items: center;
      }

      .qr-section svg {
         max-width: 100%;
         max-height: 100%;
         width: auto !important;
         height: 70px !important;
      }

      .product-info {
         font-size: 8pt;
         margin-top: 2mm;
      }

      .info-row {
         margin-bottom: 1mm;
      }

      .label-text {
         font-weight: bold;
      }

      .flex-container {
         display: table;
         width: 100%;
         table-layout: fixed;
      }

      .flex-left {
         display: table-cell;
         width: 65%;
         vertical-align: top;
         padding-right: 2mm;
      }

      .flex-right {
         display: table-cell;
         width: 35%;
         text-align: center;
         vertical-align: top;
         padding-left: 2mm;
      }
   </style>
</head>

<body>
   @foreach ($products as $product)
      <div class="label-container">
         <div class="product-name">{{ $product->name }}</div>
         <div class="product-code">{{ $product->code }}</div>

         <div class="flex-container">
            <div class="flex-left">
               <div class="barcode-section">
                  @php
                      $generator = new \Picqer\Barcode\BarcodeGeneratorPNG();
                      $barcodeData = $generator->getBarcode($product->code, $generator::TYPE_CODE_128);
                  @endphp
                  <img src="data:image/png;base64,{{ base64_encode($barcodeData) }}" alt="Barcode">
               </div>

               <div class="product-info">
                  <div class="info-row">
                     <span class="label-text">Category:</span> {{ $product->category->name ?? '-' }}
                  </div>
                  <div class="info-row">
                     <span class="label-text">Warehouse:</span> {{ $product->warehouses->first()?->name ?? '-' }}
                  </div>
                  <div class="info-row">
                     <span class="label-text">Price:</span> Rp {{ number_format($product->selling_price, 0, ',', '.') }}
                  </div>
                  <div class="info-row">
                     <span class="label-text">Stock:</span> {{ $product->total_stock }} {{ $product->unit }}
                  </div>
               </div>
            </div>

            <div class="flex-right">
               <div class="qr-section">
                  {!! \SimpleSoftwareIO\QrCode\Facades\QrCode::size(150)->margin(0)->generate(
                          json_encode([
                              'id' => $product->id,
                              'code' => $product->code,
                              'name' => $product->name,
                              'price' => $product->selling_price,
                          ]),
                      ) !!}
               </div>
            </div>
         </div>
      </div>
   @endforeach
</body>

</html>
