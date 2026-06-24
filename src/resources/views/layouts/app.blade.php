<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Dashboard')</title>

    <link rel="stylesheet" href="{{ asset('assets/css/app.css') }}" data-turbo-track="reload">
    <link rel="stylesheet" href="{{ asset('assets/css/components/navbar.css') }}" data-turbo-track="reload">
    <link rel="stylesheet" href="{{ asset('assets/css/components/sidebar.css') }}" data-turbo-track="reload">
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined" />
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600&display=swap" rel="stylesheet" />

    @stack('styles')
</head>
<body class="dashboard-body">

    <x-spinner />
    <x-alert/>

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

    {{-- Global loader helpers — defined before page scripts --}}
    <script>
        function showLoader() {
            const el = document.getElementById('loader');
            if (el) el.style.display = 'flex';
        }

        function hideLoader() {
            const el = document.getElementById('loader');
            if (el) el.style.display = 'none';
        }

        function submitWithLoader(form) {
            showLoader();
            setTimeout(() => form.submit(), 50);
        }

        // Turbo-aware: show loader on every navigation & form submit
        document.addEventListener('turbo:click',        showLoader);
        document.addEventListener('turbo:submit-start', showLoader);

        // Hide loader when Turbo finishes loading
        document.addEventListener('turbo:load',   hideLoader);
        document.addEventListener('turbo:render', hideLoader);
    </script>

    @stack('scripts')

    <script>
        document.addEventListener('DOMContentLoaded', function () {

            /* ── Dropdown system ── */
            const dropdowns = [];

            function registerDropdown(toggleEl, panelEl) {
                if (!toggleEl || !panelEl) return;

                dropdowns.push({ toggle: toggleEl, panel: panelEl });

                toggleEl.addEventListener('click', function (e) {
                    e.stopPropagation();

                    const isOpen = panelEl.classList.contains('is-open');
                    closeAllDropdowns();

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

            document.addEventListener('click', closeAllDropdowns);

            document.querySelectorAll('.navbar-dropdown, .nav-pill-dropdown').forEach(function (panel) {
                panel.addEventListener('click', e => e.stopPropagation());
            });

            document.addEventListener('keydown', function (e) {
                if (e.key === 'Escape') closeAllDropdowns();
            });

            registerDropdown(
                document.getElementById('userDropdownToggle'),
                document.getElementById('userDropdown')
            );
            registerDropdown(
                document.getElementById('notifBtn'),
                document.getElementById('notifDropdown')
            );
            registerDropdown(
                document.getElementById('langBtn'),
                document.getElementById('langDropdown')
            );

            /* ── Language switcher ── */
            const langBtn = document.getElementById('langBtn');

            document.querySelectorAll('.pill-dropdown-item[data-lang]').forEach(function (btn) {
                btn.addEventListener('click', function () {
                    const lang = this.getAttribute('data-lang');

                    document.querySelectorAll('.pill-dropdown-item[data-lang]').forEach(b => b.classList.remove('active'));
                    this.classList.add('active');

                    const selectedImg = this.querySelector('img');
                    const btnImg      = langBtn?.querySelector('img');
                    if (selectedImg && btnImg) {
                        btnImg.src = selectedImg.src;
                        btnImg.alt = selectedImg.alt;
                    }

                    closeAllDropdowns();
                    window.location.href = '/language/' + lang;
                });
            });

            /* ── Fullscreen toggle ── */
            const fsBtn = document.getElementById('fsBtn');

            if (fsBtn) {
                fsBtn.addEventListener('click', function () {
                    const icon = this.querySelector('.material-symbols-outlined');

                    if (!document.fullscreenElement) {
                        document.documentElement.requestFullscreen()
                            .then(() => { if (icon) icon.textContent = 'fullscreen_exit'; })
                            .catch(err  => console.warn('Fullscreen error:', err));
                    } else {
                        document.exitFullscreen()
                            .then(() => { if (icon) icon.textContent = 'fullscreen'; });
                    }
                });

                document.addEventListener('fullscreenchange', function () {
                    const icon = fsBtn.querySelector('.material-symbols-outlined');
                    if (icon) icon.textContent = document.fullscreenElement ? 'fullscreen_exit' : 'fullscreen';
                });
            }

            /* ── Sidebar toggle ── */
            const sidebarToggle = document.getElementById('sidebarToggle');
            if (sidebarToggle) {
                sidebarToggle.addEventListener('click', () => document.body.classList.toggle('sidebar-collapsed'));
            }

        });

        /* ── Lucide icons on Turbo navigation ── */
        document.addEventListener('turbo:load', function () {
            if (window.lucide) lucide.createIcons();
        });
    </script>

</body>
</html>
