<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'POS - Point of Sale')</title>

    <!-- CSS -->
    <link rel="stylesheet" href="https://code.jquery.com/ui/1.14.2/themes/base/jquery-ui.css">
    <link rel="stylesheet" href="{{ asset('assets/css/pos.css') }}">
    @stack('styles')
</head>
<body class="pos-body">

    <div class="pos-wrapper">
        @yield('content')
    </div>

    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>

    <!-- Other libraries -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js" defer></script>
    <script src="https://code.jquery.com/ui/1.14.2/jquery-ui.min.js" defer></script>

    @stack('scripts')

    <!-- Your POS Script(s) -->
    <script src="{{ asset('assets/js/pos/cash-register-pos.js') }}"></script>

    <script>
        document.addEventListener('DOMContentLoaded', () => {
            if (typeof lucide !== 'undefined') {
                lucide.createIcons();
            }
        });
    </script>
</body>
