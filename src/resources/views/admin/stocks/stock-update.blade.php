@extends('layouts.app')

@push('styles')
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600&display=swap" rel="stylesheet" />
    <link rel="stylesheet" href="{{ asset('assets/css/dashboard/stock/update-stock.css') }}">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@tabler/icons-webfont@latest/dist/tabler-icons.min.css" />
@endpush

@section('title', 'Stock History')

@section('content')
<div class="pm-wrapper">

    {{-- Summary stat cards --}}
    <div class="stat-grid">
        <div class="stat-card">
            <div class="stat-label">Total products</div>
            <div class="stat-value">{{ $totalProducts }}</div>
        </div>
        <div class="stat-card">
            <div class="stat-label">Low stock items</div>
            <div class="stat-value warning">{{ $lowStock }}</div>
        </div>
        <div class="stat-card">
            <div class="stat-label">Out of stock</div>
            <div class="stat-value danger">{{ $outOfStock }}</div>
        </div>
        <div class="stat-card">
            <div class="stat-label">Healthy stock</div>
            <div class="stat-value success">{{ $healthyStock }}</div>
        </div>
    </div>

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
        <button class="tab-btn" data-tab="activity">
            <svg xmlns="http://www.w3.org/2000/svg" width="15" height="15" viewBox="0 0 24 24" fill="none"
                stroke="currentColor" stroke-width="2">
                <polyline points="22 12 18 12 15 21 9 3 6 12 2 12"/>
            </svg>
            Stock activity
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
                type="text"
                name="search"
                value="{{ request('search') }}"
                placeholder="Search product name, code or invoice..."
            >
        </div>
        {{-- Keep existing filters --}}
        @foreach(request()->except('search', 'page') as $key => $value)
            <input type="hidden" name="{{ $key }}" value="{{ $value }}">
        @endforeach
        <button type="submit" class="btn-filter" onclick="showLoader()" >
            Search
        </button>
        <div class="sync-badge">
            <span class="sync-dot"></span>
            Live sync active
        </div>
    </form>

    {{-- ── TAB 1: Stock overview ── --}}
    <div id="tab-overview" class="tab-content active">
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
                                         class="product-img" style="width: 50px" >
                                @else
                                    <div class="product-img-placeholder"></div>
                                @endif
                            </td>
                            <td>
                                <span class="code-badge">{{ $product->code }}</span>
                            </td>
                            {{-- <td class="code-cell">{{ $product->code }}</td> --}}
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
            <div class="table-footer" style="width: 100%; display: flex; justify-content: space-between" id="tableFooter">
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
                                         class="product-img" style="width: 50px" >
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
            <div class="table-footer" style="width: 100%; display: flex; justify-content: space-between" id="tableFooter">
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

    {{-- ── TAB 3: Stock activity ── --}}
    <div id="tab-activity" class="tab-content">
        <div class="table-card">
            <table class="pm-table">
                <thead>
                    <tr>
                        <th>Time &amp; invoice</th>
                        <th>Product sold</th>
                        <th>Sold by</th>
                        <th style="text-align: center;">Stock reduction (before − sold → after)</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($activities as $item)
                        @php
                            $stockBefore = $item->stock_before ?? '—';
                            $stockAfter  = $item->stock_after  ?? '—';
                            $isLow       = is_numeric($stockAfter)
                                            && $item->product
                                            && $stockAfter < ($item->product->min_stock ?? 0);
                            $afterClass  = is_numeric($stockAfter) && $stockAfter == 0
                                            ? 'danger'
                                            : ($isLow ? 'warning' : 'success');
                            $afterLabel  = is_numeric($stockAfter)
                                            ? ($stockAfter == 0
                                                ? '0 left (Out)'
                                                : ($isLow
                                                    ? "{$stockAfter} left (Low)"
                                                    : "{$stockAfter} left"))
                                            : '—';
                        @endphp
                        <tr>
                            <td>
                                <span class="muted time-label">
                                    {{ $item->created_at?->diffForHumans() ?? '—' }}
                                    {{ $item->created_at ? '(' . $item->created_at->format('H:i') . ')' : '' }}
                                </span>
                                <a href="#" class="invoice-link">
                                    #{{ $item->sale->invoice_no ?? '—' }}
                                </a>
                            </td>
                            <td>
                                <span class="name-cell">{{ $item->product_name }}</span>
                                <span class="muted category-label">
                                    {{ $item->product->category->name ?? '—' }}
                                </span>
                            </td>
                            <td class="muted">
                                {{ $item->sale->user->name ?? '—' }}
                            </td>
                            <td>
                                <div class="ledger-box">
                                    <span class="ledger-before">{{ $stockBefore }}</span>
                                    <span class="ledger-sep">−</span>
                                    <span class="ledger-sold">{{ $item->quantity }}</span>
                                    <span class="ledger-arrow">→</span>
                                    <span class="ledger-after {{ $afterClass }}">
                                        {{ $afterLabel }}
                                    </span>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="empty-state">
                                No stock activity recorded today.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
            {{-- pagination --}}
            <div class="table-footer" style="width: 100%; display: flex; justify-content: space-between" id="tableFooter">
                <span class="table-footer-left">
                    <div class="table-info">
                        showing
                        {{ $activities->firstItem() ?? 0 }}
                        -
                        {{ $activities->lastItem() ?? 0 }}
                        of
                        {{ $activities->total() }}
                        Activities
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
                    {{ $activities->links('vendor.pagination.numbers-only') }}
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    const tabs     = document.querySelectorAll('.tab-btn');
    const contents = document.querySelectorAll('.tab-content');
    const search   = document.getElementById('product-search');

    tabs.forEach(btn => {
        btn.addEventListener('click', () => {
            tabs.forEach(t => t.classList.remove('active'));
            contents.forEach(c => c.classList.remove('active'));
            btn.classList.add('active');
            document.getElementById('tab-' + btn.dataset.tab).classList.add('active');
            search.value = '';
            filterRows('');
        });
    });

    function filterRows(q) {
        document.querySelectorAll('.tab-content.active .pm-table tbody tr').forEach(row => {
            row.style.display = row.textContent.toLowerCase().includes(q) ? '' : 'none';
        });
    }

    search.addEventListener('input', function () {
        filterRows(this.value.toLowerCase());
    });
</script>
@endpush
