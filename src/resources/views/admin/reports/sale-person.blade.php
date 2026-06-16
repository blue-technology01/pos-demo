@extends('layouts.app')

@push('styles')
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@tabler/icons-webfont@latest/dist/tabler-icons.min.css" />
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600&display=swap" rel="stylesheet" />
    <link rel="stylesheet" href="{{ asset('assets/css/dashboard/report/sale-person.css') }}" data-turbo-track="reload">
@endpush

@section('title', 'Sales Performance')

@section('content')

<div class="sp-page">

    {{-- ── Page Header ── --}}
    <div class="sp-page__header">
        <div class="sp-page__title-group">
            <h1 class="sp-page__title">Sales performance report</h1>
            <p class="sp-page__subtitle">Real-time point-of-sale data and staff productivity metrics.</p>
        </div>
        <div class="sp-page__actions">
            <div class="sp-toggle-group">
                <button class="sp-toggle-btn active" id="btn-last30">Last 30 days</button>
                <button class="sp-toggle-btn" id="btn-ytd">Year to date</button>
            </div>
            <button class="sp-export-btn" id="export-btn">
                <i class="ti ti-file-export"></i> Export PDF
            </button>
        </div>
    </div>

    {{-- ── Search / Filter bar ── --}}
    <div class="sp-filter-bar">
        <input type="text"
               id="searchInput"
               value="{{ $search ?? '' }}"
               placeholder="Search by staff name...">
        <input type="date"
               class="filter-date"
               id="dateFrom"
               value="{{ $startDate }}">
        <input type="date"
               class="filter-date"
               id="dateTo"
               value="{{ $endDate }}">
        <button class="btn-filter" id="filter-btn">
            <i class="ti ti-filter"></i> Filter
        </button>
        <button class="btn-reset" id="reset-btn">
            <i class="ti ti-refresh"></i> Reset
        </button>
    </div>

    {{-- ── KPI Cards ── --}}
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
                <p class="sp-kpi-card__value" style="font-size:14px; color: var(--color-text-secondary)">No data</p>
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

    {{-- ── Bottom Two-column layout ── --}}
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
                    <tbody id="unit-table-body">
                        @forelse($rows as $i => $row)
                            <tr>
                                {{-- Rank --}}
                                <td>
                                    <span class="sp-rank">
                                        #{{ str_pad($pagination['from'] + $i, 2, '0', STR_PAD_LEFT) }}
                                    </span>
                                </td>
                                <td>
                                    <div class="sp-staff-cell">
                                        <div class="sp-avatar sp-avatar--blue">
                                            {{ strtoupper(substr($row['staff_name'], 0, 2)) }}
                                        </div>
                                        <span class="sp-staff-name">{{ $row['staff_name'] }}</span>
                                    </div>
                                </td>
                                <td>${{ number_format($row['total_revenue'], 2) }}</td>
                                <td>{{ number_format($row['total_orders']) }}</td>
                                <td>${{ number_format($row['avg_per_order'], 2) }}</td>
                                <td>
                                    @if(($row['performance'] ?? 0) >= 50)
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

            {{-- ── Pagination ── --}}
            <div class="pm-pagination">
                <div class="pm-pagination__meta">
                    <span class="pm-pagination__text" id="pagination-info">
                        Showing {{ $pagination['from'] }}–{{ $pagination['to'] }} of {{ $pagination['total'] }} results
                    </span>
                    <div class="pm-pagination__per-page">
                        <label for="per-page-select">Show:</label>
                        <select id="per-page-select" class="pm-pagination__select">
                            @foreach([15, 25, 50] as $n)
                                <option value="{{ $n }}" {{ ($perPage ?? 15) == $n ? 'selected' : '' }}>
                                    {{ $n }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <div class="pm-pagination__links" id="paginationLinks">
                    <button class="pm-pagination__btn"
                            {{ $pagination['current_page'] <= 1 ? 'disabled' : '' }}
                            onclick="goToPage({{ $pagination['current_page'] - 1 }})">
                        <i class="ti ti-chevron-left"></i>
                    </button>

                    <div class="pm-pagination__pages">
                        @for($p = 1; $p <= $pagination['last_page']; $p++)
                            @if($p === 1 || $p === $pagination['last_page'] || abs($p - $pagination['current_page']) <= 1)
                                <button class="pm-pagination__btn {{ $p == $pagination['current_page'] ? 'active' : '' }}"
                                        onclick="goToPage({{ $p }})">
                                    {{ $p }}
                                </button>
                            @elseif(abs($p - $pagination['current_page']) === 2)
                                <span style="padding:0 2px; color:var(--color-text-secondary)">…</span>
                            @endif
                        @endfor
                    </div>

                    <button class="pm-pagination__btn"
                            {{ $pagination['current_page'] >= $pagination['last_page'] ? 'disabled' : '' }}
                            onclick="goToPage({{ $pagination['current_page'] + 1 }})">
                        <i class="ti ti-chevron-right"></i>
                    </button>
                </div>
            </div>
        </div>

        <div class="sp-panel sp-panel--side">
            <div class="sp-panel__header">
                <div>
                    <p class="sp-panel__title">Revenue Share</p>
                    <p class="sp-panel__sub">Contribution by salesperson</p>
                </div>
            </div>

            {{-- Donut Chart --}}
            <div class="sp-chart-wrap">
                <div class="sp-chart-container">
                    <div id="regionChart"></div>
                    <div class="sp-chart-center">
                        <span class="sp-chart-center__val">$0k</span>
                        <span class="sp-chart-center__lbl">Total</span>
                    </div>
                </div>
            </div>

            {{-- Legend --}}
            <div class="sp-region-list"></div>
        </div>

    </div>
</div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/apexcharts"></script>
<script src="{{ asset('assets/js/dashboard/report/sale-person.js') }}"></script>
<script>
(function () {

    const ROUTE_URL   = '{{ route('admin.sale-person') }}';
    const staffColors = [
        '#2563eb','#38bdf8','#7c3aed','#93c5fd',
        '#22c55e','#f43f5e','#f59e0b','#10b981',
        '#ef4444','#8b5cf6',
    ];

    let chart = null;

    // ── Default date range ─────────────────────────────────────────────────
    const DEFAULT_START = '{{ now()->subDays(29)->format('Y-m-d') }}';
    const DEFAULT_END   = '{{ now()->format('Y-m-d') }}';
    const YTD_START     = '{{ now()->startOfYear()->format('Y-m-d') }}';

    // ── State ──────────────────────────────────────────────────────────────
    const state = {
        start_date : '{{ $startDate }}',
        end_date   : '{{ $endDate }}',
        search     : @json($search ?? ''),
        per_page   : {{ $perPage ?? 15 }},
        page       : {{ $pagination['current_page'] }},
    };

    // ── Fetch (AJAX) ───────────────────────────────────────────────────────
    function fetchData() {
        const params = new URLSearchParams({
            start_date : state.start_date,
            end_date   : state.end_date,
            search     : state.search,
            per_page   : state.per_page,
            page       : state.page,
        }).toString();

        fetch(`${ROUTE_URL}?${params}`, {
            headers: {
                'X-Requested-With' : 'XMLHttpRequest',
                'Accept'           : 'application/json',
                'X-CSRF-TOKEN'     : document.querySelector('meta[name="csrf-token"]').content,
            }
        })
        .then(r => {
            if (!r.ok) throw new Error(`HTTP error: ${r.status}`);
            return r.json();
        })
        .then(data => {
            renderSummary(data.summary, data.topPerformer);
            renderTable(data.rows, data.pagination);
            renderPagination(data.pagination);
            renderChart(data.chartData);
        })
        .catch(err => console.error('Fetch error:', err));
    }

    // ── Render KPI summary ─────────────────────────────────────────────────
    function renderSummary(summary, top) {
        const totalEl = document.querySelector('.sp-kpi--total-sales');
        if (totalEl) {
            totalEl.textContent = '$' + parseFloat(summary.total_revenue)
                .toLocaleString('en-US', { minimumFractionDigits: 2 });
        }

        const avgEl = document.querySelector('.sp-kpi--avg-order');
        if (avgEl) {
            avgEl.innerHTML = '$' + parseFloat(summary.avg_per_order)
                .toLocaleString('en-US', { minimumFractionDigits: 2 })
                + ' <span class="sp-kpi-card__unit">/ transaction</span>';
        }

        if (top) {
            const nameEl = document.querySelector('.sp-kpi-person__name');
            const subEl  = document.querySelector('.sp-kpi-person__sub');
            const avEl   = document.querySelector('.sp-kpi-person .sp-avatar');
            if (nameEl) nameEl.textContent = top.staff_name;
            if (subEl)  subEl.textContent  = '$' + parseFloat(top.total_revenue)
                .toLocaleString('en-US', { minimumFractionDigits: 2 }) + ' achievement';
            if (avEl)   avEl.textContent   = top.staff_name.substring(0, 2).toUpperCase();
        }
    }

    // ── Render table ───────────────────────────────────────────────────────
    function renderTable(rows, pg) {
        const tbody = document.getElementById('unit-table-body');

        if (!rows || !rows.length) {
            tbody.innerHTML = `
                <tr>
                    <td colspan="6" style="text-align:center;padding:2rem;color:var(--color-text-secondary,#6b7280)">
                        No results found.
                    </td>
                </tr>`;
            return;
        }

        tbody.innerHTML = rows.map((row, i) => {
            const rank     = String(pg.from + i).padStart(2, '0');
            const initials = row.staff_name.substring(0, 2).toUpperCase();
            const trendUp  = (row.performance ?? 0) >= 50;
            const revenue  = parseFloat(row.total_revenue)
                .toLocaleString('en-US', { minimumFractionDigits: 2 });
            const avg      = parseFloat(row.avg_per_order)
                .toLocaleString('en-US', { minimumFractionDigits: 2 });

            return `
            <tr>
                <td><span class="sp-rank">#${rank}</span></td>
                <td>
                    <div class="sp-staff-cell">
                        <div class="sp-avatar sp-avatar--blue">${initials}</div>
                        <span class="sp-staff-name">${row.staff_name}</span>
                    </div>
                </td>
                <td>$${revenue}</td>
                <td>${Number(row.total_orders).toLocaleString()}</td>
                <td>$${avg}</td>
                <td>
                    <span class="sp-trend ${trendUp ? 'sp-trend--up' : 'sp-trend--down'}">
                        <i class="ti ${trendUp ? 'ti-trending-up' : 'ti-trending-down'}"></i>
                    </span>
                </td>
            </tr>`;
        }).join('');
    }

    // ── Render pagination ──────────────────────────────────────────────────
    function renderPagination(pg) {
        document.getElementById('pagination-info').textContent =
            `Showing ${pg.from}–${pg.to} of ${pg.total} results`;

        let html = `
            <button class="pm-pagination__btn" ${pg.current_page <= 1 ? 'disabled' : ''}
                onclick="goToPage(${pg.current_page - 1})">
                <i class="ti ti-chevron-left"></i>
            </button>
            <div class="pm-pagination__pages">`;

        for (let p = 1; p <= pg.last_page; p++) {
            if (p === 1 || p === pg.last_page || Math.abs(p - pg.current_page) <= 1) {
                html += `
                    <button class="pm-pagination__btn ${p === pg.current_page ? 'active' : ''}"
                        onclick="goToPage(${p})">${p}</button>`;
            } else if (Math.abs(p - pg.current_page) === 2) {
                html += `<span style="padding:0 2px;color:var(--color-text-secondary)">…</span>`;
            }
        }

        html += `
            </div>
            <button class="pm-pagination__btn" ${pg.current_page >= pg.last_page ? 'disabled' : ''}
                onclick="goToPage(${pg.current_page + 1})">
                <i class="ti ti-chevron-right"></i>
            </button>`;

        document.getElementById('paginationLinks').innerHTML = html;
    }

    // ── Render ApexCharts donut ────────────────────────────────────────────
    function renderChart(chartData) {
        const labels = chartData.map(d => d.name);
        const data   = chartData.map(d => parseFloat(d.revenue));
        const total  = data.reduce((a, b) => a + b, 0);

        document.querySelector('.sp-chart-center__val').textContent =
            '$' + (total / 1000).toFixed(1) + 'k';

        const options = {
            chart: {
                type    : 'donut',
                width   : 220,
                height  : 220,
                toolbar : { show: false },
            },
            series      : data,
            labels      : labels,
            colors      : staffColors.slice(0, data.length),
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
        };

        if (chart) {
            chart.updateOptions({
                series : data,
                labels : labels,
                colors : staffColors.slice(0, data.length),
            });
        } else {
            chart = new ApexCharts(document.querySelector('#regionChart'), options);
            chart.render();
        }

        // Legend
        document.querySelector('.sp-region-list').innerHTML = chartData.map((d, i) => `
            <div class="sp-region-row">
                <div class="sp-region-left">
                    <span class="sp-region-dot" style="background:${staffColors[i]}"></span>
                    <span class="sp-region-name">${d.name}</span>
                </div>
                <span class="sp-region-val">$${(parseFloat(d.revenue) / 1000).toFixed(1)}k</span>
            </div>
        `).join('');
    }

    // ── Public helpers ─────────────────────────────────────────────────────
    window.goToPage = function (page) {
        state.page = page;
        fetchData();
    };

    // ── Events ─────────────────────────────────────────────────────────────

    // Filter button
    document.getElementById('filter-btn').addEventListener('click', () => {
        state.search     = document.getElementById('searchInput').value.trim();
        state.start_date = document.getElementById('dateFrom').value || DEFAULT_START;
        state.end_date   = document.getElementById('dateTo').value   || DEFAULT_END;
        state.page       = 1;
        fetchData();
    });

    // ✅ Reset button — clear inputs only, restore Last 30 days default
    document.getElementById('reset-btn').addEventListener('click', () => {
        // 1. Reset state back to defaults
        state.search     = '';
        state.start_date = DEFAULT_START;
        state.end_date   = DEFAULT_END;
        state.per_page   = 15;
        state.page       = 1;

        // 2. Sync UI inputs to match the reset state
        document.getElementById('searchInput').value     = '';
        document.getElementById('dateFrom').value        = DEFAULT_START;
        document.getElementById('dateTo').value          = DEFAULT_END;
        document.getElementById('per-page-select').value = 15;

        // 3. Restore "Last 30 days" toggle as active
        document.querySelectorAll('.sp-toggle-btn')
            .forEach(b => b.classList.remove('active'));
        document.getElementById('btn-last30').classList.add('active');

        // 4. Re-fetch — shows all staff within last 30 days
        fetchData();
    });

    // Per-page select
    document.getElementById('per-page-select').addEventListener('change', function () {
        state.per_page = parseInt(this.value, 10);
        state.page     = 1;
        fetchData();
    });

    // Last 30 days / Year to date toggle
    document.querySelectorAll('.sp-toggle-btn').forEach(btn => {
        btn.addEventListener('click', function () {
            document.querySelectorAll('.sp-toggle-btn')
                .forEach(b => b.classList.remove('active'));
            this.classList.add('active');

            if (this.id === 'btn-last30') {
                state.start_date = DEFAULT_START;
                state.end_date   = DEFAULT_END;
            } else {
                state.start_date = YTD_START;
                state.end_date   = DEFAULT_END;
            }

            // Sync date inputs to reflect toggle selection
            document.getElementById('dateFrom').value = state.start_date;
            document.getElementById('dateTo').value   = state.end_date;

            // Keep current search term when switching date range
            state.search = document.getElementById('searchInput').value.trim();
            state.page   = 1;

            fetchData();
        });
    });

    // ── Init — render chart from server-side data on first load ────────────
    renderChart(@json($chartData));

})();
</script>
@endpush
