@extends('layouts.app')

@section('title', 'Create Product UOM')

@push('styles')
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600&display=swap" rel="stylesheet" />
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@tabler/icons-webfont@latest/dist/tabler-icons.min.css" />
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
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

            {{-- PRODUCT SEARCH --}}
            <div class="form-group">
                <label for="product-select">Product *</label>
                <select name="product_code" id="product-select" class="form-control" required>
                    <option value="">Search product...</option>

                    {{-- Keep old value after validation error --}}
                    @if(old('product_code'))
                        <option value="{{ old('product_code') }}" selected>
                            {{ old('product_code') }}
                        </option>
                    @endif
                </select>
                @error('product_code')
                    <span class="invalid-feedback">{{ $message }}</span>
                @enderror
            </div>

            {{-- UOM --}}
            <div class="form-group">
                <label for="uom-select">Unit of measure *</label>
                <select name="uom_code" id="uom-select" required>
                    <option value="">Select UOM</option>
                    @foreach($uoms as $uom)
                        <option value="{{ $uom->code }}" {{ old('uom_code') == $uom->code ? 'selected' : '' }}>
                            {{ $uom->name }}
                        </option>
                    @endforeach
                </select>
                @error('uom_code')
                    <span class="invalid-feedback">{{ $message }}</span>
                @enderror
            </div>

            {{-- UOM ROLE --}}
            <div class="form-group">
                <label for="uom-role">UOM role *</label>
                <select name="uom_role" id="uom-role" required>
                    <option value="retail">Retail</option>
                    <option value="bulk">Bulk</option>
                    <option value="alternative">Alternative</option>
                </select>
            </div>

        </div>

        {{-- PRICING --}}
        <div class="section-title">Pricing & rules</div>

        <div class="form-grid">

            <div class="form-group">
                <label for="quantity_per_unit">Quantity per unit *</label>
                <input type="number" step="0.01" min="0.01" name="quantity_per_unit"
                       id="quantity_per_unit" value="{{ old('quantity_per_unit') }}" required>
            </div>

            <div class="form-group">
                <label for="cost_price">Cost price</label>
                <input type="number" step="0.01" min="0" name="cost_price"
                       id="cost_price" value="{{ old('cost_price') }}">
            </div>

            <div class="form-group">
                <label for="selling_price">Selling price</label>
                <input type="number" step="0.01" min="0" name="selling_price"
                       id="selling_price" value="{{ old('selling_price') }}">
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
            <label for="barcode">Barcode (optional)</label>
            <input type="text" name="barcode" id="barcode" value="{{ old('barcode') }}"
                   placeholder="Scan or enter barcode">
        </div>

        {{-- ACTIONS --}}
        <div class="row-actions">
            <a href="{{ route('admin.product-uom.index') }}" class="btn-cancel" onclick="showLoader()">Cancel</a>
            <button type="submit" class="btn-submit" onclick="showLoader()">Save UOM</button>
        </div>

    </form>
</div>

@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
<script>
    $(document).ready(function () {
        $('#product-select').select2({
            placeholder: "Search product...",
            minimumInputLength: 2,
            width: '100%',
            ajax: {
                url: "{{ route('admin.product-uom.search-product') }}",
                dataType: 'json',
                delay: 300,
                data: function (params) {
                    return { q: params.term };
                },
                processResults: function (data) {
                    return {
                        results: data.map(function (product) {
                            return {
                                id: product.code,
                                text: product.name + " (" + product.code + ")"
                            };
                        })
                    };
                }
            }
        });
    });
</script>
@endpush
