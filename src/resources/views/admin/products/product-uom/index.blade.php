@extends('layouts.app')

@section('title', 'Product UOM Management')

@push('styles')
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600&display=swap" rel="stylesheet" />
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@tabler/icons-webfont@latest/dist/tabler-icons.min.css" />
    <link rel="stylesheet" href="{{ asset('assets/css/dashboard/product/uom-list.css') }}">
@endpush

@section('content')

<x-alert />

{{-- ── Page Header ── --}}
<div class="page-header">
    <div>
        <h2 class="page-title">Product UOM management</h2>
        <p class="page-subtitle">Manage product units and conversion ratios</p>
    </div>

    <a href="{{ route('admin.product-uom.create') }}" class="btn-add">
        <i class="ti ti-plus"></i> Add product UOM
    </a>
</div>

{{-- ── Filters ── --}}
<div class="filter-card">
    <form action="{{ route('admin.product-uom.index') }}" method="GET">

        <div class="filter-group">
            <label>Filter by product</label>
            <select name="product_code" class="filter-control" onchange="this.form.submit()">
                <option value="">All products</option>
                @foreach($products as $product)
                    <option value="{{ $product->code }}"
                        {{ request('product_code') == $product->code ? 'selected' : '' }}>
                        {{ $product->name }}
                    </option>
                @endforeach
            </select>
        </div>

        <div class="filter-group">
            <label>Filter by UOM</label>
            <select name="uom_code" class="filter-control" onchange="this.form.submit()">
                <option value="">All UOMs</option>
                @foreach($uoms as $uom)
                    <option value="{{ $uom->code }}"
                        {{ request('uom_code') == $uom->code ? 'selected' : '' }}>
                        {{ $uom->name }}
                    </option>
                @endforeach
            </select>
        </div>

        <div class="filter-group">
            <label>Default status</label>
            <select name="is_default" class="filter-control" onchange="this.form.submit()">
                <option value="">All</option>
                <option value="1" {{ request('is_default') == '1' ? 'selected' : '' }}>Default only</option>
                <option value="0" {{ request('is_default') == '0' ? 'selected' : '' }}>Sub units only</option>
            </select>
        </div>

        @if(request()->filled('product_code') || request()->filled('uom_code') || request()->filled('is_default'))
            <a href="{{ route('admin.product-uom.index') }}" class="btn-clear">
                <i class="ti ti-x"></i> Clear filters
            </a>
        @endif

    </form>
</div>

{{-- ── Table ── --}}
<div class="table-card">
    <div class="table-responsive">
        <table class="table-custom">
            <thead>
                <tr>
                    <th>Product</th>
                    <th>UOM</th>
                    <th>Qty / unit</th>
                    <th>Cost price</th>
                    <th>Selling price</th>
                    <th>Barcode</th>
                    <th>Role</th>
                    <th>Default</th>
                    <th>Actions</th>
                </tr>
            </thead>

            <tbody>
                @forelse($productUoms as $item)
                    <tr>
                        {{-- PRODUCT --}}
                        <td>{{ $item->product_name ?? '—' }}</td>

                        {{-- UOM --}}
                        <td>{{ $item->uom_name ?? '—' }}</td>

                        {{-- QTY --}}
                        <td>{{ number_format($item->quantity_per_unit) }}</td>

                        {{-- COST --}}
                        <td>${{ number_format($item->cost_price ?? 0, 2) }}</td>

                        {{-- SELLING --}}
                        <td>${{ number_format($item->selling_price ?? 0, 2) }}</td>

                        {{-- BARCODE --}}
                        <td>{{ $item->barcode ?: '—' }}</td>

                        {{-- ROLE --}}
                        <td>
                            <span class="badge">
                                {{ ucfirst($item->uom_role ?? 'retail') }}
                            </span>
                        </td>

                        {{-- DEFAULT --}}
                        <td>
                            @if(!empty($item->is_default))
                                <span class="badge-default">
                                    <i class="ti ti-circle-check"></i> Default
                                </span>
                            @else
                                <span style="color:#9ca3af;font-size:12px">—</span>
                            @endif
                        </td>

                        {{-- ACTIONS --}}
                        <td>
                            <div class="action-group">
                                <a href="{{ route('admin.product-uom.edit', $item->id) }}"
                                   class="btn-edit">
                                    <i class="ti ti-pencil"></i> Edit
                                </a>

                                <form action="{{ route('admin.product-uom.destroy', $item->id) }}"
                                      method="POST"
                                      onsubmit="return confirm('Are you sure you want to delete this item?')">
                                    @csrf
                                    @method('DELETE')

                                    <button type="submit" class="btn-delete">
                                        <i class="ti ti-trash"></i> Delete
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="9" style="text-align:center;padding:40px;">
                            <i class="ti ti-package-off" style="font-size:30px;color:#d1d5db;"></i>
                            <div>No product UOM found</div>
                            <small>Try changing filters or add new data</small>
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
                {{ $productUoms->firstItem() ?? 0 }}
                -
                {{ $productUoms->lastItem() ?? 0 }}
                of
                {{ $productUoms->total() }}
                sales
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
            {{ $productUoms->links('vendor.pagination.numbers-only') }}
        </div>
    </div>
</div>

@endsection
