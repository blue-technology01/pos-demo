@extends('layouts.app')

@push('styles')
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600&display=swap" rel="stylesheet" />
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@tabler/icons-webfont@latest/dist/tabler-icons.min.css" />
    <link rel="stylesheet" href="{{ asset('assets/css/dashboard/product/unit.css') }}" data-turbo-track="reload">
@endpush

@section('title', 'Unit Management')

@section('content')

<div class="page">
    {{-- <x-alert/> --}}
    {{-- ── Toolbar ── --}}
    <div class="toolbar">
        <div class="toolbar-left" >
            <form method="GET" action="{{ url()->current() }}" style="display:flex; gap:10px;">
                {{-- search --}}
                <input
                    type="text"
                    name="search"
                    value="{{ request('search') }}"
                    placeholder="Search categories..."
                >
                {{-- status --}}
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
                <button type="submit" class="btn-filter" onclick="showLoader()">Filter</button>
            </form>
        </div>
        <div class="toolbar-right">
            <button type="button" id="open-create-modal" class="btn-add">
                <i class="ti ti-plus"></i> Add unit
            </button>
        </div>
    </div>

    {{-- ── Table card ── --}}
    <div class="table-card">
        <div class="table-wrap">
            <table>
                <thead>
                    <tr>
                        <th data-col="code">
                            Code <i class="ti ti-selector sort-icon" aria-hidden="true"></i>
                        </th>
                        <th data-col="name">
                            Unit name <i class="ti ti-selector sort-icon" aria-hidden="true"></i>
                        </th>
                        <th data-col="status">
                            Status <i class="ti ti-selector sort-icon" aria-hidden="true"></i>
                        </th>
                        <th data-col="created">
                            Created <i class="ti ti-selector sort-icon" aria-hidden="true"></i>
                        </th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody id="categoryTableBody">
                    @forelse ($uoms as $uom)
                        <tr
                            data-code="{{ strtolower($uom->code) }}"
                            data-name="{{ strtolower($uom->name) }}"
                            data-status="{{ $uom->status }}"
                            data-created="{{ $uom->created_at?->timestamp ?? 0 }}"
                        >
                            <td>
                                <div style="display:flex;align-items:center;gap:7px">
                                    <span style="width:28px;height:28px;border-radius:8px;background:#eff6ff;display:inline-flex;align-items:center;justify-content:center;flex-shrink:0">
                                        <i class="ti ti-tag" style="color:#2563eb;font-size:14px" aria-hidden="true"></i>
                                    </span>
                                    <strong>{{ $uom->code }}</strong>
                                </div>
                            </td>
                            <td>{{ $uom->name }}</td>
                            <td>
                                <span class="badge badge-{{ $uom->status }}">
                                    @if($uom->status === 'active')
                                        <i class="ti ti-circle-check" aria-hidden="true" style="font-size:11px"></i>
                                    @else
                                        <i class="ti ti-circle-x" aria-hidden="true" style="font-size:11px"></i>
                                    @endif
                                    {{ ucfirst($uom->status) }}
                                </span>
                            </td>
                            <td style="color:#9ca3af">
                                <div style="display:flex;align-items:center;gap:6px">
                                    <i class="ti ti-calendar" aria-hidden="true" style="font-size:13px"></i>
                                    {{ $uom->created_at?->format('d M Y') ?? '—' }}
                                </div>
                            </td>
                            <td class="actions-cell">
                                <button
                                    class="btn-edit open-edit-modal"
                                    data-code="{{ $uom->code }}"
                                    data-name="{{ $uom->name }}"
                                    data-status="{{ $uom->status }}"
                                    title="Edit unit"
                                    type="button"
                                >
                                    <i class="ti ti-pencil" aria-hidden="true"></i> Edit
                                </button>
                                <button
                                    class="btn-delete delete-btn"
                                    data-code="{{ $uom->code }}"
                                    data-name="{{ $uom->name }}"
                                    title="Delete unit"
                                    type="button"
                                >
                                    <i class="ti ti-trash" aria-hidden="true"></i> Delete
                                </button>
                            </td>
                        </tr>
                    @empty
                        <tr class="empty-row">
                            <td colspan="5">
                                <i class="ti ti-inbox" aria-hidden="true" style="font-size:28px;display:block;margin:0 auto 8px;color:#d1d5db"></i>
                                No units found
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
                    {{ $uoms->firstItem() ?? 0 }}
                    -
                    {{ $uoms->lastItem() ?? 0 }}
                    of
                    {{ $uoms->total() }}
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
                {{ $uoms->links('vendor.pagination.numbers-only') }}
            </div>
        </div>
    </div>
</div>

{{-- ══ Create Modal ══ --}}
<div class="modal-overlay" id="createModal" role="dialog" aria-modal="true" aria-labelledby="createModalTitle">
    <div class="modal">
        <div class="modal-header">
            <span class="modal-title" id="createModalTitle">
                <i class="ti ti-tag" aria-hidden="true"></i> Add new unit
            </span>
            <button class="modal-close" data-close="createModal" aria-label="Close">
                <i class="ti ti-x" aria-hidden="true"></i>
            </button>
        </div>
        <form action="{{ route('admin.unit.store') }}" method="POST">
            @csrf
            <div class="modal-body">
                <div class="form-row">
                    <div class="form-group">
                        <label class="form-label" for="create_code">
                            Code <span class="req">*</span>
                        </label>
                        <input
                            type="text"
                            id="create_code"
                            name="code"
                            class="form-control"
                            placeholder="e.g. KG, PCS, BOX"
                            required
                            autocomplete="off"
                        >
                    </div>
                    <div class="form-group">
                        <label class="form-label" for="create_name">
                            Unit name <span class="req">*</span>
                        </label>
                        <input
                            type="text"
                            id="create_name"
                            name="name"
                            class="form-control"
                            placeholder="e.g. Kilogram"
                            required
                            autocomplete="off"
                        >
                    </div>
                    <div class="form-group">
                        <label class="form-label" for="create_status">Status</label>
                        <select id="create_status" name="status" class="form-control">
                            <option value="active">Active</option>
                            <option value="inactive">Inactive</option>
                        </select>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn-cancel" data-close="createModal">
                    <i class="ti ti-x" aria-hidden="true"></i> Cancel
                </button>
                <button type="submit" class="btn-submit" onclick="showLoader()" >
                    <i class="ti ti-device-floppy" aria-hidden="true"></i> Save unit
                </button>
            </div>
        </form>
    </div>
</div>

{{-- ══ Edit Modal ══ --}}
<div class="modal-overlay" id="editModal" role="dialog" aria-modal="true" aria-labelledby="editModalTitle">
    <div class="modal">
        <div class="modal-header">
            <span class="modal-title" id="editModalTitle">
                <i class="ti ti-pencil" aria-hidden="true"></i> Edit unit
            </span>
            <button class="modal-close" data-close="editModal" aria-label="Close">
                <i class="ti ti-x" aria-hidden="true"></i>
            </button>
        </div>
        <form id="editForm" method="POST">
            @csrf
            @method('PUT')
            <input type="hidden" name="code" id="edit_code">
            <div class="modal-body">

                {{-- Unit preview chip --}}
                <div class="edit-chip">
                    <i class="ti ti-tag" aria-hidden="true" style="font-size:20px;color:#9ca3af;flex-shrink:0"></i>
                    <div class="edit-chip-info">
                        <p id="edit_chip_name">—</p>
                        <span id="edit_chip_code">—</span>
                    </div>
                </div>

                <div class="form-group">
                    <label class="form-label" for="edit_name">
                        Unit name <span class="req">*</span>
                    </label>
                    <input
                        type="text"
                        id="edit_name"
                        name="name"
                        class="form-control"
                        required
                        autocomplete="off"
                    >
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label class="form-label" for="edit_status">Status</label>
                        <select id="edit_status" name="status" class="form-control">
                            <option value="active">Active</option>
                            <option value="inactive">Inactive</option>
                        </select>
                    </div>
                </div>

            </div>
            <div class="modal-footer">
                <button type="button" class="btn-cancel" data-close="editModal">
                    <i class="ti ti-x" aria-hidden="true"></i> Cancel
                </button>
                <button type="submit" class="btn-submit" onclick="showLoader()" >
                    <i class="ti ti-device-floppy" aria-hidden="true"></i> Save changes
                </button>
            </div>
        </form>
    </div>
</div>
@endsection

@push('scripts')
<script>
(function () {

    function openModal(id) {
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
        overlay.addEventListener('click', e => {
            if (e.target === overlay) closeModal(overlay.id);
        })
    );

    document.addEventListener('keydown', e => {
        if (e.key === 'Escape')
            document.querySelectorAll('.modal-overlay.open').forEach(m => closeModal(m.id));
    });

    // open create modal
    document.getElementById('open-create-modal').addEventListener('click', () => {
        openModal('createModal');
        document.getElementById('create_code').focus();
    });

    // open edit modal
    document.querySelectorAll('.open-edit-modal').forEach(btn =>
        btn.addEventListener('click', function () {
            const code = this.dataset.code;

            document.getElementById('edit_code').value           = code;
            document.getElementById('edit_name').value           = this.dataset.name;
            document.getElementById('edit_status').value         = this.dataset.status;
            document.getElementById('edit_chip_name').textContent = this.dataset.name;
            document.getElementById('edit_chip_code').textContent = code;
            document.getElementById('editForm').action =
                '{{ route("admin.unit.update", ":code") }}'.replace(':code', code);

            openModal('editModal');
            document.getElementById('edit_name').focus();
        })
    );
    // remove
    document.querySelectorAll('.delete-btn').forEach(btn =>
        btn.addEventListener('click', function () {
            const code = this.dataset.code;
            const name = this.dataset.name;

            if (!confirm(`Delete unit "${name}" (${code})?\n\nThis action cannot be undone.`)) return;

            const form    = document.createElement('form');
            form.method   = 'POST';
            form.action   = '{{ route("admin.unit.destroy", ":code") }}'.replace(':code', code);
            form.style.display = 'none';

            form.innerHTML = `
                <input type="hidden" name="_token" value="{{ csrf_token() }}">
                <input type="hidden" name="_method" value="DELETE">
            `;

            document.body.appendChild(form);
            form.submit();
        })
    );
    document.getElementById('btnClear').addEventListener('click', () => {
        document.getElementById('searchInput').value  = '';
        document.getElementById('filterStatus').value = '';
        currentPage = 1;
        render();
    });
    render();

})();
</script>
@endpush
