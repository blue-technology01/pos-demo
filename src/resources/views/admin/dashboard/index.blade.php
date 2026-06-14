@extends('layouts.app')

@section('title', 'Admin Dashboard')

@push('styles')
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined" />
    <link rel="stylesheet" href="{{ asset('assets/css/dashboard/dashboard.css') }}">
@endpush

@section('content')

    @php
        $stats = $dashboardData['stats'] ?? [];
    @endphp

    {{-- ─── Header ──────────────────────────────────────────────────────────── --}}
    <div class="dash-header">

        <div>
            <h1 class="dash-title">Dashboard</h1>
            <p class="dash-subtitle">Welcome back, {{ auth()->user()->name ?? 'Admin' }} 👋</p>
        </div>

        <div class="dash-period">
            <button class="period-btn" data-period="yesterday">Yesterday</button>
            <button class="period-btn active" data-period="today">Today</button>
            <button class="period-btn" data-period="week">Week</button>
            <button class="period-btn" data-period="month">Month</button>
            <button class="period-btn" data-period="year">Year</button>
        </div>

    </div>

    <div class="dash-grid">

        <div class="stat-card">
            <div class="stat-label">Total Revenue</div>
            <div class="stat-value" id="stat-revenue">—</div>
        </div>

        <div class="stat-card">
            <div class="stat-label">Total Orders</div>
            <div class="stat-value" id="stat-orders">—</div>
        </div>

        <div class="stat-card">
            <div class="stat-label">Customers</div>
            <div class="stat-value" id="stat-customers">—</div>
        </div>

        <div class="stat-card">
            <div class="stat-label">Products</div>
            <div class="stat-value" id="stat-products">—</div>
        </div>

    </div>

    <div class="dash-row-2">

        <div class="dash-card chart-card">
            <div class="dash-card-header">
                <div class="dash-card-title">Sales Analytics</div>
            </div>
            <div id="chart"></div>
        </div>

        <div class="dash-card chart-card">
            <div class="dash-card-header">
                <div class="dash-card-title">Sales by Category</div>
            </div>
            <div id="donutChart"></div>
        </div>

    </div>

@endsection

@push('scripts')

    <script>
        window.DASHBOARD_DATA_URL = @json(route('admin.dashboard.data'));
        window.INITIAL_DASHBOARD_PERIOD = @json($initialPeriod ?? 'today');
        window.INITIAL_DASHBOARD_DATA = @json($dashboardData ?? null);
    </script>
    <script src="https://cdn.jsdelivr.net/npm/apexcharts@3.45.2/dist/apexcharts.min.js"></script>
    <script src="{{ asset('assets/js/dashboard/chart/dashboard-utils.js') }}"></script>
    <script src="{{ asset('assets/js/dashboard/chart/column-chart.js') }}"></script>
    <script src="{{ asset('assets/js/dashboard/chart/donut-chart.js') }}"></script>
    <script src="{{ asset('assets/js/dashboard/chart/dashboard.js') }}"></script>

@endpush
