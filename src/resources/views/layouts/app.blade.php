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
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600&display=swap" rel="stylesheet" />

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

    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <script src="{{ asset('assets/js/app.js') }}"></script>

    @stack('scripts')

    <script>
        document.addEventListener('DOMContentLoaded', function () {

            const dropdowns = [];

            function registerDropdown(toggleEl, panelEl) {
                if (!toggleEl || !panelEl) return;

                dropdowns.push({ toggle: toggleEl, panel: panelEl });

                toggleEl.addEventListener('click', function (e) {
                    e.stopPropagation();

                    const isOpen = panelEl.classList.contains('is-open');

                    // Close all other dropdowns first
                    closeAllDropdowns();

                    // Toggle this one
                    if (!isOpen) {
                        panelEl.classList.add('is-open');
                        toggleEl.setAttribute('aria-expanded', 'true');
                    }
                });
            }

            function closeAllDropdowns() {
                dropdowns.forEach(function (d) {
                    d.panel.classList.remove('is-open');
                    d.toggle.setAttribute('aria-expanded', 'false');
                });
            }

            // Close all dropdowns when clicking outside
            document.addEventListener('click', function () {
                closeAllDropdowns();
            });

            // Prevent clicks inside a dropdown panel from closing it
            document.querySelectorAll('.navbar-dropdown, .nav-pill-dropdown').forEach(function (panel) {
                panel.addEventListener('click', function (e) {
                    e.stopPropagation();
                });
            });

            // Close all dropdowns on Escape key
            document.addEventListener('keydown', function (e) {
                if (e.key === 'Escape') {
                    closeAllDropdowns();
                }
            });


            /* ============================================================
            PROFILE DROPDOWN
            ============================================================ */

            const userToggle = document.getElementById('userDropdownToggle');
            const userDropdown = document.getElementById('userDropdown');

            registerDropdown(userToggle, userDropdown);


            /* ============================================================
            NOTIFICATION DROPDOWN
            ============================================================ */

            const notifBtn = document.getElementById('notifBtn');
            const notifDropdown = document.getElementById('notifDropdown');

            registerDropdown(notifBtn, notifDropdown);


            /* ============================================================
            LANGUAGE DROPDOWN
            ============================================================ */

            const langBtn = document.getElementById('langBtn');
            const langDropdown = document.getElementById('langDropdown');

            registerDropdown(langBtn, langDropdown);

            // Language selection
            document.querySelectorAll('.pill-dropdown-item[data-lang]').forEach(function (btn) {
                btn.addEventListener('click', function () {
                    const lang = this.getAttribute('data-lang');

                    // Update active state
                    document.querySelectorAll('.pill-dropdown-item[data-lang]').forEach(function (b) {
                        b.classList.remove('active');
                    });
                    this.classList.add('active');

                    // Swap flag icon in the button
                    const selectedImg = this.querySelector('img');
                    const btnImg = langBtn.querySelector('img');
                    if (selectedImg && btnImg) {
                        btnImg.src = selectedImg.src;
                        btnImg.alt = selectedImg.alt;
                    }

                    closeAllDropdowns();

                    // Submit language change — adjust URL to your route
                    window.location.href = '/language/' + lang;
                });
            });


            /* ============================================================
            FULLSCREEN TOGGLE
            ============================================================ */

            const fsBtn = document.getElementById('fsBtn');

            if (fsBtn) {
                fsBtn.addEventListener('click', function () {
                    const icon = this.querySelector('.material-symbols-outlined');

                    if (!document.fullscreenElement) {
                        document.documentElement.requestFullscreen().then(function () {
                            if (icon) icon.textContent = 'fullscreen_exit';
                        }).catch(function (err) {
                            console.warn('Fullscreen request failed:', err);
                        });
                    } else {
                        document.exitFullscreen().then(function () {
                            if (icon) icon.textContent = 'fullscreen';
                        });
                    }
                });

                // Sync icon if user presses Escape to exit fullscreen
                document.addEventListener('fullscreenchange', function () {
                    const icon = fsBtn.querySelector('.material-symbols-outlined');
                    if (icon) {
                        icon.textContent = document.fullscreenElement ? 'fullscreen_exit' : 'fullscreen';
                    }
                });
            }


            /* ============================================================
            SIDEBAR TOGGLE
            ============================================================ */

            const sidebarToggle = document.getElementById('sidebarToggle');

            if (sidebarToggle) {
                sidebarToggle.addEventListener('click', function () {
                    document.body.classList.toggle('sidebar-collapsed');
                });
            }

        });
        document.addEventListener('turbo:load', function () {
            // Reinitialize Lucide on every Turbo navigation
            if (window.lucide) lucide.createIcons();
        });
    </script>
</body>
</html>
