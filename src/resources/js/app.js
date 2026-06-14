const INTERVAL = 4000;

$(document).ready(function () {

    // ── User dropdown ──
    $('#userDropdownToggle').on('click', function (e) {
        e.stopPropagation();
        $('#userDropdown').toggleClass('open');
    });

    $(document).on('click', function () {
        $('#userDropdown').removeClass('open');
    });

    // ── Sidebar collapse ──
    $('#sidebarToggle').on('click', function () {
        $('#sidebar').toggleClass('collapsed');
        localStorage.setItem('sidebar_collapsed', $('#sidebar').hasClass('collapsed'));
    });

    if (localStorage.getItem('sidebar_collapsed') === 'true') {
        $('#sidebar').addClass('collapsed');
    }

    // ── Submenu toggle ──
    $('.menu-item.has-sub').on('click', function () {
        var $group = $(this).closest('.menu-group');
        var isOpen = $group.hasClass('open');

        $('.menu-group.open').not($group).removeClass('open')
            .find('.sub-menu').slideUp(200);

        if (isOpen) {
            $group.removeClass('open');
            $group.find('.sub-menu').slideUp(200);
        } else {
            $group.addClass('open');
            $group.find('.sub-menu').slideDown(200);
        }
    });

    // ── Restore open submenus on load ──
    $('.menu-group.open').find('.sub-menu').show();

});

// ── Auth page slider ──
document.addEventListener('DOMContentLoaded', function () {

    const slides = document.querySelectorAll('.slide');
    const panel  = document.querySelector('.panel-image');

    if (!slides.length || !panel) return;

    const contents = [
        { title: 'Fast Point of Sale',       sub: 'Quick checkout, table management, and real-time orders.' },
        { title: 'Smart Order Management',   sub: 'Track every table and order in real time.' },
        { title: 'Multiple Payment Options', sub: 'Accept cash, card, QR, and more seamlessly.' },
    ];

    let current = 0;
    let timer;

    // Inject progress bar
    const bar = document.createElement('div');
    bar.className = 'slide-progress';
    panel.appendChild(bar);

    function goTo(index) {
        slides[current].classList.remove('active');
        current = (index + slides.length) % slides.length;
        slides[current].classList.add('active');

        const titleEl = document.getElementById('slide-title');
        const subEl   = document.getElementById('slide-sub');
        if (titleEl) titleEl.textContent = contents[current].title;
        if (subEl)   subEl.textContent   = contents[current].sub;
    }

    function startProgress() {
        bar.style.transition = 'none';
        bar.style.width = '0%';
        requestAnimationFrame(() => requestAnimationFrame(() => {
            bar.style.transition = `width ${INTERVAL}ms linear`;
            bar.style.width = '100%';
        }));
    }

    function startAuto() {
        clearInterval(timer);
        startProgress();
        timer = setInterval(() => {
            goTo(current + 1);
            startProgress();
        }, INTERVAL);
    }

    startAuto();
});
