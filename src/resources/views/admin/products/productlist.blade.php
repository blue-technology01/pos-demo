@extends('layouts.app')

@push('styles')
    <link rel="stylesheet" href="{{ asset('assets/css/dashboard/product/products.css') }}">
@endpush

@section('title', 'Dashboard Product List')

@section('content')
    <div class="product-section">
        <div class="product-section__header">
            <div>
                <h1 class="product-section__title">Product Catalog</h1>
            </div>
            <button type="button" class="product-section__btn-add">
                <a href="{{route('admin.create-product')}}">
                    + Add New Product
                </a>
            </button>
        </div>

        <div class="table-card">
            <div class="table-responsive">
                <table class="table-custom">
                    <thead>
                        <tr>
                            <th class="col-image">Image</th>
                            <th>Barcode</th>
                            <th>Product Name</th>
                            <th>Category</th>
                            <th>Unit</th>
                            <th>Price</th>
                            <th>Stock</th>
                            <th>Status</th>
                            <th class="col-actions">Action</th>
                        </tr>
                    </thead>
                    <tbody id="product-table-body">
                        <tr data-product-id="101">
                            <td><div class="table-img-container">🍔</div></td>
                            <td><span class="barcode-badge">88301948201</span></td>
                            <td><div class="product-title">Barbecue Beef Burger</div></td>
                            <td><span class="category-tag">Snacks</span></td>
                            <td><span class="unit-text">Pcs</span></td>
                            <td><span class="price-text">$20.00</span></td>
                            <td><span class="stock-text--in">45 Units</span></td>
                            <td><span class="status-badge status-badge--active">Active</span></td>
                            <td>
                                <div class="action-group">
                                    <button class="btn-action" title="Edit Item" data-action="edit">
                                        <i data-lucide="edit-3"></i>
                                    </button>
                                    <button class="btn-action btn-action--delete" title="Delete Item" data-action="delete">
                                        <i data-lucide="trash-2"></i>
                                    </button>
                                </div>
                            </td>
                        </tr>
                        <tr data-product-id="102">
                            <td><div class="table-img-container">🥤</div></td>
                            <td><span class="barcode-badge">88301948202</span></td>
                            <td><div class="product-title">Iced Berry Fusion</div></td>
                            <td><span class="category-tag">Drinks</span></td>
                            <td><span class="unit-text">Can</span></td>
                            <td><span class="price-text">$5.50</span></td>
                            <td><span class="stock-text--out">0 Units</span></td>
                            <td><span class="status-badge status-badge--inactive">Inactive</span></td>
                            <td>
                                <div class="action-group">
                                    <button class="btn-action" title="Edit Item" data-action="edit">
                                        <i data-lucide="edit-3"></i>
                                    </button>
                                    <button class="btn-action btn-action--delete" title="Delete Item" data-action="delete">
                                        <i data-lucide="trash-2"></i>
                                    </button>
                                </div>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    {{-- Unified Script Pipeline Initialization hooks --}}
    <script src="https://unpkg.com/lucide@latest"></script>
    <script>
        $(document).ready(function() {
            // Render Lucide custom interface vector graphics inside data tags
            if (window.lucide) {
                lucide.createIcons();
            }

            // AJAX Dynamic Event Handlers Mapping Hooks
            $('#product-table-body').on('click', '[data-action]', function(e) {
                const actionType = $(this).data('action');
                const targetRowId = $(this).closest('tr').data('product-id');

                if (actionType === 'edit') {
                    console.log('Initialize update pipeline hook for Product ID:', targetRowId);
                    // Pass tracking reference data control parameters to your existing modal handler logic here
                } else if (actionType === 'delete') {
                    console.log('Initialize removal transaction alert criteria sequence logic for ID:', targetRowId);
                }
            });
        });
    </script>
@endpush
