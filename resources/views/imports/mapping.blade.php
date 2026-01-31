@extends('layouts.app')

@section('title', 'Map Columns')

@section('content')
<div class="max-w-6xl mx-auto">
    <div class="mb-6">
        <h2 class="text-2xl font-bold text-gray-800">Map Columns</h2>
        <p class="text-gray-600">Match your file columns to the system fields.</p>
    </div>

    <form action="{{ route('imports.confirm', $job) }}" method="POST">
        @csrf
        
        <div class="bg-white rounded-lg shadow-md overflow-hidden">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-bold text-gray-500 uppercase tracking-wider w-1/3">System Field</th>
                        <th class="px-6 py-3 text-left text-xs font-bold text-gray-500 uppercase tracking-wider w-1/3">Your File Column</th>
                        <th class="px-6 py-3 text-left text-xs font-bold text-gray-500 uppercase tracking-wider w-1/3">Sample Data (Row 1)</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @foreach($fields as $field)
                    @php
                        $suggestedHeader = $suggestions[$field] ?? null;
                        
                        // Find sample data for the suggested header
                        $sampleValue = '-';
                        if ($suggestedHeader) {
                            $headerIndex = array_search($suggestedHeader, $headers);
                            if ($headerIndex !== false) {
                                $sampleValue = $sample[0][$headerIndex] ?? '-';
                            }
                        }
                    @endphp
                    <tr class="hover:bg-gray-50">
                        <td class="px-6 py-4">
                            <label for="field_{{ $field }}" class="block text-sm font-medium text-gray-900">
                                {{ ucwords(str_replace('_', ' ', $field)) }}
                                @if(in_array($field, ['name', 'sku', 'code', 'email']))
                                    <span class="text-red-500">*</span>
                                @endif
                            </label>
                            <p class="text-xs text-gray-500">{{ $field }}</p>
                        </td>
                        <td class="px-6 py-4">
                            <select name="mapping[{{ $field }}]" id="field_{{ $field }}" 
                                class="block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm {{ $suggestedHeader ? 'bg-green-50 border-green-300' : '' }}">
                                <option value="">-- Do Not Import --</option>
                                @foreach($headers as $header)
                                <option value="{{ $header }}" {{ $suggestedHeader === $header ? 'selected' : '' }}>
                                    {{ $header }}
                                </option>
                                @endforeach
                            </select>
                        </td>
                        <td class="px-6 py-4 text-sm text-gray-500 font-mono text-xs">
                            {{ Str::limit($sampleValue, 50) }}
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
            
            <div class="px-6 py-4 bg-gray-50 border-t flex justify-between items-center">
                <span class="text-sm text-gray-500">
                    <span class="font-bold text-green-600">Smart Match:</span> Columns highlighted in green were auto-detected.
                </span>
                <button type="submit" class="px-6 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 shadow font-medium">
                    Start Import Job &rarr;
                </button>
            </div>
        </div>
    </form>
</div>
@endsection
