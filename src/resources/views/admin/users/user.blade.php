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
            <button class="btn-filter">Filter</button>
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

    <div class="pm-pagination">
        <div class="pm-pagination__meta">
            <span class="pm-pagination__text"></span>
            <div class="pm-pagination__per-page">
                <label for="per-page-select">Show:</label>
                <select id="per-page-select" class="pm-pagination__select">
                    <option value="15">15</option>
                    <option value="25" selected>25</option>
                    <option value="50">50</option>
                </select>
            </div>
        </div>
        <div class="pm-pagination__links" id="paginationLinks"></div>
    </div>
</div>
<div id="createUserDialog" class="dialog-overlay">
    <div class="dialog-box">
        <div class="dialog-header">
            <div class="dialog-title">
                <i class="ti ti-user-plus" id="modalIcon"></i>
                <span id="modalTitle">Create New User</span>
            </div>
            <button type="button" id="closeDialog" class="btn-close">
                <i class="ti ti-x"></i>
            </button>
        </div>

        <form id="registerForm" enctype="multipart/form-data">
            @csrf
            <input type="hidden" name="_method" id="formMethod" value="POST">
            <input type="hidden" name="user_id" id="user_id" value="">

            <div class="form-avatar-wrap">
                <div id="avatarPreview" class="avatar-preview">
                    <i class="ti ti-user"></i>
                </div>
                <label for="profileInput" class="upload-label">
                    <i class="ti ti-camera"></i> Upload Photo
                </label>
                <input type="file" id="profileInput" name="avatar" accept="image/*" hidden>
            </div>

            <div class="form-grid">
                <div class="form-group">
                    <label>Full Name <span class="req">*</span></label>
                    <input type="text" name="name" placeholder="John Doe" required>
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
                        <option value="">Select Role</option>
                        @forelse ($roles as $role)
                            <option value="{{ $role->name }}">{{ ucfirst($role->name) }}</option>
                        @empty
                            <option disabled>No roles available</option>
                        @endforelse
                    </select>
                    <small class="text-error role-error"></small>
                </div>

                <div class="form-group">
                    <label>Password <span class="req" id="passwordReq">*</span></label>
                    <input type="password" name="password" id="password" placeholder="Minimum 8 characters" minlength="8">
                    <small class="text-error password-error"></small>
                </div>

                <div class="form-group">
                    <label>Confirm Password <span class="req" id="confirmReq">*</span></label>
                    <input type="password" name="password_confirmation" id="password_confirmation" placeholder="Confirm password">
                    <small class="text-error password_confirmation-error"></small>
                </div>
            </div>

            <div class="dialog-footer">
                <button type="button" id="cancelDialog" class="btn-cancel">Cancel</button>
                <button type="submit" id="btnSave" class="btn-save">
                    <i class="ti ti-device-floppy"></i> Save User
                </button>
            </div>
        </form>
    </div>
</div>
@endsection

@push('scripts')
    <script>
        window.userRoutes = {
            users: "{{ route('admin.users') }}",
            register: "{{ route('admin.users.register') }}",
            update: "{{ route('admin.users.update', ':id') }}",
            destroy: "{{ route('admin.users.destroy', ':id') }}"
        };
    </script>
    <script src="{{ asset('assets/js/dashboard/users/render-users.js') }}"></script>
    <script src="{{ asset('assets/js/dashboard/users/fetch-users.js') }}"></script>
    <script src="{{ asset('assets/js/dashboard/users/create-user.js') }}"></script>
    <script src="{{ asset('assets/js/dashboard/users/delete-user.js') }}"></script>
    <script src="{{ asset('assets/js/dashboard/users/modal.js') }}"></script>
    <script src="{{ asset('assets/js/dashboard/users/index.js') }}"></script>
@endpush
