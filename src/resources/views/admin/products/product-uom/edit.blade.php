@extends('layouts.app')

@section('title', 'Edit Product UOM')

@push('styles')
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined" />
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

        <div class="form-group">
            <label>Product Code</label>
            <input type="text" name="product_code" value="{{ $productUom->product_code }}" readonly>
        </div>

        <div class="form-group">
            <label>Unit of Measure</label>
            <input type="text" name="uom_code" value="{{ $productUom->uom_code }}" readonly>
        </div>
        <div class="row">
            <div class="form-group">
                <label>Quantity per Unit</label>
                <input type="number" step="0.01" name="quantity_per_unit"
                       value="{{ $productUom->quantity_per_unit }}">
            </div>
            <div class="form-group">
                <label>Cost Price</label>
                <input type="number" step="0.01" name="cost_price"
                       value="{{ $productUom->cost_price }}">
            </div>
        </div>
        <div class="row">
            <div class="form-group">
                <label>Selling Price</label>
                <input type="number" step="0.01" name="selling_price"
                       value="{{ $productUom->selling_price }}">
            </div>
            <div class="form-group">
                <label>Barcode</label>
                <input type="text" name="barcode" value="{{ $productUom->barcode }}">
            </div>
        </div>

        <div class="form-group">
            <label>
                <input type="checkbox" name="is_default"
                       {{ $productUom->is_default ? 'checked' : '' }}>
                Default Unit
            </label>
        </div>

        <div class="btn-group">
            <button type="submit" class="btn-save">Update</button>

            <a href="{{ route('admin.product-uom.index') }}" class="btn-cancel">
                Cancel
            </a>
        </div>
    </form>
</div>
@endsection
