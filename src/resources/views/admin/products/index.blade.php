@extends('layouts.app')

@push('styles')
<link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined" />
<link rel="stylesheet" href="{{ asset('assets/css/dashboard/product/products.css') }}">
@endpush

@section('title', 'Product List')

@section('content')
<x-alert />
<div class="product-section">
    {{-- HEADER & FILTER BAR --}}
    <div class="product-section__header">

        <form method="GET" action="{{ route('admin.products.index') }}" class="search-wrap" id="filter-form">

            {{-- TEXT SEARCH --}}
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

            {{-- START DATE --}}
            <div class="filter-group">
                <label>Start Date</label>
                <input type="date" name="start_date" id="start_date"
                    value="{{ request('start_date') }}" class="filter-input-date">
            </div>

            {{-- END DATE --}}
            <div class="filter-group">
                <label>End Date</label>
                <input type="date" name="end_date" id="end_date"
                    value="{{ request('end_date') }}" class="filter-input-date">
            </div>

            {{-- CATEGORY --}}
            <div class="filter-group">
                <select name="category_code" id="category_code" class="filter-select">
                    <option value="">All Categories</option>
                    @foreach($categories as $category)
                        <option value="{{ $category->code }}"
                            {{ request('category_code') == $category->code ? 'selected' : '' }}>
                            {{ $category->name }}
                        </option>
                    @endforeach
                </select>
            </div>

            {{-- RESET --}}
            @if(request()->anyFilled(['search', 'start_date', 'end_date', 'category_code']))
                <a href="{{ route('admin.products.index') }}" class="btn-clear" title="Clear Filters">
                    ✕ Reset
                </a>
            @endif

        </form>

        <a href="{{ route('admin.products.create') }}" class="product-section__btn-add">
            + Add Product
        </a>
    </div>

    {{-- TABLE --}}
    <div class="table-card">
        <div class="table-responsive">
            <table class="table-custom">
                <thead>
                    <tr>
                        <th>Image</th>
                        <th>Code</th>
                        <th>Name</th>
                        <th>Category</th>
                        <th>Cost</th>
                        <th>Price</th>
                        <th>Stock</th>
                        <th>Min Stock</th>
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
                                    src="{{ $product->image ? asset('storage/'.$product->image) : 'https://via.placeholder.com/42' }}"
                                    alt="{{ $product->name }}"
                                >
                            </td>
                            <td><span class="barcode-badge">{{ $product->code }}</span></td>
                            <td>{{ $product->name }}</td>
                            <td>{{ $product->category->name ?? '-' }}</td>
                            <td>${{ number_format($product->cost_price, 2) }}</td>
                            <td>${{ number_format($product->price, 2) }}</td>
                            <td>{{ $product->stock }}</td>
                            <td>{{ $product->min_stock }}</td>
                            <td>{{ $product->barcode ?? '-' }}</td>
                            <td>{{ $product->expiry_date ?? '-' }}</td>
                            <td>
                                <span class="status-badge status-{{ $product->status }}">
                                    {{ ucfirst($product->status) }}
                                </span>
                            </td>
                            <td>
                                <div class="action-group">
                                    <a href="{{ route('admin.products.edit', $product->code) }}" class="btn-edit" title="Edit">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                            <path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/>
                                            <path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/>
                                        </svg>
                                    </a>
                                    <form action="{{ route('admin.products.destroy', $product->code) }}" method="POST"
                                        onsubmit="return confirm('Delete product {{ addslashes($product->name) }}?')"
                                        style="display:inline;">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn-delete" title="Delete">
                                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                                <polyline points="3 6 5 6 21 6"/>
                                                <path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"/>
                                                <line x1="10" y1="11" x2="10" y2="17"/>
                                                <line x1="14" y1="11" x2="14" y2="17"/>
                                            </svg>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="12">No products found</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{-- PAGINATION --}}
        <div class="pm-pagination">
            {{ $products->appends(request()->query())->links('pagination::bootstrap-5') }}
        </div>
    </div>

</div>
@endsection

@push('scripts')
<script>
(function () {
    const form = document.getElementById('filter-form');
    if (!form) return;

    let debounceTimer;

    // Text search — submit 500ms after user stops typing
    const searchInput = document.getElementById('search');
    if (searchInput) {
        searchInput.addEventListener('input', function () {
            clearTimeout(debounceTimer);
            debounceTimer = setTimeout(() => form.submit(), 500);
        });
    }

    // Date & category — submit instantly on change
    ['start_date', 'end_date', 'category_code'].forEach(function (id) {
        const el = document.getElementById(id);
        if (el) el.addEventListener('change', () => form.submit());
    });
})();
</script>

@endpush
