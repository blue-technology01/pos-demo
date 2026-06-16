@extends('layouts.app')

@push('styles')
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600&display=swap" rel="stylesheet" />
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@tabler/icons-webfont@latest/dist/tabler-icons.min.css" />
    <link rel="stylesheet" href="{{ asset('assets/css/dashboard/report/top-product.css') }}" data-turbo-track="reload">
@endpush

@section('title', 'Top Products Report')

@section('content')

<div class="tp-page">

    {{-- ── Page Header ── --}}
    <div class="tp-page__header">
        <div class="tp-page__title-group">
            <h1 class="tp-page__title">Top products report</h1>
            <p class="tp-page__subtitle">Best-selling items ranked by quantity sold</p>
        </div>
    </div>

    {{-- ── Filter Bar ── --}}
    <form method="GET" action="{{ route('admin.top-product') }}" id="filterForm">
        <div class="tp-filter-bar">

            {{-- Search --}}
            <div class="tp-search-wrap">
                <i class="ti ti-search tp-search-icon" aria-hidden="true"></i>
                <input
                    type="text"
                    name="search"
                    id="searchInput"
                    placeholder="Search product name..."
                    value="{{ request('search') }}"
                >
            </div>

            {{-- Category --}}
            <select class="filter-category" name="category_code" id="filterCategory">
                <option value="">All categories</option>
                @foreach ($categories as $category)
                    <option
                        value="{{ $category->code }}"
                        {{ request('category_code') == $category->code ? 'selected' : '' }}
                    >
                        {{ $category->name }}
                    </option>
                @endforeach
            </select>

            {{-- Date From --}}
            <input
                type="date"
                name="date_from"
                value="{{ request('date_from') }}"
            >

            {{-- Date To --}}
            <input
                type="date"
                name="date_to"
                value="{{ request('date_to') }}"
            >

            {{-- Filter --}}
            <button type="submit" class="btn-filter">
                <i class="ti ti-filter" aria-hidden="true"></i> Filter
            </button>

            {{-- Reset --}}
            <a href="{{ route('admin.top-product') }}" class="btn-reset">
                <i class="ti ti-refresh" aria-hidden="true"></i> Reset
            </a>

            {{-- Excel Export --}}
            <a href="#" class="btn-excel">
                <i class="ti ti-file-spreadsheet" aria-hidden="true"></i> Excel
            </a>

            {{-- View Toggle --}}
            <div class="tp-view-toggle">
                <button type="button" class="tp-view-btn active" id="btn-chart">
                    <i class="ti ti-chart-bar" aria-hidden="true"></i> Chart
                </button>
                <button type="button" class="tp-view-btn" id="btn-list">
                    <i class="ti ti-list" aria-hidden="true"></i> List
                </button>
            </div>

        </div>
    </form>

    {{-- ── KPI Summary Cards ── --}}
    <div class="tp-kpi-row">
        <div class="tp-kpi-card">
            <p class="tp-kpi-card__label">TOTAL PRODUCTS</p>
            <p class="tp-kpi-card__value tp-kpi-card__value--blue">
                {{ $summary['total_products'] }}
            </p>
        </div>
        <div class="tp-kpi-card">
            <p class="tp-kpi-card__label">TOTAL QTY SOLD</p>
            <p class="tp-kpi-card__value tp-kpi-card__value--green">
                {{ number_format($summary['total_qty_sold']) }}
            </p>
        </div>
        <div class="tp-kpi-card">
            <p class="tp-kpi-card__label">TOTAL REVENUE</p>
            <p class="tp-kpi-card__value tp-kpi-card__value--purple">
                ${{ number_format($summary['total_revenue'], 2) }}
            </p>
        </div>
        <div class="tp-kpi-card">
            <p class="tp-kpi-card__label">LOW / OUT OF STOCK</p>
            <p class="tp-kpi-card__value tp-kpi-card__value--red">
                {{ $summary['low_stock'] }} / {{ $summary['out_of_stock'] }}
            </p>
        </div>
    </div>

    {{-- ── Chart View ── --}}
    <div id="chart-view" class="tp-panel">
        <div class="tp-panel__header">
            <div>
                <p class="tp-panel__title">Revenue by product</p>
                <p class="tp-panel__sub">Top products this period</p>
            </div>
        </div>
        <div class="tp-chart-container">
            <div id="topProductsChart"></div>
        </div>
    </div>

    {{-- ── List View ── --}}
    <div id="list-view" class="tp-panel hidden">
        <div class="tp-panel__header">
            <div>
                <p class="tp-panel__title">Product rankings</p>
                <p class="tp-panel__sub">Ranked by total revenue generation</p>
            </div>
            <span class="tp-panel__meta">
                Showing {{ $products->count() }} of {{ $summary['total_products'] }} products
            </span>
        </div>

        <div class="table-responsive">
            <table class="table-custom" id="topProductTable">
                <thead>
                    <tr>
                        <th style="width:52px">Rank</th>
                        <th>Product name</th>
                        <th>Category</th>
                        <th>Unit price</th>
                        <th>Qty sold</th>
                        <th>Total revenue</th>
                        <th>Performance</th>
                        <th>Stock</th>
                    </tr>
                </thead>
                <tbody id="unit-table-body">
                    @php
                        $maxRevenue = $products->max('total_revenue') ?: 1;
                    @endphp

                    @forelse ($products as $product)
                        @php
                            $performance = (int) round($product->total_revenue / $maxRevenue * 100);
                            $rankClass   = $product->rank <= 3 ? 'rank-' . $product->rank : 'rank-other';
                            $stockClass  = match($product->stock_status) {
                                'In Stock'     => 'stock-in',
                                'Low Stock'    => 'stock-low',
                                'Out of Stock' => 'stock-out',
                                default        => 'stock-in',
                            };
                        @endphp
                        <tr>
                            <td>
                                <span class="rank-badge {{ $rankClass }}">
                                    {{ $product->rank }}
                                </span>
                            </td>
                            <td>
                                <div class="tp-product-cell">
                                    <div class="tp-product-icon">
                                        <i class="ti ti-package" aria-hidden="true"></i>
                                    </div>
                                    <span class="tp-product-name">{{ $product->product_name }}</span>
                                </div>
                            </td>
                            <td>{{ $product->category ?? '—' }}</td>
                            <td>${{ number_format($product->unit_price, 2) }}</td>
                            <td>{{ number_format($product->qty_sold) }}</td>
                            <td>${{ number_format($product->total_revenue, 2) }}</td>
                            <td>
                                <div class="tp-bar-wrap">
                                    <div class="tp-bar-track">
                                        <div class="tp-bar-fill" style="width:{{ $performance }}%"></div>
                                    </div>
                                    <span class="tp-bar-num">{{ $performance }}%</span>
                                </div>
                            </td>
                            <td>
                                <span class="stock-badge {{ $stockClass }}">
                                    {{ $product->stock_status }}
                                </span>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" style="text-align:center; padding:2rem; color:var(--color-text-secondary, #6b7280);">
                                No products found.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{-- Pagination --}}
        <div class="pm-pagination">
            <div class="pm-pagination__meta">
                <span class="pm-pagination__text">
                    Showing {{ $products->count() }} of {{ $summary['total_products'] }} products
                </span>
            </div>
        </div>
    </div>

</div>

@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/apexcharts"></script>

{{-- Pass PHP data to JS --}}
<script>
    window.topProductsData = @json($chartData);
</script>

<script src="{{ asset('assets/js/dashboard/chart/top-product-chart.js') }}"></script>

<script>
(function () {

    let chartInitialized = false;
    let chartInstance    = null;

    // ── Init ApexCharts ────────────────────────────────────────────────────
    function initChart() {
        const data = window.topProductsData || [];
        if (!data.length) return;

        const labels   = data.map(d => d.name);
        const qtySold  = data.map(d => parseInt(d.qty_sold)      || 0);
        const revenues = data.map(d => parseFloat(d.total_revenue) || 0);

        const options = {
            chart: {
                type    : 'bar',
                height  : 340,
                toolbar : { show: false },
                fontFamily: 'Inter, sans-serif',
            },
            series: [
                { name: 'Qty sold',     data: qtySold  },
                { name: 'Revenue ($)',  data: revenues },
            ],
            xaxis: {
                categories: labels,
                labels: { style: { fontSize: '11px' } },
            },
            yaxis: [
                {
                    seriesName : 'Qty sold',
                    title      : { text: 'Qty sold', style: { fontSize: '11px' } },
                    labels     : { style: { fontSize: '11px' } },
                },
                {
                    seriesName : 'Revenue ($)',
                    opposite   : true,
                    title      : { text: 'Revenue ($)', style: { fontSize: '11px' } },
                    labels     : {
                        style    : { fontSize: '11px' },
                        formatter: v => '$' + v.toLocaleString('en-US', { minimumFractionDigits: 0 }),
                    },
                },
            ],
            colors      : ['#2563eb', '#059669'],
            plotOptions : {
                bar: {
                    borderRadius : 6,
                    columnWidth  : '50%',
                },
            },
            dataLabels: { enabled: false },
            legend    : {
                position  : 'top',
                fontSize  : '12px',
                fontFamily: 'Inter, sans-serif',
            },
            grid: {
                borderColor    : 'rgba(0,0,0,0.06)',
                strokeDashArray: 4,
                yaxis          : { lines: { show: true  } },
                xaxis          : { lines: { show: false } },
            },
            tooltip: {
                y: [
                    { formatter: v => v + ' units' },
                    { formatter: v => '$' + v.toLocaleString('en-US', { minimumFractionDigits: 2 }) },
                ],
            },
        };

        if (chartInstance) {
            chartInstance.destroy();
            chartInstance = null;
        }

        chartInstance = new ApexCharts(
            document.querySelector('#topProductsChart'),
            options
        );
        chartInstance.render();
        chartInitialized = true;
    }

    // ── View switcher ──────────────────────────────────────────────────────
    function switchView(view) {
        const chartView = document.getElementById('chart-view');
        const listView  = document.getElementById('list-view');
        const btnChart  = document.getElementById('btn-chart');
        const btnList   = document.getElementById('btn-list');

        if (view === 'chart') {
            chartView.classList.remove('hidden');
            listView.classList.add('hidden');
            btnChart.classList.add('active');
            btnList.classList.remove('active');
            setTimeout(initChart, 150);
        } else {
            listView.classList.remove('hidden');
            chartView.classList.add('hidden');
            btnList.classList.add('active');
            btnChart.classList.remove('active');
        }
    }

    // ── Events ─────────────────────────────────────────────────────────────
    document.getElementById('btn-chart').addEventListener('click', function () {
        switchView('chart');
    });

    document.getElementById('btn-list').addEventListener('click', function () {
        switchView('list');
    });

    // Reset chartInitialized on form submit so chart re-renders after filter
    const filterForm = document.getElementById('filterForm');
    if (filterForm) {
        filterForm.addEventListener('submit', function () {
            chartInitialized = false;
        });
    }

    // ── Boot ───────────────────────────────────────────────────────────────
    function boot() {
        switchView('chart');
    }

    // Support Turbo / standard DOMContentLoaded
    if (document.readyState === 'complete' || document.readyState === 'interactive') {
        boot();
    } else {
        document.addEventListener('DOMContentLoaded', boot);
    }

    document.addEventListener('turbo:load', function () {
        chartInitialized = false;
        boot();
    });

})();
</script>
@endpush
