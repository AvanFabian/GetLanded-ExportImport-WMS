@extends('layouts.app')

@section('content')
   <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
      <div class="flex justify-between items-center mb-6">
         <div>
            <h1 class="text-3xl font-bold text-gray-900">{{ __('New Customs Declaration') }}</h1>
            <p class="mt-1 text-sm text-gray-600">{{ __('Create an export/import customs declaration') }}</p>
         </div>
         <a href="{{ route('customs.index') }}" class="px-4 py-2 bg-gray-200 hover:bg-gray-300 text-gray-700 font-semibold rounded-lg transition">← {{ __('Back') }}</a>
      </div>

      <div class="bg-white rounded-lg shadow-md p-6">
         <form method="POST" action="{{ route('customs.store') }}" class="space-y-6">
            @csrf
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
               <div>
                  <label for="declaration_type" class="block text-sm font-medium text-gray-700 mb-2">{{ __('Type') }} *</label>
                  <select id="declaration_type" name="declaration_type" required class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-emerald-500">
                     <option value="export" {{ old('declaration_type') === 'export' ? 'selected' : '' }}>{{ __('Export') }}</option>
                     <option value="import" {{ old('declaration_type') === 'import' ? 'selected' : '' }}>{{ __('Import') }}</option>
                  </select>
               </div>
               <div>
                  <label for="declaration_date" class="block text-sm font-medium text-gray-700 mb-2">{{ __('Date') }} *</label>
                  <input type="date" id="declaration_date" name="declaration_date" value="{{ old('declaration_date', date('Y-m-d')) }}" required class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-emerald-500">
               </div>
               <div>
                  <label for="outbound_shipment_id" class="block text-sm font-medium text-gray-700 mb-2">{{ __('Linked Shipment') }}</label>
                  <select id="outbound_shipment_id" name="outbound_shipment_id" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-emerald-500">
                     <option value="">{{ __('None') }}</option>
                     @foreach($shipments as $shp)
                        <option value="{{ $shp->id }}" {{ old('outbound_shipment_id') == $shp->id ? 'selected' : '' }}>{{ $shp->shipment_number }} — {{ $shp->salesOrder->customer->name ?? '' }}</option>
                     @endforeach
                  </select>
               </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
               <div>
                  <label for="customs_office" class="block text-sm font-medium text-gray-700 mb-2">{{ __('Customs Office') }}</label>
                  <input type="text" id="customs_office" name="customs_office" value="{{ old('customs_office') }}" placeholder="KPU Tanjung Priok" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-emerald-500">
               </div>
               <div>
                  <label for="hs_code" class="block text-sm font-medium text-gray-700 mb-2">{{ __('HS Code') }}</label>
                  <input type="text" id="hs_code" name="hs_code" value="{{ old('hs_code') }}" placeholder="0901.11.00" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-emerald-500">
               </div>
            </div>

            <h3 class="text-lg font-bold text-gray-900 border-b pb-2">{{ __('Duty & Tax Calculation') }}</h3>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
               <div>
                  <label for="declared_value" class="block text-sm font-medium text-gray-700 mb-2">{{ __('Declared Value') }} *</label>
                  <input type="number" step="0.01" id="declared_value" name="declared_value" value="{{ old('declared_value') }}" required class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-emerald-500" oninput="calcDuty()">
               </div>
               <div>
                  <label for="currency_code" class="block text-sm font-medium text-gray-700 mb-2">{{ __('Currency') }}</label>
                  <input type="text" id="currency_code" name="currency_code" value="{{ old('currency_code', 'USD') }}" maxlength="3" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-emerald-500">
               </div>
            </div>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
               <div>
                  <label for="duty_rate" class="block text-sm font-medium text-gray-700 mb-2">{{ __('BM / Duty Rate (%)') }}</label>
                  <input type="number" step="0.01" id="duty_rate" name="duty_rate" value="{{ old('duty_rate', 0) }}" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-emerald-500" oninput="calcDuty()">
               </div>
               <div>
                  <label for="vat_rate" class="block text-sm font-medium text-gray-700 mb-2">{{ __('PPN / VAT Rate (%)') }}</label>
                  <input type="number" step="0.01" id="vat_rate" name="vat_rate" value="{{ old('vat_rate', 11) }}" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-emerald-500" oninput="calcDuty()">
               </div>
               <div>
                  <label for="excise_amount" class="block text-sm font-medium text-gray-700 mb-2">{{ __('Excise / Cukai') }}</label>
                  <input type="number" step="0.01" id="excise_amount" name="excise_amount" value="{{ old('excise_amount', 0) }}" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-emerald-500" oninput="calcDuty()">
               </div>
            </div>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
               <div>
                  <label for="pph_rate" class="block text-sm font-medium text-gray-700 mb-2">{{ __('PPh 22 Rate (%)') }}</label>
                  <input type="number" step="0.01" id="pph_rate" name="pph_rate" value="{{ old('pph_rate', 2.5) }}" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-emerald-500" oninput="calcDuty()">
                  <p class="text-xs text-gray-500 mt-1">{{ __('2.5% with API-U, 7.5% without') }}</p>
               </div>
               <div>
                  <label for="anti_dumping_rate" class="block text-sm font-medium text-gray-700 mb-2">{{ __('Anti-dumping Rate (%)') }}</label>
                  <input type="number" step="0.01" id="anti_dumping_rate" name="anti_dumping_rate" value="{{ old('anti_dumping_rate', 0) }}" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-emerald-500" oninput="calcDuty()">
               </div>
               <div>
                  <label for="fta_scheme" class="block text-sm font-medium text-gray-700 mb-2">{{ __('FTA Scheme') }}</label>
                  <select id="fta_scheme" name="fta_scheme" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-emerald-500">
                     <option value="">{{ __('None') }}</option>
                     @if(isset($ftaSchemes) && $ftaSchemes->count() > 0)
                        @foreach($ftaSchemes as $scheme)
                           <option value="{{ $scheme->name }}" {{ old('fta_scheme') == $scheme->name ? 'selected' : '' }}>{{ $scheme->name }} - {{ $scheme->description }}</option>
                        @endforeach
                     @else
                        <!-- Fallback / Default Schemes if none defined in DB -->
                        <option value="ACFTA" {{ old('fta_scheme') === 'ACFTA' ? 'selected' : '' }}>ACFTA (ASEAN-China)</option>
                        <option value="AKFTA" {{ old('fta_scheme') === 'AKFTA' ? 'selected' : '' }}>AKFTA (ASEAN-Korea)</option>
                        <option value="IJEPA" {{ old('fta_scheme') === 'IJEPA' ? 'selected' : '' }}>IJEPA (Indonesia-Japan)</option>
                        <option value="AIFTA" {{ old('fta_scheme') === 'AIFTA' ? 'selected' : '' }}>AIFTA (ASEAN-India)</option>
                        <option value="AANZFTA" {{ old('fta_scheme') === 'AANZFTA' ? 'selected' : '' }}>AANZFTA (ASEAN-AUS-NZ)</option>
                        <option value="IA-CEPA" {{ old('fta_scheme') === 'IA-CEPA' ? 'selected' : '' }}>IA-CEPA (Indonesia-Australia)</option>
                        <option value="RCEP" {{ old('fta_scheme') === 'RCEP' ? 'selected' : '' }}>RCEP</option>
                        <option value="ATIGA" {{ old('fta_scheme') === 'ATIGA' ? 'selected' : '' }}>ATIGA (ASEAN Trade)</option>
                     @endif
                  </select>
               </div>
            </div>
            <div class="bg-blue-50 rounded-lg p-4">
               <div class="grid grid-cols-5 gap-4 text-sm">
                  <div><span class="text-gray-600">{{ __('BM') }}:</span> <span id="calc_duty" class="font-bold">0.00</span></div>
                  <div><span class="text-gray-600">{{ __('PPN') }}:</span> <span id="calc_vat" class="font-bold">0.00</span></div>
                  <div><span class="text-gray-600">{{ __('PPh 22') }}:</span> <span id="calc_pph" class="font-bold">0.00</span></div>
                  <div><span class="text-gray-600">{{ __('BMAD') }}:</span> <span id="calc_ad" class="font-bold">0.00</span></div>
                  <div><span class="text-gray-600">{{ __('Total Tax') }}:</span> <span id="calc_total" class="font-bold text-lg text-red-600">0.00</span></div>
               </div>
            </div>

            <!-- Declaration Items -->
            <h3 class="text-lg font-bold text-gray-900 border-b pb-2">{{ __('Declaration Items') }}</h3>
            <div id="decl-items">
               <div class="decl-row grid grid-cols-12 gap-3 mb-2 items-end">
                  <div class="col-span-3">
                     <label class="block text-sm font-medium text-gray-700 mb-1">{{ __('Product') }}</label>
                     <select name="items[0][product_id]" class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm">
                        <option value="">{{ __('Select') }}</option>
                        @foreach($products as $p)<option value="{{ $p->id }}">{{ $p->name }}</option>@endforeach
                     </select>
                  </div>
                  <div class="col-span-2"><label class="block text-sm font-medium text-gray-700 mb-1">{{ __('Description') }}</label><input type="text" name="items[0][description]" class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm"></div>
                  <div class="col-span-2"><label class="block text-sm font-medium text-gray-700 mb-1">{{ __('HS Code') }}</label><input type="text" name="items[0][hs_code]" class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm"></div>
                  <div class="col-span-1"><label class="block text-sm font-medium text-gray-700 mb-1">{{ __('Qty') }}</label><input type="number" name="items[0][quantity]" min="1" class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm"></div>
                  <div class="col-span-2"><label class="block text-sm font-medium text-gray-700 mb-1">{{ __('Unit Value') }}</label><input type="number" step="0.01" name="items[0][unit_value]" class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm"></div>
                  <div class="col-span-2 flex gap-1"><button type="button" onclick="addDeclRow()" class="px-3 py-2 bg-emerald-600 hover:bg-emerald-700 text-white rounded-lg text-sm">+</button></div>
               </div>
            </div>

            <div>
               <label for="notes" class="block text-sm font-medium text-gray-700 mb-2">{{ __('Notes') }}</label>
               <textarea id="notes" name="notes" rows="3" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-emerald-500">{{ old('notes') }}</textarea>
            </div>

            <div class="flex justify-end gap-3 pt-4 border-t">
               <a href="{{ route('customs.index') }}" class="px-6 py-2 bg-gray-200 hover:bg-gray-300 text-gray-700 font-semibold rounded-lg transition">{{ __('Cancel') }}</a>
               <button type="submit" class="px-6 py-2 bg-emerald-600 hover:bg-emerald-700 text-white font-semibold rounded-lg transition">{{ __('Create Declaration') }}</button>
            </div>
         </form>
      </div>
   </div>

   <script>
      function calcDuty() {
         const val = parseFloat(document.getElementById('declared_value').value) || 0;
         const dr = parseFloat(document.getElementById('duty_rate').value) || 0;
         const vr = parseFloat(document.getElementById('vat_rate').value) || 0;
         const ex = parseFloat(document.getElementById('excise_amount').value) || 0;
         const pr = parseFloat(document.getElementById('pph_rate').value) || 0;
         const ad = parseFloat(document.getElementById('anti_dumping_rate').value) || 0;

         const duty = val * (dr / 100);
         const vat = (val + duty) * (vr / 100);
         const pph = (val + duty) * (pr / 100);
         const adAmount = val * (ad / 100);

         document.getElementById('calc_duty').textContent = duty.toFixed(2);
         document.getElementById('calc_vat').textContent = vat.toFixed(2);
         document.getElementById('calc_pph').textContent = pph.toFixed(2);
         document.getElementById('calc_ad').textContent = adAmount.toFixed(2);
         document.getElementById('calc_total').textContent = (duty + vat + pph + adAmount + ex).toFixed(2);
      }
      calcDuty();

      let declIdx = 1;
      function addDeclRow() {
         const cont = document.getElementById('decl-items');
         const row = document.createElement('div');
         row.className = 'decl-row grid grid-cols-12 gap-3 mb-2 items-end';
         row.innerHTML = `
            <div class="col-span-3"><select name="items[${declIdx}][product_id]" class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm"><option value="">{{ __('Select') }}</option>@foreach($products as $p)<option value="{{ $p->id }}">{{ $p->name }}</option>@endforeach</select></div>
            <div class="col-span-2"><input type="text" name="items[${declIdx}][description]" class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm"></div>
            <div class="col-span-2"><input type="text" name="items[${declIdx}][hs_code]" class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm"></div>
            <div class="col-span-1"><input type="number" name="items[${declIdx}][quantity]" min="1" class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm"></div>
            <div class="col-span-2"><input type="number" step="0.01" name="items[${declIdx}][unit_value]" class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm"></div>
            <div class="col-span-2"><button type="button" onclick="this.closest('.decl-row').remove()" class="px-3 py-2 bg-red-500 hover:bg-red-600 text-white rounded-lg text-sm">×</button></div>`;
         cont.appendChild(row);
         declIdx++;
      }
   </script>
@endsection
