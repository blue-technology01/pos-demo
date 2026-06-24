@extends('layouts.app')

@section('title', 'Create Product UOM')

@push('styles')
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600&display=swap" rel="stylesheet" />
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@tabler/icons-webfont@latest/dist/tabler-icons.min.css" />
    <link rel="stylesheet" href="{{ asset('assets/css/dashboard/product/create-uom.css') }}">
@endpush

@section('content')

<x-alert />

{{-- ── Page Header ── --}}
<div class="page-header">
    <h2>Create product UOM</h2>
</div>

{{-- ── Form Card ── --}}
<div class="card">

    <form action="{{ route('admin.product-uom.store') }}" method="POST">
        @csrf

        {{-- ── Product Information ── --}}
        <div class="section-title">Product information</div>

        <div class="form-grid">

            <div class="form-group">
                <label for="product_code">
                    Product <span style="color:#dc2626">*</span>
                </label>
                <select name="product_code" id="product_code" required>
                    <option value="">Select product</option>
                    @foreach($products as $product)
                        <option
                            value="{{ $product->code }}"
                            {{ old('product_code') == $product->code ? 'selected' : '' }}
                        >
                            {{ $product->name }}
                        </option>
                    @endforeach
                </select>
                @error('product_code')
                    <span class="invalid-feedback">{{ $message }}</span>
                @enderror
            </div>

            <div class="form-group">
                <label for="uom_code">
                    Unit of measure <span style="color:#dc2626">*</span>
                </label>
                <select name="uom_code" id="uom_code" required>
                    <option value="">Select UOM</option>
                    @foreach($uoms as $uom)
                        <option
                            value="{{ $uom->code }}"
                            {{ old('uom_code') == $uom->code ? 'selected' : '' }}
                        >
                            {{ $uom->name }}
                        </option>
                    @endforeach
                </select>
                @error('uom_code')
                    <span class="invalid-feedback">{{ $message }}</span>
                @enderror
            </div>

            <div class="form-group">
                <label for="uom_role">
                    UOM role <span style="color:#dc2626">*</span>
                </label>
                <select name="uom_role" id="uom_role" required>
                    <option value="retail"      {{ old('uom_role') == 'retail'      ? 'selected' : '' }}>Retail</option>
                    <option value="bulk"        {{ old('uom_role') == 'bulk'        ? 'selected' : '' }}>Bulk</option>
                    <option value="alternative" {{ old('uom_role') == 'alternative' ? 'selected' : '' }}>Alternative</option>
                </select>
                @error('uom_role')
                    <span class="invalid-feedback">{{ $message }}</span>
                @enderror
            </div>

        </div>

        {{-- ── Pricing & Rules ── --}}
        <div class="section-title">Pricing & rules</div>

        <div class="form-grid">

            <div class="form-group">
                <label for="quantity_per_unit">
                    Quantity per unit <span style="color:#dc2626">*</span>
                </label>
                <input
                    type="number"
                    step="0.01"
                    name="quantity_per_unit"
                    id="quantity_per_unit"
                    value="{{ old('quantity_per_unit', '1.00') }}"
                    required
                >
                @error('quantity_per_unit')
                    <span class="invalid-feedback">{{ $message }}</span>
                @enderror
            </div>

            <div class="form-group">
                <label for="is_default">Default unit</label>
                <select name="is_default" id="is_default">
                    <option value="0" {{ old('is_default') == '0' ? 'selected' : '' }}>No</option>
                    <option value="1" {{ old('is_default') == '1' ? 'selected' : '' }}>Yes</option>
                </select>
            </div>

            <div class="form-group">
                <label for="cost_price">
                    Cost price <span style="color:#dc2626">*</span>
                </label>
                <input
                    type="number"
                    name="cost_price"
                    id="cost_price"
                    step="0.01"
                    min="0"
                    placeholder="0.00"
                    value="{{ old('cost_price') }}"
                    required
                >
                @error('cost_price')
                    <span class="invalid-feedback">{{ $message }}</span>
                @enderror
            </div>

            <div class="form-group">
                <label for="selling_price">
                    Selling price <span style="color:#dc2626">*</span>
                </label>
                <input
                    type="number"
                    name="selling_price"
                    id="selling_price"
                    step="0.01"
                    min="0"
                    placeholder="0.00"
                    value="{{ old('selling_price') }}"
                    required
                >
                @error('selling_price')
                    <span class="invalid-feedback">{{ $message }}</span>
                @enderror
            </div>

        </div>

        {{-- ── Barcode ── --}}
        <div class="form-group full">
            <label for="barcode">Barcode <span style="color:var(--color-text-tertiary,#9ca3af);font-weight:400">(optional)</span></label>
            <input
                type="text"
                name="barcode"
                id="barcode"
                value="{{ old('barcode') }}"
                placeholder="Scan or enter barcode"
                autocomplete="off"
            >
        </div>

        {{-- ── Actions ── --}}
        <div class="row-actions">
            <a href="{{ route('admin.product-uom.index') }}" onclick="showLoader()" class="btn-cancel">
                <i class="ti ti-x" aria-hidden="true"></i> Cancel
            </a>
            <button type="submit" class="btn-submit" onclick="showLoader()" >
                <i class="ti ti-device-floppy" aria-hidden="true"></i> Save product UOM
            </button>
        </div>

    </form>

</div>

@endsection