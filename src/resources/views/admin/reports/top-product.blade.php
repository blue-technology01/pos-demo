@extends('layouts.app')

@push('styles')
    <link rel="stylesheet" href="{{ asset('assets/css/dashboard/report/top-product.css') }}" data-turbo-track="reload" >
    <style>
        .view-toggle .view-btn {
            transition: all 0.2s;
        }
        .view-toggle .view-btn.active {
            background-color: #3b82f6;
            color: white;
        }
        .hidden {
            display: none !important;
        }
        #viewSelector {
            background-color: white;
            border: 2px solid #e5e7eb;
            border-radius: 8px;
            padding: 10px 16px;
            font-size: 14px;
            font-weight: 500;
            color: #374151;
            cursor: pointer;
            transition: all 0.3s ease;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.05);
        }

        #viewSelector:hover {
            border-color: #3b82f6;
            box-shadow: 0 4px 6px -1px rgba(59, 130, 246, 0.1);
        }

        #viewSelector:focus {
            outline: none;
            border-color: #3b82f6;
            box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.2);
        }

        /* Option Styling */
        #viewSelector option {
            padding: 10px;
        }
    </style>
@endpush

@section('title', 'Top Products Reports')

@section('content')
<div class="unit-section">
    <div class="unit-section__header">
        <div class="page-title-wrap">
            <h1 class="page-title">Top Products</h1>
            <p class="page-subtitle">Best-selling items ranked by quantity sold</p>
        </div>

        <div class="search-wrap flex items-center gap-3">
            <div class="search-input-wrap">
                <i class="ti ti-search search-icon"></i>
                <input type="text" id="searchInput" placeholder="Search product name...">
            </div>

            <select class="filter-category" id="filterCategory">
                <option value="">All Categories</option>
                <option value="beverages">Beverages</option>
                <option value="groceries">Groceries</option>
                <option value="snacks">Snacks & Bakery</option>
                <option value="household">Household</option>
                <option value="personal">Personal Care</option>
            </select>

            <button class="btn-filter" id="filter-btn">
                {{-- <i class="ti ti-filter"></i>  --}}
                Filter
            </button>
            <button class="btn-reset" id="reset-btn">
                {{-- <i class="ti ti-refresh"></i>  --}}
                Reset
            </button>
            <button class="btn-filter" style="background-color: green;" id="filter-btn" onclick="alert('Exporting to Excel...')">
                {{-- <i class="ti ti-filter"></i>  --}}
                Excel
            </button>

            <!-- View Selection Dropdown -->
            <div class="ml-auto">
                <select id="viewSelector" class="form-select ">
                    <option value="chart">Chart View</option>
                    <option value="list">List View</option>
                </select>
            </div>
        </div>
    </div>

    <!-- Chart View -->
    <div id="chart-view">
        <div class="chart-container">
            <div id="topProductsChart"></div>
        </div>
    </div>

    <!-- List View -->
    <div id="list-view" class="hidden">
        <div class="unit-card">
            <div class="table-responsive">
                <table class="table-custom" id="topProductTable">
                    <thead>
                        <tr>
                            <th>Rank</th>
                            <th>Product Name</th>
                            <th>Category</th>
                            <th>Unit Price</th>
                            <th>Qty Sold</th>
                            <th>Total Revenue</th>
                            <th>Stock Left</th>
                        </tr>
                    </thead>
                    <tbody id="unit-table-body">
                        <tr data-name="coca cola 330ml" data-category="beverages">
                            <td>1</td>
                            <td>Coca Cola 330ml</td>
                            <td>Beverages</td>
                            <td>$1.50</td>
                            <td>1,248</td>
                            <td>$1,872</td>
                            <td>In Stock</td>
                        </tr>
                         <tr data-name="coca cola 330ml" data-category="beverages">
                            <td>1</td>
                            <td>Coca Cola 330ml</td>
                            <td>Beverages</td>
                            <td>$1.50</td>
                            <td>1,248</td>
                            <td>$1,872</td>
                            <td>In Stock</td>
                        </tr>
                        <tr data-name="sting 330ml" data-category="beverages">
                            <td>2</td>
                            <td>Sting 330ml</td>
                            <td>Beverages</td>
                            <td>$1.50</td>
                            <td>1,248</td>
                            <td>$1,872</td>
                            <td>In Stock</td>
                        </tr>
                        <!-- Add more rows here -->
                    </tbody>
                </table>
            </div>

            {{-- pagination --}}
            <div class="pm-pagination">
                <div class="pm-pagination__meta">
                    <span class="pm-pagination__text" id="pagination-info"></span>
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

@push('scripts')
<script>
    (function() {
        if (window.topProductsInitialized) return;
        window.topProductsInitialized = true;

        let currentView = 'chart';

        function switchView(view) {
            currentView = view;

            document.getElementById('chart-view').classList.toggle('hidden', view !== 'chart');
            document.getElementById('list-view').classList.toggle('hidden', view !== 'list');

            if (view === 'chart') {
                setTimeout(() => {
                    if (typeof window.initTopProductChart === 'function') {
                        window.initTopProductChart();
                    }
                }, 150);
            }
        }

        function initializePage() {
            // Default view = Chart
            switchView('chart');

            // Listen to select change
            const viewSelector = document.getElementById('viewSelector');
            if (viewSelector) {
                viewSelector.addEventListener('change', function() {
                    switchView(this.value);
                });
            }
        }

        // Turbo Support
        document.addEventListener('turbo:load', initializePage);
        document.addEventListener('turbo:render', initializePage);

        // Initial load
        if (document.readyState === 'complete') {
            initializePage();
        } else {
            document.addEventListener('DOMContentLoaded', initializePage);
        }
    })();
</script>
    <script src="{{ asset('assets/js/dashboard/chart/top-product-chart.js') }}"></script>
@endpush
