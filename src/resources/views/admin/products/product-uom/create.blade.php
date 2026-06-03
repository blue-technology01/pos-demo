@extends('layouts.app')

@section('title', 'Create Product UOM')

@push('styles')
<style>

.page-header {
    margin-bottom: 20px;
}

.card {
    background: #fff;
    border-radius: 14px;
    padding: 20px;
    box-shadow: 0 2px 10px rgba(0,0,0,0.06);
    max-width: 900px;
}

.form-grid {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 18px;
}

.form-group {
    display: flex;
    flex-direction: column;
    margin-bottom: 15px;
}

.form-group.full {
    grid-column: 1 / -1;
}

label {
    font-weight: 600;
    font-size: 13px;
    margin-bottom: 6px;
    color: #374151;
}

input, select {
    padding: 10px 12px;
    border: 1px solid #e5e7eb;
    border-radius: 10px;
    outline: none;
    transition: 0.2s;
}

input:focus, select:focus {
    border-color: #2563eb;
    box-shadow: 0 0 0 3px rgba(37,99,235,0.1);
}

.section-title {
    font-size: 14px;
    font-weight: 700;
    margin: 15px 0 10px;
    color: #111827;
}

.btn-submit {
    background: #2563eb;
    color: #fff;
    padding: 12px 16px;
    border-radius: 10px;
    border: none;
    cursor: pointer;
    font-weight: 600;
    width: 200px;
}

.btn-submit:hover {
    background: #1d4ed8;
}

.btn-cancel {
    background: #e5e7eb;
    padding: 12px 16px;
    border-radius: 10px;
    border: none;
    cursor: pointer;
    text-decoration: none;
    color: #111;
    font-weight: 600;
    display: inline-flex;
    align-items: center;
    justify-content: center;
}

.row-actions {
    display: flex;
    justify-content: flex-end;
    gap: 12px;
    margin-top: 20px;
}

</style>
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
