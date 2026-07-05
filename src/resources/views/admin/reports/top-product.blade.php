@extends('layouts.app')

@push('styles')
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600&display=swap" rel="stylesheet" />
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@tabler/icons-webfont@latest/dist/tabler-icons.min.css" />
    <link rel="stylesheet" href="{{ asset('assets/css/dashboard/report/top-product.css') }}" data-turbo-track="reload">
@endpush

@section('title', 'Products Report')

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
            <button type="submit" class="btn-filter" onclick="showLoader()" >
                <i class="ti ti-filter" aria-hidden="true"></i> Filter
            </button>

            {{-- Reset --}}
            <a href="{{ route('admin.top-product') }}" class="btn-reset" onclick="showLoader()">
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
        <div class="table-footer" style="width: 100%; display: flex;  justify-content: space-between" id="tableFooter">
            <span class="table-footer-left">
                <div class="table-info">
                    showing
                    {{ $products->firstItem() ?? 0 }}
                    -
                    {{ $products->lastItem() ?? 0 }}
                    of
                    {{ $products->total() }}
                    sales
                </div>

                {{-- per page --}}
                <form method="GET" action="{{ request()->url() }}">
                    @foreach(request()->except('per_page', 'page') as $key => $value)
                        <input type="hidden" name="{{ $key }}" value="{{ $value }}">
                    @endforeach

                    <select name="per_page" onchange="showLoader(); this.form.submit()">
                        <option value="15" {{ request('per_page', 15) == 15 ? 'selected' : '' }}>15</option>
                        <option value="25" {{ request('per_page') == 25 ? 'selected' : '' }}>25</option>
                        <option value="50" {{ request('per_page') == 50 ? 'selected' : '' }}>50</option>
                    </select>
                </form>
            </span>

            <div class="pagination">
                {{ $products->links('vendor.pagination.numbers-only') }}
            </div>
        </div>
    </div>

</div>

@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/apexcharts"></script>

<script>
window.topProductsData = @json($chartData);
</script>

<script src="{{ asset('assets/js/dashboard/chart/top-product-chart.js') }}"></script>

<script>
(function () {

    let chartInstance = null;

    function initChart() {
        const data = window.topProductsData || [];
        if (!data.length) return;

        const labels   = data.map(d => d.name);
        const qtySold  = data.map(d => parseInt(d.qty_sold) || 0);
        const revenues = data.map(d => parseFloat(d.total_revenue) || 0);

        const options = {
            chart: {
                type: 'bar',
                height: 340,
                toolbar: { show: false },
                fontFamily: 'Inter, sans-serif',
            },
            series: [
                { name: 'Qty sold', data: qtySold },
                { name: 'Revenue ($)', data: revenues },
            ],
            xaxis: {
                categories: labels,
                labels: { style: { fontSize: '11px' } },
            },
            yaxis: [
                {
                    seriesName: 'Qty sold',
                    title: { text: 'Qty sold', style: { fontSize: '11px' } },
                },
                {
                    seriesName: 'Revenue ($)',
                    opposite: true,
                    title: { text: 'Revenue ($)', style: { fontSize: '11px' } },
                    labels: {
                        formatter: v =>
                            '$' + v.toLocaleString('en-US', { minimumFractionDigits: 0 }),
                    },
                },
            ],
            colors: ['#2563eb', '#059669'],
            dataLabels: { enabled: false },
        };

        if (chartInstance) {
            chartInstance.destroy();
        }

        chartInstance = new ApexCharts(
            document.querySelector('#topProductsChart'),
            options
        );

        chartInstance.render();
    }
    function getView() {
        return new URLSearchParams(window.location.search).get('view') || 'chart';
    }
    function setView(view, updateUrl = false) {

        const chart = document.getElementById('chart-view');
        const list  = document.getElementById('list-view');

        const btnChart = document.getElementById('btn-chart');
        const btnList  = document.getElementById('btn-list');
        const viewInput = document.getElementById('viewInput');

        // update URL
        if (updateUrl) {
            const url = new URL(window.location);
            url.searchParams.set('view', view);
            window.history.replaceState({}, '', url);
        }

        // keep form state
        if (viewInput) viewInput.value = view;

        // toggle UI
        if (view === 'chart') {
            chart.classList.remove('hidden');
            list.classList.add('hidden');

            btnChart.classList.add('active');
            btnList.classList.remove('active');

            setTimeout(initChart, 100);

        } else {
            list.classList.remove('hidden');
            chart.classList.add('hidden');

            btnList.classList.add('active');
            btnChart.classList.remove('active');
        }
    }
    document.getElementById('btn-chart').addEventListener('click', function () {
        setView('chart', true);
    });

    document.getElementById('btn-list').addEventListener('click', function () {
        setView('list', true);
    });

    document.getElementById('filterForm').addEventListener('submit', function () {
        const view = getView();
        const input = document.getElementById('viewInput');
        if (input) input.value = view;
    });

    function boot() {
        const view = getView();

        const chart = document.getElementById('chart-view');
        const list  = document.getElementById('list-view');

        const btnChart = document.getElementById('btn-chart');
        const btnList  = document.getElementById('btn-list');

        if (view === 'list') {
            chart.classList.add('hidden');
            list.classList.remove('hidden');

            btnList.classList.add('active');
            btnChart.classList.remove('active');
        } else {
            chart.classList.remove('hidden');
            list.classList.add('hidden');

            btnChart.classList.add('active');
            btnList.classList.remove('active');

            setTimeout(initChart, 100);
        }
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', boot);
    } else {
        boot();
    }

})();
</script>
@endpush
