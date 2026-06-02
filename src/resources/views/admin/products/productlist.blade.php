@extends('layouts.app')

@push('styles')
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined" />
    <link rel="stylesheet" href="{{ asset('assets/css/dashboard/product/products.css') }}" data-turbo-track="reload">
    <style>
        /* Scoped sizing to keep inline SVGs uniform */
        .search-wrap svg,
        .product-section__btn-add svg,
        .action-group svg {
            width: 16px;
            height: 16px;
            display: inline-block;
            vertical-align: middle;
            flex-shrink: 0;
        }
        
        /* Spacing adjustments for text buttons containing inline icons */
        .product-section__btn-add svg {
            margin-right: 6px;
        }

        .search-wrap {
            position: relative;
            display: flex;
            align-items: center;
        }

        .search-icon-inline {
            position: absolute;
            left: 12px;
            color: #9ca3af;
            pointer-events: none;
        }

        #searchInput {
            padding-left: 36px; /* Offsets input placeholder text for the search icon */
        }

        /* Clean up add button link wrapping */
        .product-section__btn-add a {
            color: inherit;
            text-decoration: none;
            display: flex;
            align-items: center;
            justify-content: center;
            width: 100%;
            height: 100%;
        }
    </style>
@endpush

@section('title', 'Product List')

@section('content')
    <div class="product-section">
        <div class="product-section__header">
            <div class="search-wrap">
                <svg class="search-icon-inline" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <circle cx="11" cy="11" r="8"></circle>
                    <line x1="21" y1="21" x2="16.65" y2="16.65"></line>
                </svg>
                <input type="text" id="searchInput" placeholder="Search users...">
                <button class="btn-filter">Filter</button>
            </div>
            <button type="button" class="product-section__btn-add">
                <a href="{{route('admin.create-product')}}">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                        <line x1="12" y1="5" x2="12" y2="19"></line>
                        <line x1="5" y1="12" x2="19" y2="12"></line>
                    </svg>
                    Add New Product
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
                            <td><div class="table-img-container"><img style="width: 50px" src="https://tse4.mm.bing.net/th/id/OIP.WL4mbHdZksTY2lSVZqUyaQHaHa?pid=Api&h=220&P=0" alt=""></div></td>
                            <td><span class="barcode-badge">88301948201</span></td>
                            <td><div class="product-title">Barbecue Beef Burger</div></td>
                            <td><span class="category-tag">Snacks</span></td>
                            <td><span class="unit-text">Pcs</span></td>
                            <td><span class="price-text">$20.00</span></td>
                            <td><span class="stock-text--in">45 Units</span></td>
                            <td><span class="status-badge status-badge--active">Active</span></td>
                            <td>
                                <div class="action-group">
                                    <button class="btn-action btn-action--edit" title="Edit Item" data-action="edit">
                                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                            <path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"></path>
                                            <path d="M18.5 2.5a2.121 2.121 0 1 1 3 3L12 15l-4 1 1-4 9.5-9.5z"></path>
                                        </svg>
                                    </button>
                                    <button class="btn-action btn-action--delete" title="Delete Item" data-action="delete">
                                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                            <polyline points="3 6 5 6 21 6"></polyline>
                                            <path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"></path>
                                            <line x1="10" y1="11" x2="10" y2="17"></line>
                                            <line x1="14" y1="11" x2="14" y2="17"></line>
                                        </svg>
                                    </button>
                                </div>
                            </td>
                        </tr>
                        @for($i = 0; $i < 10; $i++)
                            <tr data-product-id="101">
                            <td>
                                <div class="table-img-container">
                                    <img style="width: 50px" src="https://www.coca-cola.com/content/dam/onexp/nl/nl/brand-pages/coca-cola/coca_cola_original_taste_nl_april_2024.png" alt="">
                                </div>
                            </td>
                            <td><span class="barcode-badge">88301948201</span></td>
                            <td><div class="product-title">Barbecue Beef Burger</div></td>
                            <td><span class="category-tag">Snacks</span></td>
                            <td><span class="unit-text">Pcs</span></td>
                            <td><span class="price-text">$20.00</span></td>
                            <td><span class="stock-text--in">45 Units</span></td>
                            <td><span class="status-badge status-badge--active">Active</span></td>
                            <td>
                                <div class="action-group">
                                    <button class="btn-action btn-action--edit" title="Edit Item" data-action="edit">
                                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                            <path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"></path>
                                            <path d="M18.5 2.5a2.121 2.121 0 1 1 3 3L12 15l-4 1 1-4 9.5-9.5z"></path>
                                        </svg>
                                    </button>
                                    <button class="btn-action btn-action--delete" title="Delete Item" data-action="delete">
                                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                            <polyline points="3 6 5 6 21 6"></polyline>
                                            <path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"></path>
                                            <line x1="10" y1="11" x2="10" y2="17"></line>
                                            <line x1="14" y1="11" x2="14" y2="17"></line>
                                        </svg>
                                    </button>
                                </div>
                            </td>
                        </tr>
                        @endfor
                    </tbody>
                </table>
            </div>

            {{-- Pagination --}}
            <div class="pm-pagination">
                <div class="pm-pagination__meta">
                    <span class="pm-pagination__text"></span>
                    <div class="pm-pagination__per-page">
                        <label for="per-page-select">Show:</label>
                        <select id="per-page-select" class="pm-pagination__select">
                            <option value="15">15</option>
                            <option value="25" selected>25</option>
                            <option value="50">50</option>
                        </select>
                    </div>
                </div>
                <div class="pm-pagination__links" id="paginationLinks"></div>
            </div>
        </div>
    </div>
@endsection