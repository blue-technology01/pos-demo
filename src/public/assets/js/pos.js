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
        productStock: {}  // Separate stock tracking
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
            this.loadProducts();
            this.bindEvents();
        },

        loadProducts() {
            state.products = [
                { id: 1, name: 'Coca Cola', price: 1.50, category: 'drink', stock: 90, image: 'https://i5.walmartimages.com/seo/Coca-Cola-Soda-Pop-20-fl-oz-Bottle_011c7f32-c0b9-44a2-9c78-7e02019279c8.be7a0cbc7c42ceb7fdb7b70fb8e5832f.jpeg' },
                { id: 2, name: 'Pepsi', price: 1.40, category: 'drink', stock: 75, image: 'https://www.qibahstore.com/cdn/shop/files/332818_main.webp?v=1721580460' },
                { id: 3, name: 'Red Bull', price: 2.50, category: 'drink', stock: 65, image: 'https://www.mystore.in/s/62ea2c599d1398fa16dbae0a/67189b9d44ff040024d311af/red-bull-image.jpg' },
                { id: 4, name: 'Potato Chips', price: 1.20, category: 'snack', stock: 0, image: 'https://jgsj.jayagrocer.com/cdn/shop/files/096693-1-1_39cd9f35-360c-413b-953f-f55cbe0e4e53.jpg?v=1750072333' },
                { id: 5, name: 'Doritos Nacho Cheese', price: 1.80, category: 'snack', stock: 45, image: 'https://m.media-amazon.com/images/I/81vX1z7X9QL.jpg' },
                { id: 6, name: 'Sprite', price: 1.45, category: 'drink', stock: 55, image: 'https://i5.walmartimages.com/asr/0e8e8e8e-0b0e-4b0e-9b0e-0e8e8e8e0b0e_1.0.jpg' },
                { id: 7, name: 'Mountain Dew', price: 1.60, category: 'drink', stock: 40, image: 'https://i5.walmartimages.com/seo/Mountain-Dew-Soda-20-fl-oz-Bottle_0f0f0f0f-0f0f-0f0f-0f0f-0f0f0f0f0f0f_1.jpg' },
                { id: 8, name: "Lay's Classic Chips", price: 1.30, category: 'snack', stock: 80, image: 'https://m.media-amazon.com/images/I/51Xk2chvfuL._AC_SL1500_.jpg' },
                { id: 9, name: 'Pringles Original', price: 2.20, category: 'snack', stock: 30, image: 'https://m.media-amazon.com/images/I/71f7Q5z5ZQL._AC_SL1500_.jpg' },
                { id: 10, name: 'Snickers Bar', price: 1.10, category: 'snack', stock: 120, image: 'https://m.media-amazon.com/images/I/61f2z2z2z2z.jpg' }
            ];

            // Initialize stock tracking
            state.products.forEach(product => {
                state.productStock[product.id] = product.stock;
            });

            this.render();
        },

        filterProducts() {
            const query = state.searchQuery.toLowerCase().trim();
            return state.products.filter(product => {
                const categoryMatch = state.activeCategory === 'all' || product.category === state.activeCategory;
                const searchMatch = !query || product.name.toLowerCase().includes(query);
                return categoryMatch && searchMatch;
            });
        },

        getProductById(id) {
            return state.products.find(p => p.id === Number(id));
        }, 

        getAvailableStock(id) {
            return state.productStock[id] || 0;
        },

        render() {
            const $grid = $('#product-grid');
            const filtered = this.filterProducts();

            const html = filtered.map(product => {
                const availableStock = this.getAvailableStock(product.id);
                const isOutOfStock = availableStock === 0;
                const isLowStock = availableStock > 0 && availableStock <= CONFIG.LOW_STOCK_THRESHOLD;

                const stockClass = isOutOfStock ? 'stock-out' : isLowStock ? 'stock-low' : 'stock-good';
                const stockText = isOutOfStock ? 'Out of stock' : `${availableStock} available`;

                return `
                    <div class="product-card ${isOutOfStock ? 'product-card--disabled' : ''}" data-product-id="${product.id}">
                        <div class="product-card__image">
                            <img src="${product.image}" alt="${product.name}" loading="lazy">
                        </div>
                        <div class="product-card__info">
                            <span class="product-card__name">${product.name}</span>
                            <span class="product-card__price">${utils.formatCurrency(product.price)}</span>
                            <div class="product-card__stock ${stockClass}">${stockText}</div>
                        </div>
                    </div>`;
            }).join('');

            $grid.html(html);
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

            // Category filter
            $('#product-grid').closest('.pos-catalog').find('.pos-catalog__filter-pill').on('click', (e) => {
                $('#product-grid').closest('.pos-catalog').find('.pos-catalog__filter-pill').removeClass('pos-catalog__filter-pill--active');
                $(e.currentTarget).addClass('pos-catalog__filter-pill--active');
                state.activeCategory = $(e.currentTarget).data('category');
                this.render();
            });

            // Search
            $('#product-search').on('input', utils.debounce((e) => {
                state.searchQuery = e.target.value;
                this.render();
            }));
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
                    category: product.category,
                    image: product.image,
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
