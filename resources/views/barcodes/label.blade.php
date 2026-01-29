<x-app-layout>
   <x-slot name="header">
      <h2 class="font-semibold text-xl text-gray-800 leading-tight">
         {{ __('Product Label') }} - {{ $product->name }}
      </h2>
   </x-slot>

   <div class="py-12">
      <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
         <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
            <div class="p-6 text-gray-900">
               <!-- Product Info -->
               <div class="mb-6">
                  <h3 class="text-lg font-semibold mb-4">Informasi Produk</h3>
                  <div class="grid grid-cols-2 gap-4">
                     <div>
                        <p class="text-sm text-gray-600">Kode</p>
                        <p class="font-semibold">{{ $product->code }}</p>
                     </div>
                     <div>
                        <p class="text-sm text-gray-600">Nama</p>
                        <p class="font-semibold">{{ $product->name }}</p>
                     </div>
                     <div>
                        <p class="text-sm text-gray-600">Kategori</p>
                        <p class="font-semibold">{{ $product->category->name ?? '-' }}</p>
                     </div>
                     <div>
                        <p class="text-sm text-gray-600">Gudang</p>
                        <p class="font-semibold">{{ $product->warehouses->first()?->name ?? '-' }}</p>
                     </div>
                     <div>
                        <p class="text-sm text-gray-600">Harga</p>
                        <p class="font-semibold">Rp {{ number_format($product->selling_price, 0, ',', '.') }}</p>
                     </div>
                     <div>
                        <p class="text-sm text-gray-600">Stok</p>
                        <p class="font-semibold">{{ $product->total_stock }} {{ $product->unit }}</p>
                     </div>
                  </div>
               </div>

               <hr class="my-6">

               <!-- Barcode Section -->
               <div class="mb-6 text-center">
                  <h3 class="text-lg font-semibold mb-4">Barcode</h3>
                  <div class="flex justify-center items-center bg-white p-4 border rounded">
                     <div>
                        <img src="{{ route('products.barcode', $product->id) }}" alt="Barcode" class="mx-auto mb-2"
                           style="height: 80px;">
                        <p class="text-sm font-mono">{{ $product->code }}</p>
                     </div>
                  </div>
               </div>

               <!-- QR Code Section -->
               <div class="mb-6 text-center">
                  <h3 class="text-lg font-semibold mb-4">QR Code</h3>
                  <div class="flex justify-center items-center">
                     <img src="{{ route('products.qrcode', $product->id) }}" alt="QR Code" class="border rounded p-2"
                        style="width: 250px; height: 250px;">
                  </div>
                  <p class="text-xs text-gray-500 mt-2">Pindai untuk melihat detail produk</p>
               </div>

               <!-- Action Buttons -->
               <div class="flex justify-between items-center mt-6">
                  <a href="{{ route('products.index') }}"
                     class="bg-gray-500 hover:bg-gray-700 text-white font-bold py-2 px-4 rounded">
                     Back to Products
                  </a>
                  <button onclick="window.print()"
                     class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">
                     Print Label
                  </button>
               </div>
            </div>
         </div>
      </div>
   </div>

   <!-- Print Styles -->
   <style>
      @media print {
         body * {
            visibility: hidden;
         }

         .print-area,
         .print-area * {
            visibility: visible;
         }

         .print-area {
            position: absolute;
            left: 0;
            top: 0;
            width: 100%;
         }

         button,
         .no-print {
            display: none !important;
         }
      }
   </style>

   <script>
      // Print area wrapper
      document.addEventListener('DOMContentLoaded', function() {
         const printContent = document.querySelector('.bg-white.overflow-hidden');
         if (printContent) {
            printContent.classList.add('print-area');
         }
      });
   </script>
</x-app-layout>
