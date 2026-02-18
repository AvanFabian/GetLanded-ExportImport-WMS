@extends('layouts.app')

@section('content')
   <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
      <div class="flex justify-between items-center mb-6">
         <div>
            <h1 class="text-3xl font-bold text-gray-900">{{ $outboundShipment->shipment_number }}</h1>
            <p class="mt-1 text-sm text-gray-600">{{ __('Outbound Shipment Detail') }}</p>
         </div>
         <a href="{{ route('outbound-shipments.index') }}" class="px-4 py-2 bg-gray-200 hover:bg-gray-300 text-gray-700 font-semibold rounded-lg transition">← {{ __('Back') }}</a>
      </div>

      <!-- Export Documents -->
      <div class="flex gap-3 mb-6">
         <a href="{{ route('outbound-shipments.invoice-pdf', $outboundShipment) }}"
            class="inline-flex items-center px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white font-semibold rounded-lg transition shadow-sm">
            📄 {{ __('Commercial Invoice PDF') }}
         </a>
         <a href="{{ route('outbound-shipments.packing-list-pdf', $outboundShipment) }}"
            class="inline-flex items-center px-4 py-2 bg-emerald-600 hover:bg-emerald-700 text-white font-semibold rounded-lg transition shadow-sm">
            📦 {{ __('Packing List PDF') }}
         </a>
      </div>

      <!-- Shipment Info -->
      <div class="bg-white rounded-lg shadow-md p-6 mb-6">
         <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
            <dl class="space-y-2">
               <div class="flex justify-between"><dt class="text-sm text-gray-600">{{ __('Sales Order') }}</dt>
                  <dd class="text-sm font-medium"><a href="{{ route('sales-orders.show', $outboundShipment->salesOrder) }}" class="text-blue-600">{{ $outboundShipment->salesOrder->so_number }}</a></dd></div>
               <div class="flex justify-between"><dt class="text-sm text-gray-600">{{ __('Customer') }}</dt><dd class="text-sm font-medium">{{ $outboundShipment->salesOrder->customer->name ?? '-' }}</dd></div>
               <div class="flex justify-between"><dt class="text-sm text-gray-600">{{ __('Incoterm') }}</dt><dd class="text-sm font-medium">{{ $outboundShipment->incoterm ?? '-' }}</dd></div>
               <div class="flex justify-between"><dt class="text-sm text-gray-600">{{ __('Status') }}</dt>
                  <dd>
                     @php $colors = ['draft'=>'gray','booked'=>'blue','shipped'=>'indigo','in_transit'=>'yellow','arrived'=>'green','delivered'=>'emerald']; @endphp
                     <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-{{ $colors[$outboundShipment->status] ?? 'gray' }}-100 text-{{ $colors[$outboundShipment->status] ?? 'gray' }}-800">{{ \App\Models\OutboundShipment::STATUSES[$outboundShipment->status] ?? ucfirst($outboundShipment->status) }}</span>
                  </dd></div>
            </dl>
            <dl class="space-y-2">
               <div class="flex justify-between"><dt class="text-sm text-gray-600">{{ __('Carrier') }}</dt><dd class="text-sm font-medium">{{ $outboundShipment->carrier_name ?? '-' }}</dd></div>
               <div class="flex justify-between"><dt class="text-sm text-gray-600">{{ __('Vessel') }}</dt><dd class="text-sm font-medium">{{ $outboundShipment->vessel_name ?? '-' }} {{ $outboundShipment->voyage_number ? '/ ' . $outboundShipment->voyage_number : '' }}</dd></div>
               <div class="flex justify-between"><dt class="text-sm text-gray-600">{{ __('B/L') }}</dt><dd class="text-sm font-medium">{{ $outboundShipment->bill_of_lading ?? '-' }}</dd></div>
               <div class="flex justify-between"><dt class="text-sm text-gray-600">{{ __('Booking') }}</dt><dd class="text-sm font-medium">{{ $outboundShipment->booking_number ?? '-' }}</dd></div>
            </dl>
            <dl class="space-y-2">
               <div class="flex justify-between"><dt class="text-sm text-gray-600">{{ __('Route') }}</dt><dd class="text-sm font-medium">{{ $outboundShipment->port_of_loading ?? '-' }} → {{ $outboundShipment->port_of_discharge ?? '-' }}</dd></div>
               <div class="flex justify-between"><dt class="text-sm text-gray-600">{{ __('Ship Date') }}</dt><dd class="text-sm font-medium">{{ $outboundShipment->shipment_date->format('d M Y') }}</dd></div>
               <div class="flex justify-between"><dt class="text-sm text-gray-600">{{ __('ETA') }}</dt><dd class="text-sm font-medium">{{ $outboundShipment->estimated_arrival?->format('d M Y') ?? '-' }}</dd></div>
               <div class="flex justify-between"><dt class="text-sm text-gray-600">{{ __('Freight + Insurance') }}</dt>
                  <dd class="text-sm font-bold text-emerald-700">{{ $outboundShipment->currency_code }} {{ number_format($outboundShipment->freight_cost + $outboundShipment->insurance_cost, 2) }}</dd></div>
            </dl>
         </div>
      </div>

      <!-- Status Update -->
      @if($outboundShipment->status !== 'delivered')
         <div class="bg-white rounded-lg shadow-md p-6 mb-6">
            <h3 class="text-lg font-bold text-gray-900 mb-4">{{ __('Update Status') }}</h3>
            <form method="POST" action="{{ route('outbound-shipments.update-status', $outboundShipment) }}" class="flex gap-4 items-end">
               @csrf
               <div class="flex-1">
                  <select name="status" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-emerald-500">
                     @foreach(\App\Models\OutboundShipment::STATUSES as $key => $label)
                        <option value="{{ $key }}" {{ $outboundShipment->status === $key ? 'selected' : '' }}>{{ $label }}</option>
                     @endforeach
                  </select>
               </div>
               <div>
                  <label class="block text-xs text-gray-600 mb-1">{{ __('Actual Arrival') }}</label>
                  <input type="date" name="actual_arrival" value="{{ $outboundShipment->actual_arrival?->format('Y-m-d') }}" class="px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-emerald-500">
               </div>
               <button type="submit" class="px-6 py-2 bg-blue-600 hover:bg-blue-700 text-white font-semibold rounded-lg transition">{{ __('Update') }}</button>
            </form>
         </div>
      @endif

      <!-- Containers -->
      <div class="bg-white rounded-lg shadow-md overflow-hidden mb-6">
         <div class="px-6 py-4 bg-gray-50 border-b flex justify-between items-center">
            <h3 class="text-lg font-bold text-gray-900">{{ __('Containers') }} ({{ $outboundShipment->containers->count() }})</h3>
         </div>
         @if($outboundShipment->containers->count() > 0)
            <div class="divide-y">
               @foreach($outboundShipment->containers as $container)
                  <div class="px-6 py-4 flex justify-between items-center">
                     <div>
                        <a href="{{ route('containers.show', $container) }}" class="text-emerald-600 hover:text-emerald-900 font-medium">{{ $container->container_number }}</a>
                        <span class="text-xs text-gray-500 ml-2">{{ \App\Models\Container::TYPES[$container->container_type]['label'] ?? $container->container_type }}</span>
                        @if($container->seal_number)
                           <span class="text-xs text-blue-600 ml-2">🔒 {{ $container->seal_number }}</span>
                        @endif
                     </div>
                     <div class="text-right">
                        <div class="text-sm">{{ number_format($container->utilizationPercent(), 1) }}% {{ __('utilized') }}</div>
                        <div class="text-xs text-gray-500">{{ number_format($container->used_weight_kg) }}kg / {{ number_format($container->used_volume_cbm, 2) }}cbm</div>
                     </div>
                  </div>
               @endforeach
            </div>
         @else
            <div class="px-6 py-8 text-center text-gray-500">{{ __('No containers assigned to this shipment.') }}</div>
         @endif
      </div>

      <!-- Shipment Expenses -->
      <div class="bg-white rounded-lg shadow-md overflow-hidden mb-6">
         <div class="px-6 py-4 bg-gray-50 border-b">
            <h3 class="text-lg font-bold text-gray-900">{{ __('Shipment Expenses') }}</h3>
         </div>

         @if($outboundShipment->expenses->count() > 0)
            <table class="min-w-full divide-y divide-gray-200">
               <thead class="bg-gray-50">
                  <tr>
                     <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">{{ __('Expense') }}</th>
                     <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">{{ __('Amount') }}</th>
                     <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">{{ __('Notes') }}</th>
                     <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase"></th>
                  </tr>
               </thead>
               <tbody class="divide-y divide-gray-200">
                  @foreach($outboundShipment->expenses as $expense)
                  <tr>
                     <td class="px-6 py-3 text-sm font-medium text-gray-900">{{ $expense->name }}</td>
                     <td class="px-6 py-3 text-sm text-right font-semibold text-emerald-700">{{ $expense->currency_code }} {{ number_format($expense->amount, 2) }}</td>
                     <td class="px-6 py-3 text-sm text-gray-500">{{ $expense->notes ?? '-' }}</td>
                     <td class="px-6 py-3 text-right">
                        <form method="POST" action="{{ route('outbound-shipments.remove-expense', [$outboundShipment, $expense]) }}" onsubmit="return confirm('Remove this expense?')">
                           @csrf @method('DELETE')
                           <button type="submit" class="text-red-500 hover:text-red-700 text-sm">✕</button>
                        </form>
                     </td>
                  </tr>
                  @endforeach
               </tbody>
               <tfoot>
                  <tr class="bg-gray-50 font-bold">
                     <td class="px-6 py-3 text-sm">{{ __('Total') }}</td>
                     <td class="px-6 py-3 text-sm text-right text-emerald-700">{{ $outboundShipment->currency_code ?? 'USD' }} {{ number_format($outboundShipment->expenses->sum('amount'), 2) }}</td>
                     <td colspan="2"></td>
                  </tr>
               </tfoot>
            </table>
         @else
            <div class="px-6 py-4 text-sm text-gray-500">{{ __('No expenses recorded yet.') }}</div>
         @endif

         <!-- Add Expense Form -->
         <div class="px-6 py-4 border-t bg-gray-50">
            <form method="POST" action="{{ route('outbound-shipments.add-expense', $outboundShipment) }}" class="flex gap-3 items-end flex-wrap">
               @csrf
               <div>
                  <label class="block text-xs text-gray-600 mb-1">{{ __('Name') }}</label>
                  <select name="name" class="px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-emerald-500" required>
                     <option value="">{{ __('Select...') }}</option>
                     <option value="Ocean Freight">Ocean Freight</option>
                     <option value="Air Freight">Air Freight</option>
                     <option value="Insurance">Insurance</option>
                     <option value="THC (Terminal Handling)">THC (Terminal Handling)</option>
                     <option value="Documentation Fee">Documentation Fee</option>
                     <option value="Customs Clearance">Customs Clearance</option>
                     <option value="Trucking">Trucking</option>
                     <option value="Fumigation">Fumigation</option>
                     <option value="Other">Other</option>
                  </select>
               </div>
               <div>
                  <label class="block text-xs text-gray-600 mb-1">{{ __('Amount') }}</label>
                  <input type="number" name="amount" step="0.01" min="0" class="px-3 py-2 border border-gray-300 rounded-lg text-sm w-32 focus:ring-2 focus:ring-emerald-500" required>
               </div>
               <div>
                  <label class="block text-xs text-gray-600 mb-1">{{ __('Currency') }}</label>
                  <input type="text" name="currency_code" value="{{ $outboundShipment->currency_code ?? 'USD' }}" maxlength="3" class="px-3 py-2 border border-gray-300 rounded-lg text-sm w-20 uppercase focus:ring-2 focus:ring-emerald-500" required>
               </div>
               <div>
                  <label class="block text-xs text-gray-600 mb-1">{{ __('Notes') }}</label>
                  <input type="text" name="notes" class="px-3 py-2 border border-gray-300 rounded-lg text-sm w-40 focus:ring-2 focus:ring-emerald-500" placeholder="Optional">
               </div>
               <button type="submit" class="px-4 py-2 bg-emerald-600 hover:bg-emerald-700 text-white font-semibold rounded-lg text-sm transition">+ {{ __('Add') }}</button>
            </form>
         </div>
      </div>

      @if($outboundShipment->notes)
         <div class="bg-white rounded-lg shadow-md p-6">
            <h3 class="text-sm font-medium text-gray-500 uppercase mb-2">{{ __('Notes') }}</h3>
            <p class="text-sm text-gray-700">{{ $outboundShipment->notes }}</p>
         </div>
      @endif
   </div>
@endsection
