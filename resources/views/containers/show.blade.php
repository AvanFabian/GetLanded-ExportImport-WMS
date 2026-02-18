@extends('layouts.app')

@section('content')
   <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
      <div class="flex justify-between items-center mb-6">
         <div>
            <h1 class="text-3xl font-bold text-gray-900">{{ $container->container_number }}</h1>
            <p class="mt-1 text-sm text-gray-600">{{ \App\Models\Container::TYPES[$container->container_type]['label'] ?? $container->container_type }} — {{ __('Container Detail & Stuffing') }}</p>
         </div>
         <a href="{{ route('containers.index') }}" class="px-4 py-2 bg-gray-200 hover:bg-gray-300 text-gray-700 font-semibold rounded-lg transition">← {{ __('Back') }}</a>
      </div>

      <!-- Info & Utilization -->
      <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
         <div class="bg-white rounded-lg shadow-md p-6">
            <dl class="space-y-2">
               <div class="flex justify-between"><dt class="text-sm text-gray-600">{{ __('Status') }}</dt>
                  <dd><span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium
                     {{ $container->status === 'sealed' ? 'bg-blue-100 text-blue-800' : ($container->status === 'empty' ? 'bg-gray-100 text-gray-800' : 'bg-yellow-100 text-yellow-800') }}">
                     {{ \App\Models\Container::STATUSES[$container->status] ?? ucfirst($container->status) }}</span></dd></div>
               @if($container->outboundShipment)
                  <div class="flex justify-between"><dt class="text-sm text-gray-600">{{ __('Shipment') }}</dt>
                     <dd class="text-sm font-medium"><a href="{{ route('outbound-shipments.show', $container->outboundShipment) }}" class="text-blue-600">{{ $container->outboundShipment->shipment_number }}</a></dd></div>
                  <div class="flex justify-between"><dt class="text-sm text-gray-600">{{ __('Customer') }}</dt><dd class="text-sm font-medium">{{ $container->outboundShipment->salesOrder->customer->name ?? '-' }}</dd></div>
               @endif
               @if($container->seal_number)
                  <div class="flex justify-between"><dt class="text-sm text-gray-600">{{ __('Seal #') }}</dt><dd class="text-sm font-medium text-blue-600">🔒 {{ $container->seal_number }}</dd></div>
               @endif
            </dl>
         </div>
         <div class="bg-white rounded-lg shadow-md p-6">
            <h3 class="text-sm font-medium text-gray-500 uppercase mb-3">{{ __('Capacity') }}</h3>
            @php $util = $container->utilizationPercent(); @endphp
            <div class="mb-4">
               <div class="flex justify-between text-sm mb-1">
                  <span>{{ __('Overall') }}</span>
                  <span class="font-bold {{ $util > 90 ? 'text-red-600' : ($util > 70 ? 'text-yellow-600' : 'text-emerald-600') }}">{{ number_format($util, 1) }}%</span>
               </div>
               <div class="w-full bg-gray-200 rounded-full h-3">
                  <div class="h-3 rounded-full {{ $util > 90 ? 'bg-red-500' : ($util > 70 ? 'bg-yellow-500' : 'bg-emerald-500') }}" style="width: {{ min($util, 100) }}%"></div>
               </div>
            </div>
            <div class="grid grid-cols-2 gap-4 text-sm">
               <div><span class="text-gray-600">{{ __('Weight') }}:</span> <span class="font-semibold">{{ number_format($container->used_weight_kg) }} / {{ number_format($container->max_weight_kg) }} kg</span></div>
               <div><span class="text-gray-600">{{ __('Volume') }}:</span> <span class="font-semibold">{{ number_format($container->used_volume_cbm, 2) }} / {{ number_format($container->max_volume_cbm, 2) }} cbm</span></div>
               <div><span class="text-gray-600">{{ __('Remaining Weight') }}:</span> <span class="font-semibold text-emerald-600">{{ number_format($container->remainingWeightKg()) }} kg</span></div>
               <div><span class="text-gray-600">{{ __('Remaining Volume') }}:</span> <span class="font-semibold text-emerald-600">{{ number_format($container->remainingVolumeCbm(), 2) }} cbm</span></div>
            </div>
         </div>
      </div>

      <!-- Items in container -->
      <div class="bg-white rounded-lg shadow-md overflow-hidden mb-6">
         <div class="px-6 py-4 bg-gray-50 border-b"><h3 class="text-lg font-bold text-gray-900">{{ __('Packed Items') }} ({{ $container->items->count() }})</h3></div>
         <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
               <tr>
                  <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">{{ __('Product') }}</th>
                  <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">{{ __('Batch') }}</th>
                  <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">{{ __('Qty') }}</th>
                  <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">{{ __('Cartons') }}</th>
                  <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">{{ __('Weight (kg)') }}</th>
                  <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">{{ __('Volume (cbm)') }}</th>
                  @if(in_array($container->status, ['empty', 'loading']))
                     <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase">{{ __('Actions') }}</th>
                  @endif
               </tr>
            </thead>
            <tbody class="divide-y divide-gray-200">
               @forelse($container->items as $item)
                  <tr>
                     <td class="px-6 py-4 text-sm text-gray-900">{{ $item->product->name ?? '-' }}</td>
                     <td class="px-6 py-4 text-sm text-gray-600">{{ $item->batch->batch_number ?? '-' }}</td>
                     <td class="px-6 py-4 text-sm text-right">{{ $item->quantity }}</td>
                     <td class="px-6 py-4 text-sm text-right">{{ $item->carton_count ?: '-' }}</td>
                     <td class="px-6 py-4 text-sm text-right">{{ number_format($item->weight_kg, 2) }}</td>
                     <td class="px-6 py-4 text-sm text-right">{{ number_format($item->volume_cbm, 4) }}</td>
                     @if(in_array($container->status, ['empty', 'loading']))
                        <td class="px-6 py-4 text-center">
                           <form method="POST" action="{{ route('container-items.destroy', $item) }}" class="inline">@csrf @method('DELETE')
                              <button type="submit" class="text-red-600 hover:text-red-900 text-sm" onclick="return confirm('{{ __('Remove this item?') }}')">✕</button>
                           </form>
                        </td>
                     @endif
                  </tr>
               @empty
                  <tr><td colspan="7" class="px-6 py-8 text-center text-gray-500">{{ __('No items packed yet.') }}</td></tr>
               @endforelse
            </tbody>
         </table>
      </div>

      <!-- Stuffing Form (only for empty/loading) -->
      @if(in_array($container->status, ['empty', 'loading']))
         <div class="bg-white rounded-lg shadow-md p-6 mb-6">
            <h3 class="text-lg font-bold text-gray-900 mb-4">{{ __('Add Items (Stuffing)') }}</h3>
            <form method="POST" action="{{ route('containers.stuffing', $container) }}" class="space-y-4">
               @csrf
               <div id="stuffing-items">
                  <div class="stuff-row grid grid-cols-12 gap-3 mb-2 items-end">
                     <div class="col-span-3">
                        <label class="block text-sm font-medium text-gray-700 mb-1">{{ __('Product') }}</label>
                        <select name="items[0][product_id]" required class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm">
                           <option value="">{{ __('Select') }}</option>
                           @foreach($products as $p)<option value="{{ $p->id }}">{{ $p->name }}</option>@endforeach
                        </select>
                     </div>
                     <div class="col-span-1"><label class="block text-sm font-medium text-gray-700 mb-1">{{ __('Qty') }}</label><input type="number" name="items[0][quantity]" min="1" required class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm"></div>
                     <div class="col-span-2"><label class="block text-sm font-medium text-gray-700 mb-1">{{ __('Weight (kg)') }}</label><input type="number" step="0.01" name="items[0][weight_kg]" min="0" required class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm"></div>
                     <div class="col-span-2"><label class="block text-sm font-medium text-gray-700 mb-1">{{ __('Volume (cbm)') }}</label><input type="number" step="0.0001" name="items[0][volume_cbm]" min="0" required class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm"></div>
                     <div class="col-span-2"><label class="block text-sm font-medium text-gray-700 mb-1">{{ __('Cartons') }}</label><input type="number" name="items[0][carton_count]" min="0" class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm"></div>
                     <div class="col-span-2 flex gap-1">
                        <button type="button" onclick="addStuffRow()" class="px-3 py-2 bg-emerald-600 hover:bg-emerald-700 text-white rounded-lg text-sm">+</button>
                     </div>
                  </div>
               </div>
               <button type="submit" class="px-6 py-2 bg-emerald-600 hover:bg-emerald-700 text-white font-semibold rounded-lg transition">{{ __('Add to Container') }}</button>
            </form>
         </div>
      @endif

      <!-- Seal Form -->
      @if($container->status === 'loading')
         <div class="bg-white rounded-lg shadow-md p-6">
            <h3 class="text-lg font-bold text-gray-900 mb-4">{{ __('Seal Container') }}</h3>
            <form method="POST" action="{{ route('containers.seal', $container) }}" class="flex gap-4 items-end">
               @csrf
               <div class="flex-1">
                  <label class="block text-sm font-medium text-gray-700 mb-2">{{ __('Seal Number') }}</label>
                  <input type="text" name="seal_number" required placeholder="SEAL-00000001" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-emerald-500">
               </div>
               <button type="submit" class="px-6 py-2 bg-blue-600 hover:bg-blue-700 text-white font-semibold rounded-lg transition"
                  onclick="return confirm('{{ __('Seal this container? No more items can be added.') }}')">🔒 {{ __('Seal') }}</button>
            </form>
         </div>
      @endif
   </div>

   <script>
      let stuffIdx = 1;
      function addStuffRow() {
         const cont = document.getElementById('stuffing-items');
         const row = document.createElement('div');
         row.className = 'stuff-row grid grid-cols-12 gap-3 mb-2 items-end';
         row.innerHTML = `
            <div class="col-span-3"><select name="items[${stuffIdx}][product_id]" required class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm"><option value="">{{ __('Select') }}</option>@foreach($products as $p)<option value="{{ $p->id }}">{{ $p->name }}</option>@endforeach</select></div>
            <div class="col-span-1"><input type="number" name="items[${stuffIdx}][quantity]" min="1" required class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm"></div>
            <div class="col-span-2"><input type="number" step="0.01" name="items[${stuffIdx}][weight_kg]" min="0" required class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm"></div>
            <div class="col-span-2"><input type="number" step="0.0001" name="items[${stuffIdx}][volume_cbm]" min="0" required class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm"></div>
            <div class="col-span-2"><input type="number" name="items[${stuffIdx}][carton_count]" min="0" class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm"></div>
            <div class="col-span-2"><button type="button" onclick="this.closest('.stuff-row').remove()" class="px-3 py-2 bg-red-500 hover:bg-red-600 text-white rounded-lg text-sm">×</button></div>`;
         cont.appendChild(row);
         stuffIdx++;
      }
   </script>
@endsection
