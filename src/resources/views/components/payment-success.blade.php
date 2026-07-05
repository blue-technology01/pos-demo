<style>
.success-popup {
    position: fixed;
    top: 50%;
    left: 50%;
    transform: translate(-50%, -50%);
    background: white;
    padding: 30px 40px;
    border-radius: 16px;
    box-shadow: 0 10px 30px rgba(0,0,0,0.3);
    text-align: center;
    z-index: 10000;
    min-width: 320px;
}

.success-icon {
    font-size: 64px;
    color: #22c55e;
    margin-bottom: 15px;
}

.success-title {
    margin: 0 0 8px 0;
    color: #166534;
    font-size: 24px;
}

.success-subtitle {
    color: #64748b;
    margin: 0 0 20px 0;
    font-size: 15px;
}

.success-amount {
    font-size: 28px;
    font-weight: bold;
    color: #166534;
    margin: 15px 0;
}

.success-actions {
    display: flex;
    gap: 12px;
    margin-top: 25px;
}

.success-btn {
    flex: 1;
    padding: 12px 16px;
    border: none;
    border-radius: 8px;
    font-size: 15px;
    cursor: pointer;
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 8px;
}

.success-btn-print {
    background: #3b82f6;
    color: white;
}

.success-btn-new {
    background: #22c55e;
    color: white;
}
</style>
<div class="alert-wrapper" id="success-alert" style="display: none;">
    <div class="success-popup">
        <div class="success-icon">
            <span class="material-symbols-outlined">check_circle</span>
        </div>
        <h3 class="success-title">Payment Successful!</h3>
        <p class="success-subtitle" id="success-invoice">Invoice #INV-00001</p>

        <div class="success-amount" id="success-total">$0.00</div>

        <div class="success-actions">
            <button class="success-btn success-btn-print" id="print-receipt-btn">
                <span class="material-symbols-outlined">print</span> Print Receipt
            </button>
            <button class="success-btn success-btn-new" id="new-sale-btn">
                <span class="material-symbols-outlined">add</span> New Sale
            </button>
        </div>
    </div>
</div>

<script>
// Show Success Popup
function showPaymentSuccess(invoiceNo, totalAmount) {
    $('#success-invoice').text(`Invoice #${invoiceNo}`);
    $('#success-total').text(utils.formatCurrency(totalAmount));

    $('#success-alert').fadeIn(300);

    // Auto hide after 5 seconds
    setTimeout(() => {
        $('#success-alert').fadeOut(300);
    }, 5000);
}

// Button Actions
$(document).on('click', '#new-sale-btn', function() {
    $('#success-alert').fadeOut(300);
    // Clear cart and reset
    cartManager.clear();
});

$(document).on('click', '#print-receipt-btn', function() {
    // You can call your receipt print/download here
    if (typeof paymentManager.downloadReceiptAsPDF === 'function') {
        paymentManager.downloadReceiptAsPDF();
    }
});
</script>
