<header class="bg-white border-b sticky top-0 z-30">
   <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
      <div class="flex items-center justify-between h-16">
         <div class="flex items-center gap-4">
            {{-- Mobile Hamburger Menu --}}
            <button @click="$dispatch('sidebar-toggle')" 
                    class="md:hidden p-2 rounded-lg hover:bg-gray-100 active:bg-gray-200 transition touch-manipulation"
                    aria-label="Toggle menu">
                <svg class="w-6 h-6 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/>
                </svg>
            </button>
            <a href="{{ url('/') }}" class="flex items-center gap-3">
               @if(isset($currentCompany) && $currentCompany->logo_url)
                   <img src="{{ $currentCompany->logo_url }}" alt="{{ $currentCompany->name }}" class="h-8 w-auto">
               @else
                   <img src="{{ asset('storage/getlanded-logo.png') }}" alt="{{ config('app.name') }}" class="h-8 w-8 rounded-full">
               @endif
                <span class="text-xl font-bold text-emerald-600 tracking-tight">GetLanded</span>
               <span class="text-lg font-semibold text-gray-800 hidden sm:inline">{{ $currentCompany->name ?? config('app.name') }}</span>
            </a>
         </div>

         <div class="flex items-center gap-4">
            <!-- Language Switcher -->
            <div class="relative" x-data="{ open: false }">
               <button @click="open = !open"
                  class="flex items-center gap-2 px-3 py-2 text-sm border rounded hover:bg-gray-50">
                  <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                     <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M3 5h12M9 3v2m1.048 9.5A18.022 18.022 0 016.412 9m6.088 9h7M11 21l5-10 5 10M12.751 5C11.783 10.77 8.07 15.61 3 18.129">
                     </path>
                  </svg>
                  <span>{{ app()->getLocale() === 'id' ? 'ID' : 'EN' }}</span>
                  <svg class="w-4 h-4" :class="open ? 'rotate-180' : ''" fill="none" stroke="currentColor"
                     viewBox="0 0 24 24">
                     <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                  </svg>
               </button>
               <div x-show="open" @click.away="open = false" x-transition
                  class="absolute right-0 mt-2 w-48 bg-white border rounded shadow-lg z-50">
                  <a href="{{ route('lang.switch', 'en') }}"
                     class="block px-4 py-2 text-sm hover:bg-gray-100 {{ app()->getLocale() === 'en' ? 'bg-blue-50 text-blue-600 font-semibold' : '' }}">
                     🇬🇧 English
                  </a>
                  <a href="{{ route('lang.switch', 'id') }}"
                     class="block px-4 py-2 text-sm hover:bg-gray-100 {{ app()->getLocale() === 'id' ? 'bg-blue-50 text-blue-600 font-semibold' : '' }}">
                     🇮🇩 Bahasa Indonesia
                  </a>
               </div>
            </div>

            <div class="flex items-center gap-4">
                {{-- Notification Bell --}}
                @auth
                    <x-notification-bell />
                @endauth

                <div class="hidden sm:block">
                    <span class="text-sm text-slate-600">{{ Auth::check() ? Auth::user()->name : 'Guest' }}</span>
                </div>
            </div>
            @auth
               <form method="POST" action="{{ route('logout') }}">
                  @csrf
                  <button type="submit" class="text-sm text-red-600 hover:underline">
                     {{ __('app.logout') }}
                  </button>
               </form>
            @endauth
         </div>
      </div>
   </div>
</header>
