@extends('layouts.app')

@section('title', 'Edit Product UOM')

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
    font-size:22px;
    font-weight:600;
}

.card{
    background:#fff;
    padding:20px;
    border-radius:12px;
    box-shadow:0 2px 8px rgba(0,0,0,.05);
    max-width:700px;
}

.form-group{
    margin-bottom:15px;
}

label{
    display:block;
    font-weight:500;
    margin-bottom:6px;
}

input, select{
    width:100%;
    padding:10px;
    border:1px solid #e5e7eb;
    border-radius:8px;
    outline:none;
}

.row{
    display:grid;
    grid-template-columns:1fr 1fr;
    gap:15px;
}

.btn-group{
    display:flex;
    gap:10px;
    margin-top:20px;
}

.btn-save{
    background:#2563eb;
    color:#fff;
    padding:10px 16px;
    border:none;
    border-radius:8px;
    cursor:pointer;
}

.btn-cancel{
    background:#e5e7eb;
    padding:10px 16px;
    border:none;
    border-radius:8px;
    cursor:pointer;
    text-decoration:none;
    color:#111;
}
</style>
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

@push('scripts')
<script>
    console.log('Edit Product UOM Loaded');
</script>
@endpush
