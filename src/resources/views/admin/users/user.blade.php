@extends('layouts.app')

@section('title', 'User Management')

@push('styles')
<link rel="stylesheet" href="{{ asset('assets/css/dashboard/user.css') }}">
<link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined" />
@endpush

@section('content')
<div class="um-page">

    {{-- HEADER --}}
    <div class="um-header">
        <div>
            <h1>User Management</h1>
            <p>Manage system users and roles</p>
        </div>

        <button class="btn btn-primary" id="btnCreateUser">
            <span class="material-symbols-outlined" style="font-size:17px">person_add</span>
            New User
        </button>
    </div>

    {{-- STATS --}}
    <div class="um-stats">
        <div>Total Users: <strong>{{ $users->total() }}</strong></div>
        <div>Admins:
            <strong>{{ $users->getCollection()->filter(fn($u) => $u->hasRole('admin'))->count() }}</strong>
        </div>
        <div>Cashiers:
            <strong>{{ $users->getCollection()->filter(fn($u) => $u->hasRole('cashier'))->count() }}</strong>
        </div>
    </div>

    {{-- TABLE --}}
    <div class="um-card">
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

            <tbody id="tbody">
                @forelse($users as $index => $user)
                <tr id="row-{{ $user->id }}">

                    <td>{{ $users->firstItem() + $index }}</td>

                    <td>
                        <div class="user-cell">
                            @if($user->avatar)
                                <img src="{{ asset('storage/'.$user->avatar) }}" class="user-avatar">
                            @else
                                <div class="user-avatar-initials">
                                    {{ strtoupper(substr($user->name,0,2)) }}
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
                        @php $role = $user->roles->first()?->name ?? 'none'; @endphp
                        <span class="role-badge">{{ ucfirst($role) }}</span>
                    </td>

                    <td>
                        <button class="btn-edit"
                            data-id="{{ $user->id }}"
                            data-name="{{ $user->name }}"
                            data-email="{{ $user->email }}"
                            data-phone="{{ $user->phone }}"
                            data-role="{{ $role }}">
                            Edit
                        </button>

                        <button class="btn-delete"
                            data-id="{{ $user->id }}"
                            data-name="{{ $user->name }}">
                            Delete
                        </button>
                    </td>

                </tr>
                @empty
                <tr>
                    <td colspan="6">No users found</td>
                </tr>
                @endforelse
            </tbody>
        </table>

        <div class="um-pagination">
            {{ $users->links() }}
        </div>
    </div>
</div>

{{-- ================= MODAL ================= --}}
<div class="modal-overlay" id="userModal">
    <div class="modal-box">

        <form id="userForm" enctype="multipart/form-data">
            @csrf
            <input type="hidden" id="formMethod" value="POST">

            <h3 id="modalTitle">Create User</h3>

            <div class="avatar-upload">

                <img id="avatarPreview"
                    src="{{ asset('assets/img/default-avatar.png') }}"
                    class="avatar-preview">

                <div class="avatar-upload-info">

                    <p>Profile Photo</p>
                    <span>JPG, PNG up to 2MB</span>

                    <label for="avatarInput" class="avatar-upload-btn">
                        Choose Photo
                    </label>

                    <input type="file" name="avatar" id="avatarInput" accept="image/*">
                </div>
            </div>

            <input type="text" name="name" id="fieldName" placeholder="Name">
            <input type="email" name="email" id="fieldEmail" placeholder="Email">
            <input type="text" name="phone" id="fieldPhone" placeholder="Phone">

            <select name="role" id="fieldRole">
                <option value="">Select Role</option>
                @foreach($roles as $role)
                    <option value="{{ $role->name }}">{{ $role->name }}</option>
                @endforeach
            </select>

            <input type="password" name="password" id="fieldPassword" placeholder="Password">
            <input type="password" name="password_confirmation" id="fieldPasswordConfirm" placeholder="Confirm">

            <button type="submit">Save</button>
            <button type="button" id="closeModal">Cancel</button>
        </form>

    </div>
</div>

{{-- DELETE --}}
<div class="modal-overlay" id="deleteModal">
    <div class="modal-box">

        <p>Delete <strong id="deleteUserName"></strong>?</p>

        <form id="deleteForm">
            @csrf
            @method('DELETE')

            <button type="submit">Yes Delete</button>
            <button type="button" id="cancelDelete">Cancel</button>
        </form>

    </div>
</div>
@endsection


@push('scripts')
<script>
const URL_STORE = '{{ route("admin.users.register") }}';
const URL_BASE  = '{{ url("admin/users") }}';

const userModal   = document.getElementById('userModal');
const deleteModal = document.getElementById('deleteModal');

const userForm   = document.getElementById('userForm');
const deleteForm = document.getElementById('deleteForm');

const tbody = document.getElementById('tbody');

/* ================= CREATE MODAL OPEN ================= */
document.getElementById('btnCreateUser').addEventListener('click', () => {

    userForm.reset();
    userForm.action = URL_STORE;

    document.getElementById('formMethod').value = 'POST';
    document.getElementById('modalTitle').innerText = 'Create User';

    userModal.classList.add('active');
});

/* ================= CLOSE MODALS ================= */
document.getElementById('closeModal')?.addEventListener('click', () => {
    userModal.classList.remove('active');
});

document.getElementById('cancelDelete')?.addEventListener('click', () => {
    deleteModal.classList.remove('active');
});

/* ================= AVATAR PREVIEW ================= */
document.getElementById('avatarInput')?.addEventListener('change', function () {
    const file = this.files[0];
    if (!file) return;

    const reader = new FileReader();
    reader.onload = e => {
        document.getElementById('avatarPreview').src = e.target.result;
    };
    reader.readAsDataURL(file);
});


/* ================= CREATE / UPDATE USER ================= */
userForm.addEventListener('submit', async (e) => {
    e.preventDefault();

    const btn = userForm.querySelector('button[type="submit"]');
    const oldText = btn.innerHTML;

    btn.disabled = true;
    btn.innerHTML = "Saving...";

    try {
        const formData = new FormData(userForm);

        // ⭐ IMPORTANT FIX (THIS FIXES YOUR ERROR)
        formData.append('_method', document.getElementById('formMethod').value);

        const res = await fetch(userForm.action, {
            method: "POST",
            body: formData,
            headers: {
                "X-Requested-With": "XMLHttpRequest",
                "Accept": "application/json"
            }
        });

        const data = await res.json();

        if (!data.success) {
            alert(data.message || "Something went wrong");
            return;
        }

        // ================= CREATE SUCCESS =================
        if (document.getElementById('formMethod').value === 'POST') {

            const u = data.user;

            const avatarHTML = u.avatar
                ? `<img src="/storage/${u.avatar}" class="user-avatar">`
                : `<div class="user-avatar-initials">${u.name.substring(0,2).toUpperCase()}</div>`;

            tbody.insertAdjacentHTML("afterbegin", `
                <tr id="row-${u.id}">
                    <td>New</td>

                    <td>
                        <div class="user-cell">
                            ${avatarHTML}
                            <div>
                                <div class="user-name">${u.name}</div>
                                <div class="user-id">#${u.id}</div>
                            </div>
                        </div>
                    </td>

                    <td>${u.email}</td>
                    <td>${u.phone ?? '—'}</td>
                    <td>${u.roles?.[0]?.name ?? 'none'}</td>

                    <td>
                        <button class="btn-delete"
                            data-id="${u.id}"
                            data-name="${u.name}">
                            Delete
                        </button>
                    </td>
                </tr>
            `);

            userModal.classList.remove('active');
            userForm.reset();
        }

        // ================= UPDATE SUCCESS =================
        else {

            const row = document.getElementById(`row-${data.user.id}`);

            if (row) {
                location.reload(); // simple safe fix
            }
        }

    } catch (err) {
        console.error(err);
        alert("Server error");
    }

    btn.disabled = false;
    btn.innerHTML = oldText;
});


/* ================= DELETE USER ================= */
deleteForm.addEventListener('submit', async (e) => {
    e.preventDefault();

    const btn = deleteForm.querySelector('button[type="submit"]');
    const oldText = btn.innerHTML;

    btn.disabled = true;
    btn.innerHTML = "Deleting...";

    try {
        const formData = new FormData(deleteForm);
        formData.append('_method', 'DELETE');

        const res = await fetch(deleteForm.action, {
            method: "POST",
            body: formData,
            headers: {
                "X-Requested-With": "XMLHttpRequest",
                "Accept": "application/json"
            }
        });

        const data = await res.json();

        if (data.success) {
            document.getElementById(`row-${data.id}`)?.remove();
            deleteModal.classList.remove('active');
        } else {
            alert(data.message);
        }

    } catch (err) {
        console.error(err);
        alert("Server error");
    }

    btn.disabled = false;
    btn.innerHTML = oldText;
});


/* ================= EDIT OPEN ================= */
document.addEventListener('click', (e) => {
    const btn = e.target.closest('.btn-edit');
    if (!btn) return;

    const d = btn.dataset;

    userForm.reset();

    userForm.action = `${URL_BASE}/${d.id}`;
    document.getElementById('formMethod').value = 'PUT';

    document.getElementById('modalTitle').innerText = "Edit User";

    document.getElementById('fieldName').value  = d.name;
    document.getElementById('fieldEmail').value = d.email;
    document.getElementById('fieldPhone').value = d.phone;
    document.getElementById('fieldRole').value  = d.role;

    userModal.classList.add('active');
});


/* ================= DELETE OPEN ================= */
document.addEventListener('click', (e) => {
    const btn = e.target.closest('.btn-delete');
    if (!btn) return;

    document.getElementById('deleteUserName').innerText = btn.dataset.name;
    deleteForm.action = `${URL_BASE}/${btn.dataset.id}`;

    deleteModal.classList.add('active');
});

</script>
@endpush
