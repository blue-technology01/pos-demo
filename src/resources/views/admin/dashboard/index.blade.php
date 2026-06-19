@extends('layouts.app')

@section('title', 'Dashboard')

@push('styles')
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet" />
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined" />
    <link rel="stylesheet" href="{{ asset('assets/css/dashboard/dashboard.css') }}">
@endpush

@section('content')

@php $stats = $dashboardData['stats'] ?? []; @endphp

<div class="dash-wrapper">

    {{-- ── Header ── --}}
    <div class="dash-header">
        <div>
            <h1 class="dash-title">Dashboard</h1>
            <p class="dash-subtitle">Welcome back, {{ auth()->user()->name ?? 'Admin' }} 👋</p>
        </div>

        {{-- Period toggle --}}
        <div class="dash-period">
            <button class="period-btn"        data-period="yesterday">Yesterday</button>
            <button class="period-btn active" data-period="today">Today</button>
            <button class="period-btn"        data-period="week">Week</button>
            <button class="period-btn"        data-period="month">Month</button>
            <button class="period-btn"        data-period="year">Year</button>
        </div>
    </div>

    {{-- ── Stat Cards ── --}}
    <div class="dash-grid">

        <div class="stat-card">
            <div class="stat-icon">
                <span class="material-symbols-outlined">payments</span>
            </div>
            <div class="stat-label">Total revenue</div>
            <div class="stat-value" id="stat-revenue">—</div>
            <span class="stat-badge up" id="stat-revenue-badge"></span>
        </div>

        <div class="stat-card">
            <div class="stat-icon">
                <span class="material-symbols-outlined">shopping_bag</span>
            </div>
            <div class="stat-label">Total orders</div>
            <div class="stat-value" id="stat-orders">—</div>
            <span class="stat-badge up" id="stat-orders-badge"></span>
        </div>

        <div class="stat-card">
            <div class="stat-icon">
                <span class="material-symbols-outlined">group</span>
            </div>
            <div class="stat-label">Customers</div>
            <div class="stat-value" id="stat-customers">—</div>
            <span class="stat-badge up" id="stat-customers-badge"></span>
        </div>

        <div class="stat-card">
            <div class="stat-icon">
                <span class="material-symbols-outlined">inventory_2</span>
            </div>
            <div class="stat-label">Products</div>
            <div class="stat-value" id="stat-products">—</div>
            <span class="stat-badge up" id="stat-products-badge"></span>
        </div>

    </div>

    {{-- ── Charts Row ── --}}
    <div class="dash-row-2">

        {{-- Sales analytics (column chart) --}}
        <div class="dash-card chart-card">
            <div class="dash-card-header">
                <div>
                    <div class="dash-card-title">Sales analytics</div>
                    <div class="dash-card-sub">Revenue, orders and customer trends</div>
                </div>
                <div class="chart-tabs">
                    <button class="chart-tab active" data-chart="revenue">Revenue</button>
                    <button class="chart-tab"        data-chart="orders">Orders</button>
                    <button class="chart-tab"        data-chart="customers">Customers</button>
                </div>
            </div>
            <div id="chart"></div>
        </div>

        {{-- Product statistic (donut chart) --}}
        <div class="dash-card chart-card">
            <div class="dash-card-header">
                <div>
                    <div class="dash-card-title">Product statistic</div>
                    <div class="dash-card-sub">Track your product sales</div>
                </div>
            </div>
            <div id="donutChart"></div>
            <ul class="product-stat-list" id="product-stat-list">
                {{-- Populated by JS --}}
            </ul>
        </div>
    </div>

</div>

@endsection

@push('scripts')
<script>
    window.DASHBOARD_DATA_URL       = @json(route('admin.dashboard.data'));
    window.INITIAL_DASHBOARD_PERIOD = @json($initialPeriod ?? 'today');
    window.INITIAL_DASHBOARD_DATA   = @json($dashboardData ?? null);
</script>
<script src="https://cdn.jsdelivr.net/npm/apexcharts@3.45.2/dist/apexcharts.min.js"></script>
<script src="{{ asset('assets/js/dashboard/chart/dashboard-utils.js') }}"></script>
<script src="{{ asset('assets/js/dashboard/chart/column-chart.js') }}"></script>
<script src="{{ asset('assets/js/dashboard/chart/donut-chart.js') }}"></script>
<script src="{{ asset('assets/js/dashboard/chart/dashboard.js') }}"></script>
@endpush
