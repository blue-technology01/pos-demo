@extends('layouts.app')

@section('title', 'Create Product UOM')

@push('styles')
    <link rel="stylesheet" href="{{ asset('assets/css/dashboard/product/create-uom.css') }}">
@endpush

@section('content')

<x-alert />

<div class="page-header">
    <h2>Create Product UOM</h2>
</div>

<div class="card">

<form action="{{ route('admin.product-uom.store') }}" method="POST">
    @csrf

    <div class="section-title">Product Information</div>

    <div class="form-grid">

        <div class="form-group">
            <label>Product</label>
            <select name="product_code" required>
                <option value="">Select Product</option>
                @foreach($products as $product)
                    <option value="{{ $product->code }}">{{ $product->name }}</option>
                @endforeach
            </select>
        </div>

        <div class="form-group">
            <label>Unit of Measure</label>
            <select name="uom_code" required>
                <option value="">Select UOM</option>
                @foreach($uoms as $uom)
                    <option value="{{ $uom->code }}">{{ $uom->name }}</option>
                @endforeach
            </select>
        </div>

        <div class="form-group">
            <label>UOM Role</label>
            <select name="uom_role" required>
                <option value="retail">Retail</option>
                <option value="bulk">Bulk</option>
                <option value="alternative">Alternative</option>
            </select>
        </div>
    </div>
    <div class="section-title">Pricing & Rules</div>
    <div class="form-grid">
        <div class="form-group">
            <label>Quantity per Unit</label>
            <input type="number" step="0.01" name="quantity_per_unit" value="1.00" required>
        </div>

        <div class="form-group">
            <label>Default Unit</label>
            <select name="is_default">
                <option value="0">No</option>
                <option value="1">Yes</option>
            </select>
        </div>

        <div class="form-group">
            <label>Cost Price</label>
            <input type="number" name="cost_price" step="0.01" min="0" placeholder="0.00" required>
        </div>

        <div class="form-group">
            <label>Selling Price</label>
            <input type="number" name="selling_price" step="0.01" min="0" placeholder="0.00" required>
        </div>
    </div>

    <div class="form-group full">
        <label>Barcode (Optional)</label>
        <input type="text" name="barcode">
    </div>

    <div class="row-actions">
        <a href="{{ route('admin.product-uom.index') }}" class="btn-cancel">
            Cancel
        </a>
        <button type="submit" class="btn-submit">
            Save Product UOM
        </button>
    </div>
</form>

</div>

@endsection
