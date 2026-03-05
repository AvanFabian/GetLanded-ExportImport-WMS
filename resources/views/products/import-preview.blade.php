@extends('layouts.app')

@section('title', 'Review Smart Import Mapping')

@section('content')
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8" x-data="{ isSubmitting: false }">
    <div class="bg-white rounded-lg shadow-sm border border-gray-200 overflow-hidden">
        
        {{-- Header Section --}}
        <div class="p-6 border-b border-gray-200 bg-gradient-to-r from-blue-50 to-indigo-50 flex items-center justify-between">
            <div>
                <h2 class="text-xl font-bold text-gray-800 flex items-center gap-2">
                    <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19.428 15.428a2 2 0 00-1.022-.547l-2.387-.477a6 6 0 00-3.86.517l-.318.158a6 6 0 01-3.86.517L6.05 15.21a2 2 0 00-1.806.547M8 4h8l-1 1v5.172a2 2 0 00.586 1.414l5 5c1.26 1.26.367 3.414-1.415 3.414H4.828c-1.782 0-2.674-2.154-1.414-3.414l5-5A2 2 0 009 10.172V5L8 4z" />
                    </svg>
                    Smart Import Analysis Complete
                </h2>
                <p class="text-sm text-gray-600 mt-1">
                    Our AI has analyzed your file and mapped the columns. Please review and confirm below.
                </p>
            </div>
            <div class="text-right">
                <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-blue-100 text-blue-800">
                    {{ number_format($stats['total_rows']) }} Rows Found
                </span>
            </div>
        </div>

        {{-- Form Section --}}
        <form action="{{ route('products.import') }}" method="POST" id="confirmImportForm" @submit="isSubmitting = true">
            @csrf
            {{-- We pass the temp file path so the actual import job knows what to process --}}
            <input type="hidden" name="analyzed_file_path" value="{{ $path }}">
            <input type="hidden" name="is_smart_import" value="1">
            
            <div class="p-6">
                <div class="overflow-x-auto border border-gray-200 rounded-lg">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-bold text-gray-500 uppercase tracking-wider w-1/4">
                                    Your File Column
                                </th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-bold text-blue-600 uppercase tracking-wider w-1/4">
                                    Mapped To (Database)
                                </th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-bold text-gray-500 uppercase tracking-wider w-1/2">
                                    Sample Data (Row 1)
                                </th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            @php
                                $dbFields = [
                                    '' => '-- Ignore this column --',
                                    'name' => 'Product Name',
                                    'sku' => 'SKU / Code',
                                    'category_name' => 'Category Name',
                                    'unit' => 'Unit of Measure',
                                    'purchase_price' => 'Purchase Price',
                                    'selling_price' => 'Selling Price',
                                    'net_weight' => 'Net Weight (kg)',
                                    'gross_weight' => 'Gross Weight (kg)',
                                    'hs_code' => 'HS Code',
                                    'origin_country' => 'Origin Country'
                                ];
                                
                                $sampleRow = $stats['sample'][0] ?? [];
                            @endphp

                            @foreach($stats['headers'] as $index => $header)
                                @php
                                    $aiSuggestion = $mapping[$header] ?? '';
                                    $sampleValue = $sampleRow[$index] ?? '-';
                                    $isMapped = !empty($aiSuggestion);
                                @endphp
                                <tr class="{{ $isMapped ? 'bg-green-50/30' : '' }} hover:bg-gray-50">
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                        {{ $header ?: '(Empty Header)' }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm">
                                        <select name="mapping[{{ $index }}]" class="mt-1 block w-full pl-3 pr-10 py-2 text-base border-gray-300 focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm rounded-md shadow-sm {{ $isMapped ? 'border-green-400 bg-green-50' : '' }}">
                                            @foreach($dbFields as $key => $label)
                                                <option value="{{ $key }}" {{ $aiSuggestion === $key ? 'selected' : '' }}>
                                                    {{ $label }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 truncate max-w-xs" title="{{ $sampleValue }}">
                                        {{ Str::limit($sampleValue, 50) }}
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <div class="mt-8 flex items-center justify-end gap-3 border-t border-gray-200 pt-5">
                    <a href="{{ route('products.index') }}" class="px-4 py-2 border border-gray-300 shadow-sm text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                        Cancel
                    </a>
                    <button type="submit" :disabled="isSubmitting" class="inline-flex items-center px-6 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 disabled:opacity-50">
                        <svg x-show="isSubmitting" class="animate-spin -ml-1 mr-3 h-5 w-5 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                        </svg>
                        <span x-show="!isSubmitting">Confirm & Start Import</span>
                        <span x-show="isSubmitting" style="display: none;">Processing...</span>
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>
@endsection
