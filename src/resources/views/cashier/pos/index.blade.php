@extends('layouts.pos')

@push('styles')
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined" />
    <link rel="stylesheet" href="{{ asset('assets/css/dashboard/sale/cashe-register.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/css/dashboard/customer/customer-pos.css') }}">
    <style>
        /* ── Icon sizing fix ─────────────────────────────────── */
        .pos-dashboard .material-symbols-outlined {
            font-size: 20px !important;
            width: 20px; height: 20px;
            display: inline-flex; align-items: center; justify-content: center;
            vertical-align: middle; line-height: 1;
        }

        /* ── Product Grid ────────────────────────────────────── */
        .pos-catalog__grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(150px, 1fr));
            gap: 10px;
            padding: 10px;
        }

        /* ── Product Card ────────────────────────────────────── */
        .product-card {
            position: relative;
            background: #fff;
            border: 1.5px solid #e2e8f0;
            border-radius: 10px;
            overflow: hidden;
            cursor: pointer;
            transition: box-shadow 0.18s, border-color 0.18s, transform 0.12s;
            display: flex;
            flex-direction: column;
            user-select: none;
        }
        .product-card:hover:not(.product-card--disabled) {
            box-shadow: 0 4px 16px rgba(59,130,246,0.13);
            border-color: #93c5fd;
            transform: translateY(-2px);
        }
        .product-card:active:not(.product-card--disabled) {
            transform: scale(0.97);
        }
        .product-card--disabled {
            opacity: 0.5;
            cursor: not-allowed;
            filter: grayscale(0.4);
        }

        /* ── Card Image ──────────────────────────────────────── */
        .product-card__image {
            width: 100%;
            aspect-ratio: 1 / 1;
            background: #f8fafc;
            overflow: hidden;
            flex-shrink: 0;
        }
        .product-card__image img {
            width: 100%; height: 100%;
            object-fit: cover;
            display: block;
        }

        /* ── Card Body ───────────────────────────────────────── */
        .product-card__body {
            padding: 8px;
            display: flex;
            flex-direction: column;
            gap: 4px;
            flex: 1;
        }
        .product-card__name {
            font-size: 13px;
            font-weight: 600;
            color: #1e293b;
            line-height: 1.3;
            display: -webkit-box;
            -webkit-line-clamp: 2;
            -webkit-box-orient: vertical;
            overflow: hidden;
        }
        .product-card__price {
            font-size: 14px;
            font-weight: 700;
            color: #2563eb;
        }

        /* ── UOM Row ─────────────────────────────────────────── */
        .product-card__uom-row {
            display: flex;
            align-items: center;
            gap: 4px;
            margin-top: 2px;
        }
        .product-card__uom-select {
            flex: 1;
            font-size: 11px;
            padding: 2px 4px;
            border: 1px solid #cbd5e1;
            border-radius: 5px;
            background: #f8fafc;
            color: #475569;
            cursor: pointer;
            appearance: auto;
            min-width: 0;
        }
        .product-card__uom-select:focus {
            outline: none;
            border-color: #93c5fd;
        }

        /* ── Add Button ──────────────────────────────────────── */
        .product-card__add-btn {
            width: 26px; height: 26px;
            flex-shrink: 0;
            border: none;
            border-radius: 6px;
            background: #2563eb;
            color: #fff;
            display: flex; align-items: center; justify-content: center;
            cursor: pointer;
            font-size: 18px;
            font-weight: 700;
            line-height: 1;
            transition: background 0.15s;
        }
        .product-card__add-btn:hover { background: #1d4ed8; }
        .product-card__add-btn .material-symbols-outlined { font-size: 16px !important; }

        /* ── Stock Badge ─────────────────────────────────────── */
        .product-card__stock {
            font-size: 10px;
            font-weight: 500;
            padding: 1px 6px;
            border-radius: 99px;
            display: inline-block;
            width: fit-content;
        }
        .stock-good { background: #dcfce7; color: #166534; }
        .stock-low  { background: #fef9c3; color: #854d0e; }
        .stock-out  { background: #fee2e2; color: #991b1b; }

        /* ── Out-of-stock overlay ────────────────────────────── */
        .product-card--disabled .product-card__add-btn {
            background: #94a3b8;
            cursor: not-allowed;
        }

        /* ── Skeleton shimmer ────────────────────────────────── */
        @keyframes shimmer {
            0%   { background-position: -400px 0; }
            100% { background-position: 400px 0; }
        }
        .skeleton {
            background: linear-gradient(90deg, #f1f5f9 25%, #e2e8f0 50%, #f1f5f9 75%);
            background-size: 400px 100%;
            animation: shimmer 1.4s ease-in-out infinite;
            border-radius: 10px;
            aspect-ratio: 1 / 1.3;
        }

        /* ── Pagination ──────────────────────────────────────── */
        #product-pagination {
            padding: 8px 10px 12px;
        }
        .pg-btn {
            padding: 5px 14px;
            border: 1.5px solid #cbd5e1;
            border-radius: 7px;
            background: #fff;
            font-size: 13px;
            cursor: pointer;
            color: #334155;
            transition: background 0.14s, border-color 0.14s;
        }
        .pg-btn:hover:not(:disabled) { background: #eff6ff; border-color: #93c5fd; }
        .pg-btn:disabled { opacity: 0.4; cursor: not-allowed; }

        /* ── Low-stock badge on card ─────────────────────────── */
        .uom-badge {
            position: absolute;
            top: 6px; right: 6px;
            background: rgba(17,24,39,0.78);
            color: #fff;
            font-size: 9px;
            font-weight: 700;
            padding: 2px 7px;
            border-radius: 99px;
            text-transform: uppercase;
            letter-spacing: 0.05em;
            pointer-events: none;
        }
        .low-stock-badge {
            position: absolute;
            top: 6px; left: 6px;
            background: #fef08a;
            color: #713f12;
            font-size: 9px;
            font-weight: 700;
            padding: 2px 6px;
            border-radius: 99px;
            pointer-events: none;
        }
    </style>
@endpush

@section('content')
<div class="pos-dashboard">
    @include('components.navbar-pos')
    <main class="pos-dashboard__main">

        {{-- ══════════════════ CATALOG SECTION ══════════════════ --}}
        <section class="pos-catalog">

            {{-- Search + Action Buttons --}}
            <div class="pos-catalog__search-wrap" style="padding:10px;">
                <div class="pos-catalog__search">
                    <input type="text" id="product-search" class="pos-catalog__search-input"
                           placeholder="ស្វែងរកឈ្មោះ ឬស្កេនបាកូដ..." autocomplete="off">
                    <button class="search-submit-btn">
                        <span class="material-symbols-outlined">search</span>
                    </button>
                </div>

                <button class="action-icon-btn action-icon-btn--scan" title="ស្កេនបាកូដ">
                    <span class="material-symbols-outlined">document_scanner</span>
                </button>

                <button class="action-icon-btn action-icon-btn--register" id="open-register-btn"
                        title="គ្រប់គ្រងវេនលុយ" onclick="openRegisterPopup()">
                    <span class="material-symbols-outlined">point_of_sale</span>
                    <span class="register-status-dot" id="register-dot"></span>
                </button>

                <button class="action-icon-btn action-icon-btn--customer" id="open-customer-btn"
                        title="ជ្រើសរើសអតិថិជន" onclick="openCustomerFilterPopup()">
                    <span class="material-symbols-outlined">person_add</span>
                </button>
            </div>

            {{-- Flash Messages --}}
            @if(session('success'))
                <div style="color:#155724;background:#d4edda;padding:10px 12px;margin:0 10px 6px;border-radius:6px;font-size:13px;">
                    {{ session('success') }}
                </div>
            @endif
            @if(session('error'))
                <div style="color:#721c24;background:#f8d7da;padding:10px 12px;margin:0 10px 6px;border-radius:6px;font-size:13px;">
                    {{ session('error') }}
                </div>
            @endif

            {{-- Category Filter Pills --}}
            <div class="pos-catalog__filters-wrap" style="padding:6px 10px 4px;">
                <div class="pos-catalog__filters" id="category-filters">
                    <button class="pos-catalog__filter-pill pos-catalog__filter-pill--active" data-category="all">ទាំងអស់</button>
                    @foreach($categories ?? [] as $category)
                        <button class="pos-catalog__filter-pill" data-category="{{ $category->code }}">
                            {{ $category->name }}
                        </button>
                    @endforeach
                </div>
            </div>

            {{-- Product Grid --}}
            <div class="pos-catalog__grid-container">
                <div class="pos-catalog__grid" id="product-grid">
                    {{-- Filled by pos.js via API --}}
                </div>
            </div>

            {{-- Pagination --}}
            <div id="product-pagination"></div>

        </section>

        {{-- ══════════════════ CART SIDEBAR ══════════════════ --}}
        <aside class="pos-sidebar">
            <div class="pos-sidebar__header">
                <span class="pos-sidebar__order-badge">ការបញ្ជាទិញ</span>
                <span id="cart-item-count" class="pos-sidebar__item-count">0 មុខ</span>
            </div>

            <div class="pos-sidebar__cart-body">
                <div class="pos-sidebar__cart-empty" id="cart-empty-state">
                    <p class="pos-sidebar__cart-empty-text">មិនទាន់មានទំនិញក្នុងកន្ត្រកឡើយ</p>
                </div>
                <ul class="pos-sidebar__cart-list" id="cart-list"
                    style="display:flex;flex-direction:column;gap:10px;padding:0;margin:0;list-style:none;">
                </ul>
            </div>

            <div class="pos-sidebar__receipt">
                <div class="pos-sidebar__receipt-row"><span>Subtotal</span><span id="receipt-subtotal">$0.00</span></div>
                <div class="pos-sidebar__receipt-row"><span>Discount</span><span id="receipt-discount">$0.00</span></div>
                <div class="pos-sidebar__receipt-row"><span>Tax (10%)</span><span id="receipt-tax">$0.00</span></div>
                <div class="pos-sidebar__receipt-divider"></div>
                <div class="pos-sidebar__receipt-row pos-sidebar__receipt-row--total">
                    <span>Total</span><span id="receipt-total">$0.00</span>
                </div>
            </div>

            <div class="pos-sidebar__cta">
                <button class="btn-checkout btn-checkout--print" type="button" title="Print preview">
                    <span class="material-symbols-outlined">print</span>
                </button>
                <button class="pos-sidebar__process-btn" id="process-payment-btn" type="button" disabled
                        style="opacity:0.5;cursor:not-allowed;">
                    ទូទាត់ប្រាក់ (Place Order)
                </button>
            </div>
        </aside>

    </main>
</div>

{{-- ══ Cash Register state ══ --}}
@php
    $currentShift = app(\App\Services\Cash\CashRegisterService::class)->getCurrentOpenRegister();
@endphp

{{-- Cash Register Modal --}}
<div id="register-overlay" class="cr-overlay" style="display:none;">
    <div class="cr-popup">
        <div class="cr-header">
            <div class="cr-header-left">
                <div class="cr-icon"><span class="material-symbols-outlined">point_of_sale</span></div>
                <div>
                    <h3 class="cr-title">បញ្ជរតុលុយ (Cash Register)</h3>
                    <p class="cr-subtitle" id="cr-subtitle">
                        @if($currentShift) វេនលក់កំពុងបើកដំណើរការ @else មិនទាន់មានវេនលក់នៅឡើយទេ @endif
                    </p>
                </div>
            </div>
            <div class="cr-header-right">
                <span class="cr-status-badge" style="background:{{ $currentShift ? '#d4edda' : '#f8d7da' }};color:{{ $currentShift ? '#155724' : '#721c24' }}">
                    {{ $currentShift ? 'OPEN' : 'CLOSED' }}
                </span>
                <button class="cr-close-btn" onclick="closeRegisterPopup()">
                    <span class="material-symbols-outlined">close</span>
                </button>
            </div>
        </div>

        @if(!$currentShift)
            <form action="{{ route('cashier.open') }}" method="POST" class="cr-body">
                @csrf
                <div class="cr-info-row">
                    <span class="cr-info-label">Cashier</span>
                    <span class="cr-info-value">{{ auth()->user()->name ?? 'Cashier' }}</span>
                </div>
                <div class="cr-form-group">
                    <label class="cr-label">Opening Balance ($) <span class="cr-required">*</span></label>
                    <input type="number" name="opening_balance" class="cr-input" value="0.00" step="0.01" min="0" required>
                </div>
                <div class="cr-form-group">
                    <label class="cr-label">Note</label>
                    <textarea name="note" class="cr-textarea" placeholder="Optional note..."></textarea>
                </div>
                <button type="submit" class="cr-btn cr-btn--open">
                    <span class="material-symbols-outlined">lock_open</span> Open Shift
                </button>
            </form>
        @else
            <form action="{{ route('cashier.close', $currentShift->id) }}" method="POST" class="cr-body">
                @csrf
                <div class="cr-stat-grid">
                    <div class="cr-stat"><div class="cr-stat-label">Transactions</div><div class="cr-stat-value" id="cr-total-txn">{{ $currentShift->total_transactions ?? 0 }}</div></div>
                    <div class="cr-stat"><div class="cr-stat-label">Total Sales</div><div class="cr-stat-value">${{ number_format($currentShift->total_sales, 2) }}</div></div>
                    <div class="cr-stat"><div class="cr-stat-label">Opening Balance</div><div class="cr-stat-value">${{ number_format($currentShift->opening_balance, 2) }}</div></div>
                    <div class="cr-stat"><div class="cr-stat-label">Expected in Drawer</div><div class="cr-stat-value">${{ number_format($currentShift->opening_balance + $currentShift->total_sales, 2) }}</div></div>
                </div>
                <div class="cr-form-group">
                    <label class="cr-label">Actual Cash in Drawer ($) <span class="cr-required">*</span></label>
                    <input type="number" name="closing_balance" id="cr-closing-input" class="cr-input" step="0.01" min="0" required>
                </div>
                <div class="cr-diff-box" id="cr-diff-box">
                    <span class="cr-diff-label">Difference</span>
                    <span class="cr-diff-value" id="cr-diff-value">$0.00</span>
                </div>
                <div class="cr-form-group">
                    <label class="cr-label">Note</label>
                    <textarea name="note" class="cr-textarea" placeholder="Explain any difference..."></textarea>
                </div>
                <button type="submit" class="cr-btn cr-btn--close"
                        onclick="return confirm('Close this shift?')">
                    <span class="material-symbols-outlined">lock</span> Close Shift
                </button>
            </form>
        @endif
    </div>
</div>

{{-- Payment Modal --}}
<div id="paymentModal" class="hidden">
    <div class="payment-modal-content">
        <div class="payment-receipt-side">
            <div class="receipt-header">
                <div class="shop-brand">ShopPoint POS</div>
                <h3 id="modal-customer-name">Walk-in Customer</h3>
                <p id="modal-invoice-no" class="font-mono">#INV-{{ date('Ymd') }}-0001</p>
            </div>
            <h4>Items</h4>
            <div id="modal-cart-items" class="receipt-items"></div>
            <div class="receipt-totals">
                <div class="flex justify-between"><span>Subtotal</span><span id="modal-subtotal">$0.00</span></div>
                <div class="flex justify-between"><span>Tax (10%)</span><span id="modal-tax">$0.00</span></div>
                <div class="flex justify-between"><span>Discount</span><span id="modal-discount" class="text-green-600">-$0.00</span></div>
                <div class="receipt-grand-total"><span>Total Due</span><span id="modal-total">$0.00</span></div>
            </div>
            <p class="receipt-footer">Thank you for your purchase!</p>
        </div>

        <div class="payment-side">
            <div class="flex justify-between items-center mb-6">
                <h2>Payment</h2>
                <button id="closePaymentModal" class="close-btn">×</button>
            </div>
            <p class="mb-3">Select payment method</p>
            <div class="payment-methods">
                <button class="payment-method-btn active" data-method="cash">
                    <span class="material-symbols-outlined">payments</span><span>Cash</span>
                </button>
                <button class="payment-method-btn" data-method="qr">
                    <span class="material-symbols-outlined">qr_code_2</span><span>QR / Bank</span>
                </button>
            </div>
            <div class="amount-due-box">
                <p style="color:#fff;">Amount Due</p>
                <div id="modal-amount-due">$0.00</div>
            </div>
            <div class="mb-6">
                <label>Cash Received ($)</label>
                <input type="text" id="cash-received" value="0.00">
            </div>
            <div class="change-box">
                <span>Change</span>
                <span id="change-amount">$0.00</span>
            </div>
            <button id="confirmPaymentBtn" class="btn-confirm" type="button">Confirm & Complete Sale</button>
            <button id="cancelPaymentBtn" class="btn-cancel">Cancel</button>
        </div>
    </div>
</div>

{{-- Receipt Modal --}}
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
                <div class="flex justify-between"><span>Received</span><span id="r-cash-received"></span></div>
                <div class="flex justify-between"><span>Change</span><span id="r-change"></span></div>
            </div>
            <div class="receipt-footer">Thank you! Please come again.</div>
        </div>
    </div>
    <div id="receiptActions">
        <button id="downloadReceiptBtn" class="btn-primary">Download PDF</button>
        <button id="closeReceiptBtn" class="btn-secondary">Close</button>
    </div>
</div>

{{-- Customer Search Modal --}}
<div id="customerFilterModal" class="pos-modal">
    <div class="pos-modal__content">
        <div class="pos-modal__header">
            <h3 class="pos-modal__title">
                <span class="material-symbols-outlined">person_search</span> Select Customer
            </h3>
            <button class="pos-modal__close-btn" onclick="closeFilterModal()">&times;</button>
        </div>
        <div class="pos-modal__body">
            <div class="pos-form-group">
                <div class="pos-input-with-icon">
                    <span class="material-symbols-outlined pos-input-icon">search</span>
                    <input type="text" id="popupSearchInput" class="pos-form-control"
                           placeholder="Search by name or phone..." autocomplete="off"
                           onkeyup="filterCustomersRealTime()">
                </div>
            </div>
            <div id="popupCustomerResult" class="pos-customer-list"></div>
        </div>
        <div class="pos-modal__footer">
            <button type="button" class="pos-btn pos-btn--secondary" onclick="closeFilterModal()">Close</button>
        </div>
    </div>
</div>

{{-- Create Customer Modal --}}
<div id="create-customer-overlay" class="cr-overlay" style="display:none;">
    <div class="cr-popup" style="max-width:460px;">
        <div class="cr-header">
            <div class="cr-header-left">
                <button class="cr-back-btn" onclick="closeCreateCustomerForm()">
                    <span class="material-symbols-outlined">arrow_back</span> Back
                </button>
            </div>
            <div style="flex:1;text-align:center;"><h3 class="cr-title" style="margin:0;">New Customer</h3></div>
            <button class="cr-close-btn" onclick="closeCreateCustomerForm(); closeCustomerModal();">
                <span class="material-symbols-outlined">close</span>
            </button>
        </div>
        <div class="cr-body">
            <div class="cr-form-grid">
                <div class="cr-form-group">
                    <label class="cr-label">Name <span class="cr-required">*</span></label>
                    <input type="text" id="nc-name" class="cr-input" placeholder="Customer name">
                </div>
                <div class="cr-form-group">
                    <label class="cr-label">Phone</label>
                    <input type="tel" id="nc-phone" class="cr-input" placeholder="012 345 678">
                </div>
            </div>
            <div id="nc-error" class="cr-error-box" style="display:none;"></div>
            <button class="cr-btn cr-btn--open" id="nc-submit-btn" onclick="submitNewCustomer()">
                <span class="material-symbols-outlined">person_add</span> Save & Select
            </button>
        </div>
    </div>
</div>

@endsection

@push('scripts')
<script>
    window.PREVIEW_RECEIPT   = {{ auth()->user()->preview_receipt ? 'true' : 'false' }};
    window.isShiftOpen       = {{ $currentShift ? 'true' : 'false' }};
    window.expectedShiftBalance = parseFloat("{{ $currentShift ? ($currentShift->opening_balance + $currentShift->total_sales) : 0 }}");
    window.CUSTOMER_SEARCH_URL  = "{!! route('admin.customers.search.ajax') !!}";
    window.CUSTOMER_STORE_URL   = "{!! route('admin.customers.store') !!}";

    window.ROUTES = {
        posProducts: "{!! route('cashier.pos.products') !!}",   {{-- ← NEW: API endpoint --}}
        addItem:     "{!! route('cashier.sale-items.store') !!}",
        updateItem:  "{!! route('cashier.sale-items.update', ':rowId') !!}",
        removeItem:  "{!! route('cashier.sale-items.destroy', ':rowId') !!}",
        clearCart:   "{!! route('cashier.sale-items.clear') !!}",
        confirmSale: "{!! route('cashier.sale-items.confirm') !!}",
    };
    window.POS_ASSETS = {
        storageBase: "{{ asset('storage') }}",
        placeholder: "{{ asset('assets/images/not-product.png') }}",
    };
    window.CSRF_TOKEN = "{{ csrf_token() }}";

    window.currentRegisterId = {{
        \App\Models\CashRegister::where('user_id', auth()->id())
            ->where('status', 'open')
            ->value('id') ?? 'null'
    }};
    window.selectedCustomerId = null;
</script>
<script src="{{ asset('assets/js/components/togglescreen.js') }}" defer></script>
<script src="{{ asset('assets/js/dashboard/pos/pos.js') }}" defer></script>
<script src="{{ asset('assets/js/dashboard/customer/customer-pos.js') }}" defer></script>
<script src="{{ asset('assets/js/dashboard/pos/cash-register-pos.js') }}" defer></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/html2pdf.js/0.10.1/html2pdf.bundle.min.js"></script>
@endpush
