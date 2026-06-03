// ==============================
// Cash Register POS
// ==============================

const registerState = {
    isOpen: window.isShiftOpen ?? false,
    expectedBalance: parseFloat(window.expectedShiftBalance ?? 0)
};

// ==============================
// Open Popup
// ==============================
function openRegisterPopup() {
    console.log("openRegisterPopup() called");

    updateDatetime();

    const overlay = document.getElementById('register-overlay');

    if (!overlay) {
        console.error('register-overlay not found');
        return;
    }

    overlay.style.display = 'flex';

    if (registerState.isOpen) {
        showCloseState();
    } else {
        showOpenState();
    }
}

// ==============================
// Close Popup
// ==============================
function closeRegisterPopup() {
    const overlay = document.getElementById('register-overlay');

    if (overlay) {
        overlay.style.display = 'none';
    }
}

// ==============================
// Open Register State
// ==============================
function showOpenState() {

    const title = document.getElementById('cr-title');
    const subtitle = document.getElementById('cr-subtitle');
    const badge = document.getElementById('cr-status-badge');

    const openState = document.getElementById('cr-open-state');
    const closeState = document.getElementById('cr-close-state');

    if (title) {
        title.textContent = 'Open Register';
    }

    if (subtitle) {
        subtitle.textContent = 'Enter opening cash to start your shift';
    }

    if (badge) {
        badge.textContent = 'CLOSED';
        badge.style.background = '#f8d7da';
        badge.style.color = '#721c24';
    }

    if (openState) {
        openState.style.display = 'block';
    }

    if (closeState) {
        closeState.style.display = 'none';
    }
}

// ==============================
// Close Register State
// ==============================
function showCloseState() {

    const title = document.getElementById('cr-title');
    const subtitle = document.getElementById('cr-subtitle');
    const badge = document.getElementById('cr-status-badge');

    const openState = document.getElementById('cr-open-state');
    const closeState = document.getElementById('cr-close-state');

    if (title) {
        title.textContent = 'Close Register';
    }

    if (subtitle) {
        subtitle.textContent = 'Review your shift before closing';
    }

    if (badge) {
        badge.textContent = 'OPEN';
        badge.style.background = '#d4edda';
        badge.style.color = '#155724';
    }

    if (openState) {
        openState.style.display = 'none';
    }

    if (closeState) {
        closeState.style.display = 'block';
    }
}

// ==============================
// Difference Calculator
// ==============================
function calcDifference() {

    const closingInput = document.getElementById('cr-closing-input');
    const diffValue = document.getElementById('cr-diff-value');
    const diffBox = document.getElementById('cr-diff-box');

    if (!closingInput || !diffValue) {
        return;
    }

    const closing = parseFloat(closingInput.value || 0);
    const expected = registerState.expectedBalance;

    const diff = closing - expected;

    diffValue.textContent =
        (diff >= 0 ? '+' : '') +
        '$' +
        diff.toFixed(2);

    if (diff > 0) {

        diffValue.style.color = '#16a34a';

        if (diffBox) {
            diffBox.style.borderColor = '#16a34a';
        }

    } else if (diff < 0) {

        diffValue.style.color = '#dc2626';

        if (diffBox) {
            diffBox.style.borderColor = '#dc2626';
        }

    } else {

        diffValue.style.color = '#64748b';

        if (diffBox) {
            diffBox.style.borderColor = '#e5e7eb';
        }
    }
}

// ==============================
// Backward Compatibility
// ==============================
function calcDifferenceLocal() {
    calcDifference();
}

// ==============================
// Datetime
// ==============================
function updateDatetime() {

    const el = document.getElementById('cr-datetime');

    if (!el) {
        return;
    }

    el.textContent = new Date().toLocaleString('en-US', {
        dateStyle: 'medium',
        timeStyle: 'short'
    });
}

// ==============================
// Close on overlay click
// ==============================
document.addEventListener('click', function (e) {

    const overlay = document.getElementById('register-overlay');

    if (
        overlay &&
        e.target === overlay
    ) {
        closeRegisterPopup();
    }
});

// ==============================
// Init
// ==============================
document.addEventListener('DOMContentLoaded', () => {

    console.log('Cash Register JS Loaded');

    const dot = document.getElementById('register-dot');

    if (dot) {

        if (registerState.isOpen) {

            dot.classList.add('open');
            dot.style.background = '#16a34a';

        } else {

            dot.classList.add('closed');
            dot.style.background = '#dc2626';
        }
    }

    const closingInput = document.getElementById('cr-closing-input');

    if (closingInput) {
        closingInput.addEventListener('input', calcDifference);
    }
});
