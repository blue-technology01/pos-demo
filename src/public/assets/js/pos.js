(function () {
    'use strict';

    const CONFIG = {
        TAX_RATE: 0.10,
        LOW_STOCK_THRESHOLD: 10,
        DEBOUNCE_DELAY: 250
    };

    const state = {
        products: [],
        productStock: {},
        cart: [],
        activeCategory: 'all',
        searchQuery: '',
        pagination: { current_page: 1, last_page: 1, per_page: 20 },
        isLoading: false,
    };

    // ─────────────────────────────────────────────────────────────────────────
    // Utils
    // ─────────────────────────────────────────────────────────────────────────
    const utils = {
        formatCurrency(amount) {
            return new Intl.NumberFormat('en-US', { style: 'currency', currency: 'USD' })
                .format(parseFloat(amount) || 0);
        },
        debounce(func, wait = CONFIG.DEBOUNCE_DELAY) {
            let timeout;
            return function (...args) {
                clearTimeout(timeout);
                timeout = setTimeout(() => func.apply(this, args), wait);
            };
        },
        showNotification(message, type = 'info') {
            const colors = { success: '#4ade80', warning: '#fbbf24', error: '#f87171', info: '#60a5fa' };
            const toast = document.createElement('div');
            toast.style.cssText = `
                position:fixed;bottom:20px;right:20px;background:${colors[type]};color:#fff;
                padding:12px 16px;border-radius:6px;z-index:99999;font-size:14px;font-weight:500;
                box-shadow:0 2px 8px rgba(0,0,0,.2);`;
            toast.textContent = message;
            document.body.appendChild(toast);
            setTimeout(() => toast.remove(), 3000);
        },
        csrfToken() {
            return window.CSRF_TOKEN
                || document.querySelector('meta[name="csrf-token"]')?.getAttribute('content')
                || '';
        },
        async fetchJson(url, options = {}) {
            const res = await fetch(url, {
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': this.csrfToken(),
                    'Accept': 'application/json',
                    ...options.headers,
                },
                ...options,
            });
            if (!res.ok) {
                const err = await res.json().catch(() => ({}));
                throw new Error(err.message || `HTTP error ${res.status}`);
            }
            return res.json();
        }
    };
    const productManager = {
        // ─── all products across ALL categories (loaded once on init) ───────────
        _allProducts: [],

        init() {
            this.bindEvents();
            this.fetchProducts();
        },

        buildUrl(page = 1) {
            const params = new URLSearchParams({ per_page: state.pagination.per_page, page });
            if (state.searchQuery) params.set('search', state.searchQuery);
            return `${window.ROUTES.posProducts}?${params.toString()}`;
        },

        async fetchProducts(page = 1) {
            if (state.isLoading) return;
            state.isLoading = true;
            this.renderSkeleton();
            try {
                const response  = await utils.fetchJson(this.buildUrl(page));
                const paginator = response.data;

                const mapped = (paginator.data || []).map(p => {
                    const sellingPrice = parseFloat(p.selling_price ?? p.price ?? p.unit_price) || 0;
                    return {
                        product_code:  p.product_code,
                        name:          p.product_name,
                        image:         p.product_image ? `/storage/${p.product_image}` : '/assets/images/no-image.png',
                        stock:         parseFloat(p.stock) || 0,
                        low_stock:     p.low_stock || false,
                        uoms:          p.uoms || [],
                        price:         sellingPrice,
                        cost_price:    parseFloat(p.cost_price) || 0,
                        category_code: p.category_code || null, // ✅ needed for client-side filter
                    };
                });

                // ✅ when no search — store full set so category filter works offline
                if (!state.searchQuery) {
                    this._allProducts = mapped;
                }

                state.products = mapped;
                this.updateLocalStock();
                state.pagination.current_page = paginator.current_page;
                state.pagination.last_page    = paginator.last_page;

                this.renderFiltered();   // ✅ always go through renderFiltered, not render()
                this.renderPagination();
            } catch (err) {
                console.error('fetchProducts error:', err);
                utils.showNotification('មិនអាចទាញទិន្នន័យផលិតផលបានទេ', 'error');
            } finally {
                state.isLoading = false;
            }
        },

        updateLocalStock() {
            state.products.forEach(p => { state.productStock[p.product_code] = p.stock; });
        },
        //
        _localSearch(query) {
            const q = query.toLowerCase();
            const filtered = this._allProducts.filter(p =>
                p.name.toLowerCase().includes(q) ||
                p.product_code.toLowerCase().includes(q)
            );

            // update state but don't overwrite _allProducts
            state.products = filtered;
            this.updateLocalStock();
            this.renderFiltered();
            $('#product-pagination').html('');
        },

        // ✅ silent background fetch — updates grid when done, no skeleton
        async _serverSearch(query) {
            try {
                const response  = await utils.fetchJson(this.buildUrl(1));
                const paginator = response.data;

                // if user already changed the query, discard stale result
                if (state.searchQuery !== query) return;

                const mapped = (paginator.data || []).map(p => {
                    const sellingPrice = parseFloat(p.selling_price ?? p.price ?? p.unit_price) || 0;
                    return {
                        product_code:  p.product_code,
                        name:          p.product_name,
                        image:         p.product_image ? `/storage/${p.product_image}` : '/assets/images/no-image.png',
                        stock:         parseFloat(p.stock) || 0,
                        low_stock:     p.low_stock || false,
                        uoms:          p.uoms || [],
                        price:         sellingPrice,
                        cost_price:    parseFloat(p.cost_price) || 0,
                        category_code: p.category_code || null,
                    };
                });

                state.products = mapped;
                state.pagination.current_page = paginator.current_page;
                state.pagination.last_page    = paginator.last_page;
                this.updateLocalStock();
                this.renderFiltered();
                this.renderPagination();
            } catch (err) {
                console.error('_serverSearch error:', err);
            }
        },

        filterByCategory(category) {
            state.activeCategory = category;

            const source = state.searchQuery ? state.products : this._allProducts;

            const filtered = category === 'all'
                ? source
                : source.filter(p => p.category_code === category);

            state.products = filtered;
            this.updateLocalStock();
            this.renderFiltered();
            $('#product-pagination').html('');
        },

        renderFiltered() {
            const $grid = $('#product-grid');

            if (state.products.length === 0) {
                $grid.html(`
                    <div style="grid-column:1/-1;text-align:center;padding:40px;color:#94a3b8;font-size:14px;">
                        រកមិនឃើញផលិតផល
                    </div>`);
                return;
            }

            $grid.html(state.products.map(product => {
                const stock        = this.getAvailableStock(product.product_code);
                const isOutOfStock = stock <= 0;
                const cartItem     = state.cart.find(i => i.product_code === product.product_code);
                const qtyInCart    = cartItem ? cartItem.quantity : 0;
                const cartItemId   = cartItem ? cartItem.id : '';

                const priceFormatted = utils.formatCurrency(product.price || 0);

                return `
                <div class="product-card ${isOutOfStock ? 'stock-out-card' : ''}"
                    data-product-code="${product.product_code}">

                    <!-- Image Area -->
                    <div class="product-card__image js-card-add"
                        data-product-code="${product.product_code}">
                        <img src="${product.image}"
                            alt="${product.name}"
                            loading="lazy"
                            onerror="this.src='/assets/images/no-image.png'">

                        <!-- Stock Badge -->
                        <div class="stock-badge ${isOutOfStock ? 'stock-out' : 'stock-good'}">
                            ${isOutOfStock ? 'Out of stock' : `${stock} left`}
                        </div>
                    </div>

                    <!-- Product Info -->
                    <div class="product-card__info">
                        <div class="product-card__name">${product.name}</div>
                        <div class="product-card__price">${priceFormatted}</div>

                        <!-- Action Area -->
                        <div class="product-card__action">
                            ${qtyInCart === 0
                                ? `<button class="order-btn js-card-add"
                                        data-product-code="${product.product_code}"
                                        ${isOutOfStock ? 'disabled' : ''}>
                                        Order
                                </button>`
                                : `<div class="qty-stepper">
                                        <button class="js-grid-minus" data-cart-id="${cartItemId}">−</button>
                                        <span class="qty-value">${qtyInCart}</span>
                                        <button class="js-grid-plus" data-cart-id="${cartItemId}">+</button>
                                </div>`
                            }
                        </div>
                    </div>
                </div>`;
            }).join(''));

            // ✅ bind grid buttons via delegation on $grid — no inline onclick needed
            this._bindGridEvents($grid);
        },

        // ✅ separate method — clean delegation, no conflicts with card click
        _bindGridEvents($grid) {
            $grid.off('click.grid')

                // image or Order button → add to cart
                .on('click.grid', '.js-card-add:not([disabled])', (e) => {
                    e.stopPropagation();
                    const code = $(e.currentTarget).data('product-code');
                    productManager.addDefaultUom(code);
                })

                // stepper minus
                .on('click.grid', '.js-grid-minus', (e) => {
                    e.stopPropagation();
                    const id  = $(e.currentTarget).data('cart-id');
                    const qty = parseInt($(e.currentTarget).data('qty'));
                    cartManager.updateQuantity(id, qty);
                })

                // stepper plus
                .on('click.grid', '.js-grid-plus', (e) => {
                    e.stopPropagation();
                    const id  = $(e.currentTarget).data('cart-id');
                    const qty = parseInt($(e.currentTarget).data('qty'));
                    cartManager.updateQuantity(id, qty);
                });
        },

        renderSkeleton() {
            $('#product-grid').html(Array(8).fill(`
                <div style="background:#f1f5f9;border-radius:8px;padding:12px;">
                    <div style="background:#e2e8f0;height:100px;border-radius:6px;margin-bottom:10px;"></div>
                    <div style="background:#e2e8f0;height:14px;border-radius:4px;margin-bottom:6px;"></div>
                    <div style="background:#e2e8f0;height:12px;border-radius:4px;width:60%;"></div>
                </div>`).join(''));
        },

        renderPagination() {
            const { current_page, last_page } = state.pagination;
            if (last_page <= 1) { $('#product-pagination').html(''); return; }
            $('#product-pagination').html(`
                <div style="display:flex;justify-content:center;align-items:center;gap:12px;padding:12px 0;">
                    <button id="pg-prev" ${current_page <= 1 ? 'disabled' : ''}
                        style="padding:6px 14px;border:1px solid #cbd5e1;border-radius:6px;background:#fff;cursor:pointer;${current_page <= 1 ? 'opacity:.4;' : ''}">← Prev</button>
                    <span style="font-size:13px;color:#64748b;">Page ${current_page} / ${last_page}</span>
                    <button id="pg-next" ${current_page >= last_page ? 'disabled' : ''}
                        style="padding:6px 14px;border:1px solid #cbd5e1;border-radius:6px;background:#fff;cursor:pointer;${current_page >= last_page ? 'opacity:.4;' : ''}">Next →</button>
                </div>`);
            $('#pg-prev').on('click', () => this.fetchProducts(current_page - 1));
            $('#pg-next').on('click', () => this.fetchProducts(current_page + 1));
        },

        async addDefaultUom(productCode) {
            // ✅ search _allProducts too, not just current filtered state.products
            const product = this._allProducts.find(p => p.product_code === productCode)
                        || state.products.find(p => p.product_code === productCode);
            if (!product) { utils.showNotification('រកមិនឃើញផលិតផល', 'error'); return; }

            try {
                if (!product.uoms || product.uoms.length === 0) {
                    const res  = await utils.fetchJson(`/cashier/pos/products/${productCode}/uoms`);
                    product.uoms = Array.isArray(res) ? res : (res.data ?? []);
                }

                const selectedUom = product.uoms.find(u => u.is_default)
                                || product.uoms[0]
                                || {
                                        uom_code:         'UNIT',
                                        uom_name:         'Unit',
                                        quantity_per_unit: 1,
                                        selling_price:    product.price,
                                        cost_price:       product.cost_price,
                                        is_default:       true,
                                    };

                cartManager.addItem(product, selectedUom);
            } catch (err) {
                console.error('addDefaultUom error:', err);
                utils.showNotification('កំហុសក្នុងការបន្ថែម', 'error');
            }
        },

        // ✅ search _allProducts first so category-filtered view doesn't hide cart items
        getProductByCode(code) {
            return this._allProducts.find(p => p.product_code === code)
                || state.products.find(p => p.product_code === code);
        },
        getAvailableStock(code) { return state.productStock[code] ?? 0; },
        bindEvents() {
            $('#product-grid').off('click', '.product-card').on(
                'click', '.product-card:not(.product-card--disabled)',
                function () { productManager.addDefaultUom($(this).data('product-code')); }
            );

            $(document).off('click', '.pos-catalog__filter-pill')
                .on('click', '.pos-catalog__filter-pill', (e) => {
                    $('.pos-catalog__filter-pill').removeClass('pos-catalog__filter-pill--active');
                    $(e.currentTarget).addClass('pos-catalog__filter-pill--active');

                    const category = $(e.currentTarget).data('category');

                    if (this._allProducts.length > 0 && !state.searchQuery) {
                        this.filterByCategory(category);
                        return;
                    }

                    state.activeCategory = category;
                    this.fetchProducts(1);
                });

            $('#product-search').off('input').on('input', utils.debounce((e) => {
                const query = e.target.value.trim();
                state.searchQuery = query;

                if (!query) {
                    this.filterByCategory(state.activeCategory);
                    return;
                }

                this._localSearch(query);
                this._serverSearch(query);
            }, 300));
        },
    };

    // cart manager
   const cartManager = {
        addItem(product, uom) {
            if (!product || !uom) return;

            const uomQtyPerUnit = parseFloat(uom.quantity_per_unit) || 1;
            const initialPrice  = parseFloat(uom.selling_price)     || 0;
            const cartItemId    = `${product.product_code}-${uom.uom_code}`;

            const availableStock = productManager.getAvailableStock(product.product_code);
            if (availableStock < uomQtyPerUnit) {
                utils.showNotification('ស្តុកទំនិញនេះមិនគ្រប់គ្រាន់ឡើយ។', 'error');
                return;
            }

            const existing = state.cart.find(i => i.id === cartItemId);
            if (existing) {
                this.updateQuantity(cartItemId, existing.quantity + 1);
            } else {
                state.cart.push({
                    id:                  cartItemId,
                    product_code:        product.product_code,
                    name:                product.name,
                    price:               initialPrice,
                    cost_price:          (parseFloat(product.cost_price) || 0) * uomQtyPerUnit,
                    quantity:            1,
                    uom_code:            uom.uom_code,
                    uom_name:            uom.uom_name,
                    uom_qty_per_unit:    uomQtyPerUnit,
                    discount_percentage: 0,
                    discount_amount:     0,
                    subtotal:            initialPrice,
                    _original_stock:     product.stock,
                });
                this.syncAndRender();
            }
            utils.showNotification(`បានបន្ថែម [${product.name}] ទៅក្នុងកន្ត្រក`, 'success');
        },

        _getOriginalStock(productCode) {
            return productManager.getProductByCode(productCode)?.stock ?? 0;
        },

        switchItemUOM(cartItemId, targetUomCode) {
            const item = state.cart.find(i => i.id === cartItemId);
            if (!item) return;
            const product = productManager.getProductByCode(item.product_code);
            if (!product) return;
            const newUom = (product.uoms || []).find(u => u.uom_code === targetUomCode);
            if (!newUom) return;

            const newQtyPerUnit = parseFloat(newUom.quantity_per_unit) || 1;
            const newPrice      = parseFloat(newUom.selling_price)     || 0;

            const otherQty  = state.cart
                .filter(i => i.product_code === item.product_code && i.id !== cartItemId)
                .reduce((s, i) => s + i.quantity * i.uom_qty_per_unit, 0);
            const available = this._getOriginalStock(item.product_code) - otherQty;

            if (item.quantity * newQtyPerUnit > available) {
                utils.showNotification(`ស្តុកមិនគ្រប់គ្រាន់សម្រាប់ [${newUom.uom_name}]`, 'warning');
                this.renderCart();
                return;
            }

            const newId     = `${item.product_code}-${newUom.uom_code}`;
            const duplicate = state.cart.find(i => i.id === newId && i.id !== cartItemId);
            if (duplicate) {
                duplicate.quantity += item.quantity;
                duplicate.subtotal  = duplicate.price * duplicate.quantity;
                state.cart = state.cart.filter(i => i.id !== cartItemId);
            } else {
                item.id               = newId;
                item.uom_code         = newUom.uom_code;
                item.uom_name         = newUom.uom_name;
                item.uom_qty_per_unit = newQtyPerUnit;
                item.price            = newPrice;
                item.subtotal         = newPrice * item.quantity;
            }
            this.syncAndRender();
        },

        updateQuantity(cartItemId, newQty) {
            const item = state.cart.find(i => i.id === cartItemId);
            if (!item) return;
            if (newQty <= 0) { this.removeItem(cartItemId); return; }

            const otherQty = state.cart
                .filter(i => i.product_code === item.product_code && i.id !== cartItemId)
                .reduce((s, i) => s + i.quantity * i.uom_qty_per_unit, 0);

            if (newQty * item.uom_qty_per_unit > this._getOriginalStock(item.product_code) - otherQty) {
                utils.showNotification('ស្តុកមិនគ្រប់គ្រាន់។', 'warning');
                this.renderCart();
                return;
            }

            item.quantity = newQty;
            item.subtotal = item.price * newQty;

            // ✅ FIX 3: patch only the changed row — no full list rebuild
            this.updateCatalogStockTracking();
            this._patchCartRow(cartItemId);
            this.renderSidebarTotals();
        },

        removeItem(cartItemId) {
            state.cart = state.cart.filter(i => i.id !== cartItemId);
            this.syncAndRender();
        },

        syncAndRender() {
            this.updateCatalogStockTracking();
            this.renderCart();
            this.renderSidebarTotals();
        },

        // ✅ FIX 2: single-pass totals — replaces getSubtotal/getTotalDiscount/getTax/getTotal
        computeTotals() {
            let subtotal = 0, discount = 0, count = 0;
            for (const i of state.cart) {
                subtotal += i.price * i.quantity;
                discount += i.discount_amount || 0;
                count    += i.quantity;
            }
            const tax   = (subtotal - discount) * CONFIG.TAX_RATE;
            const total = subtotal - discount + tax;
            return { subtotal, discount, tax, total, count };
        },

        // kept as thin wrappers so paymentManager can still call them
        getSubtotal()      { return this.computeTotals().subtotal; },
        getTotalDiscount() { return this.computeTotals().discount; },
        getTax()           { return this.computeTotals().tax;      },
        getTotal()         { return this.computeTotals().total;    },

        updateCatalogStockTracking() {
            state.products.forEach(p => { state.productStock[p.product_code] = p.stock; });
            state.cart.forEach(item => {
                if (state.productStock[item.product_code] !== undefined) {
                    state.productStock[item.product_code] -= item.quantity * item.uom_qty_per_unit;
                }
            });
        },

        isEmpty() { return state.cart.length === 0; },

        renderSidebarTotals() {
            // ✅ FIX 2: one computeTotals() call instead of four separate reduce loops
            const { subtotal, discount, tax, total, count } = this.computeTotals();

            $('#receipt-subtotal').text(utils.formatCurrency(subtotal));
            $('#receipt-discount').text(utils.formatCurrency(discount));
            $('#receipt-tax').text(utils.formatCurrency(tax));
            $('#receipt-total').text(utils.formatCurrency(total));
            $('#cart-item-count').text(`${count} មុខ`);

            const $btn = $('#process-payment-btn');
            const hasItems = state.cart.length > 0;
            $btn.prop('disabled', !hasItems)
                .css({ opacity: hasItems ? '1' : '0.5', cursor: hasItems ? 'pointer' : 'not-allowed' });
        },

        clear(silent = false) {
            state.cart = [];
            this.renderCart();
            this.renderSidebarTotals();
            this.updateCatalogStockTracking();
            if (!silent) productManager.render();
        },

        renderCart() {
            const $list  = $('#cart-list');
            const $empty = $('#cart-empty-state');

            if (state.cart.length === 0) { $list.html(''); $empty.show(); return; }
            $empty.hide();

            $list.html(state.cart.map(item => {
                const product     = productManager.getProductByCode(item.product_code);
                const uoms        = product?.uoms || [];
                const hasMultiUom = uoms.length > 1;

                const uomHtml = hasMultiUom
                    ? `<select class="js-cart-uom-select" data-item-id="${item.id}"
                        style="padding:2px 6px;border:1px solid #e2e8f0; display:flex; justify-content:center; align-item:center ;border-radius:10px;
                                font-size:11px;color:#475569;background:#f8fafc;
                                cursor:pointer;max-width:80px;">
                        ${uoms.map(u =>
                            `<option value="${u.uom_code}" ${u.uom_code === item.uom_code ? 'selected' : ''}>
                                ${u.uom_name || u.uom_code}
                            </option>`
                        ).join('')}
                    </select>`
                    : `<span style="font-size:11px;color:#94a3b8;">${item.uom_name || 'Unit'}</span>`;

                return `
                <li data-cart-id="${item.id}"
                    style="display:flex;align-items:center;gap:12px;
                        padding:12px 0;border-bottom:1px solid #f1f5f9;">

                    <!-- Product image -->
                    <img src="${product?.image || '/assets/images/not-product.png'}"
                        onerror="this.src='/assets/images/not-product.png'"
                        style="width:52px;height:52px;object-fit:cover;
                                border-radius:12px;flex-shrink:0;">

                    <!-- Name + price + stepper -->
                    <div style="flex:1;min-width:0;">

                        <p style="margin:0 0 2px;font-size:13px;font-weight:700;
                                color:#0f172a;white-space:nowrap;
                                overflow:hidden;text-overflow:ellipsis;">
                            ${item.name}
                        </p>

                        <!-- price + uom -->
                        <div style="display:flex;align-items:center;gap:6px;margin-bottom:8px;">
                            <span style="font-size:12px;font-weight:600;color:#16a34a;">
                                ${utils.formatCurrency(item.price)}
                            </span>
                            ${uomHtml}
                        </div>

                        <!-- Stepper -->
                        <div style="display:inline-flex;align-items:center;gap:0;">
                            <button class="js-qty-minus" data-item-id="${item.id}"
                                style="width:28px;height:28px;border:none;
                                    background:#0f172a;color:#fff;font-size:18px;font-weight:700;
                                    border-radius:50%;cursor:pointer;
                                    display:flex;align-items:center;justify-content:center;line-height:1;">−</button>

                            <span class="js-qty-display"
                                style="min-width:32px;text-align:center;
                                        font-size:14px;font-weight:800;color:#0f172a;">
                                ${item.quantity}
                            </span>

                            <button class="js-qty-plus" data-item-id="${item.id}"
                                style="width:28px;height:28px;border:none;
                                    background:#0f172a;color:#fff;font-size:18px;font-weight:700;
                                    border-radius:50%;cursor:pointer;
                                    display:flex;align-items:center;justify-content:center;line-height:1;">+</button>
                        </div>
                    </div>

                    <!-- Delete -->
                    <button class="js-remove-item" data-item-id="${item.id}"
                        style="background:none;border:none;cursor:pointer;
                            color:#cbd5e1;padding:4px;flex-shrink:0;
                            display:flex;align-items:center;justify-content:center;
                            transition:color .15s;"
                        onmouseover="this.style.color='#ef4444'"
                        onmouseout="this.style.color='#cbd5e1'">
                        <span class="material-symbols-outlined" style="font-size:20px;">delete</span>
                    </button>
                </li>`;
            }).join(''));
        },

        _patchCartRow(cartItemId) {
            const item = state.cart.find(i => i.id === cartItemId);
            if (!item) return;
            const $row = $(`#cart-list [data-cart-id="${cartItemId}"]`);
            if (!$row.length) return;
            // ✅ update both qty display and the ×qty label in the price line
            $row.find('.js-qty-display').text(item.quantity);
            $row.find('.js-qty-label').text(`×${item.quantity}`);
        },

        bindEvents() {
            const $list = $('#cart-list');

            // ✅ only fires for products with multiple UOMs
            $list.on('change', '.js-cart-uom-select', (e) => {
                cartManager.switchItemUOM(
                    $(e.currentTarget).data('item-id'),
                    $(e.currentTarget).val()
                );
            });
            $list.on('click', '.js-qty-plus', (e) => {
                const id   = $(e.currentTarget).data('item-id');
                const item = state.cart.find(i => i.id === id);
                if (item) cartManager.updateQuantity(id, item.quantity + 1);
            });
            $list.on('click', '.js-qty-minus', (e) => {
                const id   = $(e.currentTarget).data('item-id');
                const item = state.cart.find(i => i.id === id);
                if (item) cartManager.updateQuantity(id, item.quantity - 1);
            });
            $list.on('click', '.js-remove-item', (e) => {
                cartManager.removeItem($(e.currentTarget).data('item-id'));
            });
        },
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

            // ✅ show receipt immediately with placeholder invoice number
            this.showReceiptModal('...', paymentData, { subtotal, discount, tax, total }, cartSnapshot);

            // ✅ clear cart now — cashier can start next sale while server processes
            cartManager.clear(true);

            // ✅ POST to server in background — only update invoice number when done
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
                    // ✅ receipt already visible — just swap placeholder with real invoice number
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

    // ─────────────────────────────────────────────────────────────────────────
    // Init
    // ─────────────────────────────────────────────────────────────────────────
    $(document).ready(() => {
        productManager.init();
        paymentManager.init();
        cartManager.bindEvents();
        cartManager.renderSidebarTotals();
    });

})();
