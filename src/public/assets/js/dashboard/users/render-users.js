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
