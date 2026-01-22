<x-guest-layout>
    <div class="flex flex-col items-center justify-center min-h-[60vh] text-center">
        <div class="bg-red-100 p-4 rounded-full mb-6">
             <svg class="h-16 w-16 text-red-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
            </svg>
        </div>
        <h1 class="text-6xl font-extrabold text-gray-800 tracking-tight">500</h1>
        <h2 class="text-2xl font-bold text-gray-800 mt-4">Server Error</h2>
        <p class="text-gray-500 mt-2 max-w-md">Oops! Something went wrong on our end. Please try again later or contact support.</p>
        
        <a href="{{ route('dashboard') }}" class="mt-8 px-6 py-3 bg-emerald-600 text-white font-semibold rounded-lg shadow-md hover:bg-emerald-700 focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:ring-opacity-75 transition duration-200">
            Return to Dashboard
        </a>
    </div>
</x-guest-layout>
