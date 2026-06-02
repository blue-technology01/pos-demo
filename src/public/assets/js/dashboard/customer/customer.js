
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