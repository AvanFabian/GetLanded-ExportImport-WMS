@extends('layouts.app')

@section('title', __('app.transfer_detail'))

@section('content')
   <div class="max-w-6xl mx-auto">
      <div class="flex items-center justify-between mb-4">
         <h2 class="text-xl font-semibold">{{ __('app.transfer_detail') }}</h2>
         <div class="flex gap-2">
            @if ($transfer->status === 'pending' && auth()->user()->isAdmin())
               <form action="{{ route('transfers.approve', $transfer) }}" method="POST" class="inline-block">
                  @csrf
                  <button type="submit" class="px-3 py-2 bg-success text-white rounded"
                     onclick="return confirm('{{ __('app.confirm_approve') }}')">{{ __('app.approve') }}</button>
               </form>
               <form action="{{ route('transfers.reject', $transfer) }}" method="POST" class="inline-block">
                  @csrf
                  <button type="submit" class="px-3 py-2 bg-danger text-white rounded"
                     onclick="return confirm('{{ __('app.confirm_reject') }}')">{{ __('app.reject') }}</button>
               </form>
            @endif

            @if ($transfer->status === 'approved')
               <form action="{{ route('transfers.start-transit', $transfer) }}" method="POST" class="inline-block">
                  @csrf
                  <button type="submit" class="px-3 py-2 bg-primary text-white rounded"
                     onclick="return confirm('{{ __('app.confirm_start_transit') }}')">{{ __('app.start_transit') }}</button>
               </form>
            @endif

            @if (in_array($transfer->status, ['approved', 'in_transit']))
               <form action="{{ route('transfers.complete', $transfer) }}" method="POST" class="inline-block">
                  @csrf
                  <button type="submit" class="px-3 py-2 bg-success text-white rounded"
                     onclick="return confirm('{{ __('app.confirm_complete_transfer') }}')">{{ __('app.complete_transfer') }}</button>
               </form>
            @endif

            <a href="{{ route('transfers.index') }}" class="px-3 py-2 border rounded">{{ __('app.back_to_list') }}</a>
         </div>
      </div>

      <div class="bg-white p-6 rounded shadow mb-4">
         <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
               <table class="w-full">
                  <tr class="border-b">
                     <td class="py-2 text-sm text-slate-600 w-1/3">{{ __('app.transfer_number') }}</td>
                     <td class="py-2 font-semibold">{{ $transfer->transfer_number }}</td>
                  </tr>
                  <tr class="border-b">
                     <td class="py-2 text-sm text-slate-600">{{ __('app.transfer_date') }}</td>
                     <td class="py-2">{{ $transfer->transfer_date->format('d M Y') }}</td>
                  </tr>
                  <tr class="border-b">
                     <td class="py-2 text-sm text-slate-600">{{ __('app.from_warehouse') }}</td>
                     <td class="py-2">
                        <div class="font-medium">{{ $transfer->fromWarehouse->name }}</div>
                        <div class="text-sm text-slate-600">{{ $transfer->fromWarehouse->code }}</div>
                     </td>
                  </tr>
                  <tr class="border-b">
                     <td class="py-2 text-sm text-slate-600">{{ __('app.to_warehouse') }}</td>
                     <td class="py-2">
                        <div class="font-medium">{{ $transfer->toWarehouse->name }}</div>
                        <div class="text-sm text-slate-600">{{ $transfer->toWarehouse->code }}</div>
                     </td>
                  </tr>
                  <tr class="border-b">
                     <td class="py-2 text-sm text-slate-600">{{ __('app.status') }}</td>
                     <td class="py-2">
                        @if ($transfer->status === 'pending')
                           <span class="px-2 py-1 text-xs bg-warning text-white rounded">{{ __('app.pending') }}</span>
                        @elseif($transfer->status === 'approved')
                           <span class="px-2 py-1 text-xs bg-primary text-white rounded">{{ __('app.approved') }}</span>
                        @elseif($transfer->status === 'in_transit')
                           <span class="px-2 py-1 text-xs bg-blue-500 text-white rounded">{{ __('app.in_transit') }}</span>
                        @elseif($transfer->status === 'completed')
                           <span class="px-2 py-1 text-xs bg-success text-white rounded">{{ __('app.completed') }}</span>
                        @else
                           <span class="px-2 py-1 text-xs bg-danger text-white rounded">{{ __('app.rejected') }}</span>
                        @endif
                     </td>
                  </tr>
               </table>
            </div>
            <div>
               <table class="w-full">
                  <tr class="border-b">
                     <td class="py-2 text-sm text-slate-600 w-1/3">{{ __('app.created_by') }}</td>
                     <td class="py-2">{{ $transfer->creator->name ?? '-' }}</td>
                  </tr>
                  <tr class="border-b">
                     <td class="py-2 text-sm text-slate-600">{{ __('app.created_at') }}</td>
                     <td class="py-2 text-sm">{{ $transfer->created_at->format('d M Y H:i') }}</td>
                  </tr>
                  @if ($transfer->approved_by)
                     <tr class="border-b">
                        <td class="py-2 text-sm text-slate-600">{{ __('app.approved_by') }}</td>
                        <td class="py-2">{{ $transfer->approver->name ?? '-' }}</td>
                     </tr>
                     <tr class="border-b">
                        <td class="py-2 text-sm text-slate-600">{{ __('app.approved_at') }}</td>
                        <td class="py-2 text-sm">{{ $transfer->approved_at?->format('d M Y H:i') ?? '-' }}</td>
                     </tr>
                  @endif
                  @if ($transfer->completed_by)
                     <tr class="border-b">
                        <td class="py-2 text-sm text-slate-600">{{ __('app.completed_by') }}</td>
                        <td class="py-2">{{ $transfer->completer->name ?? '-' }}</td>
                     </tr>
                     <tr class="border-b">
                        <td class="py-2 text-sm text-slate-600">{{ __('app.completed_at') }}</td>
                        <td class="py-2 text-sm">{{ $transfer->completed_at?->format('d M Y H:i') ?? '-' }}</td>
                     </tr>
                  @endif
                  <tr class="border-b">
                     <td class="py-2 text-sm text-slate-600">{{ __('app.notes') }}</td>
                     <td class="py-2">{{ $transfer->notes ?? '-' }}</td>
                  </tr>
               </table>
            </div>
         </div>
      </div>

      <div class="bg-white rounded shadow overflow-hidden">
         <div class="p-4 bg-gray-50 font-semibold">{{ __('app.transfer_items') }}</div>
         <div class="overflow-x-auto">
            <table class="min-w-full">
               <thead class="bg-gray-50">
                  <tr>
                     <th class="text-left p-3">{{ __('app.code') }}</th>
                     <th class="text-left p-3">{{ __('app.product') }}</th>
                     <th class="text-left p-3">{{ __('app.category') }}</th>
                     <th class="text-left p-3">{{ __('app.quantity') }}</th>
                     <th class="text-left p-3">{{ __('app.notes') }}</th>
                  </tr>
               </thead>
               <tbody>
                  @foreach ($transfer->items as $item)
                     <tr class="border-t">
                        <td class="p-3">{{ $item->product->code }}</td>
                        <td class="p-3">{{ $item->product->name }}</td>
                        <td class="p-3">{{ $item->product->category?->name ?? '-' }}</td>
                        <td class="p-3 text-right font-semibold">{{ $item->quantity }} {{ $item->product->unit }}</td>
                        <td class="p-3">{{ $item->notes ?? '-' }}</td>
                     </tr>
                  @endforeach
                  <tr class="border-t-2 bg-gray-50">
                     <td colspan="3" class="p-3 text-right font-semibold">{{ __('app.total_items') }}:</td>
                     <td class="p-3 text-right font-bold">{{ $transfer->getTotalItems() }}</td>
                     <td></td>
                  </tr>
               </tbody>
            </table>
         </div>
      </div>

      @if ($transfer->status === 'completed')
         <div class="mt-4 p-4 bg-green-50 border border-green-200 rounded">
            <div class="flex items-start">
               <svg class="w-5 h-5 text-green-600 mt-0.5 mr-2" fill="currentColor" viewBox="0 0 20 20">
                  <path fill-rule="evenodd"
                     d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z"
                     clip-rule="evenodd" />
               </svg>
               <div>
                  <p class="text-sm font-medium text-green-800">{{ __('app.transfer_completed_title') }}</p>
                  <p class="text-sm text-green-700 mt-1">
                     {{ __('app.transfer_completed_desc', ['from' => $transfer->fromWarehouse->name, 'to' => $transfer->toWarehouse->name]) }}
                  </p>
               </div>
            </div>
         </div>
      @elseif($transfer->status === 'rejected')
         <div class="mt-4 p-4 bg-red-50 border border-red-200 rounded">
            <div class="flex items-start">
               <svg class="w-5 h-5 text-red-600 mt-0.5 mr-2" fill="currentColor" viewBox="0 0 20 20">
                  <path fill-rule="evenodd"
                     d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z"
                     clip-rule="evenodd" />
               </svg>
               <div>
                  <p class="text-sm font-medium text-red-800">{{ __('app.transfer_rejected_title') }}</p>
                  <p class="text-sm text-red-700 mt-1">{{ __('app.transfer_rejected_desc') }}</p>
               </div>
            </div>
         </div>
      @endif
   </div>
@endsection
