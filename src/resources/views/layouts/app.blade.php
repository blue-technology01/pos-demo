<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Dashboard')</title>

    <!-- Critical CSS only -->
    <link rel="stylesheet" href="{{ asset('assets/css/app.css') }}" data-turbo-track="reload">
    <link rel="stylesheet" href="{{ asset('assets/css/components/navbar.css') }}" data-turbo-track="reload">
    <link rel="stylesheet" href="{{ asset('assets/css/components/sidebar.css') }}" data-turbo-track="reload">
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined" />

    <!-- Page-specific styles -->
    @stack('styles')
</head>
<body class="dashboard-body">

    @include('components.navbar')

    <div class="dashboard-wrapper" id="dashboard-wrapper">
        @include('components.sidebar')
        <main class="main-content">
            <div class="content-body">
                @yield('content')
            </div>
        </main>
    </div>

    <!-- Core scripts (always needed) -->
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <script src="{{ asset('assets/js/app.js') }}"></script>

    <!-- Turbo (after jQuery) -->
    {{-- <script src="https://unpkg.com/@hotwired/turbo@8.0.4/dist/turbo.es2017-umd.js"></script> --}}

    @stack('scripts')

    <script>
        document.addEventListener('turbo:load', function () {
            // Reinitialize Lucide on every Turbo navigation
            if (window.lucide) lucide.createIcons();
        });
    </script>
</body>
</html>
