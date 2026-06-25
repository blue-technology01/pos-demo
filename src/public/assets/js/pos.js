(function () {
    'use strict';

    const state = {
        products: [],
        cart: [],
        searchQuery: '',
        activeCategory: 'all',
        isLoading: false,
        pagination: {
            current_page: 1,
            last_page: 1,
            per_page: 20
        }
    };
    const utils = {
        formatCurrency(amount) {
            return new Intl.NumberFormat('en-US', {
                style: 'currency',
                currency: 'USD'
            }).format(parseFloat(amount) || 0);
        },

        debounce(fn, delay = 250) {
            let t;
            return (...args) => {
                clearTimeout(t);
                t = setTimeout(() => fn.apply(this, args), delay);
            };
        },

        notify(msg, type = 'info') {
            const colors = {
                success: '#22c55e',
                error: '#ef4444',
                warning: '#f59e0b',
                info: '#3b82f6'
            };

            const el = document.createElement('div');
            el.textContent = msg;
            el.style = `
                position:fixed;bottom:20px;right:20px;
                background:${colors[type]};color:#fff;
                padding:10px 14px;border-radius:6px;
                font-size:13px;z-index:99999;
            `;
            document.body.appendChild(el);
            setTimeout(() => el.remove(), 2500);
        }
    };

    function getAvailableStock(code) {
        const product = productManager.getProductByCode(code);
        if (!product) return 0;

        let used = 0;

        for (const item of state.cart) {
            if (item.product_code === code) {
                used += item.quantity * item.uom_qty_per_unit;
            }
        }
        return product.stock - used;
    }

    const productManager = {

        init() {
            this.bindEvents();
            this.fetchProducts();
        },

        buildUrl(page = 1) {
            const params = new URLSearchParams({
                page,
                per_page: state.pagination.per_page
            });

            if (state.searchQuery) {
                params.set('search', state.searchQuery);
            }

            if (state.activeCategory !== 'all') {
                params.set('category', state.activeCategory);
            }

            return `${window.ROUTES.posProducts}?${params}`;
        },

        async fetchProducts(page = 1) {
            try {
                state.isLoading = true;

                const res = await fetch(this.buildUrl(page)).then(r => r.json());
                const paginator = res.data;

                state.products = (paginator.data || []).map(p => ({
                    product_code: p.product_code,
                    name: p.product_name,
                    image: p.product_image
                        ? `/storage/${p.product_image}`
                        : '/assets/images/no-image.png',
                    stock: parseFloat(p.stock) || 0,
                    uoms: p.uoms || [],
                    price: parseFloat(p.selling_price ?? p.price ?? 0),
                    cost_price: parseFloat(p.cost_price) || 0,
                    category_code: p.category_code || null
                }));

                state.pagination.current_page = paginator.current_page;
                state.pagination.last_page = paginator.last_page;

                this.renderGrid();

            } catch (e) {
                console.error(e);
                utils.notify('Failed to load products', 'error');
            } finally {
                state.isLoading = false;
            }
        },

        getProductByCode(code) {
            return state.products.find(p => p.product_code === code);
        },

        renderGrid() {
            const $grid = $('#product-grid');

            if (!state.products.length) {
                $grid.html('<div style="padding:20px;text-align:center;">No products</div>');
                return;
            }

            $grid.html(state.products.map(p => {
                const stock = getAvailableStock(p.product_code);
                const out = stock <= 0;

                return `
                    <div class="product-card ${out ? 'disabled' : ''}">
                        <img src="${p.image}" />

                        <div class="name">${p.name}</div>

                        <div class="price">${utils.formatCurrency(p.price)}</div>

                        <button class="add-btn"
                            data-code="${p.product_code}"
                            ${out ? 'disabled' : ''}>
                            Add
                        </button>

                        <small>${stock} left</small>
                    </div>
                `;
            }).join(''));

            this.bindGrid();
        },

        bindGrid() {
            $('#product-grid')
                .off('click')
                .on('click', '.add-btn', (e) => {
                    const code = $(e.currentTarget).data('code');
                    cartManager.addDefault(code);
                });
        },

        bindEvents() {
            $('#product-search').on('input', utils.debounce((e) => {
                state.searchQuery = e.target.value.trim();
                this.fetchProducts(1);
            }, 300));

            $(document).on('click', '.category-btn', (e) => {
                state.activeCategory = $(e.currentTarget).data('category');
                this.fetchProducts(1);
            });
        }
    };
    // cart manager
    const cartManager = {
        addDefault(code) {
            const product = productManager.getProductByCode(code);
            if (!product) return;

            const uom = product.uoms?.find(u => u.is_default)
                || product.uoms?.[0]
                || {
                    uom_code: 'UNIT',
                    uom_name: 'Unit',
                    quantity_per_unit: 1,
                    selling_price: product.price
                };

            this.addItem(product, uom);
        },

        addItem(product, uom) {
            const id = `${product.product_code}-${uom.uom_code}`;

            const existing = state.cart.find(i => i.id === id);

            if (existing) {
                return this.updateQty(id, existing.quantity + 1);
            }

            const used = state.cart.reduce((s, i) =>
                i.product_code === product.product_code
                    ? s + (i.quantity * i.uom_qty_per_unit)
                    : s
            , 0);

            if (uom.quantity_per_unit > (product.stock - used)) {
                utils.notify('Stock not enough', 'error');
                return;
            }

            state.cart.push({
                id,
                product_code: product.product_code,
                name: product.name,
                price: uom.selling_price,
                quantity: 1,
                uom_code: uom.uom_code,
                uom_name: uom.uom_name,
                uom_qty_per_unit: uom.quantity_per_unit,
                subtotal: uom.selling_price
            });

            this.renderCart();
            this.renderTotals();
        },

        updateQty(id, qty) {
            const item = state.cart.find(i => i.id === id);
            if (!item) return;

            if (qty <= 0) return this.remove(id);

            const product = productManager.getProductByCode(item.product_code);

            const used = state.cart.reduce((s, i) =>
                i.product_code === item.product_code && i.id !== id
                    ? s + (i.quantity * i.uom_qty_per_unit)
                    : s
            , 0);

            const available = product.stock - used;

            if (qty * item.uom_qty_per_unit > available) {
                utils.notify('Not enough stock', 'warning');
                return;
            }

            item.quantity = qty;
            item.subtotal = item.price * qty;

            this.patch(id);
            this.renderTotals();
        },

        remove(id) {
            state.cart = state.cart.filter(i => i.id !== id);
            this.renderCart();
            this.renderTotals();
        },

        renderCart() {
            const $list = $('#cart-list');

            $list.html(state.cart.map(i => `
                <li data-id="${i.id}">
                    ${i.name}
                    <button class="minus" data-id="${i.id}">-</button>
                    <span>${i.quantity}</span>
                    <button class="plus" data-id="${i.id}">+</button>
                    ${utils.formatCurrency(i.subtotal)}
                </li>
            `).join(''));

            this.bindCart();
        },

        patch(id) {
            const item = state.cart.find(i => i.id === id);
            const $el = $(`[data-id="${id}"]`);

            if (!$el.length || !item) return;

            $el.find('span').text(item.quantity);
        },

        renderTotals() {
            let total = 0;

            for (const i of state.cart) {
                total += i.price * i.quantity;
            }

            $('#cart-total').text(utils.formatCurrency(total));
        },

        bindCart() {
            $('#cart-list')
                .off('click')
                .on('click', '.plus', (e) => {
                    const id = $(e.currentTarget).data('id');
                    const item = state.cart.find(i => i.id === id);
                    this.updateQty(id, item.quantity + 1);
                })
                .on('click', '.minus', (e) => {
                    const id = $(e.currentTarget).data('id');
                    const item = state.cart.find(i => i.id === id);
                    this.updateQty(id, item.quantity - 1);
                });
        }
    };
    const paymentManager = {
        selectedMethodId:   null,
        selectedMethodCode: 'cash',

        init() { this.bindEvents(); },

        openPaymentModal() {
            if (!window.currentRegisterId) {
                utils.showNotification('Please open shift (Open Shift/Register)  before !', 'error');
                return;
            }
            if (cartManager.isEmpty()) {
                utils.showNotification('មិនទាន់មានទំនិញក្នុងកន្ត្រកឡើយ!', 'warning');
                return;
            }

            const { subtotal, discount, tax, total } = cartManager.computeTotals();

            const $defaultBtn = $('.payment-method-btn').first();
            $('.payment-method-btn').removeClass('active');
            if ($defaultBtn.length > 0) {
                $defaultBtn.addClass('active');
                this.selectedMethodId   = $defaultBtn.data('id');
                this.selectedMethodCode = $defaultBtn.data('method') || 'cash';
            } else {
                this.selectedMethodId = 1; this.selectedMethodCode = 'cash';
            }

            $('#modal-subtotal').text(utils.formatCurrency(subtotal));
            $('#modal-tax').text(utils.formatCurrency(tax));
            $('#modal-discount').text(`-${utils.formatCurrency(discount)}`);
            $('#modal-total').text(utils.formatCurrency(total));
            $('#modal-amount-due').text(utils.formatCurrency(total));
            $('#cash-received').val(total.toFixed(2));
            $('#modal-customer-name').text(
                document.getElementById('selected-customer-name')?.innerText || 'Walk-in Customer'
            );
            $('#modal-cart-items').html(state.cart.map(item => `
                <div class="receipt-item-row">
                    <span class="receipt-item-row__name">${item.name} (${item.uom_name || item.uom_code})</span>
                    <span class="receipt-item-row__qty">× ${item.quantity}</span>
                    <span class="receipt-item-row__price">${utils.formatCurrency(item.subtotal)}</span>
                </div>`).join(''));

            this.updateChange(total);
            this.toggleCashInput();
            $('#paymentModal').removeClass('hidden').css('display', 'flex');
            setTimeout(() => $('#cash-received').focus(), 200);
        },

        updateChange(total = null) {
            const cash   = parseFloat($('#cash-received').val()) || 0;
            const due    = total ?? cartManager.computeTotals().total;
            const change = cash - due;
            $('#change-amount')
                .text(change >= 0 ? utils.formatCurrency(change) : `-${utils.formatCurrency(Math.abs(change))}`)
                .css('color', change >= 0 ? 'green' : 'red');
        },

        toggleCashInput() {
            const isCash = this.selectedMethodCode === 'cash';
            $('#cash-received').closest('div').toggle(isCash);
            $('.change-box').toggle(isCash);
            if (!isCash) $('#change-amount').text('$0.00').css('color', '#000');
        },

        validatePayment() {
            const { total } = cartManager.computeTotals();
            if (this.selectedMethodCode !== 'cash') return { cashReceived: total, change: 0 };
            const cash = parseFloat($('#cash-received').val()) || 0;
            if (cash <= 0)    { utils.showNotification('សូមបញ្ចូលចំនួនទឹកប្រាក់ទទួលបាន!', 'error'); return null; }
            if (cash < total) { utils.showNotification(`លុយខ្វះចំនួន៖ ${utils.formatCurrency(total - cash)} ទៀត`, 'warning'); return null; }
            return { cashReceived: cash, change: cash - total };
        },

        async confirmSale() {
            const paymentData = this.validatePayment();
            if (!paymentData) return;

            const $btn = $('#confirmPaymentBtn');
            $btn.prop('disabled', true).text('Processing...');

            const { subtotal, discount, tax, total } = cartManager.computeTotals();

            // ✅ snapshot full cart BEFORE anything — keeps all fields for POST + receipt
            const cartSnapshot = state.cart.map(item => ({
                name:                item.name,
                uom_name:            item.uom_name || item.uom_code,
                quantity:            item.quantity,
                subtotal:            item.subtotal,
                product_code:        item.product_code,
                uom_code:            item.uom_code,
                cost_price:          item.cost_price          || 0,
                price:               item.price,
                discount_percentage: item.discount_percentage || 0,
                discount_amount:     item.discount_amount     || 0,
            }));

            // show receipt immediately with placeholder invoice number
            this.showReceiptModal('...', paymentData, { subtotal, discount, tax, total }, cartSnapshot);

            // clear cart now — cashier can start next sale while server processes
            cartManager.clear(true);

            // POST to server in background — only update invoice number when done
            try {
                const data = await utils.fetchJson(window.ROUTES.confirmSale, {
                    method: 'POST',
                    body: JSON.stringify({
                        payment_method:  this.selectedMethodCode,
                        paid_amount:     paymentData.cashReceived,
                        sub_total:       subtotal,
                        discount_amount: discount,
                        total_amount:    total,
                        change_amount:   paymentData.change,
                        tax_amount:      tax,
                        customer_id:     window.selectedCustomerId || null,
                        register_id:     window.currentRegisterId  || null,
                        items: cartSnapshot.map(item => ({
                            product_code:        item.product_code,
                            product_name:        item.name,
                            uom_code:            item.uom_code,
                            quantity:            item.quantity,
                            cost_price:          item.cost_price,
                            unit_price:          item.price,
                            discount_percentage: item.discount_percentage,
                            discount_amount:     item.discount_amount,
                            amount:              item.subtotal,
                        })),
                    }),
                });

                if (data.success) {
                   
                    $('#receipt-invoice').text(`#${data.invoice_no}`);
                    utils.showNotification(`ការលក់ជោគជ័យ! លេខវិក្កយបត្រ៖ ${data.invoice_no}`, 'success');
                } else {
                    $('#receipt-invoice').text('⚠ Failed');
                    utils.showNotification(data.message || 'ការលក់បរាជ័យ។', 'error');
                }
            } catch (err) {
                $('#receipt-invoice').text('⚠ Error');
                utils.showNotification(err.message || 'ការលក់បរាជ័យ។', 'error');
            } finally {
                $btn.prop('disabled', false).text('Confirm & Complete Sale');
            }
        },

        showReceiptModal(invoiceNo, paymentData, totals, cartSnapshot) {
            const now = new Date();
            $('#receipt-date').text(
                now.toLocaleDateString('en-US', { weekday:'short', month:'long', day:'numeric', year:'numeric' })
                + ' • ' + now.toLocaleTimeString('en-US', { hour:'2-digit', minute:'2-digit' })
            );
            $('#receipt-invoice').text(`#${invoiceNo}`);
            $('#receipt-customer').text(
                document.getElementById('selected-customer-name')?.innerText || 'Walk-in Customer'
            );

            // ✅ render from snapshot — all data in memory, zero wait
            $('#receipt-items').html(cartSnapshot.map(item => `
                <div class="receipt-item">
                    <div>${item.name} (${item.uom_name}) × ${item.quantity}</div>
                    <div>${utils.formatCurrency(item.subtotal)}</div>
                </div>`).join(''));

            $('#r-subtotal').text(utils.formatCurrency(totals.subtotal));
            $('#r-tax').text(utils.formatCurrency(totals.tax));
            $('#r-discount').text(utils.formatCurrency(totals.discount));
            $('#r-total').text(utils.formatCurrency(totals.total));
            $('#r-payment-method').text(this.selectedMethodCode === 'cash' ? 'Cash' : 'QR / Mobile');
            $('#r-cash-received').text(utils.formatCurrency(paymentData.cashReceived));
            $('#r-change').text(utils.formatCurrency(paymentData.change));

            $('#paymentModal').addClass('hidden').css('display', 'none');
            $('#receiptModal').removeClass('hidden').css('display', 'flex');
        },

        closeAllModals() {
            $('#paymentModal').addClass('hidden').css('display', 'none');
            $('#receiptModal').addClass('hidden').css('display', 'none');
        },

        downloadReceiptAsPDF() {
            if (typeof html2pdf === 'undefined') { utils.showNotification('PDF library not loaded', 'error'); return; }
            const el = document.querySelector('.receipt-paper');
            if (!el) { utils.showNotification('Receipt element not found!', 'error'); return; }
            const invoiceNo = $('#receipt-invoice').text().trim().replace('#', '') || Date.now();
            utils.showNotification('កំពុងបង្កើតហ្វាយ PDF...', 'info');
            html2pdf().set({
                margin:      [10, 10, 10, 10],
                filename:    `Receipt-${invoiceNo}.pdf`,
                image:       { type: 'jpeg', quality: 0.98 },
                html2canvas: { scale: 3, useCORS: true, backgroundColor: '#ffffff' },
                jsPDF:       { unit: 'mm', format: [85, 320], orientation: 'portrait' },
            }).from(el).save()
                .then(() => { utils.showNotification('ទាញយកវិក្កយបត្រររួចរាល់!', 'success'); this.closeAllModals(); })
                .catch(() => utils.showNotification('ការទាញយកបរាជ័យ', 'error'));
        },

        bindEvents() {
            this.selectedMethodCode = $('.payment-method-btn.active').data('method') || 'cash';

            $(document).off('click', '.payment-method-btn').on('click', '.payment-method-btn', (e) => {
                const $t = $(e.currentTarget);
                $('.payment-method-btn').removeClass('active');
                $t.addClass('active');
                this.selectedMethodId   = $t.data('id');
                this.selectedMethodCode = $t.data('method') || 'cash';
                this.toggleCashInput();
                this.updateChange();
            });

            $(document).on('input',   '#cash-received', () => this.updateChange());
            $(document).on('keypress','#cash-received', (e) => { if (e.which === 13) this.confirmSale(); });

            $('#process-payment-btn').on('click', () => this.openPaymentModal());
            $('#closePaymentModal, #cancelPaymentBtn').on('click', () => {
                $('#paymentModal').addClass('hidden').css('display', 'none');
            });
            $('#confirmPaymentBtn').on('click', () => this.confirmSale());

            $(document).off('click', '#downloadReceiptBtn').on('click', '#downloadReceiptBtn', () => this.downloadReceiptAsPDF());
            $(document).off('click', '#closeReceiptBtn').on('click',    '#closeReceiptBtn',    () => this.closeAllModals());
        },
    };

    // ─────────────────────────────────────────────────────────────────────────
    // Cash Register Popup
    // ─────────────────────────────────────────────────────────────────────────
    async function openRegisterPopup() {
        const overlay = document.getElementById('register-overlay');
        if (!overlay) return;
        try {
            const res  = await fetch('/cashier/current-shift-details');
            const data = await res.json();
            if (data.success) {
                document.getElementById('cr-total-txn').innerText    = data.total_transactions;
                document.getElementById('cr-total-amount').innerText = utils.formatCurrency(data.total_amount);
                $(overlay).fadeIn();
            }
        } catch (e) { console.error('Register popup error:', e); }
    }
    $(document).ready(() => {
        productManager.init();
        paymentManager.init();
        cartManager.bindEvents();
        cartManager.renderSidebarTotals();
    });

})();
