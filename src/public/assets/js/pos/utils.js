(function () {
    'use strict';

    window.POS = window.POS || {};

    const utils = {

        // formate currency USD
        formatCurrency(amount) {
            return new Intl.NumberFormat('en-US', {
                style:    'currency',
                currency: 'USD',
            }).format(parseFloat(amount) || 0); // parseFloat: formate string to float number
        },

        // Escape any value before interpolating into HTML strings — it protect attacker by XSS
        escapeHtml(str) {
            const div = document.createElement('div');
            div.textContent = str ?? '';
            return div.innerHTML;
        },

        // Debounce function to limit the rate of function execution
        debounce(fn, delay = 250) {
            let timer;
            return (...args) => {
                clearTimeout(timer);
                timer = setTimeout(() => fn.apply(this, args), delay);
            };
        },

        // Simple toast notification
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

        // for any call to the backend, so headers, error handling stay consistent.
        async fetchJson(url, options = {}) {

            const { headers: extraHeaders, ...restOptions } = options;

            const res = await fetch(url, {
                ...restOptions,
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': window.CSRF_TOKEN,  // it will read token from window.CSRF_TOKEN  from Header
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

    window.POS.utils = utils;
})();
