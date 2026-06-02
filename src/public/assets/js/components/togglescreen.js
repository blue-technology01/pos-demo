(function() {
    'use strict';
    // function for  toggle screen
    function initFullscreen(btnId,iconExpandClass='ti-arrows-maximize',iconCollapseClass= 'ti-arrows-minimize') {
        const btn=document.getElementById(btnId);
        if(!btnId) return;
        // find the icon element within the button
        const icon=btn.querySelector('i');
        function toggle(){
            const isFullScreen=!!(document.fullscreenElement || document.webkitFullscreenElement);  //  webkitFullscreenElement for Safari support
            // toggle fullscreen based on current state
            if(!isFullScreen) {
                const el=document.documentElement;  // use the whole page for fullscreen el is 
                (el.requestFullscreen || el.webkitFullscreenElement).call(el);
            }else{
                (document.exitFullscreen || document.webkitFullscreenElement).call(document);
            }
        }

        // synce icon to actual fullscreen state
        function syncIcon(){
            const isFullscreen=!!(document.fullscreenElement || document.webkitFullscreenElement);
            // toggle icon classes based on fullscreen state
            if(icon) {
                icon.classList.toggle(iconExpandClass, !isFullscreen);
                icon.classList.toggle(iconCollapseClass, isFullscreen);
            }
        }
        
        // attach event listeners
        btn.addEventListener('click',toggle);
        document.addEventListener('fullscreenchange',syncIcon);
        document.addEventListener('webkitfullscreenchange',syncIcon);
    }
    // initialize fullscreen toggle on DOM
    $(function() {
        initFullscreen('fsBtn');
    });
})();
