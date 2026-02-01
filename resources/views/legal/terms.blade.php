<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
   <meta charset="utf-8">
   <meta name="viewport" content="width=device-width, initial-scale=1">
   <title>Terms of Service - {{ config('app.name', 'GetLanded') }}</title>
   <link rel="preconnect" href="https://fonts.bunny.net">
   <link href="https://fonts.bunny.net/css?family=figtree:400,500,600,700&display=swap" rel="stylesheet" />
   @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-gray-50 antialiased">
   {{-- Header --}}
   <header class="bg-white border-b border-gray-200 sticky top-0 z-50">
      <div class="max-w-4xl mx-auto px-4 sm:px-6 py-4">
         <div class="flex items-center justify-between">
            <a href="{{ url('/') }}" class="flex items-center gap-2">
               <div class="w-8 h-8 bg-primary rounded-lg flex items-center justify-center">
                  <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                     <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 8h14M5 8a2 2 0 110-4h14a2 2 0 110 4M5 8v10a2 2 0 002 2h10a2 2 0 002-2V8m-9 4h4"/>
                  </svg>
               </div>
               <span class="font-bold text-gray-900">{{ config('app.name', 'GetLanded') }}</span>
            </a>
            <nav class="flex items-center gap-4 text-sm">
               <a href="{{ route('privacy') }}" class="text-gray-600 hover:text-gray-900">Privacy Policy</a>
               @if (Route::has('login'))
                  <a href="{{ route('login') }}" class="text-primary font-medium">Login</a>
               @endif
            </nav>
         </div>
      </div>
   </header>

   {{-- Content --}}
   <main class="max-w-4xl mx-auto px-4 sm:px-6 py-12">
      <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-8 sm:p-12">
         <h1 class="text-3xl font-bold text-gray-900 mb-2">Terms of Service</h1>
         <p class="text-gray-500">Last updated: February 1, 2026 | Agreement for GetLanded Platform</p>

         <div class="prose prose-gray max-w-none">
            <h2>1. Acceptance of Terms</h2>
            <p>By accessing and using {{ config('app.name', 'GetLanded') }} ("The Service"), you accept and agree to be bound by these Terms of Service. If you do not agree to these terms, please do not use our service.</p>

            <h2>2. Description of Service</h2>
            <p>{{ config('app.name', 'GetLanded') }} is a warehouse and inventory management system that provides:</p>
            <ul>
               <li>Real-time inventory tracking and management</li>
               <li>Multi-warehouse support</li>
               <li>Batch and expiry date management</li>
               <li>Sales order processing and invoicing</li>
               <li>Stock transfers and stock takes</li>
               <li>Reporting and analytics</li>
            </ul>

            <h2>3. User Accounts</h2>
            <p>To use our service, you must:</p>
            <ul>
               <li>Create an account with accurate and complete information</li>
               <li>Maintain the security of your account credentials</li>
               <li>Accept responsibility for all activities under your account</li>
               <li>Notify us immediately of any unauthorized use</li>
            </ul>

            <h2>4. User Obligations</h2>
            <p>You agree to:</p>
            <ul>
               <li>Use the service only for lawful purposes</li>
               <li>Not attempt to gain unauthorized access to any part of the service</li>
               <li>Not interfere with or disrupt the service</li>
               <li>Not use the service to transmit harmful or malicious content</li>
               <li>Comply with all applicable laws and regulations</li>
            </ul>

            <h2>5. Data Ownership</h2>
            <p>You retain ownership of all data you enter into the system. We do not claim ownership of your business data. You may export your data at any time using the provided tools.</p>

            <h2>6. Service Availability</h2>
            <p>We strive to maintain 99.9% uptime but do not guarantee uninterrupted service. We may perform scheduled maintenance with prior notice. We are not liable for any loss resulting from service interruptions.</p>

            <h2>7. Payment Terms</h2>
            <p>For paid subscription plans:</p>
            <ul>
               <li>Payments are billed in advance on a monthly or annual basis</li>
               <li>Subscriptions automatically renew unless cancelled</li>
               <li>Refunds are provided in accordance with our refund policy</li>
               <li>We reserve the right to modify pricing with 30 days notice</li>
            </ul>

            <h2>8. Limitation of Liability</h2>
            <p>To the maximum extent permitted by law, {{ config('app.name', 'GetLanded') }} and its affiliates shall not be liable for any indirect, incidental, special, consequential, or punitive damages, including but not limited to loss of profits, data, or business opportunities.</p>

            <h2>9. Indemnification</h2>
            <p>You agree to indemnify and hold harmless {{ config('app.name', 'GetLanded') }}, its officers, directors, employees, and agents from any claims, damages, losses, or expenses arising from your use of the service or violation of these terms.</p>

            <h2>10. Termination</h2>
            <p>We may terminate or suspend your account at any time for violation of these terms. Upon termination, your right to use the service ceases immediately. You may export your data within 30 days of account termination.</p>

            <h2>11. Modifications to Terms</h2>
            <p>We reserve the right to modify these terms at any time. Material changes will be communicated via email or through the service. Continued use after changes constitutes acceptance of the new terms.</p>

            <h2>12. Governing Law</h2>
            <p>These terms are governed by the laws of the Republic of Indonesia. Any disputes shall be resolved in the courts of Jakarta, Indonesia.</p>

            <h2>13. Contact Information</h2>
            <p>For questions about these Terms of Service, please contact us at:</p>
            <ul>
               <li>Email: legal@avandigital.id</li>
               <li>Website: avandigital.id</li>
            </ul>
         </div>
      </div>
   </main>

   {{-- Footer --}}
   <footer class="border-t border-gray-200 bg-white py-8 mt-12">
      <div class="max-w-4xl mx-auto px-4 sm:px-6 text-center text-sm text-gray-500">
         <p>© {{ date('Y') }} {{ config('app.name', 'GetLanded') }} by avandigital.id. All rights reserved.</p>
         <div class="mt-2 flex items-center justify-center gap-4">
            <a href="{{ route('terms') }}" class="text-gray-600 hover:text-gray-900">Terms of Service</a>
            <span class="text-gray-300">|</span>
            <a href="{{ route('privacy') }}" class="text-gray-600 hover:text-gray-900">Privacy Policy</a>
         </div>
      </div>
   </footer>
</body>
</html>
