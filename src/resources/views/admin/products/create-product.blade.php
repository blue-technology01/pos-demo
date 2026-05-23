@extends('layouts.app')
@push('styles')
    <link rel="stylesheet" href="{{ asset('assets/css/dashboard/product/create.css') }}">
@endpush

@section('title', 'Dahboard Create Product')
@section('content')
    <div class="form-section">

        {{-- Top Heading Header --}}
        <div class="form-section__header">
            <h1 class="form-section__title">Create New Product</h1>
        </div>
        <form action="#" method="POST" id="product-create-form" enctype="multipart/form-data">
            @csrf

            <div class="form-grid">

                <div class="form-column">

                    {{-- Identification Context info --}}
                    <div class="form-card">
                        <h2 class="form-card__title">General Details</h2>

                        <div class="form-group">
                            <label class="form-label" for="product_name">Product Name *</label>
                            <input type="text" id="product_name" name="name" class="form-input" placeholder="e.g., Crispy Chicken Burger" required autocomplete="off">
                        </div>

                        <div class="form-row-2">
                            <div class="form-group">
                                <label class="form-label" for="product_barcode">Barcode / SKU *</label>
                                <div class="barcode-input-wrapper">
                                    <input type="text" id="product_barcode" name="barcode" class="form-input" placeholder="Scan or type barcode string" required>
                                    <i data-lucide="scan-barcode" class="barcode-icon"></i>
                                </div>
                            </div>

                            <div class="form-group">
                                <label class="form-label" for="product_category">Category *</label>
                                <select id="product_category" name="category_id" class="form-select" required>
                                    <option value="" disabled selected>Select Item Group Category</option>
                                    <option value="drink">Drinks</option>
                                    <option value="snack">Snacks / Appetizers</option>
                                    <option value="pizza">Pizzas</option>
                                </select>
                            </div>
                        </div>
                    </div>

                    {{-- Block 2: Financial Ledger Configurations --}}
                    <div class="form-card">
                        <h2 class="form-card__title">Pricing & Inventory Scales</h2>

                        <div class="form-row-2">
                            <div class="form-group">
                                <label class="form-label" for="product_unit">Measurement Unit *</label>
                                <select id="product_unit" name="unit_id" class="form-select" required>
                                    <option value="" disabled selected>Select Scale Denominator</option>
                                    <option value="pcs">Pieces (PCS)</option>
                                    <option value="can">Can (CAN)</option>
                                    <option value="kg">Kilograms (KG)</option>
                                </select>
                            </div>

                            <div class="form-group">
                                <label class="form-label" for="product_price">Base Retail Price ($) *</label>
                                <input type="number" id="product_price" name="price" class="form-input" step="0.01" min="0.00" placeholder="0.00" required>
                            </div>
                        </div>

                        <div class="form-row-2">
                            <div class="form-group">
                                <label class="form-label" for="product_stock">Initial Stock Quantity</label>
                                <input type="number" id="product_stock" name="stock" class="form-input" min="0" value="0">
                            </div>

                            <div class="form-group">
                                <label class="form-label" for="stock_alert_threshold">Low Stock Warning Limit</label>
                                <input type="number" id="stock_alert_threshold" name="alert_limit" class="form-input" min="0" value="5">
                            </div>
                        </div>
                    </div>

                </div>

                <div class="form-column">

                    {{-- Media Selector Frame --}}
                    <div class="form-card">
                        <h2 class="form-card__title">Product Graphic Image</h2>

                        <div class="form-group">
                            <label class="form-label">Upload Presentation Image Thumbnail</label>
                            <div class="image-uploader" id="dropzone-trigger">
                                <i data-lucide="cloud-lightning" class="image-uploader-icon"></i>
                                <p class="uploader-text">Drag and drop file here or <span>browse local files</span></p>
                                <input type="file" id="hidden-file-input" name="image" accept="image/*" style="display: none;">
                            </div>
                            <p class="uploader-hint">Supports high quality transparent PNG or JPEG structures up to 2MB maximum.</p>
                        </div>
                    </div>

                    {{-- Status Switches Card Block --}}
                    <div class="form-card">
                        <h2 class="form-card__title">Visibility & Availability</h2>

                        <div class="form-group form-group--switch">
                            <label class="status-toggle">
                                <input type="checkbox" name="status" value="active" checked>
                                <div>
                                    <div class="toggle-title">Publish Active Status</div>
                                    <div class="toggle-desc">Immediately renders product viewable inside the cashier terminal selection grid.</div>
                                </div>
                            </label>

                            <label class="status-toggle">
                                <input type="checkbox" name="is_featured" value="1">
                                <div>
                                    <div class="toggle-title">Pin to Special Menu</div>
                                    <div class="toggle-desc">Elevates visibility by anchoring card to the top category display section.</div>
                                </div>
                            </label>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Form Action Drawer Trigger Footer Toolbar --}}
            <div class="form-actions">
                <button type="button" class="btn-form btn-form--cancel" onclick="window.history.back()">Cancel</button>
                <button type="submit" class="btn-form btn-form--submit">Save & Register Product</button>
            </div>

        </form>
    </div>
@endsection
@push('scripts')

@endpush
