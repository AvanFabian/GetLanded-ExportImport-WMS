<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
   <meta charset="utf-8">
   <meta name="viewport" content="width=device-width, initial-scale=1">
   <title>Privacy Policy - {{ config('app.name', 'GetLanded') }}</title>
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
               <a href="{{ route('terms') }}" class="text-gray-600 hover:text-gray-900">Terms of Service</a>
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
         <h1 class="text-3xl font-bold text-gray-900 mb-2">Privacy Policy</h1>
         <p class="text-gray-500">Last updated: February 1, 2026 | Effective for GetLanded Users</p>

         <div class="prose prose-gray max-w-none">
            <h2>1. Introduction</h2>
            <p>{{ config('app.name', 'GetLanded') }} ("we", "our", or "us") is committed to protecting your privacy. This Privacy Policy explains how we collect, use, disclose, and safeguard your information when you use our warehouse management system.</p>

            <h2>2. Information We Collect</h2>
            <h3>2.1 Personal Information</h3>
            <p>We may collect the following personal information:</p>
            <ul>
               <li>Name and email address</li>
               <li>Company/organization name</li>
               <li>Phone number</li>
               <li>Billing and payment information</li>
               <li>Account credentials</li>
            </ul>

            <h3>2.2 Business Data</h3>
            <p>Through your use of the service, you may enter:</p>
            <ul>
               <li>Inventory and product information</li>
               <li>Customer and supplier data</li>
               <li>Sales and purchase order data</li>
               <li>Warehouse and location information</li>
               <li>Financial and transaction records</li>
            </ul>

            <h3>2.3 Automatically Collected Information</h3>
            <p>When you access our service, we automatically collect:</p>
            <ul>
               <li>IP address and device information</li>
               <li>Browser type and version</li>
               <li>Access times and dates</li>
               <li>Pages viewed and actions taken</li>
               <li>Error logs and performance data</li>
            </ul>

            <h2>3. How We Use Your Information</h2>
            <p>We use collected information to:</p>
            <ul>
               <li>Provide, maintain, and improve our service</li>
               <li>Process transactions and manage your account</li>
               <li>Send service-related communications</li>
               <li>Provide customer support</li>
               <li>Analyze usage patterns to improve user experience</li>
               <li>Detect and prevent fraud or security incidents</li>
               <li>Comply with legal obligations</li>
            </ul>

            <h2>4. Data Sharing and Disclosure</h2>
            <p>We do not sell your personal information. We may share data with:</p>
            <ul>
               <li><strong>Service Providers:</strong> Third-party vendors who assist in operating our service (hosting, payment processing, analytics)</li>
               <li><strong>Legal Requirements:</strong> When required by law, court order, or government request</li>
               <li><strong>Business Transfers:</strong> In connection with mergers, acquisitions, or asset sales</li>
               <li><strong>With Your Consent:</strong> When you have given explicit permission</li>
            </ul>

            <h2>5. Data Security</h2>
            <p>We implement industry-standard security measures including:</p>
            <ul>
               <li>SSL/TLS encryption for data in transit</li>
               <li>Encryption of sensitive data at rest</li>
               <li>Regular security audits and vulnerability assessments</li>
               <li>Access controls and authentication mechanisms</li>
               <li>Employee training on data protection</li>
            </ul>

            <h2>6. Data Retention</h2>
            <p>We retain your data for as long as your account is active or as needed to provide services. After account termination:</p>
            <ul>
               <li>Personal data is retained for 30 days for recovery purposes</li>
               <li>Business data can be exported before deletion</li>
               <li>Some data may be retained for legal compliance</li>
            </ul>

            <h2>7. Your Rights</h2>
            <p>You have the right to:</p>
            <ul>
               <li><strong>Access:</strong> Request a copy of your personal data</li>
               <li><strong>Correction:</strong> Request correction of inaccurate data</li>
               <li><strong>Deletion:</strong> Request deletion of your data</li>
               <li><strong>Export:</strong> Receive your data in a portable format</li>
               <li><strong>Objection:</strong> Object to certain data processing</li>
               <li><strong>Restriction:</strong> Request limited processing of your data</li>
            </ul>

            <h2>8. Cookies and Tracking</h2>
            <p>We use cookies and similar technologies to:</p>
            <ul>
               <li>Maintain your session and preferences</li>
               <li>Analyze traffic and usage patterns</li>
               <li>Provide security features</li>
            </ul>
            <p>You can control cookie settings through your browser preferences.</p>

            <h2>9. Children's Privacy</h2>
            <p>Our service is not intended for users under 18 years of age. We do not knowingly collect information from children.</p>

            <h2>10. International Data Transfers</h2>
            <p>Your data may be processed on servers located outside your country. We ensure appropriate safeguards are in place for international data transfers.</p>

            <h2>11. Changes to This Policy</h2>
            <p>We may update this Privacy Policy periodically. Material changes will be communicated via email or through the service. The "Last updated" date indicates the most recent revision.</p>

            <h2>12. Contact Us</h2>
            <p>For privacy-related questions or to exercise your rights, contact us at:</p>
            <ul>
               <li>Email: privacy@avandigital.id</li>
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
