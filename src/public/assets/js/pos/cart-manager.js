/**
 * cart-manager.js
 * Manages the shopping cart: add/remove/update items, UOM switching, totals.
 * Depends on: state.js, utils.js, product-manager.js
 */
(function () {
    'use strict';

    window.POS = window.POS || {};
    const state          = window.POS.state;
    const utils           = window.POS.utils;
    const productManager   = window.POS.productManager;

    const cartManager = {
        // Shared helper — replaces the same reduce() that used to be duplicated
        // in addItem/updateQty/changeUom/getAvailableStock.
        // Returns total quantity (in stock units) already committed to `productCode`,
        // optionally excluding one cart line (used when that line is the one being changed).
        quantityInCartFor(productCode, excludeId = null) {
            return state.cart.reduce((sum, i) =>
                i.product_code === productCode && i.id !== excludeId
                    ? sum + i.quantity * i.uom_qty_per_unit
                    : sum
            , 0);
        },

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
            const used = this.quantityInCartFor(product.product_code);
            if (uom.quantity_per_unit > product.stock - used) {
                utils.notify('Not enough stock', 'error');
                return;
            }
            const price = parseFloat(uom.selling_price) || product.price;
            state.cart.push({
                id,
                product_code:     product.product_code,
                name:             product.name,
                price,
                quantity:         1,
                uom_code:         uom.uom_code,
                uom_name:         uom.uom_name,
                uom_qty_per_unit: uom.quantity_per_unit,
                subtotal:         price,
                cost_price:       product.cost_price || 0,
            });
            this.renderCart();
            this.renderTotals();
        },

        updateQty(id, qty) {
            const item = state.cart.find(i => i.id === id);
            if (!item) return;
            if (qty <= 0) return this.remove(id);

            const product  = productManager.getByCode(item.product_code);
            const usedElse = this.quantityInCartFor(item.product_code, id);

            if (qty * item.uom_qty_per_unit > product.stock - usedElse) {
                utils.notify('Not enough stock', 'warning');
                return;
            }
            item.quantity = qty;
            item.subtotal = item.price * qty;
            const $el = $(`#cart-list [data-id="${id}"]`);
            $el.find('.qty').text(item.quantity);
            $el.find('.price').text(utils.formatCurrency(item.subtotal));
            this.renderTotals();
        },

        remove(id) {
            state.cart = state.cart.filter(i => i.id !== id);
            this.renderCart();
            this.renderTotals();
            // FIX: guard against customer-display.js not being loaded yet (or failing to
            // load) so a missing/undefined hook never crashes the cart flow.
            if (typeof window.updateCustomerScreen === 'function') {
                window.updateCustomerScreen();
            }
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
            const tax      = subtotal * 0.1; // TODO: move tax rate to server-side config
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
                const imageUrl = product?.image || window.POS_ASSETS.placeholder;

                const uomControl = hasMulti
                    ? `<select class="cart-uom-select" data-id="${item.id}">
                        ${uoms.map(u => `
                            <option value="${u.uom_code}" ${u.uom_code === item.uom_code ? 'selected' : ''}>
                                ${utils.escapeHtml(u.uom_name || u.uom_code)}
                            </option>
                        `).join('')}
                    </select>`
                    // FIX: was referencing an undefined `u` here (only defined inside the
                    // .map(u => ...) above). Single-UOM products crashed renderCart().
                    // Use the cart item's own uom_name/uom_code instead.
                    : `<span class="cart-uom-label">${utils.escapeHtml(item.uom_name || item.uom_code)}</span>`;

                return `
                    <li class="cart-item" data-id="${item.id}" data-code="${utils.escapeHtml(item.product_code)}">
                        <img class="cart-item__img"
                            src="${imageUrl}"
                            alt="${utils.escapeHtml(item.name)}"
                            onerror="this.src='${window.POS_ASSETS.placeholder}'">
                        <div class="cart-item__content">
                            <div class="cart-item__top">
                                <span class="cart-item__name">${utils.escapeHtml(item.name)}</span>
                                <button class="cart-remove" data-id="${item.id}" title="Remove">✕</button>
                            </div>
                            <div class="cart-item__bottom">
                                <div class="cart-item__controls">
                                    <button class="qty-btn minus" data-id="${item.id}">−</button>
                                    <span class="qty">${item.quantity}</span>
                                    <button class="qty-btn plus" data-id="${item.id}">+</button>
                                    ${uomControl}
                                </div>
                                <span class="price">${utils.formatCurrency(item.subtotal)}</span>
                            </div>
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
            if (!newUom || newUom.uom_code === item.uom_code) return;

            let newPrice = 0;

            if (newUom.selling_price != null && parseFloat(newUom.selling_price) > 0) {
                const unitPrice = parseFloat(newUom.selling_price);
                newPrice = unitPrice * newUom.quantity_per_unit;
            } else {
                const baseUom = product.uoms.find(u => u.is_default);
                if (!baseUom) {
                    utils.notify('System Error: No base unit defined.', 'error');
                    return;
                }
                const unitPrice = parseFloat(baseUom.selling_price || 0);
                newPrice = Math.round((unitPrice * newUom.quantity_per_unit) * 100) / 100;
            }

            const newId    = `${product.product_code}-${newUomCode}`;
            const existing = state.cart.find(i => i.id === newId);

            if (existing) {
                state.cart = state.cart.filter(i => i.id !== oldId);
                this.updateQty(newId, existing.quantity + item.quantity);
                this.renderCart();
                this.renderTotals();
                return;
            }

            const usedElse = this.quantityInCartFor(product.product_code, oldId);

            if ((item.quantity * newUom.quantity_per_unit) > (product.stock - usedElse)) {
                utils.notify('Not enough stock for this UOM', 'warning');
                $(`#cart-list [data-id="${oldId}"] .cart-uom-select`).val(item.uom_code);
                return;
            }

            item.id               = newId;
            item.uom_code         = newUomCode;
            item.uom_name         = newUom.uom_name;
            item.uom_qty_per_unit = newUom.quantity_per_unit;
            item.price            = newPrice;
            item.subtotal         = Math.round((newPrice * item.quantity) * 100) / 100;

            this.renderCart();
            this.renderTotals();
        },

        renderTotals() {
            const { subtotal, discount, tax, total } = this.computeTotals();
            const hasItems = state.cart.length > 0;
            $('#receipt-subtotal').text(utils.formatCurrency(subtotal));
            $('#receipt-discount').text(utils.formatCurrency(discount));
            $('#receipt-tax').text(utils.formatCurrency(tax));
            $('#receipt-total').text(utils.formatCurrency(total));
            $('#cart-item-count').text(`${state.cart.length} item`);
            $('#process-payment-btn')
                .prop('disabled', !hasItems)
                .css({ opacity: hasItems ? 1 : 0.5, cursor: hasItems ? 'pointer' : 'not-allowed' });
            // FIX: guard against customer-display.js not being loaded yet (or failing to
            // load) so a missing/undefined hook never crashes the cart flow. This was the
            // direct cause of "window.updateCustomerScreen is not a function" breaking
            // addDefault → addItem → renderTotals.
            if (typeof window.updateCustomerScreen === 'function') {
                window.updateCustomerScreen();
            }
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
                    const oldId      = $(e.currentTarget).data('id');
                    const newUomCode = $(e.currentTarget).val();
                    this.changeUom(oldId, newUomCode);
                });
        },
    };

    window.POS.cartManager = cartManager;
})();
