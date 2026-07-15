@extends('layouts.app')

@push('styles')
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600&display=swap" rel="stylesheet" />
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@tabler/icons-webfont@latest/dist/tabler-icons.min.css" />
    <link rel="stylesheet" href="{{ asset('assets/css/dashboard/stock/create.css') }}">
@endpush

@section('title', 'New Stock Adjustment')

@section('content')
    <div class="stock-page-header">
        <div>
            <h4 class="stock-page-title">New Stock Adjustment</h4>
            <p class="stock-page-subtitle">Submit a stock adjustment request for approval.</p>
        </div>
        <a href="{{ route('admin.products.stock.index') }}" class="stock-btn stock-btn-outline">
            <i class="ti ti-arrow-left"></i> Back to List
        </a>
    </div>

    <div class="stock-card">
        <form method="POST" action="{{ route('admin.products.stock.store') }}" class="stock-form">
            @csrf

            <div class="stock-form-grid">
                <div class="stock-field">
                    <label for="product_code" class="stock-label">Product <span class="stock-required">*</span></label>
                    <select name="product_code" id="product_code"
                        class="stock-select {{ $errors->has('product_code') ? 'is-invalid' : '' }}">
                        <option value="">-- Select Product --</option>
                        @foreach ($products as $product)
                            <option value="{{ $product->code }}"
                                @selected(old('product_code') === $product->code)>
                                {{ $product->name }} ({{ $product->code }})
                            </option>
                        @endforeach
                    </select>
                    @error('product_code')
                        <span class="stock-error">{{ $message }}</span>
                    @enderror
                </div>

                <div class="stock-field">
                    <label for="warehouse_id" class="stock-label">Warehouse <span class="stock-required">*</span></label>
                    <select name="warehouse_id" id="warehouse_id"
                        class="stock-select {{ $errors->has('warehouse_id') ? 'is-invalid' : '' }}">
                        <option value="">-- Select Warehouse --</option>
                        @foreach ($warehouses as $warehouse)
                            <option value="{{ $warehouse->id }}"
                                @selected((int) old('warehouse_id') === $warehouse->id)>
                                {{ $warehouse->name }}
                            </option>
                        @endforeach
                    </select>
                    @error('warehouse_id')
                        <span class="stock-error">{{ $message }}</span>
                    @enderror
                </div>

                <div class="stock-field">
                    <label for="adjustment_date" class="stock-label">Adjustment Date <span class="stock-required">*</span></label>
                    <input type="date" name="adjustment_date" id="adjustment_date"
                        class="stock-input {{ $errors->has('adjustment_date') ? 'is-invalid' : '' }}"
                        value="{{ old('adjustment_date', now()->toDateString()) }}">
                    @error('adjustment_date')
                        <span class="stock-error">{{ $message }}</span>
                    @enderror
                </div>

                <div class="stock-field">
                    <label for="new_quantity" class="stock-label">New Quantity <span class="stock-required">*</span></label>
                    <input type="number" min="0" name="new_quantity" id="new_quantity"
                        class="stock-input {{ $errors->has('new_quantity') ? 'is-invalid' : '' }}"
                        value="{{ old('new_quantity') }}">
                    @error('new_quantity')
                        <span class="stock-error">{{ $message }}</span>
                    @enderror
                </div>

                <div class="stock-field">
                    <label for="reason_code" class="stock-label">Reason <span class="stock-required">*</span></label>
                    <select name="reason_code" id="reason_code"
                        class="stock-select {{ $errors->has('reason_code') ? 'is-invalid' : '' }}">
                        <option value="">-- Select Reason --</option>
                        <option value="damage" @selected(old('reason_code') === 'damage')>Damage</option>
                        <option value="break" @selected(old('reason_code') === 'break')>Break</option>
                        <option value="other" @selected(old('reason_code') === 'other')>Other</option>
                    </select>
                    @error('reason_code')
                        <span class="stock-error">{{ $message }}</span>
                    @enderror
                </div>

                <div class="stock-field stock-field-full">
                    <label for="remark" class="stock-label">Remark</label>
                    <textarea name="remark" id="remark" rows="3"
                        class="stock-textarea {{ $errors->has('remark') ? 'is-invalid' : '' }}"
                        placeholder="Optional notes about this adjustment">{{ old('remark') }}</textarea>
                    @error('remark')
                        <span class="stock-error">{{ $message }}</span>
                    @enderror
                </div>
            </div>

            <div class="stock-form-actions">
                <a href="{{ route('admin.products.stock.index') }}" class="stock-btn stock-btn-outline">Cancel</a>
                <button type="submit" class="stock-btn stock-btn-primary">
                    <i class="ti ti-check"></i> Submit Adjustment
                </button>
            </div>
        </form>
    </div>
@endsection

