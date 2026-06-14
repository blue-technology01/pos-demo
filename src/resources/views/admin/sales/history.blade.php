@extends('layouts.app')

@push('styles')
    <link rel="stylesheet" href="{{ asset('assets/css/dashboard/sale/history.css') }}">
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined" />
@endpush

@section('content')
<div class="unit-section">
    <div class="unit-section__header">
        <h2 class="page-title">Sales History</h2>
        <div class="search-wrap">
            <div class="search-box">
                <span class="material-symbols-outlined">search</span>
                <input type="text" id="searchInput" class="filter-input" placeholder="Search invoice or customer...">
            </div>

            <input type="date" id="startDate" class="filter-input">
            <input type="date" id="endDate" class="filter-input">

            <button onclick="filterTable()" class="btn-filter">
                <span class="material-symbols-outlined">filter_alt</span> Filter
            </button>
            <button onclick="resetFilter()" class="btn-reset">Reset</button>
        </div>

        <div class="export-actions">
            <button class="btn-export btn-excel">
                <span class="material-symbols-outlined">table_view</span> Excel
            </button>
            <button class="btn-export btn-pdf">
                <span class="material-symbols-outlined">picture_as_pdf</span> PDF
            </button>
        </div>
    </div>

    <div class="unit-card">
        <div class="table-responsive">
            <table class="table-custom" id="salesTable">
                <thead>
                    <tr>
                        <th>Invoice No</th>
                        <th>Date & Time</th>
                        <th>Cashier</th>
                        <th>Customer</th>
                        <th>Payment</th>
                        <th>Total</th>
                        <th>Status</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody id="salesTableBody">
                    @php
                        $sales = [
                            ['id' => 1, 'invoice_no' => '#INV-20260604-0001', 'date' => '2026-06-04 09:30 AM', 'cashier' => 'Sok Na', 'customer' => 'លោក គីម', 'payment' => 'QR', 'total' => 37.40, 'status' => 'completed',
                             'items' => [['name' => 'Coca Cola', 'qty' => 2, 'price' => 1.50, 'total' => 3.00], ['name' => 'Lay\'s Chips', 'qty' => 1, 'price' => 2.50, 'total' => 2.50]]],

                            ['id' => 2, 'invoice_no' => '#INV-20260604-0002', 'date' => '2026-06-04 10:15 AM', 'cashier' => 'Dara Sok', 'customer' => 'Walk-in', 'payment' => 'Cash', 'total' => 12.65, 'status' => 'completed',
                             'items' => [['name' => 'Pepsi', 'qty' => 3, 'price' => 1.40, 'total' => 4.20]]],

                            ['id' => 3, 'invoice_no' => '#INV-20260604-0003', 'date' => '2026-06-04 11:00 AM', 'cashier' => 'Sok Na', 'customer' => 'លោកស្រី សុភា', 'payment' => 'Cash', 'total' => 9.02, 'status' => 'voided',
                             'items' => [['name' => 'Sprite', 'qty' => 2, 'price' => 1.45, 'total' => 2.90]]],

                            ['id' => 4, 'invoice_no' => '#INV-20260604-0004', 'date' => '2026-06-04 11:45 AM', 'cashier' => 'Dara Sok', 'customer' => 'លោក សុខា', 'payment' => 'ABA', 'total' => 45.80, 'status' => 'completed',
                             'items' => [['name' => 'Red Bull', 'qty' => 5, 'price' => 2.00, 'total' => 10.00], ['name' => 'Water', 'qty' => 3, 'price' => 0.50, 'total' => 1.50]]],

                            ['id' => 5, 'invoice_no' => '#INV-20260604-0005', 'date' => '2026-06-04 13:20 PM', 'cashier' => 'Sok Na', 'customer' => 'លោក រីទី', 'payment' => 'QR', 'total' => 28.50, 'status' => 'completed',
                             'items' => [['name' => 'Coffee', 'qty' => 2, 'price' => 2.75, 'total' => 5.50]]],

                            ['id' => 6, 'invoice_no' => '#INV-20260604-0006', 'date' => '2026-06-04 14:05 PM', 'cashier' => 'Dara Sok', 'customer' => 'Walk-in', 'payment' => 'Cash', 'total' => 15.75, 'status' => 'completed',
                             'items' => [['name' => 'Bread', 'qty' => 1, 'price' => 3.50, 'total' => 3.50]]],

                            ['id' => 7, 'invoice_no' => '#INV-20260604-0007', 'date' => '2026-06-04 15:30 PM', 'cashier' => 'Sok Na', 'customer' => 'លោកស្រី ម៉ាលីសា', 'payment' => 'ABA', 'total' => 67.20, 'status' => 'completed',
                             'items' => [['name' => 'iPhone Charger', 'qty' => 1, 'price' => 12.00, 'total' => 12.00]]],

                            ['id' => 8, 'invoice_no' => '#INV-20260604-0008', 'date' => '2026-06-04 16:10 PM', 'cashier' => 'Dara Sok', 'customer' => 'លោក ចាន់', 'payment' => 'Cash', 'total' => 8.90, 'status' => 'voided',
                             'items' => [['name' => 'Candy', 'qty' => 10, 'price' => 0.30, 'total' => 3.00]]],

                            ['id' => 9, 'invoice_no' => '#INV-20260604-0009', 'date' => '2026-06-04 17:25 PM', 'cashier' => 'Sok Na', 'customer' => 'Walk-in', 'payment' => 'QR', 'total' => 22.00, 'status' => 'completed',
                             'items' => [['name' => 'Milk', 'qty' => 2, 'price' => 2.50, 'total' => 5.00]]],

                            ['id' => 10, 'invoice_no' => '#INV-20260604-0010', 'date' => '2026-06-04 18:00 PM', 'cashier' => 'Dara Sok', 'customer' => 'លោក វិរៈ', 'payment' => 'ABA', 'total' => 52.30, 'status' => 'completed',
                             'items' => [['name' => 'Beer', 'qty' => 6, 'price' => 1.80, 'total' => 10.80]]],
                        ];
                    @endphp

                    @foreach($sales as $sale)
                    <tr data-invoice="{{ strtolower($sale['invoice_no']) }}"
                        data-customer="{{ strtolower($sale['customer']) }}"
                        data-date="{{ substr($sale['date'], 0, 10) }}">
                        <td><strong>{{ $sale['invoice_no'] }}</strong></td>
                        <td>{{ $sale['date'] }}</td>
                        <td>{{ $sale['cashier'] }}</td>
                        <td>{{ $sale['customer'] }}</td>
                        <td><span class="payment-method">{{ $sale['payment'] }}</span></td>
                        <td><strong class="amount">${{ number_format($sale['total'], 2) }}</strong></td>
                        <td><span class="status-badge status-badge--{{ $sale['status'] }}">{{ ucfirst($sale['status']) }}</span></td>
                        <td>
                            <button class="btn-action btn-view" onclick='viewDetails(@json($sale))' title="View Details">
                                <span class="material-symbols-outlined">visibility</span>
                            </button>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>

{{-- Modal --}}
<div id="view-sale-modal" class="sale-modal-overlay" style="display:none;">
    <div class="sale-modal-box">
        <button class="modal-close-btn" onclick="closeModal()">×</button>
        <h3 id="modal-invoice-no"></h3>

        <div class="sale-info-grid">
            <div><span>Customer</span><strong id="modal-customer"></strong></div>
            <div><span>Date</span><strong id="modal-date"></strong></div>
        </div>

        <table class="table-custom modal-table">
            <thead><tr><th>Item</th><th>Qty</th><th>Price</th><th>Total</th></tr></thead>
            <tbody id="modal-items-body"></tbody>
        </table>

        <div class="sale-summary">
            <p><strong>Total:</strong> $<span id="modal-total" class="grand-total"></span></p>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    function viewDetails(sale) {
        document.getElementById('modal-invoice-no').innerText = sale.invoice_no;
        document.getElementById('modal-customer').innerText = sale.customer;
        document.getElementById('modal-date').innerText = sale.date;
        document.getElementById('modal-total').innerText = parseFloat(sale.total).toFixed(2);

        let html = '';
        sale.items.forEach(item => {
            html += `
                <tr>
                    <td>${item.name}</td>
                    <td>${item.qty}</td>
                    <td>$${parseFloat(item.price).toFixed(2)}</td>
                    <td>$${parseFloat(item.total).toFixed(2)}</td>
                </tr>`;
        });
        document.getElementById('modal-items-body').innerHTML = html;
        document.getElementById('view-sale-modal').style.display = 'flex';
    }

    function closeModal() {
        document.getElementById('view-sale-modal').style.display = 'none';
    }

    function filterTable() {
        const search = document.getElementById('searchInput').value.toLowerCase();
        const rows = document.querySelectorAll('#salesTableBody tr');
        rows.forEach(row => {
            const text = row.innerText.toLowerCase();
            row.style.display = text.includes(search) ? '' : 'none';
        });
    }

    function resetFilter() {
        document.getElementById('searchInput').value = '';
        document.getElementById('startDate').value = '';
        document.getElementById('endDate').value = '';
        filterTable();
    }
</script>
@endpush
