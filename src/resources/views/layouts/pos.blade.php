<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>POS — @yield('title', 'Cashier')</title>
    <link rel="stylesheet" href="{{ asset('assets/css/app.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/css/pos.css') }}">
    @stack('styles')
</head>
<body class="pos-layout">
    @include('components.pos-header')
    @yield('content')
    <script src="{{ asset('assets/js/app.js') }}"></script>
    <script src="{{ asset('assets/js/pos.js') }}"></script>
    @stack('scripts')
</body>
</html>
