<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Authentication') — POS System</title>

    <link rel="preload" as="image" href="{{ asset('assets/images/logo.png') }}">
    <link rel="icon" type="image/png" href="{{ asset('assets/images/logo.png') }}">

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
            <div class="slide active" style="background-image: url('{{ asset('assets/images/slider1.png') }}')"></div>
            <div class="slide" style="background-image: url('{{ asset('assets/images/slider2.jpg') }}')"></div>
            <div class="slide" style="background-image: url('{{ asset('assets/images/slider3.webp') }}')"></div>
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

    <script src="{{ asset('assets/js/app.js') }}" defer></script>

    <script>
    document.addEventListener('DOMContentLoaded', function () {

        const slides = document.querySelectorAll('.slide');
        if (!slides.length) return;

        const contents = [
            { title: 'Fast Point of Sale',       sub: 'Quick checkout, table management, and real-time orders.' },
            { title: 'Smart Order Management',   sub: 'Track every table and order in real time.' },
            { title: 'Multiple Payment Options', sub: 'Accept cash, card, QR, and more seamlessly.' },
        ];

        let current = 0;

        function goTo(index) {
            slides[current].classList.remove('active');
            current = (index + slides.length) % slides.length;
            slides[current].classList.add('active');

            const titleEl = document.getElementById('slide-title');
            const subEl   = document.getElementById('slide-sub');
            if (titleEl) titleEl.textContent = contents[current].title;
            if (subEl)   subEl.textContent   = contents[current].sub;
        }

        setInterval(() => goTo(current + 1), 8000);
    });
    </script>

    @stack('scripts')
</body>
</html>
