@extends('layouts.app')

@section('content')
   <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
      <div class="flex justify-between items-center mb-6">
         <div>
            <h1 class="text-3xl font-bold text-gray-900">{{ __('Create Claim') }}</h1>
            <p class="mt-1 text-sm text-gray-600">{{ __('File a damage, shortage, or delay claim') }}</p>
         </div>
         <a href="{{ route('claims.index') }}" class="px-4 py-2 bg-gray-200 hover:bg-gray-300 text-gray-700 font-semibold rounded-lg transition">← {{ __('Back') }}</a>
      </div>

      <div class="bg-white rounded-lg shadow-md p-6">
         <form method="POST" action="{{ route('claims.store') }}" class="space-y-6">
            @csrf
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
               <div>
                  <label for="sales_order_id" class="block text-sm font-medium text-gray-700 mb-2">{{ __('Sales Order') }} *</label>
                  <select id="sales_order_id" name="sales_order_id" required class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-emerald-500 focus:border-transparent">
                     <option value="">{{ __('Select Sales Order') }}</option>
                     @foreach ($salesOrders as $order)
                        <option value="{{ $order->id }}" {{ old('sales_order_id') == $order->id ? 'selected' : '' }}>
                           {{ $order->so_number }} — {{ $order->customer->name ?? '' }}
                        </option>
                     @endforeach
                  </select>
                  @error('sales_order_id') <p class="text-red-500 text-sm mt-1">{{ $message }}</p> @enderror
               </div>
               <div>
                  <label for="claim_type" class="block text-sm font-medium text-gray-700 mb-2">{{ __('Claim Type') }} *</label>
                  <select id="claim_type" name="claim_type" required class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-emerald-500 focus:border-transparent">
                     <option value="damage" {{ old('claim_type') === 'damage' ? 'selected' : '' }}>{{ __('Damage') }}</option>
                     <option value="shortage" {{ old('claim_type') === 'shortage' ? 'selected' : '' }}>{{ __('Shortage') }}</option>
                     <option value="delay" {{ old('claim_type') === 'delay' ? 'selected' : '' }}>{{ __('Delay') }}</option>
                  </select>
                  @error('claim_type') <p class="text-red-500 text-sm mt-1">{{ $message }}</p> @enderror
               </div>
            </div>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
               <div>
                  <label for="claimed_amount" class="block text-sm font-medium text-gray-700 mb-2">{{ __('Claimed Amount (IDR)') }} *</label>
                  <input type="number" step="0.01" id="claimed_amount" name="claimed_amount" value="{{ old('claimed_amount') }}" required
                     class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-emerald-500 focus:border-transparent">
                  @error('claimed_amount') <p class="text-red-500 text-sm mt-1">{{ $message }}</p> @enderror
               </div>
               <div>
                  <label for="insurance_policy_number" class="block text-sm font-medium text-gray-700 mb-2">{{ __('Insurance Policy #') }}</label>
                  <input type="text" id="insurance_policy_number" name="insurance_policy_number" value="{{ old('insurance_policy_number') }}"
                     class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-emerald-500 focus:border-transparent">
                  @error('insurance_policy_number') <p class="text-red-500 text-sm mt-1">{{ $message }}</p> @enderror
               </div>
            </div>
            <div>
               <label for="description" class="block text-sm font-medium text-gray-700 mb-2">{{ __('Description') }} *</label>
               <textarea id="description" name="description" rows="4" required
                  class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-emerald-500 focus:border-transparent">{{ old('description') }}</textarea>
               @error('description') <p class="text-red-500 text-sm mt-1">{{ $message }}</p> @enderror
            </div>
            <div class="flex justify-end gap-3 pt-4 border-t">
               <a href="{{ route('claims.index') }}" class="px-6 py-2 bg-gray-200 hover:bg-gray-300 text-gray-700 font-semibold rounded-lg transition">{{ __('Cancel') }}</a>
               <button type="submit" class="px-6 py-2 bg-emerald-600 hover:bg-emerald-700 text-white font-semibold rounded-lg transition">{{ __('Create Claim') }}</button>
            </div>
         </form>
      </div>
   </div>
@endsection
