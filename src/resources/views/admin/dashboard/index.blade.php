@extends('layouts.app')

@section('title', 'Admin Dashboard')

@push('styles')
    <link rel="stylesheet" href="{{ asset('assets/css/dashboard/dashboard.css') }}">
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
            <span class="stat-icon icon-blue"><i class="ti ti-currency-dollar"></i></span>
        </div>
        <div class="stat-value">$24,350</div>
        <div class="stat-badge badge-up"><i class="ti ti-trending-up"></i> 12% this month</div>
    </div>
    <div class="stat-card">
        <div class="stat-top">
            <span class="stat-label">Total Orders</span>
            <span class="stat-icon icon-orange"><i class="ti ti-shopping-cart"></i></span>
        </div>
        <div class="stat-value">1,284</div>
        <div class="stat-badge badge-up"><i class="ti ti-trending-up"></i> 8% this month</div>
    </div>
    <div class="stat-card">
        <div class="stat-top">
            <span class="stat-label">Customers</span>
            <span class="stat-icon icon-green"><i class="ti ti-users"></i></span>
        </div>
        <div class="stat-value">842</div>
        <div class="stat-badge badge-up"><i class="ti ti-trending-up"></i> 5% this month</div>
    </div>
    <div class="stat-card">
        <div class="stat-top">
            <span class="stat-label">Products</span>
            <span class="stat-icon icon-red"><i class="ti ti-box"></i></span>
        </div>
        <div class="stat-value">196</div>
        <div class="stat-badge badge-down"><i class="ti ti-trending-down"></i> 2 low stock</div>
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
        <div class="chart-area">
            <div class="chart-y-labels">
                <span>250</span>
                <span>200</span>
                <span>150</span>
                <span>100</span>
                <span>50</span>
                <span>0</span>
            </div>
            <div class="chart-bars-wrap">
                <div class="chart-bar-col">
                    <div class="chart-bar-track">
                        <div class="chart-bar" style="height:55%"></div>
                    </div>
                    <div class="chart-bar-label">Mon</div>
                </div>
                <div class="chart-bar-col">
                    <div class="chart-bar-track">
                        <div class="chart-bar" style="height:75%">
                            <div class="bar-tooltip">$205</div>
                        </div>
                    </div>
                    <div class="chart-bar-label">Tue</div>
                </div>
                <div class="chart-bar-col">
                    <div class="chart-bar-track">
                        <div class="chart-bar" style="height:45%"></div>
                    </div>
                    <div class="chart-bar-label">Wed</div>
                </div>
                <div class="chart-bar-col">
                    <div class="chart-bar-track">
                        <div class="chart-bar active" style="height:92%">
                            <div class="bar-tooltip">$312</div>
                        </div>
                    </div>
                    <div class="chart-bar-label">Thu</div>
                </div>
                <div class="chart-bar-col">
                    <div class="chart-bar-track">
                        <div class="chart-bar" style="height:65%"></div>
                    </div>
                    <div class="chart-bar-label">Fri</div>
                </div>
                <div class="chart-bar-col">
                    <div class="chart-bar-track">
                        <div class="chart-bar" style="height:82%"></div>
                    </div>
                    <div class="chart-bar-label">Sat</div>
                </div>
                <div class="chart-bar-col">
                    <div class="chart-bar-track">
                        <div class="chart-bar" style="height:38%"></div>
                    </div>
                    <div class="chart-bar-label">Sun</div>
                </div>
            </div>
        </div>
    </div>

    {{-- Donut Chart --}}
    <div class="dash-card donut-card">
        <div class="dash-card-header">
            <div class="dash-card-title">Total Income</div>
            <select class="card-select">
                <option>Today</option>
                <option>Week</option>
            </select>
        </div>
        <div class="donut-wrap">
            <svg viewBox="0 0 120 120" class="donut-svg">
                <circle cx="60" cy="60" r="46" fill="none" stroke="#f3f4f6" stroke-width="14"/>
                {{-- Food: 55% --}}
                <circle cx="60" cy="60" r="46" fill="none" stroke="#ef4444" stroke-width="14"
                    stroke-dasharray="159 289" stroke-dashoffset="0" stroke-linecap="round"/>
                {{-- Drinks: 30% --}}
                <circle cx="60" cy="60" r="46" fill="none" stroke="#f97316" stroke-width="14"
                    stroke-dasharray="87 289" stroke-dashoffset="-159" stroke-linecap="round"/>
                {{-- Others: 15% --}}
                <circle cx="60" cy="60" r="46" fill="none" stroke="#1e293b" stroke-width="14"
                    stroke-dasharray="43 289" stroke-dashoffset="-246" stroke-linecap="round"/>
                <text x="60" y="55" text-anchor="middle" font-size="11" font-weight="700" fill="#111827">$77,541</text>
                <text x="60" y="67" text-anchor="middle" font-size="7" fill="#9ca3af">Total Income</text>
            </svg>
        </div>
        <div class="donut-legend">
            <div class="legend-item">
                <span class="legend-dot" style="background:#ef4444"></span>
                Food
                <strong class="legend-pct">55%</strong>
            </div>
            <div class="legend-item">
                <span class="legend-dot" style="background:#f97316"></span>
                Drinks
                <strong class="legend-pct">30%</strong>
            </div>
            <div class="legend-item">
                <span class="legend-dot" style="background:#1e293b"></span>
                Others
                <strong class="legend-pct">15%</strong>
            </div>
        </div>
    </div>

    {{-- Mini Stat Cards --}}
    <div class="mini-stats">
        <div class="mini-card">
            <div class="mini-icon icon-orange"><i class="ti ti-shopping-bag"></i></div>
            <div class="mini-info">
                <div class="mini-label">Total Orders</div>
                <div class="mini-badge badge-down"><i class="ti ti-trending-down"></i> -2.33%</div>
                <div class="mini-value">21,375</div>
            </div>
        </div>
        <div class="mini-card">
            <div class="mini-icon icon-green"><i class="ti ti-user-plus"></i></div>
            <div class="mini-info">
                <div class="mini-label">New Customers</div>
                <div class="mini-badge badge-up"><i class="ti ti-trending-up"></i> +32.40%</div>
                <div class="mini-value">256</div>
            </div>
        </div>
    </div>

</div>

@push('scripts')
<script>
    // Period tab switcher
    document.querySelectorAll('.period-btn').forEach(btn => {
        btn.addEventListener('click', () => {
            document.querySelectorAll('.period-btn').forEach(b => b.classList.remove('active'));
            btn.classList.add('active');
        });
    });
</script>
@endpush

@endsection
