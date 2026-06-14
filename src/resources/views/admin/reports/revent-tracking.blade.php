@extends('layouts.app')

@push('styles')
    <link rel="stylesheet" href="{{ asset('assets/css/dashboard/report/revent.css') }}" data-turbo-track="reload">
@endpush

@section('title', 'Revenue Tracking')

@section('content')
    <div class="unit-section">
        <div class="unit-section__header">
            <div class="search-wrap">
                <input type="text" id="searchInput" placeholder="Search date...">
                <input type="date" class="filter-date" id="dateFrom" value="{{ $startDate }}">
                <input type="date" class="filter-date" id="dateTo" value="{{ $endDate }}">
                <button class="btn-filter" id="filter-btn">
                    <i class="ti ti-filter"></i> Filter
                </button>
                <button class="btn-reset" id="reset-btn">
                    <i class="ti ti-refresh"></i> Reset
                </button>
            </div>
        </div>

        {{-- Summary Cards --}}
        <div class="summary-cards">
            <div class="summary-card">
                <p class="summary-card__label">Total Orders</p>
                <p class="summary-card__value" id="summary-orders">
                    {{ number_format($summary['total_orders']) }}
                </p>
            </div>
            <div class="summary-card">
                <p class="summary-card__label">Total Revenue</p>
                <p class="summary-card__value" id="summary-revenue">
                    ${{ number_format($summary['total_revenue'], 2) }}
                </p>
            </div>
            <div class="summary-card">
                <p class="summary-card__label">Average Sale</p>
                <p class="summary-card__value" id="summary-avg">
                    ${{ number_format($summary['average_sale'], 2) }}
                </p>
            </div>
        </div>

        <div class="unit-card">
            <div class="table-responsive">
                <table class="table-custom" id="salesTable">
                    <thead>
                        <tr>
                            <th>Date</th>
                            <th>Total Orders</th>
                            <th>Total Revenue</th>
                            <th>Average Sale</th>
                        </tr>
                    </thead>
                    <tbody id="unit-table-body">
                        @forelse ($rows as $row)
                            <tr>
                                <td>{{ $row['date'] }}</td>
                                <td>{{ number_format($row['total_orders']) }}</td>
                                <td>${{ number_format($row['total_revenue'], 2) }}</td>
                                <td>${{ number_format($row['average_sale'], 2) }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" style="text-align:center;padding:2rem">No data found</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            {{-- Pagination --}}
            <div class="pm-pagination">
                <div class="pm-pagination__meta">
                    <span class="pm-pagination__text" id="pagination-info"></span>
                    <div class="pm-pagination__per-page">
                        <label for="per-page-select">Show:</label>
                        <select id="per-page-select" class="pm-pagination__select">
                            <option value="15" {{ $pagination['per_page'] == 15 ? 'selected' : '' }}>15</option>
                            <option value="25" {{ $pagination['per_page'] == 25 ? 'selected' : '' }}>25</option>
                            <option value="50" {{ $pagination['per_page'] == 50 ? 'selected' : '' }}>50</option>
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
    const BASE_URL = '{{ route('admin.revenue-tracking') }}';

    let state = {
        search:   '',
        dateFrom: '{{ $startDate }}',
        dateTo:   '{{ $endDate }}',
        perPage:  {{ $pagination['per_page'] }},
        page:     {{ $pagination['current_page'] }},
    };

    const searchInput     = document.getElementById('searchInput');
    const dateFrom        = document.getElementById('dateFrom');
    const dateTo          = document.getElementById('dateTo');
    const filterBtn       = document.getElementById('filter-btn');
    const resetBtn        = document.getElementById('reset-btn');
    const perPageSelect   = document.getElementById('per-page-select');
    const tbody           = document.getElementById('unit-table-body');
    const paginationInfo  = document.getElementById('pagination-info');
    const paginationLinks = document.getElementById('paginationLinks');
    const summaryOrders   = document.getElementById('summary-orders');
    const summaryRevenue  = document.getElementById('summary-revenue');
    const summaryAvg      = document.getElementById('summary-avg');

    async function fetchData() {
        tbody.innerHTML = `<tr><td colspan="4" style="text-align:center;padding:2rem">
            <i class="ti ti-loader"></i> Loading...
        </td></tr>`;
        filterBtn.disabled  = true;
        filterBtn.innerHTML = '<i class="ti ti-loader"></i> Loading...';

        const params = new URLSearchParams({
            start_date: state.dateFrom,
            end_date:   state.dateTo,
            per_page:   state.perPage,
            page:       state.page,
        });

        if (state.search) params.set('search', state.search);

        try {
            const response = await fetch(`${BASE_URL}?${params}`, {
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'application/json',
                }
            });

            if (!response.ok) throw new Error('Server error');

            const data = await response.json();

            renderTable(data.rows);
            renderSummary(data.summary);
            renderPagination(data.pagination);

            window.history.pushState({}, '', `${BASE_URL}?${params}`);

        } catch (error) {
            console.error('Fetch error:', error);
            tbody.innerHTML = `<tr><td colspan="4" style="text-align:center;color:red;padding:2rem">
                Failed to load data. Please try again.
            </td></tr>`;
        } finally {
            filterBtn.disabled  = false;
            filterBtn.innerHTML = '<i class="ti ti-filter"></i> Filter';
        }
    }

    function renderTable(rows) {
        if (!rows || !rows.length) {
            tbody.innerHTML = `<tr><td colspan="4" style="text-align:center;padding:2rem">No data found</td></tr>`;
            return;
        }
        tbody.innerHTML = rows.map(row => `
            <tr>
                <td>${row.date}</td>
                <td>${Number(row.total_orders).toLocaleString()}</td>
                <td>$${Number(row.total_revenue).toLocaleString(undefined, { minimumFractionDigits: 2 })}</td>
                <td>$${Number(row.average_sale).toLocaleString(undefined, { minimumFractionDigits: 2 })}</td>
            </tr>
        `).join('');
    }

    function renderSummary(summary) {
        summaryOrders.textContent  = Number(summary.total_orders).toLocaleString();
        summaryRevenue.textContent = '$' + Number(summary.total_revenue).toLocaleString(undefined, { minimumFractionDigits: 2 });
        summaryAvg.textContent     = '$' + Number(summary.average_sale).toLocaleString(undefined, { minimumFractionDigits: 2 });
    }

    function renderPagination(pagination) {
        const { current_page, last_page, from, to, total } = pagination;

        paginationInfo.textContent = total > 0
            ? `Showing ${from} - ${to} of ${total} results`
            : 'No results';

        let links = '';

        links += `<button class="pm-pagination__btn" data-page="${current_page - 1}"
            ${current_page === 1 ? 'disabled' : ''}>
            <i class="ti ti-chevron-left"></i>
        </button>`;

        let lastPrinted = 0;
        for (let i = 1; i <= last_page; i++) {
            const isEdge = i === 1 || i === last_page;
            const isNear = i >= current_page - 1 && i <= current_page + 1;

            if (isEdge || isNear) {
                if (lastPrinted && i - lastPrinted > 1) {
                    links += `<span class="pm-pagination__ellipsis">...</span>`;
                }
                links += `<button class="pm-pagination__btn ${i === current_page ? 'active' : ''}"
                    data-page="${i}">${i}</button>`;
                lastPrinted = i;
            }
        }

        links += `<button class="pm-pagination__btn" data-page="${current_page + 1}"
            ${current_page === last_page ? 'disabled' : ''}>
            <i class="ti ti-chevron-right"></i>
        </button>`;

        paginationLinks.innerHTML = links;
    }

    paginationLinks.addEventListener('click', function (e) {
        const btn = e.target.closest('[data-page]');
        if (btn && !btn.disabled) {
            state.page = parseInt(btn.dataset.page);
            fetchData();
        }
    });

    filterBtn.addEventListener('click', function () {
        state.search   = searchInput.value.trim();
        state.dateFrom = dateFrom.value;
        state.dateTo   = dateTo.value;
        state.page     = 1;
        fetchData();
    });

    resetBtn.addEventListener('click', function () {
        const defaultFrom = '{{ now()->subDays(29)->format('Y-m-d') }}';
        const defaultTo   = '{{ now()->format('Y-m-d') }}';

        searchInput.value = '';
        dateFrom.value    = defaultFrom;
        dateTo.value      = defaultTo;

        state = {
            search:   '',
            dateFrom: defaultFrom,
            dateTo:   defaultTo,
            perPage:  25,
            page:     1,
        };
        fetchData();
    });

    perPageSelect.addEventListener('change', function () {
        state.perPage = parseInt(this.value);
        state.page    = 1;
        fetchData();
    });

    searchInput.addEventListener('keyup', function (e) {
        if (e.key === 'Enter') {
            state.search = this.value.trim();
            state.page   = 1;
            fetchData();
        }
    });

    dateFrom.addEventListener('change', function () {
        dateTo.min = this.value;
        if (dateTo.value && dateTo.value < this.value) {
            dateTo.value = this.value;
        }
    });

    renderPagination({
        total:        {{ $pagination['total'] }},
        per_page:     {{ $pagination['per_page'] }},
        current_page: {{ $pagination['current_page'] }},
        last_page:    {{ $pagination['last_page'] }},
        from:         {{ $pagination['from'] }},
        to:           {{ $pagination['to'] }},
    });

})();
</script>
@endpush
