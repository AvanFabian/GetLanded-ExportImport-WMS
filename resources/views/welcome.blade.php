<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <x-meta-tags 
        :title="__('landing.hero_headline_1') . ' ' . __('landing.hero_headline_2')"
        :description="__('landing.hero_sub')"
    />
    
    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600,700,800&display=swap" rel="stylesheet" />
    
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    
    <style>
        .gradient-text {
            background: linear-gradient(135deg, #064E3B 0%, #10B981 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }
        .hero-gradient {
            background: linear-gradient(180deg, #F0FDF4 0%, #FFFFFF 100%);
        }
        .feature-card:hover {
            transform: translateY(-4px);
        }
        .glass-card {
            background: rgba(255, 255, 255, 0.7);
            backdrop-filter: blur(10px);
        }
    </style>
</head>
<body class="antialiased bg-white" x-data="{ mobileMenu: false }">
    
    {{-- Navigation --}}
    <nav class="fixed top-0 left-0 right-0 z-50 bg-white/90 backdrop-blur-md border-b border-gray-100">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex items-center justify-between h-16">
                {{-- Logo --}}
                <div class="flex items-center gap-2">
                    <div class="w-9 h-9 bg-gradient-to-br from-emerald-600 to-emerald-700 rounded-xl flex items-center justify-center shadow-lg shadow-emerald-200">
                        <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 8h14M5 8a2 2 0 110-4h14a2 2 0 110 4M5 8v10a2 2 0 002 2h10a2 2 0 002-2V8m-9 4h4"/>
                        </svg>
                    </div>
                    <span class="text-xl font-bold text-gray-900">{{ config('app.name', 'AgroWMS') }}</span>
                </div>
                
                {{-- Desktop Nav --}}
                <div class="hidden md:flex items-center gap-8">
                    <a href="#features" class="text-gray-600 hover:text-gray-900 text-sm font-medium transition">{{ __('landing.nav_features') }}</a>
                    <a href="#benefits" class="text-gray-600 hover:text-gray-900 text-sm font-medium transition">{{ __('landing.nav_benefits') }}</a>
                    <a href="{{ route('terms') }}" class="text-gray-600 hover:text-gray-900 text-sm font-medium transition">{{ __('landing.nav_terms') }}</a>
                    <a href="{{ route('privacy') }}" class="text-gray-600 hover:text-gray-900 text-sm font-medium transition">{{ __('landing.nav_privacy') }}</a>
                </div>
                
                {{-- Auth Buttons --}}
                <div class="hidden md:flex items-center gap-3">
                    <x-language-switcher />
                    @if (Route::has('login'))
                        @auth
                            <a href="{{ url('/dashboard') }}" class="px-4 py-2 text-sm font-medium text-emerald-700 hover:text-emerald-800 transition">
                                {{ __('landing.nav_dashboard') }}
                            </a>
                        @else
                            <a href="{{ route('login') }}" class="px-4 py-2 text-sm font-medium text-gray-700 hover:text-gray-900 transition">
                                {{ __('landing.nav_login') }}
                            </a>
                            @if (Route::has('register'))
                                <a href="{{ route('register') }}" class="px-6 py-2.5 bg-emerald-600 text-white text-sm font-semibold rounded-lg hover:bg-emerald-700 transition shadow-lg shadow-emerald-200">
                                    {{ __('landing.nav_register') }}
                                </a>
                            @endif
                        @endauth
                    @endif
                </div>
                
                {{-- Mobile Menu Toggle --}}
                <button @click="mobileMenu = !mobileMenu" class="md:hidden p-2 text-gray-600">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/>
                    </svg>
                </button>
            </div>
            
            {{-- Mobile Menu --}}
            <div x-show="mobileMenu" x-cloak class="md:hidden py-4 border-t border-gray-100">
                <div class="flex flex-col gap-4">
                    <a href="#features" class="text-gray-600 hover:text-gray-900 text-sm font-medium">{{ __('landing.nav_features') }}</a>
                    <a href="#benefits" class="text-gray-600 hover:text-gray-900 text-sm font-medium">{{ __('landing.nav_benefits') }}</a>
                    <a href="{{ route('terms') }}" class="text-gray-600 hover:text-gray-900 text-sm font-medium">{{ __('landing.nav_terms') }}</a>
                    <a href="{{ route('privacy') }}" class="text-gray-600 hover:text-gray-900 text-sm font-medium">{{ __('landing.nav_privacy') }}</a>
                    <hr class="border-gray-100">
                    @guest
                        <a href="{{ route('login') }}" class="text-gray-700 font-medium">{{ __('landing.nav_login') }}</a>
                        <a href="{{ route('register') }}" class="px-4 py-2 bg-emerald-600 text-white text-center rounded-lg font-medium">{{ __('landing.nav_register') }}</a>
                    @else
                        <a href="{{ url('/dashboard') }}" class="text-emerald-700 font-medium">{{ __('landing.nav_dashboard') }}</a>
                    @endguest
                    <hr class="border-gray-100">
                    <x-language-switcher :mobile="true" />
                </div>
            </div>
        </div>
    </nav>
    
    {{-- Hero Section (Anti-Pusing Framework) --}}
    <section class="hero-gradient pt-32 pb-20 overflow-hidden">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="grid lg:grid-cols-2 gap-12 items-center">
                <div class="text-center lg:text-left">
                    {{-- Badge --}}
                    <div class="inline-flex items-center gap-2 px-4 py-2 bg-emerald-100 text-emerald-700 rounded-full text-sm font-medium mb-6">
                        <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M11.3 1.046A1 1 0 0112 2v5h4a1 1 0 01.82 1.573l-7 10A1 1 0 018 18v-5H4a1 1 0 01-.82-1.573l7-10a1 1 0 011.12-.38z" clip-rule="evenodd"/>
                        </svg>
                        {{ __('landing.hero_badge') }}
                    </div>
                    
                    {{-- Headline --}}
                    <h1 class="text-4xl sm:text-5xl lg:text-6xl font-extrabold text-gray-900 leading-tight mb-6">
                        {{ __('landing.hero_headline_1') }}<br>
                        <span class="gradient-text">{{ __('landing.hero_headline_2') }}</span>
                    </h1>
                    
                    {{-- Sub-headline --}}
                    <p class="text-xl text-gray-600 mb-8 max-w-lg mx-auto lg:mx-0">
                        {{ __('landing.hero_sub') }}
                    </p>
                    
                    {{-- CTA Buttons --}}
                    <div class="flex flex-col sm:flex-row gap-4 justify-center lg:justify-start">
                        @if (Route::has('register'))
                            <a href="{{ route('register') }}" class="px-8 py-4 bg-emerald-600 text-white font-bold rounded-xl hover:bg-emerald-700 transition shadow-xl shadow-emerald-200 text-lg flex items-center justify-center gap-2">
                                {{ __('landing.cta_start') }}
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6"/>
                                </svg>
                            </a>
                        @endif
                        <a href="#features" class="px-8 py-4 bg-white text-gray-900 font-semibold rounded-xl border-2 border-gray-200 hover:border-gray-300 transition text-lg">
                            {{ __('landing.cta_features') }}
                        </a>
                    </div>
                    
                    {{-- Trust Badges --}}
                    <div class="mt-10 flex flex-wrap items-center justify-center lg:justify-start gap-6 text-sm text-gray-500">
                        <div class="flex items-center gap-2">
                            <svg class="w-5 h-5 text-emerald-500" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                            </svg>
                            {{ __('landing.trust_no_cc') }}
                        </div>
                        <div class="flex items-center gap-2">
                            <svg class="w-5 h-5 text-emerald-500" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                            </svg>
                            {{ __('landing.trust_setup') }}
                        </div>
                        <div class="flex items-center gap-2">
                            <svg class="w-5 h-5 text-emerald-500" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                            </svg>
                            {{ __('landing.trust_support') }}
                        </div>
                    </div>
                </div>
                
                {{-- Hero Visual --}}
                <div class="relative lg:pl-8">
                    <div class="relative">
                        {{-- Dashboard Preview Card --}}
                        <div class="bg-white rounded-2xl shadow-2xl shadow-gray-200/50 border border-gray-100 p-6 transform rotate-1 hover:rotate-0 transition-transform duration-500">
                            <div class="flex items-center gap-3 mb-4">
                                <div class="w-3 h-3 rounded-full bg-red-400"></div>
                                <div class="w-3 h-3 rounded-full bg-yellow-400"></div>
                                <div class="w-3 h-3 rounded-full bg-green-400"></div>
                            </div>
                            <div class="space-y-3">
                                <div class="h-8 bg-gradient-to-r from-emerald-100 to-emerald-50 rounded-lg"></div>
                                <div class="grid grid-cols-3 gap-3">
                                    <div class="h-20 bg-blue-50 rounded-lg flex flex-col items-center justify-center">
                                        <div class="text-2xl font-bold text-blue-600">847</div>
                                        <div class="text-xs text-gray-500">{{ __('landing.dashboard_preview_sku') }}</div>
                                    </div>
                                    <div class="h-20 bg-emerald-50 rounded-lg flex flex-col items-center justify-center">
                                        <div class="text-2xl font-bold text-emerald-600">12</div>
                                        <div class="text-xs text-gray-500">{{ __('landing.dashboard_preview_warehouse') }}</div>
                                    </div>
                                    <div class="h-20 bg-purple-50 rounded-lg flex flex-col items-center justify-center">
                                        <div class="text-2xl font-bold text-purple-600">99%</div>
                                        <div class="text-xs text-gray-500">{{ __('landing.dashboard_preview_accuracy') }}</div>
                                    </div>
                                </div>
                                <div class="h-24 bg-gray-50 rounded-lg"></div>
                            </div>
                        </div>
                        
                        {{-- Floating Card 1 --}}
                        <div class="absolute -top-4 -left-4 bg-white rounded-xl shadow-lg p-4 glass-card">
                            <div class="flex items-center gap-3">
                                <div class="w-10 h-10 bg-green-100 rounded-full flex items-center justify-center">
                                    <svg class="w-5 h-5 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                                    </svg>
                                </div>
                                <div>
                                    <div class="text-sm font-semibold text-gray-900">{{ __('landing.card_stock_updated') }}</div>
                                    <div class="text-xs text-gray-500">{{ __('landing.card_just_now') }}</div>
                                </div>
                            </div>
                        </div>
                        
                        {{-- Floating Card 2 --}}
                        <div class="absolute -bottom-4 -right-4 bg-white rounded-xl shadow-lg p-4 glass-card">
                            <div class="flex items-center gap-3">
                                <div class="w-10 h-10 bg-amber-100 rounded-full flex items-center justify-center">
                                    <svg class="w-5 h-5 text-amber-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                    </svg>
                                </div>
                                <div>
                                    <div class="text-sm font-semibold text-gray-900">{{ __('landing.card_realtime') }}</div>
                                    <div class="text-xs text-gray-500">{{ __('landing.card_multidevice') }}</div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
    
    {{-- Problem Section (Anti-Pusing: Identify the Pain) --}}
    <section class="py-20 bg-gray-50">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="text-center max-w-3xl mx-auto mb-12">
                <h2 class="text-3xl sm:text-4xl font-bold text-gray-900 mb-4">
                    {{ __('landing.pain_title') }}
                </h2>
                <p class="text-lg text-gray-600">
                    {{ __('landing.pain_sub') }}
                </p>
            </div>
            
            <div class="grid md:grid-cols-3 gap-8">
                {{-- Pain Point 1 --}}
                <div class="bg-white p-8 rounded-2xl border border-red-100">
                    <div class="w-14 h-14 bg-red-100 rounded-xl flex items-center justify-center mb-6">
                        <svg class="w-7 h-7 text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                        </svg>
                    </div>
                    <h3 class="text-xl font-bold text-gray-900 mb-3">{{ __('landing.pain_1_title') }}</h3>
                    <p class="text-gray-600">{{ __('landing.pain_1_desc') }}</p>
                </div>
                
                {{-- Pain Point 2 --}}
                <div class="bg-white p-8 rounded-2xl border border-red-100">
                    <div class="w-14 h-14 bg-red-100 rounded-xl flex items-center justify-center mb-6">
                        <svg class="w-7 h-7 text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                    </div>
                    <h3 class="text-xl font-bold text-gray-900 mb-3">{{ __('landing.pain_2_title') }}</h3>
                    <p class="text-gray-600">{{ __('landing.pain_2_desc') }}</p>
                </div>
                
                {{-- Pain Point 3 --}}
                <div class="bg-white p-8 rounded-2xl border border-red-100">
                    <div class="w-14 h-14 bg-red-100 rounded-xl flex items-center justify-center mb-6">
                        <svg class="w-7 h-7 text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.172 16.172a4 4 0 015.656 0M9 10h.01M15 10h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                    </div>
                    <h3 class="text-xl font-bold text-gray-900 mb-3">{{ __('landing.pain_3_title') }}</h3>
                    <p class="text-gray-600">{{ __('landing.pain_3_desc') }}</p>
                </div>
            </div>
        </div>
    </section>
    
    {{-- Features Section --}}
    <section id="features" class="py-20 bg-white">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="text-center max-w-3xl mx-auto mb-16">
                <div class="inline-flex items-center gap-2 px-4 py-2 bg-emerald-100 text-emerald-700 rounded-full text-sm font-medium mb-4">
                    {{ __('landing.features_badge') }}
                </div>
                <h2 class="text-3xl sm:text-4xl font-bold text-gray-900 mb-4">
                    {{ __('landing.features_title') }}
                </h2>
                <p class="text-lg text-gray-600">
                    {{ __('landing.features_sub') }}
                </p>
            </div>
            
            <div class="grid md:grid-cols-2 lg:grid-cols-3 gap-8">
                {{-- Feature 1 --}}
                <div class="feature-card bg-white p-8 rounded-2xl border border-gray-100 hover:shadow-xl hover:border-emerald-100 transition-all duration-300">
                    <div class="w-14 h-14 bg-gradient-to-br from-blue-500 to-blue-600 rounded-xl flex items-center justify-center mb-6 shadow-lg shadow-blue-200">
                        <svg class="w-7 h-7 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
                        </svg>
                    </div>
                    <h3 class="text-xl font-bold text-gray-900 mb-3">{{ __('landing.feature_1_title') }}</h3>
                    <p class="text-gray-600">{{ __('landing.feature_1_desc') }}</p>
                </div>
                
                {{-- Feature 2 --}}
                <div class="feature-card bg-white p-8 rounded-2xl border border-gray-100 hover:shadow-xl hover:border-emerald-100 transition-all duration-300">
                    <div class="w-14 h-14 bg-gradient-to-br from-purple-500 to-purple-600 rounded-xl flex items-center justify-center mb-6 shadow-lg shadow-purple-200">
                        <svg class="w-7 h-7 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
                        </svg>
                    </div>
                    <h3 class="text-xl font-bold text-gray-900 mb-3">{{ __('landing.feature_2_title') }}</h3>
                    <p class="text-gray-600">{{ __('landing.feature_2_desc') }}</p>
                </div>
                
                {{-- Feature 3 --}}
                <div class="feature-card bg-white p-8 rounded-2xl border border-gray-100 hover:shadow-xl hover:border-emerald-100 transition-all duration-300">
                    <div class="w-14 h-14 bg-gradient-to-br from-amber-500 to-amber-600 rounded-xl flex items-center justify-center mb-6 shadow-lg shadow-amber-200">
                        <svg class="w-7 h-7 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                        </svg>
                    </div>
                    <h3 class="text-xl font-bold text-gray-900 mb-3">{{ __('landing.feature_3_title') }}</h3>
                    <p class="text-gray-600">{{ __('landing.feature_3_desc') }}</p>
                </div>
                
                {{-- Feature 4 --}}
                <div class="feature-card bg-white p-8 rounded-2xl border border-gray-100 hover:shadow-xl hover:border-emerald-100 transition-all duration-300">
                    <div class="w-14 h-14 bg-gradient-to-br from-emerald-500 to-emerald-600 rounded-xl flex items-center justify-center mb-6 shadow-lg shadow-emerald-200">
                        <svg class="w-7 h-7 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                        </svg>
                    </div>
                    <h3 class="text-xl font-bold text-gray-900 mb-3">{{ __('landing.feature_4_title') }}</h3>
                    <p class="text-gray-600">{{ __('landing.feature_4_desc') }}</p>
                </div>
                
                {{-- Feature 5 --}}
                <div class="feature-card bg-white p-8 rounded-2xl border border-gray-100 hover:shadow-xl hover:border-emerald-100 transition-all duration-300">
                    <div class="w-14 h-14 bg-gradient-to-br from-rose-500 to-rose-600 rounded-xl flex items-center justify-center mb-6 shadow-lg shadow-rose-200">
                        <svg class="w-7 h-7 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4"/>
                        </svg>
                    </div>
                    <h3 class="text-xl font-bold text-gray-900 mb-3">{{ __('landing.feature_5_title') }}</h3>
                    <p class="text-gray-600">{{ __('landing.feature_5_desc') }}</p>
                </div>
                
                {{-- Feature 6 --}}
                <div class="feature-card bg-white p-8 rounded-2xl border border-gray-100 hover:shadow-xl hover:border-emerald-100 transition-all duration-300">
                    <div class="w-14 h-14 bg-gradient-to-br from-cyan-500 to-cyan-600 rounded-xl flex items-center justify-center mb-6 shadow-lg shadow-cyan-200">
                        <svg class="w-7 h-7 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                        </svg>
                    </div>
                    <h3 class="text-xl font-bold text-gray-900 mb-3">{{ __('landing.feature_6_title') }}</h3>
                    <p class="text-gray-600">{{ __('landing.feature_6_desc') }}</p>
                </div>
            </div>
        </div>
    </section>
    
    {{-- Benefits Section --}}
    <section id="benefits" class="py-20 bg-gradient-to-br from-emerald-700 to-emerald-900 text-white">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="text-center mb-16">
                <h2 class="text-3xl sm:text-4xl font-bold mb-4">
                    {{ __('landing.benefits_title') }}
                </h2>
                <p class="text-lg text-emerald-100 max-w-2xl mx-auto">
                    {{ __('landing.benefits_sub') }}
                </p>
            </div>
            
            <div class="grid md:grid-cols-3 gap-8">
                <div class="text-center p-8">
                    <div class="text-5xl font-bold text-white mb-2">90%</div>
                    <div class="text-emerald-100">{{ __('landing.benefit_1_label') }}</div>
                </div>
                <div class="text-center p-8">
                    <div class="text-5xl font-bold text-white mb-2">5x</div>
                    <div class="text-emerald-100">{{ __('landing.benefit_2_label') }}</div>
                </div>
                <div class="text-center p-8">
                    <div class="text-5xl font-bold text-white mb-2">99%</div>
                    <div class="text-emerald-100">{{ __('landing.benefit_3_label') }}</div>
                </div>
            </div>
        </div>
    </section>
    
    {{-- CTA Section --}}
    <section class="py-20 bg-gray-50">
        <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 text-center">
            <h2 class="text-3xl sm:text-4xl font-bold text-gray-900 mb-4">
                {{ __('landing.cta_footer_title') }}
            </h2>
            <p class="text-xl text-gray-600 mb-8">
                {{ __('landing.cta_footer_sub') }}
            </p>
            @if (Route::has('register'))
                <a href="{{ route('register') }}" class="inline-flex items-center gap-2 px-8 py-4 bg-emerald-600 text-white font-bold rounded-xl hover:bg-emerald-700 transition shadow-xl shadow-emerald-200 text-lg">
                    {{ __('landing.cta_footer_btn') }}
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6"/>
                    </svg>
                </a>
            @endif
        </div>
    </section>
    
    {{-- Footer --}}
    <footer class="bg-gray-900 text-gray-400 py-16">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="grid md:grid-cols-4 gap-12">
                {{-- Brand --}}
                <div class="md:col-span-2">
                    <div class="flex items-center gap-2 mb-4">
                        <div class="w-9 h-9 bg-emerald-600 rounded-xl flex items-center justify-center">
                            <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 8h14M5 8a2 2 0 110-4h14a2 2 0 110 4M5 8v10a2 2 0 002 2h10a2 2 0 002-2V8m-9 4h4"/>
                            </svg>
                        </div>
                        <span class="text-xl font-bold text-white">{{ config('app.name', 'AgroWMS') }}</span>
                    </div>
                        {{ __('landing.footer_desc') }}
                    </p>
                </div>
                
                {{-- Links --}}
                <div>
                    <h4 class="text-white font-semibold mb-4">{{ __('landing.footer_product') }}</h4>
                    <ul class="space-y-2">
                        <li><a href="#features" class="hover:text-white transition">{{ __('landing.nav_features') }}</a></li>
                        <li><a href="#benefits" class="hover:text-white transition">{{ __('landing.nav_benefits') }}</a></li>
                    </ul>
                </div>
                
                {{-- Legal --}}
                <div>
                    <h4 class="text-white font-semibold mb-4">{{ __('landing.footer_legal') }}</h4>
                    <ul class="space-y-2">
                        <li><a href="{{ route('terms') }}" class="hover:text-white transition">{{ __('landing.nav_terms') }}</a></li>
                        <li><a href="{{ route('privacy') }}" class="hover:text-white transition">{{ __('landing.nav_privacy') }}</a></li>
                    </ul>
                </div>
            </div>
            
            <div class="border-t border-gray-800 mt-12 pt-8 flex flex-col md:flex-row items-center justify-between gap-4">
                <p class="text-sm">© {{ date('Y') }} {{ config('app.name', 'AgroWMS') }}. All rights reserved.</p>
                <div class="flex items-center gap-4 text-sm">
                    <a href="{{ route('terms') }}" class="hover:text-white transition">{{ __('landing.nav_terms') }}</a>
                    <span>•</span>
                    <a href="{{ route('privacy') }}" class="hover:text-white transition">{{ __('landing.nav_privacy') }}</a>
                </div>
            </div>
        </div>
    </footer>
</body>
</html>
