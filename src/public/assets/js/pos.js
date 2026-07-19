(function () {

    'use strict'; // use for open funstion and better secuarity

    // function for protect system crash
    function safeInit(name, fn) {

        const mod = window.POS && window.POS[name];

        if (!mod) {

            console.error(`[POS] "${name}" is missing — check that assets/js/pos/${name.replace(/([A-Z])/g, '-$1').toLowerCase()}.js loaded (Network tab, look for 404).`);

            return;
        }

        try {

            fn(mod);

        } catch (err) {

            console.error(`[POS] "${name}" threw during init:`, err);

        }

    }

    // it will waiting loading ready it will show all module
    $(document).ready(() => {

        safeInit('productManager',  (m) => m.init());  // it will call function safeInit for start module
        safeInit('paymentManager',  (m) => m.init());
        safeInit('cartManager',     (m) => m.bindCart());
        safeInit('barcodeScanner',  (m) => m.init());

    });
})();
