document.addEventListener('DOMContentLoaded', function () {

    //  generic function to toggle any dropdown by button and dropdown IDs
    function toggleDropdown(btnId, dropdownId) {
        const btn      = document.getElementById(btnId);
        const dropdown = document.getElementById(dropdownId);

        if (!btn || !dropdown) return;

        btn.addEventListener('click', function (e) {
            e.stopPropagation();

            const isOpen = dropdown.classList.contains('show');

            // Close all dropdowns first
            closeAll();

            // Toggle the clicked one
            if (!isOpen) {
                dropdown.classList.add('show');
            }
        });
    }

    // close dropdowns and user menu
    function closeAll() {
        document.querySelectorAll('.nav-pill-dropdown').forEach(el => {
            el.classList.remove('show');
        });

        const userWrap = document.getElementById('userDropdownToggle');
        if (userWrap) userWrap.classList.remove('open');
    }

    // register dropdowns
    toggleDropdown('langBtn',  'langDropdown');
    toggleDropdown('posBtn',   'posDropdown');
    toggleDropdown('notifBtn', 'notifDropdown');

    // user menu is handled separately to also toggle the button state
    const userToggle   = document.getElementById('userDropdownToggle');
    const userDropdown = document.getElementById('userDropdown');

    if (userToggle && userDropdown) {
        userToggle.addEventListener('click', function (e) {
            e.stopPropagation();

            const isOpen = userToggle.classList.contains('open');
            closeAll();

            if (!isOpen) {
                userToggle.classList.add('open');
            }
        });
    }

    // Handle language selection from dropdown
    const langDropdown = document.getElementById('langDropdown');
    if (langDropdown) {
        langDropdown.addEventListener('click', function (e) {
            const item = e.target.closest('.pill-dropdown-item');
            if (!item) return;

            e.stopPropagation();

            // Update active state
            langDropdown.querySelectorAll('.pill-dropdown-item').forEach(el => {
                el.classList.remove('active');
            });
            item.classList.add('active');

            // Update flag icon on button
            const selectedImg = item.querySelector('img');
            const langBtn     = document.getElementById('langBtn');
            if (selectedImg && langBtn) {
                langBtn.querySelector('img').src = selectedImg.src;
                langBtn.querySelector('img').alt = selectedImg.alt;
            }

            const langCode = item.dataset.lang;
            console.log('Language changed to:', langCode);

            // Close dropdown after selection
            closeAll();
        });
    }

    // This prevents clicks inside dropdowns from closing them immediately
    document.querySelectorAll('.nav-pill-dropdown').forEach(el => {
        el.addEventListener('click', function (e) {
            e.stopPropagation();
        });
    });

    // close all dropdowns when clicking outside
    document.addEventListener('click', closeAll);

});
