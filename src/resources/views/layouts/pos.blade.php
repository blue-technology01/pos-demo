<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>@yield('title', 'POS - Point of Sale')</title>

    <!-- Tabler Icons (you are using this) -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@tabler/icons-webfont@latest/tabler-icons.min.css">

    <!-- Lucide Icons (if you want to keep using it) -->
    <script src="https://unpkg.com/lucide@latest/dist/umd/lucide.js" defer></script>

    <!-- jQuery + UI -->
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <script src="https://code.jquery.com/ui/1.14.2/jquery-ui.min.js"></script>
    <link rel="stylesheet" href="https://code.jquery.com/ui/1.14.2/themes/base/jquery-ui.css">

    <link rel="stylesheet" href="{{ asset('assets/css/pos.css') }}">

    @stack('styles')
</head>
<body class="pos-body">

    <div class="pos-wrapper">
        @yield('content')
    </div>

    <script src="{{ asset('assets/js/pos.js') }}"></script>
    @stack('scripts')

    <script>
        document.addEventListener('DOMContentLoaded', () => {
            if (typeof lucide !== 'undefined') {
                lucide.createIcons();
            }
        });
    </script>

    @push('scripts')
<script>
$(function() {

    // Open Payment Modal
    $('#process-payment-btn').on('click', function() {
        let total = parseFloat($('#receipt-total').text().replace('$','')) || 0;
        if (total <= 0) {
            alert("Cart is empty!");
            return;
        }
        $('#paymentModal').removeClass('hidden');
        $('#displayAmount').text('$' + total.toFixed(2));
    });

    // Close Modal
    $('#closePaymentModal').on('click', function() {
        $('#paymentModal').addClass('hidden');
    });

    // Number Pad
    let currentAmount = '';

    $('.num-key').on('click', function() {
        let val = $(this).text();
        if (val === '⌫') {
            currentAmount = currentAmount.slice(0, -1);
        } else {
            currentAmount += val;
        }
        updateDisplay();
    });

    function updateDisplay() {
        let num = parseFloat(currentAmount) || 0;
        $('#displayAmount').text('$' + num.toFixed(2));
    }

    // Quick Amounts
    $('.quick-amount-btn').on('click', function() {
        let amount = $(this).data('amount');
        currentAmount = amount.toString();
        updateDisplay();
    });

    // Backspace
    $('#backspace').on('click', function() {
        currentAmount = currentAmount.slice(0, -1);
        updateDisplay();
    });

    // Pay Now
    $('#payNowBtn').on('click', function() {
        let total = parseFloat($('#receipt-total').text().replace('$','')) || 0;
        let paid = parseFloat($('#displayAmount').text().replace('$','')) || 0;

        if (paid < total) {
            alert("Paid amount is not enough!");
            return;
        }

        alert(`Payment Successful!\nTotal: $${total.toFixed(2)}\nPaid: $${paid.toFixed(2)}\nChange: $${(paid - total).toFixed(2)}`);

        // Clear cart after success
        cart = {};
        renderCart();
        $('#paymentModal').addClass('hidden');
    });

    // Close on backdrop click
    $('#paymentModal').on('click', function(e) {
        if (e.target.id === 'paymentModal') {
            $(this).addClass('hidden');
        }
    });
});
</script>

<script src="{{ asset('assets/js/pos.js') }}"></script>
@endpush

</body>
</html>
