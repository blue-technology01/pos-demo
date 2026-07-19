/**
 * payment-manager.js
 * Handles payment modal, method selection, sale confirmation, and receipt generation.
 * Depends on: state.js, utils.js, cart-manager.js
 */
(function () {
    'use strict';

    window.POS = window.POS || {};

    const paymentManager = {
        selectedMethodCode: 'cash',
        selectedMethodId: null,

        init() {
            console.log(' Payment Manager Initialized');
            this.bindEvents();
            this.injectPopupStyles();
        },

        injectPopupStyles() {
            if (document.getElementById('sale-popup-styles')) return;
            const style = document.createElement('style');
            style.id = 'sale-popup-styles';
            style.textContent = `
                @keyframes salePopIn {
                    from { opacity: 0; transform: scale(0.75); }
                    to   { opacity: 1; transform: scale(1); }
                }
            `;
            document.head.appendChild(style);
        },

        openPaymentModal() {
            if (!window.currentRegisterId) {
                window.POS.utils.notify('Please open a shift before processing payment!', 'error');
                return;
            }

            const { subtotal, discount, tax, total } = window.POS.cartManager.computeTotals();

            // Reset payment method
            $('.payment-method-btn').removeClass('active');
            $('.payment-method-btn').first().addClass('active');
            this.selectedMethodCode = 'cash';

            // Populate receipt side
            $('#modal-subtotal').text(window.POS.utils.formatCurrency(subtotal));
            $('#modal-tax').text(window.POS.utils.formatCurrency(tax));
            $('#modal-discount').text(`-${window.POS.utils.formatCurrency(discount)}`);
            $('#modal-total').text(window.POS.utils.formatCurrency(total));
            $('#modal-amount-due').text(window.POS.utils.formatCurrency(total));
            $('#cash-received').val(total.toFixed(2));

            $('#modal-customer-name').text(
                document.getElementById('selected-customer-name')?.innerText || 'Walk-in Customer'
            );

            $('#modal-cart-items').html(
                window.POS.state.cart.map(item => `
                    <div class="receipt-item-row">
                        <span class="receipt-item-row__name">${window.POS.utils.escapeHtml(item.name)} (${window.POS.utils.escapeHtml(item.uom_name)})</span>
                        <span class="receipt-item-row__qty">X ${item.quantity}</span>
                        <span class="receipt-item-row__price">${window.POS.utils.formatCurrency(item.subtotal)}</span>
                    </div>
                `).join('')
            );

            this.updateChange(total);
            this.toggleCashInput();

            $('#paymentModal').removeClass('hidden').css('display', 'flex');
            setTimeout(() => $('#cash-received').focus(), 200);
        },

        updateChange(totalOverride = null) {
            const cash = parseFloat($('#cash-received').val()) || 0;
            const due = totalOverride ?? window.POS.cartManager.computeTotals().total;
            const change = cash - due;

            $('#change-amount')
                .text(change >= 0 ? window.POS.utils.formatCurrency(change) : `-${window.POS.utils.formatCurrency(Math.abs(change))}`)
                .css('color', change >= 0 ? 'green' : 'red');
        },

        toggleCashInput() {
            const isCash = this.selectedMethodCode === 'cash';
            $('#cash-received').closest('div').toggle(isCash);
            $('.change-box').toggle(isCash);
            if (!isCash) $('#change-amount').text('$0.00').css('color', '#000');
        },

        validatePayment() {
            const { total } = window.POS.cartManager.computeTotals();
            if (this.selectedMethodCode !== 'cash') {
                return { cashReceived: total, change: 0 };
            }

            const cash = parseFloat($('#cash-received').val()) || 0;
            if (cash <= 0) {
                window.POS.utils.notify('Please enter the amount received!', 'error');
                return null;
            }
            if (cash < total) {
                window.POS.utils.notify(`Insufficient: ${window.POS.utils.formatCurrency(total - cash)} more needed`, 'warning');
                return null;
            }
            return { cashReceived: cash, change: cash - total };
        },

        async confirmSale() {
            const paymentData = this.validatePayment();
            if (!paymentData) return;

            const $btn = $('#confirmPaymentBtn');
            $btn.prop('disabled', true).text('Processing…');

            const { subtotal, discount, tax, total } = window.POS.cartManager.computeTotals();
            const cartSnapshot = window.POS.state.cart.map(item => ({
                name: item.name,
                uom_name: item.uom_name || item.uom_code,
                quantity: item.quantity,
                subtotal: item.subtotal,
                product_code: item.product_code,
                uom_code: item.uom_code,
                cost_price: item.cost_price || 0,
                price: item.price,
                discount_percentage: item.discount_percentage || 0,
                discount_amount: item.discount_amount || 0,
            }));

            try {
                const data = await window.POS.utils.fetchJson(window.ROUTES.confirmSale, {
                    method: 'POST',
                    body: JSON.stringify({
                        payment_method: this.selectedMethodCode,
                        paid_amount: paymentData.cashReceived,
                        sub_total: subtotal,
                        discount_amount: discount,
                        total_amount: total,
                        change_amount: paymentData.change,
                        tax_amount: tax,
                        customer_id: window.selectedCustomerId || null,
                        register_id: window.currentRegisterId || null,
                        items: cartSnapshot.map(item => ({
                            product_code: item.product_code,
                            product_name: item.name,
                            uom_code: item.uom_code,
                            quantity: item.quantity,
                            cost_price: item.cost_price,
                            unit_price: item.price,
                            discount_percentage: item.discount_percentage,
                            discount_amount: item.discount_amount,
                            amount: item.subtotal,
                        })),
                    }),
                });

                if (data.success) {
                    $('#paymentModal').addClass('hidden').css('display', 'none');
                    window.POS.cartManager.clear(false);

                    window.selectedCustomerId = null;
                    window.selectedCustomerName = null;

                    if (window.POS.customerDisplay) {
                        window.POS.customerDisplay.window?.postMessage({ type: 'HIDE_QR' }, window.location.origin);
                        window.POS.customerDisplay.update();
                    }

                    this.showSalePopup('success', data.invoice_no);

                    if (window.PREVIEW_RECEIPT === true) {
                        this.showReceiptModal(data.invoice_no, paymentData, { subtotal, discount, tax, total }, cartSnapshot);
                    } else {
                        setTimeout(() => this.autoDownloadReceipt(data.invoice_no, paymentData, { subtotal, discount, tax, total }, cartSnapshot), 500);
                    }
                } else {
                    this.showSalePopup('error', data.message || 'Sale failed.');
                }
            } catch (err) {
                console.error('confirmSale error:', err);
                window.POS.utils.notify(err.message || 'Sale failed.', 'error');
            } finally {
                $btn.prop('disabled', false).text('Confirm & Complete Sale');
            }
        },

        showSalePopup(type, value) {
            $('#sale-popup-overlay').remove();
            const isSuccess = type === 'success';
            const html = `
            <div id="sale-popup-overlay" style="position:fixed;inset:0;background:rgba(0,0,0,0.45);display:flex;align-items:center;justify-content:center;z-index:9999;">
                <div style="background:#fff;border-radius:16px;border:1px solid #e5e7eb;padding:2rem 1.75rem 1.5rem;width:320px;text-align:center;animation:salePopIn 0.35s cubic-bezier(0.34,1.56,0.64,1) forwards;">
                    <div style="width:64px;height:64px;border-radius:50%;margin:0 auto 1.25rem;display:flex;align-items:center;justify-content:center;font-size:28px;background:${isSuccess ? '#dcfce7' : '#fee2e2'};border:2px solid ${isSuccess ? '#86efac' : '#fca5a5'};color:${isSuccess ? '#16a34a' : '#dc2626'};">
                        ${isSuccess ? '✓' : '!'}
                    </div>
                    <p style="font-size:18px;font-weight:600;margin:0 0 6px;color:#111827;">${isSuccess ? 'Sale complete' : 'Sale failed'}</p>
                    <p style="font-size:13px;color:#6b7280;margin:0 0 1.25rem;line-height:1.6;">${isSuccess ? 'Payment received and recorded.' : window.POS.utils.escapeHtml(String(value))}</p>
                    ${isSuccess ? `<div style="display:inline-flex;align-items:center;gap:6px;background:#f9fafb;border:1px solid #e5e7eb;border-radius:8px;padding:6px 14px;font-size:13px;font-weight:500;color:#15803d;margin-bottom:1.25rem;">Invoice: #${window.POS.utils.escapeHtml(String(value))}</div>` : ''}
                </div>
            </div>`;
            $('body').append(html);

            setTimeout(() => {
                $('#sale-popup-overlay').fadeOut(300, function () { $(this).remove(); });
            }, 3000);
        },

        // ... (Receipt methods - shortened for brevity, add if needed)

        bindEvents() {
            $(document).off('click.payMethod')
                .on('click.payMethod', '.payment-method-btn', (e) => {
                    const $t = $(e.currentTarget);
                    $('.payment-method-btn').removeClass('active');
                    $t.addClass('active');
                    this.selectedMethodCode = $t.data('method') || 'cash';
                    this.toggleCashInput();
                    this.updateChange();
                });

            $(document).on('input', '#cash-received', () => this.updateChange());
            $(document).on('keypress', '#cash-received', (e) => {
                if (e.which === 13) this.confirmSale();
            });

            $('#process-payment-btn').on('click', () => this.openPaymentModal());
            $('#closePaymentModal, #cancelPaymentBtn').on('click', () => {
                $('#paymentModal').addClass('hidden').css('display', 'none');
            });
            $('#confirmPaymentBtn').on('click', () => this.confirmSale());
        }
    };

    window.POS.paymentManager = paymentManager;
})();
