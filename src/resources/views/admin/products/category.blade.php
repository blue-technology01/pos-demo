@extends('layouts.app')

@push('styles')
    <link rel="stylesheet" href="{{ asset('assets/css/dashboard/product/category.css') }}" data-turbo-track="reload">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@tabler/icons-webfont@latest/dist/tabler-icons.min.css" />
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined" />
@endpush

@section('title', 'Product Category')

@section('content')
<div class="page">
    <x-alert/>
    {{-- Toolbar --}}
    <div class="toolbar">
        <div class="toolbar-left" style="display: flex;justify-content: space-between" >
            <form method="GET" action="{{ url()->current() }}" style="display:flex; gap:10px;">
                <input
                    type="text"
                    name="search"
                    value="{{ request('search') }}"
                    placeholder="Search categories..."
                >
                <select name="status" class="border rounded px-3 py-2">
                    <option value="">All Status</option>
                    <option value="active" {{ request('status') == 'active' ? 'selected' : '' }}>Active</option>
                    <option value="inactive" {{ request('status') == 'inactive' ? 'selected' : '' }}>Inactive</option>
                </select>
                <select name="sort">
                    <option value="newest" {{ request('sort') == 'newest' ? 'selected' : '' }}>Newest first</option>
                    <option value="oldest" {{ request('sort') == 'oldest' ? 'selected' : '' }}>Oldest first</option>
                    <option value="name_asc" {{ request('sort') == 'name_asc' ? 'selected' : '' }}>Name A-Z</option>
                    <option value="name_desc" {{ request('sort') == 'name_desc' ? 'selected' : '' }}>Name Z-A</option>
                    <option value="code_asc" {{ request('sort') == 'code_asc' ? 'selected' : '' }}>Code A-Z</option>
                </select>
                <button type="submit" onclick="showLoader()" >Filter</button>
            </form>
            <button
                type="button"
                id="open-create-modal"
                class="btn-add"
            >
                <i class="ti ti-plus"></i>
                Add Category
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
                        {{-- <td style="color:#9ca3af">{{ Str::limit($category->description, 50) ?? '—' }}</td> --}}
                        <td style="color: #9ca3af">{{ $category->description ? Str::limit($category->description, 50) : '—' }}</td>
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
                            <button class="btn-edit open-edit-modal"
                                    data-code="{{ $category->code }}"
                                    data-name="{{ $category->name }}"
                                    data-description="{{ $category->description ?? '' }}"
                                    data-status="{{ $category->status }}"
                                    title="Edit Category">
                                Edit
                            </button>

                            <button class="btn-delete delete-btn"
                                    data-code="{{ $category->code }}"
                                    data-name="{{ $category->name }}"
                                    title="Delete Category">
                                Delete
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
        {{-- pagination --}}
        <div class="table-footer" style="width: 100%; display: flex; justify-content: space-between" id="tableFooter">
            <span class="table-footer-left">
                <div class="table-info">
                    showing
                    {{ $categories->firstItem() ?? 0 }}
                    -
                    {{ $categories->lastItem() ?? 0 }}
                    of
                    {{ $categories->total() }}
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
                {{ $categories->links('vendor.pagination.numbers-only') }}
            </div>
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
                <button type="submit" class="btn-submit" onclick="showLoader()" >
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
                <button type="submit" class="btn-submit" onclick="showLoader()" >
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
    /* Initial render */
    render();
})();
</script>
@endpush
