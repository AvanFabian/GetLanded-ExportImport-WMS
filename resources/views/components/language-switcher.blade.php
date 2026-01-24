@props(['mobile' => false])

@if($mobile)
    <div class="pt-4 pb-1 border-t border-gray-200">
        <div class="mt-3 space-y-1">
            <div class="px-4 text-xs font-semibold text-gray-500 uppercase">
                Language / Bahasa
            </div>
            <x-responsive-nav-link :href="route('lang.switch', 'id')" :active="app()->getLocale() === 'id'">
                🇮🇩 Indonesia
            </x-responsive-nav-link>
            <x-responsive-nav-link :href="route('lang.switch', 'en')" :active="app()->getLocale() === 'en'">
                🇺🇸 English
            </x-responsive-nav-link>
        </div>
    </div>
@else
    <x-dropdown align="right" width="48">
        <x-slot name="trigger">
            <button class="inline-flex items-center px-3 py-2 border border-transparent text-sm leading-4 font-medium rounded-md text-gray-500 bg-white hover:text-gray-700 focus:outline-none transition ease-in-out duration-150">
                <div class="font-bold">{{ strtoupper(app()->getLocale()) }}</div>

                <div class="ms-1">
                    <svg class="fill-current h-4 w-4" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd" />
                    </svg>
                </div>
            </button>
        </x-slot>

        <x-slot name="content">
            <x-dropdown-link :href="route('lang.switch', 'id')">
                <div class="flex items-center gap-2">
                    <span>🇮🇩</span> <span>Indonesia</span>
                </div>
            </x-dropdown-link>
            <x-dropdown-link :href="route('lang.switch', 'en')">
                <div class="flex items-center gap-2">
                    <span>🇺🇸</span> <span>English</span>
                </div>
            </x-dropdown-link>
        </x-slot>
    </x-dropdown>
@endif
