@extends('layouts.app')

@push('styles')
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600&display=swap" rel="stylesheet" />
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@tabler/icons-webfont@latest/dist/tabler-icons.min.css" />
    <link rel="stylesheet" href="{{ asset('assets/css/dashboard/product/products.css') }}">
@endpush

@section('title', 'Product List')

@section('content')

<x-alert />
<x-spinner />
<div class="product-section">
    {{-- ── Header & Filter Bar ── --}}
    <div class="product-section__header">

        <form method="GET" action="{{ route('admin.products.index') }}" class="search-wrap" id="filter-form">

            {{-- Search --}}
            <div class="filter-group">
                <input
                    type="text"
                    name="search"
                    id="search"
                    value="{{ request('search') }}"
                    placeholder="Search products..."
                    class="filter-input"
                    autocomplete="off"
                >
            </div>

            {{-- Start date --}}
            <div class="filter-group">
                <label for="start_date">Start date</label>
                <input
                    type="date"
                    name="start_date"
                    id="start_date"
                    value="{{ request('start_date') }}"
                    class="filter-input-date"
                >
            </div>

            {{-- End date --}}
            <div class="filter-group">
                <label for="end_date">End date</label>
                <input
                    type="date"
                    name="end_date"
                    id="end_date"
                    value="{{ request('end_date') }}"
                    class="filter-input-date"
                >
            </div>

            {{-- Category --}}
            <div class="filter-group">
                <select name="category_code" id="category_code" class="filter-select">
                    <option value="">All categories</option>
                    @foreach($categories as $category)
                        <option
                            value="{{ $category->code }}"
                            {{ request('category_code') == $category->code ? 'selected' : '' }}
                        >
                            {{ $category->name }}
                        </option>
                    @endforeach
                </select>
            </div>
            <button type="submit" class="product-section__btn-add" onclick="showLoader()">Filter</button>
            {{-- Reset --}}
            @if(request()->anyFilled(['search', 'start_date', 'end_date', 'category_code']))
                <a href="{{ route('admin.products.index') }}" onclick="showLoader()" class="btn-clear" title="Clear filters">
                    <i class="ti ti-x" aria-hidden="true"></i> Reset
                </a>
            @endif
        </form>
        <form action="#"
            method="POST"
            enctype="multipart/form-data">
            @csrf
            <input type="file"
                onclick="alert('Comming soon!.')"
                name="file"
                id="excelFile"
                accept=".xlsx,.xls,.csv"
                hidden>
            <button type="button"
                    class="product-section__btn-add"
                    style="background-color: rgb(53, 185, 53)"
                    onclick="document.getElementById('excelFile').click()">
                <i class="ti ti-upload"></i> Choose File
            </button>
            <button type="submit" class="product-section__btn-add">
                <i class="ti ti-check"></i> Import
            </button>
        </form>
        {{-- Add product --}}
        <a href="{{ route('admin.products.create') }}" class="product-section__btn-add">
            <i class="ti ti-plus" aria-hidden="true"></i> Add product
        </a>
    </div>

    {{-- ── Table ── --}}
    <div class="table-card">
        <div class="table-responsive">
            <table class="table-custom">
                <thead>
                    <tr>
                        <th>Image</th>
                        <th>Code</th>
                        <th>Name</th>
                        <th>Category</th>
                        {{-- <th>Cost</th> --}}
                        {{-- <th>Price</th> --}}
                        <th>Stock</th>
                        <th>Min stock</th>
                        <th>Barcode</th>
                        <th>Expiry</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($products as $product)
                        <tr>
                            <td>
                                <img
                                    loading="lazy"
                                    src="{{ $product->image ? asset('storage/' . $product->image) : 'https://via.placeholder.com/40' }}"
                                    alt="{{ $product->name }}"
                                >
                            </td>

                            <td>
                                <span class="barcode-badge">{{ $product->code }}</span>
                            </td>

                            <td>{{ $product->name }}</td>
                            <td>{{ $product->category->name ?? '—' }}</td>
                            {{-- <td>${{ number_format($product->cost_price, 2) }}</td> --}}
                            {{-- <td>${{ number_format($product->price, 2) }}</td> --}}
                            <td>{{ number_format($product->stock) }}</td>
                            <td>{{ number_format($product->min_stock) }}</td>
                            <td>{{ $product->barcode ?? '—' }}</td>
                            <td>{{ $product->expiry_date ?? '—' }}</td>
                            <td>
                                <span class="status-badge status-{{ $product->status }}">
                                    {{ ucfirst($product->status) }}
                                </span>
                            </td>

                            <td>
                                <div class="action-group">
                                    {{-- Edit --}}
                                    <a
                                        href="{{ route('admin.products.edit', $product->code) }}"
                                        class="btn-edit"
                                        title="Edit product"
                                    >
                                        <i class="ti ti-pencil" aria-hidden="true"></i>
                                    </a>
                                    {{-- Delete --}}
                                    <form
                                        action="{{ route('admin.products.destroy', $product->code) }}"
                                        method="POST"
                                        onsubmit="return confirm('Delete product \'{{ addslashes($product->name) }}\'?')"
                                        style="display:inline"
                                    >
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn-delete" title="Delete product">
                                            <i class="ti ti-trash" aria-hidden="true"></i>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="12">
                                <i class="ti ti-package-off" aria-hidden="true" style="font-size:28px;display:block;margin:0 auto 8px;color:#d1d5db"></i>
                                No products found.
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
                    {{ $products->firstItem() ?? 0 }}
                    -
                    {{ $products->lastItem() ?? 0 }}
                    of
                    {{ $products->total() }}
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
                {{ $products->links('vendor.pagination.numbers-only') }}
            </div>
        </div>
    </div>
</div>

@endsection
@push('scripts')
    <script>
        document.getElementById('filter-form').addEventListener('submit', function () {
            showLoader();
        });
    </script>
@endpush
