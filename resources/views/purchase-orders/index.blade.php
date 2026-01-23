@extends('layouts.app')

@section('title', __('app.purchase_orders'))

@section('content')
   <div class="max-w-7xl mx-auto">
      <div class="flex items-center justify-between mb-4">
         <div>
            <h2 class="text-xl font-semibold">{{ __('app.purchase_orders') }}</h2>
            <p class="text-sm text-gray-600">{{ __('app.manage_purchase_orders') }}</p>
         </div>
         <a href="{{ route('purchase-orders.create') }}" class="px-4 py-2 bg-primary text-white rounded hover:bg-blue-700">
            <svg class="w-4 h-4 inline-block mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
               <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
            </svg>
            {{ __('app.create_new_po') }}
         </a>
      </div>

      <!-- Filter Section -->
      <form method="GET" action="{{ route('purchase-orders.index') }}" class="mb-4 bg-white p-4 rounded shadow">
         <div class="grid grid-cols-1 md:grid-cols-5 gap-3">
            <div>
               <label class="block text-sm font-medium text-gray-700 mb-1">{{ __('app.search_po') }}</label>
               <input type="text" name="q" value="{{ request('q') }}"
                  placeholder="{{ __('app.po_number_placeholder') }}" class="w-full border rounded px-3 py-2" />
            </div>
            <div>
               <label class="block text-sm font-medium text-gray-700 mb-1">{{ __('app.warehouse') }}</label>
               <select name="warehouse_id" class="w-full border rounded px-3 py-2">
                  <option value="">{{ __('app.all_warehouses') }}</option>
                  @foreach ($warehouses as $warehouse)
                     <option value="{{ $warehouse->id }}"
                        {{ request('warehouse_id') == $warehouse->id ? 'selected' : '' }}>
                        {{ $warehouse->name }}
                     </option>
                  @endforeach
               </select>
            </div>
            <div>
               <label class="block text-sm font-medium text-gray-700 mb-1">{{ __('app.supplier') }}</label>
               <select name="supplier_id" class="w-full border rounded px-3 py-2">
                  <option value="">{{ __('app.all_suppliers') }}</option>
                  @foreach ($suppliers as $supplier)
                     <option value="{{ $supplier->id }}" {{ request('supplier_id') == $supplier->id ? 'selected' : '' }}>
                        {{ $supplier->name }}
                     </option>
                  @endforeach
               </select>
            </div>
            <div>
               <label class="block text-sm font-medium text-gray-700 mb-1">{{ __('app.status') }}</label>
               <select name="status" class="w-full border rounded px-3 py-2">
                  <option value="">{{ __('app.all_statuses') }}</option>
                  <option value="draft" {{ request('status') == 'draft' ? 'selected' : '' }}>{{ __('app.draft') }}
                  </option>
                  <option value="pending" {{ request('status') == 'pending' ? 'selected' : '' }}>{{ __('app.pending') }}
                  </option>
                  <option value="approved" {{ request('status') == 'approved' ? 'selected' : '' }}>
                     {{ __('app.approved') }}</option>
                  <option value="partially_received" {{ request('status') == 'partially_received' ? 'selected' : '' }}>
                     {{ __('app.partially_received') }}</option>
                  <option value="completed" {{ request('status') == 'completed' ? 'selected' : '' }}>
                     {{ __('app.completed') }}</option>
                  <option value="cancelled" {{ request('status') == 'cancelled' ? 'selected' : '' }}>
                     {{ __('app.cancelled') }}</option>
               </select>
            </div>
            <div class="flex items-end gap-2">
               <button type="submit" class="px-4 py-2 bg-secondary text-white rounded hover:bg-gray-700 flex-1">
                  <svg class="w-4 h-4 inline-block mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                     <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                  </svg>
                  {{ __('app.search') }}
               </button>
               <a href="{{ route('purchase-orders.index') }}"
                  class="px-4 py-2 bg-gray-500 text-white rounded hover:bg-gray-600">
                  <svg class="w-4 h-4 inline-block" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                     <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15">
                     </path>
                  </svg>
               </a>
            </div>
         </div>
      </form>

      <!-- Alerts -->
      @if (session('success'))
         <div class="mb-4 p-4 bg-green-100 border border-green-400 text-green-700 rounded">
            {{ session('success') }}
         </div>
      @endif
      @if (session('error'))
         <div class="mb-4 p-4 bg-red-100 border border-red-400 text-red-700 rounded">
            {{ session('error') }}
         </div>
      @endif

      <!-- Table -->
      <div class="bg-white rounded shadow overflow-hidden">
         <div class="overflow-x-auto">
            <table class="min-w-full">
               <thead class="bg-gray-50">
                  <tr>
                     <th class="text-left p-3 font-semibold">{{ __('app.po_number') }}</th>
                     <th class="text-left p-3 font-semibold">{{ __('app.date') }}</th>
                     <th class="text-left p-3 font-semibold">{{ __('app.warehouse') }}</th>
                     <th class="text-left p-3 font-semibold">{{ __('app.supplier') }}</th>
                     <th class="text-left p-3 font-semibold">{{ __('app.total') }}</th>
                     <th class="text-left p-3 font-semibold">{{ __('app.status') }}</th>
                     <th class="text-left p-3 font-semibold">{{ __('app.created_by') }}</th>
                     <th class="text-left p-3 font-semibold">{{ __('app.actions') }}</th>
                  </tr>
               </thead>
               <tbody>
                  @forelse($purchaseOrders as $po)
                     <tr class="border-t hover:bg-gray-50">
                        <td class="p-3">
                           <span class="font-semibold text-blue-600">{{ $po->po_number }}</span>
                        </td>
                        <td class="p-3">{{ $po->order_date->format('d/m/Y') }}</td>
                        <td class="p-3">{{ $po->warehouse->name }}</td>
                        <td class="p-3">{{ $po->supplier->name }}</td>
                        <td class="p-3">Rp {{ number_format($po->total_amount, 0, ',', '.') }}</td>
                        <td class="p-3">
                           @if ($po->status === 'draft')
                              <span
                                 class="px-2 py-1 text-xs rounded bg-gray-100 text-gray-700">{{ __('app.draft') }}</span>
                           @elseif($po->status === 'pending')
                              <span
                                 class="px-2 py-1 text-xs rounded bg-yellow-100 text-yellow-700">{{ __('app.pending') }}</span>
                           @elseif($po->status === 'approved')
                              <span
                                 class="px-2 py-1 text-xs rounded bg-green-100 text-green-700">{{ __('app.approved') }}</span>
                           @elseif($po->status === 'partially_received')
                              <span
                                 class="px-2 py-1 text-xs rounded bg-blue-100 text-blue-700">{{ __('app.partially_received') }}</span>
                           @elseif($po->status === 'completed')
                              <span
                                 class="px-2 py-1 text-xs rounded bg-purple-100 text-purple-700">{{ __('app.completed') }}</span>
                           @elseif($po->status === 'cancelled')
                              <span
                                 class="px-2 py-1 text-xs rounded bg-red-100 text-red-700">{{ __('app.cancelled') }}</span>
                           @endif
                        </td>
                        <td class="p-3">{{ $po->createdBy->name }}</td>
                        <td class="p-3">
                           <div class="flex gap-1">
                              <a href="{{ route('purchase-orders.show', $po) }}"
                                 class="px-2 py-1 bg-blue-500 text-white rounded hover:bg-blue-600 text-sm"
                                 title="{{ __('app.view') }}">
                                 <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                       d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                       d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z">
                                    </path>
                                 </svg>
                              </a>
                              @if ($po->canBeEdited())
                                 <a href="{{ route('purchase-orders.edit', $po) }}"
                                    class="px-2 py-1 bg-yellow-500 text-white rounded hover:bg-yellow-600 text-sm"
                                    title="{{ __('app.edit') }}">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                       <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                          d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z">
                                       </path>
                                    </svg>
                                 </a>
                                 <form action="{{ route('purchase-orders.destroy', $po) }}" method="POST"
                                    class="inline-block"
                                    onsubmit="return confirm('{{ __('app.confirm_delete', ['item' => __('app.purchase_order')]) }}')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit"
                                       class="px-2 py-1 bg-red-500 text-white rounded hover:bg-red-600 text-sm"
                                       title="{{ __('app.delete') }}">
                                       <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                             d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16">
                                          </path>
                                       </svg>
                                    </button>
                                 </form>
                              @endif
                           </div>
                        </td>
                     </tr>
                  @empty
                     <tr>
                        <td colspan="8" class="p-8 text-center text-gray-500">
                           <svg class="w-12 h-12 mx-auto mb-2 text-gray-400" fill="none" stroke="currentColor"
                              viewBox="0 0 24 24">
                              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                 d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z">
                              </path>
                           </svg>
                           <p>{{ __('app.no_po_data') }}</p>
                        </td>
                     </tr>
                  @endforelse
               </tbody>
            </table>
         </div>

         <!-- Pagination -->
         <div class="p-4 border-t">
            {{ $purchaseOrders->links() }}
         </div>
      </div>
   </div>
@endsection
