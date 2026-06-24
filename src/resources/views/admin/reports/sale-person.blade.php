@extends('layouts.app')

@push('styles')
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@tabler/icons-webfont@latest/dist/tabler-icons.min.css" />
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600&display=swap" rel="stylesheet" />
    <link rel="stylesheet" href="{{ asset('assets/css/dashboard/report/sale-person.css') }}" data-turbo-track="reload">
@endpush

@section('title', 'Sales Performance')

@section('content')

<div class="sp-page">

    {{-- Page Header --}}
    <div class="sp-page__header">
        <div class="sp-page__title-group">
            <h1 class="sp-page__title">Sales performance report</h1>
            <p class="sp-page__subtitle">Real-time point-of-sale data and staff productivity metrics.</p>
        </div>
    </div>

    {{-- Filter Bar --}}
    <form method="GET" action="{{ route('admin.sale-person') }}" class="sp-filter-bar" id="filter-form">
        <input
            type="text"
            name="search"
            id="searchInput"
            value="{{ $search ?? '' }}"
            placeholder="Search by staff name...">

        <input
            type="date"
            name="start_date"
            class="filter-date"
            id="dateFrom"
            value="{{ $startDate }}"
            max="{{ now()->format('Y-m-d') }}">

        <input
            type="date"
            name="end_date"
            class="filter-date"
            id="dateTo"
            value="{{ $endDate }}"
            max="{{ now()->format('Y-m-d') }}">

        <button class="btn-filter" type="submit" id="filter-btn" onclick="showLoader()"  >
            <i class="ti ti-filter"></i> Filter
        </button>
        <a href="{{ route('admin.sale-person') }}" class="btn-reset" onclick="showLoader()" >
            <i class="ti ti-refresh"></i> Reset
        </a>
    </form>

    {{-- KPI Cards --}}
    <div class="sp-kpi-row">

        {{-- Total Sales --}}
        <div class="sp-kpi-card">
            <div class="sp-kpi-card__top">
                <span class="sp-kpi-card__icon">
                    <i class="ti ti-wallet"></i>
                </span>
                <span class="sp-kpi-badge sp-kpi-badge--green">
                    <i class="ti ti-trending-up"></i> live
                </span>
            </div>
            <p class="sp-kpi-card__label">TOTAL SALES</p>
            <p class="sp-kpi-card__value sp-kpi--total-sales">
                ${{ number_format($summary['total_revenue'], 2) }}
            </p>
            <div class="sp-kpi-card__bar"></div>
        </div>

        {{-- Top Salesperson --}}
        <div class="sp-kpi-card">
            <div class="sp-kpi-card__top">
                <span class="sp-kpi-card__icon">
                    <i class="ti ti-user-star"></i>
                </span>
                <span class="sp-kpi-card__period">This period</span>
            </div>
            <p class="sp-kpi-card__label">TOP SALESPERSON</p>
            @if($topPerformer)
                <div class="sp-kpi-person">
                    <div class="sp-avatar sp-avatar--blue">
                        {{ strtoupper(substr($topPerformer->staff_name, 0, 2)) }}
                    </div>
                    <div>
                        <p class="sp-kpi-person__name">{{ $topPerformer->staff_name }}</p>
                        <p class="sp-kpi-person__sub">${{ number_format($topPerformer->total_revenue, 2) }} achievement</p>
                    </div>
                </div>
            @else
                <p class="sp-kpi-card__value" style="font-size:14px; color:var(--color-text-secondary)">No data</p>
            @endif
        </div>

        {{-- Avg. Sale Value --}}
        <div class="sp-kpi-card">
            <div class="sp-kpi-card__top">
                <span class="sp-kpi-card__icon">
                    <i class="ti ti-report-analytics"></i>
                </span>
                <span class="sp-kpi-badge sp-kpi-badge--green">
                    <i class="ti ti-trending-up"></i> avg
                </span>
            </div>
            <p class="sp-kpi-card__label">AVG. SALE VALUE</p>
            <p class="sp-kpi-card__value sp-kpi--avg-order">
                ${{ number_format($summary['avg_per_order'], 2) }}
                <span class="sp-kpi-card__unit">/ transaction</span>
            </p>
        </div>

    </div>

    {{-- Bottom Two-column layout --}}
    <div class="sp-bottom-row">

        {{-- Left: Top Performers Table --}}
        <div class="sp-panel">
            <div class="sp-panel__header">
                <div>
                    <p class="sp-panel__title">Top Performers</p>
                    <p class="sp-panel__sub">Ranked by total revenue generation</p>
                </div>
            </div>

            <div class="table-responsive">
                <table class="table-custom">
                    <thead>
                        <tr>
                            <th>Rank</th>
                            <th>Salesperson Name</th>
                            <th>Total Sales</th>
                            <th>Transactions</th>
                            <th>Avg. Order Value</th>
                            <th>Trend</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($rows as $i => $row)
                            <tr>
                                <td>
                                    <span class="sp-rank">
                                        #{{ str_pad($rows->firstItem() + $i, 2, '0', STR_PAD_LEFT) }}
                                    </span>
                                </td>
                                <td>
                                    <div class="sp-staff-cell">
                                        <div class="sp-avatar sp-avatar--blue">
                                            {{ strtoupper(substr($row->staff_name, 0, 2)) }}
                                        </div>
                                        <span class="sp-staff-name">{{ $row->staff_name }}</span>
                                    </div>
                                </td>
                                <td>${{ number_format($row->total_revenue, 2) }}</td>
                                <td>{{ number_format($row->total_orders) }}</td>
                                <td>${{ number_format($row->avg_per_order, 2) }}</td>
                                <td>
                                    @if(($row->performance ?? 0) >= 50)
                                        <span class="sp-trend sp-trend--up">
                                            <i class="ti ti-trending-up"></i>
                                        </span>
                                    @else
                                        <span class="sp-trend sp-trend--down">
                                            <i class="ti ti-trending-down"></i>
                                        </span>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" style="text-align:center; padding:2rem; color:var(--color-text-secondary, #6b7280);">
                                    No results found.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            {{-- Pagination --}}
            <div class="table-footer">
                <span class="table-footer-left">
                    <div class="table-info">
                        @if($rows->total() > 0)
                            Showing {{ $rows->firstItem() }} - {{ $rows->lastItem() }} of {{ $rows->total() }} results
                        @else
                            No results
                        @endif
                    </div>

                    {{-- Per page --}}
                    <form method="GET" action="{{ route('admin.sale-person') }}">
                        @foreach (request()->except('per_page', 'page') as $key => $value)
                            <input type="hidden" name="{{ $key }}" value="{{ $value }}">
                        @endforeach
                        <select name="per_page" class="pm-pagination__select" onchange="showLoader(); this.form.submit()">
                            <option value="15" {{ request('per_page', 15) == 15 ? 'selected' : '' }}>15</option>
                            <option value="25" {{ request('per_page') == 25 ? 'selected' : '' }}>25</option>
                            <option value="50" {{ request('per_page') == 50 ? 'selected' : '' }}>50</option>
                        </select>
                    </form>
                </span>

                <div class="pagination">
                    {{ $rows->appends(request()->query())->links('vendor.pagination.numbers-only') }}
                </div>
            </div>
        </div>

        {{-- Right: Revenue Share Chart --}}
        <div class="sp-panel sp-panel--side">
            <div class="sp-panel__header">
                <div>
                    <p class="sp-panel__title">Revenue Share</p>
                    <p class="sp-panel__sub">Contribution by salesperson</p>
                </div>
            </div>

            <div class="sp-chart-wrap">
                <div class="sp-chart-container">
                    <div id="regionChart"></div>
                    <div class="sp-chart-center">
                        <span class="sp-chart-center__val">
                            ${{ number_format(collect($chartData)->sum('revenue') / 1000, 1) }}k
                        </span>
                        <span class="sp-chart-center__lbl">Total</span>
                    </div>
                </div>
            </div>

            {{-- Legend --}}
            <div class="sp-region-list">
                @foreach($chartData as $i => $item)
                    @php
                        $colors = ['#2563eb','#38bdf8','#7c3aed','#93c5fd','#22c55e','#f43f5e','#f59e0b','#10b981','#ef4444','#8b5cf6'];
                        $color  = $colors[$i % count($colors)];
                    @endphp
                    <div class="sp-region-row">
                        <div class="sp-region-left">
                            <span class="sp-region-dot" style="background:{{ $color }}"></span>
                            <span class="sp-region-name">{{ $item['name'] }}</span>
                        </div>
                        <span class="sp-region-val">${{ number_format($item['revenue'] / 1000, 1) }}k</span>
                    </div>
                @endforeach
            </div>
        </div>
    </div>
</div>

@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/apexcharts"></script>
<script>
(function () {
    const staffColors = [
        '#2563eb','#38bdf8','#7c3aed','#93c5fd',
        '#22c55e','#f43f5e','#f59e0b','#10b981',
        '#ef4444','#8b5cf6',
    ];

    const chartData = @json($chartData);
    const labels    = chartData.map(d => d.name);
    const series    = chartData.map(d => parseFloat(d.revenue));
    const total     = series.reduce((a, b) => a + b, 0);
    // chart
    const chart = new ApexCharts(document.querySelector('#regionChart'), {
        chart: {
            type    : 'donut',
            width   : 220,
            height  : 220,
            toolbar : { show: false },
        },
        series      : series,
        labels      : labels,
        colors      : staffColors.slice(0, series.length),
        dataLabels  : { enabled: false },
        legend      : { show: false },
        stroke      : { width: 3, colors: ['#fff'] },
        plotOptions : {
            pie: {
                donut        : { size: '72%', labels: { show: false } },
                expandOnClick: false,
            },
        },
        tooltip: {
            y: {
                formatter: val => {
                    const pct = total > 0 ? Math.round(val / total * 100) : 0;
                    return '$' + (val / 1000).toFixed(1) + 'k (' + pct + '%)';
                }
            }
        },
        states: {
            hover: { filter: { type: 'darken', value: 0.85 } },
        },
    });

    chart.render();
})();
</script>
@endpush
