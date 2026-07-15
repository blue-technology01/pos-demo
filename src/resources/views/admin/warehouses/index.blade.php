@extends('layouts.app')

@push('styles')
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600&display=swap" rel="stylesheet" />
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@tabler/icons-webfont@2.47.0/dist/tabler-icons.min.css" />
    <link rel="stylesheet" href="{{ asset('assets/css/dashboard/warehouse/index.css') }}">
@endpush

@section('content')

<x-alert />

<div class="container">

    {{-- ── Header ── --}}
    <div>
        <h2>Warehouses</h2>
        <button
            type="button"
            class="btn btn-primary"
            onclick="document.getElementById('createWarehouseModal').style.display='flex'"
        >
            <i class="ti ti-plus" aria-hidden="true"></i> Add new
        </button>
    </div>

    {{-- ── Filters ── --}}
    <div class="filters-bar" style="display:flex;gap:.5rem;flex-wrap:wrap;align-items:center;margin:1rem 0;">
        <form method="GET" action="{{ route('admin.warehouses.index') }}" style="display:flex;gap:.5rem;flex-wrap:wrap;align-items:center;">
            {{-- keep current per_page when filtering --}}
            <input type="hidden" name="per_page" value="{{ request('per_page', 15) }}">

            <input
                type="text"
                name="search"
                value="{{ request('search') }}"
                class="form-control"
                placeholder="Search name, location, phone..."
                autocomplete="off"
            >

            <select name="is_active" class="form-control">
                <option value="">All status</option>
                <option value="1" {{ request('is_active') === '1' ? 'selected' : '' }}>Active</option>
                <option value="0" {{ request('is_active') === '0' ? 'selected' : '' }}>Inactive</option>
            </select>

            <button type="submit" class="btn btn-secondary">
                <i class="ti ti-filter" aria-hidden="true"></i> Filter
            </button>

            @if(request()->anyFilled(['search', 'is_active']))
                <a href="{{ route('admin.warehouses.index') }}" class="btn btn-outline">
                    <i class="ti ti-x" aria-hidden="true"></i> Clear
                </a>
            @endif
        </form>
    </div>

    {{-- ── Table ── --}}
    <div class="table-wrap">
        <table class="table">
            <thead>
                <tr>
                    <th>Name</th>
                    <th>Location</th>
                    <th>Phone number</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($warehouses as $warehouse)
                    <tr>
                        <td>{{ $warehouse->name }}</td>
                        <td>{{ $warehouse->location ?? '—' }}</td>
                        <td>{{ $warehouse->phone ?? '—' }}</td>
                        <td>
                            {{-- Edit opens a modal instead of a broken route --}}
                            <button
                                type="button"
                                class="btn btn-sm btn-primary"
                                title="Edit warehouse"
                                onclick="openEditWarehouseModal({{ $warehouse->id }}, '{{ addslashes($warehouse->name) }}', '{{ addslashes($warehouse->location ?? '') }}', '{{ addslashes($warehouse->phone ?? '') }}')"
                            >
                                <i class="ti ti-pencil" aria-hidden="true"></i> Edit
                            </button>

                            <form
                                action="{{ route('admin.warehouses.destroy', $warehouse->id) }}"
                                method="POST"
                                style="display:inline"
                                onsubmit="return confirm('Delete warehouse \'{{ addslashes($warehouse->name) }}\'?')"
                            >
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-sm btn-danger" title="Delete warehouse">
                                    <i class="ti ti-trash" aria-hidden="true"></i> Delete
                                </button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="4" style="text-align:center;padding:2.5rem;color:var(--color-text-secondary,#6b7280)">
                            <i class="ti ti-building-warehouse" aria-hidden="true" style="font-size:28px;display:block;margin:0 auto 8px;color:#d1d5db"></i>
                            No warehouses found.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>

        {{-- pagination --}}
        <div class="table-footer" id="tableFooter">
            <span class="table-footer-left">
                <div class="table-info">
                    showing
                    {{ $warehouses->firstItem() ?? 0 }}
                    -
                    {{ $warehouses->lastItem() ?? 0 }}
                    of
                    {{ $warehouses->total() }}
                    Warehouse
                </div>

                {{-- per page --}}
                <form method="GET" action="{{ request()->url() }}">
                    @foreach(request()->except('per_page', 'page') as $key => $value)
                        <input type="hidden" name="{{ $key }}" value="{{ $value }}">
                    @endforeach

                    <select name="per_page" onchange="this.form.submit()">
                        <option value="15" {{ request('per_page', 15) == 15 ? 'selected' : '' }}>15</option>
                        <option value="25" {{ request('per_page') == 25 ? 'selected' : '' }}>25</option>
                        <option value="50" {{ request('per_page') == 50 ? 'selected' : '' }}>50</option>
                    </select>
                </form>
            </span>

            <div class="pagination">
                {{ $warehouses->links('vendor.pagination.numbers-only') }}
            </div>
        </div>
    </div>
</div>

{{-- ── Create Warehouse Modal ── --}}
<div id="createWarehouseModal" class="modal" role="dialog" aria-modal="true" aria-labelledby="createModalTitle">
    <div class="modal-content">

        <span
            class="close-btn"
            onclick="document.getElementById('createWarehouseModal').style.display='none'"
            aria-label="Close"
        >&times;</span>

        <h3 id="createModalTitle">Create new warehouse</h3>

        <form action="{{ route('admin.warehouses.store') }}" method="POST">
            @csrf

            <div class="mb-3">
                <label for="wh_name">Name <span style="color:#dc2626">*</span></label>
                <input
                    type="text"
                    id="wh_name"
                    name="name"
                    class="form-control"
                    placeholder="e.g. Main Warehouse"
                    required
                    autocomplete="off"
                >
            </div>

            <div class="mb-3">
                <label for="wh_location">Location</label>
                <input
                    type="text"
                    id="wh_location"
                    name="location"
                    class="form-control"
                    placeholder="e.g. Phnom Penh"
                    autocomplete="off"
                >
            </div>

            <div class="mb-3">
                <label for="wh_phone">Phone number</label>
                <input
                    type="text"
                    id="wh_phone"
                    name="phone"
                    class="form-control"
                    placeholder="e.g. 012 345 678"
                    autocomplete="off"
                >
            </div>

            <button type="submit" class="btn btn-success mt-2">
                <i class="ti ti-device-floppy" aria-hidden="true"></i> Save warehouse
            </button>
        </form>
    </div>
</div>

{{-- ── Edit Warehouse Modal ── --}}
<div id="editWarehouseModal" class="modal" role="dialog" aria-modal="true" aria-labelledby="editModalTitle">
    <div class="modal-content">

        <span
            class="close-btn"
            onclick="document.getElementById('editWarehouseModal').style.display='none'"
            aria-label="Close"
        >&times;</span>

        <h3 id="editModalTitle">Edit warehouse</h3>

        <form id="editWarehouseForm" action="" method="POST">
            @csrf
            @method('PUT')

            <div class="mb-3">
                <label for="edit_wh_name">Name <span style="color:#dc2626">*</span></label>
                <input
                    type="text"
                    id="edit_wh_name"
                    name="name"
                    class="form-control"
                    placeholder="e.g. Main Warehouse"
                    required
                    autocomplete="off"
                >
            </div>

            <div class="mb-3">
                <label for="edit_wh_location">Location</label>
                <input
                    type="text"
                    id="edit_wh_location"
                    name="location"
                    class="form-control"
                    placeholder="e.g. Phnom Penh"
                    autocomplete="off"
                >
            </div>

            <div class="mb-3">
                <label for="edit_wh_phone">Phone number</label>
                <input
                    type="text"
                    id="edit_wh_phone"
                    name="phone"
                    class="form-control"
                    placeholder="e.g. 012 345 678"
                    autocomplete="off"
                >
            </div>

            <button type="submit" class="btn btn-success mt-2">
                <i class="ti ti-device-floppy" aria-hidden="true"></i> Update warehouse
            </button>
        </form>
    </div>
</div>

@endsection

@push('scripts')
<script>
(function () {
    const createModal = document.getElementById('createWarehouseModal');
    const editModal = document.getElementById('editWarehouseModal');

    // Close on backdrop click
    [createModal, editModal].forEach(function (modal) {
        modal.addEventListener('click', function (e) {
            if (e.target === modal) modal.style.display = 'none';
        });
    });

    // Close on Escape key
    document.addEventListener('keydown', function (e) {
        if (e.key === 'Escape') {
            if (createModal.style.display !== 'none') createModal.style.display = 'none';
            if (editModal.style.display !== 'none') editModal.style.display = 'none';
        }
    });
})();

// Base URL pattern for building the update action per-warehouse.
const warehouseUpdateUrlTemplate = "{{ route('admin.warehouses.update', ['warehouse' => '__ID__']) }}";

function openEditWarehouseModal(id, name, location, phone) {
    const form = document.getElementById('editWarehouseForm');
    form.action = warehouseUpdateUrlTemplate.replace('__ID__', id);

    document.getElementById('edit_wh_name').value = name || '';
    document.getElementById('edit_wh_location').value = location || '';
    document.getElementById('edit_wh_phone').value = phone || '';

    document.getElementById('editWarehouseModal').style.display = 'flex';
}
</script>
@endpush
