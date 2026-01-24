<x-guest-layout>
    <div class="mb-8">
        <h2 class="text-3xl font-bold text-gray-900">{{ __('Register') }}</h2>
        <p class="mt-2 text-sm text-gray-600">
            {{ __('Create your account to start managing your warehouse.') }}
        </p>
    </div>

    <form method="POST" action="{{ route('register') }}" class="space-y-5">
        @csrf

        <!-- Name -->
        <div>
            <x-input-label for="name" :value="__('Name')" class="text-gray-700 font-medium" />
            <x-text-input id="name" class="block mt-1 w-full rounded-lg border-gray-300 focus:ring-emerald-500 focus:border-emerald-500 transition" 
                          type="text" name="name" :value="old('name')" required autofocus autocomplete="name" 
                          placeholder="John Doe" />
            <x-input-error :messages="$errors->get('name')" class="mt-2" />
        </div>

        <!-- Email Address -->
        <div>
            <x-input-label for="email" :value="__('Email')" class="text-gray-700 font-medium" />
            <x-text-input id="email" class="block mt-1 w-full rounded-lg border-gray-300 focus:ring-emerald-500 focus:border-emerald-500 transition" 
                          type="email" name="email" :value="old('email')" required autocomplete="username" 
                          placeholder="nama@perusahaan.com" />
            <x-input-error :messages="$errors->get('email')" class="mt-2" />
        </div>

        <!-- Password -->
        <div>
            <x-input-label for="password" :value="__('Password')" class="text-gray-700 font-medium" />
            <x-text-input id="password" class="block mt-1 w-full rounded-lg border-gray-300 focus:ring-emerald-500 focus:border-emerald-500 transition" 
                          type="password" name="password" required autocomplete="new-password" 
                          placeholder="••••••••" />
            <x-input-error :messages="$errors->get('password')" class="mt-2" />
        </div>

        <!-- Confirm Password -->
        <div>
            <x-input-label for="password_confirmation" :value="__('Confirm Password')" class="text-gray-700 font-medium" />
            <x-text-input id="password_confirmation" class="block mt-1 w-full rounded-lg border-gray-300 focus:ring-emerald-500 focus:border-emerald-500 transition" 
                          type="password" name="password_confirmation" required autocomplete="new-password" 
                          placeholder="••••••••" />
            <x-input-error :messages="$errors->get('password_confirmation')" class="mt-2" />
        </div>

        <!-- Terms of Service & Privacy Policy -->
        <div class="mt-4">
            <label class="flex items-start">
                <input type="checkbox" 
                       name="terms" 
                       id="terms"
                       class="rounded border-gray-300 text-emerald-600 shadow-sm focus:ring-emerald-500 mt-1 cursor-pointer"
                       {{ old('terms') ? 'checked' : '' }}
                       required>
                <span class="ms-2 text-sm text-gray-600">
                    {{ __('I agree to the') }}
                    <a href="{{ route('terms') }}" target="_blank" class="text-emerald-600 hover:underline font-medium">{{ __('Terms of Service') }}</a>
                    {{ __('and') }}
                    <a href="{{ route('privacy') }}" target="_blank" class="text-emerald-600 hover:underline font-medium">{{ __('Privacy Policy') }}</a>
                </span>
            </label>
            <x-input-error :messages="$errors->get('terms')" class="mt-2" />
        </div>

        <div class="pt-2">
            <x-primary-button class="w-full justify-center py-3 bg-emerald-600 hover:bg-emerald-700 focus:ring-emerald-500 text-base font-semibold shadow-lg shadow-emerald-200">
                {{ __('Register') }}
            </x-primary-button>
        </div>

        <div class="mt-6 text-center text-sm text-gray-600">
            {{ __('Already registered?') }}
            <a href="{{ route('login') }}" class="font-medium text-emerald-600 hover:text-emerald-500">
                {{ __('Log in') }}
            </a>
        </div>
    </form>
</x-guest-layout>
