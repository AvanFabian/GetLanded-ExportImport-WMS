@extends('layouts.app')

@section('title', 'Knowledge Base')

@section('content')
<div class="max-w-6xl mx-auto">
    <div class="text-center mb-12">
        <h1 class="text-3xl font-bold text-gray-900 mb-4">Hello! How can we help you?</h1>
        <p class="text-lg text-gray-600">Search our articles or browse the categories below.</p>
    </div>

    <!-- Quick Links Grid -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-12">
        
        <!-- Smart Importer Guide -->
        <a href="{{ route('help.article', 'smart-importer-guide') }}" class="block p-6 bg-blue-50 hover:bg-blue-100 rounded-xl transition border border-blue-100 group">
            <div class="w-12 h-12 bg-blue-500 rounded-lg flex items-center justify-center mb-4 text-white group-hover:scale-110 transition">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"></path></svg>
            </div>
            <h3 class="text-xl font-bold text-blue-900 mb-2">Smart Importer</h3>
            <p class="text-blue-700">Learn how to upload messy Excel files, map columns automatically, and fix data errors.</p>
        </a>

        <!-- FAQ -->
        <a href="{{ route('help.faq') }}" class="block p-6 bg-white hover:bg-gray-50 rounded-xl transition border border-gray-200 shadow-sm group">
            <div class="w-12 h-12 bg-gray-100 rounded-lg flex items-center justify-center mb-4 text-gray-600 group-hover:bg-gray-200 transition">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8.228 9c.549-1.165 2.03-2 3.772-2 2.21 0 4 1.343 4 3 0 1.4-1.278 2.575-3.006 2.907-.542.104-.994.54-.994 1.093m0 3h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
            </div>
            <h3 class="text-xl font-bold text-gray-900 mb-2">FAQ</h3>
            <p class="text-gray-600">Common questions about billing, multi-tenancy rules, and data safety.</p>
        </a>

        <!-- System Setup -->
        <div class="block p-6 bg-white rounded-xl border border-gray-200 shadow-sm opacity-60 cursor-not-allowed">
            <div class="w-12 h-12 bg-gray-100 rounded-lg flex items-center justify-center mb-4 text-gray-400">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path></svg>
            </div>
            <h3 class="text-xl font-bold text-gray-400 mb-2">System Setup</h3>
            <p class="text-gray-400">Coming soon. Advanced configuration guides.</p>
        </div>
    </div>

    <!-- Contact Support Footer -->
    <div class="bg-gray-900 rounded-xl p-8 text-center text-white">
        <h2 class="text-2xl font-bold mb-2">Still need help?</h2>
        <p class="text-gray-400 mb-6">Our support team is available Mon-Fri, 9am - 5pm.</p>
        <a href="mailto:support@avandigital.id" class="inline-block bg-blue-600 hover:bg-blue-700 text-white font-bold py-3 px-8 rounded-lg transition duration-300">Contact Support</a>
    </div>
</div>
@endsection
