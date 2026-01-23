@extends('layouts.app')

@section('title', __('app.inter_warehouse_transfer'))

@section('content')
   <div class="max-w-7xl mx-auto">
      <div class="flex items-center justify-between mb-4">
         <h2 class="text-xl font-semibold">{{ __('app.inter_warehouse_transfer') }}</h2>
         <a href="{{ route('transfers.create') }}"
            class="px-3 py-2 bg-primary text-white rounded">{{ __('app.new_transfer') }}</a>
      </div>

      <form method="GET" class="mb-4 bg-white p-4 rounded shadow">
         <div class="grid grid-cols-1 md:grid-cols-4 gap-3">
            <div>
               <input type="text" name="q" value="{{ $q ?? '' }}"
                  placeholder="{{ __('app.search_transfer_number') }}" class="w-full border rounded px-2 py-1" />
            </div>
            <div>
               <select name="status" class="w-full border rounded px-2 py-1">
                  <option value="">{{ __('app.all_status') }}</option>
                  <option value="pending" {{ $status == 'pending' ? 'selected' : '' }}>{{ __('app.pending') }}</option>
                  <option value="approved" {{ $status == 'approved' ? 'selected' : '' }}>{{ __('app.approved') }}</option>
                  <option value="in_transit" {{ $status == 'in_transit' ? 'selected' : '' }}>{{ __('app.in_transit') }}
                  </option>
                  <option value="completed" {{ $status == 'completed' ? 'selected' : '' }}>{{ __('app.completed') }}
                  </option>
                  <option value="rejected" {{ $status == 'rejected' ? 'selected' : '' }}>{{ __('app.rejected') }}
                  </option>
               </select>
            </div>
            <div>
               <select name="warehouse_id" class="w-full border rounded px-2 py-1">
                  <option value="">{{ __('app.all_warehouses') }}</option>
                  @foreach ($warehouses as $wh)
                     <option value="{{ $wh->id }}" {{ $warehouseId == $wh->id ? 'selected' : '' }}>
                        {{ $wh->name }}</option>
                  @endforeach
               </select>
            </div>
            <div>
               <button class="w-full px-3 py-1 bg-secondary text-white rounded">{{ __('app.filter') }}</button>
            </div>
         </div>
         <div class="grid grid-cols-2 gap-3 mt-3">
            <div>
               <input type="date" name="date_from" value="{{ $dateFrom ?? '' }}"
                  class="w-full border rounded px-2 py-1" placeholder="{{ __('app.from') }}" />
            </div>
            <div>
               <input type="date" name="date_to" value="{{ $dateTo ?? '' }}" class="w-full border rounded px-2 py-1"
                  placeholder="{{ __('app.to') }}" />
            </div>
         </div>
      </form>

      <div class="bg-white rounded shadow overflow-hidden">
         <table class="min-w-full">
            <thead class="bg-gray-50">
               <tr>
                  <th class="text-left p-3">{{ __('app.transfer_number') }}</th>
                  <th class="text-left p-3">{{ __('app.date') }}</th>
                  <th class="text-left p-3">{{ __('app.from_warehouse') }}</th>
                  <th class="text-left p-3">{{ __('app.to_warehouse') }}</th>
                  <th class="text-left p-3">{{ __('app.product_items') }}</th>
                  <th class="text-left p-3">{{ __('app.status') }}</th>
                  <th class="text-left p-3">{{ __('app.created_by') }}</th>
                  <th class="text-left p-3">{{ __('app.actions') }}</th>
               </tr>
            </thead>
            <tbody>
               @forelse($transfers as $transfer)
                  <tr class="border-t hover:bg-gray-50">
                     <td class="p-3 font-medium">{{ $transfer->transfer_number }}</td>
                     <td class="p-3">{{ $transfer->transfer_date->format('d M Y') }}</td>
                     <td class="p-3">{{ $transfer->fromWarehouse->name }}</td>
                     <td class="p-3">{{ $transfer->toWarehouse->name }}</td>
                     <td class="p-3">{{ $transfer->items->count() }} items</td>
                     <td class="p-3">
                        @if ($transfer->status === 'pending')
                           <span class="px-2 py-1 text-xs bg-warning text-white rounded">{{ __('app.pending') }}</span>
                        @elseif($transfer->status === 'approved')
                           <span class="px-2 py-1 text-xs bg-primary text-white rounded">{{ __('app.approved') }}</span>
                        @elseif($transfer->status === 'in_transit')
                           <span
                              class="px-2 py-1 text-xs bg-blue-500 text-white rounded">{{ __('app.in_transit') }}</span>
                        @elseif($transfer->status === 'completed')
                           <span class="px-2 py-1 text-xs bg-success text-white rounded">{{ __('app.completed') }}</span>
                        @else
                           <span class="px-2 py-1 text-xs bg-danger text-white rounded">{{ __('app.rejected') }}</span>
                        @endif
                     </td>
                     <td class="p-3">{{ $transfer->creator->name ?? '-' }}</td>
                     <td class="p-3">
                        <a href="{{ route('transfers.show', $transfer) }}"
                           class="text-blue-600 mr-2">{{ __('app.view') }}</a>
                        @if ($transfer->status === 'pending')
                           <form action="{{ route('transfers.destroy', $transfer) }}" method="POST" class="inline-block"
                              onsubmit="return confirm('{{ __('app.confirm_delete', ['item' => __('app.warehouse_transfer')]) }}')">
                              @csrf
                              @method('DELETE')
                              <button class="text-red-600">{{ __('app.delete') }}</button>
                           </form>
                        @endif
                     </td>
                  </tr>
               @empty
                  <tr>
                     <td colspan="8" class="p-12">
                        <div class="text-center">
                           <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" viewBox="0 0 24 24"
                              stroke="currentColor">
                              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                 d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4" />
                           </svg>
                           <h3 class="mt-2 text-sm font-medium text-gray-900">{{ __('app.no_transfer_data') }}</h3>
                           <p class="mt-1 text-sm text-gray-500">
                              {{ __('app.get_started', ['item' => __('app.warehouse_transfer')]) }}</p>
                           <div class="mt-6">
                              <a href="{{ route('transfers.create') }}"
                                 class="inline-flex items-center px-4 py-2 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-primary hover:bg-primary/90 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary">
                                 <svg class="-ml-1 mr-2 h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                       d="M12 4v16m8-8H4" />
                                 </svg>
                                 {{ __('app.new_transfer') }}
                              </a>
                           </div>
                        </div>
                     </td>
                  </tr>
               @endforelse
            </tbody>
         </table>
      </div>

      <div class="mt-4">{{ $transfers->links() }}</div>
   </div>
@endsection
