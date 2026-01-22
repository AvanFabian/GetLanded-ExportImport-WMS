<x-guest-layout>
    <div class="flex flex-col items-center justify-center min-h-[60vh] text-center">
        <div class="bg-emerald-100 p-4 rounded-full mb-6">
            <svg class="h-16 w-16 text-emerald-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.172 16.172a4 4 0 015.656 0M9 10h.01M15 10h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
            </svg>
        </div>
        <h1 class="text-6xl font-extrabold text-emerald-600 tracking-tight">404</h1>
        <h2 class="text-2xl font-bold text-gray-800 mt-4">Page Not Found</h2>
        <p class="text-gray-500 mt-2 max-w-md">Sorry, we couldn't find the page you're looking for. It might have been moved or deleted.</p>
        
        <a href="{{ route('dashboard') }}" class="mt-8 px-6 py-3 bg-emerald-600 text-white font-semibold rounded-lg shadow-md hover:bg-emerald-700 focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:ring-opacity-75 transition duration-200">
            Return to Dashboard
        </a>
    </div>
</x-guest-layout>
