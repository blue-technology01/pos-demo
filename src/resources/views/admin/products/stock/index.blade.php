@extends('layouts.app')

@push('styles')
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600&display=swap" rel="stylesheet" />
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@tabler/icons-webfont@latest/dist/tabler-icons.min.css" />
    <link rel="stylesheet" href="{{ asset('assets/css/dashboard/stock/index.css') }}">
@endpush

@section('title', 'Ajustment List')

@section('content')
    <div class="page-header d-flex justify-content-between align-items-center mb-3">
        <div>
            <h4 class="mb-0">Stock Adjustments</h4>
            <p class="text-muted mb-0">Manage and review stock adjustment requests.</p>
        </div>
        <a href="{{ route('admin.products.stock.create') }}" class="btn btn-primary">
            <i class="ti ti-plus"></i> New Adjustment
        </a>
    </div>
    <div class="card">
        <div class="card-body">
            {{-- Filters --}}
            <form method="GET" action="{{ route('admin.products.stock.index') }}" class="row g-2 mb-3">
                <div class="col-md-3">
                    <select name="status" class="form-select">
                        <option value="">All Status</option>
                        <option value="pending" @selected(($filters['status'] ?? '') === 'pending')>Pending</option>
                        <option value="approved" @selected(($filters['status'] ?? '') === 'approved')>Approved</option>
                        <option value="rejected" @selected(($filters['status'] ?? '') === 'rejected')>Rejected</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <input type="text" name="product_code" class="form-control" placeholder="Product Code"
                        value="{{ $filters['product_code'] ?? '' }}">
                </div>
                <div class="col-md-2">
                    <input type="date" name="date_from" class="form-control"
                        value="{{ $filters['date_from'] ?? '' }}">
                </div>
                <div class="col-md-2">
                    <input type="date" name="date_to" class="form-control"
                        value="{{ $filters['date_to'] ?? '' }}">
                </div>
                <div class="col-md-2 d-flex gap-2">
                    <button type="submit" class="btn btn-outline-secondary flex-fill">
                        <i class="ti ti-filter"></i> Filter
                    </button>
                    <a href="{{ route('admin.products.stock.index') }}" class="btn btn-outline-secondary">
                        <i class="ti ti-x"></i>
                    </a>
                </div>
            </form>

            {{-- Table --}}
            <div class="table-responsive">
                <table class="table table-hover align-middle">
                    <thead>
                        <tr>
                            <th>Date</th>
                            <th>Product</th>
                            <th>Warehouse</th>
                            <th>New Qty</th>
                            <th>Reason</th>
                            <th>Status</th>
                            <th>Created By</th>
                            <th class="text-end">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($adjustments as $adjustment)
                            <tr>
                                <td>{{ \Carbon\Carbon::parse($adjustment->adjustment_date)->format('d M Y') }}</td>
                                <td>
                                    <div class="fw-medium">{{ $adjustment->product?->name ?? '-' }}</div>
                                    <div class="text-muted small">{{ $adjustment->product_code }}</div>
                                </td>
                                <td>{{ $adjustment->warehouse?->name ?? '-' }}</td>
                                <td>{{ number_format($adjustment->new_quantity) }}</td>
                                <td>{{ ucfirst($adjustment->reason_code) }}</td>
                                <td>
                                    @switch($adjustment->status)
                                        @case('pending')
                                            <span class="badge bg-warning-subtle text-warning">Pending</span>
                                            @break
                                        @case('approved')
                                            <span class="badge bg-success-subtle text-success">Approved</span>
                                            @break
                                        @case('rejected')
                                            <span class="badge bg-danger-subtle text-danger">Rejected</span>
                                            @break
                                    @endswitch
                                </td>
                                <td>{{ $adjustment->creator?->name ?? '-' }}</td>
                                <td class="text-end">
                                    <div class="d-inline-flex gap-1">
                                        <a href="{{ route('admin.products.stock.show', $adjustment) }}"
                                            class="btn btn-sm btn-outline-secondary" title="View">
                                            <i class="ti ti-eye"></i>
                                        </a>
                                        @if ($adjustment->status === 'pending')
                                            <a href="{{ route('admin.products.stock.edit', $adjustment) }}"
                                                class="btn btn-sm btn-outline-secondary" title="Edit">
                                                <i class="ti ti-edit"></i>
                                            </a>
                                            {{-- Approve --}}
                                            <form action="{{ route('admin.products.stock.approve', $adjustment) }}"
                                                method="POST" class="d-inline"
                                                onsubmit="return confirm('Approve this stock adjustment?');">
                                                @csrf
                                                <button type="submit"
                                                    class="btn btn-sm btn-outline-success"
                                                    title="Approve">
                                                    <i class="ti ti-check"></i>
                                                </button>
                                            </form>
                                            {{-- Reject --}}
                                            <form action="{{ route('admin.products.stock.reject', $adjustment) }}"
                                                method="POST" class="d-inline"
                                                onsubmit="return confirm('Reject this stock adjustment?');">
                                                @csrf
                                                <button type="submit"
                                                    class="btn btn-sm btn-outline-warning"
                                                    title="Reject">
                                                    <i class="ti ti-x"></i>
                                                </button>
                                            </form>
                                            <form action="{{ route('admin.products.stock.destroy', $adjustment) }}"
                                                method="POST" class="d-inline"
                                                onsubmit="return confirm('Delete this adjustment permanently?');">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn btn-sm btn-outline-danger"
                                                    title="Delete">
                                                    <i class="ti ti-trash"></i>
                                                </button>
                                            </form>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="text-center text-muted py-4">
                                    No stock adjustments found.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            {{-- pagination --}}
            <div class="table-footer" style="width: 100%; display: flex; justify-content: space-between" id="tableFooter">
                <span class="table-footer-left">
                    <div class="table-info">
                        showing
                        {{ $adjustments->firstItem() ?? 0 }}
                        -
                        {{ $adjustments->lastItem() ?? 0 }}
                        of
                        {{ $adjustments->total() }}
                        Ajustment
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
                    {{ $adjustments->links('vendor.pagination.numbers-only') }}
                </div>
            </div>
        </div>
    </div>
@endsection
@push('scripts')
{{-- scripts --}}
@endpush
