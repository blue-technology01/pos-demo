// ============================================================
// CUSTOMER POS — Search, Select, Create
// ============================================================

// ✅ local cache — loaded once when modal opens
let _allCustomers = [];
let _customersLoaded = false;
let searchTimeout = null;

// ─── Filter Modal ────────────────────────────────────────────

function openCustomerFilterPopup() {
    resetSearchModal();
    document.getElementById("customerFilterModal").style.display = "flex";
    document.getElementById("popupSearchInput").focus();

    if (!_customersLoaded) {
        _fetchAllCustomers();
    } else {
        _renderLocalResults(""); // already cached — show instantly
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

// ─── Load full customer list once ────────────────────────────

async function _fetchAllCustomers() {
    const resultContainer = document.getElementById("popupCustomerResult");
    resultContainer.innerHTML = renderLoading();

    try {
        const response = await fetch(`${window.CUSTOMER_SEARCH_URL}?per_page=9999`);
        if (!response.ok) throw new Error("Network error");
        const data = await response.json();

        // support both flat array and paginated { data: [...] }
        _allCustomers   = Array.isArray(data) ? data : (data.data ?? []);
        _customersLoaded = true;

        // render with whatever is currently in the search box
        const keyword = document.getElementById("popupSearchInput").value.trim();
        _renderLocalResults(keyword);
    } catch (err) {
        console.error("Error loading customers:", err);
        resultContainer.innerHTML = renderError();
    }
}

// ─── Real-time Search ────────────────────────────────────────

function filterCustomersRealTime() {
    clearTimeout(searchTimeout);
    const keyword = document.getElementById("popupSearchInput").value.trim();

    // ✅ instant local filter — no debounce needed, no server call
    if (_customersLoaded) {
        _renderLocalResults(keyword);

        // ✅ silently refresh from server in background for accuracy
        clearTimeout(searchTimeout);
        searchTimeout = setTimeout(() => _serverSearchCustomers(keyword), 400);
        return;
    }

    // not loaded yet — debounce server fetch
    searchTimeout = setTimeout(() => fetchCustomersFromBackend(keyword), 300);
}

// ─── Local filter (instant) ───────────────────────────────────

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
    }
}

// ─── Silent background refresh ────────────────────────────────

async function _serverSearchCustomers(keyword) {
    try {
        const response = await fetch(
            `${window.CUSTOMER_SEARCH_URL}?keyword=${encodeURIComponent(keyword)}`
        );
        if (!response.ok) return;
        const data = await response.json();

        const results = Array.isArray(data) ? data : (data.data ?? []);

        // ✅ discard if user has already changed the input
        const currentKeyword = document.getElementById("popupSearchInput").value.trim();
        if (currentKeyword !== keyword) return;

        // ✅ merge new results back into cache so future local searches are fresh
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

// ─── Fetch from Backend (fallback only) ──────────────────────

function fetchCustomersFromBackend(keyword) {
    const resultContainer = document.getElementById("popupCustomerResult");
    resultContainer.innerHTML = renderLoading();

    fetch(`${window.CUSTOMER_SEARCH_URL}?keyword=${encodeURIComponent(keyword)}`)
        .then(response => {
            if (!response.ok) throw new Error("Network response was not ok");
            return response.json();
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

// ─── Render Helpers ──────────────────────────────────────────

function renderLoading() {
    return `
        <div style="padding:30px;text-align:center;color:#64748b;font-size:14px;">
            <span style="font-size:22px;">⏳</span>
            <p style="margin:8px 0 0;">Loading...</p>
        </div>`;
}

function renderEmpty() {
    return `
        <div style="padding:30px;text-align:center;color:#94a3b8;font-size:14px;">
            <span style="font-size:32px;">🔍</span>
            <p style="margin:8px 0 0;">Type a name or phone number to search</p>
        </div>`;
}

function renderNotFound(keyword) {
    return `
        <div style="padding:30px 20px;text-align:center;">
            <span style="font-size:32px;">😕</span>
            <p style="color:#64748b;font-size:14px;margin:10px 0 16px;">
                No customer found for <strong>"${keyword}"</strong>
            </p>
            <button
                type="button"
                onclick="triggerAddNewCustomer('${keyword}')"
                style="
                    display:inline-flex;align-items:center;gap:6px;
                    background:#16a34a;color:#fff;
                    padding:10px 20px;border:none;border-radius:6px;
                    font-size:14px;font-weight:600;font-family:inherit;cursor:pointer;
                ">
                <span class="material-symbols-outlined" style="font-size:18px;">person_add</span>
                Create "${keyword}" as new customer
            </button>
        </div>`;
}

function renderError() {
    return `
        <div style="padding:30px;text-align:center;color:#ef4444;font-size:14px;">
            <span style="font-size:32px;">⚠️</span>
            <p style="margin:8px 0 0;">Failed to connect to server. Please try again.</p>
        </div>`;
}

function renderCustomerList(customers) {
    return customers.map(customer => `
        <div
            onclick="selectCustomerForPOS('${customer.id}', '${customer.name}')"
            style="
                display:flex;justify-content:space-between;align-items:center;
                padding:12px 16px;border-bottom:1px solid #e2e8f0;
                cursor:pointer;transition:background 0.15s;
            "
            onmouseover="this.style.background='#f8fafc'"
            onmouseout="this.style.background=''"
        >
            <div style="display:flex;align-items:center;gap:10px;">
                <div style="
                    width:36px;height:36px;border-radius:50%;
                    background:#e0f2fe;color:#0284c7;
                    display:flex;align-items:center;justify-content:center;
                    font-weight:700;font-size:15px;flex-shrink:0;
                ">
                    ${customer.name.charAt(0).toUpperCase()}
                </div>
                <div>
                    <p style="margin:0;font-size:14px;font-weight:600;color:#0f172a;">${customer.name}</p>
                    <p style="margin:2px 0 0;font-size:12px;color:#64748b;">
                        📱 ${customer.phone || 'No phone'}
                    </p>
                </div>
            </div>
            <span style="font-size:12px;font-weight:600;color:#16a34a;white-space:nowrap;">
                Select ✓
            </span>
        </div>
    `).join("");
}

// ─── Select Customer ─────────────────────────────────────────

function selectCustomerForPOS(id, name) {
    window.selectedCustomerId = id;

    const nameDisplay = document.getElementById("selected-customer-name");
    const idDisplay   = document.getElementById("selected-customer-id");
    if (nameDisplay) nameDisplay.innerText = name;
    if (idDisplay)   idDisplay.innerText   = `#C-${String(id).padStart(6, "0")}`;

    closeFilterModal();
}

// ─── Create New Customer ─────────────────────────────────────

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
    const name      = document.getElementById("nc-name").value.trim();
    const phone     = document.getElementById("nc-phone").value.trim();
    const errorBox  = document.getElementById("nc-error");
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
            // ✅ add new customer to local cache so it appears instantly next time
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
