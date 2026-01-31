@extends('layouts.app')

@section('title', 'Smart Importer Guide')

@section('content')
<div class="max-w-4xl mx-auto">
    
    <!-- Breadcrumb -->
    <nav class="flex mb-8" aria-label="Breadcrumb">
        <ol class="inline-flex items-center space-x-1 md:space-x-3">
            <li class="inline-flex items-center">
                <a href="{{ route('help.index') }}" class="text-gray-700 hover:text-blue-600">Knowledge Base</a>
            </li>
            <li>
                <div class="flex items-center">
                    <svg class="w-6 h-6 text-gray-400" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd"></path></svg>
                    <span class="ml-1 text-gray-500 md:ml-2">Smart Importer</span>
                </div>
            </li>
        </ol>
    </nav>

    <article class="prose prose-blue max-w-none bg-white p-8 rounded-lg shadow-sm">
        <h1 class="text-4xl font-bold text-gray-900 mb-6">Using the Smart Importer</h1>
        
        <p class="lead text-xl text-gray-600 mb-8">
            The Smart Importer allows you to bring messy data from Excel/CSV directly into AgroWMS without manual cleanup. It automatically fixes currency formats, weight units, and typos.
        </p>

        <div class="bg-blue-50 border-l-4 border-blue-500 p-4 mb-8">
            <p class="text-blue-700 font-bold">🚀 Key Capability</p>
            <p class="text-blue-600">You do not need to format your CSV perfectly. The system can read "Rp. 18.000", "10 lbs", and "Pieces" and fix them for you.</p>
        </div>

        <h3>1. Preparing Your File</h3>
        <p>You can upload any <code>.csv</code> or <code>.txt</code> file. Ensure your file has a header row.</p>
        <ul>
            <li><strong>Supported Columns:</strong> Name, Code/SKU, Category, Purchase Price, Selling Price, Unit, Weight.</li>
            <li><strong>Max File Size:</strong> 10MB (approx 50,000 rows).</li>
        </ul>

        <h3>2. Mapping Columns</h3>
        <p>After uploading, you will see the <strong>Smart Mapping</strong> screen. The system uses AI-like logic to guess which column is which.</p>
        
        <table class="min-w-full mt-4 mb-8 border">
            <thead class="bg-gray-50">
                <tr>
                    <th class="p-2 border">Your CSV Header</th>
                    <th class="p-2 border">System Field</th>
                    <th class="p-2 border">Auto-Match?</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td class="p-2 border">Product Name</td>
                    <td class="p-2 border">Name</td>
                    <td class="p-2 border text-green-600 font-bold">Yes</td>
                </tr>
                <tr>
                    <td class="p-2 border">Cost Price (IDR)</td>
                    <td class="p-2 border">Purchase Price</td>
                    <td class="p-2 border text-green-600 font-bold">Yes</td>
                </tr>
                <tr>
                    <td class="p-2 border">Net Wt.</td>
                    <td class="p-2 border">Min Stock (Weight)</td>
                    <td class="p-2 border text-green-600 font-bold">Yes</td>
                </tr>
            </tbody>
        </table>

        <h3>3. Automatic Data Cleaning</h3>
        <p>The importer fixes common data issues automatically:</p>
        
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-8">
            <div class="bg-gray-50 p-4 rounded-lg">
                <h4 class="font-bold text-gray-800">Currency Fixing</h4>
                <p class="text-gray-600 text-sm">Input: <code class="bg-gray-200 px-1">Rp. 15.000,00</code></p>
                <p class="text-green-600 font-bold">Result: 15000.00</p>
            </div>
            <div class="bg-gray-50 p-4 rounded-lg">
                <h4 class="font-bold text-gray-800">Weight Conversion</h4>
                <p class="text-gray-600 text-sm">Input: <code class="bg-gray-200 px-1">10 lbs</code></p>
                <p class="text-green-600 font-bold">Result: 4.53 kg</p>
            </div>
             <div class="bg-gray-50 p-4 rounded-lg">
                <h4 class="font-bold text-gray-800">Unit Normalization</h4>
                <p class="text-gray-600 text-sm">Input: <code class="bg-gray-200 px-1">Pieces</code> or <code class="bg-gray-200 px-1">Buah</code></p>
                <p class="text-green-600 font-bold">Result: pcs</p>
            </div>
        </div>

        <h3>4. Troubleshooting</h3>
        <details class="bg-gray-50 p-4 rounded-lg mb-2 cursor-pointer">
            <summary class="font-bold text-gray-800">My Categories created duplicates?</summary>
            <p class="mt-2 text-gray-600">The system creates a new category if it doesn't match an existing one exactly. "Electronics" and "elektronik" will be two different categories.</p>
        </details>

        <details class="bg-gray-50 p-4 rounded-lg mb-2 cursor-pointer">
            <summary class="font-bold text-gray-800">Import stuck at "Processing"?</summary>
            <p class="mt-2 text-gray-600">Large files are processed in chunks of 100 to prevent server crashes. Please be patient; a 10,000 row file may take 2-3 minutes.</p>
        </details>

    </article>
</div>
@endsection
