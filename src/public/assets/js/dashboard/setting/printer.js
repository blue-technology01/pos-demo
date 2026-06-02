    function onToggle(cb, userId) {
        const lbl = document.getElementById('lbl-' + userId);
        if (cb.checked) {
            lbl.textContent = 'Preview';
            lbl.className = 'toggle-label on';
        } else {
            lbl.textContent = 'No Preview';
            lbl.className = 'toggle-label off';
        }
        updateCounts();
    }

    function updateCounts() {
        const all = document.querySelectorAll('#user-list input[type=checkbox]');
        const on  = [...all].filter(c => c.checked).length;
        document.getElementById('count-on').textContent  = on;
        document.getElementById('count-off').textContent = all.length - on;
    }

    function filterUsers(q) {
        const rows = document.querySelectorAll('.user-row');
        const term = q.toLowerCase().trim();
        let visible = 0;
        rows.forEach(row => {
            const match = !term
                || row.dataset.name.includes(term)
                || row.dataset.role.includes(term);
            row.classList.toggle('hidden', !match);
            if (match) visible++;
        });
        const vc = document.getElementById('visible-count');
        vc.textContent = term ? `${visible} result${visible !== 1 ? 's' : ''}` : '';
        document.getElementById('no-results').style.display = visible === 0 ? 'block' : 'none';
    }

    function bulkSet(state) {
        document.querySelectorAll('.user-row:not(.hidden) input[type=checkbox]').forEach(cb => {
            cb.checked = state;
            const userId = cb.value;
            const lbl = document.getElementById('lbl-' + userId);
            if (lbl) {
                lbl.textContent = state ? 'Preview' : 'No Preview';
                lbl.className   = 'toggle-label ' + (state ? 'on' : 'off');
            }
        });
        updateCounts();
    }

    // Init counts on load
    document.addEventListener('DOMContentLoaded', updateCounts);
