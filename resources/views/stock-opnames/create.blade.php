@extends('layouts.app')

@section('title', 'Buat Stok Opname')

@section('content')
    <div class="max-w-4xl mx-auto">
       <h2 class="text-xl font-semibold mb-4">{{ __('app.record_stock_opname') }}</h2>
 
       @if ($errors->any())
          <div class="mb-4 p-3 bg-red-50 border border-red-200 text-red-700 rounded">
             <ul class="list-disc list-inside">
                @foreach ($errors->all() as $error)
                   <li>{{ $error }}</li>
                @endforeach
             </ul>
          </div>
       @endif
 
       <form method="POST" action="{{ route('stock-opnames.store') }}" class="bg-white p-4 rounded shadow">
          @csrf
 
          <div class="mb-4">
             <label class="block text-sm mb-1">{{ __('app.date') }} <span class="text-red-500">*</span></label>
             <input type="date" name="date" value="{{ old('date', date('Y-m-d')) }}"
                class="w-full border rounded px-2 py-1" required />
          </div>
 
          <div class="mb-4">
             <label class="block text-sm mb-1">{{ __('app.product') }} <span class="text-red-500">*</span></label>
             <select name="product_id" id="productSelect" class="w-full border rounded px-2 py-1" required
                onchange="updateSystemQty()">
                <option value="">{{ __('app.select_product') }}</option>
                @foreach ($products as $p)
                   @php
                      $totalStock = $p->warehouses->sum('pivot.stock');
                   @endphp
                   <option value="{{ $p->id }}" data-stock="{{ $totalStock }}" data-unit="{{ $p->unit }}"
                      {{ old('product_id') == $p->id ? 'selected' : '' }}>
                      {{ $p->code }} - {{ $p->name }} ({{ __('app.stock') }}: {{ $totalStock }})
                   </option>
                @endforeach
             </select>
          </div>
 
          <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
             <div>
                <label class="block text-sm mb-1">{{ __('app.system_qty') }}</label>
                <input type="text" id="systemQty" class="w-full border rounded px-2 py-1 bg-gray-100" readonly
                   placeholder="{{ __('app.select_product') }}" />
             </div>
             <div>
                <label class="block text-sm mb-1">{{ __('app.counted_qty') }} <span class="text-red-500">*</span></label>
                <input type="number" name="counted_qty" value="{{ old('counted_qty', 0) }}"
                   class="w-full border rounded px-2 py-1" required min="0" id="countedQty"
                   onchange="calculateDifference()" />
             </div>
          </div>
 
          <div class="mb-4">
             <label class="block text-sm mb-1">{{ __('app.difference') }}</label>
             <input type="text" id="difference" class="w-full border rounded px-2 py-1 bg-gray-100 font-semibold"
                readonly placeholder="" />
          </div>
 
          <div class="mb-4">
             <label class="block text-sm mb-1">{{ __('app.reason') }} <span class="text-red-500">*</span></label>
             <select name="reason" class="w-full border rounded px-2 py-1" required>
                <option value="">{{ __('app.select_option', ['item' => __('app.reason')]) }}</option>
                <option value="Damaged">{{ __('app.damaged') }}</option>
                <option value="Lost">{{ __('app.lost') }}</option>
                <option value="Expired">{{ __('app.expired') }}</option>
                <option value="Found">{{ __('app.found') }}</option>
                <option value="Counting Error">{{ __('app.counting_error') }}</option>
                <option value="Other">{{ __('app.other') }}</option>
             </select>
          </div>
 
          <div class="flex gap-2 mt-6">
             <button type="submit" class="px-4 py-2 bg-warning text-white rounded">{{ __('app.confirm_record_opname') }}</button>
             <a href="{{ route('stock-opnames.index') }}" class="px-4 py-2 border rounded">{{ __('app.cancel') }}</a>
          </div>
       </form>
    </div>

   <script>
      function updateSystemQty() {
         const select = document.getElementById('productSelect');
         const option = select.options[select.selectedIndex];
         const stock = option.getAttribute('data-stock') || '';
         const unit = option.getAttribute('data-unit') || '';

         document.getElementById('systemQty').value = stock ? `${stock} ${unit}` : '';
         calculateDifference();
      }

      function calculateDifference() {
         const select = document.getElementById('productSelect');
         const option = select.options[select.selectedIndex];
         const systemQty = parseFloat(option.getAttribute('data-stock')) || 0;
         const countedQty = parseFloat(document.getElementById('countedQty').value) || 0;
         const difference = countedQty - systemQty;

         const diffEl = document.getElementById('difference');
         diffEl.value = difference > 0 ? `+${difference}` : difference;

         if (difference < 0) {
            diffEl.classList.add('text-red-600');
            diffEl.classList.remove('text-green-600');
         } else if (difference > 0) {
            diffEl.classList.add('text-green-600');
            diffEl.classList.remove('text-red-600');
         } else {
            diffEl.classList.remove('text-red-600', 'text-green-600');
         }
      }
   </script>
@endsection
