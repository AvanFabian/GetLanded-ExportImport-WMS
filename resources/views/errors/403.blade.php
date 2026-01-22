<x-guest-layout>
    <div class="flex flex-col items-center justify-center min-h-[60vh] text-center">
        <div class="bg-yellow-100 p-4 rounded-full mb-6">
            <svg class="h-16 w-16 text-yellow-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
            </svg>
        </div>
        <h1 class="text-6xl font-extrabold text-gray-800 tracking-tight">403</h1>
        <h2 class="text-2xl font-bold text-gray-800 mt-4">Access Forbidden</h2>
        <p class="text-gray-500 mt-2 max-w-md">You don't have permission to access this area. Please contact your administrator if you believe this is an error.</p>
        
        <a href="{{ route('dashboard') }}" class="mt-8 px-6 py-3 bg-emerald-600 text-white font-semibold rounded-lg shadow-md hover:bg-emerald-700 focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:ring-opacity-75 transition duration-200">
            Return to Dashboard
        </a>
    </div>
</x-guest-layout>
