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
