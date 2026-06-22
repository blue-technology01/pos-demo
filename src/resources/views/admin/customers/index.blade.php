@extends('layouts.app')

@push('styles')
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600&display=swap" rel="stylesheet" />
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@tabler/icons-webfont@latest/dist/tabler-icons.min.css" />
    <link rel="stylesheet" href="{{ asset('assets/css/dashboard/customer/customer.css') }}">
@endpush

@section('title', 'Customer List')

@section('content')

<div class="customer-list-container">

    {{-- ── Page Title ── --}}
    <h1>Customer list</h1>

    {{-- ── Alert ── --}}
    <x-alert />

    {{-- ── Actions Bar ── --}}
    <div class="customer-list-actions">

        {{-- Search --}}
        <form action="{{ route('admin.customers.index') }}" method="GET" class="customer-search-form">
            <input
                type="text"
                name="search"
                value="{{ request('search') }}"
                placeholder="Search name, phone, email..."
                class="customer-search-input"
                autocomplete="off"
            >
            <button type="submit" class="btn btn-search">
                <i class="ti ti-search" aria-hidden="true"></i> Search
            </button>
            @if (request('search'))
                <a href="{{ route('admin.customers.index') }}" class="btn btn-clear">
                    <i class="ti ti-x" aria-hidden="true"></i> Clear
                </a>
            @endif
        </form>

        {{-- Add Customer --}}
        <button type="button" onclick="openModal('create')" class="btn btn-add-customer">
            <i class="ti ti-plus" aria-hidden="true"></i> Add customer
        </button>

    </div>

    {{-- ── Table Card ── --}}
    <div class="customer-list-card">
        <table class="customer-table">
            <thead>
                <tr>
                    <th>Name</th>
                    <th>Email</th>
                    <th>Phone</th>
                    <th>Status</th>
                    <th class="customer-actions-td">Actions</th>
                </tr>
            </thead>
            <tbody id="customer-table-body">
                @forelse ($customers as $customer)
                    <tr>
                        <td class="customer-name-td">{{ $customer->name }}</td>
                        <td>{{ $customer->email ?? '—' }}</td>
                        <td>{{ $customer->phone ?? '—' }}</td>
                        <td>
                            <span class="status-badge {{ $customer->status === 'active' ? 'active' : 'inactive' }}">
                                {{ ucfirst($customer->status) }}
                            </span>
                        </td>
                        <td class="customer-actions-td">
                            <button
                                type="button"
                                class="btn-action-edit"
                                title="Edit customer"
                                onclick="openModal('edit', {
                                    id:     '{{ $customer->id }}',
                                    name:   '{{ addslashes($customer->name) }}',
                                    email:  '{{ addslashes($customer->email) }}',
                                    phone:  '{{ addslashes($customer->phone) }}',
                                    status: '{{ $customer->status }}'
                                })"
                            >
                                <i class="ti ti-pencil" aria-hidden="true"></i> Edit
                            </button>
                            <button
                                type="button"
                                class="btn-action-delete"
                                title="Delete customer"
                                onclick="confirmDelete('{{ $customer->id }}', '{{ addslashes($customer->name) }}')"
                            >
                                <i class="ti ti-trash" aria-hidden="true"></i> Delete
                            </button>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="table-empty-td">No customers found.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
        <div class="table-footer" id="tableFooter">
            <div class="table-info">
                Showing
                {{ $customers->firstItem() ?? 0 }}
                -
                {{ $customers->lastItem() ?? 0 }}
                of
                {{ $customers->total() }}
                units
            </div>
            <div class="pagination">
                {{ $customers->links() }}
            </div>
            {{-- per page --}}
            <form method="GET" action="{{ url()->current() }}">
                @foreach(request()->except('per_page', 'page') as $key => $value)
                    <input type="hidden" name="{{ $key }}" value="{{ $value }}">
                @endforeach
                <select name="per_page" onchange="this.form.submit()">
                    <option value="15" {{ request('per_page') == 15 ? 'selected' : '' }}>15</option>
                    <option value="25" {{ request('per_page') == 25 ? 'selected' : '' }}>25</option>
                    <option value="50" {{ request('per_page') == 50 ? 'selected' : '' }}>50</option>
                </select>
            </form>
        </div>
    </div>
</div>

{{-- ── Customer Modal ── --}}
<div id="customerModal" class="modal" role="dialog" aria-modal="true" aria-labelledby="modalTitle">
    <div class="modal-content">

        <h2 id="modalTitle" class="modal-title">Add customer</h2>

        <form id="customerForm" method="POST" action="">
            @csrf
            <input type="hidden" name="id" id="customer_id">
            <div id="methodContainer"></div>

            <div class="form-group">
                <label for="name">
                    Full name <span class="required-star">*</span>
                </label>
                <input
                    type="text"
                    id="name"
                    name="name"
                    placeholder="Enter full name"
                    required
                    autocomplete="off"
                >
            </div>

            <div class="form-group">
                <label for="email">Email address</label>
                <input
                    type="email"
                    id="email"
                    name="email"
                    placeholder="Enter email address"
                    autocomplete="off"
                >
            </div>

            <div class="form-group">
                <label for="phone">Phone number</label>
                <input
                    type="text"
                    id="phone"
                    name="phone"
                    placeholder="Enter phone number"
                    autocomplete="off"
                >
            </div>

            <div class="form-group">
                <label for="status">
                    Status <span class="required-star">*</span>
                </label>
                <select id="status" name="status" required>
                    <option value="active">Active</option>
                    <option value="inactive">Inactive</option>
                </select>
            </div>

            <div class="modal-footer-actions">
                <button type="button" onclick="closeModal()" class="btn-modal-close">
                    Cancel
                </button>
                <button type="submit" id="saveCustomerBtn" class="btn-modal-save">
                    <i class="ti ti-device-floppy" aria-hidden="true"></i> Save
                </button>
            </div>
        </form>

    </div>
</div>

{{-- ── Delete Form ── --}}
<form id="deleteForm" method="POST" action="" style="display:none">
    @csrf
    @method('DELETE')
</form>

@endsection

@push('scripts')
<script>
(function () {

    const modal           = document.getElementById('customerModal');
    const form            = document.getElementById('customerForm');
    const methodContainer = document.getElementById('methodContainer');
    const modalTitle      = document.getElementById('modalTitle');

    function resetForm() {
        document.getElementById('customer_id').value = '';
        document.getElementById('name').value        = '';
        document.getElementById('email').value       = '';
        document.getElementById('phone').value       = '';
        document.getElementById('status').value      = 'active';
        methodContainer.innerHTML = '';
    }

    window.openModal = function (mode, data = null) {
        resetForm();

        if (mode === 'create') {
            modalTitle.textContent = 'Add new customer';
            form.action = '{{ route('admin.customers.store') }}';
        }

        if (mode === 'edit' && data) {
            modalTitle.textContent = 'Edit customer info';

            const updateUrl = '{{ route('admin.customers.update', ':id') }}';
            form.action = updateUrl.replace(':id', data.id);
            methodContainer.innerHTML = '<input type="hidden" name="_method" value="PUT">';

            document.getElementById('customer_id').value = data.id;
            document.getElementById('name').value        = data.name ?? '';
            document.getElementById('email').value       = (data.email && data.email !== 'null') ? data.email : '';
            document.getElementById('phone').value       = (data.phone && data.phone !== 'null') ? data.phone : '';
            document.getElementById('status').value      = data.status ?? 'active';
        }

        modal.style.display = 'flex';
        document.getElementById('name').focus();
    };

    window.closeModal = function () {
        modal.style.display = 'none';
    };

    // Close on backdrop click
    modal.addEventListener('click', function (e) {
        if (e.target === modal) closeModal();
    });

    // Close on Escape key
    document.addEventListener('keydown', function (e) {
        if (e.key === 'Escape' && modal.style.display === 'flex') closeModal();
    });

    // ── Delete ─────────────────────────────────────────────────────────────
    window.confirmDelete = function (id, name) {
        if (!confirm(`Are you sure you want to delete "${name}"?`)) return;
        const deleteForm = document.getElementById('deleteForm');
        deleteForm.action = `/admin/customers/${id}`;
        deleteForm.submit();
    };

})();
</script>
@endpush
