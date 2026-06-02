@extends('layouts.app')

@push('styles')
    <link rel="stylesheet" href="{{ asset('assets/css/dashboard/report/revent.css') }}" data-turbo-track="reload">
@endpush

@section('title', 'Daily Sales List')

@section('content')
    <div class="unit-section">
        <div class="unit-section__header">
            <div class="search-wrap">
                <input type="text" id="searchInput" placeholder="Search invoices, customers...">
                <input type="date" class="filter-date" id="dateFrom">   
                <input type="date" class="filter-date" id="dateTo">
                <button class="btn-filter" id="filter-btn">
                    <i class="ti ti-filter"></i> Filter
                </button>
                <button class="btn-reset" id="reset-btn">
                    <i class="ti ti-refresh"></i> Reset
                </button>
            </div>
        </div>

        <div class="unit-card">
            <div class="table-responsive">
                <table class="table-custom" id="salesTable">
                    <thead>
                        <tr>
                            <th>Date</th>
                            <th>Total Order</th>
                            <th>Total Revenue</th>
                            <th>Average Sale</th>
                        </tr>
                    </thead>
                    <tbody id="unit-table-body">
                        <!-- Data will be loaded by JS or Blade -->
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
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

    <!-- Add/Edit Sale Dialog -->
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
<script>
    (function() {
        let initialized = false;

        function initSalesPage() {
            if (initialized) return;   // ← Prevent running multiple times
            initialized = true;

            // Remove old listeners first
            $('#searchInput').off('keyup');
            $('#filter-btn').off('click');
            $('#reset-btn').off('click');
            $('#add-sale-btn').off('click');
            $('#per-page-select').off('change');

            // Your existing code here...
            $('#searchInput').on('keyup', function() {
                // Your search logic
            });

            $('#filter-btn').on('click', function() {
                console.log('Filter clicked');
                // Your filter logic
            });

            $('#reset-btn').on('click', function() {
                $('#searchInput').val('');
                $('.filter-date').val('');
                // Reset table logic
            });

            // Dialog (only initialize once)
            if (!$("#pm-dialog").hasClass('ui-dialog-content')) {
                $("#pm-dialog").dialog({
                    autoOpen: false,
                    width: 420,
                    modal: true,
                    buttons: {
                        "Save": function() {
                            $(this).dialog("close");
                        },
                        "Cancel": function() {
                            $(this).dialog("close");
                        }
                    }
                });
            }

            console.log('Sales Page Initialized Successfully');
        }

        // Run on Turbo navigation
        document.addEventListener('turbo:load', initSalesPage);

        // Also run on first load
        if (document.readyState === 'loading') {
            document.addEventListener('DOMContentLoaded', initSalesPage);
        } else {
            initSalesPage();
        }
    })();
</script>
@endpush
