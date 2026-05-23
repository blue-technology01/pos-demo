@extends('layouts.pos')

@section('title', 'Product Mart - POS')

@section('content')

<div class="pos-dashboard">
    {{-- Top Navbar matching your exact brand identity background color --}}
    @include('components.navbar-pos')
    <main class="pos-dashboard__main">
        <section class="pos-catalog">
            {{-- Header / Search Row --}}
            <div class="pos-catalog__search-wrap" style="display: flex; align-items: center; justify-content: space-between; width: 100%; gap: 24px;">
                <div class="pos-catalog__search" style="flex: 1; max-width: 500px; display: flex; align-items: center; background: var(--color-surface); border: 1px solid var(--color-border); border-radius: var(--radius-md); padding: 4px 6px 4px 16px; box-shadow: var(--shadow-sm);">
                    <input type="text" id="product-search" class="pos-catalog__search-input" placeholder="Search menu, products here…" autocomplete="off" style="flex: 1; border: none; background: transparent; padding: 8px 0; font-size: 14px; color: var(--color-text-primary);">
                    <button class="search-submit-btn" aria-label="Run Search" style="display: flex; align-items: center; justify-content: center; width: 36px; height: 36px; background-color: var(--color-brand); color: var(--color-text-inverse); border-radius: var(--radius-pill);">
                        <i data-lucide="search" style="width: 16px; height: 16px;"></i>
                    </button>
                </div>

                <div class="header-actions" style="display: flex; align-items: center; gap: 12px;">
                    <button class="action-icon-btn action-icon-btn--scan" title="Scan Barcode / QR" style="position: relative; display: flex; align-items: center; justify-content: center; width: 44px; height: 44px; border-radius: var(--radius-md); box-shadow: var(--shadow-sm); background-color: var(--color-success-bg); border: 1px solid rgba(16, 185, 129, 0.2); color: var(--color-success);">
                        <i data-lucide="scan-barcode" style="width: 20px; height: 20px;"></i>
                    </button>
                </div>
            </div>

            {{-- Categories Section ("Choose Category") --}}
            <div class="pos-catalog__filters-wrap" style="display: flex; flex-direction: column; gap: 12px; margin-top: 8px;">
                <div style="display: flex; justify-content: space-between; align-items: center;">
                    <h2 style="font-family: var(--font-display); font-size: 18px; font-weight: 700;">Choose Category</h2>
                    <a href="#" class="view-all-link">View All</a>
                </div>

                <div class="pos-catalog__filters">
                    <button class="pos-catalog__filter-pill pos-catalog__filter-pill--active" data-category="all">All</button>
                    <button class="pos-catalog__filter-pill" data-category="drink">Drink</button>
                    {{-- <button class="pos-catalog__filter-pill" data-category="snack">Snack</button> --}}
                    @for($i = 0; $i < 50; $i++)
                        <button class="pos-catalog__filter-pill" data-category="snack">Snack</button>
                    @endfor
                </div>
            </div>

            {{-- Product Grid Render Window --}}
            <div style="display: flex; justify-content: space-between; align-items: center; margin-top: 8px;">
                <h2 style="font-family: var(--font-display); font-size: 18px; font-weight: 700;">Special Menu</h2>
                <a href="#" class="view-all-link">View All</a>
            </div>
            <div class="pos-catalog__grid" id="product-grid"></div>
        </section>

        <aside class="pos-sidebar">

            {{-- Cart Header Counter --}}
            <div class="pos-sidebar__header" style="padding: 14px 24px 10px;">
                <span class="pos-sidebar__order-badge">Order</span>
                <span id="cart-item-count" class="pos-sidebar__item-count">0 items</span>
            </div>

            {{-- Cart Dynamic Area --}}
            <div class="pos-sidebar__cart-body">
                <div class="pos-sidebar__cart-empty" id="cart-empty-state">
                    <p class="pos-sidebar__cart-empty-text">Cart is empty</p>
                </div>
                <ul class="pos-sidebar__cart-list" id="cart-list"></ul>
            </div>

            {{-- Customer Metadata Fields --}}
            <div class="customer-meta-box" style="padding: 14px 24px; background-color: var(--color-surface-2); border-top: 1px solid var(--color-border); border-bottom: 1px solid var(--color-border); display: flex; flex-direction: column; gap: 8px;">
                <div class="meta-row" style="display: flex; justify-content: space-between; align-items: center; font-size: 13px;">
                    <span class="meta-label" style="color: var(--color-text-secondary); font-weight: 500; display: flex; align-items: center; gap: 6px;">
                        <i data-lucide="user" style="width: 14px; height: 14px;"></i> Recipient
                    </span>
                    <span class="meta-value" style="color: var(--color-text-primary); font-weight: 700;">Sarah Moanees</span>
                </div>
                <div class="meta-row" style="display: flex; justify-content: space-between; align-items: center; font-size: 13px;">
                    <span class="meta-label" style="color: var(--color-text-secondary); font-weight: 500; display: flex; align-items: center; gap: 6px;">
                        <i data-lucide="id-card" style="width: 14px; height: 14px;"></i> Customer ID
                    </span>
                    <span class="meta-value" style="color: var(--color-text-primary); font-weight: 700;">#C-904823</span>
                </div>
            </div>

            {{-- Accounting Breakdown Ledger Box --}}
            <div class="pos-sidebar__receipt">
                <div class="pos-sidebar__receipt-row"><span>SubTotal</span><span id="receipt-subtotal">$0.00</span></div>
                <div class="pos-sidebar__receipt-row"><span>Tax (10%)</span><span id="receipt-tax">$0.00</span></div>
                <div class="pos-sidebar__receipt-divider"></div>
                <div class="pos-sidebar__receipt-row pos-sidebar__receipt-row--total">
                    <span>Total</span><span id="receipt-total">$0.00</span>
                </div>
            </div>

            {{-- Payment Selector Blocks --}}
            <div class="pos-sidebar__payment">
                <p class="pos-sidebar__payment-label">Payment Method</p>
                <div class="pos-sidebar__payment-methods">
                    <button class="pos-sidebar__payment-btn pos-sidebar__payment-btn--active" data-payment="cash">Cash</button>
                    <button class="pos-sidebar__payment-btn" data-payment="aba">ABA</button>
                </div>
            </div>

            {{-- Action Group CTA Footer Drawer --}}
            <div class="pos-sidebar__cta" style="display: flex; gap: 12px; padding: 12px 18px 16px;">
                <button class="btn-checkout btn-checkout--print" type="button" style="display: flex; align-items: center; justify-content: center; gap: 8px; padding: 14px 20px; font-size: 14px; font-weight: 700; border-radius: var(--radius-md); box-shadow: var(--shadow-sm); background-color: var(--color-success); color: var(--color-text-inverse); width: 110px;">
                    <i data-lucide="printer" style="width: 16px; height: 16px;"></i> Print
                </button>
                <button class="pos-sidebar__process-btn" id="process-payment-btn" disabled style="flex: 1; margin-top: 0; padding: 14px; border-radius: var(--radius-md);">
                    Place Order
                </button>
            </div>
        </aside>
    </main>
</div>

<div id="payment-modal" class="payment-modal hidden">
    <div class="payment-modal__container">
        <button class="payment-modal__close" data-dismiss="modal" aria-label="Dismiss Modal">&times;</button>
        <div class="payment-modal__layout">

            {{-- Left Split Side: Transaction Details --}}
            <div class="receipt-summary">
                <h3 class="receipt-summary__title">Transaction Details</h3>
                <div class="receipt-summary__items" id="modal-receipt-items"></div>
                <div class="receipt-summary__total">
                    <span>Grand Total:</span><span id="modal-grand-total">$0.00</span>
                </div>
            </div>

            {{-- Right Split Side: Dynamic Virtual Cash Register Keypad --}}
            <div class="keypad">
                <div>
                    <label class="keypad__label">Amount Paid</label>
                    <div class="keypad__display" id="amount-display">$0.00</div>
                </div>

                <div class="quick-cash">
                    <button class="quick-cash__btn" data-amount="5">$5</button>
                    <button class="quick-cash__btn" data-amount="10">$10</button>
                    <button class="quick-cash__btn" data-amount="20">$20</button>
                    <button class="quick-cash__btn" data-amount="50">$50</button>
                    <button class="quick-cash__btn" data-amount="100">$100</button>
                </div>

                <div class="number-pad">
                    <button class="number-pad__key" data-key="1">1</button>
                    <button class="number-pad__key" data-key="2">2</button>
                    <button class="number-pad__key" data-key="3">3</button>
                    <button class="number-pad__key" data-key="4">4</button>
                    <button class="number-pad__key" data-key="5">5</button>
                    <button class="number-pad__key" data-key="6">6</button>
                    <button class="number-pad__key" data-key="7">7</button>
                    <button class="number-pad__key" data-key="8">8</button>
                    <button class="number-pad__key" data-key="9">9</button>
                    <button class="number-pad__key" data-key=".">.</button>
                    <button class="number-pad__key" data-key="0">0</button>
                    <button class="number-pad__key" data-action="backspace" aria-label="Backspace">
                        {{-- Optimized Backspace SVG Icon Match --}}
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" style="width: 18px; height: 18px; display: inline-block; vertical-align: middle;">
                            <path d="M21 4H8l-7 8 7 8h13a2 2 0 0 0 2-2V6a2 2 0 0 0-2-2z" />
                            <line x1="18" y1="9" x2="12" y2="15" />
                            <line x1="12" y1="9" x2="18" y2="15" />
                        </svg>
                    </button>
                </div>

                <button class="btn-pay" id="pay-now-btn">Pay Now</button>
            </div>
        </div>
    </div>
</div>
@endsection
