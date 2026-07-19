(function () {
    'use strict';

    window.POS = window.POS || {};

    window.POS.state = {    // it will reciave all data from server to object
        products:       [], // product that get from server
        cart:           [], // product that customer choose
        totalProducts:  0,
        searchQuery:    '',
        activeCategory: 'all',
        isLoading:      false,
    };
})();
