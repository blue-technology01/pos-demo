@extends('layouts.pos')

@push('styles')
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined" />
    <link rel="stylesheet" href="{{ asset('assets/css/dashboard/sale/cashe-register.css') }}">
    <style>
        .pos-dashboard .material-symbols-outlined {
            font-size: 20px !important;
            width: 20px;
            height: 20px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            vertical-align: middle;
            line-height: 1;
        }
        .meta-label {
            display: inline-flex;
            align-items: center;
            gap: 6px;
        }
    </style>
@endpush

@section('content')
<div class="pos-dashboard">
    @include('components.navbar-pos')
    <main class="pos-dashboard__main">
        <section class="pos-catalog">

            <div class="pos-catalog__search-wrap" style="padding: 10px">
                <div class="pos-catalog__search">
                    <input type="text" id="product-search" class="pos-catalog__search-input"
                           placeholder="Search menu, products here…" autocomplete="off">
                    <button class="search-submit-btn">
                        <span class="material-symbols-outlined">search</span>
                    </button>
                </div>

                <button class="action-icon-btn action-icon-btn--scan" title="Scan Barcode">
                    <span class="material-symbols-outlined">document_scanner</span>
                </button>

                {{-- ── Cash Register Button ── --}}
                <button class="action-icon-btn action-icon-btn--register" id="open-register-btn"
                        title="Cash Register" onclick="openRegisterPopup()">
                    <span class="material-symbols-outlined">point_of_sale</span>
                    <span class="register-status-dot" id="register-dot"></span>
                </button>
            </div>

            {{-- Flash Messages --}}
            @if(session('success'))
                <div style="color: #155724; background-color: #d4edda; border-color: #c3e6cb; padding: 12px; margin: 10px; border-radius: 4px;">
                    {{ session('success') }}
                </div>
            @endif
            @if(session('error'))
                <div style="color: #721c24; background-color: #f8d7da; border-color: #f5c6cb; padding: 12px; margin: 10px; border-radius: 4px;">
                    {{ session('error') }}
                </div>
            @endif

            <div class="pos-catalog__filters-wrap" style="padding: 10px">
                <div class="flex justify-between items-center">
                    <h2>Choose Category</h2>
                    <a href="#" class="view-all-link">View All</a>
                </div>
                <div class="pos-catalog__filters" id="category-filters">
                    <button class="pos-catalog__filter-pill pos-catalog__filter-pill--active" data-category="all">All</button>
                    @for($i = 0; $i < 10; $i++)
                        <button class="pos-catalog__filter-pill" data-category="snack">Snack</button>
                    @endfor
                </div>
            </div>

            <div class="flex justify-between items-center" style="padding: 0px 10px">
                <h2>Special Menu</h2>
                <a href="#" class="view-all-link">View All</a>
            </div>
            <div class="pos-catalog__grid-container">
                <div class="pos-catalog__grid" id="product-grid"></div>
            </div>
        </section>

        <aside class="pos-sidebar">
            <div class="pos-sidebar__header">
                <span class="pos-sidebar__order-badge">Order</span>
                <span id="cart-item-count" class="pos-sidebar__item-count">0 items</span>
            </div>

            <div class="pos-sidebar__cart-body">
                <div class="pos-sidebar__cart-empty" id="cart-empty-state">
                    <p class="pos-sidebar__cart-empty-text">Cart is empty</p>
                </div>
                <ul class="pos-sidebar__cart-list" id="cart-list"></ul>
            </div>

            <div class="customer-meta-box">
                <div class="meta-row">
                    <span class="meta-label">
                        <span class="material-symbols-outlined">person</span> Customer
                    </span>
                    <span class="meta-value" id="selected-customer-name">Walk-in Customer</span>
                </div>
                <div class="meta-row">
                    <span class="meta-label">
                        <span class="material-symbols-outlined">badge</span> Customer ID
                    </span>
                    <span class="meta-value" id="selected-customer-id">#C-000000</span>
                </div>

                <button onclick="openCustomerModal()" class="change-customer-btn">
                    <span class="material-symbols-outlined">swap_horiz</span>
                    Change Customer
                </button>
            </div>

            <div class="pos-sidebar__receipt">
                <div class="pos-sidebar__receipt-row"><span>SubTotal</span><span id="receipt-subtotal">$0.00</span></div>
                <div class="pos-sidebar__receipt-row"><span>Tax (10%)</span><span id="receipt-tax">$0.00</span></div>
                <div class="pos-sidebar__receipt-divider"></div>
                <div class="pos-sidebar__receipt-row pos-sidebar__receipt-row--total">
                    <span>Total</span><span id="receipt-total">$0.00</span>
                </div>
            </div>

            <div class="pos-sidebar__cta">
                <button class="btn-checkout btn-checkout--print" type="button" title="Print Receipt Preview">
                    <span class="material-symbols-outlined">print</span>
                </button>
                <button class="pos-sidebar__process-btn" id="process-payment-btn" type="button">
                    Place Order
                </button>
            </div>
        </aside>
    </main>
</div>

{{-- check current sale  --}}
@php
    $currentShift = app(\App\Services\Cash\CashRegisterService::class)->getCurrentOpenRegister();
@endphp

{{-- cash register popup --}}
<div id="register-overlay" class="cr-overlay" style="display:none;">
    <div class="cr-popup">
        {{-- Header --}}
        <div class="cr-header">
            <div class="cr-header-left">
                <div class="cr-icon">
                    <span class="material-symbols-outlined">point_of_sale</span>
                </div>
                <div>
                    <h3 class="cr-title" id="cr-title">Cash Register</h3>
                    <p class="cr-subtitle" id="cr-subtitle">
                        @if($currentShift) វេនលក់កំពុងបើកដំណើរការ @else មិនទាន់មានវេនលក់នៅឡើយទេ @endif
                    </p>
                </div>
            </div>
            <div class="cr-header-right">
                <span class="cr-status-badge" id="cr-status-badge" style="background: {{ $currentShift ? '#d4edda' : '#f8d7da' }}; color: {{ $currentShift ? '#155724' : '#721c24' }}">
                    {{ $currentShift ? 'OPEN' : 'CLOSED' }}
                </span>
                <button class="cr-close-btn" onclick="closeRegisterPopup()">
                    <span class="material-symbols-outlined">close</span>
                </button>
            </div>
        </div>

        @if(!$currentShift)
            {{-- ── Open Register ── --}}
            <form action="{{ route('cashier.open') }}" method="POST" id="cr-open-state" class="cr-body">
                @csrf
                <div class="cr-info-row">
                    <span class="cr-info-label">Cashier</span>
                    <span class="cr-info-value">{{ auth()->user()->name ?? 'Cashier' }}</span>
                </div>
                <div class="cr-info-row">
                    <span class="cr-info-label">Date & time</span>
                    <span class="cr-info-value" id="cr-datetime">{{ now()->format('Y-m-d H:i A') }}</span>
                </div>

                <div class="cr-form-group">
                    <label class="cr-label">Opening balance ($) <span class="cr-required">*</span></label>
                    <input type="number" name="opening_balance" id="cr-opening-input" class="cr-input"
                           value="0.00" step="0.01" min="0" required>
                </div>

                <div class="cr-form-group">
                    <label class="cr-label">Note (optional)</label>
                    <textarea name="note" id="cr-open-note" class="cr-textarea" placeholder="Any remark..."></textarea>
                </div>

                <button type="submit" class="cr-btn cr-btn--open" id="cr-open-btn">
                    <span class="material-symbols-outlined">lock_open</span>
                    Open register
                </button>
            </form>
        @else
            {{-- ── Close Register ── --}}
            <form action="{{ route('cashier.close', $currentShift->id) }}" method="POST" id="cr-close-state" class="cr-body">
                @csrf
                {{-- Summary stats --}}
                <div class="cr-stat-grid">
                    <div class="cr-stat">
                        <div class="cr-stat-label">Total transactions</div>
                        <div class="cr-stat-value" id="cr-total-txn">{{ $currentShift->total_transactions ?? 0 }}</div>
                    </div>
                    <div class="cr-stat">
                        <div class="cr-stat-label">Total sales</div>
                        <div class="cr-stat-value" id="cr-total-sales">${{ number_format($currentShift->total_sales, 2) }}</div>
                    </div>
                    <div class="cr-stat">
                        <div class="cr-stat-label">Opening balance</div>
                        <div class="cr-stat-value" id="cr-show-opening">${{ number_format($currentShift->opening_balance, 2) }}</div>
                    </div>
                    <div class="cr-stat">
                        <div class="cr-stat-label">Expected balance</div>
                        <div class="cr-stat-value" id="cr-expected">${{ number_format(($currentShift->opening_balance + $currentShift->total_sales), 2) }}</div>
                    </div>
                </div>

                <div class="cr-info-row">
                    <span class="cr-info-label">Cashier</span>
                    <span class="cr-info-value">{{ auth()->user()->name ?? 'Cashier' }}</span>
                </div>
                <div class="cr-info-row">
                    <span class="cr-info-label">Opened at</span>
                    <span class="cr-info-value" id="cr-opened-at">{{ $currentShift->opened_at }}</span>
                </div>

                <div class="cr-form-group">
                    <label class="cr-label">Closing balance ($) <span class="cr-required">*</span></label>
                    <input
                        type="number"
                        name="closing_balance"
                        id="cr-closing-input"
                        class="cr-input"
                        placeholder="Count and enter cash in drawer"
                        step="0.01"
                        min="0"
                        required
                    >
                </div>

                {{-- Difference --}}
                <div class="cr-diff-box" id="cr-diff-box">
                    <span class="cr-diff-label">Difference</span>
                    <span class="cr-diff-value" id="cr-diff-value">$0.00</span>
                </div>

                <div class="cr-form-group">
                    <label class="cr-label">Note (optional)</label>
                    <textarea name="note" id="cr-close-note" class="cr-textarea"
                              placeholder="Reason for difference or any remark..."></textarea>
                </div>

                <button type="submit" class="cr-btn cr-btn--close" id="cr-close-btn" onclick="return confirm('តើអ្នកពិតជាចង់បិទវេនលក់នេះមែនទេ?')">
                    <span class="material-symbols-outlined">lock</span>
                    Close register
                </button>
            </form>
        @endif
    </div>
</div>

{{-- payment modal --}}
<div id="paymentModal" class="hidden">
    <div class="payment-modal-content">

        <div class="payment-receipt-side">
            <div class="receipt-header">
                <div class="shop-brand">ShopPoint POS</div>
                <h3 id="modal-customer-name">Walk-in Customer</h3>
                <p id="modal-invoice-no" class="font-mono">#INV-{{ date('Ymd') }}-0001</p>
            </div>

            <h4>Transaction Details</h4>
            <div id="modal-cart-items" class="receipt-items"></div>

            <div class="receipt-totals">
                <div class="flex justify-between"><span>Subtotal</span><span id="modal-subtotal">$0.00</span></div>
                <div class="flex justify-between"><span>Tax (10%)</span><span id="modal-tax">$0.00</span></div>
                <div class="flex justify-between"><span>Discount</span><span id="modal-discount" class="text-green-600">-$0.00</span></div>
                <div class="receipt-grand-total">
                    <span>Total</span>
                    <span id="modal-total">$0.00</span>
                </div>
            </div>

            <p class="receipt-footer">Thank you for shopping with us!<br>Please come again</p>
        </div>

        <div class="payment-side">
            <div class="flex justify-between items-center mb-6">
                <h2>Process Payment</h2>
                <button id="closePaymentModal" class="close-btn">×</button>
            </div>

            <p class="mb-3">Select Payment Method</p>
            <div class="payment-methods">
                <button class="payment-method-btn active" data-method="cash">
                    <span class="material-symbols-outlined">payments</span>
                    <span>Cash</span>
                </button>
                <button class="payment-method-btn" data-method="qr">
                    <span class="material-symbols-outlined">qr_code_2</span>
                    <span>QR / Mobile</span>
                </button>
            </div>

            <div class="amount-due-box">
                <p>Amount Due</p>
                <div id="modal-amount-due">$0.00</div>
            </div>

            <div class="mb-6">
                <label>Cash Received</label>
                <input type="text" id="cash-received" value="0.00">
            </div>

            <div class="change-box">
                <span class="flex items-center gap-1">Change</span>
                <span id="change-amount">$0.00</span>
            </div>

            <button id="confirmPaymentBtn" class="btn-confirm" type="button">
                Confirm & Complete Sale
            </button>

            <button id="cancelPaymentBtn" class="btn-cancel">
                Cancel Transaction
            </button>
        </div>
    </div>
</div>

{{-- Receipt --}}
<div id="receiptModal" class="hidden">
    <div class="receipt-modal-content">
        <div class="receipt-paper">
            <div class="receipt-header">
                <h2>ShopPoint POS</h2>
                <p id="receipt-date" class="text-sm text-gray-500"></p>
                <p id="receipt-invoice" class="font-mono text-sm"></p>
            </div>
            <hr>
            <div id="receipt-items" class="receipt-items"></div>
            <hr>
            <div class="receipt-totals">
                <div class="flex justify-between"><span>Subtotal</span><span id="r-subtotal"></span></div>
                <div class="flex justify-between"><span>Tax (10%)</span><span id="r-tax"></span></div>
                <div class="flex justify-between font-bold text-lg"><span>Total</span><span id="r-total"></span></div>
            </div>
            <hr>
            <div class="receipt-payment-info">
                <div class="flex justify-between"><span>Payment</span><span id="r-payment-method">Cash</span></div>
                <div class="flex justify-between"><span>Cash Received</span><span id="r-cash-received"></span></div>
                <div class="flex justify-between"><span>Change</span><span id="r-change"></span></div>
            </div>
            <div class="receipt-footer">
                Thank you for your purchase!<br>
                Please come again
            </div>
        </div>
    </div>
    <div id="receiptActions">
        <button id="downloadReceiptBtn" class="btn-primary">Download Receipt</button>
        <button id="closeReceiptBtn" class="btn-secondary">Close</button>
    </div>
</div>

{{-- Customer Modal --}}
<div id="create-customer-overlay" class="cr-overlay" style="display:none;">
    <div class="cr-popup" style="max-width:460px;">

        {{-- Header --}}
        <div class="cr-header">
            <div class="cr-header-left">
                <button class="cr-back-btn" onclick="closeCreateCustomerForm()">
                    <span class="material-symbols-outlined">arrow_back</span>
                    Back
                </button>
            </div>
            <div style="flex:1;text-align:center;">
                <h3 class="cr-title" style="margin:0;">New customer</h3>
            </div>
            <button class="cr-close-btn" onclick="closeCreateCustomerForm(); closeCustomerModal();">
                <span class="material-symbols-outlined">close</span>
            </button>
        </div>

        <div class="cr-body">

            {{-- Name + Phone --}}
            <div class="cr-form-grid">
                <div class="cr-form-group">
                    <label class="cr-label">Full name <span class="cr-required">*</span></label>
                    <input type="text" id="nc-name" class="cr-input" placeholder="e.g. Dara Sok">
                </div>
                <div class="cr-form-group">
                    <label class="cr-label">Phone number</label>
                    <input type="tel" id="nc-phone" class="cr-input" placeholder="e.g. 012 345 678">
                </div>
            </div>

            {{-- Gender + DOB --}}
            <div class="cr-form-grid" style="margin-top:12px;">
                <div class="cr-form-group">
                    <label class="cr-label">Gender</label>
                    <select id="nc-gender" class="cr-select">
                        <option value="">— Select —</option>
                        <option value="male">Male</option>
                        <option value="female">Female</option>
                        <option value="other">Other</option>
                    </select>
                </div>
                <div class="cr-form-group">
                    <label class="cr-label">Date of birth</label>
                    <input type="date" id="nc-dob" class="cr-input">
                </div>
            </div>

            {{-- Email --}}
            <div class="cr-form-group">
                <label class="cr-label">Email (optional)</label>
                <input type="email" id="nc-email" class="cr-input" placeholder="e.g. dara@example.com">
            </div>

            {{-- Address --}}
            <div class="cr-form-group">
                <label class="cr-label">Address (optional)</label>
                <textarea id="nc-address" class="cr-textarea" placeholder="Street, city..."></textarea>
            </div>

            {{-- Error --}}
            <div id="nc-error" class="cr-error-box" style="display:none;"></div>

            {{-- Submit --}}
            <button class="cr-btn cr-btn--open" id="nc-submit-btn" onclick="submitNewCustomer()">
                <span class="material-symbols-outlined">person_add</span>
                Create & select
            </button>

        </div>
    </div>
</div>


@endsection
@push('scripts')
    <script>
        window.PREVIEW_RECEIPT = {{ auth()->user()->preview_receipt ? 'true' : 'false' }};

        window.isShiftOpen = {{ $currentShift ? 'true' : 'false' }};
        window.expectedShiftBalance = parseFloat("{{ $currentShift ? ($currentShift->opening_balance + $currentShift->total_sales) : 0 }}");
    </script>

    <script src="{{ asset('assets/js/components/togglescreen.js') }}" defer></script>
    <script src="{{ asset('assets/js/dashboard/pos/pos.js') }}" defer></script>

    <script src="{{ asset('assets/js/dashboard/customer/customer.js') }}" defer></script>

    <script src="{{ asset('assets/js/dashboard/pos/cash-register-pos.js') }}" defer></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/html2pdf.js/0.10.1/html2pdf.bundle.min.js"></script>
@endpush
