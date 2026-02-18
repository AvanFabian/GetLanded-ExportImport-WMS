@extends('layouts.app')

@section('content')
<div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
   <div class="flex justify-between items-center mb-6">
      <div>
         <h1 class="text-3xl font-bold text-gray-900">{{ $ftaScheme->name }}</h1>
         <p class="mt-1 text-sm text-gray-600">{{ $ftaScheme->description }}</p>
      </div>
      <a href="{{ route('fta-schemes.index') }}" class="px-4 py-2 bg-gray-200 hover:bg-gray-300 text-gray-700 font-semibold rounded-lg transition">← {{ __('Back') }}</a>
   </div>

   <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
      <!-- Add Rate Form -->
      <div class="md:col-span-1">
         <div class="bg-white rounded-lg shadow-md p-6 sticky top-6">
            <h3 class="text-lg font-bold text-gray-900 mb-4">{{ __('Add Preferential Rate') }}</h3>
            <form method="POST" action="{{ route('fta-schemes.rates.store', $ftaScheme) }}" class="space-y-4">
               @csrf
               <div>
                  <label class="block text-sm font-medium text-gray-700 mb-2">{{ __('HS Code') }}</label>
                  <input type="text" name="hs_code" required placeholder="e.g. 0901.11.00" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-emerald-500">
               </div>
               <div>
                  <label class="block text-sm font-medium text-gray-700 mb-2">{{ __('Rate (%)') }}</label>
                  <input type="number" step="0.01" name="rate" required placeholder="0.00" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-emerald-500">
               </div>
               <button type="submit" class="w-full px-4 py-2 bg-emerald-600 hover:bg-emerald-700 text-white font-semibold rounded-lg transition">{{ __('Save Rate') }}</button>
            </form>
         </div>
      </div>

      <!-- Rates List -->
      <div class="md:col-span-2">
         <div class="bg-white rounded-lg shadow-md overflow-hidden">
            <div class="px-6 py-4 bg-gray-50 border-b">
               <h3 class="text-lg font-bold text-gray-900">{{ __('Defined Rates') }}</h3>
            </div>
            @if($ftaScheme->rates->count() > 0)
               <table class="min-w-full divide-y divide-gray-200">
                  <thead class="bg-gray-50">
                     <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">{{ __('HS Code') }}</th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">{{ __('Rate') }}</th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">{{ __('Action') }}</th>
                     </tr>
                  </thead>
                  <tbody class="divide-y divide-gray-200">
                     @foreach($ftaScheme->rates as $rate)
                     <tr class="hover:bg-gray-50">
                        <td class="px-6 py-3 text-sm font-mono text-gray-900">{{ $rate->hs_code }}</td>
                        <td class="px-6 py-3 text-sm text-right font-bold text-emerald-700">{{ $rate->rate * 1 }}%</td>
                        <td class="px-6 py-3 text-right">
                           <form method="POST" action="{{ route('fta-schemes.rates.destroy', [$ftaScheme, $rate]) }}" onsubmit="return confirm('Remove rate?')">
                              @csrf @method('DELETE')
                              <button type="submit" class="text-red-500 hover:text-red-700 text-sm">✕</button>
                           </form>
                        </td>
                     </tr>
                     @endforeach
                  </tbody>
               </table>
            @else
               <div class="p-8 text-center text-gray-500">
                  <p>{{ __('No rates defined yet.') }}</p>
               </div>
            @endif
         </div>
      </div>
   </div>
</div>
@endsection
