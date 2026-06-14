@extends('layouts.app')

@push('styles')
    <link rel="stylesheet" href="{{ asset('assets/css/dashboard/report/sale-person.css') }}" data-turbo-track="reload">
@endpush

@section('title', 'Sales Performance')

@section('content')

<div class="sp-page">

    {{-- Page Header --}}
    <div class="sp-page__header">
        <div class="sp-page__title-group">
            <h1 class="sp-page__title">Sales Performance Report</h1>
            <p class="sp-page__subtitle">Real-time point-of-sale data and staff productivity metrics.</p>
        </div>
        <div class="sp-page__actions">
            <div class="sp-toggle-group">
                <button class="sp-toggle-btn active" id="btn-last30">Last 30 Days</button>
                <button class="sp-toggle-btn" id="btn-ytd">Year to Date</button>
            </div>
            <button class="sp-export-btn">
                <i class="ti ti-file-export"></i> Export PDF
            </button>
        </div>
    </div>

    {{-- Search / Filter bar --}}
    <div class="sp-filter-bar">
        <input type="text" id="searchInput" placeholder="Search by staff name...">
        <input type="date" class="filter-date" id="dateFrom">
        <input type="date" class="filter-date" id="dateTo">
        <button class="btn-filter" id="filter-btn">
            <i class="ti ti-filter"></i> Filter
        </button>
        <button class="btn-reset" id="reset-btn">
            <i class="ti ti-refresh"></i> Reset
        </button>
    </div>

    {{-- KPI Cards --}}
    <div class="sp-kpi-row">
        <div class="sp-kpi-card">
            <div class="sp-kpi-card__top">
                <span class="sp-kpi-card__icon"><i class="ti ti-wallet"></i></span>
                <span class="sp-kpi-badge sp-kpi-badge--green">
                    <i class="ti ti-trending-up"></i> +12.5%
                </span>
            </div>
            <p class="sp-kpi-card__label">TOTAL SALES</p>
            <p class="sp-kpi-card__value">$128,430.00</p>
            <div class="sp-kpi-card__bar"></div>
        </div>

        <div class="sp-kpi-card">
            <div class="sp-kpi-card__top">
                <span class="sp-kpi-card__icon"><i class="ti ti-user-star"></i></span>
                <span class="sp-kpi-card__period">This Month</span>
            </div>
            <p class="sp-kpi-card__label">TOP SALESPERSON</p>
            <div class="sp-kpi-person">
                <div class="sp-avatar sp-avatar--teal">A</div>
                <div>
                    <p class="sp-kpi-person__name">Alex Martinez</p>
                    <p class="sp-kpi-person__sub">$14,290 Achievement</p>
                </div>
            </div>
        </div>

        <div class="sp-kpi-card">
            <div class="sp-kpi-card__top">
                <span class="sp-kpi-card__icon"><i class="ti ti-report-analytics"></i></span>
                <span class="sp-kpi-badge sp-kpi-badge--red">
                    <i class="ti ti-trending-down"></i> -2.4%
                </span>
            </div>
            <p class="sp-kpi-card__label">AVG. SALE VALUE</p>
            <p class="sp-kpi-card__value">$84.50 <span class="sp-kpi-card__unit">/ transaction</span></p>
        </div>
    </div>

    {{-- Bottom Two-column layout --}}
    <div class="sp-bottom-row">

        {{-- Left: Top Performers Table --}}
        <div class="sp-panel">
            <div class="sp-panel__header">
                <div>
                    <p class="sp-panel__title">Top Performers</p>
                    <p class="sp-panel__sub">Ranked by total revenue generation</p>
                </div>
                <a href="#" class="sp-panel__link">View Detailed Report →</a>
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
                        <tr>
                            <td><span class="sp-rank">#01</span></td>
                            <td>
                                <div class="sp-staff-cell">
                                    <div class="sp-avatar sp-avatar--blue">AM</div>
                                    <span class="sp-staff-name">Alex Martinez</span>
                                </div>
                            </td>
                            <td>$14,290.00</td>
                            <td>168</td>
                            <td>$85.06</td>
                            <td><span class="sp-trend sp-trend--up"><i class="ti ti-trending-up"></i></span></td>
                        </tr>
                        <tr>
                            <td><span class="sp-rank">#02</span></td>
                            <td>
                                <div class="sp-staff-cell">
                                    <div class="sp-avatar sp-avatar--green">SK</div>
                                    <span class="sp-staff-name">Sarah Kim</span>
                                </div>
                            </td>
                            <td>$12,450.50</td>
                            <td>142</td>
                            <td>$87.68</td>
                            <td><span class="sp-trend sp-trend--up"><i class="ti ti-trending-up"></i></span></td>
                        </tr>
                        <tr>
                            <td><span class="sp-rank">#03</span></td>
                            <td>
                                <div class="sp-staff-cell">
                                    <div class="sp-avatar sp-avatar--gray">JR</div>
                                    <span class="sp-staff-name">James Rodriguez</span>
                                </div>
                            </td>
                            <td>$11,890.00</td>
                            <td>155</td>
                            <td>$76.71</td>
                            <td><span class="sp-trend sp-trend--down"><i class="ti ti-trending-down"></i></span></td>
                        </tr>
                        <tr>
                            <td><span class="sp-rank">#04</span></td>
                            <td>
                                <div class="sp-staff-cell">
                                    <div class="sp-avatar sp-avatar--purple">LW</div>
                                    <span class="sp-staff-name">Linda White</span>
                                </div>
                            </td>
                            <td>$10,120.25</td>
                            <td>118</td>
                            <td>$85.76</td>
                            <td><span class="sp-trend sp-trend--up"><i class="ti ti-trending-up"></i></span></td>
                        </tr>
                    </tbody>
                </table>
            </div>

            {{-- Pagination --}}
            <div class="pm-pagination">
                <div class="pm-pagination__meta">
                    <span class="pm-pagination__text" id="pagination-info">Showing 1–4 of 4 results</span>
                    <div class="pm-pagination__per-page">
                        <label for="per-page-select">Show:</label>
                        <select id="per-page-select" class="pm-pagination__select">
                            <option value="15" selected>15</option>
                            <option value="25">25</option>
                            <option value="50">50</option>
                        </select>
                    </div>
                </div>
                <div class="pm-pagination__links" id="paginationLinks">
                    <button class="pm-pagination__btn" disabled><i class="ti ti-chevron-left"></i></button>
                    <button class="pm-pagination__btn active">1</button>
                    <button class="pm-pagination__btn" disabled><i class="ti ti-chevron-right"></i></button>
                </div>
            </div>
        </div>

        {{-- Right: Sales Performance Chart --}}
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
                    <canvas id="regionChart"></canvas>
                    <div class="sp-chart-center">
                        <span class="sp-chart-center__val">$46k</span>
                        <span class="sp-chart-center__lbl">Total</span>
                    </div>
                </div>
            </div>

            {{-- Legend --}}
            <div class="sp-region-list">
                <div class="sp-region-row">
                    <div class="sp-region-left">
                        <span class="sp-region-dot" style="background:#6366f1"></span>
                        <span class="sp-region-name">Alex Martinez</span>
                    </div>
                    <span class="sp-region-val">$14.3k</span>
                </div>
                <div class="sp-region-row">
                    <div class="sp-region-left">
                        <span class="sp-region-dot" style="background:#22d3ee"></span>
                        <span class="sp-region-name">Sarah Kim</span>
                    </div>
                    <span class="sp-region-val">$12.5k</span>
                </div>
                <div class="sp-region-row">
                    <div class="sp-region-left">
                        <span class="sp-region-dot" style="background:#f97316"></span>
                        <span class="sp-region-name">James Rodriguez</span>
                    </div>
                    <span class="sp-region-val">$11.9k</span>
                </div>
                <div class="sp-region-row">
                    <div class="sp-region-left">
                        <span class="sp-region-dot" style="background:#a855f7"></span>
                        <span class="sp-region-name">Linda White</span>
                    </div>
                    <span class="sp-region-val">$10.1k</span>
                </div>
            </div>
        </div>
    </div>
</div>

@endsection

@push('scripts')
    <script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/4.4.1/chart.umd.min.js"></script>
    <script src="{{ asset('assets/js/dashboard/report/sale-person.js') }}"></script>
    <script>
    (function () {
        const ctx = document.getElementById('regionChart').getContext('2d');

        // Data passed from Laravel controller
        const staffColors = ['#6366f1','#22d3ee','#f97316','#a855f7','#22c55e','#f43f5e'];
        const staffHover  = ['#4f46e5','#06b6d4','#ea580c','#9333ea','#16a34a','#e11d48'];

        const chartData = @json(
            collect($rows ?? [])->take(6)->map(fn($r) => [
                'name'    => $r['staff_name']    ?? $r['name']    ?? 'Staff',
                'revenue' => $r['total_revenue'] ?? $r['revenue'] ?? 0,
            ])->values()
        );

        const labels  = chartData.map(d => d.name);
        const data    = chartData.map(d => parseFloat(d.revenue));
        const total   = data.reduce((a, b) => a + b, 0);
        const topName = labels[0] ?? '';
        const topPct  = total > 0 ? Math.round((data[0] ?? 0) / total * 100) : 0;

        // Update center total
        document.querySelector('.sp-chart-center__val').textContent =
            '$' + (total / 1000).toFixed(1) + 'k';

        // Update insight box
        document.querySelector('.sp-insight-box__text').textContent =
            `${topName} leads with ${topPct}% of total revenue this period.`;

        new Chart(ctx, {
            type: 'doughnut',
            data: {
                labels,
                datasets: [{
                    data,
                    backgroundColor: staffColors.slice(0, data.length),
                    hoverBackgroundColor: staffHover.slice(0, data.length),
                    borderWidth: 3,
                    borderColor: '#fff',
                    hoverBorderColor: '#fff',
                    borderRadius: 4,
                }]
            },
            options: {
                cutout: '72%',
                plugins: {
                    legend: { display: false },
                    tooltip: {
                        callbacks: {
                            label: (ctx) => {
                                const pct = total > 0 ? Math.round(ctx.parsed / total * 100) : 0;
                                return ` $${(ctx.parsed / 1000).toFixed(1)}k  (${pct}%)`;
                            }
                        }
                    }
                },
                animation: { animateRotate: true, duration: 800 }
            }
        });

        // Build legend dynamically
        const legendEl = document.querySelector('.sp-region-list');
        legendEl.innerHTML = chartData.map((d, i) => `
            <div class="sp-region-row">
                <div class="sp-region-left">
                    <span class="sp-region-dot" style="background:${staffColors[i]}"></span>
                    <span class="sp-region-name">${d.name}</span>
                </div>
                <span class="sp-region-val">$${(parseFloat(d.revenue)/1000).toFixed(1)}k</span>
            </div>
        `).join('');
    })();
    </script>
@endpush
