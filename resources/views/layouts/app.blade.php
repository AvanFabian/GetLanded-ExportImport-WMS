<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
   <meta charset="utf-8">
   <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no">
   <meta name="csrf-token" content="{{ csrf_token() }}">

   {{-- PWA Meta Tags --}}
   <meta name="theme-color" content="#064E3B">
   <meta name="mobile-web-app-capable" content="yes">
   <meta name="apple-mobile-web-app-capable" content="yes">
   <meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">
   <meta name="apple-mobile-web-app-title" content="AgroWMS">
   <meta name="application-name" content="AgroWMS">
   <meta name="msapplication-TileColor" content="#064E3B">
   <meta name="msapplication-config" content="/browserconfig.xml">

   {{-- PWA Manifest --}}
   <link rel="manifest" href="/manifest.json">

   {{-- iOS Icons --}}
   <link rel="apple-touch-icon" href="/icons/icon-152x152.png">
   <link rel="apple-touch-icon" sizes="180x180" href="/icons/icon-192x192.png">

   {{-- Favicon --}}
   <link rel="icon" type="image/png" sizes="32x32" href="/icons/icon-72x72.png">
   <link rel="icon" type="image/png" sizes="16x16" href="/icons/icon-72x72.png">

   <title>@yield('title', config('app.name', 'Warehouse Inventory'))</title>

   <!-- Fonts -->
   <link rel="preconnect" href="https://fonts.bunny.net">
   <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />

   <!-- Scripts -->
   @vite(['resources/css/app.css', 'resources/js/app.js'])

   {{-- Service Worker Registration --}}
   <script>
      if ('serviceWorker' in navigator) {
         window.addEventListener('load', () => {
            navigator.serviceWorker.register('/sw.js')
               .then((registration) => {
                  console.log('SW registered:', registration.scope);
               })
               .catch((error) => {
                  console.log('SW registration failed:', error);
               });
         });
      }
   </script>
</head>

<body class="font-sans antialiased bg-gray-100">
   <div class="min-h-screen flex flex-col">
      <!-- Header -->
      @include('layouts.header')

      <div class="flex flex-1">
         <!-- Sidebar -->
         @include('layouts.sidebar')

         <!-- Main Content -->
         <main class="flex-1 overflow-y-auto">
            <!-- Flash Messages -->
            @if (session('success'))
               <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative m-4"
                  role="alert">
                  <span class="block sm:inline">{{ session('success') }}</span>
               </div>
            @endif

            @if (session('error'))
               <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative m-4" role="alert">
                  <span class="block sm:inline">{{ session('error') }}</span>
               </div>
            @endif

            @yield('content')
         </main>
      </div>

    <!-- Footer -->
    <footer class="bg-white border-t border-gray-200 py-4">
       <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <p class="text-center text-sm text-gray-500">
           © {{ date('Y') }} Warehouse Inventory Management System by avandigital.id
        </p>
       </div>
    </footer>
   </div>
</body>

</html>
