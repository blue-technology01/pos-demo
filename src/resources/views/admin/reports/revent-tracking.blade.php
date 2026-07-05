@extends('layouts.app')

@push('styles')
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600&display=swap" rel="stylesheet" />
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@tabler/icons-webfont@latest/dist/tabler-icons.min.css" />
    <link rel="stylesheet" href="{{ asset('assets/css/dashboard/report/revent.css') }}" data-turbo-track="reload">
@endpush

@section('title', 'Revenue Tracking')

@section('content')

<div class="rv-page">

    {{-- Header --}}
    <div class="rv-page__header">
        <h1 class="rv-page__title">Revenue tracking</h1>
        <p class="rv-page__subtitle">Daily revenue breakdown with order and average sale metrics.</p>
    </div>

    {{-- Filter Bar --}}
    <form method="GET" action="{{ route('admin.revenue-tracking') }}" class="rv-filter-bar" id="filter-form">
        <input
            type="date"
            name="start_date"
            id="dateFrom"
            value="{{ $startDate }}"
            max="{{ now()->format('Y-m-d') }}">

        <input
            type="date"
            name="end_date"
            id="dateTo"
            value="{{ $endDate }}"
            max="{{ now()->format('Y-m-d') }}">

        <button class="btn-filter" type="submit" id="filter-btn" onclick="showLoader()" >
            <i class="ti ti-filter" aria-hidden="true"></i> Filter
        </button>

        <a href="{{ route('admin.revenue-tracking') }}" onclick="showLoader()" class="btn-reset">
            <i class="ti ti-refresh" aria-hidden="true"></i> Reset
        </a>
    </form>
    {{-- Table Panel --}}
    <div class="rv-panel">

        <div class="rv-panel__header">
            <div>
                <p class="rv-panel__title">Daily breakdown</p>
                <p class="rv-panel__sub">Revenue grouped by date</p>
            </div>
        </div>

        <div class="table-responsive">
            <table class="table-custom">
                <thead>
                    <tr>
                        <th>Date</th>
                        <th>Total orders</th>
                        <th>Total revenue</th>
                        <th>Average sale</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($rows as $row)
                        <tr>
                            <td>
                                <div class="rv-date-cell">
                                    <div class="rv-date-icon">
                                        <i class="ti ti-calendar" aria-hidden="true"></i>
                                    </div>
                                    {{-- object access since $rows is a paginator --}}
                                    {{ Carbon\Carbon::parse($row->date)->format('d M Y') }}
                                </div>
                            </td>
                            <td>{{ number_format($row->total_orders) }}</td>
                            <td class="rv-value">${{ number_format($row->total_revenue, 2) }}</td>
                            <td>${{ number_format($row->average_sale, 2) }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="rv-state-cell">No data found.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{-- Pagination --}}
        <div class="table-footer">
            <span class="table-footer-left">
                <div class="table-info">
                    @if($rows->total() > 0)
                        Showing {{ $rows->firstItem() }} - {{ $rows->lastItem() }} of {{ $rows->total() }} results
                    @else
                        No results
                    @endif
                </div>

                {{-- Per page --}}
                <form method="GET" action="{{ route('admin.revenue-tracking') }}">
                    @foreach (request()->except('per_page', 'page') as $key => $value)
                        <input type="hidden" name="{{ $key }}" value="{{ $value }}">
                    @endforeach
                    <select name="per_page" class="pm-pagination__select" onchange="showLoader(); this.form.submit()">
                        <option value="15" {{ request('per_page', 15) == 15 ? 'selected' : '' }}>15</option>
                        <option value="25" {{ request('per_page') == 25 ? 'selected' : '' }}>25</option>
                        <option value="50" {{ request('per_page') == 50 ? 'selected' : '' }}>50</option>
                    </select>
                </form>
            </span>

            <div class="pagination">
                {{ $rows->links('vendor.pagination.numbers-only') }}
            </div>
        </div>

    </div>

</div>

@endsection

@push('scripts')
<script>
(function () {
    const dateFrom = document.getElementById('dateFrom');
    const dateTo   = document.getElementById('dateTo');

    if (dateFrom && dateTo) {
        dateFrom.addEventListener('change', function () {
            dateTo.min = this.value;
            if (dateTo.value && dateTo.value < this.value) {
                dateTo.value = this.value;
            }
        });
    }

    const filterBtn  = document.getElementById('filter-btn');
    const filterForm = document.getElementById('filter-form');
})();
</script>
@endpush
