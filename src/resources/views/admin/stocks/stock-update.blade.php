@extends('layouts.app')

@push('styles')
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600&display=swap" rel="stylesheet" />
    <link rel="stylesheet" href="{{ asset('assets/css/dashboard/stock/update-stock.css') }}">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@tabler/icons-webfont@2.47.0/dist/tabler-icons.min.css" />
@endpush

@section('title', 'Stock History')

@section('content')
{{-- <div class="pm-wrapper"> --}}

    {{-- Tabs --}}
    <div class="tab-bar">
        <button class="tab-btn active" data-tab="overview">
            <svg xmlns="http://www.w3.org/2000/svg" width="15" height="15" viewBox="0 0 24 24" fill="none"
                stroke="currentColor" stroke-width="2">
                <rect x="2" y="3" width="20" height="14" rx="2"/>
                <line x1="8" y1="21" x2="16" y2="21"/>
                <line x1="12" y1="17" x2="12" y2="21"/>
            </svg>
            Stock overview
        </button>
        <button class="tab-btn" data-tab="lowstock">
            <svg xmlns="http://www.w3.org/2000/svg" width="15" height="15" viewBox="0 0 24 24" fill="none"
                stroke="currentColor" stroke-width="2">
                <path d="M10.29 3.86L1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z"/>
                <line x1="12" y1="9" x2="12" y2="13"/>
                <line x1="12" y1="17" x2="12.01" y2="17"/>
            </svg>
            Low stock alert
            @if($lowStock > 0)
                <span class="tab-badge danger">{{ $lowStock }}</span>
            @endif
        </button>
    </div>

    {{-- Search & sync bar --}}
    <form method="GET" action="{{ url()->current() }}" class="search-bar">
        <div class="search-wrap">
            <svg class="search-icon"
                xmlns="http://www.w3.org/2000/svg"
                width="16"
                height="16"
                viewBox="0 0 24 24"
                fill="none"
                stroke="currentColor"
                stroke-width="2.5">
                <circle cx="11" cy="11" r="8"></circle>
                <line x1="21" y1="21" x2="16.65" y2="16.65"></line>
            </svg>
            <input
                id="product-search"
                type="text"
                name="search"
                value="{{ request('search') }}"
                placeholder="Search product name, code."
            >
            <button type="submit" class="btn-filter" onclick="showLoader()">
                Search
            </button>
        </div>
        @foreach(request()->except('search', 'page') as $key => $value)
            <input type="hidden" name="{{ $key }}" value="{{ $value }}">
        @endforeach

        <div class="sync-badge">
            <span class="sync-dot"></span>
            Live sync active
        </div>
    </form>

    {{-- ── TAB 1: Stock overview ── --}}
    <div id="tab-overview" class="tab-content active">

        {{-- Charts --}}
        <div class="chart-row">
            <div class="chart-card chart-card-wide">
                <div class="chart-card-header">
                    <h3>Current stock vs minimum stock</h3>
                    <span class="muted">Showing products on this page</span>
                </div>
                <div id="chart-overview-stock"></div>
            </div>

            <div class="chart-card chart-card-narrow">
                <div class="chart-card-header">
                    <h3>Stock status breakdown</h3>
                    <span class="muted">All products</span>
                </div>
                <div id="chart-overview-status"></div>
            </div>
        </div>

        <div class="table-card">
            <table class="pm-table">
                <thead>
                    <tr>
                        <th>Image</th>
                        <th>Code</th>
                        <th>Name</th>
                        <th>Category</th>
                        <th>Current stock</th>
                        <th>Min stock</th>
                        <th>Unit</th>
                        <th>Status</th>
                        <th>Last updated</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($products as $product)
                        @php
                            $stockClass = match (true) {
                                $product->stock == 0                          => 'danger',
                                $product->stock < ($product->min_stock ?? 0)  => 'warning',
                                default                                        => 'success',
                            };
                            $statusLabel = match ($stockClass) {
                                'danger'  => 'Out of Stock',
                                'warning' => 'Low Stock',
                                default   => 'In Stock',
                            };
                        @endphp
                        <tr>
                            <td>
                                @if($product->image)
                                    <img src="{{ asset('storage/' . $product->image) }}"
                                         alt="{{ $product->name }}"
                                         class="product-img">
                                @else
                                    <div class="product-img-placeholder"></div>
                                @endif
                            </td>
                            <td>
                                <span class="code-badge">{{ $product->code }}</span>
                            </td>
                            <td class="name-cell">{{ $product->name }}</td>
                            <td>{{ $product->category->name ?? '—' }}</td>
                            <td>
                                <span class="stock-num {{ $stockClass }}">
                                    {{ number_format($product->stock) }}
                                </span>
                            </td>
                            <td class="muted">{{ number_format($product->min_stock ?? 0) }}</td>
                            <td class="muted">{{ $product->uom_code ?? '—' }}</td>
                            <td>
                                <span class="status-badge {{ $stockClass }}">
                                    {{ $statusLabel }}
                                </span>
                            </td>
                            <td class="muted">
                                {{ $product->updated_at?->format('d M Y') ?? '—' }}
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="9" class="empty-state">
                                No products found.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
            {{-- pagination --}}
            <div class="table-footer" id="tableFooter">
                <span class="table-footer-left">
                    <div class="table-info">
                        showing
                        {{ $products->firstItem() ?? 0 }}
                        -
                        {{ $products->lastItem() ?? 0 }}
                        of
                        {{ $products->total() }}
                        Product
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

    {{-- ── TAB 2: Low stock alert ── --}}
    <div id="tab-lowstock" class="tab-content">

        {{-- Chart: shortage per product --}}
        <div class="chart-card">
            <div class="chart-card-header">
                <h3>Shortage by product</h3>
                <span class="muted">Units needed to reach minimum stock</span>
            </div>
            <div id="chart-lowstock-shortage"></div>
        </div>

        <div class="table-card">
            <table class="pm-table">
                <thead>
                    <tr>
                        <th>Image</th>
                        <th>Code</th>
                        <th>Name</th>
                        <th>Category</th>
                        <th>Current stock</th>
                        <th>Min stock</th>
                        <th>Shortage</th>
                        <th>Level</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($lowStockProducts as $product)
                        @php
                            $minStock   = $product->min_stock ?? 1;
                            $shortage   = $minStock - $product->stock;
                            $fillPct    = $minStock > 0
                                            ? min(100, round(($product->stock / $minStock) * 100))
                                            : 0;
                            $stockClass = $product->stock == 0 ? 'danger' : 'warning';
                            $label      = $product->stock == 0 ? 'Out of stock' : 'Low stock';
                        @endphp
                        <tr>
                            <td>
                                @if($product->image)
                                    <img src="{{ asset('storage/' . $product->image) }}"
                                         alt="{{ $product->name }}"
                                         class="product-img">
                                @else
                                    <div class="product-img-placeholder"></div>
                                @endif
                            </td>
                            <td>
                                <span class="code-badge">{{ $product->code }}</span>
                            </td>
                            <td class="name-cell">{{ $product->name }}</td>
                            <td>{{ $product->category->name ?? '—' }}</td>
                            <td>
                                <span class="stock-num {{ $stockClass }}">
                                    {{ number_format($product->stock) }}
                                </span>
                            </td>
                            <td class="muted">{{ number_format($minStock) }}</td>
                            <td>
                                <span class="shortage">−{{ number_format($shortage) }}</span>
                            </td>
                            <td>
                                <div class="stock-bar">
                                    <div class="stock-fill {{ $stockClass }}"
                                         style="width: {{ max(2, $fillPct) }}%"></div>
                                </div>
                            </td>
                            <td>
                                <span class="status-badge {{ $stockClass }}">{{ $label }}</span>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="9" class="empty-state">
                                All products are well stocked!
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
            {{-- pagination --}}
            <div class="table-footer" id="tableFooter">
                <span class="table-footer-left">
                    <div class="table-info">
                        showing
                        {{ $lowStockProducts->firstItem() ?? 0 }}
                        -
                        {{ $lowStockProducts->lastItem() ?? 0 }}
                        of
                        {{ $lowStockProducts->total() }}
                        Low stock
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
                    {{ $lowStockProducts->links('vendor.pagination.numbers-only') }}
                </div>
            </div>
        </div>
    </div>

{{-- </div> --}}
@endsection
@push('scripts')
{{-- ApexCharts --}}
<script src="https://cdn.jsdelivr.net/npm/apexcharts@3.49.0/dist/apexcharts.min.js"></script>

<script>
    // tab
    const tabs     = document.querySelectorAll('.tab-btn');
    const contents = document.querySelectorAll('.tab-content');
    const search   = document.getElementById('product-search');

    tabs.forEach(btn => {
        btn.addEventListener('click', () => {
            tabs.forEach(t => t.classList.remove('active'));
            contents.forEach(c => c.classList.remove('active'));
            btn.classList.add('active');
            document.getElementById('tab-' + btn.dataset.tab).classList.add('active');
            if (search) {
                search.value = '';
                filterRows('');
            }
        });
    });

    function filterRows(q) {
        document.querySelectorAll('.tab-content.active .pm-table tbody tr').forEach(row => {
            row.style.display = row.textContent.toLowerCase().includes(q) ? '' : 'none';
        });
    }

    if (search) {
        search.addEventListener('input', function () {
            filterRows(this.value.toLowerCase());
        });
    }
    // chart data from blade
    const overviewNames    = @json($products->pluck('name'));
    const overviewStock    = @json($products->pluck('stock'));
    const overviewMinStock = @json($products->pluck('min_stock')->map(fn ($v) => $v ?? 0));

    const lowStockNames     = @json($lowStockProducts->pluck('name'));
    const lowStockShortages = @json(
        $lowStockProducts->map(fn ($p) => max(0, ($p->min_stock ?? 1) - $p->stock))
    );
    @php
        $totalProducts   = $products->total();
        $lowOrOutCount   = $lowStock;
        $healthyCount    = max(0, $totalProducts - $lowOrOutCount);
    @endphp
    const statusLabels = @json(['Healthy stock', 'Low / out of stock']);
    const statusCounts = @json([$healthyCount, $lowOrOutCount]);

    // chart stock overview current vs min
    if (document.getElementById('chart-overview-stock')) {
        new ApexCharts(document.getElementById('chart-overview-stock'), {
            chart: { type: 'bar', height: 300, toolbar: { show: false } },
            series: [
                { name: 'Current stock', data: overviewStock },
                { name: 'Min stock', data: overviewMinStock },
            ],
            xaxis: {
                categories: overviewNames,
                labels: { rotate: -45, trim: true },
            },
            colors: ['#2e7d32', '#c62828'],
            plotOptions: { bar: { columnWidth: '55%', borderRadius: 3 } },
            dataLabels: { enabled: false },
            legend: { position: 'top' },
            noData: { text: 'No products to chart' },
        }).render();
    }

    // chart low stock shortage
    if (document.getElementById('chart-lowstock-shortage')) {
        new ApexCharts(document.getElementById('chart-lowstock-shortage'), {
            chart: { type: 'bar', height: 300, toolbar: { show: false } },
            series: [{ name: 'Shortage', data: lowStockShortages }],
            xaxis: {
                categories: lowStockNames,
                labels: { rotate: -45, trim: true },
            },
            colors: ['#ef6c00'],
            plotOptions: { bar: { columnWidth: '45%', borderRadius: 3, distributed: false } },
            dataLabels: { enabled: false },
            legend: { show: false },
            noData: { text: 'All products are well stocked' },
        }).render();
    }
    // chart stock status breakdown donut
    if (document.getElementById('chart-overview-status')) {
        new ApexCharts(document.getElementById('chart-overview-status'), {
            chart: { type: 'donut', height: 300 },
            series: statusCounts,
            labels: statusLabels,
            colors: ['#2e7d32', '#c62828'],
            legend: { position: 'bottom' },
            dataLabels: { enabled: true, formatter: (val) => val.toFixed(0) + '%' },
            plotOptions: {
                pie: {
                    donut: {
                        labels: {
                            show: true,
                            total: { show: true, label: 'Total products' },
                        },
                    },
                },
            },
            noData: { text: 'No products to chart' },
        }).render();
    }
</script>
@endpush
