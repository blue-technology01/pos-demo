@extends('layouts.app')

@push('styles')
    <link rel="stylesheet" href="{{ asset('assets/css/dashboard/stock/update-stock.css') }}">
@endpush

@section('title', 'Inventory Management')

@section('content')
<div class="pm-wrapper">

    {{-- Summary stat cards --}}
    <div class="stat-grid">
        <div class="stat-card">
            <div class="stat-label">Total products</div>
            <div class="stat-value">148</div>
        </div>
        <div class="stat-card">
            <div class="stat-label">Low stock items</div>
            <div class="stat-value warning">12</div>
        </div>
        <div class="stat-card">
            <div class="stat-label">Out of stock</div>
            <div class="stat-value danger">3</div>
        </div>
        <div class="stat-card">
            <div class="stat-label">Healthy stock</div>
            <div class="stat-value success">133</div>
        </div>
    </div>

    {{-- Info banner --}}
    <div class="automation-banner">
        <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none"
            stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
            <circle cx="12" cy="12" r="10"></circle>
            <line x1="12" y1="8" x2="12" y2="12"></line>
            <line x1="12" y1="16" x2="12.01" y2="16"></line>
        </svg>
        <div>
            <strong>System automation active</strong>
            <p>Stock levels update automatically after every POS sale is completed.</p>
        </div>
    </div>

    {{-- Tabs --}}
    <div class="tab-bar">
        <button class="tab-btn active" data-tab="overview">
            <svg xmlns="http://www.w3.org/2000/svg" width="15" height="15" viewBox="0 0 24 24" fill="none"
                stroke="currentColor" stroke-width="2"><rect x="2" y="3" width="20" height="14" rx="2"/><line x1="8" y1="21" x2="16" y2="21"/><line x1="12" y1="17" x2="12" y2="21"/></svg>
            Stock overview
        </button>
        <button class="tab-btn" data-tab="lowstock">
            <svg xmlns="http://www.w3.org/2000/svg" width="15" height="15" viewBox="0 0 24 24" fill="none"
                stroke="currentColor" stroke-width="2"><path d="M10.29 3.86L1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z"/><line x1="12" y1="9" x2="12" y2="13"/><line x1="12" y1="17" x2="12.01" y2="17"/></svg>
            Low stock alert
        </button>
        <button class="tab-btn" data-tab="activity">
            <svg xmlns="http://www.w3.org/2000/svg" width="15" height="15" viewBox="0 0 24 24" fill="none"
                stroke="currentColor" stroke-width="2"><polyline points="22 12 18 12 15 21 9 3 6 12 2 12"/></svg>
            Stock activity
        </button>
    </div>

    {{-- Search & sync bar --}}
    <div class="search-bar">
        <div class="search-wrap">
            <svg class="search-icon" xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24"
                fill="none" stroke="currentColor" stroke-width="2.5">
                <circle cx="11" cy="11" r="8"></circle>
                <line x1="21" y1="21" x2="16.65" y2="16.65"></line>
            </svg>
            <input type="text" id="product-search" placeholder="Search product name, code or invoice...">
        </div>
        <div class="sync-badge">
            <span class="sync-dot"></span>
            Live sync active
        </div>
    </div>

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
                    {{-- Example rows — replace with @foreach($products as $p) --}}
                    <tr>
                        <td><div class="product-img-placeholder"></div></td>
                        <td class="code-cell">PRD-001</td>
                        <td class="name-cell">Instant noodles</td>
                        <td>Food</td>
                        <td>
                            <span class="stock-num warning">5</span>
                        </td>
                        <td class="muted">10</td>
                        <td class="muted">pack</td>
                        <td><span class="status-badge warning">Low stock</span></td>
                        <td class="muted">30 May 2026</td>
                    </tr>
                    <tr>
                        <td><div class="product-img-placeholder"></div></td>
                        <td class="code-cell">PRD-002</td>
                        <td class="name-cell">Mineral water</td>
                        <td>Drinks</td>
                        <td>
                            <span class="stock-num danger">0</span>
                        </td>
                        <td class="muted">20</td>
                        <td class="muted">bottle</td>
                        <td><span class="status-badge danger">Out of stock</span></td>
                        <td class="muted">30 May 2026</td>
                    </tr>
                    <tr>
                        <td><div class="product-img-placeholder"></div></td>
                        <td class="code-cell">PRD-003</td>
                        <td class="name-cell">Coca-Cola can</td>
                        <td>Drinks</td>
                        <td>
                            <span class="stock-num success">99</span>
                        </td>
                        <td class="muted">20</td>
                        <td class="muted">can</td>
                        <td><span class="status-badge success">In stock</span></td>
                        <td class="muted">30 May 2026</td>
                    </tr>
                </tbody>
            </table>
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
                    <tr>
                        <td><div class="product-img-placeholder"></div></td>
                        <td class="code-cell">PRD-001</td>
                        <td class="name-cell">Instant noodles</td>
                        <td>Food</td>
                        <td><span class="stock-num warning">5</span></td>
                        <td class="muted">10</td>
                        <td><span class="shortage">−5</span></td>
                        <td>
                            <div class="stock-bar">
                                <div class="stock-fill warning" style="width: 50%"></div>
                            </div>
                        </td>
                        <td><span class="status-badge warning">Low stock</span></td>
                    </tr>
                    <tr>
                        <td><div class="product-img-placeholder"></div></td>
                        <td class="code-cell">PRD-002</td>
                        <td class="name-cell">Mineral water</td>
                        <td>Drinks</td>
                        <td><span class="stock-num danger">0</span></td>
                        <td class="muted">20</td>
                        <td><span class="shortage">−20</span></td>
                        <td>
                            <div class="stock-bar">
                                <div class="stock-fill danger" style="width: 2%"></div>
                            </div>
                        </td>
                        <td><span class="status-badge danger">Out of stock</span></td>
                    </tr>
                    <tr>
                        <td><div class="product-img-placeholder"></div></td>
                        <td class="code-cell">PRD-007</td>
                        <td class="name-cell">Cooking oil 1L</td>
                        <td>Food</td>
                        <td><span class="stock-num warning">3</span></td>
                        <td class="muted">15</td>
                        <td><span class="shortage">−12</span></td>
                        <td>
                            <div class="stock-bar">
                                <div class="stock-fill warning" style="width: 20%"></div>
                            </div>
                        </td>
                        <td><span class="status-badge warning">Low stock</span></td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>

    {{-- ── TAB 3: Stock activity (from sale_items) ── --}}
    <div id="tab-activity" class="tab-content">
        <div class="table-card">
            <table class="pm-table">
                <thead>
                    <tr>
                        <th>Time & invoice</th>
                        <th>Product sold</th>
                        <th>Sold by</th>
                        <th style="text-align: center;">Stock reduction (before − sold → after)</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td>
                            <span class="muted time-label">Just now (17:05)</span>
                            <a href="#" class="invoice-link">#INV-2026-0034</a>
                        </td>
                        <td>
                            <span class="name-cell">Instant noodles</span>
                            <span class="muted category-label">Food</span>
                        </td>
                        <td class="muted">Dara Sok</td>
                        <td>
                            <div class="ledger-box">
                                <span class="ledger-before">45</span>
                                <span class="ledger-sep">−</span>
                                <span class="ledger-sold">2</span>
                                <span class="ledger-arrow">→</span>
                                <span class="ledger-after success">43 left</span>
                            </div>
                        </td>
                    </tr>
                    <tr>
                        <td>
                            <span class="muted time-label">3 mins ago (17:02)</span>
                            <a href="#" class="invoice-link">#INV-2026-0033</a>
                        </td>
                        <td>
                            <span class="name-cell">Coca-Cola can</span>
                            <span class="muted category-label">Drinks</span>
                        </td>
                        <td class="muted">Maly Chan</td>
                        <td>
                            <div class="ledger-box">
                                <span class="ledger-before">100</span>
                                <span class="ledger-sep">−</span>
                                <span class="ledger-sold">1</span>
                                <span class="ledger-arrow">→</span>
                                <span class="ledger-after success">99 left</span>
                            </div>
                        </td>
                    </tr>
                    <tr>
                        <td>
                            <span class="muted time-label">12 mins ago (16:54)</span>
                            <a href="#" class="invoice-link">#INV-2026-0032</a>
                        </td>
                        <td>
                            <span class="name-cell">Sting strawberry</span>
                            <span class="muted category-label">Drinks</span>
                        </td>
                        <td class="muted">Dara Sok</td>
                        <td>
                            <div class="ledger-box">
                                <span class="ledger-before">12</span>
                                <span class="ledger-sep">−</span>
                                <span class="ledger-sold">4</span>
                                <span class="ledger-arrow">→</span>
                                <span class="ledger-after warning">8 left (Low)</span>
                            </div>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
        <div class="table-footer">
            <span>Showing latest stock activity from today's sales</span>
            <span class="page-badge">Page 1 of 1</span>
        </div>
    </div>

</div>
@endsection

@push('scripts')
<script>
    const tabs = document.querySelectorAll('.tab-btn');
    const contents = document.querySelectorAll('.tab-content');

    tabs.forEach(btn => {
        btn.addEventListener('click', () => {
            tabs.forEach(t => t.classList.remove('active'));
            contents.forEach(c => c.classList.remove('active'));
            btn.classList.add('active');
            document.getElementById('tab-' + btn.dataset.tab).classList.add('active');
        });
    });

    document.getElementById('product-search').addEventListener('input', function () {
        const q = this.value.toLowerCase();
        document.querySelectorAll('.tab-content.active .pm-table tbody tr').forEach(row => {
            row.style.display = row.textContent.toLowerCase().includes(q) ? '' : 'none';
        });
    });
</script>
@endpush
