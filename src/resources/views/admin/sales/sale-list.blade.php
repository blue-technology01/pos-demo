@extends('layouts.app')

@push('styles')
    <link rel="stylesheet" href="{{ asset('assets/css/dashboard/sale/sale.css') }}">
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined" />
    <style>
        /* Scoped sizing to keep your new inline SVGs perfect */
        .search-wrap svg,
        .unit-section__btn-add svg,
        .action-group svg {
            width: 16px;
            height: 16px;
            display: inline-block;
            vertical-align: middle;
            flex-shrink: 0;
        }

        .btn-filter svg,
        .btn-reset svg,
        .unit-section__btn-add svg {
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
            padding-left: 36px;
        }
    </style>
@endpush

@section('title', 'Daily Sales List')

@section('content')
    <div class="unit-section">
        <div class="unit-section__header">
            <div class="search-wrap">
                <svg class="search-icon-inline" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <circle cx="11" cy="11" r="8"></circle>
                    <line x1="21" y1="21" x2="16.65" y2="16.65"></line>
                </svg>

                <input type="text" id="searchInput" placeholder="Search invoices, customers...">
                <span class="search-label" style="margin-right: 8px; font-size: 14px; color: #4b5563;">
                    Start date
                </span>
                <input type="date" class="filter-date" id="startDate" title="Start Date">
                <span class="search-label" style="margin-right: 8px; font-size: 14px; color: #4b5563;">
                    End date
                </span>
                <input type="date" class="filter-date" id="endDate" title="End Date">

                <button class="btn-filter">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <polygon points="22 3 2 3 10 12.46 10 19 14 21 14 12.46 22 3"></polygon>
                    </svg>
                    Filter
                </button>

                <button class="btn-reset" id="reset-btn">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path d="M21.5 2v6h-6M21.34 15.57a10 10 0 1 1-.57-8.38l5.67-5.67"></path>
                    </svg>
                    Reset
                </button>
            </div>

            <button type="button" id="open-pm-modal" class="unit-section__btn-add">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                    <line x1="12" y1="5" x2="12" y2="19"></line>
                    <line x1="5" y1="12" x2="19" y2="12"></line>
                </svg>
                 Add New Sale
            </button>
        </div>

        <div class="unit-card">
            <div class="table-responsive">
                <table class="table-custom">
                    <thead>
                        <tr>
                            <th class="col-id">Invoice No</th>
                            <th>Date & Time</th>
                            <th>Cashier</th>
                            <th>Payment Method</th>
                            <th>Subtotal</th>
                            <th>Discount</th>
                            <th>Total Amount</th>
                            <th>Status</th>
                            <th class="col-actions">Action</th>
                        </tr>
                    </thead>
                    <tbody id="unit-table-body">
                        <tr data-unit-id="1001">
                            <td><span class="unit-id-text">#INV-1001</span></td>
                            <td><span class="sale-date-text">2026-05-25 09:30 AM</span></td>
                            <td><span class="unit-name-text">John Doe</span></td>
                            <td><span class="unit-badge">ABA Pay</span></td>
                            <td><span class="unit-badge">$34</span></td>
                            <td><span class="unit-badge">%0</span></td>
                            <td><strong>$24.50</strong></td>
                            <td data-status="paid"><span class="status-badge status-badge--active">Paid</span></td>
                            <td>
                                <div class="action-group">
                                   <button class="btn-action btn-action--edit" title="Edit Sale" data-action="edit">
                                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                            <path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"></path>
                                            <path d="M18.5 2.5a2.121 2.121 0 1 1 3 3L12 15l-4 1 1-4 9.5-9.5z"></path>
                                        </svg>
                                    </button>
                                    <button class="btn-action btn-action--delete" title="Delete/Void Sale" data-action="delete">
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
                            <tr data-unit-id="1001">
                            <td><span class="unit-id-text">#INV-1001</span></td>
                            <td><span class="sale-date-text">2026-05-25 09:30 AM</span></td>
                            <td><span class="unit-name-text">Sok Na</span></td>
                            <td><span class="unit-badge">QR</span></td>
                            <td><span class="unit-badge">$34</span></td>
                            <td><span class="unit-badge">%0</span></td>
                            <td><strong>$24.50</strong></td>
                            <td data-status="paid"><span class="status-badge status-badge--active">Paid</span></td>
                            <td>
                                <div class="action-group">
                                   <button class="btn-action btn-action--edit" title="Edit Sale" data-action="edit">
                                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                            <path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"></path>
                                            <path d="M18.5 2.5a2.121 2.121 0 1 1 3 3L12 15l-4 1 1-4 9.5-9.5z"></path>
                                        </svg>
                                    </button>
                                    <button class="btn-action btn-action--delete" title="Delete/Void Sale" data-action="delete">
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

            {{-- Pagination Matrix --}}
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

    {{-- Popup Form Unit / Add & Edit Sale Dialog --}}
    <div id="pm-dialog" title="Record New Sale" style="display:none;">
        <form id="pm-form" action="#" method="POST">
            @csrf
            <input type="hidden" id="pm_id" name="id">

            <div class="form-group">
                <label for="pm_customer">Customer Name</label>
                <input type="text" id="pm_customer" name="customer_name" placeholder="e.g., Walk-in Customer" required>
            </div>

            <div class="form-group">
                <label for="pm_payment_method">Payment Method</label>
                <select id="pm_payment_method" name="payment_method">
                    <option value="Cash">Cash</option>
                    <option value="ABA Pay">ABA Pay</option>
                    <option value="Wing">Wing</option>
                    <option value="Card">Credit/Debit Card</option>
                </select>
            </div>

            <div class="form-group">
                <label for="pm_total">Total Amount ($)</label>
                <input type="number" step="0.01" id="pm_total" name="total_amount" placeholder="0.00" required>
            </div>

            <div class="form-group">
                <label for="pm_status">Payment Status</label>
                <select id="pm_status" name="status">
                    <option value="paid">Paid</option>
                    <option value="pending">Pending</option>
                    <option value="refunded">Refunded</option>
                </select>
            </div>
        </form>
    </div>
@endsection

@push('scripts')
    <script src="{{ asset('assets/js/dashboard/sale/sale-list.js') }}"></script>
@endpush
