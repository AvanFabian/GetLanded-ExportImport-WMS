@extends('layouts.app')

@section('title', __('app.add_zone'))

@section('content')
   <div class="max-w-3xl mx-auto">
      <div class="flex items-center justify-between mb-4">
         <h2 class="text-xl font-semibold">{{ __('app.add_zone') }}</h2>
         <a href="{{ route('zones.index') }}" class="text-gray-600 hover:text-gray-900">
            &larr; {{ __('app.back_to_list') }}
         </a>
      </div>

      <div class="bg-white rounded shadow p-6">
         <form action="{{ route('zones.store') }}" method="POST">
            @csrf
            
            <div class="mb-4">
               <label for="warehouse_id" class="block text-sm font-medium text-gray-700 mb-1">
                  {{ __('app.warehouse') }} <span class="text-red-500">*</span>
               </label>
               <select name="warehouse_id" id="warehouse_id" required
                  class="w-full border rounded px-3 py-2 @error('warehouse_id') border-red-500 @enderror">
                  <option value="">{{ __('app.select_warehouse') }}</option>
                  @foreach ($warehouses as $warehouse)
                     <option value="{{ $warehouse->id }}" {{ old('warehouse_id') == $warehouse->id ? 'selected' : '' }}>
                        {{ $warehouse->name }} ({{ $warehouse->code }})
                     </option>
                  @endforeach
               </select>
               @error('warehouse_id')
                  <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
               @enderror
            </div>

            <div class="grid grid-cols-2 gap-4 mb-4">
               <div>
                  <label for="code" class="block text-sm font-medium text-gray-700 mb-1">
                     {{ __('app.code') }} <span class="text-red-500">*</span>
                  </label>
                  <input type="text" name="code" id="code" value="{{ old('code') }}" required maxlength="20"
                     class="w-full border rounded px-3 py-2 @error('code') border-red-500 @enderror"
                     placeholder="e.g., ZONE-A">
                  @error('code')
                     <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                  @enderror
               </div>

               <div>
                  <label for="name" class="block text-sm font-medium text-gray-700 mb-1">
                     {{ __('app.name') }} <span class="text-red-500">*</span>
                  </label>
                  <input type="text" name="name" id="name" value="{{ old('name') }}" required maxlength="100"
                     class="w-full border rounded px-3 py-2 @error('name') border-red-500 @enderror"
                     placeholder="e.g., Storage Zone A">
                  @error('name')
                     <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                  @enderror
               </div>
            </div>

            <div class="mb-4">
               <label for="type" class="block text-sm font-medium text-gray-700 mb-1">
                  {{ __('app.type') }} <span class="text-red-500">*</span>
               </label>
               <select name="type" id="type" required
                  class="w-full border rounded px-3 py-2 @error('type') border-red-500 @enderror">
                  @foreach ($types as $type)
                     <option value="{{ $type }}" {{ old('type') == $type ? 'selected' : '' }}>
                        {{ __('app.' . $type) }}
                     </option>
                  @endforeach
               </select>
               @error('type')
                  <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
               @enderror
            </div>

            <div class="mb-4">
               <label for="description" class="block text-sm font-medium text-gray-700 mb-1">
                  {{ __('app.description') }}
               </label>
               <textarea name="description" id="description" rows="3" maxlength="500"
                  class="w-full border rounded px-3 py-2 @error('description') border-red-500 @enderror"
                  placeholder="{{ __('app.optional_description') }}">{{ old('description') }}</textarea>
               @error('description')
                  <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
               @enderror
            </div>

            <div class="mb-6">
               <label class="inline-flex items-center">
                  <input type="checkbox" name="is_active" value="1" {{ old('is_active', true) ? 'checked' : '' }}
                     class="rounded border-gray-300 text-primary focus:ring-primary">
                  <span class="ml-2">{{ __('app.active') }}</span>
               </label>
            </div>

            <div class="flex justify-end gap-2">
               <a href="{{ route('zones.index') }}" class="px-4 py-2 border rounded text-gray-700 hover:bg-gray-50">
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
