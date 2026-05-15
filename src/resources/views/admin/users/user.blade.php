@extends('layouts.app')

@section('title', 'User Management')

@push('styles')
    <link rel="stylesheet" href="{{ asset('assets/css/dashboard/user.css') }}">
@endpush

@section('content')
<div class="uw">
    <div class="user-topbar">
        <div class="search-wrap">
            <i class="ti ti-search"></i>
            <input type="text" id="searchInput" placeholder="Search users...">
        </div>
        <button class="btn-new" id="btnCreateUser">
            <i class="ti ti-user-plus"></i> New User
        </button>
    </div>

    <div class="table-wrap">
        <table class="user-table" id="userTable">
            <thead>
                <tr>
                    <th>#</th>
                    <th>User</th>
                    <th>Email</th>
                    <th>Phone</th>
                    <th>Role</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody id="tbody"></tbody>
        </table>
    </div>

    <!-- Pagination will be handled later if needed -->
</div>

<!-- Create User Dialog -->
<div id="createUserDialog" class="dialog-overlay">
    <div class="dialog-box">

        <div class="dialog-header">
            <div class="dialog-title">
                <div class="dialog-icon">
                    <i class="ti ti-user-plus"></i>
                </div>
                <span>Create new user</span>
            </div>
            <button type="button" id="closeDialog" class="btn-close">
                <i class="ti ti-x"></i>
            </button>
        </div>

        <form id="registerForm" enctype="multipart/form-data">
            @csrf

            <div class="form-avatar-wrap">
                <div id="avatarPreview" class="avatar-preview">
                    <i class="ti ti-user"></i>
                </div>
                <label for="profileInput" class="upload-label">
                    <i class="ti ti-camera"></i> Upload photo
                </label>
                <input type="file" id="profileInput" name="avatar" accept="image/*" hidden>
            </div>

            <div class="form-grid">
                <div class="form-group">
                    <label>Full name <span class="req">*</span></label>
                    <input type="text" name="name" placeholder="e.g. John Doe" required>
                    <small class="text-error name-error"></small>
                </div>

                <div class="form-group">
                    <label>Email <span class="req">*</span></label>
                    <input type="email" name="email" placeholder="user@example.com" required>
                    <small class="text-error email-error"></small>
                </div>

                <div class="form-group">
                    <label>Phone <span class="req">*</span></label>
                    <input type="text" name="phone" placeholder="09xxxxxxxx" required>
                    <small class="text-error phone-error"></small>
                </div>

                <div class="form-group">
                    <label>Role <span class="req">*</span></label>
                    <select name="role" required>
                        <option value="">Select role</option>
                        @forelse ($roles as $role)
                            <option value="{{ $role->name }}">{{ ucfirst($role->name) }}</option>
                        @empty
                            <option disabled>No roles found</option>
                        @endforelse
                    </select>
                    <small class="text-error role-error"></small>
                </div>

                <div class="form-group">
                    <label>Password <span class="req">*</span></label>
                    <input type="password" name="password" placeholder="Min. 6 characters" required minlength="6">
                    <small class="text-error password-error"></small>
                </div>

                <div class="form-group">
                    <label>Confirm password <span class="req">*</span></label>
                    <input type="password" name="password_confirmation" placeholder="Repeat password" required>
                    <small class="text-error password_confirmation-error"></small>
                </div>
            </div>

            <div class="dialog-footer">
                <button type="button" id="cancelDialog" class="btn-cancel">Cancel</button>
                <button type="submit" id="btnSave" class="btn-save">
                    <i class="ti ti-device-floppy"></i> Save user
                </button>
            </div>
        </form>

    </div>
</div>
@endsection

@push('scripts')
<script>
$(document).ready(function() {
    fetchUsers();   // Load users on page ready

    // Open Create Dialog
    $('#btnCreateUser').on('click', () => $('#createUserDialog').addClass('active'));

    // Close Dialog
    $('#closeDialog, #cancelDialog').on('click', () => $('#createUserDialog').removeClass('active'));
    $('#createUserDialog').on('click', function(e) {
        if (e.target === this) $(this).removeClass('active');
    });

    // Avatar Preview
    $('#profileInput').on('change', function() {
        const file = this.files[0];
        if (!file) return;
        const reader = new FileReader();
        reader.onload = e => {
            $('#avatarPreview').html(`<img src="${e.target.result}" style="width:100%;height:100%;object-fit:cover;">`);
        };
        reader.readAsDataURL(file);
    });

    // Submit Create Form
    $('#registerForm').on('submit', function(e) {
        e.preventDefault();

        const $btn = $('#btnSave');
        const originalText = $btn.html();

        $btn.prop('disabled', true).html('<i class="ti ti-loader"></i> Saving...');

        $('.text-error').text('');

        $.ajax({
            url: "{{ route('admin.users.register') }}",
            type: "POST",
            data: new FormData(this),
            processData: false,
            contentType: false,
            dataType: "json",

            success: function(response) {
                if (response.success) {
                    $('#registerForm')[0].reset();
                    $('#avatarPreview').html('<i class="ti ti-user"></i>');
                    $('#createUserDialog').removeClass('active');
                    fetchUsers();   // Refresh table

                    Swal.fire({
                        icon: 'success',
                        title: 'Success!',
                        text: response.message,
                        timer: 1800,
                        showConfirmButton: false
                    });
                }
            },

            error: function(xhr) {
                if (xhr.status === 422) {
                    const errors = xhr.responseJSON.errors;
                    $.each(errors, (key, msgs) => {
                        $(`.${key}-error`).text(msgs[0]);
                    });
                } else {
                    alert('Failed to create user!');
                }
            },

            complete: function() {
                $btn.prop('disabled', false).html(originalText);
            }
        });
    });
});

// fetch user
function fetchUsers() {

    $.get("{{ route('admin.users') }}", function (response) {

        console.log(response);

        let users = response.data;
        let rows = '';

        if (!users || users.length === 0) {

            $('#tbody').html(`
                <tr>
                    <td colspan="6" class="empty-state">
                        No users found
                    </td>
                </tr>
            `);

            return;
        }

        users.forEach(function(user, index) {

            const avatarHtml = user.avatar
                ? `<img src="/storage/${user.avatar}"
                        loading="lazy"
                        style="width:100%;height:100%;object-fit:cover;">`
                : `<span>${user.name.charAt(0).toUpperCase()}</span>`;

            const roleBadge = (user.roles && user.roles.length > 0)
                ? `<span class="role-badge">${user.roles[0].name}</span>`
                : `<span class="role-badge role-none">No Role</span>`;

            rows += `
                <tr data-id="${user.id}">

                    <td>${index + 1}</td>

                    <td>
                        <div class="user-cell">
                            <div class="avatar">
                                ${avatarHtml}
                            </div>

                            <span class="user-name">
                                ${user.name}
                            </span>
                        </div>
                    </td>

                    <td>${user.email}</td>

                    <td>${user.phone}</td>

                    <td>${roleBadge}</td>

                    <td>
                        <button class="btn-edit"
                            onclick="editUser(${user.id})">
                            <i class="ti ti-edit"></i>
                        </button>

                        <button class="btn-delete"
                            onclick="deleteUser(${user.id})">
                            <i class="ti ti-trash"></i>
                        </button>
                    </td>

                </tr>
            `;
        });

        $('#tbody').html(rows);

    }).fail(function(error) {

        console.error(error);

        $('#tbody').html(`
            <tr>
                <td colspan="6"
                    class="empty-state text-danger">
                    Error loading users
                </td>
            </tr>
        `);

    });

}

function editUser(id) {
    // TODO: Implement later
    alert('Edit function coming soon... ID: ' + id);
}

function deleteUser(id) {
    if (!confirm('Are you sure you want to delete this user?')) return;

    $.ajax({
        url: `/admin/users/${id}`,   // You need to create DELETE route
        type: "DELETE",
        data: { _token: "{{ csrf_token() }}" },
        success: function() {
            fetchUsers();
            Swal.fire('Deleted!', '', 'success');
        },
        error: function() {
            alert('Failed to delete user');
        }
    });
}
</script>
@endpush
