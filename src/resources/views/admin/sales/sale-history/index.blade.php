@extends('layouts.app')

@push('styles')
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet" />
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined" />
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@tabler/icons-webfont@latest/dist/tabler-icons.min.css" />

    <link rel="stylesheet" href="{{ asset('assets/css/dashboard/sale/history.css') }}">
@endpush

@section('content')
<x-spinner />
<div class="unit-section">
    {{-- ── Header ── --}}
    <div class="unit-section__header">
        <h2 class="page-title">Sales history</h2>
        {{-- Search + Filter --}}
        <form action="{{ route('admin.sales.index') }}" method="GET" class="search-wrap">

            <div class="search-box">
                <span class="material-symbols-outlined">search</span>
                <input
                    type="text"
                    name="search"
                    value="{{ request('search') }}"
                    class="filter-input"
                    placeholder="Search cashier..."
                    autocomplete="off"
                >
            </div>

            <input type="datetime-local" name="start_date" value="{{ request('start_date') }}" class="filter-input">
            <input type="datetime-local" name="end_date"   value="{{ request('end_date') }}"   class="filter-input">

            <button type="submit" class="btn-filter" onclick="showLoader()">
                <span class="material-symbols-outlined">filter_alt</span> Filter
            </button>

            <a href="{{ route('admin.sales.index') }}" class="btn-reset" onclick="showLoader()">
                <span class="material-symbols-outlined">refresh</span>
                Reset
            </a>

        </form>

        {{-- Export --}}
        <div class="export-actions">
            <button class="btn-export btn-excel">
                <span class="material-symbols-outlined">table_view</span> Excel
            </button>
            <button class="btn-export btn-pdf">
                <span class="material-symbols-outlined">picture_as_pdf</span> PDF
            </button>
        </div>
    </div>

    {{-- ── Table ── --}}
    <div class="unit-card " >
        <div class="table-responsive">
            <table class="table-custom" id="salesTable">
                <thead>
                    <tr>
                        <th>Invoice no.</th>
                        <th>Date & time</th>
                        <th>Cashier</th>
                        <th>Payment</th>
                        <th>Total</th>
                        <th>Status</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($sales as $sale)
                        <tr>
                            <td><strong>{{ $sale->invoice_no }}</strong></td>
                            <td>{{ $sale->created_at->format('d M Y H:i') }}</td>
                            <td>{{ $sale->user->name ?? 'N/A' }}</td>
                            <td>
                                <span class="payment-method">
                                    {{ strtoupper($sale->payment_method) }}
                                </span>
                            </td>
                            <td>
                                <strong class="amount">${{ number_format($sale->total_amount, 2) }}</strong>
                            </td>
                            <td>
                                <span class="status-badge status-badge--{{ $sale->status }}">
                                    {{ ucfirst($sale->status) }}
                                </span>
                            </td>
                            <td>
                                <div class="action-flex">
                                    {{-- View --}}
                                    <button
                                        class="btn-action btn-view"
                                        data-sale='@json($sale)'
                                        title="View invoice"
                                        type="button"
                                    >
                                        <span class="material-symbols-outlined">visibility</span>
                                    </button>

                                    {{-- Void / Cancel --}}
                                    @if($sale->status !== 'voided')
                                        <form
                                            action="{{ route('admin.sales.cancel', $sale->id) }}"
                                            method="POST"
                                            onsubmit="return confirm('Are you sure you want to void this invoice?')"
                                        >
                                            @csrf
                                            <button type="submit" class="btn-cancel" title="Void sale">
                                                <span class="material-symbols-outlined">block</span>
                                            </button>
                                        </form>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" style="text-align:center;padding:2.5rem 1rem;color:#9ca3af;font-size:13px">
                                <span class="material-symbols-outlined" style="font-size:28px;display:block;margin:0 auto 8px;color:#d1d5db">receipt_long</span>
                                No sales records found.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{-- pagination --}}
        <div class="table-footer" style="width: 100%; display: flex; justify-content: space-between" id="tableFooter">
            <span class="table-footer-left">
                <div class="table-info">
                    showing
                    {{ $sales->firstItem() ?? 0 }}
                    -
                    {{ $sales->lastItem() ?? 0 }}
                    of
                    {{ $sales->total() }}
                    sales
                </div>

                {{-- per page --}}
                <form method="GET" action="{{ request()->url() }}">
                    @foreach(request()->except('per_page', 'page') as $key => $value)
                        <input type="hidden" name="{{ $key }}" value="{{ $value }}">
                    @endforeach

                    <select name="per_page" onchange="showLoader(); this.form.submit()">
                        <option value="15" {{ request('per_page', 15) == 15 ? 'selected' : '' }}>15</option>
                        <option value="25" {{ request('per_page') == 25 ? 'selected' : '' }}>25</option>
                        <option value="50" {{ request('per_page') == 50 ? 'selected' : '' }}>50</option>
                    </select>
                </form>
            </span>

            <div class="pagination">
                {{ $sales->links('vendor.pagination.numbers-only') }}
            </div>
        </div>
    </div>
</div>

{{-- ══ Invoice Modal ══ --}}
<div class="inv-overlay" id="invOverlay">
    <div class="inv-modal" id="invModal">

        <button class="inv-close-btn" id="invCloseBtn" type="button" aria-label="Close">×</button>

        {{-- Invoice header --}}
        <div class="inv-header">
            <div>
                <div class="company-name">YOUR STORE</div>
                <div class="company-sub">Point of Sale System</div>
            </div>
            <div class="inv-title-block">
                <div class="inv-word">INVOICE</div>
                <div class="inv-no" id="invNo"></div>
            </div>
        </div>

        {{-- Meta row --}}
        <div class="inv-meta">
            <div class="inv-meta-cell">
                <div class="lbl">Date</div>
                <div class="val" id="invDate"></div>
            </div>
            <div class="inv-meta-cell">
                <div class="lbl">Cashier</div>
                <div class="val" id="invCashier"></div>
            </div>
            <div class="inv-meta-cell">
                <div class="lbl">Payment</div>
                <div class="val" id="invPayment"></div>
            </div>
            <div class="inv-meta-cell">
                <div class="lbl">Status</div>
                <div class="val" id="invStatus"></div>
            </div>
        </div>

        {{-- Items table --}}
        <div class="inv-body">
            <table class="inv-items">
                <thead>
                    <tr>
                        <th>Item</th>
                        <th>Qty</th>
                        <th>Unit price</th>
                        <th>Discount</th>
                        <th>Amount</th>
                    </tr>
                </thead>
                <tbody id="invItems"></tbody>
            </table>
        </div>

        {{-- Footer totals --}}
        <div class="inv-foot">
            <div class="inv-note">Thank you for your purchase!</div>
            <div class="inv-totals">
                <div class="t-row">
                    <span>Subtotal</span>
                    <span id="invSubtotal"></span>
                </div>
                <div class="t-row">
                    <span>Discount</span>
                    <span id="invDiscount"></span>
                </div>
                <div class="t-row grand">
                    <span>Grand total</span>
                    <span id="invTotal"></span>
                </div>
            </div>
        </div>

    </div>
</div>

@endsection

@push('scripts')
<script>
(function () {

    const overlay = document.getElementById('invOverlay');

    // helper
    function formatDate(str) {
        if (!str) return '';
        const d = new Date(str);
        if (isNaN(d)) return str;
        const p = n => String(n).padStart(2, '0');
        return `${d.getFullYear()}-${p(d.getMonth()+1)}-${p(d.getDate())} ${p(d.getHours())}:${p(d.getMinutes())}`;
    }

    function fmt(val, prefix = '$') {
        return prefix + parseFloat(val || 0).toFixed(2);
    }

    // open modal
    function openModal(sale) {
        document.getElementById('invNo').textContent      = sale.invoice_no;
        document.getElementById('invDate').textContent    = formatDate(sale.sale_date);
        document.getElementById('invCashier').textContent = sale.user?.name ?? 'N/A';
        document.getElementById('invPayment').textContent = sale.payment_method?.toUpperCase() ?? '—';
        document.getElementById('invStatus').textContent  = sale.status
            ? sale.status.charAt(0).toUpperCase() + sale.status.slice(1)
            : '—';
        document.getElementById('invSubtotal').textContent = fmt(sale.sub_total);
        document.getElementById('invDiscount').textContent = '– ' + fmt(sale.discount_amount);
        document.getElementById('invTotal').textContent    = fmt(sale.total_amount);

        const rows = sale.items?.length
            ? sale.items.map(item => `
                <tr>
                    <td>
                        ${item.product_name}
                        <div class="pcode">${item.product_code}</div>
                    </td>
                    <td>${parseFloat(item.quantity)}</td>
                    <td>${fmt(item.unit_price)}</td>
                    <td>${fmt(item.discount_amount)} <small>(${parseFloat(item.discount_percentage)}%)</small></td>
                    <td>${fmt(item.amount)}</td>
                </tr>`).join('')
            : `<tr><td colspan="5" style="text-align:center;color:#9ca3af;padding:1.5rem">No items found.</td></tr>`;

        document.getElementById('invItems').innerHTML = rows;

        overlay.classList.add('active');
        document.body.style.overflow = 'hidden';
    }

    function closeModal() {
        overlay.classList.remove('active');
        document.body.style.overflow = '';
    }

    document.querySelectorAll('.btn-view').forEach(btn => {
        btn.addEventListener('click', function () {
            openModal(JSON.parse(this.getAttribute('data-sale')));
        });
    });

    document.getElementById('invCloseBtn').addEventListener('click', closeModal);

    overlay.addEventListener('click', e => {
        if (e.target === overlay) closeModal();
    });

    document.addEventListener('keydown', e => {
        if (e.key === 'Escape') closeModal();
    });

})();
</script>
@endpush
