@props([
    'title' => null,
    'description' => null,
    'image' => null,
    'url' => null,
    'type' => 'website'
])

@php
    $finalTitle = $title ? $title . ' - ' . config('app.name', 'AgroWMS') : config('app.name', 'AgroWMS') . ' - ' . __('landing.hero_badge');
    $finalDescription = $description ?? __('landing.hero_sub');
    $finalImage = $image ?? asset('images/og-image.jpg'); // Fallback image needed
    $finalUrl = $url ?? url()->current();
@endphp

{{-- Primary Meta Tags --}}
<title>{{ $finalTitle }}</title>
<meta name="title" content="{{ $finalTitle }}">
<meta name="description" content="{{ $finalDescription }}">

{{-- Open Graph / Facebook --}}
<meta property="og:type" content="{{ $type }}">
<meta property="og:url" content="{{ $finalUrl }}">
<meta property="og:title" content="{{ $finalTitle }}">
<meta property="og:description" content="{{ $finalDescription }}">
<meta property="og:image" content="{{ $finalImage }}">

{{-- Twitter --}}
<meta property="twitter:card" content="summary_large_image">
<meta property="twitter:url" content="{{ $finalUrl }}">
<meta property="twitter:title" content="{{ $finalTitle }}">
<meta property="twitter:description" content="{{ $finalDescription }}">
<meta property="twitter:image" content="{{ $finalImage }}">
