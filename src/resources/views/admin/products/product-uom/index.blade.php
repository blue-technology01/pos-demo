@extends('layouts.app')

@section('title', 'Product UOM Management')

@push('styles')
<link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined" />
<style>
    .page-header{
        display:flex;
        justify-content:space-between;
        align-items:center;
        margin-bottom:20px;
    }

    .page-title{
        font-size:24px;
        font-weight:600;
        margin:0;
    }

    .page-subtitle{
        color:#6b7280;
        margin-top:4px;
    }

    .btn-add{
        background:#2563eb;
        color:#fff;
        padding:10px 16px;
        border-radius:8px;
        text-decoration:none;
        font-weight:500;
    }

    /* ស្ទីលសម្រាប់របារ Filter */
    .filter-card {
        background: #fff;
        padding: 16px;
        border-radius: 12px;
        margin-bottom: 20px;
        box-shadow: 0 2px 8px rgba(0,0,0,.04);
        display: flex;
        gap: 15px;
        align-items: flex-end;
    }

    .filter-group {
        display: flex;
        flex-direction: column;
        width: 300px;
    }

    .filter-group label {
        font-weight: 600;
        font-size: 13px;
        margin-bottom: 6px;
        color: #374151;
    }

    .filter-control {
        padding: 8px 12px;
        border: 1px solid #e5e7eb;
        border-radius: 8px;
        outline: none;
        background-color: #fff;
    }

    .btn-clear {
        background: #e5e7eb;
        color: #1f2937;
        padding: 9px 16px;
        border-radius: 8px;
        text-decoration: none;
        font-size: 13px;
        font-weight: 500;
    }

    /* ស្ទីលសម្រាប់តារាង */
    .table-card{
        background:#fff;
        border-radius:12px;
        overflow:hidden;
        box-shadow:0 2px 8px rgba(0,0,0,.05);
    }

    .table-responsive{
        overflow-x:auto;
    }

    .table-custom{
        width:100%;
        border-collapse:collapse;
    }

    .table-custom th,
    .table-custom td{
        padding:12px 16px;
        border-bottom:1px solid #eee;
        text-align:left;
    }

    .table-custom th{
        background:#f8fafc;
        font-weight:600;
    }

    .badge-default{
        background:#dcfce7;
        color:#166534;
        padding:4px 8px;
        border-radius:999px;
        font-size:12px;
        font-weight:600;
    }

    .action-group{
        display:flex;
        gap:8px;
    }

    .btn-edit,
    .btn-delete{
        border:none;
        background:none;
        cursor:pointer;
        padding:4px;
        font-weight: 500;
        text-decoration: none;
    }

    .btn-edit{
        color:#2563eb;
    }

    .btn-delete{
        color:#dc2626;
    }

    /* ស្ទីលសម្រាប់ Pagination block */
    .pagination-wrapper {
        padding: 16px;
        border-top: 1px solid #eee;
        display: flex;
        justify-content: center;
    }
</style>
@endpush

@section('content')

<x-alert />

<div class="page-header">
    <div>
        <h2 class="page-title">Product UOM Management</h2>
        <p class="page-subtitle">
            Manage product units and conversion ratios
        </p>
    </div>

    <a href="{{ route('admin.product-uom.create') }}" class="btn-add">
        + Add Product UOM
    </a>
</div>

<div class="filter-card" style="background: #fff; padding: 16px; border-radius: 12px; margin-bottom: 20px; box-shadow: 0 2px 8px rgba(0,0,0,.04);">
    <form action="{{ route('admin.product-uom.index') }}" method="GET" style="display: flex; gap: 12px; align-items: flex-end; width: 100%; flex-wrap: wrap;">

        <div class="filter-group">
            <label>Filter by Product</label>
            <select name="product_code" onchange="this.form.submit()" class="filter-control">
                <option value="">All Products</option>
                @foreach($products as $product)
                    <option value="{{ $product->code }}" {{ request('product_code') == $product->code ? 'selected' : '' }}>
                        {{ $product->name }}
                    </option>
                @endforeach
            </select>
        </div>

        <div class="filter-group">
            <label>Filter by UOM</label>
            <select name="uom_code" onchange="this.form.submit()" class="filter-control">
                <option value="">All UOMs</option>
                @foreach($uoms as $uom)
                    <option value="{{ $uom->code }}" {{ request('uom_code') == $uom->code ? 'selected' : '' }}>
                        {{ $uom->name }}
                    </option>
                @endforeach
            </select>
        </div>

        <div class="filter-group">
            <label>Filter by Default Status</label>
            <select name="is_default" onchange="this.form.submit()" class="filter-control">
                <option value="">All Status</option>
                <option value="1" {{ request('is_default') === '1' ? 'selected' : '' }}>Default Only</option>
                <option value="0" {{ request('is_default') === '0' ? 'selected' : '' }}>Sub-units Only</option>
            </select>
        </div>

        @if(request('product_code') || request('uom_code') || (request('is_default') !== null && request('is_default') !== ''))
            <a href="{{ route('admin.product-uom.index') }}" class="btn-clear" style="height: 38px; display: inline-flex; align-items: center; justify-content: center; background: #e5e7eb; color: #1f2937; padding: 0 16px; border-radius: 8px; text-decoration: none; font-size: 13px; font-weight: 500;">
                Clear Filters
            </a>
        @endif
    </form>
</div>

<div class="table-card">

    <div class="table-responsive">

        <table class="table-custom">
            <thead>
                <tr>
                    <th>Product</th>
                    <th>UOM</th>
                    <th>Qty / Unit</th>
                    <th>Cost Price</th>
                    <th>Selling Price</th>
                    <th>Barcode</th>
                    <th>Default</th>
                    <th>Action</th>
                </tr>
            </thead>

            <tbody>
                @forelse($productUoms as $item)
                    <tr>
                        <td>{{ $item->product->name ?? '-' }}</td>
                        <td>{{ $item->uom->name ?? '-' }}</td>
                        <td>{{ $item->quantity_per_unit }}</td>
                        <td>${{ number_format($item->cost_price, 2) }}</td>

                        <td>${{ number_format($item->selling_price, 2) }}</td>

                        <td>{{ $item->barcode ?? '-' }}</td>
                        <td>
                            @if($item->is_default)
                                <span class="badge-default">Default</span>
                            @else
                                -
                            @endif
                        </td>
                        <td>
                            <div class="action-group">
                                <a href="{{ route('admin.product-uom.edit', $item->id) }}" class="btn-edit">
                                    Edit
                                </a>
                                <form action="{{ route('admin.product-uom.destroy', $item->id) }}"
                                      method="POST"
                                      onsubmit="return confirm('Delete this Product UOM?')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn-delete">
                                        Delete
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="8" style="text-align:center; color: #6b7280; padding: 24px;">
                            No Product UOM found.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>

    </div>

    <div class="pagination-wrapper">
        {{ $productUoms->appends(request()->query())->links() }}
    </div>

</div>

@endsection

@push('scripts')
<script>
    console.log('Product UOM Page Loaded');
</script>
@endpush
