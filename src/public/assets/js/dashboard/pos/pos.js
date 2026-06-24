(function() {
    'use strict';

    const CONFIG = {
        TAX_RATE: 0.10, // 10%
        LOW_STOCK_THRESHOLD: 10,  // 10 or less is considered low stock
        DEBOUNCE_DELAY: 250 // 250ms debounce for search input
    };

    const state = {
        cart: {},
        products: [],
        activeCategory: 'all',
        searchQuery: '',
        productStock: {},  // Separate stock tracking
        productPagination: null,
        productRequestId: 0
    };

    const utils = {
        formatCurrency(amount) {
            return new Intl.NumberFormat('en-US', {
                style: 'currency',
                currency: 'USD'
            }).format(parseFloat(amount) || 0);
        },

        parseCurrency(str) {
            return parseFloat(str.replace(/[^0-9.-]+/g, '')) || 0;
        },

        escapeHtml(value) {
            return String(value ?? '').replace(/[&<>"']/g, char => ({
                '&': '&amp;',
                '<': '&lt;',
                '>': '&gt;',
                '"': '&quot;',
                "'": '&#039;'
            }[char]));
        },

        productImageUrl(path) {
            if (!path) return window.POS_ASSETS?.placeholder || '/assets/images/not-product.png';
            if (/^https?:\/\//i.test(path)) return path;

            const storageBase = (window.POS_ASSETS?.storageBase || '/storage').replace(/\/$/, '');
            return `${storageBase}/${String(path).replace(/^\/+/, '')}`;
        },

        debounce(func, wait = CONFIG.DEBOUNCE_DELAY) {
            let timeout;
            return function executedFunction(...args) {
                clearTimeout(timeout);
                timeout = setTimeout(() => func.apply(this, args), wait);
            };
        },

        showNotification(message, type = 'info') {
            const colors = {
                success: '#4ade80',
                warning: '#fbbf24',
                error: '#f87171',
                info: '#60a5fa'
            };

            // Log to console
            console.log(`%c[${type.toUpperCase()}] ${message}`, `color: ${colors[type] || '#fff'}; font-weight: bold;`);

            // Create toast notification
            const toast = document.createElement('div');
            toast.className = `notification notification--${type}`;
            toast.style.cssText = `
                position: fixed;
                bottom: 20px;
                right: 20px;
                background: ${colors[type]};
                color: white;
                padding: 12px 16px;
                border-radius: 4px;
                z-index: 9999;
                font-size: 14px;
                font-weight: 500;
                box-shadow: 0 2px 8px rgba(0,0,0,0.2);
                animation: slideIn 0.3s ease-out;
            `;
            toast.textContent = message;
            document.body.appendChild(toast);

            setTimeout(() => {
                toast.style.animation = 'slideOut 0.3s ease-out';
                setTimeout(() => toast.remove(), 300);
            }, 3000);
        }
    };

    const productManager = {
        init() {
            this.bindEvents();
            this.loadProducts();
        },

        async loadProducts(page = 1) {
            const requestId = ++state.productRequestId;
            const $grid = $('#product-grid');

            this.renderSkeleton();

            try {
                const url = new URL(window.ROUTES.posProducts, window.location.origin);
                url.searchParams.set('page', page);
                url.searchParams.set('per_page', 30);
                url.searchParams.set('simple', 1);

                if (state.activeCategory !== 'all') {
                    url.searchParams.set('category_code', state.activeCategory);
                }

                if (state.searchQuery.trim()) {
                    url.searchParams.set('search', state.searchQuery.trim());
                }

                const response = await fetch(url.toString(), {
                    headers: {
                        'Accept': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest'
                    }
                });

                if (!response.ok) {
                    throw new Error(`Product request failed: ${response.status}`);
                }

                const payload = await response.json();
                const paginator = payload.data || {};

                if (requestId !== state.productRequestId) return;

                state.products = (paginator.data || []).map(item => this.normalizeProduct(item));
                state.productPagination = paginator;

                state.products.forEach(product => {
                    if (state.productStock[product.id] === undefined) {
                        state.productStock[product.id] = product.stock;
                    }
                });

                this.render();
                this.renderPagination();
            } catch (error) {
                console.error(error);
                $grid.html(`
                    <div style="grid-column:1/-1;padding:24px;text-align:center;color:#991b1b;">
                        Unable to load products. Please refresh and try again.
                    </div>
                `);
                $('#product-pagination').empty();
            }
        },

        normalizeProduct(item) {
            const uoms = Array.isArray(item.uoms) ? item.uoms : [];
            const defaultUom = uoms.find(uom => uom.is_default) || uoms[0] || {};

            return {
                id: item.product_code,
                code: item.product_code,
                name: item.product_name,
                category: item.category_code,
                stock: Number(item.stock) || 0,
                minStock: Number(item.min_stock) || 0,
                lowStock: Boolean(item.low_stock),
                image: utils.productImageUrl(item.product_image),
                uoms,
                selectedUomId: defaultUom.id ?? '',
                price: Number(defaultUom.selling_price) || 0,
                costPrice: Number(defaultUom.cost_price) || 0,
                uomCode: defaultUom.uom_code || null,
                uomName: defaultUom.uom_name || defaultUom.uom_code || ''
            };
        },

        renderSkeleton() {
            $('#product-grid').html(Array.from({ length: 12 }, () => '<div class="skeleton"></div>').join(''));
            $('#product-pagination').empty();
        },

        filterProducts() {
            return state.products;
        },

        getProductById(id) {
            return state.products.find(p => p.id === Number(id));
        },

        getAvailableStock(id) {
            return state.productStock[id] || 0;
        },

        getSelectedUom(product) {
            return product.uoms.find(uom => String(uom.id) === String(product.selectedUomId)) || product.uoms[0] || null;
        },

        render() {
            const $grid = $('#product-grid');
            const filtered = this.filterProducts();

            if (!filtered.length) {
                $grid.html(`
                    <div style="grid-column:1/-1;padding:24px;text-align:center;color:#64748b;">
                        No products found.
                    </div>
                `);
                return;
            }

            const html = filtered.map(product => {
                const availableStock = this.getAvailableStock(product.id);
                const isOutOfStock = availableStock === 0;
                const isLowStock = availableStock > 0 && availableStock <= CONFIG.LOW_STOCK_THRESHOLD;
                const selectedUom = this.getSelectedUom(product);
                const price = selectedUom ? Number(selectedUom.selling_price) || 0 : product.price;

                const stockClass = isOutOfStock ? 'stock-out' : isLowStock ? 'stock-low' : 'stock-good';
                const stockText = isOutOfStock ? 'Out of stock' : `${availableStock} available`;
                const uomOptions = product.uoms.map(uom => `
                    <option value="${utils.escapeHtml(uom.id)}" ${String(uom.id) === String(product.selectedUomId) ? 'selected' : ''}>
                        ${utils.escapeHtml(uom.uom_name || uom.uom_code)}
                    </option>
                `).join('');

                return `
                    <div class="product-card ${isOutOfStock ? 'product-card--disabled' : ''}" data-product-id="${product.id}">
                        ${isLowStock ? '<span class="low-stock-badge">LOW</span>' : ''}
                        <div class="product-card__image">
                            <img src="${product.image}" alt="${utils.escapeHtml(product.name)}" loading="lazy" onerror="this.src='${window.POS_ASSETS?.placeholder || '/assets/images/not-product.png'}'">
                        </div>
                        <div class="product-card__body">
                            <span class="product-card__name">${utils.escapeHtml(product.name)}</span>
                            <span class="product-card__price">${utils.formatCurrency(price)}</span>
                            <div class="product-card__stock ${stockClass}">${stockText}</div>
                            <div class="product-card__uom-row">
                                <select class="product-card__uom-select" data-product-id="${product.id}" ${product.uoms.length <= 1 ? 'disabled' : ''}>
                                    ${uomOptions}
                                </select>
                                <button type="button" class="product-card__add-btn" ${isOutOfStock ? 'disabled' : ''}>
                                    <span class="material-symbols-outlined">add</span>
                                </button>
                            </div>
                        </div>
                    </div>`;
            }).join('');

            $grid.html(html);
        },

        renderPagination() {
            const paginator = state.productPagination;
            if (!paginator) return;

            const currentPage = paginator.current_page || 1;
            const hasPrev = Boolean(paginator.prev_page_url);
            const hasNext = Boolean(paginator.next_page_url);

            $('#product-pagination').html(`
                <div style="display:flex;align-items:center;justify-content:center;gap:10px;">
                    <button class="pg-btn" data-page="${currentPage - 1}" ${hasPrev ? '' : 'disabled'}>Prev</button>
                    <span style="font-size:13px;color:#64748b;">Page ${currentPage}</span>
                    <button class="pg-btn" data-page="${currentPage + 1}" ${hasNext ? '' : 'disabled'}>Next</button>
                </div>
            `);
        },

        bindEvents() {
            // Add to cart
            $('#product-grid').on('click', '.product-card:not(.product-card--disabled)', (e) => {
                const id = $(e.currentTarget).data('product-id');
                const product = this.getProductById(id);
                const available = this.getAvailableStock(id);

                if (product && available > 0) {
                    cartManager.addItem(product);
                    state.productStock[id]--;
                    this.render();
                }
            });

            $('#product-grid').on('click', '.product-card__uom-select, .product-card__add-btn', (e) => {
                e.stopPropagation();
            });

            $('#product-grid').on('change', '.product-card__uom-select', (e) => {
                const id = $(e.currentTarget).data('product-id');
                const product = this.getProductById(id);
                if (!product) return;

                product.selectedUomId = $(e.currentTarget).val();
                const selectedUom = this.getSelectedUom(product);
                product.price = Number(selectedUom?.selling_price) || product.price;
                product.costPrice = Number(selectedUom?.cost_price) || product.costPrice;
                product.uomCode = selectedUom?.uom_code || product.uomCode;
                product.uomName = selectedUom?.uom_name || selectedUom?.uom_code || product.uomName;
                this.render();
            });

            $('#product-grid').on('click', '.product-card__add-btn', (e) => {
                const id = $(e.currentTarget).closest('.product-card').data('product-id');
                const product = this.getProductById(id);
                const available = this.getAvailableStock(id);

                if (product && available > 0) {
                    cartManager.addItem(product);
                    state.productStock[id]--;
                    this.render();
                }
            });

            // Category filter
            $('#product-grid').closest('.pos-catalog').find('.pos-catalog__filter-pill').on('click', (e) => {
                $('#product-grid').closest('.pos-catalog').find('.pos-catalog__filter-pill').removeClass('pos-catalog__filter-pill--active');
                $(e.currentTarget).addClass('pos-catalog__filter-pill--active');
                state.activeCategory = $(e.currentTarget).data('category');
                this.loadProducts(1);
            });

            // Search
            $('#product-search').on('input', utils.debounce((e) => {
                state.searchQuery = e.target.value;
                this.loadProducts(1);
            }));

            $('#product-pagination').on('click', '.pg-btn:not(:disabled)', (e) => {
                this.loadProducts(Number($(e.currentTarget).data('page')) || 1);
            });
        }
    };

    const cartManager = {
        init() {
            this.bindEvents();
            this.render();
        },

        addItem(product) {
            if (state.cart[product.id]) {
                state.cart[product.id].quantity++;
            } else {
                state.cart[product.id] = {
                    id: product.id,
                    name: product.name,
                    price: product.price,
                    costPrice: product.costPrice,
                    category: product.category,
                    image: product.image,
                    productCode: product.code,
                    uomCode: product.uomCode,
                    uomName: product.uomName,
                    quantity: 1
                };
            }
            this.render();
        },

        updateQuantity(id, delta) {
            const item = state.cart[id];
            if (!item) return;

            const availableStock = productManager.getAvailableStock(id);
            const currentInCart = item.quantity;
            const totalAvailable = availableStock + currentInCart;
            const newQty = item.quantity + delta;

            if (newQty <= 0) {
                this.removeItem(id);
                return;
            }

            if (newQty > totalAvailable) {
                utils.showNotification('Not enough stock available!', 'warning');
                return;
            }

            item.quantity = newQty;
            state.productStock[id] -= delta;
            this.render();
            productManager.render();
        },

        removeItem(id) {
            const item = state.cart[id];
            if (item) {
                state.productStock[id] += item.quantity;
                delete state.cart[id];
            }
            this.render();
            productManager.render();
        },

        clear(completeSale = false) {
            if (!completeSale) {
                Object.values(state.cart).forEach(item => {
                    state.productStock[item.id] += item.quantity;
                });
            }
            state.cart = {};
            this.render();
            productManager.render();
        },

        getSubtotal() {
            return Object.values(state.cart).reduce((sum, item) => sum + item.price * item.quantity, 0);
        },

        getTax() {
            return this.getSubtotal() * CONFIG.TAX_RATE;
        },

        getTotal() {
            return this.getSubtotal() + this.getTax();
        },

        isEmpty() {
            return Object.keys(state.cart).length === 0;
        },

        render() {
            const $empty = $('#cart-empty-state');
            const $list = $('#cart-list');
            const $btn = $('#process-payment-btn');

            if (this.isEmpty()) {
                $empty.show();
                $list.empty();
                $btn.prop('disabled', true);
                this.updateSummary(0, 0, 0);
                $('#cart-item-count').text('0 items');
                return;
            }

            $empty.hide();
            $btn.prop('disabled', false);

            let itemCount = 0;
            const html = Object.values(state.cart).map(item => {
                itemCount += item.quantity;
                return this.createCartItemHTML(item);
            }).join('');

            $list.html(html);
            $('#cart-item-count').text(`${itemCount} item${itemCount > 1 ? 's' : ''}`);
            this.updateSummary(this.getSubtotal(), this.getTax(), this.getTotal());
        },

        createCartItemHTML(item) {
            return `
                <li class="pos-sidebar__cart-item">
                    <div class="pos-sidebar__cart-item-info">
                        <span class="pos-sidebar__cart-item-name">${item.name}</span>
                        <div class="cart-item__qty-controls">
                            <button class="btn-qty btn-qty-minus" data-id="${item.id}">-</button>
                            <span class="pos-sidebar__cart-controls">${item.quantity}</span>
                            <button class="btn-qty btn-qty-plus" data-id="${item.id}">+</button>
                            <span class="pos-sidebar__cart-item-price">@ ${utils.formatCurrency(item.price)}</span>
                        </div>
                    </div>
                    <div style="display:flex; align-items:center; gap:12px;">
                        <span>${utils.formatCurrency(item.price * item.quantity)}</span>
                        <button class="cart-item__remove" data-id="${item.id}">&times;</button>
                    </div>
                </li>`;
        },

        updateSummary(subtotal, tax, total) {
            $('#receipt-subtotal').text(utils.formatCurrency(subtotal));
            $('#receipt-tax').text(utils.formatCurrency(tax));
            $('#receipt-total').text(utils.formatCurrency(total));
        },

        bindEvents() {
            $(document).on('click', '.cart-item__remove', (e) => {
                const id = $(e.currentTarget).data('id');
                if (confirm('Remove this item from cart?')) {
                    this.removeItem(id);
                }
            });
            $(document).on('click', '.btn-qty-plus', (e) => this.updateQuantity($(e.currentTarget).data('id'), 1));
            $(document).on('click', '.btn-qty-minus', (e) => this.updateQuantity($(e.currentTarget).data('id'), -1));
        }
    };

    const paymentManager = {
        init() {
            this.bindEvents();
        },

        openPaymentModal() {
            if (cartManager.isEmpty()) {
                utils.showNotification("Cart is empty!", "warning");
                return;
            }

            const subtotal = cartManager.getSubtotal();
            const tax = cartManager.getTax();
            const total = cartManager.getTotal();

            $('#modal-subtotal').text(utils.formatCurrency(subtotal));
            $('#modal-tax').text(utils.formatCurrency(tax));
            $('#modal-total').text(utils.formatCurrency(total));
            $('#modal-amount-due').text(utils.formatCurrency(total));
            $('#cash-received').val(total.toFixed(2));

            const itemsHtml = Object.values(state.cart).map(item => `
                <div class="receipt-item">
                    <div>${item.name} × ${item.quantity}</div>
                    <div>${utils.formatCurrency(item.price * item.quantity)}</div>
                </div>
            `).join('');

            $('#modal-cart-items').html(itemsHtml);
            $('#paymentModal').fadeIn(200).addClass('show');

            // Focus on cash input
            setTimeout(() => $('#cash-received').focus(), 200);
        },

        validatePayment() {
            const cashStr = $('#cash-received').val();
            const cashReceived = utils.parseCurrency(cashStr);
            const total = cartManager.getTotal();

            // Validation checks
            if (!cashStr || cashStr.trim() === '') {
                utils.showNotification('Please enter a cash amount', 'error');
                return null;
            }

            if (isNaN(cashReceived) || cashReceived < 0) {
                utils.showNotification('Please enter a valid amount', 'error');
                return null;
            }

            if (cashReceived < total) {
                const shortage = total - cashReceived;
                utils.showNotification(
                    `Insufficient payment. Need $${shortage.toFixed(2)} more`,
                    'warning'
                );
                return null;
            }

            return {
                cashReceived: cashReceived,
                change: cashReceived - total
            };
        },

        showReceiptModal() {
            const paymentData = this.validatePayment();

            if (!paymentData) {
                return;
            }

            const total = cartManager.getTotal();
            const now = new Date();
            const invoiceNo = `#RCP-${String(Math.floor(100000 + Math.random() * 900000))}`;

            $('#receipt-date').text(
                now.toLocaleDateString('en-US', { weekday: 'short', month: 'long', day: 'numeric', year: 'numeric' }) +
                ' • ' + now.toLocaleTimeString('en-US', { hour: '2-digit', minute: '2-digit' })
            );

            $('#receipt-invoice').text(invoiceNo);

            const itemsHtml = Object.values(state.cart).map(item => `
                <div class="receipt-item">
                    <div>${item.name} × ${item.quantity}</div>
                    <div>${utils.formatCurrency(item.price * item.quantity)}</div>
                </div>
            `).join('');

            $('#receipt-items').html(itemsHtml);
            $('#r-subtotal').text(utils.formatCurrency(cartManager.getSubtotal()));
            $('#r-tax').text(utils.formatCurrency(cartManager.getTax()));
            $('#r-total').text(utils.formatCurrency(total));
            $('#r-cash-received').text(utils.formatCurrency(paymentData.cashReceived));
            $('#r-change').text(utils.formatCurrency(paymentData.change));

            $('#paymentModal').hide();
            $('#receiptModal').removeClass('hidden').fadeIn(200).css('display', 'flex');

            $('#receiptActions').show();
        },

        closeAllModals() {

            // if (!confirm('Finish transaction and clear cart?')) {
            //     return;
            // }

            $('#paymentModal').fadeOut(200).removeClass('show');
            $('#receiptModal').fadeOut(200).removeClass('show');
            cartManager.clear(true);
            utils.showNotification('Transaction completed successfully!', 'success');
        },

        downloadReceiptAsPDF() {
            // Check if html2pdf library is loaded
            if (typeof html2pdf === 'undefined') {
                utils.showNotification('PDF library not loaded', 'error');
                return;
            }

            const receiptElement = document.querySelector('.receipt-paper');
            if (!receiptElement) {
                utils.showNotification("Receipt element not found!", "error");
                return;
            }

            const invoiceNo = $('#receipt-invoice').text().trim().replace('#', '') || Date.now();

            const opt = {
                margin: [10, 10, 10, 10],
                filename: `Receipt-${invoiceNo}.pdf`,
                image: { type: 'jpeg', quality: 0.98 },
                html2canvas: {
                    scale: 3,
                    useCORS: true,
                    backgroundColor: '#ffffff'
                },
                jsPDF: {
                    unit: 'mm',
                    format: [85, 320],
                    orientation: 'portrait'
                }
            };

            html2pdf()
                .set(opt)  // Set options before passing the element
                .from(receiptElement) // Pass the element
                .save() // Trigger the download
                .then(() => {
                    utils.showNotification("Receipt downloaded successfully!", "success");
                    this.closeAllModals();
                })
                .catch(err => {
                    console.error('PDF generation failed:', err);
                    utils.showNotification("Failed to download receipt", "error");
                });
        },

        bindEvents() {
            $('#process-payment-btn').on('click', () => this.openPaymentModal());
            $('#closePaymentModal, #cancelPaymentBtn').on('click', () => {
                $('#paymentModal').fadeOut(200).removeClass('show');
            });

            $('#confirmPaymentBtn').on('click', () => this.showReceiptModal());

            // Allow Enter key to confirm payment
            $(document).on('keypress', '#cash-received', (e) => {
                if (e.which === 13) { // Enter key
                    this.showReceiptModal();
                }
            });

            $(document).on('click', '#downloadReceiptBtn', () => {
                this.downloadReceiptAsPDF();
            });

            $(document).on('click', '#closeReceiptBtn', () => {
                this.closeAllModals();
            });
        }
    };

    // Add CSS animations
    const style = document.createElement('style');
    style.textContent = `
        @keyframes slideIn {
            from {
                transform: translateX(400px);
                opacity: 0;
            }
            to {
                transform: translateX(0);
                opacity: 1;
            }
        }

        @keyframes slideOut {
            from {
                transform: translateX(0);
                opacity: 1;
            }
            to {
                transform: translateX(400px);
                opacity: 0;
            }
        }

        .product-card--disabled {
            opacity: 0.5;
            cursor: not-allowed;
            pointer-events: none;
        }
    `;
    document.head.appendChild(style);

    // Initialize the app
    $(function() {
        productManager.init();
        cartManager.init();
        paymentManager.init();
    });

})();
