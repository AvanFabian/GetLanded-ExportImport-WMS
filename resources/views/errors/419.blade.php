<x-guest-layout>

@section('title', '419 - Session Expired')

@section('content')
   <div class="flex items-center justify-center min-h-[60vh] px-4">
      <div class="text-center">
         <!-- Error Icon -->
         <div class="flex justify-center mb-6">
            <svg class="h-24 w-24 text-orange-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
               <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                  d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
            </svg>
         </div>

         <!-- Error Message -->
         <h1 class="text-6xl font-bold text-gray-900 mb-4">419</h1>
         <h2 class="text-2xl font-semibold text-gray-800 mb-4">Session Expired</h2>
         <p class="text-gray-600 mb-8 max-w-md mx-auto">
            Your session has expired due to inactivity. Please refresh the page or log in again to continue.
         </p>

         <!-- Info Box -->
         <div class="bg-orange-50 border border-orange-200 rounded-lg p-6 max-w-md mx-auto mb-8">
            <div class="flex items-start">
               <svg class="h-6 w-6 text-orange-600 mt-1 mr-3 flex-shrink-0" fill="none" stroke="currentColor"
                  viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                     d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
               </svg>
               <div class="text-left">
                  <h3 class="text-sm font-semibold text-orange-800 mb-1">Security Notice</h3>
                  <p class="text-sm text-orange-700">
                     For your security, sessions expire after a period of inactivity. Your data has been saved.
                  </p>
               </div>
            </div>
         </div>

         <!-- Action Buttons -->
         <div class="flex flex-col sm:flex-row gap-4 justify-center">
            <button onclick="window.location.reload()"
               class="inline-flex items-center px-6 py-3 bg-primary text-white font-semibold rounded-lg shadow-md hover:bg-blue-700 transition duration-300">
               <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                     d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
               </svg>
               Refresh Page
            </button>
            <a href="{{ route('login') }}"
               class="inline-flex items-center px-6 py-3 bg-gray-200 text-gray-800 font-semibold rounded-lg shadow-md hover:bg-gray-300 transition duration-300">
               <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                     d="M11 16l-4-4m0 0l4-4m-4 4h14m-5 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h7a3 3 0 013 3v1" />
               </svg>
               Go to Login
            </a>
         </div>
      </div>
   </div>
</x-guest-layout>
