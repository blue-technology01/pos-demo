(function() {
    'use strict';

    const CONFIG = {
        TAX_RATE: 0.10,
        LOW_STOCK_THRESHOLD: 10
    };

    const state = {
        cart: {},
        products: [],
        currentAmount: '',
        selectedPaymentMethod: 'cash',
        activeCategory: 'all',
        searchQuery: ''
    };

    const utils = {
        formatCurrency(amount) {
            return `$${parseFloat(amount).toFixed(2)}`;
        },
        parseCurrency(str) {
            return parseFloat(str.replace(/[^0-9.-]+/g, '')) || 0;
        },
        debounce(func, wait) {
            let timeout;
            return function executedFunction(...args) {
                const later = () => { clearTimeout(timeout); func(...args); };
                clearTimeout(timeout);
                timeout = setTimeout(later, wait);
            };
        },
        showNotification(message, type = 'info') {
            alert(`[${type.toUpperCase()}] ${message}`);
        }
    };

    const productManager = {
        init() {
            this.loadProducts();
            this.bindEvents();
        },
        loadProducts() {
            // Mock Core - Connect endpoint when standard API blocks are complete
            state.products = [
                { id: 1, name: 'Coca Cola', price: 1.50, category: 'drink', stock: 90, image: 'https://placehold.co/80x80' },
                { id: 2, name: 'Pepsi', price: 1.40, category: 'drink', stock: 8, image: 'https://placehold.co/80x80' },
                { id: 3, name: 'Red Bull', price: 2.50, category: 'drink', stock: 65, image: 'https://placehold.co/80x80' },
                { id: 4, name: 'Potato Chips', price: 1.20, category: 'snack', stock: 0, image: 'https://placehold.co/80x80' },
                { id: 5, name: 'Doritos', price: 1.80, category: 'snack', stock: 45, image: 'https://placehold.co/80x80' }
            ];
            this.render();
        },
        render() {
            const grid = $('#product-grid');
            const html = this.filterProducts().map(p => {
                const isOut = p.stock === 0;
                const sClass = p.stock === 0 ? 'stock-out' : (p.stock <= CONFIG.LOW_STOCK_THRESHOLD ? 'stock-low' : 'stock-good');
                const sText = p.stock === 0 ? 'Out of stock' : `${p.stock} available`;
                return `
                    <div class="product-card ${isOut ? 'product-card--disabled' : ''}" data-product-id="${p.id}">
                        <div class="product-card__image"><img src="${p.image}" alt="${p.name}"></div>
                        <div class="product-card__info">
                            <span class="product-card__name">${p.name}</span>
                            <span class="product-card__price">${utils.formatCurrency(p.price)}</span>
                            <div class="product-card__stock ${sClass}">${sText}</div>
                        </div>
                    </div>`;
            }).join('');
            grid.html(html);
        },
        filterProducts() {
            return state.products.filter(p => {
                const catMatch = state.activeCategory === 'all' || p.category === state.activeCategory;
                const srcMatch = p.name.toLowerCase().includes(state.searchQuery.toLowerCase());
                return catMatch && srcMatch;
            });
        },
        getProductById(id) {
            return state.products.find(p => p.id === parseInt(id));
        },
        bindEvents() {
            $(document).on('click', '.product-card:not(.product-card--disabled)', (e) => {
                const id = $(e.currentTarget).data('product-id');
                const prod = this.getProductById(id);
                if (prod && prod.stock > 0) {
                    cartManager.addItem(prod);
                    prod.stock--;
                    this.render();
                }
            });

            $('.pos-catalog__filter-pill').on('click', (e) => {
                $('.pos-catalog__filter-pill').removeClass('pos-catalog__filter-pill--active');
                $(e.currentTarget).addClass('pos-catalog__filter-pill--active');
                state.activeCategory = $(e.currentTarget).data('category');
                this.render();
            });

            $('#product-search').on('input', utils.debounce((e) => {
                state.searchQuery = $(e.target).val();
                this.render();
            }, 250));
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
                state.cart[product.id] = { id: product.id, name: product.name, price: product.price, quantity: 1 };
            }
            this.render();
        },
        updateQuantity(id, amt) {
            const cItem = state.cart[id];
            const prod = productManager.getProductById(id);
            if (!cItem || !prod) return;

            if (amt > 0 && prod.stock <= 0) {
                utils.showNotification('No items left in inventory!', 'warning');
                return;
            }

            cItem.quantity += amt;
            prod.stock -= amt;

            if (cItem.quantity <= 0) {
                delete state.cart[id];
            }
            this.render();
            productManager.render();
        },
        removeItem(id) {
            const cItem = state.cart[id];
            const prod = productManager.getProductById(id);
            if (cItem) {
                if (prod) prod.stock += cItem.quantity;
                delete state.cart[id];
            }
            this.render();
            productManager.render();
        },
        clear(completeSale = false) {
            if (!completeSale) {
                Object.values(state.cart).forEach(item => {
                    const prod = productManager.getProductById(item.id);
                    if (prod) prod.stock += item.quantity;
                });
            }
            state.cart = {};
            this.render();
            productManager.render();
        },
        getSubtotal() { return Object.values(state.cart).reduce((s, i) => s + (i.price * i.quantity), 0); },
        getTax() { return this.getSubtotal() * CONFIG.TAX_RATE; },
        getTotal() { return this.getSubtotal() + this.getTax(); },
        isEmpty() { return Object.keys(state.cart).length === 0; },
        render() {
            const emptyState = $('#cart-empty-state');
            const list = $('#cart-list');
            const procBtn = $('#process-payment-btn');

            if (this.isEmpty()) {
                emptyState.show(); list.empty(); procBtn.prop('disabled', true);
                $('#receipt-subtotal, #receipt-tax, #receipt-total').text('$0.00');
                $('#cart-item-count').text('0 items');
                return;
            }

            emptyState.hide();
            procBtn.prop('disabled', false);

            let count = 0;
            const html = Object.values(state.cart).map(item => {
                count += item.quantity;
                return `
                    <li class="cart-item">
                        <div class="cart-item__info">
                            <span class="cart-item__name">${item.name}</span>
                            <div class="cart-item__qty-controls">
                                <button class="btn-qty btn-qty-minus" data-id="${item.id}">-</button>
                                <span>${item.quantity}</span>
                                <button class="btn-qty btn-qty-plus" data-id="${item.id}">+</button>
                                <span class="cart-item__unit-price">@ ${utils.formatCurrency(item.price)}</span>
                            </div>
                        </div>
                        <div style="display:flex; align-items:center; gap:12px;">
                            <span>${utils.formatCurrency(item.price * item.quantity)}</span>
                            <button class="cart-item__remove" data-id="${item.id}">&times;</button>
                        </div>
                    </li>`;
            }).join('');

            list.html(html);
            $('#cart-item-count').text(`${count} item${count > 1 ? 's' : ''}`);
            $('#receipt-subtotal').text(utils.formatCurrency(this.getSubtotal()));
            $('#receipt-tax').text(utils.formatCurrency(this.getTax()));
            $('#receipt-total').text(utils.formatCurrency(this.getTotal()));
        },
        bindEvents() {
            $(document).on('click', '.cart-item__remove', (e) => this.removeItem($(e.currentTarget).data('id')));
            $(document).on('click', '.btn-qty-plus', (e) => this.updateQuantity($(e.currentTarget).data('id'), 1));
            $(document).on('click', '.btn-qty-minus', (e) => this.updateQuantity($(e.currentTarget).data('id'), -1));
        }
    };

    const paymentManager = {
        init() { this.bindEvents(); },
        openModal() {
            if (cartManager.isEmpty()) return;
            const total = cartManager.getTotal();
            $('#modal-receipt-items').html($('#cart-list').html()).find('.cart-item__qty-controls button, .cart-item__remove').remove();
            $('#modal-grand-total').text(utils.formatCurrency(total));
            state.currentAmount = '';
            this.updateDisplay(total);
            $('#payment-modal').removeClass('hidden');
        },
        closeModal() { $('#payment-modal').addClass('hidden'); },
        updateDisplay(def = null) {
            let val = state.currentAmount === '' ? (def !== null ? def : 0) : parseFloat(state.currentAmount) || 0;
            $('#amount-display').text(state.currentAmount.endsWith('.') ? `$${val}.` : utils.formatCurrency(val));
        },
        handleKeypad(key) {
            if (key === '.' && state.currentAmount.includes('.')) return;
            if (state.currentAmount.includes('.') && state.currentAmount.split('.')[1].length >= 2) return;
            state.currentAmount += key;
            this.updateDisplay();
        },
        handleBackspace() {
            state.currentAmount = state.currentAmount.slice(0, -1);
            this.updateDisplay();
        },
        processPayment() {
            const total = cartManager.getTotal();
            const paid = utils.parseCurrency($('#amount-display').text());

            if (paid < total) {
                utils.showNotification(`Insufficient funds! Short by ${utils.formatCurrency(total - paid)}`, 'error');
                return;
            }

            utils.showNotification(`Sale completed! Change due: ${utils.formatCurrency(paid - total)}`, 'success');
            cartManager.clear(true); // pass true to indicate transaction completion (do not return stock)
            this.closeModal();
        },
        bindEvents() {
            $('#process-payment-btn').on('click', () => this.openModal());
            $('[data-dismiss="modal"]').on('click', () => this.closeModal());

            $('.pos-sidebar__payment-btn').on('click', (e) => {
                $('.pos-sidebar__payment-btn').removeClass('pos-sidebar__payment-btn--active');
                $(e.currentTarget).addClass('pos-sidebar__payment-btn--active');
                state.selectedPaymentMethod = $(e.currentTarget).data('payment');
            });

            $('.number-pad__key').on('click', (e) => {
                const key = $(e.currentTarget).data('key');
                if (key !== undefined) this.handleKeypad(key.toString());
                if ($(e.currentTarget).data('action') === 'backspace') this.handleBackspace();
            });

            $('.quick-cash__btn').on('click', (e) => {
                state.currentAmount = $(e.currentTarget).data('amount').toString();
                this.updateDisplay();
            });

            $('#pay-now-btn').on('click', () => this.processPayment());
        }
    };

    // function for  toggle screen
    function initFullscreen(btnId,iconExpandClass='ti-arrows-maximize',iconCollapseClass= 'ti-arrows-minimize') {
        const btn=document.getElementById(btnId);
        if(!btnId) return;
        const icon=btn.querySelector('i');
        //toggle fullscreen
        function toggle(){
            const isFullScreen=!!(document.fullscreenElement || document.webkitFullscreenElement);
            if(!isFullScreen) {
                const el=document.documentElement;
                (el.requestFullscreen || el.webkitFullscreenElement).call(el);
            }else{
                (document.exitFullscreen || document.webkitFullscreenElement).call(document);
            }
        }
        // synce icon to actual fullscreen state
        function syncIcon(){
            const isFullscreen=!!(document.fullscreenElement || document.webkitFullscreenElement);
            if(icon) {
                icon.classList.toggle(iconExpandClass, !isFullscreen);
                icon.classList.toggle(iconExpandClass,isFullscreen);
            }
        }
        btn.addEventListener('click',toggle);
        document.addEventListener('fullscreenchange',syncIcon);
        document.addEventListener('webkitfullscreenchange',syncIcon);
    }

    $(function() {
        productManager.init();
        cartManager.init();
        paymentManager.init();
        initFullscreen('fsBtn');
    });
})();
