@extends('layouts.app')

@push('styles')
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined" />
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&family=Koh+Santepheap:wght@300;400;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('assets/css/dashboard/customer/customer.css') }}">
@endpush

@section('title', 'Customer List')
@section('content')
<div class="customer-list-container">
    <h1>Customer List</h1>

    {{-- message from component --}}
    <x-alert />

    <div class="customer-list-actions">
        <form action="{{ route('admin.customers.index') }}" method="GET" class="customer-search-form">
            <input type="text" name="search" value="{{ request('search') }}" placeholder="Search name, phone, email..." class="form-control customer-search-input">
            <button type="submit" class="btn btn-secondary btn-search">Search</button>
            @if(request('search'))
                <a href="{{ route('admin.customers.index') }}" class="btn btn-light btn-clear">Clear</a>
            @endif
        </form>

        <button onclick="openModal('create')" class="btn btn-primary btn-add-customer">
            Add Customer
        </button>
    </div>

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
                @forelse($customers as $customer)
                    <tr>
                        <td class="customer-name-td">{{ $customer->name }}</td>
                        <td>{{ $customer->email ?? '---' }}</td>
                        <td>{{ $customer->phone ?? '---' }}</td>
                        <td>
                            <span class="status-badge {{ $customer->status == 'active' ? 'active' : 'inactive' }}">
                                {{ ucfirst($customer->status) }}
                            </span>
                        </td>
                        <td class="customer-actions-td">
                        <button class="btn-action-edit" title="Edit Customer"
                            onclick="openModal('edit', { id: '{{ $customer->id }}', name: '{{ $customer->name }}', email: '{{ $customer->email }}', phone: '{{ $customer->phone }}', status: '{{ $customer->status }}' })">
                            Edit
                        </button>

                        <button class="btn-action-delete" title="Delete Customer"
                            onclick="confirmDelete('{{ $customer->id }}', '{{ $customer->name }}')">
                           Delete
                    </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="table-empty-td">No customers found.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="pagination-container">
        {{ $customers->links() }}
    </div>
</div>

<div id="customerModal" class="modal">
    <div class="modal-content">
        <h2 id="modalTitle" class="modal-title">Add Customer</h2>

        <form id="customerForm" method="POST" action="">
            @csrf
            <input type="hidden" name="id" id="customer_id">
            <div id="methodContainer"></div>

            <div class="form-group">
                <label for="name">Full Name <span class="required-star">*</span></label>
                <input type="text" id="name" name="name" placeholder="Enter name" required>
            </div>
            <div class="form-group">
                <label for="email">Email Address</label>
                <input type="email" id="email" name="email" placeholder="Enter email">
            </div>
            <div class="form-group">
                <label for="phone">Phone Number</label>
                <input type="text" id="phone" name="phone" placeholder="Enter phone number">
            </div>

            <div class="form-group">
                <label for="status">Status <span class="required-star">*</span></label>
                <select id="status" name="status" required>
                    <option value="active">Active</option>
                    <option value="inactive">Inactive</option>
                </select>
            </div>

            <div class="modal-footer-actions">
                <button type="button" onclick="closeModal()" class="btn btn-secondary btn-modal-close">Close</button>
                <button type="submit" id="saveCustomerBtn" class="btn btn-success btn-modal-save">Save</button>
            </div>
        </form>
    </div>
</div>

<form id="deleteForm" method="POST" action="" style="display: none;">
    @csrf
    @method('DELETE')
</form>
@endsection

@push('scripts')
<script>
    function openModal(mode, data = null) {
        const modal = document.getElementById("customerModal");
        const form = document.getElementById("customerForm");
        const methodContainer = document.getElementById("methodContainer");
        const title = document.getElementById("modalTitle");

        document.getElementById("customer_id").value = "";
        document.getElementById("name").value = "";
        document.getElementById("email").value = "";
        document.getElementById("phone").value = "";
        document.getElementById("status").value = "active";
        methodContainer.innerHTML = "";

        if (mode === 'create') {
            title.textContent = "Add New Customer";
            form.action = "{{ route('admin.customers.store') }}";
        } else if (mode === 'edit' && data) {
            title.textContent = " Edit Customer Info";

            let updateUrl = "{{ route('admin.customers.update', ':id') }}";
            form.action = updateUrl.replace(':id', data.id);
            methodContainer.innerHTML = `<input type="hidden" name="_method" value="PUT">`;

            document.getElementById("customer_id").value = data.id;
            document.getElementById("name").value = data.name;
            document.getElementById("email").value = (data.email && data.email !== 'null') ? data.email : '';
            document.getElementById("phone").value = (data.phone && data.phone !== 'null') ? data.phone : '';
            document.getElementById("status").value = data.status ? data.status : 'active';
        }

        modal.style.display = "flex";
    }

    function closeModal() {
        document.getElementById("customerModal").style.display = "none";
    }

    function confirmDelete(id, name) {
        if (confirm(`Are you sure you want to delete "${name}"?`)) {
            const deleteForm = document.getElementById("deleteForm");
            deleteForm.action = `/admin/customers/${id}`;
            deleteForm.submit();
        }
    }
</script>
@endpush
