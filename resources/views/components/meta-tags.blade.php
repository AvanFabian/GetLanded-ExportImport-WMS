@props([
    'title' => null,
    'description' => null,
    'image' => null,
    'url' => null,
    'type' => 'website'
])

@php
    $finalTitle = $title ? $title . ' - ' . config('app.name', 'GetLanded') : config('app.name', 'GetLanded') . ' - ' . __('landing.hero_badge');
    $finalDescription = $description ?? __('landing.hero_sub');
    $finalImage = $image ?? asset('images/og-image.jpg'); // Fallback image needed
    $finalUrl = $url ?? url()->current();
@endphp

{{-- Primary Meta Tags --}}
<title>{{ $finalTitle }}</title>
<meta name="title" content="{{ $finalTitle }}">
<meta name="description" content="{{ $description ?? 'GetLanded - Real-time smart logistics and warehouse management system.' }}">
<meta name="author" content="Avan Digital">

{{-- Open Graph / Facebook --}}
<meta property="og:type" content="{{ $type }}">
<meta property="og:url" content="{{ $finalUrl }}">
<meta property="og:title" content="{{ $title ?? 'GetLanded' }}">
<meta property="og:description" content="{{ $description ?? 'GetLanded - Real-time smart logistics and warehouse management system.' }}">
<meta property="og:image" content="{{ $finalImage }}">

{{-- Twitter --}}
<meta property="twitter:card" content="summary_large_image">
<meta property="twitter:url" content="{{ $finalUrl }}">
<meta property="twitter:title" content="{{ $finalTitle }}">
<meta property="twitter:description" content="{{ $finalDescription }}">
<meta property="twitter:image" content="{{ $finalImage }}">
