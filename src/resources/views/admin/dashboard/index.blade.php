@extends('layouts.app')

@section('title', 'Admin Dashboard')

@push('styles')
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined" />
    <link rel="stylesheet" href="{{ asset('assets/css/dashboard/dashboard.css') }}" data-turbo-track="reload">
@endpush

@section('content')

{{-- Page Header --}}
<div class="dash-header">
    <div>
        <h1 class="dash-title">Dashboard</h1>
        <p class="dash-subtitle">Welcome back, {{ auth()->user()->name ?? 'Admin' }} 👋</p>
    </div>
    <div class="dash-period">
        <button class="period-btn">Yesterday</button>
        <button class="period-btn active">Today</button>
        <button class="period-btn">Week</button>
        <button class="period-btn">Month</button>
        <button class="period-btn">Year</button>
    </div>
</div>

{{-- Row 1: Stat Cards --}}
<div class="dash-grid">
    <div class="stat-card">
        <div class="stat-top">
            <span class="stat-label">Total Revenue</span>
            <span class="stat-icon icon-blue">
            <svg xmlns="http://www.w3.org/2000/svg"
                 width="22"
                 height="22"
                 viewBox="0 0 24 24"
                 fill="none"
                 stroke="currentColor"
                 stroke-width="2"
                 stroke-linecap="round"
                 stroke-linejoin="round">

                <path d="M12 1v22"></path>
                <path d="M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"></path>
            </svg>
        </span>

        </div>
        <div class="stat-value">$24,350</div>
        <div class="stat-badge badge-up">
            <svg xmlns="http://www.w3.org/2000/svg"
                width="16"
                height="16"
                viewBox="0 0 24 24"
                fill="none"
                stroke="currentColor"
                stroke-width="2">

                <path d="M7 17L17 7"></path>
                <path d="M7 7h10v10"></path>
            </svg>

            12% this month
        </div>
    </div>
    <div class="stat-card">
        <div class="stat-top">
            <span class="stat-label">Total Orders</span>
            <span class="stat-icon icon-orange">
                <svg xmlns="http://www.w3.org/2000/svg" width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"> <circle cx="9" cy="21" r="1"></circle> <circle cx="20" cy="21" r="1"></circle>
                    <path d="M1 1h4l2.68 13.39a2 2 0 0 0 2 1.61h9.72a2 2 0 0 0 2-1.61L23 6H6"></path>
                </svg>
            </span>
        </div>
        <div class="stat-value">1,284</div>
        <div class="stat-badge badge-up"> <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"> <path d="M7 17L17 7"></path> <path d="M7 7h10v10"></path> </svg>
            8% this month </div>
    </div>
    <div class="stat-card">
        <div class="stat-top">
            <span class="stat-label">Customers</span>
            <span class="stat-icon icon-green">
                <svg xmlns="http://www.w3.org/2000/svg" width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"> <circle cx="12" cy="12" r="10"></circle> <path d="M16 16s-1.5-2-4-2-4 2-4 2"></path> </svg>
            </span>
        </div>
        <div class="stat-value">842</div>
        <div class="stat-badge badge-up"> <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"> <path d="M7 17L17 7"></path> <path d="M7 7h10v10"></path> </svg>
             5% this month
        </div>
    </div>
    <div class="stat-card">
        <div class="stat-top">
            <span class="stat-label">Products</span>
            <span class="stat-icon icon-red"> <svg xmlns="http://www.w3.org/2000/svg" width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"> <path d="M21 16V8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16z"></path> <polyline points="7.5 4.21 12 6.81 16.5 4.21"></polyline> <polyline points="7.5 19.79 7.5 14.6 3 12"></polyline> <polyline points="21 12 16.5 14.6 16.5 19.79"></polyline> <polyline points="12 22.08 12 17"></polyline> <polyline points="12 17 21 12"></polyline> <polyline points="12 17 3 12"></polyline> </svg> </span>
        </div>
        <div class="stat-value">196</div>
        <div class="stat-badge badge-down"> <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"> <path d="M17 17L7 7"></path> <path d="M17 7H7v10"></path> </svg>
             2 low stock
        </div>
      </div>
</div>

{{-- Row 2: Chart + Donut + Mini Stats --}}
<div class="dash-row-2">

    {{-- Sales Chart --}}
    <div class="dash-card chart-card">
        <div class="dash-card-header">
            <div class="dash-card-title">Daily Sales</div>
            <select class="card-select">
                <option>This Week</option>
                <option>Last Week</option>
            </select>
        </div>
        <div id="chart"></div>
    </div>
    <div class="dash-card chart-card">
        <div class="dash-card-header">
            <div class="dash-card-title">Total Income</div>
            <select class="card-select">
                <option>Today</option>
                <option>Week</option>
            </select>
        </div>
            <div id="donutChart"></div>
    </div>
</div>
@endsection

@push('scripts')

{{-- ApexCharts --}}
<script src="https://cdn.jsdelivr.net/npm/apexcharts"></script>
<script src="{{ asset('assets/js/dashboard/chart/column-chart.js') }}"></script>
<script src="{{ asset('assets/js/dashboard/chart/donut-chart.js') }}"></script>

<script>
    // Period tab switcher
    document.querySelectorAll('.period-btn').forEach(btn => {
        btn.addEventListener('click', () => {
            // Remove active from all, then add to clicked
            document.querySelectorAll('.period-btn').forEach(b => b.classList.remove('active'));
            // Add active to clicked button
            btn.classList.add('active');
        });
    });
</script>
@endpush
