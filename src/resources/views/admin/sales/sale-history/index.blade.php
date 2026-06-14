@extends('layouts.app')

@push('styles')
    <link rel="stylesheet" href="{{ asset('assets/css/dashboard/sale/history.css') }}">
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined" />
@endpush

@section('content')
<div class="unit-section">

    {{-- ===== HEADER ===== --}}
    <div class="unit-section__header">
        <h2 class="page-title">List Sales History</h2>

        {{-- Search + Filter wrapped in .search-wrap to match CSS --}}
        <form action="{{ route('admin.sales.index') }}" method="GET" class="search-wrap">
            <div class="search-box">
                <span class="material-symbols-outlined">search</span>
                <input type="text" name="invoice_no" value="{{ request('invoice_no') }}"
                       class="filter-input" placeholder="Search invoice number...">
            </div>

            <input type="date" name="date_from" value="{{ request('date_from') }}"
                   class="filter-input" title="ចាប់ពីកាលបរិច្ឆេទ">

            <input type="date" name="date_to" value="{{ request('date_to') }}"
                   class="filter-input" title="រហូតដល់កាលបរិច្ឆេទ">

            <button type="submit" class="btn-filter">
                <span class="material-symbols-outlined">filter_alt</span> Filter
            </button>

            <a href="{{ route('admin.sales.index') }}" class="btn-reset">Reset</a>
        </form>

        <div class="export-actions">
            <button class="btn-export btn-excel">
                <span class="material-symbols-outlined">table_view</span> Excel
            </button>
            <button class="btn-export btn-pdf">
                <span class="material-symbols-outlined">picture_as_pdf</span> PDF
            </button>
        </div>
    </div>

    {{-- ===== ALERTS ===== --}}
    @if(session('success'))
        <div style="background:#d4edda;color:#155724;padding:12px;margin-bottom:15px;border-radius:8px;">
            {{ session('success') }}
        </div>
    @endif
    @if(session('error'))
        <div style="background:#f8d7da;color:#721c24;padding:12px;margin-bottom:15px;border-radius:8px;">
            {{ session('error') }}
        </div>
    @endif

    {{-- ===== TABLE ===== --}}
    <div class="unit-card">
        <div class="table-responsive">
            <table class="table-custom" id="salesTable">
                <thead>
                    <tr>
                        <th>Invoice No</th>
                        <th>Date & Time</th>
                        <th>Cashier</th>
                        <th>Payment</th>
                        <th>Total</th>
                        <th>Status</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($sales as $sale)
                    <tr>
                        <td><strong>{{ $sale->invoice_no }}</strong></td>
                        <td>{{ \Carbon\Carbon::parse($sale->sale_date)->format('Y-m-d H:i') }}</td>
                        <td>{{ $sale->user->name ?? 'N/A' }}</td>
                        <td><span class="payment-method">{{ strtoupper($sale->payment_method) }}</span></td>
                        <td><strong class="amount">${{ number_format($sale->total_amount, 2) }}</strong></td>
                        <td>
                            <span class="status-badge status-badge--{{ $sale->status }}">
                                {{ ucfirst($sale->status) }}
                            </span>
                        </td>
                        <td>
                            <div class="action-flex">
                                <button class="btn-action btn-view"
                                        data-sale='@json($sale)'
                                        title="View Invoice">
                                    <span class="material-symbols-outlined">visibility</span>
                                </button>
                                @if($sale->status !== 'voided')
                                    <form action="{{ route('admin.sales.cancel', $sale->id) }}" method="POST"
                                          onsubmit="return confirm('តើអ្នកពិតជាចង់បោះបង់វិក្កយបត្រនេះមែនទេ?')">
                                        @csrf
                                        <button type="submit" class="btn-cancel" title="Void / Cancel Sale">
                                            <span class="material-symbols-outlined" style="font-size:18px;">block</span>
                                        </button>
                                    </form>
                                @endif
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" style="text-align:center;padding:24px;color:#6c757d;">
                            មិនមានទិន្នន័យវិក្កយបត្រលក់ឡើយ។
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div style="margin-top:15px;display:flex;justify-content:center;padding-bottom:16px;">
            {{ $sales->appends(request()->query())->links() }}
        </div>
    </div>
</div>

{{-- ===== INVOICE POPUP OVERLAY ===== --}}
<div class="inv-overlay" id="invOverlay">
    <div class="inv-modal" id="invModal">
        <button class="inv-close-btn" id="invCloseBtn">×</button>

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

        <div class="inv-meta">
            <div class="inv-meta-cell"><div class="lbl">Date</div><div class="val" id="invDate"></div></div>
            <div class="inv-meta-cell"><div class="lbl">Cashier</div><div class="val" id="invCashier"></div></div>
            <div class="inv-meta-cell"><div class="lbl">Payment</div><div class="val" id="invPayment"></div></div>
            <div class="inv-meta-cell"><div class="lbl">Status</div><div class="val" id="invStatus"></div></div>
        </div>

        <div class="inv-body">
            <table class="inv-items">
                <thead>
                    <tr>
                        <th>Item</th>
                        <th>Qty</th>
                        <th>Unit Price</th>
                        <th>Discount</th>
                        <th>Amount</th>
                    </tr>
                </thead>
                <tbody id="invItems"></tbody>
            </table>
        </div>

        <div class="inv-foot">
            <div class="inv-note">Thank you for your purchase!</div>
            <div class="inv-totals">
                <div class="t-row"><span>Subtotal</span><span id="invSubtotal"></span></div>
                <div class="t-row"><span>Discount</span><span id="invDiscount"></span></div>
                <div class="t-row grand"><span>Grand Total</span><span id="invTotal"></span></div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
(function () {
    var overlay = document.getElementById('invOverlay');

    function formatDate(str) {
        if (!str) return '';
        var d = new Date(str);
        if (isNaN(d)) return str;
        var p = function(n){ return String(n).padStart(2,'0'); };
        return d.getFullYear() + '-' + p(d.getMonth()+1) + '-' + p(d.getDate())
             + ' ' + p(d.getHours()) + ':' + p(d.getMinutes());
    }

    function openModal(sale) {
        var cashier = (sale.user && sale.user.name) ? sale.user.name : 'N/A';

        document.getElementById('invNo').textContent       = sale.invoice_no;
        document.getElementById('invDate').textContent     = formatDate(sale.sale_date);
        document.getElementById('invCashier').textContent  = cashier;
        document.getElementById('invPayment').textContent  = sale.payment_method.toUpperCase();
        document.getElementById('invStatus').textContent   = sale.status.charAt(0).toUpperCase() + sale.status.slice(1);
        document.getElementById('invSubtotal').textContent = '$' + parseFloat(sale.sub_total).toFixed(2);
        document.getElementById('invDiscount').textContent = '- $' + parseFloat(sale.discount_amount).toFixed(2);
        document.getElementById('invTotal').textContent    = '$' + parseFloat(sale.total_amount).toFixed(2);

        var rows = '';
        if (sale.items && sale.items.length > 0) {
            sale.items.forEach(function(item) {
                rows += '<tr>' +
                    '<td>' + item.product_name + '<div class="pcode">' + item.product_code + '</div></td>' +
                    '<td>' + parseFloat(item.quantity) + '</td>' +
                    '<td>$' + parseFloat(item.unit_price).toFixed(2) + '</td>' +
                    '<td>$' + parseFloat(item.discount_amount).toFixed(2) + ' <small>(' + parseFloat(item.discount_percentage) + '%)</small></td>' +
                    '<td>$' + parseFloat(item.amount).toFixed(2) + '</td>' +
                '</tr>';
            });
        } else {
            rows = '<tr><td colspan="5" style="text-align:center;color:#adb5bd;padding:16px;">គ្មានទំនិញលម្អិតឡើយ</td></tr>';
        }
        document.getElementById('invItems').innerHTML = rows;

        overlay.classList.add('active');
        document.body.style.overflow = 'hidden';
    }

    function closeModal() {
        overlay.classList.remove('active');
        document.body.style.overflow = '';
    }

    document.querySelectorAll('.btn-view').forEach(function(btn) {
        btn.addEventListener('click', function() {
            openModal(JSON.parse(this.getAttribute('data-sale')));
        });
    });

    document.getElementById('invCloseBtn').addEventListener('click', closeModal);

    overlay.addEventListener('click', function(e) {
        if (e.target === overlay) closeModal();
    });

    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') closeModal();
    });
}());
</script>
@endpush
