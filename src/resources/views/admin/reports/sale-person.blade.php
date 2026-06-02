@extends('layouts.app')

@push('styles')
    <link rel="stylesheet" href="{{ asset('assets/css/dashboard/report/sale-person.css') }}" data-turbo-track="reload" >
@endpush

@section('title', 'Sales by Person')

@section('content')

<div class="unit-section">
    <div class="unit-section__header">
        <div class="search-wrap">
            <input type="text" id="searchInput" placeholder="Search by staff name...">
            <input type="date" class="filter-date" id="dateFrom">
            <input type="date" class="filter-date" id="dateTo">
            <button class="btn-filter">
                <i class="ti ti-filter"></i> Filter
            </button>
            <button class="btn-reset" id="reset-btn">
                <i class="ti ti-refresh"></i> Reset
            </button>
        </div>
    </div>

    {{-- Top Performer Banner --}}
    <div class="sp-top-performer">
        <div class="sp-performer-left">
            <div class="sp-performer-avatar">
                <img src="https://img.freepik.com/premium-photo/happy-man-ai-generated-portrait-user-profile_1119669-1.jpg?w=2000" alt="">
            </div>
            <div>
                <div class="sp-performer-name">🏆Sophea Meas</div>
                <div class="sp-performer-sub">Top performer this month</div>
            </div>
        </div>
        <div class="sp-performer-stats">
            <div class="sp-performer-stat">
                <span class="sp-stat-val">$12,400</span>
                <span class="sp-stat-lbl">Revenue</span>
            </div>
            <div class="sp-performer-stat">
                <span class="sp-stat-val">284</span>
                <span class="sp-stat-lbl">Orders</span>
            </div>
        </div>
    </div>

    <div class="unit-card">
        <div class="table-responsive">
            <table class="table-custom">
                <thead>
                    <tr>
                        <th class="col-id">#</th>
                        <th>Staff Name</th>
                        <th>Total Orders</th>
                        <th>Total Revenue</th>
                        <th>Avg per Order</th>
                        <th>Performance</th>
                    </tr>
                </thead>
                <tbody id="unit-table-body">
                    <tr>
                        <td><span class="unit-id-text">1</span></td>
                        <td>
                            <div class="sp-staff-cell">
                                <div class="sp-avatar sp-avatar-blue">S</div>
                                <span class="sp-staff-name">Sophea Meas</span>
                            </div>
                        </td>
                        <td><span class="sale-date-text">284</span></td>
                        <td><span class="unit-name-text">$12,400.00</span></td>
                        <td><span class="unit-badge">$43.66</span></td>
                        <td>
                            <div class="sp-progress-wrap">
                                <div class="sp-progress-bar">
                                    <div class="sp-progress-fill" style="width: 100%"></div>
                                </div>
                                <span class="sp-progress-label">100%</span>
                            </div>
                        </td>
                    </tr>
                    <tr>
                        <td><span class="unit-id-text">2</span></td>
                        <td>
                            <div class="sp-staff-cell">
                                <div class="sp-avatar sp-avatar-green">R</div>
                                <span class="sp-staff-name">Ratha Keo</span>
                            </div>
                        </td>
                        <td><span class="sale-date-text">210</span></td>
                        <td><span class="unit-name-text">$9,800.00</span></td>
                        <td><span class="unit-badge">$46.67</span></td>
                        <td>
                            <div class="sp-progress-wrap">
                                <div class="sp-progress-bar">
                                    <div class="sp-progress-fill" style="width: 79%"></div>
                                </div>
                                <span class="sp-progress-label">79%</span>
                            </div>
                        </td>
                    </tr>
                    <tr>
                        <td><span class="unit-id-text">3</span></td>
                        <td>
                            <div class="sp-staff-cell">
                                <div class="sp-avatar sp-avatar-orange">D</div>
                                <span class="sp-staff-name">Dara Pich</span>
                            </div>
                        </td>
                        <td><span class="sale-date-text">178</span></td>
                        <td><span class="unit-name-text">$7,650.00</span></td>
                        <td><span class="unit-badge">$42.98</span></td>
                        <td>
                            <div class="sp-progress-wrap">
                                <div class="sp-progress-bar">
                                    <div class="sp-progress-fill" style="width: 62%"></div>
                                </div>
                                <span class="sp-progress-label">62%</span>
                            </div>
                        </td>
                    </tr>
                    <tr>
                        <td><span class="unit-id-text">4</span></td>
                        <td>
                            <div class="sp-staff-cell">
                                <div class="sp-avatar sp-avatar-purple">C</div>
                                <span class="sp-staff-name">Chanthy Ros</span>
                            </div>
                        </td>
                        <td><span class="sale-date-text">142</span></td>
                        <td><span class="unit-name-text">$6,100.00</span></td>
                        <td><span class="unit-badge">$42.96</span></td>
                        <td>
                            <div class="sp-progress-wrap">
                                <div class="sp-progress-bar">
                                    <div class="sp-progress-fill" style="width: 49%"></div>
                                </div>
                                <span class="sp-progress-label">49%</span>
                            </div>
                        </td>
                    </tr>
                    <tr>
                        <td><span class="unit-id-text">5</span></td>
                        <td>
                            <div class="sp-staff-cell">
                                <div class="sp-avatar sp-avatar-red">B</div>
                                <span class="sp-staff-name">Bopha Lim</span>
                            </div>
                        </td>
                        <td><span class="sale-date-text">98</span></td>
                        <td><span class="unit-name-text">$4,200.00</span></td>
                        <td><span class="unit-badge">$42.86</span></td>
                        <td>
                            <div class="sp-progress-wrap">
                                <div class="sp-progress-bar">
                                    <div class="sp-progress-fill" style="width: 34%"></div>
                                </div>
                                <span class="sp-progress-label">34%</span>
                            </div>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>

        {{-- Pagination --}}
        <div class="pm-pagination">
            <div class="pm-pagination__meta">
                <span class="pm-pagination__text">Showing 1–5 of 5 results</span>
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
                <button class="disabled">«</button>
                <button class="active">1</button>
                <button class="disabled">»</button>
            </div>
        </div>
    </div>
</div>

@endsection

@push('scripts')
    <script src="{{ asset('assets/js/dashboard/report/sale-person.js') }}"></script>
@endpush
