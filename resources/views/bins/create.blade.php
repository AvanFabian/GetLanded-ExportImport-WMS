@extends('layouts.app')

@section('title', __('app.add_bin'))

@section('content')
   <div class="max-w-3xl mx-auto">
      <div class="flex items-center justify-between mb-4">
         <h2 class="text-xl font-semibold">{{ __('app.add_bin') }}</h2>
         <a href="{{ route('bins.index') }}" class="text-gray-600 hover:text-gray-900">
            &larr; {{ __('app.back_to_list') }}
         </a>
      </div>

      <div class="bg-white rounded shadow p-6">
         <form action="{{ route('bins.store') }}" method="POST">
            @csrf
            
            <div class="mb-4">
               <label for="rack_id" class="block text-sm font-medium text-gray-700 mb-1">
                  {{ __('app.rack') }} <span class="text-red-500">*</span>
               </label>
               <select name="rack_id" id="rack_id" required
                  class="w-full border rounded px-3 py-2 @error('rack_id') border-red-500 @enderror">
                  <option value="">{{ __('app.select_rack') }}</option>
                  @foreach ($racks as $rack)
                     <option value="{{ $rack->id }}" {{ old('rack_id', $selectedRackId) == $rack->id ? 'selected' : '' }}>
                        {{ $rack->zone->warehouse->name ?? '' }} / {{ $rack->zone->name ?? '' }} / {{ $rack->code }}
                     </option>
                  @endforeach
               </select>
               @error('rack_id')
                  <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
               @enderror
            </div>

            <div class="grid grid-cols-2 gap-4 mb-4">
               <div>
                  <label for="code" class="block text-sm font-medium text-gray-700 mb-1">
                     {{ __('app.code') }} <span class="text-red-500">*</span>
                  </label>
                  <input type="text" name="code" id="code" value="{{ old('code') }}" required maxlength="30"
                     class="w-full border rounded px-3 py-2 @error('code') border-red-500 @enderror"
                     placeholder="e.g., BIN-001">
                  @error('code')
                     <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                  @enderror
               </div>

               <div>
                  <label for="barcode" class="block text-sm font-medium text-gray-700 mb-1">
                     {{ __('app.barcode') }}
                  </label>
                  <input type="text" name="barcode" id="barcode" value="{{ old('barcode') }}" maxlength="50"
                     class="w-full border rounded px-3 py-2 @error('barcode') border-red-500 @enderror"
                     placeholder="{{ __('app.auto_generated') }}">
                  @error('barcode')
                     <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                  @enderror
               </div>
            </div>

            <div class="grid grid-cols-3 gap-4 mb-4">
               <div>
                  <label for="level" class="block text-sm font-medium text-gray-700 mb-1">
                     {{ __('app.level') }}
                  </label>
                  <input type="number" name="level" id="level" value="{{ old('level', 1) }}" min="1" max="50"
                     class="w-full border rounded px-3 py-2 @error('level') border-red-500 @enderror">
                  @error('level')
                     <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                  @enderror
               </div>

               <div>
                  <label for="max_capacity" class="block text-sm font-medium text-gray-700 mb-1">
                     {{ __('app.max_capacity') }}
                  </label>
                  <input type="number" name="max_capacity" id="max_capacity" value="{{ old('max_capacity') }}" min="1"
                     class="w-full border rounded px-3 py-2 @error('max_capacity') border-red-500 @enderror"
                     placeholder="{{ __('app.unlimited') }}">
                  @error('max_capacity')
                     <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                  @enderror
               </div>

               <div>
                  <label for="pick_priority" class="block text-sm font-medium text-gray-700 mb-1">
                     {{ __('app.pick_priority') }}
                  </label>
                  <select name="pick_priority" id="pick_priority"
                     class="w-full border rounded px-3 py-2 @error('pick_priority') border-red-500 @enderror">
                     @foreach ($priorities as $priority)
                        <option value="{{ $priority }}" {{ old('pick_priority', 'medium') == $priority ? 'selected' : '' }}>
                           {{ __('app.' . $priority) }}
                        </option>
                     @endforeach
                  </select>
                  @error('pick_priority')
                     <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                  @enderror
               </div>
            </div>

            <div class="mb-6">
               <label class="inline-flex items-center">
                  <input type="checkbox" name="is_active" value="1" {{ old('is_active', true) ? 'checked' : '' }}
                     class="rounded border-gray-300 text-primary focus:ring-primary">
                  <span class="ml-2">{{ __('app.active') }}</span>
               </label>
            </div>

            <div class="flex justify-end gap-2">
               <a href="{{ route('bins.index') }}" class="px-4 py-2 border rounded text-gray-700 hover:bg-gray-50">
                  {{ __('app.cancel') }}
               </a>
               <button type="submit" class="px-4 py-2 bg-primary text-white rounded hover:bg-primary/90">
                  {{ __('app.save') }}
               </button>
            </div>
         </form>
      </div>
   </div>
@endsection
