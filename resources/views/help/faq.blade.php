@extends('layouts.app')

@section('title', 'Frequently Asked Questions')

@section('content')
<div class="max-w-4xl mx-auto">
    <div class="text-center mb-10">
        <h1 class="text-3xl font-bold text-gray-900">Frequently Asked Questions</h1>
        <p class="text-gray-600 mt-2">Answers to the most common questions about using AgroWMS.</p>
    </div>

    <div class="space-y-4">
        
        <!-- FAQ Item 1 -->
        <div class="bg-white border border-gray-200 rounded-lg p-6 shadow-sm" x-data="{ open: false }">
            <button @click="open = !open" class="flex justify-between items-center w-full text-left">
                <span class="text-lg font-semibold text-gray-800">Is my data safe from other companies?</span>
                <span x-show="!open" class="text-gray-400">+</span>
                <span x-show="open" class="text-gray-400">-</span>
            </button>
            <div x-show="open" class="mt-4 text-gray-600 border-t pt-4" style="display: none;">
                <p><strong>Yes, absolutely.</strong> AgroWMS uses "Tenant Isolation" technology. Every database query automatically filters for your specific <code>Company ID</code>. It is physically impossible for another company to see your data unless they have your login credentials.</p>
            </div>
        </div>

        <!-- FAQ Item 2 -->
        <div class="bg-white border border-gray-200 rounded-lg p-6 shadow-sm" x-data="{ open: false }">
            <button @click="open = !open" class="flex justify-between items-center w-full text-left">
                <span class="text-lg font-semibold text-gray-800">Why did my Import fail?</span>
                <span x-show="!open" class="text-gray-400">+</span>
                <span x-show="open" class="text-gray-400">-</span>
            </button>
             <div x-show="open" class="mt-4 text-gray-600 border-t pt-4" style="display: none;">
                <p>Common reasons for failure:</p>
                <ul class="list-disc ml-5 mt-2">
                    <li><strong>Required Fields:</strong> Product Name is mandatory.</li>
                    <li><strong>Duplicate Codes:</strong> If a Product Code/SKU already exists, it might be skipped depending on settings.</li>
                    <li><strong>File Format:</strong> Ensure it is a valid CSV. Excel (.xlsx) files should be "Saved As CSV" first.</li>
                </ul>
                <p class="mt-2 text-blue-600"><a href="{{ route('help.article', 'smart-importer-guide') }}">Read the Import Guide &rarr;</a></p>
            </div>
        </div>

        <!-- FAQ Item 3 -->
        <div class="bg-white border border-gray-200 rounded-lg p-6 shadow-sm" x-data="{ open: false }">
            <button @click="open = !open" class="flex justify-between items-center w-full text-left">
                <span class="text-lg font-semibold text-gray-800">Who can see the "Purchase Price"?</span>
                <span x-show="!open" class="text-gray-400">+</span>
                <span x-show="open" class="text-gray-400">-</span>
            </button>
             <div x-show="open" class="mt-4 text-gray-600 border-t pt-4" style="display: none;">
                <p>Only users with the <strong>Admin</strong> or <strong>Manager</strong> role can see sensitive financial data like Purchase Price (HPP). Standard <strong>Staff</strong> users only see stock quantities and locations.</p>
            </div>
        </div>

    </div>
</div>
@endsection
