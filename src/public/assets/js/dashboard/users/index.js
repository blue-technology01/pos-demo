// Global user cache — single source of truth
window.usersCache = [];

// Global search timer
let searchTimer = null;

// Global trigger — accessible by all modules
function triggerFetch(targetPage = 1) {
    const query   = $('#searchInput').val().trim();
    const perPage = parseInt($('#per-page-select').val()) || 25;
    fetchUsers(query, perPage, targetPage);
}

$(document).ready(function () {

    // Initial load
    fetchUsers('', 25, 1);
    initModal();
    initCreateUser();
    initDeleteUser();

    // only Filter button triggers search
    $('.btn-filter').on('click', function (e) {
        e.preventDefault();
        triggerFetch(1);
    });

    // Per-page dropdown change still resets page
    $('#per-page-select').on('change', function () {
        triggerFetch(1);
    });

    // Pagination clicks
    $(document).on('click', '.page-link', function (e) {
        e.preventDefault();
        const selectedPage = parseInt($(this).data('page'));
        if (!isNaN(selectedPage)) {
            triggerFetch(selectedPage);
        }
    });
});
