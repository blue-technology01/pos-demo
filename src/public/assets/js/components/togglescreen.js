(function() {
    'use strict';

    function initFullscreen(btnId, iconExpandClass = 'ti-arrows-maximize', iconCollapseClass = 'ti-arrows-minimize') {
        const btn = document.getElementById(btnId);
        if (!btn) {
            console.warn(`[fullscreen] Button #${btnId} not found in DOM.`);
            return;
        }

        const icon = btn.querySelector('i');

        function isFullScreen() {
            return !!(document.fullscreenElement || document.webkitFullscreenElement);
        }

        function toggle() {
            // Toggle fullscreen on the POS page itself
            if (!isFullScreen()) {  
                const el = document.documentElement;
                (el.requestFullscreen || el.webkitRequestFullscreen)?.call(el);
            } else {
                (document.exitFullscreen || document.webkitExitFullscreen)?.call(document);
            }

            // Mirror fullscreen on the customer display, if it's open
            const cd = window.customerDisplay;
            if (!cd) {
                console.warn('[fullscreen] window.customerDisplay is not defined — cannot sync customer display.');
                return;
            }
            if (!cd.window || cd.window.closed) {
                console.warn('[fullscreen] Customer display window is not open — nothing to sync.');
                return;
            }
            cd.window.postMessage({ type: 'TOGGLE_FULLSCREEN' }, window.location.origin);
        }

        function syncIcon() {
            const fs = isFullScreen();
            if (icon) {
                icon.classList.toggle(iconExpandClass, !fs);
                icon.classList.toggle(iconCollapseClass, fs);
            }
        }

        btn.addEventListener('click', toggle);
        document.addEventListener('fullscreenchange', syncIcon);
        document.addEventListener('webkitfullscreenchange', syncIcon);
    }

    $(function() {
        initFullscreen('fsBtn');
    });
})();