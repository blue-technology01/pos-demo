(
    function() {
        'use strict';

        window.POS = window.POS || {}; //  checking using ready for customer display, if don't use it will create
        const state       = window.POS.state;
        const cartManager = window.POS.cartManager; // get module cart using

        //
        const customerDisplay = {
            window: null,

            open() {

                const width  = screen.availWidth;
                const height = screen.availHeight;

                this.window = window.open(
                    window.CUSTOMER_DISPLAY_URL,
                    'CustomerDisplay',
                    `width=${width},height=${height},left=0,top=0`
                );
            },

            update() { // it will checking window open or not
                // if window it not open
                if (!this.window || this.window.closed) return;

                const { subtotal, discount, tax, total } = cartManager.computeTotals();

                this.window.postMessage({  // it will sending data from window to other window
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
                }, window.location.origin); // manage sending and recieveing have one only domain
            },
        };

        window.POS.customerDisplay = customerDisplay; // it will sending object to customer display to global pos that other file can be open

        // this code it will expose  customer display to global window
        window.customerDisplay      = customerDisplay;


        window.openCustomerWindow   = () => customerDisplay.open();
        window.updateCustomerScreen = () => customerDisplay.update();

        window.addEventListener('message', (e) => {

            if (e.origin !== window.location.origin) return;

            if (e.data?.type === 'CUSTOMER_DISPLAY_READY') {
                customerDisplay.update();
            }

        });

    }
)
