let registerState = {
    isOpen: false,
    openingBalance: 1250.00,
    totalSales: 845.50,
    totalTransactions: 28,
    openedAt: "2026-05-30 08:15:00",
    expectedBalance: 2095.50,
};

// Open Popup
function openRegisterPopup() {
    console.log("openRegisterPopup() called"); // For debugging

    updateDatetime();

    if (registerState.isOpen) {
        showCloseState();
    } else {
        showOpenState();
    }

    document.getElementById('register-overlay').style.display = 'flex';
}

// Close Popup
function closeRegisterPopup() {
    document.getElementById('register-overlay').style.display = 'none';
}

// Show Open State
function showOpenState() {
    document.getElementById('cr-title').textContent = 'Open Register';
    document.getElementById('cr-subtitle').textContent = 'Enter opening cash to start your shift';

    document.getElementById('cr-status-badge').textContent = 'Not opened';
    document.getElementById('cr-status-badge').className = 'cr-status-badge none';

    document.getElementById('cr-open-state').style.display = 'block';
    document.getElementById('cr-close-state').style.display = 'none';
}

// Show Close State
function showCloseState() {
    document.getElementById('cr-title').textContent = 'Close Register';
    document.getElementById('cr-subtitle').textContent = 'Review your shift before closing';

    document.getElementById('cr-status-badge').textContent = 'Open';
    document.getElementById('cr-status-badge').className = 'cr-status-badge open';

    // Fill data
    document.getElementById('cr-total-txn').textContent = registerState.totalTransactions;
    document.getElementById('cr-total-sales').textContent = '$' + registerState.totalSales.toFixed(2);
    document.getElementById('cr-show-opening').textContent = '$' + registerState.openingBalance.toFixed(2);
    document.getElementById('cr-expected').textContent = '$' + registerState.expectedBalance.toFixed(2);

    const openedAt = new Date(registerState.openedAt).toLocaleString('en-US', {
        dateStyle: 'medium',
        timeStyle: 'short'
    });
    document.getElementById('cr-opened-at').textContent = openedAt;

    document.getElementById('cr-open-state').style.display = 'none';
    document.getElementById('cr-close-state').style.display = 'block';
}

// Calculate Difference
function calcDifference() {
    const closing = parseFloat(document.getElementById('cr-closing-input').value) || 0;
    const expected = registerState.expectedBalance;
    const diff = closing - expected;

    const el = document.getElementById('cr-diff-value');
    el.textContent = (diff >= 0 ? '+' : '') + '$' + diff.toFixed(2);

    if (diff < 0) el.style.color = '#dc2626';
    else if (diff > 0) el.style.color = '#16a34a';
    else el.style.color = '#64748b';
}

// Update Date & Time
function updateDatetime() {
    const el = document.getElementById('cr-datetime');
    if (el) {
        el.textContent = new Date().toLocaleString('en-US', {
            dateStyle: 'medium',
            timeStyle: 'short'
        });
    }
}

// Submit Open Register (Static)
function submitOpenRegister() {
    const opening = parseFloat(document.getElementById('cr-opening-input').value);
    if (isNaN(opening) || opening < 0) {
        alert("Please enter a valid opening balance!");
        return;
    }

    alert("Register Opened Successfully! (Static Test)");
    registerState.isOpen = true;
    closeRegisterPopup();
}

// Submit Close Register (Static)
function submitCloseRegister() {
    const closing = parseFloat(document.getElementById('cr-closing-input').value);
    if (isNaN(closing) || closing < 0) {
        alert("❌ Please enter a valid closing balance!");
        return;
    }

    alert("Register Closed Successfully! (Static Test)");
    registerState.isOpen = false;
    closeRegisterPopup();
}

// Initialize on page load
document.addEventListener('DOMContentLoaded', function() {
    // Set initial dot color
    const dot = document.getElementById('register-dot');
    if (dot) dot.classList.add('closed');

    console.log("Cash Register JS Loaded");
});
