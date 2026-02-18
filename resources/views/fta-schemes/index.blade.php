@extends('layouts.app')

@section('content')
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
   <div class="flex justify-between items-center mb-6">
      <div>
         <h1 class="text-3xl font-bold text-gray-900">{{ __('FTA Schemes') }}</h1>
         <p class="mt-1 text-sm text-gray-600">{{ __('Manage preferential tariff rates') }}</p>
      </div>
      <a href="{{ route('fta-schemes.create') }}" class="px-4 py-2 bg-emerald-600 hover:bg-emerald-700 text-white font-semibold rounded-lg transition">+ {{ __('Add Scheme') }}</a>
   </div>

   <div class="bg-white rounded-lg shadow-md overflow-hidden">
      @if($schemes->count() > 0)
      <table class="min-w-full divide-y divide-gray-200">
         <thead class="bg-gray-50">
            <tr>
               <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">{{ __('Scheme Name') }}</th>
               <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">{{ __('Description') }}</th>
               <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">{{ __('Member Countries') }}</th>
               <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase">{{ __('Rates') }}</th>
               <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">{{ __('Action') }}</th>
            </tr>
         </thead>
         <tbody class="divide-y divide-gray-200">
            @foreach($schemes as $scheme)
            <tr class="hover:bg-gray-50">
               <td class="px-6 py-4 text-sm font-bold text-gray-900">{{ $scheme->name }}</td>
               <td class="px-6 py-4 text-sm text-gray-600">{{ $scheme->description }}</td>
               <td class="px-6 py-4 text-sm text-gray-600">
                  @if($scheme->member_countries)
                     @foreach($scheme->member_countries as $country)
                        <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-gray-100 text-gray-800">{{ $country }}</span>
                     @endforeach
                  @else
                     -
                  @endif
               </td>
               <td class="px-6 py-4 text-center text-sm text-gray-600">{{ $scheme->rates_count }}</td>
               <td class="px-6 py-4 text-right">
                  <a href="{{ route('fta-schemes.show', $scheme) }}" class="text-blue-600 hover:text-blue-900 text-sm font-medium">{{ __('Manage Rates') }}</a>
               </td>
            </tr>
            @endforeach
         </tbody>
      </table>
      @else
         <div class="p-12 text-center text-gray-500">
            <h3 class="text-lg font-medium text-gray-900">{{ __('No FTA schemes found') }}</h3>
            <p>{{ __('Create a scheme to start managing preferential rates.') }}</p>
         </div>
      @endif
   </div>
</div>
@endsection
