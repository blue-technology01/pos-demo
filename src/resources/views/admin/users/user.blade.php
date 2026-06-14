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
    </div>

    {{-- TOOLBAR --}}
    <div class="um-filter">
        <div class="um-search-box">
            <span class="material-symbols-outlined">search</span>
            <input type="text" id="searchInput" placeholder="Search name or email…" autocomplete="off">
        </div>

        <select id="roleFilter" class="um-filter-select">
            <option value="">All Roles</option>
            @foreach($roles as $r)
                <option value="{{ $r->name }}">{{ ucfirst($r->name) }}</option>
            @endforeach
        </select>

        <select id="sortFilter" class="um-filter-select">
            <option value="newest">Newest first</option>
            <option value="oldest">Oldest first</option>
            <option value="name_asc">Name A–Z</option>
            <option value="name_desc">Name Z–A</option>
        </select>

        <button id="btnClear" class="um-btn-reset" style="display:none">
            <span class="material-symbols-outlined" style="font-size:14px">close</span>
            Clear
        </button>

        <div class="um-per-page">
            <label for="perPage">Show</label>
            <select id="perPage" class="um-filter-select">
                <option value="10">10</option>
                <option value="25" selected>25</option>
                <option value="50">50</option>
            </select>
        </div>

        <button class="btn btn-primary" id="btnCreateUser">
            <span class="material-symbols-outlined" style="font-size:17px">person_add</span>
            New User
        </button>
    </div>

    {{-- STATS --}}
    <div class="um-stats">
        <div>Total Users: <strong id="statTotal">{{ $users->count() }}</strong></div>
        <div>Admins: <strong id="statAdmin">{{ $users->filter(fn($u) => $u->hasRole('admin'))->count() }}</strong></div>
        <div>Cashiers: <strong id="statCashier">{{ $users->filter(fn($u) => $u->hasRole('cashier'))->count() }}</strong></div>
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
                <tbody id="tbody">
                    @foreach($users as $user)
                        @php
                            $primaryRole = $user->roles->first()?->name ?? 'none';
                        @endphp
                        <tr id="row-{{ $user->id }}"
                            data-id="{{ $user->id }}"
                            data-role="{{ $primaryRole }}"
                            data-name="{{ strtolower($user->name) }}"
                            data-email="{{ strtolower($user->email) }}"
                            data-created="{{ $user->created_at->timestamp }}">

                            <td class="row-index"></td>
                            <td>
                                <div class="user-cell">
                                    @if($user->avatar)
                                        <img src="{{ asset('storage/' . $user->avatar) }}" class="user-avatar" alt="{{ $user->name }}">
                                    @else
                                        <div class="user-avatar-initials">{{ strtoupper(substr($user->name, 0, 2)) }}</div>
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
                                <button class="btn-delete" data-id="{{ $user->id }}" data-name="{{ $user->name }}">Delete</button>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <div class="um-table-footer">
            <div class="um-table-info" id="tableInfo"></div>
            <div class="um-pagination" id="pagination"></div>
        </div>
    </div>
</div>

{{-- Modals (unchanged) --}}
@include('admin.users.partials.modals')

@endsection

@push('scripts')
<script>
    (function () {
        'use strict';

        // ====================== CONFIG & STATE ======================
        const config = {
            urls: {
                store: @json(route('admin.users.register')),
                base: @json(url('admin/users')),
            },
            defaultAvatar: @json(asset('assets/img/default-avatar.png'))
        };

        let allRows = Array.from(document.querySelectorAll('#tbody tr[data-id]'));
        let currentPage = 1;
        let currentFilters = {};

        // ====================== UTILITIES ======================
        const escHtml = (str) => String(str ?? '')
            .replace(/&/g, '&amp;').replace(/</g, '&lt;')
            .replace(/>/g, '&gt;').replace(/"/g, '&quot;');

        const escAttr = (str) => String(str ?? '').replace(/"/g, '&quot;').replace(/'/g, '&#39;');

        // ====================== ROW BUILDER ======================
        function buildRow(user) {
            const role = user.roles?.[0]?.name ?? 'none';
            const avatarHTML = user.avatar
                ? `<img src="/storage/${escAttr(user.avatar)}" class="user-avatar" alt="${escHtml(user.name)}">`
                : `<div class="user-avatar-initials">${escHtml(user.name.substring(0, 2).toUpperCase())}</div>`;

            const tr = document.createElement('tr');
            tr.id = `row-${user.id}`;
            Object.assign(tr.dataset, {
                id: user.id,
                role: role,
                name: user.name.toLowerCase(),
                email: user.email.toLowerCase(),
                created: user.created_at ?? Math.floor(Date.now() / 1000)
            });

            tr.innerHTML = `
                <td class="row-index"></td>
                <td>
                    <div class="user-cell">
                        ${avatarHTML}
                        <div>
                            <div class="user-name">${escHtml(user.name)}</div>
                            <div class="user-id">#${user.id}</div>
                        </div>
                    </div>
                </td>
                <td>${escHtml(user.email)}</td>
                <td>${user.phone ? escHtml(user.phone) : '—'}</td>
                <td><span class="role-badge role-badge--${escAttr(role)}">${escHtml(role)}</span></td>
                <td>
                    <button class="btn-edit" data-user="${escAttr(JSON.stringify(user))}">Edit</button>
                    <button class="btn-delete" data-id="${user.id}" data-name="${escAttr(user.name)}">Delete</button>
                </td>
            `;

            return tr;
        }

        // ====================== RENDER ENGINE ======================
        function render() {
            const search = document.getElementById('searchInput').value.toLowerCase().trim();
            const role = document.getElementById('roleFilter').value;
            const sort = document.getElementById('sortFilter').value;
            const perPage = parseInt(document.getElementById('perPage').value);

            // Filter
            let visible = allRows.filter(row => {
                const matchSearch = !search ||
                    row.dataset.name.includes(search) ||
                    row.dataset.email.includes(search);
                const matchRole = !role || row.dataset.role === role;
                return matchSearch && matchRole;
            });

            // Sort
            visible = [...visible].sort((a, b) => {
                if (sort === 'newest') return b.dataset.created - a.dataset.created;
                if (sort === 'oldest') return a.dataset.created - b.dataset.created;
                if (sort === 'name_asc')  return a.dataset.name.localeCompare(b.dataset.name);
                if (sort === 'name_desc') return b.dataset.name.localeCompare(a.dataset.name);
                return 0;
            });

            // Paginate
            const total = visible.length;
            const totalPages = Math.max(1, Math.ceil(total / perPage));
            if (currentPage > totalPages) currentPage = totalPages;

            const start = (currentPage - 1) * perPage;
            const paged = visible.slice(start, start + perPage);

            // Render
            allRows.forEach(r => r.style.display = 'none');
            document.querySelector('.js-empty')?.remove();

            if (paged.length === 0) {
                const empty = document.createElement('tr');
                empty.className = 'js-empty';
                empty.innerHTML = `<td colspan="6" class="um-empty">No users found</td>`;
                document.getElementById('tbody').appendChild(empty);
            } else {
                paged.forEach((row, i) => {
                    row.style.display = '';
                    row.querySelector('.row-index').textContent = start + i + 1;
                    document.getElementById('tbody').appendChild(row);
                });
            }

            // Update UI
            updateStats();
            updateTableInfo(total, start + 1, Math.min(start + perPage, total));
            renderPagination(currentPage, totalPages);
            document.getElementById('btnClear').style.display = (search || role) ? '' : 'none';
        }

        function updateStats() {
            document.getElementById('statTotal').textContent = allRows.length;
            document.getElementById('statAdmin').textContent = allRows.filter(r => r.dataset.role === 'admin').length;
            document.getElementById('statCashier').textContent = allRows.filter(r => r.dataset.role === 'cashier').length;
        }

        function updateTableInfo(total, from, to) {
            document.getElementById('tableInfo').innerHTML = total === 0
                ? 'No results'
                : `Showing <b>${from}–${to}</b> of <b>${total}</b> users`;
        }

        // ====================== PAGINATION ======================
        function renderPagination(current, total) {
            const container = document.getElementById('pagination');
            container.innerHTML = '';

            if (total <= 1) return;

            const addBtn = (text, page, disabled = false, active = false, icon = false) => {
                const btn = document.createElement('button');
                btn.className = `pg-btn ${active ? 'active' : ''}`;
                btn.disabled = disabled;
                if (icon) {
                    btn.innerHTML = `<span class="material-symbols-outlined">${text}</span>`;
                } else {
                    btn.textContent = text;
                }
                if (!disabled) btn.addEventListener('click', () => { currentPage = page; render(); });
                container.appendChild(btn);
            };

            addBtn('chevron_left', current - 1, current === 1, false, true);

            // Simple page numbers
            for (let i = 1; i <= total; i++) {
                if (total > 7 && i > 2 && i < total - 1 && Math.abs(i - current) > 1) {
                    if (i === 3 || i === total - 2) {
                        const span = document.createElement('span');
                        span.className = 'pg-ellipsis';
                        span.textContent = '…';
                        container.appendChild(span);
                    }
                    continue;
                }
                addBtn(i, i, false, i === current);
            }

            addBtn('chevron_right', current + 1, current === total, false, true);
        }

        // ====================== EVENT LISTENERS ======================
        function initEventListeners() {
            // Create User
            document.getElementById('btnCreateUser').addEventListener('click', () => {
                // Reset form logic...
                document.getElementById('userModal').classList.add('active');
            });

            // Filters
            ['searchInput', 'roleFilter', 'sortFilter', 'perPage'].forEach(id => {
                const el = document.getElementById(id);
                if (el) el.addEventListener('input', () => { currentPage = 1; render(); });
                if (el) el.addEventListener('change', () => { currentPage = 1; render(); });
            });

            // Clear
            document.getElementById('btnClear').addEventListener('click', () => {
                document.getElementById('searchInput').value = '';
                document.getElementById('roleFilter').value = '';
                document.getElementById('sortFilter').value = 'newest';
                currentPage = 1;
                render();
            });

            // Edit & Delete (delegation)
            document.addEventListener('click', e => {
                if (e.target.closest('.btn-edit')) {
                    const btn = e.target.closest('.btn-edit');
                    const user = JSON.parse(btn.dataset.user);
                    // Open edit modal with user data...
                }
                if (e.target.closest('.btn-delete')) {
                    // Delete logic...
                }
            });
        }

        // Initialize
        initEventListeners();
        render();

    })();
</script>
@endpush
