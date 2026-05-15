<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="POS System - Dashboard">

    <title>@yield('title', 'Dashboard')</title>

    {{-- Tabler Icons --}}
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@tabler/icons-webfont@latest/tabler-icons.min.css">

    {{-- jQuery UI --}}
    <link rel="stylesheet" href="https://code.jquery.com/ui/1.14.2/themes/base/jquery-ui.css">

    {{-- Main CSS --}}
    <link rel="stylesheet" href="{{ asset('assets/css/app.css') }}">
    @stack('styles')
    {{-- @stack('scripts') <!-- Define a stack for additional scripts --> --}}

</head>
<body class="dashboard-body">
    @include('components.navbar')
    <div class="dashboard-wrapper" id="dashboard-wrapper">
        @include('components.sidebar')
        <main class="main-content">
            @include('components.alert')
            <div class="content-body">
                @yield('content')
            </div>
        </main>
    </div>

    {{-- Scripts --}}
    <script src="https://code.jquery.com/jquery-3.7.1.js"></script>
    <script src="https://code.jquery.com/ui/1.14.2/jquery-ui.js"></script>
    <script src="{{ asset('assets/js/app.js') }}"></script>
    @stack('scripts')
</body>
</html>
