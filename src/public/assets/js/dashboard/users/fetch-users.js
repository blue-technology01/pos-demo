// escapeHtml
function escapeHtml(str) {
    if (!str) return '';
    return String(str)
        .replace(/&/g, '&amp;')
        .replace(/</g, '&lt;')
        .replace(/>/g, '&gt;')
        .replace(/"/g, '&quot;')
        .replace(/'/g, '&#039;');
}

// renderUsers
function renderUsers(users) {
    if (users.length === 0) {
        $('#tbody').html(`
            <tr>
                <td colspan="6" class="empty-state">No users found.</td>
            </tr>
        `);
        return;
    }

    const html = users.map((user, index) => {
        const avatar = user.avatar
            ? `<img src="/storage/${escapeHtml(user.avatar)}" alt="Avatar">`
            : `<span>${escapeHtml(user.name.charAt(0).toUpperCase())}</span>`;

        const role = user.roles?.length
            ? `<span class="role-badge">${escapeHtml(user.roles[0].name)}</span>`
            : `<span class="role-badge role-none">No Role</span>`;

        return `
            <tr data-id="${user.id}">
                <td>${index + 1}</td>
                <td>
                    <div class="user-cell">
                        <div class="avatar">${avatar}</div>
                        <span class="user-name">${escapeHtml(user.name)}</span>
                    </div>
                </td>
                <td>${escapeHtml(user.email)}</td>
                <td>${user.phone ? escapeHtml(user.phone) : '-'}</td>
                <td>${role}</td>
                <td>
                    <button class="btn-edit" data-id="${user.id}" aria-label="Edit user">
                        <i class="ti ti-edit"></i>
                    </button>
                    <button class="btn-delete" data-id="${user.id}" aria-label="Delete user">
                        <i class="ti ti-trash" style="color:white"></i>
                    </button>
                </td>
            </tr>
        `;
    }).join('');

    $('#tbody').html(html);
}

// renderPaginationControls used by fetchUsers
function renderPaginationControls(currentPage, lastPage) {
    let html = '';

    // Previous button
    if (currentPage > 1) {
        html += `<span class="pm-pagination__btn page-link" data-page="${currentPage - 1}">&laquo;</span>`;
    } else {
        html += `<span class="pm-pagination__btn pm-pagination__btn--disabled">&laquo;</span>`;
    }

    // Page number buttons
    for (let i = 1; i <= lastPage; i++) {
        if (i === currentPage) {
            html += `<span class="pm-pagination__btn pm-pagination__btn--active">${i}</span>`;
        } else {
            html += `<span class="pm-pagination__btn page-link" data-page="${i}">${i}</span>`;
        }
    }

    // Next button
    if (currentPage < lastPage) {
        html += `<span class="pm-pagination__btn page-link" data-page="${currentPage + 1}">&raquo;</span>`;
    } else {
        html += `<span class="pm-pagination__btn pm-pagination__btn--disabled">&raquo;</span>`;
    }

    $('#paginationLinks').html(html);
}

// fetchUsers — LAST
let currentFetchRequest = null;

function fetchUsers(search = '', perPage = 25, page = 1) {

    if (currentFetchRequest) {
        currentFetchRequest.abort();
    }

    $('#tbody').html(`
        <tr>
            <td colspan="6" class="empty-state">
                <i class="ti ti-loader"></i> Loading...
            </td>
        </tr>
    `);

    currentFetchRequest = $.get(window.userRoutes.users, {
        search: search,
        per_page: perPage,
        page: page
    })
    .done(function(response) {
        const users = response.users || [];
        window.usersCache = users;
        renderUsers(users); //renderUsers

        const meta        = response.pagination || {};
        const currentPage = meta.page  || 1;
        const lastPage    = meta.last  || 1;
        const total       = meta.total || 0;

        const from = total === 0 ? 0 : (currentPage - 1) * perPage + 1;
        const to   = Math.min(currentPage * perPage, total);

        $('.pm-pagination__text').html(`
            Showing <strong>${from}</strong> - <strong>${to}</strong> of <strong>${total}</strong> results
        `);

        renderPaginationControls(currentPage, lastPage); //calls renderPaginationControls
    })
    .fail(function(jqXHR) {
        if (jqXHR.statusText === 'abort') return;

        $('#tbody').html(`
            <tr>
                <td colspan="6" class="empty-state text-danger">
                    Failed to load users. Please refresh.
                </td>
            </tr>
        `);
    })
    .always(function() {
        currentFetchRequest = null;
    });
}
