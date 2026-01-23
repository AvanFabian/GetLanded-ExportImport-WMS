@extends('layouts.app')

@section('title', __('app.system_settings') ?? 'System Settings')

@section('content')
   <div class="max-w-4xl mx-auto">
      <div class="flex items-center justify-between mb-6">
         <h2 class="text-2xl font-bold">{{ __('app.system_settings') ?? 'System Settings' }}</h2>
      </div>

      <form method="POST" action="{{ route('settings.update') }}" class="space-y-6">
         @csrf
         @method('PUT')

         <!-- Company Information -->
         <div class="bg-white rounded-lg shadow p-6">
            <div class="flex items-start justify-between mb-4">
               <div>
                  <h3 class="text-lg font-semibold text-gray-900">{{ __('app.company_information') }}</h3>
                  <p class="text-sm text-gray-500 mt-1">{{ __('app.company_info_desc') }}</p>
               </div>
               <div class="flex items-center text-primary">
                  <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                     <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z">
                     </path>
                  </svg>
               </div>
            </div>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
               <div class="md:col-span-2">
                  <label class="block text-sm font-medium text-gray-700 mb-2">{{ __('app.company_name') }} *</label>
                  <input type="text" name="company_name"
                     value="{{ old('company_name', $settings['company_name'] ?? '') }}"
                     class="w-full border-gray-300 rounded-lg" required>
                  @error('company_name')
                     <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                  @enderror
               </div>

               <div class="md:col-span-2">
                  <label class="block text-sm font-medium text-gray-700 mb-2">{{ __('app.company_address') }}</label>
                  <textarea name="company_address" rows="3" class="w-full border-gray-300 rounded-lg">{{ old('company_address', $settings['company_address'] ?? '') }}</textarea>
                  @error('company_address')
                     <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                  @enderror
               </div>

               <div>
                  <label class="block text-sm font-medium text-gray-700 mb-2">{{ __('app.company_phone') }}</label>
                  <input type="text" name="company_phone"
                     value="{{ old('company_phone', $settings['company_phone'] ?? '') }}"
                     class="w-full border-gray-300 rounded-lg">
                  @error('company_phone')
                     <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                  @enderror
               </div>

               <div>
                  <label class="block text-sm font-medium text-gray-700 mb-2">{{ __('app.company_email') }}</label>
                  <input type="email" name="company_email"
                     value="{{ old('company_email', $settings['company_email'] ?? '') }}"
                     class="w-full border-gray-300 rounded-lg">
                  @error('company_email')
                     <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                  @enderror
               </div>
            </div>
         </div>

         <!-- System Preferences -->
         <div class="bg-white rounded-lg shadow p-6">
            <h3 class="text-lg font-semibold mb-4 text-gray-900">{{ __('app.system_preferences') }}</h3>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
               <div>
                  <label class="block text-sm font-medium text-gray-700 mb-2">{{ __('app.currency') ?? 'Currency' }} *</label>
                  <select name="currency" class="w-full border-gray-300 rounded-lg" required>
                     <option value="IDR"
                        {{ old('currency', $settings['currency'] ?? 'IDR') == 'IDR' ? 'selected' : '' }}>
                        IDR (Indonesian Rupiah)</option>
                     <option value="USD" {{ old('currency', $settings['currency'] ?? '') == 'USD' ? 'selected' : '' }}>
                        USD
                        (US Dollar)</option>
                     <option value="EUR" {{ old('currency', $settings['currency'] ?? '') == 'EUR' ? 'selected' : '' }}>
                        EUR
                        (Euro)</option>
                  </select>
                  @error('currency')
                     <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                  @enderror
               </div>

               <div>
                  <label class="block text-sm font-medium text-gray-700 mb-2">{{ __('app.items_per_page') }} *</label>
                  <input type="number" name="items_per_page"
                     value="{{ old('items_per_page', $settings['items_per_page'] ?? 20) }}" min="5" max="100"
                     class="w-full border-gray-300 rounded-lg" required>
                  @error('items_per_page')
                     <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                  @enderror
               </div>

               <div class="md:col-span-2">
                  <label class="flex items-center">
                     <input type="checkbox" name="low_stock_alert" value="1"
                        {{ old('low_stock_alert', $settings['low_stock_alert'] ?? 1) ? 'checked' : '' }}
                        class="rounded border-gray-300 text-primary shadow-sm focus:ring-primary">
                     <span class="ml-2 text-sm text-gray-700">{{ __('app.enable_low_stock_alert') }}</span>
                  </label>
                  <p class="ml-6 text-xs text-gray-500">{{ __('app.enable_low_stock_alert_desc') }}</p>
                  @error('low_stock_alert')
                     <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                  @enderror
               </div>
            </div>
         </div>

         <!-- Action Buttons -->
         <div class="flex justify-end gap-3">
            <a href="{{ route('dashboard') }}"
               class="px-4 py-2 bg-gray-200 text-gray-700 rounded-lg hover:bg-gray-300 transition">
               {{ __('app.cancel') }}
            </a>
            <button type="submit" class="px-4 py-2 bg-primary text-white rounded-lg hover:bg-primary/90 transition">
               {{ __('app.save_settings') }}
            </button>
         </div>
      </form>
   </div>
@endsection
