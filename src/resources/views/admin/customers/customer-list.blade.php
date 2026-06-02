@extends('layouts.app')
@push('styles')
<link rel="stylesheet" href="{{ asset('assets/css/dashboard/customer/customer.css') }}">
@endpush

@section('title', 'Customer List')
@section('content')
<div class="customer-list-container">
    <h1>Customer List</h1>

    <div class="customer-list-actions">
        <input type="text" id="customerSearchInput" placeholder="Search customers..." class="form-control">

        <button onclick="openModal()" class="btn btn-primary">
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
                    <th>Address</th>
                    <th>Status</th>
                    <th>Actions</th>
                </tr>
            </thead>

            <tbody id="customer-table-body">
                <tr>
                    <td>John Doe</td>
                    <td>john.doe@example.com</td>
                    <td>+1 234 567 890</td>
                    <td>123 Main St</td>
                    <td>Active</td>
                    <td>
                        <button class="btn btn-primary">Edit</button>
                        <button class="btn btn-danger">Delete</button>
                    </td>
                </tr>
            </tbody>
        </table>
    </div>
</div>

<!-- ✅ MODAL OUTSIDE -->
<div id="customerModal" class="modal" style="display:none;">
    <div class="modal-content">

        <h2>Add Customer</h2>

        <input type="text" id="name" placeholder="Name">
        <input type="email" id="email" placeholder="Email">
        <input type="text" id="phone" placeholder="Phone">
        <input type="text" id="address" placeholder="Address">

        <button id="saveCustomerBtn" class="btn btn-success">Save</button>
        <button onclick="closeModal()" class="btn btn-secondary">Close</button>

    </div>
</div>
@endsection
@push('scripts')
    <script>
                
        // --------------------
        // STATIC DATA
        // --------------------
        let customers = [
            {
                name: "John Doe",
                email: "john.doe@example.com",
                phone: "+1 234 567 890",
                address: "123 Main St, Anytown, USA",
                status: "Active"
            },
            {
                name: "Jane Smith",
                email: "jane.smith@example.com",
                phone: "+1 987 654 321",
                address: "456 Elm St, Othertown, USA",
                status: "Inactive"
            }
        ];

        // --------------------
        // RENDER TABLE
        // --------------------
        function renderTable(data) {
            const tbody = document.getElementById("customer-table-body");
            tbody.innerHTML = "";

            data.forEach((c, index) => {
                tbody.innerHTML += `
                    <tr>
                        <td>${c.name}</td>
                        <td>${c.email}</td>
                        <td>${c.phone}</td>
                        <td>${c.address}</td>
                        <td>${c.status}</td>
                        <td>
                            <button class="btn btn-sm btn-primary" onclick="editCustomer(${index})">Edit</button>
                            <button class="btn btn-sm btn-danger" onclick="deleteCustomer(${index})">Delete</button>
                        </td>
                    </tr>
                `;
            });
        }

        // first load
        renderTable(customers);

        // --------------------
        // SEARCH
        // --------------------
        document.getElementById("customerSearchInput").addEventListener("input", function () {
            let keyword = this.value.toLowerCase();

            let filtered = customers.filter(c =>
                c.name.toLowerCase().includes(keyword) ||
                c.email.toLowerCase().includes(keyword) ||
                c.phone.includes(keyword)
            );

            renderTable(filtered);
        });

        // --------------------
        // OPEN MODAL
        // --------------------
        document.getElementById("addCustomerBtn").addEventListener("click", function () {
            document.getElementById("customerModal").style.display = "block";
        });

        // --------------------
        // CLOSE MODAL
        // --------------------
        function closeModal() {
            document.getElementById("customerModal").style.display = "none";
        }

        // --------------------
        // SAVE CUSTOMER
        // --------------------
        document.getElementById("saveCustomerBtn").addEventListener("click", function () {

            let name = document.getElementById("name").value;
            let email = document.getElementById("email").value;
            let phone = document.getElementById("phone").value;
            let address = document.getElementById("address").value;

            customers.push({
                name,
                email,
                phone,
                address,
                status: "Active"
            });

            renderTable(customers);
            closeModal();

            // clear
            document.getElementById("name").value = "";
            document.getElementById("email").value = "";
            document.getElementById("phone").value = "";
            document.getElementById("address").value = "";
        });

        // --------------------
        // DELETE
        // --------------------
        function deleteCustomer(index) {
            customers.splice(index, 1);
            renderTable(customers);
        }

        // --------------------
        // EDIT (simple demo)
        // --------------------
        function editCustomer(index) {
            let c = customers[index];

            document.getElementById("name").value = c.name;
            document.getElementById("email").value = c.email;
            document.getElementById("phone").value = c.phone;
            document.getElementById("address").value = c.address;

            document.getElementById("customerModal").style.display = "block";

            deleteCustomer(index);
        }
    </script>
{{-- <script src="{{ asset('assets/js/dashboard/customer/customer.js') }}"></script> --}}
@endpush