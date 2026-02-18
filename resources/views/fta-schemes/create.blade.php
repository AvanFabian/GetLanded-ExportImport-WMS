@extends('layouts.app')

@section('content')
<div class="max-w-2xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
   <div class="mb-6">
      <h1 class="text-3xl font-bold text-gray-900">{{ __('New FTA Scheme') }}</h1>
   </div>

   <div class="bg-white rounded-lg shadow-md p-6">
      <form method="POST" action="{{ route('fta-schemes.store') }}" class="space-y-6">
         @csrf
         <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">{{ __('Scheme Name') }} *</label>
            <input type="text" name="name" required placeholder="e.g. ACFTA" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-emerald-500">
         </div>
         <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">{{ __('Description') }}</label>
            <input type="text" name="description" placeholder="e.g. ASEAN-China Free Trade Area" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-emerald-500">
         </div>
         <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">{{ __('Member Countries (comma separated)') }}</label>
            <input type="text" name="member_countries" placeholder="CN, ID, MY, SG, TH, VN..." class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-emerald-500">
         </div>
         <div class="flex justify-end gap-3 pt-4">
            <a href="{{ route('fta-schemes.index') }}" class="px-6 py-2 bg-gray-200 hover:bg-gray-300 text-gray-700 font-semibold rounded-lg transition">{{ __('Cancel') }}</a>
            <button type="submit" class="px-6 py-2 bg-emerald-600 hover:bg-emerald-700 text-white font-semibold rounded-lg transition">{{ __('Create Scheme') }}</button>
         </div>
      </form>
   </div>
</div>
@endsection
