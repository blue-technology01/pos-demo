@extends('layouts.app')

@push('styles')
    <link rel="stylesheet" href="{{ asset('assets/css/dashboard/product/category.css') }}" data-turbo-track="reload">
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined" />
    <style>
        @keyframes slideIn {
            from {
                transform: translateX(100%);
                opacity: 0;
            }
            to {
                transform: translateX(0);
                opacity: 1;
            }
        }
        @keyframes slideOut {
            from {
                transform: translateX(0);
                opacity: 1;
            }
            to {
                transform: translateX(100%);
                opacity: 0;
            }
        }
    </style>
@endpush

@section('title', 'Product Category')

@section('content')
<div class="page">
    {{-- Toolbar --}}
    <div class="toolbar">
        <div class="toolbar-left">

            {{-- Search --}}
            <div class="search-box">
                <i class="ti ti-search"></i>
                <input type="text" id="searchInput" placeholder="Search categories...">
            </div>

            {{-- Status filter --}}
            <select id="filterStatus" class="filter-select">
                <option value="">All status</option>
                <option value="active">Active</option>
                <option value="inactive">Inactive</option>
            </select>

            {{-- Sort filter --}}
            <select id="filterSort" class="filter-select">
                <option value="newest">Newest first</option>
                <option value="oldest">Oldest first</option>
                <option value="name_asc">Name A–Z</option>
                <option value="name_desc">Name Z–A</option>
                <option value="code_asc">Code A–Z</option>
            </select>

            {{-- Clear filters button --}}
            <button id="btnClear" class="btn-clear">
                <i class="ti ti-x" style="font-size:12px"></i> Clear filters
            </button>

        </div>
        <div class="toolbar-right">
            {{-- Per page --}}
            <div class="per-page-wrap">
                <label for="perPage">Show</label>
                <select id="perPage" class="filter-select" style="width:70px">
                    <option value="10">10</option>
                    <option value="25">25</option>
                    <option value="50">50</option>
                </select>
            </div>

            <button type="button" id="open-create-modal" class="btn-add">
                <i class="ti ti-plus" style="font-size:15px"></i> Add Category
            </button>
        </div>
    </div>

    {{-- Table --}}
    <div class="table-card">
        <div class="table-wrap">
            <table>
                <thead>
                    <tr>
                        <th data-col="code">Code <i class="ti ti-selector sort-icon"></i></th>
                        <th data-col="name">Name <i class="ti ti-selector sort-icon"></i></th>
                        <th>Description</th>
                        <th>Image</th>
                        <th data-col="status">Status <i class="ti ti-selector sort-icon"></i></th>
                        <th data-col="created">Created <i class="ti ti-selector sort-icon"></i></th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody id="categoryTableBody">
                    @forelse ($categories as $category)
                    <tr
                        data-code="{{ strtolower($category->code) }}"
                        data-name="{{ strtolower($category->name) }}"
                        data-description="{{ strtolower($category->description ?? '') }}"
                        data-status="{{ $category->status }}"
                        data-created="{{ $category->created_at->timestamp }}"
                    >
                        <td><strong>{{ $category->code }}</strong></td>
                        <td>{{ $category->name }}</td>
                        <td style="color:#9ca3af">{{ Str::limit($category->description, 50) ?? '—' }}</td>
                        <td>
                            @if ($category->image)
                                <img src="{{ Storage::url($category->image) }}" class="img-thumb" alt="">
                            @else
                                <div class="img-placeholder"><i class="ti ti-photo"></i></div>
                            @endif
                        </td>
                        <td>
                            <span class="badge badge-{{ $category->status }}">
                                {{ ucfirst($category->status) }}
                            </span>
                        </td>
                        <td style="color:#9ca3af">{{ $category->created_at->format('d M Y') }}</td>
                        <td class="actions-cell">
                            <button class="btn-icon btn-edit open-edit-modal"
                                    data-code="{{ $category->code }}"
                                    data-name="{{ $category->name }}"
                                    data-description="{{ $category->description ?? '' }}"
                                    data-status="{{ $category->status }}"
                                    title="Edit Category">
                                <span class="material-symbols-outlined">edit</span>
                            </button>

                            <button class="btn-icon btn-delete delete-btn"
                                    data-code="{{ $category->code }}"
                                    data-name="{{ $category->name }}"
                                    title="Delete Category">
                                <span class="material-symbols-outlined">delete</span>
                            </button>
                        </td>
                    </tr>
                    @empty
                    <tr class="empty-row">
                        <td colspan="7">
                            <i class="ti ti-inbox" style="font-size:24px;display:block;margin:0 auto 8px;color:#d1d5db"></i>
                            No categories found
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{-- Table footer: info + pagination --}}
        <div class="table-footer" id="tableFooter">
            <div class="table-info" id="tableInfo"></div>
            <div class="pagination" id="pagination"></div>
        </div>
    </div>

</div>

{{-- ══ Create Modal ══ --}}
<div class="modal-overlay" id="createModal">
    <div class="modal">
        <div class="modal-header">
            <span class="modal-title"><i class="ti ti-tag"></i> Add new category</span>
            <button class="modal-close" data-close="createModal" aria-label="Close"><i class="ti ti-x"></i></button>
        </div>
        <form action="{{ route('admin.category.store') }}" method="POST" enctype="multipart/form-data">
            @csrf
            <div class="modal-body">
                <div class="form-row">
                    <div class="form-group">
                        <label class="form-label">Code <span class="req">*</span></label>
                        <input type="text" name="code" class="form-control" placeholder="e.g. CAT-001" required>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Status</label>
                        <select name="status" class="form-control">
                            <option value="active">Active</option>
                            <option value="inactive">Inactive</option>
                        </select>
                    </div>
                </div>
                <div class="form-group">
                    <label class="form-label">Name <span class="req">*</span></label>
                    <input type="text" name="name" class="form-control" placeholder="Category name" required>
                </div>
                <div class="form-group">
                    <label class="form-label">Description</label>
                    <textarea name="description" class="form-control" placeholder="Short description (optional)"></textarea>
                </div>
                <div class="form-group">
                    <label class="form-label">Image</label>
                    <label class="file-upload" id="createFileLabel">
                        <i class="ti ti-cloud-upload"></i>
                        <span id="createFileName">Click to upload image</span>
                        <input type="file" name="image" accept="image/*" id="createFileInput">
                    </label>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn-cancel" data-close="createModal">Cancel</button>
                <button type="submit" class="btn-submit">
                    <i class="ti ti-check" style="font-size:14px"></i> Save category
                </button>
            </div>
        </form>
    </div>
</div>

{{-- ══ Edit Modal ══ --}}
<div class="modal-overlay" id="editModal">
    <div class="modal">
        <div class="modal-header">
            <span class="modal-title"><i class="ti ti-edit"></i> Edit category</span>
            <button class="modal-close" data-close="editModal" aria-label="Close"><i class="ti ti-x"></i></button>
        </div>
        <form id="editForm" method="POST" enctype="multipart/form-data">
            @csrf
            @method('PUT')
            <input type="hidden" name="code" id="edit_code">
            <div class="modal-body">
                <div class="edit-chip">
                    <i class="ti ti-tag" style="font-size:18px;color:#9ca3af"></i>
                    <div class="edit-chip-info">
                        <p id="edit_chip_name">—</p>
                        <span id="edit_chip_code">—</span>
                    </div>
                </div>
                <div class="form-group">
                    <label class="form-label">Name <span class="req">*</span></label>
                    <input type="text" name="name" id="edit_name" class="form-control" required>
                </div>
                <div class="form-group">
                    <label class="form-label">Description</label>
                    <textarea name="description" id="edit_description" class="form-control"></textarea>
                </div>
                <div class="form-row">
                    <div class="form-group">
                        <label class="form-label">Image</label>
                        <label class="file-upload" id="editFileLabel">
                            <i class="ti ti-photo"></i>
                            <span id="editFileName">Replace image</span>
                            <input type="file" name="image" accept="image/*" id="editFileInput">
                        </label>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Status</label>
                        <select name="status" id="edit_status" class="form-control">
                            <option value="active">Active</option>
                            <option value="inactive">Inactive</option>
                        </select>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn-cancel" data-close="editModal">Cancel</button>
                <button type="submit" class="btn-submit">
                    <i class="ti ti-device-floppy" style="font-size:14px"></i> Save changes
                </button>
            </div>
        </form>
    </div>
</div>

@endsection

@push('scripts')
<script>
(function () {

    // message when success or error, auto hide after 3s
    function showNotification(message, type = 'info') {
        const colors = {
            success: '#4ade80',
            warning: '#fbbf24',
            error: '#f87171',
            info: '#60a5fa'
        };

        const toast = document.createElement('div');

        Object.assign(toast.style, {
            position: 'fixed',
            top: '20px',
            right: '20px',
            background: colors[type] || '#60a5fa',
            color: 'white',
            padding: '12px 16px',
            borderRadius: '6px',
            zIndex: 99999,
            fontSize: '14px',
            fontWeight: '500',
            boxShadow: '0 2px 10px rgba(0,0,0,0.2)',
            animation: 'slideIn 0.3s ease-out'
        });

        toast.textContent = message;
        document.body.appendChild(toast);

        setTimeout(() => {
            toast.style.animation = 'slideOut 0.3s ease-out';
            toast.addEventListener('animationend', () => toast.remove());
        }, 3000);
    }
    // slide in/out keyframes
    document.addEventListener('DOMContentLoaded', () => {

        @if(session('success'))
            showNotification(@json(session('success')), 'success');
        @endif

        @if(session('error'))
            showNotification(@json(session('error')), 'error');
        @endif
    });

    /* ── Modal ── */
    function openModal(id)  { 
        document.getElementById(id).classList.add('open');    
        document.body.style.overflow = 'hidden'; 
    }
    function closeModal(id) { 
        document.getElementById(id).classList.remove('open'); 
        document.body.style.overflow = ''; 
    }

    document.querySelectorAll('[data-close]').forEach(btn =>
        btn.addEventListener('click', () => closeModal(btn.dataset.close))
    );
    document.querySelectorAll('.modal-overlay').forEach(overlay =>
        overlay.addEventListener('click', e => { if (e.target === overlay) closeModal(overlay.id); })
    );
    document.addEventListener('keydown', e => {
        if (e.key === 'Escape')
            document.querySelectorAll('.modal-overlay.open').forEach(m => closeModal(m.id));
    });

    document.getElementById('open-create-modal').addEventListener('click', () => openModal('createModal'));

    document.querySelectorAll('.open-edit-modal').forEach(btn =>
        btn.addEventListener('click', () => {
            const code = btn.dataset.code;
            document.getElementById('edit_code').value          = code;
            document.getElementById('edit_name').value          = btn.dataset.name;
            document.getElementById('edit_description').value   = btn.dataset.description || '';
            document.getElementById('edit_status').value        = btn.dataset.status;
            document.getElementById('edit_chip_name').textContent = btn.dataset.name;
            document.getElementById('edit_chip_code').textContent = code;
            document.getElementById('editForm').action =
                '{{ route("admin.category.update", ":code") }}'.replace(':code', code);
            openModal('editModal');
        })
    );

    /* File labels */
    function bindFile(inputId, spanId) {
        document.getElementById(inputId).addEventListener('change', function () {
            document.getElementById(spanId).textContent =
                this.files[0] ? this.files[0].name : 'Click to upload image';
        });
    }
    bindFile('createFileInput', 'createFileName');
    bindFile('editFileInput',   'editFileName');

    document.querySelectorAll('.delete-btn').forEach(btn => {
        btn.addEventListener('click', function () {
            const code = this.dataset.code;
            const name = this.dataset.name;

            if (confirm(`Are you sure you want to delete category "${name}" (${code})?\n\nThis action cannot be undone.`)) {
                // Create and submit delete form
                const form = document.createElement('form');
                form.method = 'POST';
                form.action = '{{ route("admin.category.destroy", ":code") }}'.replace(':code', code);
                form.style.display = 'none';

                const csrf = document.createElement('input');
                csrf.type = 'hidden';
                csrf.name = '_token';
                csrf.value = '{{ csrf_token() }}';

                const method = document.createElement('input');
                method.type = 'hidden';
                method.name = '_method';
                method.value = 'DELETE';

                form.appendChild(csrf);
                form.appendChild(method);
                document.body.appendChild(form);
                form.submit();
            }
        });
    });

    /* ── Filter + Sort + Pagination engine ── */
    const allRows  = Array.from(document.querySelectorAll('#categoryTableBody tr[data-name]'));
    const tbody    = document.getElementById('categoryTableBody');
    const infoEl   = document.getElementById('tableInfo');
    const pagEl    = document.getElementById('pagination');

    let currentPage = 1;
    let sortCol     = 'created';
    let sortDir     = 'desc';

    function getFilters() {
        return {
            search:  document.getElementById('searchInput').value.toLowerCase().trim(),
            status:  document.getElementById('filterStatus').value,
            sort:    document.getElementById('filterSort').value,
            perPage: parseInt(document.getElementById('perPage').value, 10),
        };
    }

    function applySort(rows, sort) {
        return [...rows].sort((a, b) => {
            if (sort === 'newest')    return b.dataset.created - a.dataset.created;
            if (sort === 'oldest')    return a.dataset.created - b.dataset.created;
            if (sort === 'name_asc')  return a.dataset.name.localeCompare(b.dataset.name);
            if (sort === 'name_desc') return b.dataset.name.localeCompare(a.dataset.name);
            if (sort === 'code_asc')  return a.dataset.code.localeCompare(b.dataset.code);
            return 0;
        });
    }

    function render() {
        const { search, status, sort, perPage } = getFilters();

        /* Filter */
        let visible = allRows.filter(row => {
            const matchSearch = !search ||
                row.dataset.name.includes(search) ||
                row.dataset.code.includes(search) ||
                row.dataset.description.includes(search);
            const matchStatus = !status || row.dataset.status === status;
            return matchSearch && matchStatus;
        });

        /* Sort */
        visible = applySort(visible, sort);

        const total      = visible.length;
        const totalPages = Math.max(1, Math.ceil(total / perPage));
        if (currentPage > totalPages) currentPage = totalPages;

        const start = (currentPage - 1) * perPage;
        const end   = Math.min(start + perPage, total);
        const paged = visible.slice(start, end);

        /* Render rows */
        allRows.forEach(r => { r.style.display = 'none'; });

        if (paged.length === 0) {
            /* show empty state */
            let empty = tbody.querySelector('.js-empty');
            if (!empty) {
                empty = document.createElement('tr');
                empty.className = 'empty-row js-empty';
                empty.innerHTML = `<td colspan="7">
                    <i class="ti ti-inbox" style="font-size:24px;display:block;margin:0 auto 8px;color:#d1d5db"></i>
                    No categories found
                </td>`;
                tbody.appendChild(empty);
            }
            empty.style.display = '';
        } else {
            const empty = tbody.querySelector('.js-empty');
            if (empty) empty.style.display = 'none';
            paged.forEach(r => {
                r.style.display = '';
                tbody.appendChild(r); /* re-order */
            });
        }

        /* Info */
        if (total === 0) {
            infoEl.innerHTML = 'No results';
        } else {
            infoEl.innerHTML = `Showing <b>${start + 1}–${end}</b> of <b>${total}</b> categories`;
        }

        /* Pagination */
        renderPagination(currentPage, totalPages);

        /* Clear button visibility */
        const hasFilter = search || status;
        document.getElementById('btnClear').classList.toggle('visible', !!hasFilter);
        document.getElementById('filterStatus').classList.toggle('has-filter', !!status);
        document.getElementById('searchInput').style.borderColor = search ? '#111827' : '';
    }

    function renderPagination(page, total) {
        pagEl.innerHTML = '';
        if (total <= 1) return;

        const btn = (label, p, disabled = false, active = false, isIcon = false) => {
            const b = document.createElement('button');
            b.className = 'pg-btn' + (active ? ' active' : '');
            b.disabled  = disabled;
            if (isIcon) b.innerHTML = `<i class="${label}"></i>`;
            else        b.textContent = label;
            if (!disabled) b.addEventListener('click', () => { currentPage = p; render(); });
            return b;
        };

        pagEl.appendChild(btn('ti ti-chevron-left',  page - 1, page === 1,     false, true));

        const pages = getPageNumbers(page, total);
        pages.forEach(p => {
            if (p === '...') {
                const span = document.createElement('span');
                span.className = 'pg-ellipsis';
                span.textContent = '…';
                pagEl.appendChild(span);
            } else {
                pagEl.appendChild(btn(p, p, false, p === page));
            }
        });

        pagEl.appendChild(btn('ti ti-chevron-right', page + 1, page === total, false, true));
    }

    function getPageNumbers(current, total) {
        if (total <= 7) return Array.from({ length: total }, (_, i) => i + 1);
        const pages = [];
        pages.push(1);
        if (current > 3)        pages.push('...');
        for (let p = Math.max(2, current - 1); p <= Math.min(total - 1, current + 1); p++) pages.push(p);
        if (current < total - 2) pages.push('...');
        pages.push(total);
        return pages;
    }

    /* Event listeners */
    ['searchInput', 'filterStatus', 'filterSort', 'perPage'].forEach(id => {
        document.getElementById(id).addEventListener('input', () => { currentPage = 1; render(); });
        document.getElementById(id).addEventListener('change', () => { currentPage = 1; render(); });
    });

    document.getElementById('btnClear').addEventListener('click', () => {
        document.getElementById('searchInput').value  = '';
        document.getElementById('filterStatus').value = '';
        currentPage = 1;
        render();
    });

    /* Initial render */
    render();

})();
</script>
@endpush