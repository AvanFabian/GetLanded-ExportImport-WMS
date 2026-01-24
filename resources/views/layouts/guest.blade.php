<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <x-meta-tags />

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />

        <!-- Scripts -->
        @vite(['resources/css/app.css', 'resources/js/app.js'])
        
        <style>
            .auth-gradient {
                background: linear-gradient(135deg, #064E3B 0%, #10B981 100%);
            }
            .glass-panel {
                background: rgba(255, 255, 255, 0.95);
                backdrop-filter: blur(10px);
            }
        </style>
    </head>
    <body class="font-sans text-gray-900 antialiased bg-gray-50">
        <div class="min-h-screen flex">
            <!-- Left Side: Hero Image (Hidden on mobile) -->
            <div class="hidden lg:flex w-1/2 bg-gray-900 relative user-select-none">
                <div class="absolute inset-0 bg-cover bg-center opacity-60" style="background-image: url('https://images.unsplash.com/photo-1586528116311-ad8dd3c8310d?ixlib=rb-1.2.1&auto=format&fit=crop&w=1950&q=80');"></div>
                <div class="absolute inset-0 bg-gradient-to-br from-emerald-900/90 to-black/50"></div>
                
                <div class="relative z-10 w-full flex flex-col justify-center px-12 text-white">
                    <a href="/" class="mb-8 flex items-center gap-3">
                        <div class="w-10 h-10 bg-white/20 rounded-xl flex items-center justify-center backdrop-blur-sm">
                            <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 8h14M5 8a2 2 0 110-4h14a2 2 0 110 4M5 8v10a2 2 0 002 2h10a2 2 0 002-2V8m-9 4h4"/>
                            </svg>
                        </div>
                        <span class="text-2xl font-bold tracking-tight">{{ config('app.name', 'AgroWMS') }}</span>
                    </a>
                    
                    <h1 class="text-4xl font-bold mb-6 leading-tight">
                        {{ __('landing.hero_headline_1') }}<br>
                        <span class="text-emerald-400">{{ __('landing.hero_headline_2') }}</span>
                    </h1>
                    <p class="text-lg text-gray-300 max-w-md">
                        {{ __('landing.hero_sub') }}
                    </p>
                    
                    <div class="mt-12 flex gap-4 text-sm text-gray-400">
                        <div class="flex items-center gap-2">
                            <svg class="w-5 h-5 text-emerald-500" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/></svg>
                            <span>{{ __('landing.trust_no_cc') }}</span>
                        </div>
                        <div class="flex items-center gap-2">
                            <svg class="w-5 h-5 text-emerald-500" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/></svg>
                            <span>{{ __('landing.trust_support') }}</span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Right Side: Auth Form -->
            <div class="w-full lg:w-1/2 flex items-center justify-center p-6 sm:p-12 relative">
                {{-- Language Switcher Top Right --}}
                <div class="absolute top-6 right-6">
                    <x-language-switcher />
                </div>

                <div class="w-full max-w-md">
                    {{-- Mobile Logo --}}
                    <div class="flex justify-center mb-8 lg:hidden">
                        <a href="/" class="flex items-center gap-2">
                            <div class="w-10 h-10 bg-emerald-600 rounded-xl flex items-center justify-center">
                                <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 8h14M5 8a2 2 0 110-4h14a2 2 0 110 4M5 8v10a2 2 0 002 2h10a2 2 0 002-2V8m-9 4h4"/>
                                </svg>
                            </div>
                            <span class="text-2xl font-bold text-gray-900">{{ config('app.name', 'AgroWMS') }}</span>
                        </a>
                    </div>

                    {{ $slot }}
                </div>
            </div>
        </div>
    </body>
</html>
