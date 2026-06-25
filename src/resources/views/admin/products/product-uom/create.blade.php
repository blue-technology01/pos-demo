@extends('layouts.app')

@section('title', 'Create Product UOM')

@push('styles')
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600&display=swap" rel="stylesheet" />
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@tabler/icons-webfont@latest/dist/tabler-icons.min.css" />
<link rel="stylesheet" href="{{ asset('assets/css/dashboard/product/create-uom.css') }}">
@endpush

@section('content')

<x-alert />

<div class="page-header">
    <h2>Create product UOM</h2>
</div>

<div class="card">

<form action="{{ route('admin.product-uom.store') }}" method="POST">
@csrf

{{-- PRODUCT INFO --}}
<div class="section-title">Product information</div>

<div class="form-grid">

    <div class="form-group">
        <label>Product *</label>
        <select name="product_code" required>
            <option value="">Select product</option>
            @foreach($products as $product)
                <option value="{{ $product->code }}" {{ old('product_code') == $product->code ? 'selected' : '' }}>
                    {{ $product->name }}
                </option>
            @endforeach
        </select>
        @error('product_code') <span class="invalid-feedback">{{ $message }}</span> @enderror
    </div>

    <div class="form-group">
        <label>Unit of measure *</label>
        <select name="uom_code" required>
            <option value="">Select UOM</option>
            @foreach($uoms as $uom)
                <option value="{{ $uom->code }}" {{ old('uom_code') == $uom->code ? 'selected' : '' }}>
                    {{ $uom->name }}
                </option>
            @endforeach
        </select>
        @error('uom_code') <span class="invalid-feedback">{{ $message }}</span> @enderror
    </div>

    <div class="form-group">
        <label>UOM role *</label>
        <select name="uom_role" required>
            <option value="retail">Retail</option>
            <option value="bulk">Bulk</option>
            <option value="alternative">Alternative</option>
        </select>
    </div>

</div>

{{-- PRICING --}}
<div class="section-title">Pricing & rules (optional in Option B)</div>

<div class="form-grid">

    <div class="form-group">
        <label>Quantity per unit *</label>
        <input type="number" step="0.01" min="0.01" name="quantity_per_unit"
               value="{{ old('quantity_per_unit') }}" required>
    </div>

    <div class="form-group">
        <label>Cost price</label>
        <input type="number" step="0.01" min="0" name="cost_price"
               value="{{ old('cost_price') }}">
    </div>

    <div class="form-group">
        <label>Selling price</label>
        <input type="number" step="0.01" min="0" name="selling_price"
               value="{{ old('selling_price') }}">
    </div>

</div>

{{-- DEFAULT --}}
<div class="form-group">
    <label>
        <input type="checkbox" name="is_default" value="1" {{ old('is_default') ? 'checked' : '' }}>
        Default unit
    </label>
</div>

{{-- BARCODE --}}
<div class="form-group full">
    <label>Barcode (optional)</label>
    <input type="text" name="barcode" value="{{ old('barcode') }}"
           placeholder="Scan or enter barcode">
</div>

{{-- ACTIONS --}}
<div class="row-actions">
    <a href="{{ route('admin.product-uom.index') }}" class="btn-cancel" onclick="showLoader()" >Cancel</a>
    <button type="submit" class="btn-submit" onclick="showLoader()" >Save UOM</button>
</div>

</form>

</div>

@endsection
