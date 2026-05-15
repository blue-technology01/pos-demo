<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>@yield('title', 'Authentication') — POS System</title>

    <link rel="icon" type="image/png" href="{{ asset('assets/images/logo.png') }}">
    <link href="https://fonts.googleapis.com/css2?family=DM+Serif+Display:ital@0;1&family=DM+Sans:wght@300;400;500;600&display=swap" rel="stylesheet"/>
    <link rel="stylesheet" href="https://code.jquery.com/ui/1.14.2/themes/base/jquery-ui.css">
    <link rel="stylesheet" href="{{ asset('assets/css/auth.css') }}">

    @stack('styles')
</head>
<body>
    @include('components.alert')

    <div class="panel-image">
        <div class="panel-logo">
            <img src="{{ asset('assets/images/logo.png') }}" alt="POS Logo">
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

    {{-- Scripts at bottom for faster page load --}}
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <script src="https://code.jquery.com/ui/1.14.2/jquery-ui.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="{{ asset('assets/js/app.js') }}"></script>
    @stack('scripts')
</body>
</html>
