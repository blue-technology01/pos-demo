(function () {
    'use strict';

    window.POS = window.POS || {};

    // create object barcodeScanner it make have only one module
    const barcodeScanner = {
        // property
        buffer: '', // where that scanner store title or number when read

        lastTime: 0,  // store time key last key of keyboard and and be tracking scaner or user click keybord

        init() {  // method

            $('#start-btn').on('click', function () {

                window.POS.utils.notify('Barcode Scanner Activated', 'success');

                setTimeout(() => $('body').focus(), 100);

            });

            $(document).on('keypress', (e) => this.handleKeyPress(e));
        },

        // this is method that get key from barcode scanner  and store it on buffer by params e
        handleKeyPress(e) {

            // checking user input or not
            const tag = document.activeElement?.tagName;

            if (tag === 'INPUT' || tag === 'TEXTAREA' || tag === 'SELECT') {
                return;
            }

            // store time now that store milliseconds
            const now = Date.now();
            // checking if between key now - lastTime < 70
            // it mean it stay scanner, but < 70 it mean new scanner
            if (now - this.lastTime > 70) {
                this.buffer = '';
            }

            this.lastTime = now;

            // checking key Enter
            if (e.which === 13) {

                if (this.buffer.length > 5) {

                    this.processBarcode(this.buffer.trim());
                    // processBarcode ("123434")
                }

                this.buffer = '';
                return;
            }

            this.buffer += String.fromCharCode(e.which); // inert key to buffer
        },

        // method for finding barcode product and insert to cart
        processBarcode(barcode) {

            const product = window.POS.state.products.find(p =>  // find it will finding first item = true
                p.uoms && p.uoms.some(u =>
                    (u.barcode || '').toString().trim() === barcode
                )
            );

            if (product) {
                if (window.POS.cartManager) {
                    window.POS.cartManager.addDefault(product.product_code);  // add product to cart
                }
                window.POS.utils.notify(`Added: ${product.name}`, 'success');
            } else {
                window.POS.utils.notify(`Not found: ${barcode}`, 'error');
            }
        }
    };

    window.POS.barcodeScanner = barcodeScanner;
})();
