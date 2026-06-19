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
        <i class="ti ti-plus" aria-hidden="true"></i> Add product UOM
    </a>
</div>

{{-- ── Filters ── --}}
<div class="filter-card">
    <form action="{{ route('admin.product-uom.index') }}" method="GET">

        <div class="filter-group">
            <label for="product_code">Filter by product</label>
            <select
                name="product_code"
                id="product_code"
                class="filter-control"
                onchange="this.form.submit()"
            >
                <option value="">All products</option>
                @foreach($products as $product)
                    <option
                        value="{{ $product->code }}"
                        {{ request('product_code') == $product->code ? 'selected' : '' }}
                    >
                        {{ $product->name }}
                    </option>
                @endforeach
            </select>
        </div>

        <div class="filter-group">
            <label for="uom_code">Filter by UOM</label>
            <select
                name="uom_code"
                id="uom_code"
                class="filter-control"
                onchange="this.form.submit()"
            >
                <option value="">All UOMs</option>
                @foreach($uoms as $uom)
                    <option
                        value="{{ $uom->code }}"
                        {{ request('uom_code') == $uom->code ? 'selected' : '' }}
                    >
                        {{ $uom->name }}
                    </option>
                @endforeach
            </select>
        </div>

        <div class="filter-group">
            <label for="is_default">Filter by default status</label>
            <select
                name="is_default"
                id="is_default"
                class="filter-control"
                onchange="this.form.submit()"
            >
                <option value="">All status</option>
                <option value="1" {{ request('is_default') === '1' ? 'selected' : '' }}>Default only</option>
                <option value="0" {{ request('is_default') === '0' ? 'selected' : '' }}>Sub-units only</option>
            </select>
        </div>

        @if(request('product_code') || request('uom_code') || (request('is_default') !== null && request('is_default') !== ''))
            <a href="{{ route('admin.product-uom.index') }}" class="btn-clear">
                <i class="ti ti-x" aria-hidden="true"></i> Clear filters
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
                    <th>Default</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($productUoms as $item)
                    <tr>
                        <td>{{ $item->product->name ?? '—' }}</td>
                        <td>{{ $item->uom->name ?? '—' }}</td>
                        <td>{{ number_format($item->quantity_per_unit) }}</td>
                        <td>${{ number_format($item->cost_price, 2) }}</td>
                        <td>${{ number_format($item->selling_price, 2) }}</td>
                        <td>{{ $item->barcode ?? '—' }}</td>
                        <td>
                            @if($item->is_default)
                                <span class="badge-default">
                                    <i class="ti ti-circle-check" aria-hidden="true" style="font-size:11px"></i>
                                    Default
                                </span>
                            @else
                                <span style="color:var(--color-text-tertiary,#9ca3af);font-size:12px">—</span>
                            @endif
                        </td>
                        <td>
                            <div class="action-group">
                                <a
                                    href="{{ route('admin.product-uom.edit', $item->id) }}"
                                    class="btn-edit"
                                    title="Edit"
                                >
                                    <i class="ti ti-pencil" aria-hidden="true"></i> Edit
                                </a>
                                <form
                                    action="{{ route('admin.product-uom.destroy', $item->id) }}"
                                    method="POST"
                                    onsubmit="return confirm('Delete this product UOM?')"
                                    style="display:inline"
                                >
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn-delete" title="Delete">
                                        <i class="ti ti-trash" aria-hidden="true"></i> Delete
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="8">
                            <i class="ti ti-package-off" aria-hidden="true" style="font-size:28px;display:block;margin:0 auto 8px;color:#d1d5db"></i>
                            No product UOM found.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    {{-- ── Pagination ── --}}
    <div class="pagination-wrapper">
        {{ $productUoms->appends(request()->query())->links() }}
    </div>

</div>

@endsection