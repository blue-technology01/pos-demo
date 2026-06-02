<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Authentication') — POS System</title>

    {{-- Preload logo (used as favicon + panel image) --}}
    <link rel="preload" as="image" href="{{ asset('assets/images/logo.png') }}">
    <link rel="icon" type="image/png" href="{{ asset('assets/images/logo.png') }}">

    {{-- Preload slide background images --}}
    <link rel="preload" as="image" href="{{ asset('assets/images/slide-1.jpg') }}">
    <link rel="preload" as="image" href="{{ asset('assets/images/slide-2.jpg') }}">
    <link rel="preload" as="image" href="{{ asset('assets/images/slide-3.jpg') }}">

    {{-- Fonts: use preconnect + optional self-host for offline/LAN use --}}
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=DM+Serif+Display:ital@0;1&family=DM+Sans:ital,opsz,wght@0,9..40,300;0,9..40,400;0,9..40,500;0,9..40,600;1,9..40,400&display=swap" rel="stylesheet">

    <link rel="stylesheet" href="{{ asset('assets/css/auth.css') }}">

    @stack('styles')
</head>
<body>

    <div class="panel-image">
        <div class="panel-logo">
            <img src="{{ asset('assets/images/logo.png') }}" alt="POS Logo" width="120" height="40">
        </div>
        <div class="slides">
            <div class="slide slide-1 active"></div>
            <div class="slide slide-2"></div>
            <div class="slide slide-3"></div>
        </div>
        <div class="panel-copy">
            <h2 id="slide-title">Fast Point of Sale</h2>
            <p id="slide-sub">Quick checkout, table management, and real-time orders.</p>
            <ul class="features">
                <li>Instant billing</li>
                <li>Table &amp; order management</li>
                <li>Multiple payment options</li>
            </ul>
        </div>
    </div>

    @yield('content')

    <script src="{{ asset('assets/js/app.js') }}" ></script>

    @stack('scripts')
</body>
</html>
