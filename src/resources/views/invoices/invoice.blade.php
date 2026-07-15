<!DOCTYPE html>
<html>
<head>
<meta charset="utf-8">
<title>Invoice {{ $sale->invoice_no }}</title>
<style>
    *{
        box-sizing: border-box;
    }
    body {
        font-family: Arial, Helvetica, sans-serif;
        color: #1F2937;
        font-size: 12px;
        margin: 0;
        background: #EAEAEA;
    }
    .page {
        width: 210mm;
        min-height: 297mm;
        margin: 16px auto 40px;
        background: #fff;
        padding: 18mm 16mm;
        box-shadow: 0 0 8px rgba(0,0,0,0.15);
    }
    table {
        border-collapse: collapse;
        width: 100%;
    }
    .header-table td {
        vertical-align: top;
        padding: 0;
    }
    .store-brand {
        display: flex;
        align-items: center;
        gap: 14px;
    }
    .store-logo {
        width: 120px;
        max-width: 120px;
        height: auto;
    }
    .store-name {
        font-size: 20px;
        font-weight: bold;
        margin-bottom: 4px;
    }
    .store-meta {
        font-size: 10px;
        color: #6B7280;
        line-height: 1.5;
    }
    .invoice-title {
        font-size: 24px;
        font-weight: bold;
        color: #2563EB;
        text-align: right;
    }
    .meta-table {
        width: 100%;
        font-size: 10px;
        margin-top: 6px;
    }
    .meta-table td {
        padding: 2px 0;
    }
    .meta-label {
        color: #6B7280;
        font-weight: bold;
        text-align: right;
        padding-right: 8px;
    }
    .meta-value {
        text-align: right;
    }
    .header-divider {
        border-bottom: 1px solid #14171a;
        margin: 10px 0 16px 0;
    }
    table.items th, table.items td {
        border: 1px solid #E5E7EB;
    }
    table.items th {
        background-color: #1F2937;
        color: #fff;
        padding: 10px 8px;
    }
    table.items td {
        padding: 10px 8px;
    }
    table.items tr:nth-child(even) {
        background-color: #F8FAFC;
    }
    table.totals {
        width: 320px;
        float: right;
        font-size: 11px;
        border-collapse: collapse;
        margin-top: 16px;
    }
    table.totals td {
        padding: 8px 10px;
        border: 1px solid #E5E7EB;
    }
    table.totals tr.grand td {
        background-color: #1F2937;
        color: #fff;
        font-weight: bold;
    }
    .bill-to {
        background-color: #F3F4F6;
        padding: 8px 12px;
        font-size: 11px;
        margin-bottom: 16px;
    }
    .bill-to .label {
        font-size: 9px;
        font-weight: bold;
        color: #6B7280;
        letter-spacing: 0.5px;
    }
    .bill-to .name {
        font-size: 13px;
        font-weight: bold;
        margin-top: 2px;
    }
    table.items {
        margin-bottom: 12px;
    }
    table.items th {
        background-color: #2563EB;
        color: #fff;
        text-align: left;
        padding: 6px 8px;
        font-size: 10px;
    }
    table.items th.num {
        text-align: right;
    }
    table.items td {
        padding: 5px 8px;
        border-bottom: 1px solid #D1D5DB;
        font-size: 11px;
    }
    table.items td.num {
        text-align: right;
    }
    table.items tr:nth-child(even) {
        background-color: #FAFAFA;
    }
    .pcode {
        font-size: 9px;
        color: #9CA3AF;
    }

    table.totals {
        width: 260px;
        float: right;
        font-size: 11px;
    }
    table.totals td {
        padding: 4px 8px;
    }
    table.totals td.label {
        color: #374151;
    }
    table.totals td.value {
        text-align: right;
    }
    table.totals tr.grand td {
        background-color: #1F2937;
        color: #fff;
        font-weight: bold;
        font-size: 13px;
        padding: 7px 8px;
    }
    .footer {
        text-align: center;
        font-size: 10px;
        color: #6B7280;
        margin-top: 80px;
        clear: both;
    }
    .footer .thanks {
        font-size: 12px;
        font-style: italic;
        color: #1F2937;
        margin-bottom: 3px;
    }

    @media print {
        body {
            background: #fff;
        }
        .page {
            box-shadow: none;
            margin: 0;
            width: auto;
            min-height: auto;
            padding: 12mm 14mm;
        }
        @page {
            size: A4;
            margin: 0;
        }
    }
</style>
</head>
<body>

    <div class="page">
        <table class="header-table">
            <tr>
                <td style="width: 55%;">
                    <div class="store-brand">
                        <div>
                            <img class="store-logo" src="{{ asset('assets/images/logo.png') }}" alt="Store logo">
                            <div class="store-meta">
                                123 Main Street, Phnom Penh, Cambodia<br>
                                Phone: +855 12 345 678 &nbsp;|&nbsp; Email: blue@gmail.com
                            </div>
                        </div>
                    </div>
                </td>
                <td style="width: 45%;">
                    <div class="invoice-title">INVOICE</div>
                    <table class="meta-table">
                        <tr>
                            <td class="meta-label">Invoice #:</td>
                            <td class="meta-value">{{ $sale->invoice_no }}</td>
                        </tr>
                        <tr>
                            <td class="meta-label">Date:</td>
                            {{-- <td class="meta-value">{{ $sale->created_at->format('d M Y, H:i') }}</td> --}}
                            <td class="meta-value">{{ $sale->created_at->format('d M Y') }}</td>
                        </tr>
                        <tr>
                            <td class="meta-label">Cashier:</td>
                            <td class="meta-value">{{ $sale->user->name ?? 'N/A' }}</td>
                        </tr>
                        <tr>
                            <td class="meta-label">Payment:</td>
                            <td class="meta-value">{{ strtoupper($sale->payment_method) }}</td>
                        </tr>
                    </table>
                </td>
            </tr>
        </table>
        <div class="header-divider"></div>
        <div class="bill-to">
            <div class="label">BILL TO</div>
            <div class="name">{{ $sale->customer->name ?? 'Walk-in Customer' }}</div>
        </div>

        <table class="items">
            <thead>
                <tr>
                    <th style="width: 38%;">Item</th>
                    <th class="num" style="width: 12%;">Qty</th>
                    <th class="num" style="width: 18%;">Unit Price</th>
                    <th class="num" style="width: 16%;">Discount</th>
                    <th class="num" style="width: 16%;">Amount</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($sale->items as $item)
                    <tr>
                        <td>
                            {{ $item->product_name }}
                            <div class="pcode">{{ $item->product_code }}</div>
                        </td>
                        <td class="num">{{ (float) $item->quantity }}</td>
                        <td class="num">${{ number_format($item->unit_price, 2) }}</td>
                        <td class="num">${{ number_format($item->discount_amount, 2) }} ({{ (float) $item->discount_percentage }}%)</td>
                        <td class="num">${{ number_format($item->amount, 2) }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" style="text-align:center;color:#9CA3AF;">No items found.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
        <table class="totals">
            <tr>
                <td class="label">Subtotal</td>
                <td class="value">${{ number_format($sale->sub_total, 2) }}</td>
            </tr>
            <tr>
                <td class="label">Discount</td>
                <td class="value">– ${{ number_format($sale->discount_amount, 2) }}</td>
            </tr>
            <tr class="grand">
                <td>TOTAL DUE</td>
                <td class="value">${{ number_format($sale->total_amount, 2) }}</td>
            </tr>
        </table>
        <div class="footer">
            <div class="thanks">Thank you for your purchase!</div>
            Please keep this invoice for your records.
        </div>
    </div>

    <script>
        window.onload = function () {
            window.print();
        };
        // Close the tab after printing/cancelling (optional — comment out if you don't want this)
        window.onafterprint = function () {
            window.close();
        };
    </script>
</body>
</html>
