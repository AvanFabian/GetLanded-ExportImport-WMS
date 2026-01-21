@extends('layouts.app')

@section('title', 'Stock In Detail')

@section('content')
   <div class="max-w-6xl mx-auto">
      <div class="flex items-center justify-between mb-4">
         <h2 class="text-xl font-semibold">Stock In Detail</h2>
         <div class="flex gap-2">
            <a href="{{ route('stock-ins.index') }}" class="px-3 py-2 border rounded">Back to List</a>
         </div>
      </div>

      <div class="bg-white p-6 rounded shadow mb-4">
         <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
               <table class="w-full">
                  <tr class="border-b">
                     <td class="py-2 text-sm text-slate-600 w-1/3">Transaction Code</td>
                     <td class="py-2 font-semibold">{{ $stockIn->transaction_code }}</td>
                  </tr>
                  <tr class="border-b">
                     <td class="py-2 text-sm text-slate-600">Date</td>
                     <td class="py-2">{{ date('d M Y', strtotime($stockIn->date)) }}</td>
                  </tr>
                  <tr class="border-b">
                     <td class="py-2 text-sm text-slate-600">Supplier</td>
                     <td class="py-2">{{ $stockIn->supplier?->name ?? '-' }}</td>
                  </tr>
               </table>
            </div>
            <div>
               <table class="w-full">
                  <tr class="border-b">
                     <td class="py-2 text-sm text-slate-600 w-1/3">Total Amount</td>
                     <td class="py-2 font-bold text-lg">Rp {{ number_format($stockIn->total, 0, ',', '.') }}</td>
                  </tr>
                  <tr class="border-b">
                     <td class="py-2 text-sm text-slate-600">Notes</td>
                     <td class="py-2">{{ $stockIn->notes ?? '-' }}</td>
                  </tr>
                  <tr>
                     <td class="py-2 text-sm text-slate-600">Created At</td>
                     <td class="py-2 text-sm">{{ $stockIn->created_at->format('d M Y H:i') }}</td>
                  </tr>
               </table>
            </div>
         </div>
      </div>

      <div class="bg-white rounded shadow overflow-hidden">
         <div class="p-4 bg-gray-50 font-semibold">Product Items</div>
         <div class="overflow-x-auto">
            <table class="min-w-full">
               <thead class="bg-gray-50">
                  <tr>
                     <th class="text-left p-3">Code</th>
                     <th class="text-left p-3">Produk</th>
                     <th class="text-left p-3">Category</th>
                     <th class="text-right p-3">Qty</th>
                     <th class="text-left p-3">Harga</th>
                     <th class="text-left p-3">Subtotal</th>
                  </tr>
               </thead>
               <tbody>
                  @foreach ($stockIn->details as $detail)
                     <tr class="border-t">
                        <td class="p-3">{{ $detail->product->code }}</td>
                        <td class="p-3">{{ $detail->product->name }}</td>
                        <td class="p-3">{{ $detail->product->category?->name ?? '-' }}</td>
                        <td class="p-3 text-right">{{ $detail->quantity }} {{ $detail->product->unit }}</td>
                        <td class="p-3 text-right">Rp {{ number_format($detail->purchase_price, 0, ',', '.') }}</td>
                        <td class="p-3 text-right font-semibold">Rp {{ number_format($detail->total, 0, ',', '.') }}</td>
                     </tr>
                  @endforeach
                  <tr class="border-t-2 bg-gray-50">
                     <td colspan="5" class="p-3 text-right font-semibold">Grand Total:</td>
                     <td class="p-3 text-right font-bold">Rp {{ number_format($stockIn->total, 0, ',', '.') }}</td>
                  </tr>
               </tbody>
            </table>
         </div>
      </div>

      {{-- Document Center --}}
      <div class="bg-white rounded shadow p-6 mt-4">
         <h3 class="text-lg font-semibold text-gray-800 mb-4 flex items-center gap-2">
            <svg class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
               <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
            </svg>
            Document Center
         </h3>
         <p class="text-sm text-gray-500 mb-4">Download official documents for this transaction.</p>
         
         <div class="flex flex-wrap gap-3">
            <a href="{{ route('pdf.receipt', $stockIn) }}" 
               class="inline-flex items-center gap-2 px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-lg transition font-medium">
               <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z"/>
               </svg>
               📄 Warehouse Receipt
            </a>
         </div>
      </div>
   </div>
@endsection
