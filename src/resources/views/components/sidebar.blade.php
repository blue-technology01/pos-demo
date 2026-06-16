@php
    $user = auth()->user();
@endphp
@push('style')
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined" />
    <style>
        .menu-icon,
        .sidebar.collapsed .menu-icon,
        .sidebar.hover-expand .menu-icon,
        .menu-arrow svg {
            width: 18px !important;
            height: 18px !important;
            min-width: 18px !important;
            min-height: 18px !important;
            stroke: currentColor;
            stroke-width: 2px;
            display: inline-block;
            vertical-align: middle;
        }
    </style>
@endpush
<nav class="sidebar" id="sidebar">
    <div class="sidebar-menu">

        @role('admin|cashier')
        <a href="{{ route('admin.dashboard') }}"
           class="menu-item {{ request()->routeIs('admin.dashboard') ? 'active' : '' }}"
           data-tooltip="Dashboard">
            <svg class="menu-icon" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round">
                <rect x="3" y="3" width="7" height="9" />
                <rect x="14" y="3" width="7" height="5" />
                <rect x="14" y="12" width="7" height="9" />
                <rect x="3" y="16" width="7" height="5" />
            </svg>
            <span class="menu-label">Dashboard</span>
        </a>

        <div class="menu-group {{ request()->routeIs('admin.sales.*', 'admin.shift') ? 'open' : '' }}">
            <div class="menu-item has-sub" data-tooltip="Sales">
                <svg class="menu-icon" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round">
                    <line x1="18" y1="20" x2="18" y2="10"></line>
                    <line x1="12" y1="20" x2="12" y2="4"></line>
                    <line x1="6" y1="20" x2="6" y2="14"></line>
                </svg>
                <span class="menu-label">Sales</span>
                <i class="ti ti-chevron-right menu-arrow"></i>
            </div>
            <div class="sub-menu">
                <a href="{{ route('admin.sales.index') }}"
                   class="sub-item {{ request()->routeIs('admin.sales.*') ? 'active' : '' }}">
                    <i class="ti ti-point"></i> Sale History
                </a>
                <a href="{{ route('admin.shift') }}"
                   class="sub-item {{ request()->routeIs('admin.shift') ? 'active' : '' }}">
                    <i class="ti ti-point"></i> Cash Register
                </a>
            </div>
        </div>

        @endrole

        @role('admin')
        <div class="menu-group {{ request()->routeIs('admin.products.*') ? 'open' : '' }}">

            <div class="menu-item has-sub" data-tooltip="Products">
                <svg class="menu-icon" xmlns="http://www.w3.org/2000/svg"
                    viewBox="0 0 24 24" fill="none" stroke="currentColor"
                    stroke-linecap="round" stroke-linejoin="round">
                    <path d="M21 16V8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16z"></path>
                    <polyline points="3.27 6.96 12 12.01 20.73 6.96"></polyline>
                    <line x1="12" y1="22.08" x2="12" y2="12"></line>
                </svg>

                <span class="menu-label">Products</span>
                <i class="ti ti-chevron-right menu-arrow"></i>
            </div>

            <div class="sub-menu">

                {{-- Products --}}
                <a href="{{ route('admin.products.index') }}"
                class="sub-item {{ request()->routeIs('admin.products.index', 'admin.products.create', 'admin.products.edit') ? 'active' : '' }}">
                    <i class="ti ti-point"></i>
                    All Products
                </a>

                {{-- Product UOM --}}
                <a href="{{ route('admin.product-uom.index') }}"
                    class="sub-item {{ request()->routeIs('admin.product-uom.*') ? 'active' : '' }}">
                        <i class="ti ti-point"></i>
                        Product UOM
                </a>

                {{-- Category --}}
                <a href="{{ route('admin.category') }}"
                class="sub-item {{ request()->routeIs('admin.category') ? 'active' : '' }}">
                    <i class="ti ti-point"></i>
                    Categories
                </a>

                {{-- Unit --}}
                <a href="{{ route('admin.unit') }}"
                class="sub-item {{ request()->routeIs('admin.unit') ? 'active' : '' }}">
                    <i class="ti ti-point"></i>
                    Units
                </a>

            </div>

        </div>

        {{-- Customers --}}
        <div class="menu-group {{ request()->routeIs('admin.customers*') ? 'open' : '' }}">
            <div class="menu-item has-sub" data-tooltip="Customers">
                <svg class="menu-icon" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"></path>
                    <circle cx="9" cy="7" r="4"></circle>
                    <path d="M23 21v-2a4 4 0 0 0-3-3.87"></path>
                    <path d="M16 3.13a4 4 0 0 1 0 7.75"></path>
                </svg>
                <span class="menu-label">Customer</span>
                <i class="ti ti-chevron-right menu-arrow"></i>
            </div>
            <div class="sub-menu">
                <a href="{{ route('admin.customers.index') }}"
                   class="sub-item {{ request()->routeIs('admin.customers.index') ? 'active' : '' }}">
                    <i class="ti ti-point"></i> All customers
                </a>
            </div>
        </div>
        {{-- Inventory --}}
        <div class="menu-group {{ request()->routeIs('admin.stocks*') ? 'open' : '' }}">
            <div class="menu-item has-sub" data-tooltip="Inventory">
                <svg class="menu-icon" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round">
                    <polyline points="22 12 16 12 14 15 10 15 8 12 2 12"></polyline>
                    <path d="M5.45 5.11L2 12v6a2 2 0 0 0 2 2h16a2 2 0 0 0 2-2v-6l-3.45-6.89A2 2 0 0 0 16.76 4H7.24a2 2 0 0 0-1.79 1.11z"></path>
                </svg>
                <span class="menu-label">Inventory</span>
                <i class="ti ti-chevron-right menu-arrow"></i>
            </div>
            <div class="sub-menu">
                <a href="{{ route('admin.stock-update') }}"
                    class="sub-item {{ request()->routeIs('admin.stock-update') ? 'active' : '' }}">
                    <i class="ti ti-point"></i> Stock History
                </a>
                <a href="{{ route('admin.stock-validation') }}"
                   class="sub-item {{ request()->routeIs('admin.stocks.stock-validation') ? 'active' : '' }}">
                    <i class="ti ti-point"></i> Stock Validate
                </a>
            </div>
        </div>

        <div class="menu-group {{ request()->routeIs('admin.reports*') ? 'open' : '' }}">
            <div class="menu-item has-sub" data-tooltip="Reports">
                <svg class="menu-icon" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path>
                    <polyline points="14 2 14 8 20 8"></polyline>
                    <line x1="16" y1="13" x2="8" y2="13"></line>
                    <line x1="16" y1="17" x2="8" y2="17"></line>
                    <polyline points="10 9 9 9 8 9"></polyline>
                </svg>
                <span class="menu-label">Reports</span>
                <i class="ti ti-chevron-right menu-arrow"></i>
            </div>
            <div class="sub-menu">
                <a href="{{ route('admin.reports.index') }}"
                    class="sub-item {{ request()->routeIs('admin.reports.index') ? 'active' : '' }}">
                    <i class="ti ti-point"></i> Daily Sale Report
                </a>
                <a href="{{ route('admin.revenue-tracking') }}"
                    class="sub-item {{ request()->routeIs('admin.revenue-tracking') ? 'active' : '' }}">
                        <i class="ti ti-point"></i> Revenue Tracking
                    </a>
                <a href="{{ route('admin.sale-person') }}"
                   class="sub-item {{ request()->routeIs('admin.sale-person') ? 'active' : '' }}">
                    <i class="ti ti-point"></i> Top Sale
                </a>
                <a href="{{ route('admin.top-product') }}"
                   class="sub-item {{ request()->routeIs('admin.top-product') ? 'active' : '' }}">
                    <i class="ti ti-point"></i> Top Products
                </a>
            </div>
        </div>

        <div class="menu-group {{ request()->routeIs('admin.users*') ? 'open' : '' }}">
            <div class="menu-item has-sub" data-tooltip="Settings">
                <svg class="menu-icon" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round">
                    <circle cx="12" cy="12" r="3"></circle>
                    <path d="M19.4 15a1.65 1.65 0 0 0 .33 1.82l.06.06a2 2 0 1 1-2.83 2.83l-.06-.06a1.65 1.65 0 0 0-1.82-.33 1.65 1.65 0 0 0-1 1.51V21a2 2 0 0 1-4 0v-.09A1.65 1.65 0 0 0 9 19.4a1.65 1.65 0 0 0-1.82.33l-.06.06a2 2 0 1 1-2.83-2.83l.06-.06a1.65 1.65 0 0 0 .33-1.82 1.65 1.65 0 0 0-1.51-1H3a2 2 0 0 1 0-4h.09A1.65 1.65 0 0 0 4.6 9a1.65 1.65 0 0 0-.33-1.82l-.06-.06a2 2 0 1 1 2.83-2.83l.06.06a1.65 1.65 0 0 0 1.82.33H9a1.65 1.65 0 0 0 1-1.51V3a2 2 0 0 1 4 0v.09a1.65 1.65 0 0 0 1 1.51 1.65 1.65 0 0 0 1.82-.33l.06-.06a2 2 0 1 1 2.83 2.83l-.06.06a1.65 1.65 0 0 0-.33 1.82V9a1.65 1.65 0 0 0 1.51 1H21a2 2 0 0 1 0 4h-.09a1.65 1.65 0 0 0-1.51 1z"></path>
                </svg>
                <span class="menu-label">Settings</span>
                <i class="ti ti-chevron-right menu-arrow"></i>
            </div>
            <div class="sub-menu">
                <a href="{{ route('admin.users') }}"
                   class="sub-item {{ request()->routeIs('admin.users*') ? 'active' : '' }}">
                    <i class="ti ti-point"></i> All users
                </a>
                <a href="{{ route('admin.profile') }}"
                   class="sub-item {{ request()->routeIs('admin.profile*') ? 'active' : '' }}">
                    <i class="ti ti-point"></i> Profile
                </a>
                <a href="{{ route('admin.preview-settings') }}"
                   class="sub-item {{ request()->routeIs('admin.preview-settings') ? 'active' : '' }}">
                    <i class="ti ti-point"></i> Preview Settings
                </a>
            </div>
        </div>
        @endrole
    </div>
</nav>

@push('scripts')
<script>
    $(document).ready(function () {

        // ── Sidebar Collapse Toggle ──
        $('#sidebarToggle').on('click', function () {
            $('#sidebar').toggleClass('collapsed');
            localStorage.setItem('sidebar_collapsed', $('#sidebar').hasClass('collapsed'));
        });

        // Restore sidebar state
        if (localStorage.getItem('sidebar_collapsed') === 'true') {
            $('#sidebar').addClass('collapsed');
        }

        // ── Hover Expand when collapsed ──
        let sidebarTimer;
        $('#sidebar').on('mouseenter', function () {
            if ($(this).hasClass('collapsed')) {
                clearTimeout(sidebarTimer);
                $(this).addClass('hover-expand');
            }
        });

        $('#sidebar').on('mouseleave', function () {
            const sidebar = $(this);
            sidebarTimer = setTimeout(function () {
                sidebar.removeClass('hover-expand');
            }, 150);
        });

        // ── Submenu Toggle ──
        $('.menu-item.has-sub').on('click', function (e) {
            if ($('#sidebar').hasClass('collapsed')) return;

            let $group = $(this).closest('.menu-group');
            let isOpen = $group.hasClass('open');

            // Close other open groups
            $('.menu-group.open').not($group).removeClass('open').find('.sub-menu').slideUp(200);

            if (isOpen) {
                $group.removeClass('open');
                $group.find('.sub-menu').slideUp(200);
            } else {
                $group.addClass('open');
                $group.find('.sub-menu').slideDown(200);
            }
        });

        // Restore open submenus
        $('.menu-group.open').each(function () {
            $(this).find('.sub-menu').css('display', 'block');
        });

        // ── User Dropdown
        $('#userDropdownToggle').on('click', function (e) {
            e.stopPropagation();
            $('#userDropdown').toggleClass('open');
        });

        $(document).on('click', function () {
            $('#userDropdown').removeClass('open');
        });
    });
</script>
@endpush
