<x-guest-layout>
    <div class="mb-8">
        <h2 class="text-3xl font-bold text-gray-900">{{ __('Log in') }}</h2>
        <p class="mt-2 text-sm text-gray-600">
            {{ __('Welcome back! Please enter your details.') }}
        </p>
    </div>

    <!-- Session Status -->
    <x-auth-session-status class="mb-4" :status="session('status')" />

    <form method="POST" action="{{ route('login') }}" class="space-y-6">
        @csrf

        <!-- Email Address -->
        <div>
            <x-input-label for="email" :value="__('Email')" class="text-gray-700 font-medium" />
            <x-text-input id="email" class="block mt-1 w-full rounded-lg border-gray-300 focus:ring-emerald-500 focus:border-emerald-500 transition" 
                          type="email" name="email" :value="old('email')" required autofocus autocomplete="username" 
                          placeholder="namamu@perusahaan.com" />
            <x-input-error :messages="$errors->get('email')" class="mt-2" />
        </div>

        <!-- Password -->
        <div>
            <x-input-label for="password" :value="__('Password')" class="text-gray-700 font-medium" />
            <x-text-input id="password" class="block mt-1 w-full rounded-lg border-gray-300 focus:ring-emerald-500 focus:border-emerald-500 transition" 
                          type="password" name="password" required autocomplete="current-password" 
                          placeholder="••••••••" />
            <x-input-error :messages="$errors->get('password')" class="mt-2" />
        </div>

        <!-- Remember Me & Forgot Password -->
        <div class="flex items-center justify-between">
            <label for="remember_me" class="inline-flex items-center">
                <input id="remember_me" type="checkbox" class="rounded border-gray-300 text-emerald-600 shadow-sm focus:ring-emerald-500 cursor-pointer" name="remember">
                <span class="ms-2 text-sm text-gray-600 cursor-pointer">{{ __('Remember me') }}</span>
            </label>

            @if (Route::has('password.request'))
                <a class="text-sm text-emerald-600 hover:text-emerald-700 font-medium" href="{{ route('password.request') }}">
                    {{ __('Forgot your password?') }}
                </a>
            @endif
        </div>

        <div>
            <x-primary-button class="w-full justify-center py-3 bg-emerald-600 hover:bg-emerald-700 focus:ring-emerald-500 text-base font-semibold shadow-lg shadow-emerald-200">
                {{ __('Log in') }}
            </x-primary-button>
        </div>

        <div class="mt-6 text-center text-sm text-gray-600">
            {{ __('Don\'t have an account?') }}
            <a href="{{ route('register') }}" class="font-medium text-emerald-600 hover:text-emerald-500">
                {{ __('Register') }}
            </a>
        </div>
    </form>
</x-guest-layout>
