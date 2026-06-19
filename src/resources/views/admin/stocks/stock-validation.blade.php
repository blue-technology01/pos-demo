@extends('layouts.app')

@push('styles')
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600&display=swap" rel="stylesheet" />
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@tabler/icons-webfont@latest/dist/tabler-icons.min.css" />
    <link rel="stylesheet" href="{{ asset('assets/css/dashboard/stock/validate-stock.css') }}">
@endpush

@section('title', 'Stock Validation')

@section('content')

<div class="pm-wrapper">

    {{-- ── Page Header ── --}}
    <div class="pm-page-header">
        <div>
            <h1 class="pm-page-title">Stock validation</h1>
            <p class="pm-page-subtitle">Monitor product availability checks and blocked sale attempts.</p>
        </div>
    </div>

    {{-- ── Search / Filter Bar ── --}}
    <form method="GET" action="{{ route('admin.stock-validation') }}" class="pm-search-box">

        {{-- Search --}}
        <div class="pm-search-wrap">
            <i class="ti ti-search pm-search-icon" aria-hidden="true"></i>
            <input
                type="text"
                name="search"
                value="{{ request('search') }}"
                placeholder="Search product..."
                autocomplete="off"
            >
        </div>

        {{-- Status filter --}}
        <div class="filter-wrap">
            <select name="status">
                <option value="">All status</option>
                <option value="allowed"  {{ request('status') === 'allowed'  ? 'selected' : '' }}>Allowed</option>
                <option value="blocked"  {{ request('status') === 'blocked'  ? 'selected' : '' }}>Blocked</option>
            </select>
        </div>

        {{-- Per page --}}
        <div class="filter-wrap">
            <select name="per_page">
                <option value="15" {{ request('per_page', 15) == 15 ? 'selected' : '' }}>15 / page</option>
                <option value="24" {{ request('per_page', 15) == 24 ? 'selected' : '' }}>24 / page</option>
                <option value="50" {{ request('per_page', 15) == 50 ? 'selected' : '' }}>50 / page</option>
            </select>
        </div>

        {{-- Filter button --}}
        <button type="submit" class="pm-btn-filter">
            <i class="ti ti-filter" aria-hidden="true"></i> Filter
        </button>

        {{-- Reset --}}
        @if(request('search') || request('status'))
            <a href="{{ route('admin.stock-validation') }}" class="pm-btn-reset">
                <i class="ti ti-refresh" aria-hidden="true"></i> Reset
            </a>
        @endif

    </form>

    {{-- ── Table ── --}}
    <div class="pm-table-wrap">
        <table class="pm-table">
            <thead>
                <tr>
                    <th>Product name</th>
                    <th>Current stock</th>
                    <th>Requested qty</th>
                    <th>Status</th>
                    <th>System action</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($attempts as $attempt)
                    @php $isBlocked = $attempt->reason !== 'available'; @endphp
                    <tr>
                        <td>
                            <div class="pm-product-cell">
                                <div class="pm-product-icon">
                                    <i class="ti ti-package" aria-hidden="true"></i>
                                </div>
                                {{ $attempt->productUom?->product?->name ?? '—' }}
                            </div>
                        </td>
                        <td>
                            <div style="display:flex;align-items:center;gap:6px">
                                <i class="ti ti-stack-2" aria-hidden="true" style="color:#9ca3af;font-size:14px"></i>
                                {{ number_format($attempt->available_stock) }}
                            </div>
                        </td>
                        <td>
                            <div style="display:flex;align-items:center;gap:6px">
                                <i class="ti ti-shopping-cart" aria-hidden="true" style="color:#9ca3af;font-size:14px"></i>
                                {{ number_format($attempt->requested_qty) }}
                            </div>
                        </td>
                        <td>
                            @if ($isBlocked)
                                <span class="pm-badge pm-badge--blocked">
                                    <i class="ti ti-circle-x" aria-hidden="true"></i> Blocked
                                </span>
                            @else
                                <span class="pm-badge pm-badge--allowed">
                                    <i class="ti ti-circle-check" aria-hidden="true"></i> Allowed
                                </span>
                            @endif
                        </td>
                        <td>
                            <div style="display:flex;align-items:center;gap:6px">
                                <i class="ti ti-info-circle" aria-hidden="true" style="color:#9ca3af;font-size:14px"></i>
                                {{ ucwords(str_replace('_', ' ', $attempt->reason)) }}
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="pm-empty-cell">
                            <i class="ti ti-inbox" aria-hidden="true" style="font-size:28px;display:block;margin:0 auto 8px;color:#d1d5db"></i>
                            No records found
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    {{-- ── Pagination ── --}}
    <div class="pm-pagination">
        <div class="pm-pagination__info">
            <i class="ti ti-list-numbers" aria-hidden="true"></i>
            Showing
            <strong>{{ $attempts->firstItem() ?? 0 }}</strong>
            –
            <strong>{{ $attempts->lastItem() ?? 0 }}</strong>
            of
            <strong>{{ $attempts->total() }}</strong>
            records
        </div>
        <div class="pm-pagination__links">
            {{ $attempts->links() }}
        </div>
    </div>

</div>

@endsection

@push('scripts')
<script>
(function () {
    // Filter dropdown toggle (if needed in future)
    const filterBtn      = document.getElementById('filter-btn');
    const filterDropdown = document.getElementById('filter-dropdown');

    if (filterBtn && filterDropdown) {
        filterBtn.addEventListener('click', (e) => {
            e.stopPropagation();
            filterDropdown.classList.toggle('open');
        });
        document.addEventListener('click', () => {
            filterDropdown.classList.remove('open');
        });
    }
})();
</script>
@endpush
