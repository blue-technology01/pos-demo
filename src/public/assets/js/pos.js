(function () {
    'use strict';

    // ─────────────────────────────────────────────
    // STATE
    // ─────────────────────────────────────────────
    const state = {
        products:      [],
        cart:          [],
        searchQuery:   '',
        activeCategory:'all',
        isLoading:     false,
        pagination: {
            current_page: 1,
            last_page:    1,
            per_page:     20,
        },
    };

    // ─────────────────────────────────────────────
    // UTILS
    // ─────────────────────────────────────────────
    const utils = {
        formatCurrency(amount) {
            return new Intl.NumberFormat('en-US', {
                style:    'currency',
                currency: 'USD',
            }).format(parseFloat(amount) || 0);
        },

        debounce(fn, delay = 250) {
            let timer;
            return (...args) => {
                clearTimeout(timer);
                timer = setTimeout(() => fn.apply(this, args), delay);
            };
        },

        notify(msg, type = 'info') {
            const colors = {
                success: '#22c55e',
                error:   '#ef4444',
                warning: '#f59e0b',
                info:    '#3b82f6',
            };
            const el = Object.assign(document.createElement('div'), {
                textContent: msg,
            });
            Object.assign(el.style, {
                position:     'fixed',
                bottom:       '20px',
                right:        '20px',
                background:   colors[type] || colors.info,
                color:        '#fff',
                padding:      '10px 14px',
                borderRadius: '6px',
                fontSize:     '13px',
                zIndex:       '99999',
                boxShadow:    '0 2px 8px rgba(0,0,0,0.15)',
            });
            document.body.appendChild(el);
            setTimeout(() => el.remove(), 2500);
        },

        // Alias used by paymentManager
        showNotification(msg, type) {
            this.notify(msg, type);
        },

        async fetchJson(url, options = {}) {
            const { headers: extraHeaders, ...restOptions } = options;
            const res = await fetch(url, {
                ...restOptions,
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': window.CSRF_TOKEN,
                    'Accept':       'application/json',
                    ...extraHeaders,
                },
            });
            if (!res.ok) {
                const err = await res.json().catch(() => ({}));
                throw new Error(err.message || `HTTP ${res.status}`);
            }
            return res.json();
        },
    };

    // ─────────────────────────────────────────────
    // STOCK HELPER
    // ─────────────────────────────────────────────
    function getAvailableStock(productCode) {
        const product = productManager.getByCode(productCode);
        if (!product) return 0;

        const used = state.cart.reduce((sum, item) =>
            item.product_code === productCode
                ? sum + item.quantity * item.uom_qty_per_unit
                : sum
        , 0);

        return product.stock - used;
    }

    // ─────────────────────────────────────────────
    // PRODUCT MANAGER
    // ─────────────────────────────────────────────
    const productManager = {
        init() {
            this.bindSearch();
            this.bindCategoryFilter();
            this.bindGrid();
            this.fetchProducts();
        },

        buildUrl(page = 1) {
            const params = new URLSearchParams({
                page,
                per_page: state.pagination.per_page,
            });
            if (state.searchQuery)              params.set('search',   state.searchQuery);
            if (state.activeCategory !== 'all') params.set('category', state.activeCategory);
            return `${window.ROUTES.posProducts}?${params}`;
        },

        async fetchProducts(page = 1) {
            if (state.isLoading) return;
            state.isLoading = true;

            try {
                const res       = await fetch(this.buildUrl(page)).then(r => r.json());
                const paginator = res.data;

                state.products = (paginator.data || []).map(p => {
                    const basePrice = parseFloat(
                        p.selling_price ?? p.unit_price ?? p.price ?? 0
                    ) || 0;
                    console.log('UOM sample:', paginator.data?.[0]?.uoms);
                    return {
                        product_code:  p.product_code,
                        name:          p.product_name,
                        image:         p.product_image
                            ? `${window.POS_ASSETS.storageBase}/${p.product_image}`
                            : window.POS_ASSETS.placeholder,
                        stock:         parseFloat(p.stock)      || 0,
                        price:         basePrice,
                        cost_price:    parseFloat(p.cost_price) || 0,
                        category_code: p.category_code          || null,
                        uoms: (p.uoms || []).map(u => ({
                            uom_code:         u.uom_code || u.code,
                            uom_name:         u.uom_name || u.name || u.uom_code || u.code,
                            quantity_per_unit: parseFloat(u.quantity_per_unit ?? u.qty_per_unit ?? 1),
                            selling_price:    parseFloat(u.selling_price ?? u.unit_price ?? u.price ?? basePrice) || basePrice,
                            is_default:       !!(u.is_default || u.default),
                        })),
                    };
                });

                state.pagination.current_page = paginator.current_page;
                state.pagination.last_page    = paginator.last_page;

                this.renderGrid();
                this.renderPagination();

            } catch (err) {
                console.error('fetchProducts error:', err);
                utils.notify('Failed to load products', 'error');
            } finally {
                state.isLoading = false;
            }
        },

        getByCode(code) {
            const normalized = (code || '').trim().toUpperCase();
            return state.products.find(p =>
                (p.product_code || '').trim().toUpperCase() === normalized
            ) || null;
        },

        renderGrid() {
            const $grid = $('#product-grid');

            if (!state.products.length) {
                $grid.html('<div style="padding:20px;color:#64748b;">No products found.</div>');
                return;
            }

            const html = state.products.map(p => {
                const stock      = getAvailableStock(p.product_code);
                const isDisabled = stock <= 0;
                const stockClass = stock > 10 ? 'stock-good' : stock > 0 ? 'stock-low' : 'stock-out';
                const stockLabel = stock > 0 ? `${stock} left` : 'Out of stock';

                return `
                    <div class="product-card ${isDisabled ? 'product-card--disabled' : ''}"
                         data-code="${p.product_code}">
                        <div class="product-card__image">
                            <img src="${p.image}" alt="${p.name}" loading="lazy"
                                 onerror="this.src='${window.POS_ASSETS.placeholder}'">
                        </div>
                        <div class="product-card__body">
                            <div class="product-card__name">${p.name}</div>
                            <div class="product-card__price">${utils.formatCurrency(p.price)}</div>
                            <div class="product-card__uom-row">
                                <button class="product-card__add-btn"
                                        data-code="${p.product_code}"
                                        ${isDisabled ? 'disabled' : ''}
                                        title="Add to cart">
                                    <span class="material-symbols-outlined">add</span>
                                </button>
                                <span class="product-card__stock ${stockClass}">${stockLabel}</span>
                            </div>
                        </div>
                    </div>
                `;
            }).join('');

            $grid.html(html);
        },

        renderPagination() {
            const { current_page, last_page } = state.pagination;
            $('#product-pagination').html(`
                <div style="display:flex;align-items:center;gap:8px;">
                    <button class="pg-btn" id="pg-prev" ${current_page <= 1 ? 'disabled' : ''}>← Prev</button>
                    <span style="font-size:13px;color:#64748b;">Page ${current_page} / ${last_page}</span>
                    <button class="pg-btn" id="pg-next" ${current_page >= last_page ? 'disabled' : ''}>Next →</button>
                </div>
            `);

            $('#pg-prev').on('click', () => this.fetchProducts(current_page - 1));
            $('#pg-next').on('click', () => this.fetchProducts(current_page + 1));
        },

        bindGrid() {
            $('#product-grid')
                .off('click.addBtn')
                .on('click.addBtn', '.product-card__add-btn:not([disabled])', (e) => {
                    e.stopPropagation();
                    if (state.isLoading) { utils.notify('Loading products…', 'warning'); return; }
                    const code = $(e.currentTarget).data('code');
                    cartManager.addDefault(code);
                });
        },

        bindSearch() {
            $('#product-search').on('input', utils.debounce((e) => {
                state.searchQuery = e.target.value.trim();
                this.fetchProducts(1);
            }, 300));
        },

        // FIX 1: corrected selector from `.category-btn` → `.pos-catalog__filter-pill`
        bindCategoryFilter() {
            $(document).on('click', '.pos-catalog__filter-pill', (e) => {
                const $pill = $(e.currentTarget);
                $('.pos-catalog__filter-pill').removeClass('pos-catalog__filter-pill--active');
                $pill.addClass('pos-catalog__filter-pill--active');
                state.activeCategory = $pill.data('category');
                this.fetchProducts(1);
            });
        },
    };

    // ─────────────────────────────────────────────
    // CART MANAGER
    // ─────────────────────────────────────────────
    const cartManager = {
        addDefault(code) {
            const product = productManager.getByCode(code);
            if (!product) {
                utils.notify('Product not found', 'error');
                return;
            }

            const uom = product.uoms?.find(u => u.is_default) || product.uoms?.[0];

            if (!uom) {
                utils.notify(`No UOM configured for: ${product.name}`, 'error');
                return;
            }

            this.addItem(product, uom);
        },

        addItem(product, uom) {
            const id       = `${product.product_code}-${uom.uom_code}`;
            const existing = state.cart.find(i => i.id === id);

            if (existing) {
                return this.updateQty(id, existing.quantity + 1);
            }

            const used = state.cart.reduce((sum, i) =>
                i.product_code === product.product_code
                    ? sum + i.quantity * i.uom_qty_per_unit
                    : sum
            , 0);

            if (uom.quantity_per_unit > product.stock - used) {
                utils.notify('Not enough stock', 'error');
                return;
            }

            const price = parseFloat(uom.selling_price) || product.price;

            state.cart.push({
                id,
                product_code:    product.product_code,
                name:            product.name,
                price,
                quantity:        1,
                uom_code:        uom.uom_code,
                uom_name:        uom.uom_name,
                uom_qty_per_unit:uom.quantity_per_unit,
                subtotal:        price,
                cost_price:      product.cost_price || 0,
            });

            this.renderCart();
            this.renderTotals();
        },

        updateQty(id, qty) {
            const item = state.cart.find(i => i.id === id);
            if (!item) return;

            if (qty <= 0) return this.remove(id);

            const product  = productManager.getByCode(item.product_code);
            const usedElse = state.cart.reduce((sum, i) =>
                i.product_code === item.product_code && i.id !== id
                    ? sum + i.quantity * i.uom_qty_per_unit
                    : sum
            , 0);

            if (qty * item.uom_qty_per_unit > product.stock - usedElse) {
                utils.notify('Not enough stock', 'warning');
                return;
            }

            item.quantity = qty;
            item.subtotal = item.price * qty;

            // Patch DOM in-place — avoid full re-render
            const $el = $(`#cart-list [data-id="${id}"]`);
            $el.find('.qty').text(item.quantity);
            $el.find('.price').text(utils.formatCurrency(item.subtotal));

            this.renderTotals();
        },

        remove(id) {
            state.cart = state.cart.filter(i => i.id !== id);
            this.renderCart();
            this.renderTotals();
        },

        clear(silent = false) {
            state.cart = [];
            if (!silent) {
                this.renderCart();
                this.renderTotals();
            }
        },

        isEmpty() {
            return state.cart.length === 0;
        },

        computeTotals() {
            const subtotal = state.cart.reduce((sum, i) => sum + i.price * i.quantity, 0);
            const discount = 0;
            const tax      = subtotal * 0.1;
            const total    = subtotal + tax - discount;
            return { subtotal, discount, tax, total };
        },

        renderCart() {
            const $list  = $('#cart-list');
            const $empty = $('#cart-empty-state');

            if (!state.cart.length) {
                $list.html('');
                $empty.show();
                return;
            }

            $empty.hide();

            const html = state.cart.map(item => {
                const product  = productManager.getByCode(item.product_code);
                const uoms     = product?.uoms || [];
                const hasMulti = uoms.length > 1;

                const uomControl = hasMulti
                    ? `<select class="cart-uom-select" data-id="${item.id}"
                               style="font-size:11px;padding:2px 6px;border:1px solid #cbd5e1;
                                      border-radius:5px;background:#f8fafc;color:#475569;
                                      cursor:pointer;max-width:90px;height:24px;">
                           ${uoms.map(u => `
                               <option value="${u.uom_code}" ${u.uom_code === item.uom_code ? 'selected' : ''}>
                                   ${u.uom_name || u.uom_code}
                               </option>
                           `).join('')}
                       </select>`
                    : `<span style="font-size:11px;color:#64748b;">${item.uom_name || item.uom_code}</span>`;

                return `
                    <li class="cart-item" data-id="${item.id}"
                        style="background:#fff;border:1px solid #e2e8f0;border-radius:8px;padding:10px 12px;">
                        <div style="display:flex;justify-content:space-between;align-items:flex-start;gap:8px;">
                            <span style="font-size:13px;font-weight:600;color:#1e293b;flex:1;line-height:1.3;">
                                ${item.name}
                            </span>
                            <button class="cart-remove" data-id="${item.id}"
                                    style="background:none;border:none;cursor:pointer;color:#94a3b8;
                                           font-size:16px;padding:0;line-height:1;"
                                    title="Remove">✕</button>
                        </div>
                        <div style="display:flex;align-items:center;justify-content:space-between;margin-top:6px;gap:4px;">
                            <div style="display:flex;align-items:center;gap:5px;flex-wrap:wrap;">
                                <button class="minus" data-id="${item.id}"
                                        style="width:24px;height:24px;border:1px solid #cbd5e1;border-radius:5px;
                                               background:#f8fafc;cursor:pointer;font-size:14px;font-weight:700;
                                               display:flex;align-items:center;justify-content:center;">−</button>
                                <span class="qty"
                                      style="min-width:20px;text-align:center;font-size:13px;font-weight:600;">
                                    ${item.quantity}
                                </span>
                                <button class="plus" data-id="${item.id}"
                                        style="width:24px;height:24px;border:none;border-radius:5px;
                                               background:#2563eb;color:#fff;cursor:pointer;font-size:14px;font-weight:700;
                                               display:flex;align-items:center;justify-content:center;">+</button>
                                ${uomControl}
                            </div>
                            <span class="price"
                                  style="font-size:13px;font-weight:700;color:#2563eb;white-space:nowrap;">
                                ${utils.formatCurrency(item.subtotal)}
                            </span>
                        </div>
                    </li>
                `;
            }).join('');

            $list.html(html);
        },

        changeUom(oldId, newUomCode) {
            const item = state.cart.find(i => i.id === oldId);
            if (!item) return;

            const product = productManager.getByCode(item.product_code);
            if (!product) return;

            const newUom = product.uoms.find(u => u.uom_code === newUomCode);
            if (!newUom || newUomCode === item.uom_code) return;

            const newId    = `${product.product_code}-${newUomCode}`;
            const existing = state.cart.find(i => i.id === newId);

            // Merge into existing row if same UOM already in cart
            if (existing) {
                state.cart = state.cart.filter(i => i.id !== oldId);
                this.updateQty(newId, existing.quantity + item.quantity);
                return;
            }

            // Stock check with new UOM multiplier
            const usedElse = state.cart.reduce((sum, i) =>
                i.product_code === product.product_code && i.id !== oldId
                    ? sum + i.quantity * i.uom_qty_per_unit
                    : sum
            , 0);

            if (item.quantity * newUom.quantity_per_unit > product.stock - usedElse) {
                utils.notify('Not enough stock for this UOM', 'warning');
                $(`#cart-list [data-id="${oldId}"] .cart-uom-select`).val(item.uom_code);
                return;
            }

            // Swap entry in-place
            const price           = parseFloat(newUom.selling_price) || product.price;
            item.id               = newId;
            item.uom_code         = newUom.uom_code;
            item.uom_name         = newUom.uom_name;
            item.uom_qty_per_unit = newUom.quantity_per_unit;
            item.price            = price;
            item.subtotal         = price * item.quantity;

            this.renderCart();
            this.renderTotals();
        },

        // FIX 3: renderTotals targets correct element IDs from the HTML
        renderTotals() {
            const { subtotal, discount, tax, total } = this.computeTotals();
            const hasItems = state.cart.length > 0;

            $('#receipt-subtotal').text(utils.formatCurrency(subtotal));
            $('#receipt-discount').text(utils.formatCurrency(discount));
            $('#receipt-tax').text(utils.formatCurrency(tax));
            $('#receipt-total').text(utils.formatCurrency(total));
            $('#cart-item-count').text(`${state.cart.length} មុខ`);

            $('#process-payment-btn')
                .prop('disabled', !hasItems)
                .css({ opacity: hasItems ? 1 : 0.5, cursor: hasItems ? 'pointer' : 'not-allowed' });
        },

        bindCart() {
            $('#cart-list')
                .off('click.cart change.cart')
                .on('click.cart', '.plus', (e) => {
                    const id   = $(e.currentTarget).data('id');
                    const item = state.cart.find(i => i.id === id);
                    if (item) this.updateQty(id, item.quantity + 1);
                })
                .on('click.cart', '.minus', (e) => {
                    const id   = $(e.currentTarget).data('id');
                    const item = state.cart.find(i => i.id === id);
                    if (item) this.updateQty(id, item.quantity - 1);
                })
                .on('click.cart', '.cart-remove', (e) => {
                    const id = $(e.currentTarget).data('id');
                    this.remove(id);
                })
                .on('change.cart', '.cart-uom-select', (e) => {
                    const oldId     = $(e.currentTarget).data('id');
                    const newUomCode = $(e.currentTarget).val();
                    this.changeUom(oldId, newUomCode);
                });
        },
    };

    // ─────────────────────────────────────────────
    // PAYMENT MANAGER
    // ─────────────────────────────────────────────
    const paymentManager = {
        selectedMethodCode: 'cash',
        selectedMethodId:   null,

        init() {
            this.bindEvents();
        },

        openPaymentModal() {
            if (!window.currentRegisterId) {
                utils.notify('Please open a shift before processing payment!', 'error');
                return;
            }
            if (cartManager.isEmpty()) {
                utils.notify('Cart is empty!', 'warning');
                return;
            }

            const { subtotal, discount, tax, total } = cartManager.computeTotals();

            // Reset payment method to first button
            const $defaultBtn = $('.payment-method-btn').first();
            $('.payment-method-btn').removeClass('active');
            $defaultBtn.addClass('active');
            this.selectedMethodId   = $defaultBtn.data('id');
            this.selectedMethodCode = $defaultBtn.data('method') || 'cash';

            // Populate receipt side
            $('#modal-subtotal').text(utils.formatCurrency(subtotal));
            $('#modal-tax').text(utils.formatCurrency(tax));
            $('#modal-discount').text(`-${utils.formatCurrency(discount)}`);
            $('#modal-total').text(utils.formatCurrency(total));
            $('#modal-amount-due').text(utils.formatCurrency(total));
            $('#cash-received').val(total.toFixed(2));
            $('#modal-customer-name').text(
                document.getElementById('selected-customer-name')?.innerText || 'Walk-in Customer'
            );
            $('#modal-cart-items').html(
                state.cart.map(item => `
                    <div class="receipt-item-row">
                        <span class="receipt-item-row__name">${item.name} (${item.uom_name || item.uom_code})</span>
                        <span class="receipt-item-row__qty">× ${item.quantity}</span>
                        <span class="receipt-item-row__price">${utils.formatCurrency(item.subtotal)}</span>
                    </div>
                `).join('')
            );

            this.updateChange(total);
            this.toggleCashInput();
            $('#paymentModal').removeClass('hidden').css('display', 'flex');
            setTimeout(() => $('#cash-received').focus(), 200);
        },

        updateChange(totalOverride = null) {
            const cash   = parseFloat($('#cash-received').val()) || 0;
            const due    = totalOverride ?? cartManager.computeTotals().total;
            const change = cash - due;

            $('#change-amount')
                .text(change >= 0
                    ? utils.formatCurrency(change)
                    : `-${utils.formatCurrency(Math.abs(change))}`)
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

            if (this.selectedMethodCode !== 'cash') {
                return { cashReceived: total, change: 0 };
            }

            const cash = parseFloat($('#cash-received').val()) || 0;
            if (cash <= 0) {
                utils.notify('Please enter the amount received!', 'error');
                return null;
            }
            if (cash < total) {
                utils.notify(`Insufficient: ${utils.formatCurrency(total - cash)} more needed`, 'warning');
                return null;
            }

            return { cashReceived: cash, change: cash - total };
        },

        async confirmSale() {
            const paymentData = this.validatePayment();
            // [] + [] + [] + [] + [] + [] + []
            if (!paymentData) return;

            const $btn = $('#confirmPaymentBtn');
            $btn.prop('disabled', true).text('Processing…');

            const { subtotal, discount, tax, total } = cartManager.computeTotals();

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

            // Show receipt immediately — cashier can start next sale
            this.showReceiptModal('…', paymentData, { subtotal, discount, tax, total }, cartSnapshot);
            cartManager.clear(true);

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
                    utils.notify(`Sale complete! Invoice: ${data.invoice_no}`, 'success');
                } else {
                    $('#receipt-invoice').text('⚠ Failed');
                    utils.notify(data.message || 'Sale failed.', 'error');
                }
            } catch (err) {
                console.error('confirmSale error:', err);
                $('#receipt-invoice').text('⚠ Error');
                utils.notify(err.message || 'Sale failed.', 'error');
            } finally {
                $btn.prop('disabled', false).text('Confirm & Complete Sale');
            }
        },

        showReceiptModal(invoiceNo, paymentData, totals, cartSnapshot) {
            const now = new Date();
            $('#receipt-date').text(
                now.toLocaleDateString('en-US', {
                    weekday: 'short', month: 'long', day: 'numeric', year: 'numeric',
                }) + ' • ' + now.toLocaleTimeString('en-US', {
                    hour: '2-digit', minute: '2-digit',
                })
            );
            $('#receipt-invoice').text(`#${invoiceNo}`);
            $('#receipt-customer').text(
                document.getElementById('selected-customer-name')?.innerText || 'Walk-in Customer'
            );
            $('#receipt-items').html(
                cartSnapshot.map(item => `
                    <div class="receipt-item">
                        <div>${item.name} (${item.uom_name}) × ${item.quantity}</div>
                        <div>${utils.formatCurrency(item.subtotal)}</div>
                    </div>
                `).join('')
            );

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
            $('#paymentModal, #receiptModal').addClass('hidden').css('display', 'none');
        },

        downloadReceiptAsPDF() {
            if (typeof html2pdf === 'undefined') {
                utils.notify('PDF library not loaded', 'error');
                return;
            }
            const el = document.querySelector('.receipt-paper');
            if (!el) { utils.notify('Receipt element not found!', 'error'); return; }

            const invoiceNo = $('#receipt-invoice').text().trim().replace('#', '') || Date.now();
            utils.notify('Generating PDF…', 'info');

            html2pdf()
                .set({
                    margin:      [10, 10, 10, 10],
                    filename:    `Receipt-${invoiceNo}.pdf`,
                    image:       { type: 'jpeg', quality: 0.98 },
                    html2canvas: { scale: 3, useCORS: true, backgroundColor: '#ffffff' },
                    jsPDF:       { unit: 'mm', format: [85, 320], orientation: 'portrait' },
                })
                .from(el)
                .save()
                .then(() => {
                    utils.notify('Receipt downloaded!', 'success');
                    this.closeAllModals();
                })
                .catch(() => utils.notify('Download failed', 'error'));
        },

        bindEvents() {
            // Payment method toggle
            $(document).off('click.payMethod').on('click.payMethod', '.payment-method-btn', (e) => {
                const $t = $(e.currentTarget);
                $('.payment-method-btn').removeClass('active');
                $t.addClass('active');
                this.selectedMethodId   = $t.data('id');
                this.selectedMethodCode = $t.data('method') || 'cash';
                this.toggleCashInput();
                this.updateChange();
            });

            $(document).on('input',   '#cash-received', ()  => this.updateChange());
            $(document).on('keypress','#cash-received', (e) => { if (e.which === 13) this.confirmSale(); });

            $('#process-payment-btn').on('click', () => this.openPaymentModal());

            $('#closePaymentModal, #cancelPaymentBtn').on('click', () => {
                $('#paymentModal').addClass('hidden').css('display', 'none');
            });

            $('#confirmPaymentBtn').on('click', () => this.confirmSale());

            $(document).off('click.receipt')
                .on('click.receipt', '#downloadReceiptBtn', () => this.downloadReceiptAsPDF())
                .on('click.receipt', '#closeReceiptBtn',    () => this.closeAllModals());
        },
    };

    // ─────────────────────────────────────────────
    // BOOT
    // ─────────────────────────────────────────────
    $(document).ready(() => {
        productManager.init();
        paymentManager.init();
        cartManager.bindCart();
    });

})();
