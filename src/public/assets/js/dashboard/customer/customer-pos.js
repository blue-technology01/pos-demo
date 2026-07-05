let _allCustomers   = [];
let _customersLoaded = false;
let searchTimeout   = null;

function _esc(str) {
    const d = document.createElement('div');
    d.appendChild(document.createTextNode(str ?? ''));
    return d.innerHTML;
}

function openCustomerFilterPopup() {
    resetSearchModal();
    document.getElementById("customerFilterModal").style.display = "flex";
    document.getElementById("popupSearchInput").focus();
    if (!_customersLoaded) {
        _fetchAllCustomers();
    } else {
        _renderLocalResults("");
    }
}

function closeFilterModal() {
    document.getElementById("customerFilterModal").style.display = "none";
    resetSearchModal();
}

function resetSearchModal() {
    document.getElementById("popupSearchInput").value = "";
    document.getElementById("popupCustomerResult").innerHTML = "";
    clearTimeout(searchTimeout);
}

async function _fetchAllCustomers() {
    const resultContainer = document.getElementById("popupCustomerResult");
    resultContainer.innerHTML = renderLoading();

    try {
        const response = await fetch(`${window.CUSTOMER_SEARCH_URL}?per_page=9999`);
        if (!response.ok) throw new Error("Network error");
        const data = await response.json();

        _allCustomers    = Array.isArray(data) ? data : (data.data ?? []);
        _customersLoaded = true;

        const keyword = document.getElementById("popupSearchInput").value.trim();
        _renderLocalResults(keyword);
    } catch (err) {
        console.error("Error loading customers:", err);
        resultContainer.innerHTML = renderError();
    }
}

function filterCustomersRealTime() {
    clearTimeout(searchTimeout);
    const keyword = document.getElementById("popupSearchInput").value.trim();

    if (_customersLoaded) {
        _renderLocalResults(keyword);
        searchTimeout = setTimeout(() => _serverSearchCustomers(keyword), 400);
        return;
    }

    searchTimeout = setTimeout(() => fetchCustomersFromBackend(keyword), 300);
}

function _renderLocalResults(keyword) {
    const resultContainer = document.getElementById("popupCustomerResult");
    const q = keyword.toLowerCase();

    const filtered = q
        ? _allCustomers.filter(c =>
            (c.name  || '').toLowerCase().includes(q) ||
            (c.phone || '').toLowerCase().includes(q)
          )
        : _allCustomers;

    if (filtered.length === 0 && keyword !== "") {
        resultContainer.innerHTML = renderNotFound(keyword);
    } else if (filtered.length === 0) {
        resultContainer.innerHTML = renderEmpty();
    } else {
        resultContainer.innerHTML = renderCustomerList(filtered);

        //  Attach events safely — no inline onclick with raw data
        resultContainer.querySelectorAll("[data-customer-id]").forEach(el => {
            el.addEventListener("click", () => {
                selectCustomerForPOS(
                    el.dataset.customerId,
                    el.dataset.customerName
                );
            });
        });
    }
}

async function _serverSearchCustomers(keyword) {
    try {
        const response = await fetch(
            `${window.CUSTOMER_SEARCH_URL}?keyword=${encodeURIComponent(keyword)}`
        );
        if (!response.ok) return;
        const data = await response.json();
        const results = Array.isArray(data) ? data : (data.data ?? []);

        const currentKeyword = document.getElementById("popupSearchInput").value.trim();
        if (currentKeyword !== keyword) return;

        results.forEach(incoming => {
            const idx = _allCustomers.findIndex(c => c.id === incoming.id);
            if (idx >= 0) _allCustomers[idx] = incoming;
            else _allCustomers.push(incoming);
        });

        _renderLocalResults(keyword);
    } catch (err) {
        console.error("Background customer search error:", err);
    }
}
function fetchCustomersFromBackend(keyword) {
    const resultContainer = document.getElementById("popupCustomerResult");
    resultContainer.innerHTML = renderLoading();

    fetch(`${window.CUSTOMER_SEARCH_URL}?keyword=${encodeURIComponent(keyword)}`)
        .then(r => {
            if (!r.ok) throw new Error("Network error");
            return r.json();
        })
        .then(customers => {
            const list = Array.isArray(customers) ? customers : (customers.data ?? []);
            if (list.length === 0 && keyword !== "") {
                resultContainer.innerHTML = renderNotFound(keyword);
            } else if (list.length === 0) {
                resultContainer.innerHTML = renderEmpty();
            } else {
                resultContainer.innerHTML = renderCustomerList(list);
            }
        })
        .catch(() => {
            resultContainer.innerHTML = renderError();
        });
}

function renderLoading() {
    return `
        <div class="loading-wrapper">
            <div class="loading-icon">
                <span class="material-symbols-outlined loading-spin">
                    progress_activity
                </span>
            </div>
            <p class="loading-text">Loading customers...</p>
        </div>
    `;
}

function renderEmpty() {
    return `
        <div class="empty-wrapper">
            <div class="empty-icon">
                <span class="material-symbols-outlined">
                    search
                </span>
            </div>
            <p class="empty-text">
                Type a name or phone number to search
            </p>
            <p class="empty-sub">
                Start typing to find customers quickly
            </p>
        </div>
    `;
}

function renderNotFound(keyword) {
    const html = `
        <div class="notfound-wrapper">
            <div class="notfound-icon">
                <span class="material-symbols-outlined">sentiment_dissatisfied</span>
            </div>
            <p class="notfound-title">
                No customer found!
            </p>
            <button
                type="button"
                id="btn-add-new-customer"
                class="notfound-btn"
                data-keyword="${_esc(keyword)}">

                <span class="material-symbols-outlined">person_add</span>
                Create new customer
            </button>

        </div>
    `;
    setTimeout(() => {
        const btn = document.getElementById("btn-add-new-customer");
        if (btn) {
            btn.addEventListener("click", () =>
                triggerAddNewCustomer(btn.dataset.keyword)
            );
        }
    }, 0);
    return html;
}

function renderCustomerList(customers) {
    return customers.map(customer => `
        <div
            class="customer-item"
            data-customer-id="${_esc(String(customer.id))}"
            data-customer-name="${_esc(customer.name)}"
        >
            <div class="customer-left">
                <div class="customer-avatar">
                    ${_esc(customer.name.charAt(0).toUpperCase())}
                </div>
                <div>
                    <p class="customer-name">
                        ${_esc(customer.name)}
                    </p>
                    <p class="customer-phone">
                        <span class="material-symbols-outlined" style="font-size:14px;">
                            phone_iphone
                        </span>
                        ${_esc(customer.phone || 'No phone')}
                    </p>
                </div>
            </div>
            <span class="customer-right">
                Select
                <span class="material-symbols-outlined" style="font-size:16px;">
                    arrow_forward
                </span>
            </span>
        </div>
    `).join("");
}

function selectCustomerForPOS(id, name) {
    window.selectedCustomerId = id;
    const nameDisplay = document.getElementById("selected-customer-name");
    const idDisplay   = document.getElementById("selected-customer-id");
    if (nameDisplay) nameDisplay.innerText = name;
    if (idDisplay)   idDisplay.innerText   = `#C-${String(id).padStart(6, "0")}`;
    //  show clear button after customer selected
    const clearBtn = document.getElementById("clear-customer-btn");
    if (clearBtn) clearBtn.style.display = "inline-flex";
    closeFilterModal();
}

//  New — clear selected customer
function clearSelectedCustomer() {
    window.selectedCustomerId = null;
    const nameDisplay = document.getElementById("selected-customer-name");
    const idDisplay   = document.getElementById("selected-customer-id");
    if (nameDisplay) nameDisplay.innerText = "No customer selected";
    if (idDisplay)   idDisplay.innerText   = "";
    const clearBtn = document.getElementById("clear-customer-btn");
    if (clearBtn) clearBtn.style.display = "none";
}

function triggerAddNewCustomer(passedName) {
    closeFilterModal();
    openCreateCustomerForm(passedName);
}

function openCreateCustomerForm(prefillValue = "") {
    resetCreateForm();
    const overlay = document.getElementById("create-customer-overlay");
    if (!overlay) return;
    overlay.style.display = "flex";
    const isPhone = /^[0-9\s\+\-]+$/.test(prefillValue);
    const inputId = isPhone ? "nc-phone" : "nc-name";
    const el = document.getElementById(inputId);
    if (el) el.value = prefillValue;
    //  focus the prefilled input
    if (el) el.focus();
}

function closeCreateCustomerForm() {
    const overlay = document.getElementById("create-customer-overlay");
    if (overlay) overlay.style.display = "none";
    resetCreateForm();
}

function resetCreateForm() {
    ["nc-name", "nc-phone"].forEach(id => {
        const el = document.getElementById(id);
        if (el) el.value = "";
    });

    const errorBox = document.getElementById("nc-error");
    if (errorBox) { errorBox.style.display = "none"; errorBox.innerText = ""; }
    const submitBtn = document.getElementById("nc-submit-btn");
    if (submitBtn) {
        submitBtn.disabled = false;
        submitBtn.innerHTML = `<span class="material-symbols-outlined">person_add</span> Create & select`;
    }
}

function submitNewCustomer() {
    const name     = document.getElementById("nc-name").value.trim();
    const phone    = document.getElementById("nc-phone").value.trim();
    const errorBox = document.getElementById("nc-error");
    const submitBtn = document.getElementById("nc-submit-btn");
    if (!name) { showCreateError("Full name is required."); return; }
    submitBtn.disabled = true;
    submitBtn.innerHTML = `<span class="material-symbols-outlined">hourglass_top</span> Creating...`;
    errorBox.style.display = "none";
    fetch(window.CUSTOMER_STORE_URL, {
        method: "POST",
        headers: {
            "Content-Type": "application/json",
            "X-CSRF-TOKEN": document.querySelector('meta[name="csrf-token"]').getAttribute("content"),
            "Accept": "application/json",
        },
        body: JSON.stringify({ name, phone }),
    })
    .then(r => r.json())
    .then(data => {
        if (data.success) {
            _allCustomers.unshift(data.customer);
            selectCustomerForPOS(data.customer.id, data.customer.name);
            closeCreateCustomerForm();
        } else {
            const msg = data.errors
                ? Object.values(data.errors).flat().join(" ")
                : (data.message || "Failed to create customer.");
            showCreateError(msg);
            submitBtn.disabled = false;
            submitBtn.innerHTML = `<span class="material-symbols-outlined">person_add</span> Create & select`;
        }
    })
    .catch(() => {
        showCreateError("Server error. Please try again.");
        submitBtn.disabled = false;
        submitBtn.innerHTML = `<span class="material-symbols-outlined">person_add</span> Create & select`;
    });
}

function showCreateError(message) {
    const errorBox = document.getElementById("nc-error");
    if (errorBox) { errorBox.innerText = message; errorBox.style.display = "block"; }
}
