@extends('layouts.guest')

@section('title', 'Account Suspended')

@section('content')
<div class="min-h-screen flex items-center justify-center bg-gradient-to-br from-red-50 to-orange-50 p-4">
    <div class="max-w-md w-full bg-white rounded-2xl shadow-xl p-8 text-center">
        {{-- Icon --}}
        <div class="w-20 h-20 mx-auto bg-red-100 rounded-full flex items-center justify-center mb-6">
            <svg class="w-10 h-10 text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
            </svg>
        </div>

        <h1 class="text-2xl font-bold text-gray-800 mb-2">Account Suspended</h1>
        
        <p class="text-gray-600 mb-6">
            {{ session('suspension_reason', 'Your company account has been suspended. Please contact your administrator or support for assistance.') }}
        </p>

        {{-- Possible Reasons --}}
        <div class="bg-gray-50 rounded-lg p-4 mb-6 text-left">
            <p class="text-sm font-medium text-gray-700 mb-2">This may happen due to:</p>
            <ul class="text-sm text-gray-600 space-y-1">
                <li class="flex items-center gap-2">
                    <span class="text-red-400">•</span> Payment overdue
                </li>
                <li class="flex items-center gap-2">
                    <span class="text-red-400">•</span> Subscription expired
                </li>
                <li class="flex items-center gap-2">
                    <span class="text-red-400">•</span> Terms of service violation
                </li>
                <li class="flex items-center gap-2">
                    <span class="text-red-400">•</span> Administrative action
                </li>
            </ul>
        </div>

        {{-- Actions --}}
        <div class="space-y-3">
            <a href="mailto:support@avandigital.com" 
               class="block w-full py-3 px-4 bg-emerald-600 text-white rounded-lg hover:bg-emerald-700 transition font-semibold">
                Contact Support
            </a>
            <form action="{{ route('logout') }}" method="POST">
                @csrf
                <button type="submit" class="w-full py-3 px-4 border border-gray-300 text-gray-700 rounded-lg hover:bg-gray-50 transition">
                    Sign Out
                </button>
            </form>
        </div>

        {{-- Company Info --}}
        @if(auth()->check() && auth()->user()->company)
        <div class="mt-6 pt-6 border-t border-gray-100 text-sm text-gray-500">
            <p>Company: <strong>{{ auth()->user()->company->name }}</strong></p>
            <p>Account: {{ auth()->user()->email }}</p>
        </div>
        @endif
    </div>
</div>
@endsection
