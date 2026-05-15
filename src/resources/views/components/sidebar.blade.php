@php
    $user = auth()->user();
@endphp

<nav class="sidebar" id="sidebar">
    <div class="sidebar-menu">
         @role('admin|cashier')
        {{-- Dashboard --}}
        <a href="{{ route('admin.dashboard') }}"
           class="menu-item {{ request()->routeIs('admin.dashboard') ? 'active' : '' }}"
           data-tooltip="Dashboard">
            <i class="ti ti-dashboard menu-icon"></i>
            <span class="menu-label">Dashboard</span>
        </a>
        {{-- Sales --}}
            <div class="menu-group {{ request()->routeIs('admin.sales*') ? 'open' : '' }}">
                <div class="menu-item has-sub" data-tooltip="Sales">
                    <i class="ti ti-chart-bar menu-icon"></i>
                    <span class="menu-label">Sales</span>
                    <i class="ti ti-chevron-right menu-arrow"></i>
                </div>
                <div class="sub-menu">
                    <a href="#" class="sub-item {{ request()->routeIs('admin.sales.index') ? 'active' : '' }}">
                        <i class="ti ti-point"></i> All sales
                    </a>
                    <a href="#" class="sub-item {{ request()->routeIs('admin.sales.reports') ? 'active' : '' }}">
                        <i class="ti ti-point"></i> Sales report
                    </a>
                </div>
            </div>
        @endrole

        @role('admin')
        {{-- Products --}}
        <div class="menu-group {{ request()->routeIs('admin.products*') ? 'open' : '' }}">
            <div class="menu-item has-sub" data-tooltip="Products">
                <i class="ti ti-box menu-icon"></i>
                <span class="menu-label">Products</span>
                <i class="ti ti-chevron-right menu-arrow"></i>
            </div>
            <div class="sub-menu">
                <a href="#" class="sub-item {{ request()->routeIs('admin.products.index') ? 'active' : '' }}">
                    <i class="ti ti-point"></i> All products
                </a>
                <a href="#" class="sub-item {{ request()->routeIs('admin.products.categories') ? 'active' : '' }}">
                    <i class="ti ti-point"></i> Categories
                </a>
            </div>
        </div>

        {{-- Customers --}}
        <a href="#"
           class="menu-item {{ request()->routeIs('admin.customers*') ? 'active' : '' }}"
           data-tooltip="Customers">
            <i class="ti ti-users menu-icon"></i>
            <span class="menu-label">Customers</span>
        </a>

        {{-- Reports --}}
        <a href="#"
           class="menu-item {{ request()->routeIs('admin.reports*') ? 'active' : '' }}"
           data-tooltip="Reports">
            <i class="ti ti-report-analytics menu-icon"></i>
            <span class="menu-label">Reports</span>
        </a>

        {{-- Users --}}
        <div class="menu-group {{ request()->routeIs('admin.users*') ? 'open' : '' }}">
            <div class="menu-item has-sub" data-tooltip="Users">
                <i class="ti ti-user-cog menu-icon"></i>
                <span class="menu-label">Users</span>
                <i class="ti ti-chevron-right menu-arrow"></i>
            </div>
            <div class="sub-menu">
                <a href="{{ route('admin.users') }}" class="sub-item {{ request()->routeIs('admin.users*') ? 'active' : '' }}">
                    <i class="ti ti-point"></i> All users
                </a>
                <a href="#" class="sub-item">
                    <i class="ti ti-point"></i> Roles
                </a>
            </div>
        </div>

        <div class="menu-divider"></div>

        {{-- Settings --}}
        <a href="#" class="menu-item" data-tooltip="Settings">
            <i class="ti ti-settings menu-icon"></i>
            <span class="menu-label">Settings</span>
        </a>
        @endrole
    </div>
</nav>

@push('scripts')
    <script>
    // public/assets/js/app.js
    $(document).ready(function () {

        // ── User dropdown ──
        $('#userDropdownToggle').on('click', function (e) {
            e.stopPropagation();
            $('#userDropdown').toggleClass('open');
        });

        $(document).on('click', function () {
            $('#userDropdown').removeClass('open');
        });

        // ── Sidebar collapse ──
        $('#sidebarToggle').on('click', function () {
            $('#sidebar').toggleClass('collapsed');
            localStorage.setItem('sidebar_collapsed', $('#sidebar').hasClass('collapsed'));
        });

        if (localStorage.getItem('sidebar_collapsed') === 'true') {
            $('#sidebar').addClass('collapsed');
        }

        // ── Submenu toggle ──
        $('.menu-item.has-sub').on('click', function () {
            var $group = $(this).closest('.menu-group');
            var isOpen = $group.hasClass('open');

            $('.menu-group.open').not($group).removeClass('open')
                .find('.sub-menu').slideUp(200);

            if (isOpen) {
                $group.removeClass('open');
                $group.find('.sub-menu').slideUp(200);
            } else {
                $group.addClass('open');
                $group.find('.sub-menu').slideDown(200);
            }
        });
        // ── Restore open submenus on load ──
        $('.menu-group.open').find('.sub-menu').show();
    });
    </script>
@endpush
