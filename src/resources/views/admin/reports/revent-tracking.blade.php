@extends('layouts.app')

@push('styles')
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600&display=swap" rel="stylesheet" />
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@tabler/icons-webfont@latest/dist/tabler-icons.min.css" />
    <link rel="stylesheet" href="{{ asset('assets/css/dashboard/report/revent.css') }}" data-turbo-track="reload">
@endpush

@section('title', 'Revenue Tracking')

@section('content')

<div class="rv-page">

    {{-- ── Header ── --}}
    <div class="rv-page__header">
        <h1 class="rv-page__title">Revenue tracking</h1>
        <p class="rv-page__subtitle">Daily revenue breakdown with order and average sale metrics.</p>
    </div>

    {{-- ── Filter Bar ── --}}
    <div class="rv-filter-bar">
        <input
            type="text"
            id="searchInput"
            placeholder="Search date..."
            value="{{ request('search') }}"
            autocomplete="off"
        >
        <input
            type="date"
            id="dateFrom"
            value="{{ $startDate }}"
        >
        <input
            type="date"
            id="dateTo"
            value="{{ $endDate }}"
        >
        <button class="btn-filter" id="filter-btn" type="button">
            <i class="ti ti-filter" aria-hidden="true"></i> Filter
        </button>
        <button class="btn-reset" id="reset-btn" type="button">
            <i class="ti ti-refresh" aria-hidden="true"></i> Reset
        </button>
    </div>

    {{-- ── KPI Cards ── --}}
    <div class="rv-kpi-row">
        <div class="rv-kpi-card">
            <p class="rv-kpi-card__label">TOTAL ORDERS</p>
            <p class="rv-kpi-card__value" id="summary-orders">
                {{ number_format($summary['total_orders']) }}
            </p>
        </div>
        <div class="rv-kpi-card">
            <p class="rv-kpi-card__label">TOTAL REVENUE</p>
            <p class="rv-kpi-card__value rv-kpi-card__value--blue" id="summary-revenue">
                ${{ number_format($summary['total_revenue'], 2) }}
            </p>
        </div>
        <div class="rv-kpi-card">
            <p class="rv-kpi-card__label">AVERAGE SALE</p>
            <p class="rv-kpi-card__value" id="summary-avg">
                ${{ number_format($summary['average_sale'], 2) }}
            </p>
        </div>
    </div>

    {{-- ── Table Panel ── --}}
    <div class="rv-panel">

        <div class="rv-panel__header">
            <div>
                <p class="rv-panel__title">Daily breakdown</p>
                <p class="rv-panel__sub">Revenue grouped by date</p>
            </div>
        </div>

        <div class="table-responsive">
            <table class="table-custom" id="salesTable">
                <thead>
                    <tr>
                        <th>Date</th>
                        <th>Total orders</th>
                        <th>Total revenue</th>
                        <th>Average sale</th>
                    </tr>
                </thead>
                <tbody id="unit-table-body">
                    @forelse ($rows as $row)
                        <tr>
                            <td>
                                <div class="rv-date-cell">
                                    <div class="rv-date-icon">
                                        <i class="ti ti-calendar" aria-hidden="true"></i>
                                    </div>
                                    {{ $row['date'] }}
                                </div>
                            </td>
                            <td>{{ number_format($row['total_orders']) }}</td>
                            <td class="rv-value">${{ number_format($row['total_revenue'], 2) }}</td>
                            <td>${{ number_format($row['average_sale'], 2) }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="rv-state-cell">No data found.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{-- ── Pagination ── --}}
        <div class="pm-pagination">
            <div class="pm-pagination__meta">
                <span class="pm-pagination__text" id="pagination-info"></span>
                <div class="pm-pagination__per-page">
                    <label for="per-page-select">Show:</label>
                    <select id="per-page-select" class="pm-pagination__select">
                        @foreach ([15, 25, 50] as $size)
                            <option
                                value="{{ $size }}"
                                {{ $pagination['per_page'] == $size ? 'selected' : '' }}
                            >
                                {{ $size }}
                            </option>
                        @endforeach
                    </select>
                </div>
            </div>
            <div class="pm-pagination__links" id="paginationLinks"></div>
        </div>

    </div>

</div>

@endsection

@push('scripts')
<script>
(function () {

    const BASE_URL      = '{{ route('admin.revenue-tracking') }}';
    const DEFAULT_FROM  = '{{ now()->subDays(29)->format('Y-m-d') }}';
    const DEFAULT_TO    = '{{ now()->format('Y-m-d') }}';

    const els = {
        search      : document.getElementById('searchInput'),
        dateFrom    : document.getElementById('dateFrom'),
        dateTo      : document.getElementById('dateTo'),
        filterBtn   : document.getElementById('filter-btn'),
        resetBtn    : document.getElementById('reset-btn'),
        perPage     : document.getElementById('per-page-select'),
        tbody       : document.getElementById('unit-table-body'),
        pgInfo      : document.getElementById('pagination-info'),
        pgLinks     : document.getElementById('paginationLinks'),
        sumOrders   : document.getElementById('summary-orders'),
        sumRevenue  : document.getElementById('summary-revenue'),
        sumAvg      : document.getElementById('summary-avg'),
    };

    // state
    let state = {
        search   : '{{ request('search', '') }}',
        dateFrom : '{{ $startDate }}',
        dateTo   : '{{ $endDate }}',
        perPage  : {{ $pagination['per_page'] }},
        page     : {{ $pagination['current_page'] }},
    };
    // fetch data
    async function fetchData() {
        setLoading(true);

        const params = new URLSearchParams({
            start_date : state.dateFrom,
            end_date   : state.dateTo,
            per_page   : state.perPage,
            page       : state.page,
        });

        if (state.search) params.set('search', state.search);

        try {
            const res = await fetch(`${BASE_URL}?${params}`, {
                headers: {
                    'X-Requested-With' : 'XMLHttpRequest',
                    'Accept'           : 'application/json',
                    'X-CSRF-TOKEN'     : document.querySelector('meta[name="csrf-token"]').content,
                },
            });

            if (!res.ok) throw new Error(`Server error: ${res.status}`);

            const data = await res.json();

            renderSummary(data.summary);
            renderTable(data.rows);
            renderPagination(data.pagination);

            window.history.replaceState({}, '', `${BASE_URL}?${params}`);

        } catch (err) {
            console.error('Revenue tracking fetch error:', err);
            els.tbody.innerHTML = `
                <tr>
                    <td colspan="4" class="rv-state-cell rv-state-cell--error">
                        <i class="ti ti-alert-circle" aria-hidden="true"></i>
                        Failed to load data. Please try again.
                    </td>
                </tr>`;
        } finally {
            setLoading(false);
        }
    }
    // summary cards
    function renderSummary(summary) {
        els.sumOrders.textContent  = Number(summary.total_orders).toLocaleString();
        els.sumRevenue.textContent = '$' + Number(summary.total_revenue).toLocaleString('en-US', { minimumFractionDigits: 2 });
        els.sumAvg.textContent     = '$' + Number(summary.average_sale).toLocaleString('en-US', { minimumFractionDigits: 2 });
    }

    function renderTable(rows) {
        if (!rows?.length) {
            els.tbody.innerHTML = `
                <tr>
                    <td colspan="4" class="rv-state-cell">No data found.</td>
                </tr>`;
            return;
        }

        els.tbody.innerHTML = rows.map(row => `
            <tr>
                <td>
                    <div class="rv-date-cell">
                        <div class="rv-date-icon">
                            <i class="ti ti-calendar" aria-hidden="true"></i>
                        </div>
                        ${row.date}
                    </div>
                </td>
                <td>${Number(row.total_orders).toLocaleString()}</td>
                <td class="rv-value">
                    $${Number(row.total_revenue).toLocaleString('en-US', { minimumFractionDigits: 2 })}
                </td>
                <td>
                    $${Number(row.average_sale).toLocaleString('en-US', { minimumFractionDigits: 2 })}
                </td>
            </tr>
        `).join('');
    }
    // render pagination
    function renderPagination({ current_page, last_page, from, to, total }) {
        els.pgInfo.textContent = total > 0
            ? `Showing ${from}–${to} of ${total} results`
            : 'No results';

        let html = `
            <button class="pm-pagination__btn"
                data-page="${current_page - 1}"
                ${current_page === 1 ? 'disabled' : ''}>
                <i class="ti ti-chevron-left" aria-hidden="true"></i>
            </button>`;

        let lastPrinted = 0;

        for (let i = 1; i <= last_page; i++) {
            const isEdge = i === 1 || i === last_page;
            const isNear = Math.abs(i - current_page) <= 1;

            if (isEdge || isNear) {
                if (lastPrinted && i - lastPrinted > 1) {
                    html += `<span class="pm-pagination__ellipsis">…</span>`;
                }
                html += `
                    <button class="pm-pagination__btn ${i === current_page ? 'active' : ''}"
                        data-page="${i}">
                        ${i}
                    </button>`;
                lastPrinted = i;
            }
        }

        html += `
            <button class="pm-pagination__btn"
                data-page="${current_page + 1}"
                ${current_page === last_page ? 'disabled' : ''}>
                <i class="ti ti-chevron-right" aria-hidden="true"></i>
            </button>`;

        els.pgLinks.innerHTML = html;
    }

    // loading state
    function setLoading(loading) {
        els.filterBtn.disabled = loading;
        els.filterBtn.innerHTML = loading
            ? '<i class="ti ti-loader-2 ti-spin" aria-hidden="true"></i> Loading...'
            : '<i class="ti ti-filter" aria-hidden="true"></i> Filter';

        if (loading) {
            els.tbody.innerHTML = `
                <tr>
                    <td colspan="4" class="rv-state-cell">
                        <i class="ti ti-loader-2 ti-spin" aria-hidden="true"></i> Loading...
                    </td>
                </tr>`;
        }
    }

    // events
    els.filterBtn.addEventListener('click', () => {
        state.search   = els.search.value.trim();
        state.dateFrom = els.dateFrom.value;
        state.dateTo   = els.dateTo.value;
        state.page     = 1;
        fetchData();
    });

    els.resetBtn.addEventListener('click', () => {
        els.search.value   = '';
        els.dateFrom.value = DEFAULT_FROM;
        els.dateTo.value   = DEFAULT_TO;

        state = {
            search   : '',
            dateFrom : DEFAULT_FROM,
            dateTo   : DEFAULT_TO,
            perPage  : 15,
            page     : 1,
        };

        fetchData();
    });

    els.perPage.addEventListener('change', function () {
        state.perPage = parseInt(this.value);
        state.page    = 1;
        fetchData();
    });

    els.pgLinks.addEventListener('click', function (e) {
        const btn = e.target.closest('[data-page]');
        if (!btn || btn.disabled) return;
        state.page = parseInt(btn.dataset.page);
        fetchData();
    });

    els.search.addEventListener('keydown', function (e) {
        if (e.key !== 'Enter') return;
        state.search = this.value.trim();
        state.page   = 1;
        fetchData();
    });

    els.dateFrom.addEventListener('change', function () {
        els.dateTo.min = this.value;
        if (els.dateTo.value && els.dateTo.value < this.value) {
            els.dateTo.value = this.value;
        }
    });
    // render initial pagination from server data
    renderPagination({
        total        : {{ $pagination['total'] }},
        per_page     : {{ $pagination['per_page'] }},
        current_page : {{ $pagination['current_page'] }},
        last_page    : {{ $pagination['last_page'] }},
        from         : {{ $pagination['from'] }},
        to           : {{ $pagination['to'] }},
    });

})();
</script>
@endpush
