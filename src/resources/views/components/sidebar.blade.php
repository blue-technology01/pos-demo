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
                <a href="{{route('admin.productlist')}}" class="sub-item {{ request()->routeIs('admin.products.productlist') ? 'active' : '' }}">
                    <i class="ti ti-point"></i> All products
                </a>
                <a href="{{route('admin.category')}}" class="sub-item {{ request()->routeIs('admin.products.category') ? 'active' : '' }}">
                    <i class="ti ti-point"></i> Category
                </a>
                <a href="{{route('admin.unit')}}" class="sub-item {{ request()->routeIs('admin.products.unit') ? 'active' : '' }}">
                    <i class="ti ti-point"></i> Units
                </a>
            </div>
        </div>
        {{-- Inventory --}}
        <div class="menu-group {{ request()->routeIs('admin.stocks*') ? 'open' : '' }}">
            <div class="menu-item has-sub" data-tooltip="Products">
                <i class="ti ti-box menu-icon"></i>
                <span class="menu-label">Inventory</span>
                <i class="ti ti-chevron-right menu-arrow"></i>
            </div>
            <div class="sub-menu">
                <a href="{{route('admin.stock-update')}}" class="sub-item {{ request()->routeIs('admin.stocks.stock-update') ? 'active' : '' }}">
                    <i class="ti ti-point"></i> Stock History
                </a>
                <a href="{{route('admin.stock-validation')}}" class="sub-item {{ request()->routeIs('admin.stocks.stock-validation') ? 'active' : '' }}">
                    <i class="ti ti-point"></i> Stock Validate
                </a>
            </div>
        </div>

        {{-- Reports --}}
        <div class="menu-group {{ request()->routeIs('admin.reports*') ? 'open' : '' }}">
            <div class="menu-item has-sub" data-tooltip="Reports">
                <i class="ti ti-user-cog menu-icon"></i>
                <span class="menu-label">Reports</span>
                <i class="ti ti-chevron-right menu-arrow"></i>
            </div>
            <div class="sub-menu">
                <a href="" class="sub-item">
                    <i class="ti ti-point"></i>Daily sale Report
                </a>
                <a href="" class="sub-item">
                    <i class="ti ti-point"></i>Reven Tracking
                </a>
                <a href="" class="sub-item">
                    <i class="ti ti-point"></i>Top Product
                </a>
                <a href="" class="sub-item">
                    <i class="ti ti-point"></i>Sale Person
                </a>
            </div>
        </div>

        {{-- Settings --}}
        <div class="menu-group {{ request()->routeIs('admin.users*') ? 'open' : '' }}">
            <div class="menu-item has-sub" data-tooltip="Settings">
                <i class="ti ti-user-cog menu-icon"></i>
                <span class="menu-label">Settings</span>
                <i class="ti ti-chevron-right menu-arrow"></i>
            </div>
            <div class="sub-menu">
                <a href="{{ route('admin.users') }}" class="sub-item {{ request()->routeIs('admin.users*') ? 'active' : '' }}">
                    <i class="ti ti-point"></i> All users
                </a>
                <a href="{{ route('admin.profile') }}" class="sub-item {{ request()->routeIs('admin.profile*') ? 'active' : '' }}" class="sub-item">
                    <i class="ti ti-point"></i> Profile
                </a>
                <a href="{{ route('admin.payment-method') }}" class="sub-item {{ request()->routeIs('admin.payment-method*') ? 'active' : '' }}" class="sub-item">
                    <i class="ti ti-point"></i> Payment Method
                </a>
            </div>
        </div>
        @endrole
    </div>
</nav>

@push('scripts')
    <script>
        $(document).ready(function () {

            // ── User dropdown ──
            $('#userDropdownToggle').on('click', function (e) {
                e.stopPropagation();
                $('#userDropdown').toggleClass('open');
            });

            $(document).on('click', function () {
                $('#userDropdown').removeClass('open');
            });

            // ── Sidebar collapse toggle ──
            $('#sidebarToggle').on('click', function () {
                $('#sidebar').toggleClass('collapsed');

                localStorage.setItem(
                    'sidebar_collapsed',
                    $('#sidebar').hasClass('collapsed')
                );
            });

            let sidebarTimer;

            $('#sidebar').on('mouseenter', function(){

                if($(this).hasClass('collapsed')){

                    clearTimeout(sidebarTimer);

                    $(this).addClass('hover-expand');
                }
            });

            $('#sidebar').on('mouseleave', function(){

                const sidebar=$(this);

                sidebarTimer=setTimeout(function(){

                    sidebar.removeClass('hover-expand');

                },120);
            });

            // Restore state
            if (localStorage.getItem('sidebar_collapsed') === 'true') {
                $('#sidebar').addClass('collapsed');
            }

            // ── Auto expand on hover ──
            $('#sidebar').hover(
                function () {
                    if ($(this).hasClass('collapsed')) {
                        $(this).addClass('hover-expand');
                    }
                },
                function () {
                    $(this).removeClass('hover-expand');
                }
            );

            // ── Submenu toggle ──
            $('.menu-item.has-sub').on('click', function () {

                // prevent submenu in collapsed mode
                if ($('#sidebar').hasClass('collapsed')) {
                    return;
                }

                let $group = $(this).closest('.menu-group');
                let isOpen = $group.hasClass('open');

                $('.menu-group.open')
                    .not($group)
                    .removeClass('open')
                    .find('.sub-menu')
                    .slideUp(200);

                if (isOpen) {
                    $group.removeClass('open');
                    $group.find('.sub-menu').slideUp(200);
                } else {
                    $group.addClass('open');
                    $group.find('.sub-menu').slideDown(200);
                }
            });

            // Restore submenu
            $('.menu-group.open').each(function () {
                $(this).find('.sub-menu').css('display', 'block');
            });
        });
    </script>
@endpush
