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

    if (!tbody) {
        console.warn("customer-table-body not found");
        return;
    }

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
                    <button class="btn btn-sm btn-primary" onclick="editCustomer(${index})">
                        Edit
                    </button>

                    <button class="btn btn-sm btn-danger" onclick="deleteCustomer(${index})">
                        Delete
                    </button>
                </td>
            </tr>
        `;
    });
}

// --------------------
// SEARCH
// --------------------
function initializeSearch() {

    const searchInput = document.getElementById("customerSearchInput");

    if (!searchInput) {
        return;
    }

    searchInput.addEventListener("input", function () {

        const keyword = this.value.toLowerCase();

        const filtered = customers.filter(c =>
            c.name.toLowerCase().includes(keyword) ||
            c.email.toLowerCase().includes(keyword) ||
            c.phone.toLowerCase().includes(keyword)
        );

        renderTable(filtered);
    });
}

// --------------------
// OPEN MODAL
// --------------------
function initializeAddCustomerButton() {

    const addBtn = document.getElementById("addCustomerBtn");

    if (!addBtn) {
        return;
    }

    addBtn.addEventListener("click", function () {

        const modal = document.getElementById("customerModal");

        if (modal) {
            modal.style.display = "block";
        }
    });
}

// --------------------
// CLOSE MODAL
// --------------------
function closeModal() {

    const modal = document.getElementById("customerModal");

    if (modal) {
        modal.style.display = "none";
    }
}

// --------------------
// SAVE CUSTOMER
// --------------------
function initializeSaveCustomer() {

    const saveBtn = document.getElementById("saveCustomerBtn");

    if (!saveBtn) {
        return;
    }

    saveBtn.addEventListener("click", function () {

        const name = document.getElementById("name");
        const email = document.getElementById("email");
        const phone = document.getElementById("phone");
        const address = document.getElementById("address");

        if (!name || !email || !phone || !address) {
            return;
        }

        customers.push({
            name: name.value,
            email: email.value,
            phone: phone.value,
            address: address.value,
            status: "Active"
        });

        renderTable(customers);

        closeModal();

        name.value = "";
        email.value = "";
        phone.value = "";
        address.value = "";
    });
}

// --------------------
// DELETE
// --------------------
function deleteCustomer(index) {

    customers.splice(index, 1);

    renderTable(customers);
}

// --------------------
// EDIT
// --------------------
function editCustomer(index) {

    const customer = customers[index];

    const name = document.getElementById("name");
    const email = document.getElementById("email");
    const phone = document.getElementById("phone");
    const address = document.getElementById("address");
    const modal = document.getElementById("customerModal");

    if (!name || !email || !phone || !address || !modal) {
        return;
    }

    name.value = customer.name;
    email.value = customer.email;
    phone.value = customer.phone;
    address.value = customer.address;

    modal.style.display = "block";

    deleteCustomer(index);
}

// --------------------
// INITIALIZE
// --------------------
document.addEventListener("DOMContentLoaded", function () {

    console.log("Customer JS Loaded");

    renderTable(customers);

    initializeSearch();

    initializeAddCustomerButton();

    initializeSaveCustomer();
});
