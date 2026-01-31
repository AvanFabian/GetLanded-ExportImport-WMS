@extends('layouts.app')

@section('title', 'Import Data')

@section('content')
<div class="max-w-4xl mx-auto">
    <div class="mb-6 flex items-center justify-between">
        <h2 class="text-2xl font-bold text-gray-800">Import Data</h2>
        <a href="{{ route('imports.index') }}" class="text-blue-600 hover:text-blue-800">Back to History</a>
    </div>

    <div class="bg-white rounded-lg shadow-md p-6">
        <form action="{{ route('imports.upload') }}" method="POST" enctype="multipart/form-data">
            @csrf

            <div class="mb-6">
                <label class="block text-gray-700 text-sm font-bold mb-2">Import Type</label>
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    @foreach($importTypes as $key => $label)
                    <label class="border rounded-lg p-4 cursor-pointer hover:bg-gray-50 flex items-center gap-3">
                        <input type="radio" name="type" value="{{ $key }}" class="h-4 w-4 text-blue-600" {{ $key === 'products' ? 'checked' : '' }}>
                        <span class="font-medium">{{ $label }}</span>
                    </label>
                    @endforeach
                </div>
                @error('type') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
            </div>

            <div class="mb-6">
                <label class="block text-gray-700 text-sm font-bold mb-2">Upload File (CSV/Excel)</label>
                <div class="border-2 border-dashed border-gray-300 rounded-lg p-8 text-center hover:border-blue-500 transition-colors">
                    <input type="file" name="file" class="w-full" accept=".csv,.txt,.xlsx">
                    <p class="text-xs text-gray-500 mt-2">Max size: 10MB. Formats: CSV, TXT</p>
                </div>
                @error('file') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
            </div>

            <div class="flex justify-end gap-3">
                <button type="submit" class="px-6 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 shadow font-medium">
                    Next: Map Columns &rarr;
                </button>
            </div>
        </form>
    </div>
</div>
@endsection
