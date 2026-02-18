@extends('layouts.app')

@section('content')
   <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
      <div class="flex justify-between items-center mb-6">
         <div>
            <h1 class="text-3xl font-bold text-gray-900">{{ __('Create Outbound Shipment') }}</h1>
            <p class="mt-1 text-sm text-gray-600">{{ __('Register a new export shipment') }}</p>
         </div>
         <a href="{{ route('outbound-shipments.index') }}" class="px-4 py-2 bg-gray-200 hover:bg-gray-300 text-gray-700 font-semibold rounded-lg transition">← {{ __('Back') }}</a>
      </div>

      <div class="bg-white rounded-lg shadow-md p-6">
         <form method="POST" action="{{ route('outbound-shipments.store') }}" class="space-y-6">
            @csrf
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
               <div>
                  <label for="sales_order_id" class="block text-sm font-medium text-gray-700 mb-2">{{ __('Sales Order') }} *</label>
                  <select id="sales_order_id" name="sales_order_id" required class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-emerald-500">
                     <option value="">{{ __('Select') }}</option>
                     @foreach ($salesOrders as $order)
                        <option value="{{ $order->id }}" {{ old('sales_order_id') == $order->id ? 'selected' : '' }}>{{ $order->so_number }} — {{ $order->customer->name ?? '' }}</option>
                     @endforeach
                  </select>
                  @error('sales_order_id') <p class="text-red-500 text-sm mt-1">{{ $message }}</p> @enderror
               </div>
               <div>
                  <label for="shipment_date" class="block text-sm font-medium text-gray-700 mb-2">{{ __('Shipment Date') }} *</label>
                  <input type="date" id="shipment_date" name="shipment_date" value="{{ old('shipment_date', date('Y-m-d')) }}" required class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-emerald-500">
                  @error('shipment_date') <p class="text-red-500 text-sm mt-1">{{ $message }}</p> @enderror
               </div>
            </div>

            <h3 class="text-lg font-bold text-gray-900 border-b pb-2">{{ __('Carrier & Vessel') }}</h3>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
               <div>
                  <label for="carrier_name" class="block text-sm font-medium text-gray-700 mb-2">{{ __('Carrier') }}</label>
                  <input type="text" id="carrier_name" name="carrier_name" value="{{ old('carrier_name') }}" placeholder="Maersk, MSC..." class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-emerald-500">
               </div>
               <div>
                  <label for="vessel_name" class="block text-sm font-medium text-gray-700 mb-2">{{ __('Vessel Name') }}</label>
                  <input type="text" id="vessel_name" name="vessel_name" value="{{ old('vessel_name') }}" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-emerald-500">
               </div>
               <div>
                  <label for="voyage_number" class="block text-sm font-medium text-gray-700 mb-2">{{ __('Voyage #') }}</label>
                  <input type="text" id="voyage_number" name="voyage_number" value="{{ old('voyage_number') }}" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-emerald-500">
               </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
               <div>
                  <label for="bill_of_lading" class="block text-sm font-medium text-gray-700 mb-2">{{ __('Bill of Lading #') }}</label>
                  <input type="text" id="bill_of_lading" name="bill_of_lading" value="{{ old('bill_of_lading') }}" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-emerald-500">
               </div>
               <div>
                  <label for="booking_number" class="block text-sm font-medium text-gray-700 mb-2">{{ __('Booking #') }}</label>
                  <input type="text" id="booking_number" name="booking_number" value="{{ old('booking_number') }}" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-emerald-500">
               </div>
            </div>

            <h3 class="text-lg font-bold text-gray-900 border-b pb-2">{{ __('Route & Terms') }}</h3>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
               <div>
                  <label for="port_of_loading" class="block text-sm font-medium text-gray-700 mb-2">{{ __('Port of Loading') }}</label>
                  <input type="text" id="port_of_loading" name="port_of_loading" value="{{ old('port_of_loading') }}" placeholder="Tanjung Priok" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-emerald-500">
               </div>
               <div>
                  <label for="port_of_discharge" class="block text-sm font-medium text-gray-700 mb-2">{{ __('Port of Discharge') }}</label>
                  <input type="text" id="port_of_discharge" name="port_of_discharge" value="{{ old('port_of_discharge') }}" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-emerald-500">
               </div>
               <div>
                  <label for="destination_country" class="block text-sm font-medium text-gray-700 mb-2">{{ __('Destination Country') }}</label>
                  <input type="text" id="destination_country" name="destination_country" value="{{ old('destination_country') }}" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-emerald-500">
               </div>
            </div>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
               <div>
                  <label for="incoterm" class="block text-sm font-medium text-gray-700 mb-2">{{ __('Incoterm') }}</label>
                  <select id="incoterm" name="incoterm" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-emerald-500">
                     <option value="">{{ __('Select') }}</option>
                     @foreach ($incoterms as $code => $label)
                        <option value="{{ $code }}" {{ old('incoterm') === $code ? 'selected' : '' }}>{{ $label }}</option>
                     @endforeach
                  </select>
               </div>
               <div>
                  <label for="estimated_arrival" class="block text-sm font-medium text-gray-700 mb-2">{{ __('Est. Arrival') }}</label>
                  <input type="date" id="estimated_arrival" name="estimated_arrival" value="{{ old('estimated_arrival') }}" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-emerald-500">
               </div>
               <div>
                  <label for="currency_code" class="block text-sm font-medium text-gray-700 mb-2">{{ __('Currency') }}</label>
                  <input type="text" id="currency_code" name="currency_code" value="{{ old('currency_code', 'USD') }}" maxlength="3" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-emerald-500">
               </div>
            </div>

            <h3 class="text-lg font-bold text-gray-900 border-b pb-2">{{ __('Costs') }}</h3>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
               <div>
                  <label for="freight_cost" class="block text-sm font-medium text-gray-700 mb-2">{{ __('Freight Cost') }}</label>
                  <input type="number" step="0.01" id="freight_cost" name="freight_cost" value="{{ old('freight_cost', 0) }}" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-emerald-500">
               </div>
               <div>
                  <label for="insurance_cost" class="block text-sm font-medium text-gray-700 mb-2">{{ __('Insurance Cost') }}</label>
                  <input type="number" step="0.01" id="insurance_cost" name="insurance_cost" value="{{ old('insurance_cost', 0) }}" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-emerald-500">
               </div>
            </div>

            <div>
               <label for="notes" class="block text-sm font-medium text-gray-700 mb-2">{{ __('Notes') }}</label>
               <textarea id="notes" name="notes" rows="3" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-emerald-500">{{ old('notes') }}</textarea>
            </div>

            <div class="flex justify-end gap-3 pt-4 border-t">
               <a href="{{ route('outbound-shipments.index') }}" class="px-6 py-2 bg-gray-200 hover:bg-gray-300 text-gray-700 font-semibold rounded-lg transition">{{ __('Cancel') }}</a>
               <button type="submit" class="px-6 py-2 bg-emerald-600 hover:bg-emerald-700 text-white font-semibold rounded-lg transition">{{ __('Create Shipment') }}</button>
            </div>
         </form>
      </div>
   </div>
@endsection
