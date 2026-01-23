@extends('layouts.app')

@section('title', __('app.stock_out_detail'))

@section('content')
   <div class="max-w-6xl mx-auto">
      <div class="flex items-center justify-between mb-4">
         <h2 class="text-xl font-semibold">{{ __('app.stock_out_detail') }}</h2>
         <div class="flex gap-2">
            <a href="{{ route('stock-outs.index') }}" class="px-3 py-2 border rounded">{{ __('app.back_to_list') }}</a>
         </div>
      </div>

      <div class="bg-white p-6 rounded shadow mb-4">
         <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
               <table class="w-full">
                  <tr class="border-b">
                     <td class="py-2 text-sm text-slate-600 w-1/3">{{ __('app.transaction_code') }}</td>
                     <td class="py-2 font-semibold">{{ $stockOut->transaction_code }}</td>
                  </tr>
                  <tr class="border-b">
                     <td class="py-2 text-sm text-slate-600">{{ __('app.date') }}</td>
                     <td class="py-2">{{ date('d M Y', strtotime($stockOut->date)) }}</td>
                  </tr>
                  <tr class="border-b">
                     <td class="py-2 text-sm text-slate-600">{{ __('app.customer') }}</td>
                     <td class="py-2">{{ $stockOut->customer ?? '-' }}</td>
                  </tr>
               </table>
            </div>
            <div>
               <table class="w-full">
                  <tr class="border-b">
                     <td class="py-2 text-sm text-slate-600 w-1/3">{{ __('app.total') }}</td>
                     <td class="py-2 font-bold text-lg">Rp {{ number_format($stockOut->total, 0, ',', '.') }}</td>
                  </tr>
                  <tr class="border-b">
                     <td class="py-2 text-sm text-slate-600">{{ __('app.notes') }}</td>
                     <td class="py-2">{{ $stockOut->notes ?? '-' }}</td>
                  </tr>
                  <tr>
                     <td class="py-2 text-sm text-slate-600">{{ __('app.created_at') }}</td>
                     <td class="py-2 text-sm">{{ $stockOut->created_at->format('d M Y H:i') }}</td>
                  </tr>
               </table>
            </div>
         </div>
      </div>

      <div class="bg-white rounded shadow overflow-hidden">
         <div class="p-4 bg-gray-50 font-semibold">{{ __('app.product_items') }}</div>
         <div class="overflow-x-auto">
            <table class="min-w-full">
               <thead class="bg-gray-50">
                  <tr>
                     <th class="text-left p-3">{{ __('app.code') }}</th>
                     <th class="text-left p-3">{{ __('app.product') }}</th>
                     <th class="text-left p-3">{{ __('app.category') }}</th>
                     <th class="text-right p-3">{{ __('app.qty') }}</th>
                     <th class="text-left p-3">{{ __('app.price') }}</th>
                     <th class="text-left p-3">{{ __('app.subtotal') }}</th>
                  </tr>
               </thead>
               <tbody>
                  @foreach ($stockOut->details as $detail)
                     <tr class="border-t">
                        <td class="p-3">{{ $detail->product->code }}</td>
                        <td class="p-3">{{ $detail->product->name }}</td>
                        <td class="p-3">{{ $detail->product->category?->name ?? '-' }}</td>
                        <td class="p-3 text-right">{{ $detail->quantity }} {{ $detail->product->unit }}</td>
                        <td class="p-3 text-right">Rp {{ number_format($detail->selling_price, 0, ',', '.') }}</td>
                        <td class="p-3 text-right font-semibold">Rp {{ number_format($detail->total, 0, ',', '.') }}</td>
                     </tr>
                  @endforeach
                  <tr class="border-t-2 bg-gray-50">
                     <td colspan="5" class="p-3 text-right font-semibold">{{ __('app.grand_total') }}:</td>
                     <td class="p-3 text-right font-bold">Rp {{ number_format($stockOut->total, 0, ',', '.') }}</td>
                  </tr>
               </tbody>
            </table>
         </div>
      </div>
   </div>
@endsection
