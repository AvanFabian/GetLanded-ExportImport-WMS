<x-guest-layout>

@section('title', '429 - Too Many Requests')

@section('content')
   <div class="flex items-center justify-center min-h-[60vh] px-4">
      <div class="text-center">
         <!-- Error Icon -->
         <div class="flex justify-center mb-6">
            <svg class="h-24 w-24 text-orange-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
               <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                  d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
            </svg>
         </div>

         <!-- Error Message -->
         <h1 class="text-6xl font-bold text-gray-900 mb-4">429</h1>
         <h2 class="text-2xl font-semibold text-gray-800 mb-4">Too Many Requests</h2>
         <p class="text-gray-600 mb-8 max-w-md mx-auto">
            {{ $message ?? 'You have made too many requests in a short period. Please wait a few minutes and try again.' }}
         </p>

         <!-- Info Box -->
         <div class="bg-orange-50 border border-orange-200 rounded-lg p-6 max-w-md mx-auto mb-8">
            <div class="flex items-start">
               <svg class="h-6 w-6 text-orange-600 mt-1 mr-3 flex-shrink-0" fill="none" stroke="currentColor"
                  viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                     d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
               </svg>
               <div class="text-left">
                  <h3 class="text-sm font-semibold text-orange-800 mb-1">Rate Limit Protection</h3>
                  <p class="text-sm text-orange-700">
                     This is a security measure to protect against automated attacks and ensure fair usage for all users.
                  </p>
               </div>
            </div>
         </div>

         <!-- Countdown Timer (Optional enhancement) -->
         <div id="countdown" class="mb-8 text-lg font-semibold text-gray-700">
            Please wait <span id="timer" class="text-primary">60</span> seconds...
         </div>

         <!-- Action Buttons -->
         <div class="flex flex-col sm:flex-row gap-4 justify-center">
            <a href="{{ route('dashboard') }}"
               class="inline-flex items-center px-6 py-3 bg-primary text-white font-semibold rounded-lg shadow-md hover:bg-blue-700 transition duration-300">
               <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                     d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6" />
               </svg>
               Back to Dashboard
            </a>
            <button onclick="window.history.back()"
               class="inline-flex items-center px-6 py-3 bg-gray-200 text-gray-800 font-semibold rounded-lg shadow-md hover:bg-gray-300 transition duration-300">
               <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
               </svg>
               Go Back
            </button>
         </div>

         <!-- Additional Info -->
         <div class="mt-8 text-sm text-gray-500">
            <p>If you believe this is an error, please contact the system administrator.</p>
         </div>
      </div>
   </div>

   <script>
      // Simple countdown timer
      let timeLeft = 60;
      const timerElement = document.getElementById('timer');

      const countdown = setInterval(() => {
         timeLeft--;
         if (timerElement) {
            timerElement.textContent = timeLeft;
         }

         if (timeLeft <= 0) {
            clearInterval(countdown);
            // Optionally auto-refresh or enable retry button
            if (document.getElementById('countdown')) {
               document.getElementById('countdown').innerHTML =
                  '<span class="text-green-600">You can try again now!</span>';
            }
         }
      }, 1000);
   </script>
</x-guest-layout>
