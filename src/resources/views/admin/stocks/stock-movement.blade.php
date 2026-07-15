@extends('layouts.app')

@push('styles')
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600&display=swap" rel="stylesheet" />
    <link rel="stylesheet" href="{{ asset('assets/css/dashboard/stock/update-stock.css') }}">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@tabler/icons-webfont@2.47.0/dist/tabler-icons.min.css" />
@endpush

@section('title', 'Stock Movement')

@section('content')

    {{-- ── Filter bar ── --}}
    <form method="GET" action="{{ url()->current() }}" class="search-bar">
        {{-- keep current per_page when filtering --}}
        <input type="hidden" name="per_page" value="{{ request('per_page', 15) }}">

        <div class="search-wrap">
            <svg class="search-icon"
                xmlns="http://www.w3.org/2000/svg"
                width="16" height="16" viewBox="0 0 24 24"
                fill="none" stroke="currentColor" stroke-width="2.5">
                <circle cx="11" cy="11" r="8"></circle>
                <line x1="21" y1="21" x2="16.65" y2="16.65"></line>
            </svg>
            <input
                id="product-search"
                type="text"
                name="product_code"
                value="{{ $filters['product_code'] ?? '' }}"
                placeholder="Search product code."
                autocomplete="off"
            >
        </div>

        <select name="movement_type" class="filter-select">
            <option value="">All movement types</option>
            @foreach(['sale' => 'Sale', 'purchase' => 'Purchase', 'transfer_in' => 'Transfer In', 'transfer_out' => 'Transfer Out', 'adjustment' => 'Adjustment', 'return' => 'Return'] as $value => $label)
                <option value="{{ $value }}" {{ ($filters['movement_type'] ?? '') == $value ? 'selected' : '' }}>
                    {{ $label }}
                </option>
            @endforeach
        </select>

        <input type="date" name="date_from" value="{{ $filters['date_from'] ?? '' }}" class="filter-date">
        <input type="date" name="date_to" value="{{ $filters['date_to'] ?? '' }}" class="filter-date">

        <button type="submit" class="btn-filter">
            Filter
        </button>

        @if(request()->anyFilled(['product_code', 'movement_type', 'date_from', 'date_to']))
            <a href="{{ url()->current() }}" class="btn-outline">
                Clear
            </a>
        @endif

        <div class="sync-badge">
            <span class="sync-dot"></span>
            Live sync active
        </div>
    </form>

    {{-- ── Stock movement table ── --}}
    <div class="table-card">
        <table class="pm-table">
            <thead>
                <tr>
                    <th>Date</th>
                    <th>Product</th>
                    <th>Type</th>
                    <th>Quantity</th>
                    <th>By</th>
                </tr>
            </thead>
            <tbody>
                @forelse($movements as $movement)
                    @php
                        $typeClass = $movement->quantity < 0 ? 'danger' : ($movement->quantity > 0 ? 'success' : 'muted');
                    @endphp
                    <tr>
                        <td class="muted">{{ $movement->created_at?->format('d M Y H:i') ?? '—' }}</td>
                        <td>
                            <span class="code-badge">{{ $movement->product_code }}</span>
                            {{ $movement->product->name ?? '—' }}
                        </td>
                        <td>
                            <span class="status-badge {{ $typeClass }}">
                                {{ ucfirst(str_replace('_', ' ', $movement->movement_type)) }}
                            </span>
                        </td>
                        <td>
                            <span class="stock-num {{ $typeClass }}">
                                {{ sprintf('%+d', $movement->quantity) }}
                            </span>
                        </td>
                        <td class="muted">{{ $movement->createdBy->name ?? '—' }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="empty-state">
                            No stock movements found.
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
                    {{ $movements->firstItem() ?? 0 }}
                    -
                    {{ $movements->lastItem() ?? 0 }}
                    of
                    {{ $movements->total() }}
                    movement(s)
                </div>

                <form method="GET" action="{{ request()->url() }}">
                    @foreach(request()->except('per_page', 'page') as $key => $value)
                        <input type="hidden" name="{{ $key }}" value="{{ $value }}">
                    @endforeach

                    <select name="per_page" onchange="this.form.submit()">
                        <option value="15" {{ request('per_page', 15) == 15 ? 'selected' : '' }}>15</option>
                        <option value="25" {{ request('per_page') == 25 ? 'selected' : '' }}>25</option>
                        <option value="50" {{ request('per_page') == 50 ? 'selected' : '' }}>50</option>
                    </select>
                </form>
            </span>

            <div class="pagination">
                {{ $movements->links('vendor.pagination.numbers-only') }}
            </div>
        </div>
    </div>
@endsection
