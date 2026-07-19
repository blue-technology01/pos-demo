(function () {
    'use strict';

    window.POS = window.POS || {}; // create object POS on global window
    const state = window.POS.state;
    const utils = window.POS.utils;

    // function for calculate total of item on stock can be sale
    function getAvailableStock(productCode) {

        const product = productManager.getByCode(productCode);

        if (!product) return 0;

        // calculate stock that use on cart
        const used = state.cart.reduce((sum, item) =>
            item.product_code === productCode
                ? sum + item.quantity * item.uom_qty_per_unit // true  => ((12 * 1 ) + (12 * 1))
                : sum  // don't sum
        , 0);
        return product.stock - used;  //  return stock
    }

    // create object method for manage product on POS
    const productManager = {
        // start product module
        init() {
            this.bindSearch();
            this.bindCategoryFilter();
            this.bindGrid();
            this.fetchProducts();
        },

        buildUrl() {
            const params = new URLSearchParams(); // build query param /pos/products?search=Coca&category_code=drink

            if (state.searchQuery) {
                params.set('search', state.searchQuery);
            }

            if (state.activeCategory !== 'all') {
                params.set('category_code', state.activeCategory);
            }

            const query = params.toString();
            return query ? `${window.ROUTES.posProducts}?${query}` : window.ROUTES.posProducts;
        },

        // show loading UI
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
            $('#product-grid').html(Array(count).fill(skeletonCard).join(''));
        },

        // fetching product from controller
        async fetchProducts() {

            if (state.isLoading) return;

            state.isLoading = true;
            this.renderSkeleton(8);

            try {

                const res = await utils.fetchJson(this.buildUrl());
                const productsData =
                    res.data?.data ||
                    res.data ||
                    res.products ||
                    [];

                state.totalProducts = res.data?.total ?? res.total ?? productsData.length;

                state.products = productsData.map(p => {

                    const uoms = p.uoms || []; // handle  uom

                    // checking default uom
                    const defaultUom =
                        uoms.find(u => u.is_default === true || u.is_default === 1)
                        || uoms[0];

                    const displayPrice = defaultUom
                        ? parseFloat(defaultUom.selling_price || 0)
                        : 0;

                    // return new object
                    return {
                        product_code: p.product_code,
                        name: p.product_name,
                        image: p.product_image ? `${window.POS_ASSETS.storageBase}/${p.product_image}` : window.POS_ASSETS.placeholder,
                        stock: parseFloat(p.stock) || 0,
                        min_stock: parseFloat(p.min_stock) || 0,
                        description: p.product_description || '',
                        price: displayPrice,
                        cost_price: 0,
                        category_code: p.category_code || null,
                        uoms: uoms.map(u => ({
                            uom_code: u.uom_code,
                            uom_name: u.uom_name || u.uom_code,
                            barcode: u.barcode,
                            quantity_per_unit: parseFloat(u.quantity_per_unit || 1),
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

            const normalized = (code || '').trim().toUpperCase();

            return state.products.find(p =>
                (p.product_code || '').trim().toUpperCase() === normalized
            ) || null;

        },

        // render product cards for user POS
        renderGrid() {

            const $grid = $('#product-grid');

            if (!state.products.length) { // checking product have or not
                $grid.html(`
                    <div class="product-grid__empty">
                        <span class="material-symbols-outlined">inventory_2</span>
                        <div class="product-grid__empty-title">No products found</div>
                        <div class="product-grid__empty-sub">Try changing category or search term</div>
                    </div>
                `);
                return;
            }

            const html = state.products.map(p => {

                const stock      = getAvailableStock(p.product_code);
                const isDisabled = stock <= 0;
                const minStock   = p.min_stock || 0;

                let stockClass, stockLabel;

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

                const safeCode = utils.escapeHtml(p.product_code);
                const safeName = utils.escapeHtml(p.name);

                return `
                    <div class="product-card product-card__add-btn ${isDisabled ? 'product-card--disabled' : ''}"
                        data-code="${safeCode}">

                        <div class="product-card__image">
                            <img src="${p.image}" alt="${safeName}" loading="lazy"
                                onerror="this.src='${window.POS_ASSETS.placeholder}'">
                            <span class="product-card__stock ${stockClass}">
                                <span class="material-symbols-outlined">inventory_2</span>
                                ${stockLabel}
                            </span>
                        </div>

                        <div class="product-card__body">
                            <div class="product-card__name">
                                ${safeName}
                            </div>
                            <div class="product-card__price">${utils.formatCurrency(p.price)}</div>
                            ${p.description
                                ? `<div class="product-card__desc">
                                    ${utils.escapeHtml(p.description)}
                                </div>`
                                : ''
                            }
                        </div>

                        <div class="product-card__footer">
                            <button class="product-card__btn ${isDisabled ? 'product-card__btn--disabled' : ''}"
                                    ${isDisabled ? 'disabled' : ''}
                                    data-code="${safeCode}">
                                <span class="material-symbols-outlined">add</span>
                                ${isDisabled ? 'Out of stock' : 'Add to cart'}
                            </button>
                        </div>

                    </div>
                `;
            }).join('');
            $grid.html(html);
        },

        bindGrid() {
            $('#product-grid')
                .off('click.addBtn')
                .on('click.addBtn', '.product-card__add-btn:not([disabled])', (e) => {
                    e.stopPropagation();
                    if (state.isLoading) {
                        utils.notify('Loading products…', 'warning');
                        return;
                    }
                    const code = $(e.currentTarget).data('code');
                    window.POS.cartManager.addDefault(code);
                });
        },

        bindSearch() {
            $('#product-search').on('input', utils.debounce((e) => {
                state.searchQuery = e.target.value.trim();
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

    window.POS.productManager      = productManager;
    window.POS.getAvailableStock   = getAvailableStock;
})();
