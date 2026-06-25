@extends('layouts.app')

@section('title', 'Edit Product UOM')

@push('styles')
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600&display=swap" rel="stylesheet" />
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@tabler/icons-webfont@latest/dist/tabler-icons.min.css" />
<link rel="stylesheet" href="{{ asset('assets/css/dashboard/product/edit-uom.css') }}">
@endpush

@section('content')

<x-alert />

<div class="page-header">
    <h1 class="page-title">Edit Product UOM</h1>
</div>

<div class="card">

<form action="{{ route('admin.product-uom.update', $productUom->id) }}" method="POST">
@csrf
@method('PUT')

{{-- PRODUCT --}}
<div class="form-group">
    <label>Product</label>
    <div class="readonly-box">
        {{ $productUom->product->name ?? $productUom->product_code }}
    </div>
</div>

{{-- UOM --}}
<div class="form-group">
    <label>Unit of Measure</label>
    <div class="readonly-box">
        {{ $productUom->uom->name ?? $productUom->uom_code }}
    </div>
</div>

<div class="row">

    <div class="form-group">
        <label>Quantity per unit</label>
        <input type="number" step="0.01" min="0.01" name="quantity_per_unit"
               value="{{ old('quantity_per_unit', $productUom->quantity_per_unit) }}">
        @error('quantity_per_unit') <span class="invalid-feedback">{{ $message }}</span> @enderror
    </div>

    <div class="form-group">
        <label>Cost price</label>
        <input type="number" step="0.01" min="0" name="cost_price"
               value="{{ old('cost_price', $productUom->cost_price) }}">
        @error('cost_price') <span class="invalid-feedback">{{ $message }}</span> @enderror
    </div>

</div>

<div class="row">

    <div class="form-group">
        <label>Selling price</label>
        <input type="number" step="0.01" min="0" name="selling_price"
               value="{{ old('selling_price', $productUom->selling_price) }}">
        @error('selling_price') <span class="invalid-feedback">{{ $message }}</span> @enderror
    </div>

    <div class="form-group">
        <label>Barcode</label>
        <input type="text" name="barcode"
               value="{{ old('barcode', $productUom->barcode) }}">
        @error('barcode') <span class="invalid-feedback">{{ $message }}</span> @enderror
    </div>

</div>

{{-- DEFAULT --}}
<div class="form-group">
    <label>
        <input type="checkbox" name="is_default" value="1"
               {{ old('is_default', $productUom->is_default) ? 'checked' : '' }}>
        Default Unit
    </label>
</div>

{{-- ACTIONS --}}
<div class="btn-group">
    <button type="submit" class="btn-save" onclick="showLoader()" onclick="showLoader()">Update</button>
    <a href="{{ route('admin.product-uom.index') }}" class="btn-cancel" onclick="showLoader()" >Cancel</a>
</div>

</form>

</div>

@endsection
