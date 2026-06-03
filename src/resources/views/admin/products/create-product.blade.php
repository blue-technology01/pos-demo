@extends('layouts.app')

@push('styles')
<link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined" />
<link rel="stylesheet" href="{{ asset('assets/css/dashboard/product/create.css') }}">
@endpush

@section('title', 'Create Product')

@section('content')

<div class="form-section">
    <div class="form-section__header">
        <h1>Create New Product</h1>
    </div>
    <form action="{{ route('products.store') }}" method="POST" enctype="multipart/form-data">
        @csrf
        <div class="form-grid">
            {{-- LEFT --}}
            <div class="form-column">
                {{-- GENERAL --}}
                <div class="form-card">
                    <h2>General Details</h2>
                    {{-- NAME --}}
                    <div class="form-group">
                        <label>Product Name *</label>
                        <input type="text"
                               name="name"
                               value="{{ old('name') }}"
                               class="form-input @error('name') is-invalid @enderror">
                        @error('name')
                            <small class="text-danger">{{ $message }}</small>
                        @enderror
                    </div>
                    {{-- CODE + BARCODE --}}
                    <div class="form-row-2">
                        <div class="form-group">
                            <label>Product Code *</label>
                            <input type="text"
                                   name="code"
                                   value="{{ old('code') }}"
                                   class="form-input @error('code') is-invalid @enderror">
                            @error('code')
                                <small class="text-danger">{{ $message }}</small>
                            @enderror
                        </div>
                        <div class="form-group">
                            <label>Barcode</label>
                            <input type="text"
                                   name="barcode"
                                   value="{{ old('barcode') }}"
                                   class="form-input @error('barcode') is-invalid @enderror">
                            @error('barcode')
                                <small class="text-danger">{{ $message }}</small>
                            @enderror
                        </div>
                    </div>
                    {{-- CATEGORY --}}
                    <div class="form-group">
                        <label>Category</label>
                        <input type="text"
                               name="category_code"
                               value="{{ old('category_code') }}"
                               class="form-input">
                    </div>
                </div>
                {{-- PRICING --}}
                <div class="form-card">
                    <h2>Pricing & Stock</h2>
                    <div class="form-row-2">
                        <div class="form-group">
                            <label>Cost Price *</label>
                            <input type="number"
                                   step="0.01"
                                   name="cost_price"
                                   value="{{ old('cost_price') }}"
                                   class="form-input">
                        </div>
                        <div class="form-group">
                            <label>Selling Price *</label>
                            <input type="number"
                                   step="0.01"
                                   name="price"
                                   value="{{ old('price') }}"
                                   class="form-input">
                        </div>
                    </div>
                    <div class="form-row-2">
                        <div class="form-group">
                            <label>Stock</label>
                            <input type="number"
                                   name="stock"
                                   value="{{ old('stock', 0) }}"
                                   class="form-input">
                        </div>
                        <div class="form-group">
                            <label>Min Stock</label>
                            <input type="number"
                                   name="min_stock"
                                   value="{{ old('min_stock', 0) }}"
                                   class="form-input">
                        </div>
                    </div>
                </div>
            </div>
            {{-- RIGHT --}}
            <div class="form-column">
                {{-- IMAGE --}}
                <div class="form-card">
                    <h2>Product Image</h2>
                    <div class="form-group">
                        <input type="file"
                               name="image"
                               accept="image/*"
                               class="form-input">
                    </div>
                </div>
                {{-- EXTRA --}}
                <div class="form-card">
                    <h2>Extra Information</h2>
                    <div class="form-group">
                        <label>Description</label>
                        <textarea name="description"
                                  class="form-input">{{ old('description') }}</textarea>
                    </div>
                    <div class="form-group">
                        <label>Expiry Date</label>
                        <input type="date"
                               name="expiry_date"
                               value="{{ old('expiry_date') }}"
                               class="form-input">
                    </div>
                    <div class="form-group">
                        <label>Status</label>
                        <select name="status" class="form-select">
                            <option value="active">Active</option>
                            <option value="inactive">Inactive</option>
                        </select>
                    </div>
                </div>
            </div>
        </div>
        {{-- ACTIONS --}}
        <div class="form-actions">
            <button type="button" onclick="window.history.back()">Cancel</button>
            <button type="submit">Save Product</button>
        </div>
    </form>
</div>

@endsection
