function initCreateUser() {

    // Open modal for create
    $('#btnCreateUser').off('click').on('click', function () {
        resetFormToCreate();
        openModal();
    });

    // Edit button — uses cache, no extra request
    $(document).on('click', '.btn-edit', function () {
        const id   = $(this).data('id');
        const user = window.usersCache.find(u => u.id == id);

        if (!user) {
            Swal.fire('Error', 'User not found.', 'error');
            return;
        }

        populateEditForm(user);
        openModal();
    });

    // Handle create & edit form submit
    $('#registerForm').off('submit').on('submit', function (e) {
        e.preventDefault();

        // const $btn         = $('#loginBtn');
        const $btn         = $('#btnSave');
        const originalHtml = $btn.html();
        const isEdit       = $('#formMethod').val() === 'PUT';
        const url          = isEdit
            ? window.userRoutes.update.replace(':id', $('#user_id').val())
            : window.userRoutes.register;

        $btn.prop('disabled', true).text('Saving...');
        $('.text-error').text('');

        $.ajax({
            url,
            type: 'POST',
            data: new FormData(this),
            processData: false,
            contentType: false,

            success: function (res) {
                // check so standard 200 status responses trigger
                Swal.fire({
                    icon: 'success',
                    title: 'Success',
                    text: res.message || 'User saved successfully.',
                    timer: 1800,
                    showConfirmButton: false,
                }).then(() => {
                    closeModal();
                    resetFormToCreate();

                    // Pass current parameters to fetchUsers to refresh data seamlessly
                    const currentQuery = $('#searchInput').val();
                    const currentPerPage = $('#per-page-select').val();
                    fetchUsers(currentQuery, currentPerPage, 1);
                });
            },

            error: function (xhr) {
                if (xhr.status === 422 && xhr.responseJSON) {
                    const errors = xhr.responseJSON.errors || xhr.responseJSON;
                    $.each(errors, function (key, messages) {
                        $(`.${key}-error`).text(Array.isArray(messages) ? messages[0] : messages);
                    });
                } else {
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: xhr.responseJSON?.message || 'Something went wrong.',
                    });
                }
                $btn.prop('disabled', false).html(originalHtml);
            },
        });
    });
}

function populateEditForm(user) {
    $('#modalTitle').text('Edit User');
    $('#modalIcon').removeClass('ti-user-plus').addClass('ti-edit');

    $('#formMethod').val('PUT');
    $('#user_id').val(user.id);

    $('input[name="name"]').val(user.name);
    $('input[name="email"]').val(user.email);
    $('input[name="phone"]').val(user.phone || '');
    $('select[name="role"]').val(user.roles?.[0]?.name || '');

    $('#avatarPreview').html(
        user.avatar
            ? `<img src="/storage/${escapeHtml(user.avatar)}" style="width:100%;height:100%;object-fit:cover;border-radius:50%;">`
            : `<i class="ti ti-user"></i>`
    );

    $('#password').prop('required', false).val('');
    $('#password_confirmation').prop('required', false).val('');
    $('#passwordReq, #confirmReq').hide();

    $('.text-error').text('');
}

function resetFormToCreate() {
    $('#modalTitle').text('Create New User');
    $('#modalIcon').removeClass('ti-edit').addClass('ti-user-plus');

    $('#formMethod').val('POST');
    $('#user_id').val('');

    $('#registerForm')[0].reset();
    $('#avatarPreview').html('<i class="ti ti-user"></i>');

    $('#password').prop('required', true);
    $('#password_confirmation').prop('required', true);
    $('#passwordReq, #confirmReq').show();

    $('.text-error').text('');
    $('#btnSave').prop('disabled', false).html('<i class="ti ti-device-floppy"></i> Save User');
}
