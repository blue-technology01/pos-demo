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
    <x-loading />

    <div class="panel-image">
        <div class="panel-logo">
            <img src="{{ asset('assets/images/logo.png') }}" alt="POS Logo" width="120" height="40">
        </div>

        <div class="slider-panel" id="auth-slider-panel">
            <div class="slider-track" id="auth-slider-track">
                <div class="slide" style="background-image: url('{{ asset('assets/images/slider1.png') }}')"></div>
                <div class="slide" style="background-image: url('{{ asset('assets/images/slider3.jpg') }}')"></div>
                <div class="slide" style="background-image: url('{{ asset('assets/images/slider3.webp') }}')"></div>
                {{-- Clone of slide 1, used only so the scroll can loop seamlessly back to the start --}}
                <div class="slide" aria-hidden="true" style="background-image: url('{{ asset('assets/images/slider1.png') }}')"></div>
            </div>
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

    {{-- <script src="{{ asset('assets/js/app.js') }}" defer></script> --}}

    @stack('scripts')

    <script>
        // ── Auth page slider (smooth loop, same pattern as customer display) ──
        document.addEventListener('DOMContentLoaded', function () {

            const INTERVAL = 4000; // declared locally — no dependency on app.js

            const track = document.getElementById('auth-slider-track');
            const panel = document.querySelector('.panel-image');

            if (!track || !panel) return;

            // Only count REAL slides (exclude the clone used for the loop)
            const realSlideCount = track.children.length - 1;

            const contents = [
                { title: 'Fast Point of Sale',       sub: 'Quick checkout, table management, and real-time orders.' },
                { title: 'Smart Order Management',   sub: 'Track every table and order in real time.' },
                { title: 'Multiple Payment Options', sub: 'Accept cash, card, QR, and more seamlessly.' },
            ];

            let index = 0;
            let timer;

            // Inject progress bar
            const bar = document.createElement('div');
            bar.className = 'slide-progress';
            panel.appendChild(bar);

            function goToSlide(nextIndex) {
                index = nextIndex;
                track.style.transform = `translateX(-${index * 100}%)`;

                const content = contents[index % realSlideCount];
                const titleEl = document.getElementById('slide-title');
                const subEl   = document.getElementById('slide-sub');
                if (titleEl) titleEl.textContent = content.title;
                if (subEl)   subEl.textContent   = content.sub;

                // Once we've scrolled onto the cloned slide, snap back to slide 0
                // invisibly once the animation finishes.
                if (index === realSlideCount) {
                    track.addEventListener('transitionend', function reset() {
                        track.removeEventListener('transitionend', reset);
                        track.style.transition = 'none';
                        index = 0;
                        track.style.transform = 'translateX(0%)';
                        void track.offsetHeight; // force reflow
                        track.style.transition = '';
                    });
                }
            }

            function startProgress() {
                bar.style.transition = 'none';
                bar.style.width = '0%';
                requestAnimationFrame(() => requestAnimationFrame(() => {
                    bar.style.transition = `width ${INTERVAL}ms linear`;
                    bar.style.width = '100%';
                }));
            }

            function startAuto() {
                clearInterval(timer);
                startProgress();
                timer = setInterval(() => {
                    goToSlide(index + 1);
                    startProgress();
                }, INTERVAL);
            }
            startAuto();
        });
    </script>
</body>
</html>
