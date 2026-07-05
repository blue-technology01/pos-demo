@extends('layouts.app')

@section('title', 'User Management')

@push('styles')
<link rel="stylesheet" href="{{ asset('assets/css/dashboard/user.css') }}">
<link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined" />
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@tabler/icons-webfont@latest/dist/tabler-icons.min.css" />
@endpush

@section('content')
<div class="um-page">

    {{-- HEADER --}}
    <div class="um-header">
        <div>
            <h1>User Management</h1>
            <p>Manage system users and roles</p>
        </div>
    </div>

    {{-- FILTER --}}
    <form method="GET" class="um-filter">
        <span style="display: flex; gap: 12px" >
            <div class="um-search-box">
                <span class="material-symbols-outlined">search</span>
                <input type="text"
                    name="search"
                    value="{{ request('search') }}"
                    placeholder="Search name or email…"
                    autocomplete="off">
            </div>
            <select name="role" class="um-filter-select">
                <option value="">All Roles</option>
                @foreach($roles as $r)
                    <option value="{{ $r->name }}"
                        {{ request('role') == $r->name ? 'selected' : '' }}>
                        {{ ucfirst($r->name) }}
                    </option>
                @endforeach
            </select>
            <button type="submit" class="product-section__btn-add" onclick="showLoader()">Filter</button>

            <a href="{{ url()->current() }}" class="um-btn-reset">Clear</a>
        </span>

        <button type="button" class="btn btn-primary" id="btnCreateUser">
            <span class="material-symbols-outlined">person_add</span>
            New User
        </button>
    </form>

    {{-- STATS --}}
    @php
        $collection = $users->getCollection();
    @endphp

    <div class="um-stats">
        <div>Total Users: <strong>{{ $users->total() }}</strong></div>
        <div>Admins: <strong>{{ $collection->filter(fn($u) => $u->hasRole('admin'))->count() }}</strong></div>
        <div>Cashiers: <strong>{{ $collection->filter(fn($u) => $u->hasRole('cashier'))->count() }}</strong></div>
    </div>

    {{-- TABLE --}}
    <div class="um-card">
        <div class="um-table-wrap">
            <table class="um-table">
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

                <tbody>
                    @foreach($users as $index => $user)
                        @php
                            $primaryRole = $user->roles->first()?->name ?? 'none';
                        @endphp

                        <tr>
                            <td>
                                {{ $users->firstItem() + $index }}
                            </td>

                            <td>
                                <div class="user-cell">
                                    @if($user->avatar)
                                        <img src="{{ asset('storage/' . $user->avatar) }}" class="user-avatar">
                                    @else
                                        <div class="user-avatar-initials">
                                            {{ strtoupper(substr($user->name, 0, 2)) }}
                                        </div>
                                    @endif

                                    <div>
                                        <div class="user-name">{{ $user->name }}</div>
                                        <div class="user-id">#{{ $user->id }}</div>
                                    </div>
                                </div>
                            </td>

                            <td>{{ $user->email }}</td>
                            <td>{{ $user->phone ?? '—' }}</td>

                            <td>
                                <span class="role-badge role-badge--{{ $primaryRole }}">
                                    {{ ucfirst($primaryRole) }}
                                </span>
                            </td>

                            <td>
                                <button class="btn-edit" data-user="{{ $user->toJson() }}">Edit</button>
                                <button class="btn-delete" data-id="{{ $user->id }}">Delete</button>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        {{-- Pagination --}}
        <div class="table-footer" style="width: 100%; display: flex;  justify-content: space-between" id="tableFooter">
            <span class="table-footer-left">
                <div class="table-info">
                    showing
                    {{ $users->firstItem() ?? 0 }}
                    -
                    {{ $users->lastItem() ?? 0 }}
                    of
                    {{ $users->total() }}
                    sales
                </div>

                {{-- per page --}}
                <form method="GET" action="{{ request()->url() }}">
                    @foreach(request()->except('per_page', 'page') as $key => $value)
                        <input type="hidden" name="{{ $key }}" value="{{ $value }}">
                    @endforeach

                    <select name="per_page" onchange="showLoader(); this.form.submit()">
                        <option value="15" {{ request('per_page', 15) == 15 ? 'selected' : '' }}>15</option>
                        <option value="25" {{ request('per_page') == 25 ? 'selected' : '' }}>25</option>
                        <option value="50" {{ request('per_page') == 50 ? 'selected' : '' }}>50</option>
                    </select>
                </form>
            </span>

            <div class="pagination">
                {{ $users->links('vendor.pagination.numbers-only') }}
            </div>
        </div>
    </div>
</div>
@include('admin.users.partials.modals')
@endsection

@push('scripts')
<script>
(function () {
    'use strict';

    const config = {
        urls: {
            store: @json(route('admin.users.register')),
            base: @json(url('admin/users')),
        }
    };

    const defaultAvatar = @json(asset('assets/img/default-avatar.png'));
    const storageUrl = @json(asset('storage'));
    const userForm = document.getElementById('userForm');
    // create new user
    document.getElementById('btnCreateUser')?.addEventListener('click', () => {
        userForm?.reset();
        if (userForm) userForm.action = config.urls.store;
        document.getElementById('formMethod').value = 'POST';
        document.getElementById('avatarPreview').src = defaultAvatar;
        document.getElementById('modalTitle').innerHTML =
            '<span class="material-symbols-outlined">person_add</span> Create user';

        if (window.userModalControls) {
            window.userModalControls.open();
        } else {
            document.getElementById('userModal')?.classList.add('active');
        }
    });

    userForm?.addEventListener('submit', (e) => {
        e.preventDefault();

        const method = document.getElementById('formMethod').value || 'POST';
        const formData = new FormData(userForm);

        if (method !== 'POST') {
            formData.append('_method', method);
        }

        fetch(userForm.action || config.urls.store, {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                'Accept': 'application/json'
            },
            body: formData
        })
        .then(async (res) => {
            const data = await res.json();
            if (!res.ok || !data.success) {
                throw new Error(data.message || 'Save failed');
            }
            window.location.reload();
        })
        .catch(err => alert(err.message));
    });
    // edit and remove user
    document.addEventListener('click', (e) => {

        // EDIT USER
        const editBtn = e.target.closest('.btn-edit');
        if (editBtn) {
            const user = JSON.parse(editBtn.dataset.user || '{}');

            console.log('Edit user:', user);

            const role = user.roles?.[0]?.name || '';

            if (userForm) userForm.action = `${config.urls.base}/${user.id}`;
            document.getElementById('formMethod').value = 'PUT';
            document.getElementById('fieldName').value = user.name || '';
            document.getElementById('fieldEmail').value = user.email || '';
            document.getElementById('fieldPhone').value = user.phone || '';
            document.getElementById('fieldRole').value = role;
            document.getElementById('fieldPassword').value = '';
            document.getElementById('fieldPasswordConfirm').value = '';
            document.getElementById('avatarInput').value = '';
            document.getElementById('avatarPreview').src = user.avatar
                ? `${storageUrl}/${user.avatar}`
                : defaultAvatar;
            document.getElementById('modalTitle').innerHTML =
                '<span class="material-symbols-outlined">edit</span> Edit user';

            const modal = document.getElementById('userModal');
            if (window.userModalControls) {
                window.userModalControls.open();
            } else if (modal) {
                modal.classList.add('active');
            }

            return;
        }

        // DELETE USER
        const deleteBtn = e.target.closest('.btn-delete');
        if (deleteBtn) {
            const id = deleteBtn.dataset.id;

            if (!confirm('Are you sure you want to delete this user?')) return;

            fetch(`${config.urls.base}/${id}`, {
                method: 'DELETE',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                    'Accept': 'application/json'
                }
            })
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    // remove row
                    const row = document.getElementById(`row-${id}`);
                    if (row) row.remove();
                } else {
                    alert(data.message || 'Delete failed');
                }
            })
            .catch(err => console.error(err));
        }
    });

})();
</script>
@endpush
