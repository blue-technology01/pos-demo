(function () {
    'use strict';
    // every UI store on state object
    const state = {
        products:      [],
        cart:          [],
        searchQuery:   '',
        activeCategory:'all',
        isLoading:     false,
    };
    // function for format currency, debounce, notify, fetchJson
    const utils = {
        formatCurrency(amount) {
            return new Intl.NumberFormat('en-US', {
                style:    'currency',
                currency: 'USD',
            }).format(parseFloat(amount) || 0);
        },
        // Debounce function to limit the rate of function execution
        debounce(fn, delay = 250) {
            let timer;
            return (...args) => {
                clearTimeout(timer);
                timer = setTimeout(() => fn.apply(this, args), delay);
            };
        },
        // Simple notification function
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

        showNotification(msg, type) {
            this.notify(msg, type);
        },

        async fetchJson(url, options = {}) {
            // Ensure CSRF token is included in headers
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

    // stock management function to get available stock for a product considering items in the cart
    function getAvailableStock(productCode) {
        const product = productManager.getByCode(productCode);
        if (!product) return 0;
        // Calculate total quantity of this product in the cart
        const used = state.cart.reduce((sum, item) =>
            item.product_code === productCode
                ? sum + item.quantity * item.uom_qty_per_unit // sum the total quantity used in the cart
                : sum
        , 0);
        return product.stock - used;
    }
    // product management object to handle product fetching, rendering, and filtering
    const productManager = {
        // Initialize product manager: bind events and fetch products
        init() {
            this.bindSearch();
            this.bindCategoryFilter();
            this.bindGrid();
            this.fetchProducts();
        },
        // Build the URL for fetching products based on search query and active category
        buildUrl() {
            const params = new URLSearchParams();

            if (state.searchQuery) {
                params.set('search', state.searchQuery);
            }
            if (state.activeCategory !== 'all') {
                params.set('category_code', state.activeCategory);
            }

            const query = params.toString();
            return query ? `${window.ROUTES.posProducts}?${query}` : window.ROUTES.posProducts;
        },

        renderSkeleton(count = 8) {
            const skeletonCard = `
                <div class="product-card--skeleton">
                    <div class="skeleton-image"></div>
                    <div class="skeleton-body">
                        <div class="skeleton skeleton-name"></div>
                        <div class="skeleton skeleton-price"></div>
                    </div>
                    <div class="skeleton-footer">
                        <div class="skeleton skeleton-btn"></div>
                    </div>
                </div>
            `;
            // Render skeleton cards in the product grid
            $('#product-grid').html(Array(count).fill(skeletonCard).join(''));
        },
        // fetch products from the server and update the state and UI accordingly
        async fetchProducts() {
            if (state.isLoading) return;
            // Set loading state and render skeletons while fetching
            state.isLoading = true;
            this.renderSkeleton(8);
            try {
                const res = await fetch(this.buildUrl()).then(r => r.json());
                const productsData =
                    res.data?.data ||
                    res.data ||
                    res.products ||
                    [];

                state.totalProducts = res.data?.total ?? res.total ?? productsData.length;
                // convert data from backend format to frontend format
                state.products = productsData.map(p => {
                    const uoms = p.uoms || [];
                    const defaultUom =
                        uoms.find(u => u.is_default === true || u.is_default === 1)
                        || uoms[0];
                    const displayPrice = defaultUom
                        ? parseFloat(defaultUom.selling_price || 0)
                        : 0;

                    return {
                        product_code: p.product_code,
                        name: p.product_name,
                        image: p.product_image ? `${window.POS_ASSETS.storageBase}/${p.product_image}` : window.POS_ASSETS.placeholder,
                        stock: parseFloat(p.stock) || 0, // Ensure stock is a number
                        min_stock: parseFloat(p.min_stock) || 0, // Ensure min_stock is a number
                        description: p.product_description || '',
                        price: displayPrice,
                        cost_price: 0,
                        category_code: p.category_code || null,
                        uoms: uoms.map(u => ({
                            uom_code: u.uom_code,
                            uom_name: u.uom_name || u.uom_code,
                            quantity_per_unit: parseFloat(u.quantity_per_unit || 1), // Default to 1 if not provided
                            selling_price: parseFloat(u.selling_price || 0),
                            is_default: Boolean(u.is_default),
                        })),
                    };
                });
                this.renderGrid();

            } catch (err) {
                console.error('fetchProducts error:', err);
                utils.notify('Failed to load products', 'error');
                this.renderGrid();
            } finally {
                state.isLoading = false;
            }
        },

        getByCode(code) {
            const normalized = (code || '').trim().toUpperCase(); // trim and normalize the code for comparison
            return state.products.find(p => // find the product by matching the product code or any of its UOM barcodes
                (p.product_code || '').trim().toUpperCase() === normalized
            ) || null;
        },

        renderGrid() {
            const $grid = $('#product-grid');
            if (!state.products.length) {
                $grid.html(`
                    <div class="product-grid__empty">
                        <span class="material-symbols-outlined">inventory_2</span>
                        <div class="product-grid__empty-title">No products found</div>
                        <div class="product-grid__empty-sub">Try changing category or search term</div>
                    </div>
                `);
                return;
            }

            // Render product cards based on the current state of products
            const html = state.products.map(p => {
                const stock      = getAvailableStock(p.product_code);
                const isDisabled = stock <= 0;
                const minStock = p.min_stock || 0;
                let stockClass, stockLabel;
                // stock > 0: in stock, stock <= minStock: low stock, stock <= 0: out of stock
                if (stock <= 0) {
                    stockClass = 'stock-out';
                    stockLabel = 'Out of stock';
                } else if (stock <= minStock) {
                    stockClass = 'stock-low';
                    stockLabel = 'Low stock';
                } else {
                    stockClass = 'stock-in';
                    stockLabel = 'In stock';
                }

                return `
                    <div class="product-card product-card__add-btn ${isDisabled ? 'product-card--disabled' : ''}"
                        data-code="${p.product_code}">

                        <div class="product-card__image">
                            <img src="${p.image}" alt="${p.name}" loading="lazy"
                                onerror="this.src='${window.POS_ASSETS.placeholder}'">
                            <span class="product-card__stock ${stockClass}">
                                <span class="material-symbols-outlined">inventory_2</span>
                                ${stockLabel}
                            </span>
                        </div>

                        <div class="product-card__body">
                            <div class="product-card__name">${p.name}</div>
                            <div class="product-card__price">${utils.formatCurrency(p.price)}</div>
                            ${p.description ? `<div class="product-card__desc">${p.description}</div>` : ''}
                        </div>

                        <div class="product-card__footer">
                            <button class="product-card__btn ${isDisabled ? 'product-card__btn--disabled' : ''}"
                                    ${isDisabled ? 'disabled' : ''}
                                    data-code="${p.product_code}">
                                <span class="material-symbols-outlined">add</span>
                                ${isDisabled ? 'Out of stock' : 'Add to cart'}
                            </button>
                        </div>

                    </div>
                `;
            }).join(''); // join the array of HTML strings into a single string for rendering
            $grid.html(html); // update the product grid with the generated HTML
        },
        bindGrid() {
            $('#product-grid')
                .off('click.addBtn')
                .on('click.addBtn', '.product-card__add-btn:not([disabled])', (e) => {
                    e.stopPropagation(); // trigger only when clicking the button, not the card itself
                    if (state.isLoading) {
                        utils.notify('Loading products…', 'warning');
                        return;
                    }
                    const code = $(e.currentTarget).data('code');
                    cartManager.addDefault(code);
                });
        },

        bindSearch() {
            $('#product-search').on('input', utils.debounce((e) => {
                state.searchQuery = e.target.value.trim(); // update the search query in the state
                this.fetchProducts();
            }, 300));
        },
        bindCategoryFilter() {
            $(document).off('click.categoryFilter')
                .on('click.categoryFilter', '.pos-catalog__filter-pill', (e) => {
                    const $pill = $(e.currentTarget);

                    $('.pos-catalog__filter-pill').removeClass('pos-catalog__filter-pill--active');
                    $pill.addClass('pos-catalog__filter-pill--active');

                    state.activeCategory = $pill.data('category') || 'all';
                    state.searchQuery    = '';
                    $('#product-search').val('');
                    this.fetchProducts();
                });
        },
    };
    /**
     *  Handles barcode scanner input and processes scanned
     *  barcodes to add products to the cart.
     */
    const barcodeScanner = {
        buffer: '',
        lastTime: 0,
        init() {
            $('#start-btn').on('click', function() {
                utils.notify('Barcode Scanner Activated', 'success');
                // Focus body so scanner input is captured
                setTimeout(() => $('body').focus(), 100);
            });

            $(document).on('keypress', (e) => {
                const now = Date.now();

                if (now - this.lastTime > 70) {
                    this.buffer = '';
                }
                this.lastTime = now;

                if (e.which === 13) { // Enter key
                    if (this.buffer.length > 5) {
                        this.processBarcode(this.buffer.trim());
                    }
                    this.buffer = '';
                    return;
                }

                this.buffer += String.fromCharCode(e.which);
            });
        },
        /**
         * Process the scanned barcode: search for the product and add it to the cart if found
         *  Flow: scan -> match product -> add to cart
        */
        processBarcode(barcode) {
            console.log('Scanned Barcode:', barcode);
            // Search in loaded products
            const product = state.products.find(p =>
                p.uoms && p.uoms.some(u =>
                    (u.barcode || '').toString().trim() === barcode
                )
            );
            if (product) {
                cartManager.addDefault(product.product_code);
                utils.notify(`Added: ${product.name}`, 'success');
            } else {
                utils.notify(`Not found: ${barcode}`, 'error');
            }
        }
    };
    /**
     *  Manages the shopping cart, including
     *  adding/removing items, updating quantities,
    */
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
            if (uom.quantity_per_unit > product.stock - used) { // check if stock is enough for the new item
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
            const $el = $(`#cart-list [data-id="${id}"]`);
            $el.find('.qty').text(item.quantity);
            $el.find('.price').text(utils.formatCurrency(item.subtotal));
            this.renderTotals();
        },

        remove(id) {
            state.cart = state.cart.filter(i => i.id !== id);
            this.renderCart();
            this.renderTotals();
            updateCustomerScreen();

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
            const subtotal = state.cart.reduce((sum, i) => sum + i.price * i.quantity, 0); // calculate subtotal by summing up the price * quantity of each item in the cart
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

                const imageUrl = product?.image || window.POS_ASSETS.placeholder;

                const uomControl = hasMulti
                    ? `<select class="cart-uom-select" data-id="${item.id}">
                        ${uoms.map(u => `
                            <option value="${u.uom_code}" ${u.uom_code === item.uom_code ? 'selected' : ''}>
                                ${u.uom_name || u.uom_code}
                            </option>
                        `).join('')}
                    </select>`
                    : `<span class="cart-uom-label">${item.uom_name || item.uom_code}</span>`;
                return `
                    <li class="cart-item" data-id="${item.id}" data-code="${item.product_code}">
                        <img class="cart-item__img"
                            src="${imageUrl}"
                            alt="${item.name}"
                            onerror="this.src='${window.POS_ASSETS.placeholder}'">
                        <div class="cart-item__content">
                            <div class="cart-item__top">
                                <span class="cart-item__name">${item.name}</span>
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
            if (!newUom || newUomCode === item.uom_code) return;
            const newId    = `${product.product_code}-${newUomCode}`;
            const existing = state.cart.find(i => i.id === newId);
            if (existing) {
                state.cart = state.cart.filter(i => i.id !== oldId);
                this.updateQty(newId, existing.quantity + item.quantity);
                return;
            }
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
            updateCustomerScreen();
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
    /**
     * Manages the payment process, including opening the payment modal,
     * handling payment method selection, and confirming sales.
    */
    const paymentManager = {
        selectedMethodCode: 'cash',
        selectedMethodId:   null,
        init() {
            this.bindEvents();
            this.injectPopupStyles();
        },
        // Inject keyframe animations once
        injectPopupStyles() {
            if (document.getElementById('sale-popup-styles')) return;
            const style = document.createElement('style');
            style.id = 'sale-popup-styles';
            style.textContent = `
                @keyframes salePopIn {
                    from { opacity: 0; transform: scale(0.75); }
                    to   { opacity: 1; transform: scale(1); }
                }
                @keyframes saleBarShrink {
                    from { width: 100%; }
                    to   { width: 0%; }
                }
            `;
            document.head.appendChild(style);
        },

        openPaymentModal() { // openPaymentModal() is called when the user clicks the "Process Payment" button
            if (!window.currentRegisterId) {
                utils.notify('Please open a shift before processing payment!', 'error');
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
                        <span class="receipt-item-row__qty">X ${item.quantity}</span>
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
                    // show negative change in red with a minus sign if cash received is less than amount due
                    : `-${utils.formatCurrency(Math.abs(change))}`)
                .css('color', change >= 0 ? 'green' : 'red');
        },

        toggleCashInput() {
            const isCash = this.selectedMethodCode === 'cash';
            $('#cash-received').closest('div').toggle(isCash);
            $('.change-box').toggle(isCash);
            if (!isCash) $('#change-amount').text('$0.00').css('color', '#000');
        },

        validatePayment() { // for cash payment, check if cash received is enough and calculate change
            const { total } = cartManager.computeTotals();
            if (this.selectedMethodCode !== 'cash') {
                return { cashReceived: total, change: 0 };
            }
            const cash = parseFloat($('#cash-received').val()) || 0;
            // check cash received is enough and calculate change
            if (cash <= 0) {
                utils.notify('Please enter the amount received!', 'error');
                return null;
            }
            // check if cash received is less than total amount due
            if (cash < total) {
                utils.notify(`Insufficient: ${utils.formatCurrency(total - cash)} more needed`, 'warning');
                return null;
            }
            return { cashReceived: cash, change: cash - total };
        },

        async confirmSale() {
            const paymentData = this.validatePayment();
            if (!paymentData) return;
            const $btn = $('#confirmPaymentBtn');
            $btn.prop('disabled', true).text('Processing…');
            // Compute totals and prepare cart snapshot
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

            cartManager.clear(false);
            // try to send sale data to server
            try {
                const data = await utils.fetchJson(window.ROUTES.confirmSale, {
                    method: 'POST',
                    body: JSON.stringify({  // stringify is use for convert object to json string
                        payment_method:  this.selectedMethodCode,
                        paid_amount:     paymentData.cashReceived,
                        sub_total:       subtotal,
                        discount_amount: discount,
                        total_amount:    total,
                        change_amount:   paymentData.change,
                        tax_amount:      tax,
                        customer_id:     window.selectedCustomerId || null,
                        register_id:     window.currentRegisterId  || null,
                        // map cart items to the required format for the server
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
                    // Close payment modal
                    $('#paymentModal').addClass('hidden').css('display', 'none');
                    // Clear cart
                    cartManager.clear(false);
                    // Clear selected customer
                    window.selectedCustomerId   = null;
                    window.selectedCustomerName = null;
                    customerDisplay.window?.postMessage({ type: 'HIDE_QR' }, '*'); // hide QR code on customer display if open
                    customerDisplay.window ? customerDisplay.update() : null;
                    // Always show success popup
                    showSalePopup('success', data.invoice_no);
                    if (window.PREVIEW_RECEIPT === true) {
                        this.showReceiptModal(
                            data.invoice_no,
                            paymentData,
                            { subtotal, discount, tax, total },
                            cartSnapshot
                        );
                    } else {
                        setTimeout(() => {
                            this.autoDownloadReceipt(
                                data.invoice_no,
                                paymentData,
                                { subtotal, discount, tax, total },
                                cartSnapshot
                            );
                        }, 500);
                    }
                } else {
                    showSalePopup('error', data.message || 'Sale failed.');
                }
                // function for show popup form
                function showSalePopup(type, value) {
                    $('#sale-popup-overlay').remove();
                    const isSuccess = type === 'success';
                    const html = `
                    <div id="sale-popup-overlay" style="
                        position:fixed; inset:0; background:rgba(0,0,0,0.45);
                        display:flex; align-items:center; justify-content:center; z-index:9999;">
                    <div style="
                        background:#fff; border-radius:16px; border:1px solid #e5e7eb;
                        padding:2rem 1.75rem 1.5rem; width:320px; text-align:center;
                        animation:salePopIn 0.35s cubic-bezier(0.34,1.56,0.64,1) forwards;">
                        <div style="
                            width:64px; height:64px; border-radius:50%; margin:0 auto 1.25rem;
                            display:flex; align-items:center; justify-content:center; font-size:28px;
                            background:${isSuccess ? '#dcfce7' : '#fee2e2'};
                            border:2px solid ${isSuccess ? '#86efac' : '#fca5a5'};
                            color:${isSuccess ? '#16a34a' : '#dc2626'};">
                        ${isSuccess ? '✓' : '!'}
                        </div>
                        <p style="font-size:18px; font-weight:600; margin:0 0 6px; color:#111827;">
                        ${isSuccess ? 'Sale complete' : 'Sale failed'}
                        </p>
                        <p style="font-size:13px; color:#6b7280; margin:0 0 1.25rem; line-height:1.6;">
                        ${isSuccess ? 'Payment received and recorded.' : value}
                        </p>
                        ${isSuccess ? `
                        <div style="
                            display:inline-flex; align-items:center; gap:6px;
                            background:#f9fafb; border:1px solid #e5e7eb; border-radius:8px;
                            padding:6px 14px; font-size:13px; font-weight:500;
                            color:#15803d; margin-bottom:1.25rem;">
                        Invoice: #${value}
                        </div>` : ''}
                    </div>
                    </div>`;
                    $('body').append(html);
                    setTimeout(() => {
                        $('#sale-popup-overlay').fadeOut(300, function () { $(this).remove(); });
                    }, 3000);
                    $('#sale-popup-overlay').on('click', function (e) {
                        if (e.target === this) $(this).remove();
                    });
                }
            } catch (err) {
                console.error('confirmSale error:', err);
                utils.notify(err.message || 'Sale failed.', 'error');
            } finally {
                $btn.prop('disabled', false).text('Confirm & Complete Sale');
            }
        },

        // show preview UI for receipt
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
                        <div>${item.name} (${item.uom_name}) X ${item.quantity}</div>
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

        // pdf generation and download ( no preview )
        autoDownloadReceipt(invoiceNo, paymentData, totals, cartSnapshot) {
            if (typeof html2pdf === 'undefined') {
                console.warn('html2pdf not loaded — skipping auto download.');
                return;
            }
            // Populate receipt paper element silently (modal stays hidden)
            const now = new Date();
            $('#receipt-date').text(
                now.toLocaleDateString('en-US', {
                    weekday: 'short', month: 'long', day: 'numeric', year: 'numeric',
                }) + ' • ' + now.toLocaleTimeString('en-US', {
                    hour: '2-digit', minute: '2-digit',
                })
            );
            $('#receipt-invoice').text(`#${invoiceNo}`);
            $('#modal-customer-name').text(
                window.selectedCustomerName || 'Walk-in Customer'
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
            const el = document.querySelector('.receipt-paper');
            if (!el) {
                console.warn('receipt-paper element not found — skipping auto download.');
                return;
            }
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
                .catch(err => console.error('Auto download failed:', err));
        },
        closeAllModals() {
            $('#paymentModal, #receiptModal').addClass('hidden').css('display', 'none');
        },

        // invoice printing and download as pdf
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

                if (this.selectedMethodCode === 'qr') {
                    const { total } = cartManager.computeTotals();
                    // TODO: replace with real QR image URL once payment gateway is integrated
                    const qrImageUrl = '/assets/images/qr.png';
                    customerDisplay.window?.postMessage({
                        type: 'SHOW_QR',
                        qrImageUrl: qrImageUrl,
                        amount: total
                    }, '*');
                } else {
                    customerDisplay.window?.postMessage({ type: 'HIDE_QR' }, '*');
                }
            });
            $(document).on('input',    '#cash-received', ()  => this.updateChange());
            $(document).on('keypress', '#cash-received', (e) => { if (e.which === 13) this.confirmSale(); });
            $('#process-payment-btn').on('click', () => this.openPaymentModal());
            $('#closePaymentModal, #cancelPaymentBtn').on('click', () => {
                $('#paymentModal').addClass('hidden').css('display', 'none');
                customerDisplay.window?.postMessage({ type: 'HIDE_QR' }, '*');
            });
            $('#confirmPaymentBtn').on('click', () => this.confirmSale());
            $(document).off('click.receipt')
                .on('click.receipt', '#downloadReceiptBtn', () => this.downloadReceiptAsPDF())
                .on('click.receipt', '#closeReceiptBtn',    () => this.closeAllModals());
        },
    };

    $(document).ready(() => {
        productManager.init();
        paymentManager.init();
        cartManager.bindCart();
        barcodeScanner.init();
    });
    /**
     *  Handles the customer display window,
     *  including opening the window and updating its content based on the current cart and selected customer.
     */
    const customerDisplay = {
        window: null,
        open() {
            const width = screen.availWidth;
            const height = screen.availHeight;
            this.window = window.open(
                window.CUSTOMER_DISPLAY_URL,
                'CustomerDisplay',
                `width=${width},height=${height},left=0,top=0`
            );
        },
        update() {
            if (!this.window || this.window.closed) return;

            const { subtotal, discount, tax, total } = cartManager.computeTotals();

            this.window.postMessage({
                type: 'UPDATE_DISPLAY',
                customer: window.selectedCustomerName || 'Walk-in Customer',
                items: state.cart.map(item => ({
                    name: item.name,
                    uom_name: item.uom_name || item.uom_code,
                    quantity: item.quantity,
                    price: item.price,
                    subtotal: item.subtotal,
                })),
                subtotal,
                discount,
                tax,
                total,
            }, window.location.origin);
        }
    };

    window.customerDisplay = customerDisplay; //guarantees the fullscreen script can reach it, regardless of what scope this code is wrapped in

    window.openCustomerWindow = () => customerDisplay.open();
    window.updateCustomerScreen = () => customerDisplay.update();

    window.addEventListener('message', (e) => {
        if (e.origin !== window.location.origin) return;
        if (e.data?.type === 'CUSTOMER_DISPLAY_READY') {
            customerDisplay.update();
        }
    });
})

();
