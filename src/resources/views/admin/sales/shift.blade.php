@extends('layouts.app')

@push('styles')
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined" />
    <link rel="stylesheet" href="{{ asset('assets/css/dashboard/sale/shift.css') }}">
@endpush

@section('title', 'Cash Register')

@section('content')
<div class="unit-section">

    {{-- Header --}}
    <div class="header-actions">
        <div class="search-wrap">
            <span class="material-symbols-outlined">search</span>
            <input type="text" id="searchInput"
                placeholder="Search by cashier or invoice...">

            <span class="search-label">Start date</span>
            <input type="date" class="filter-date" id="startDate">

            <span class="search-label">End date</span>
            <input type="date" class="filter-date" id="endDate">

            <button class="btn-filter" id="filter-btn">Filter</button>
            <button class="btn-reset" id="reset-btn">Reset</button>
        </div>

        <button type="button" id="open-shift-modal" class="unit-section__btn-add">
            <span class="material-symbols-outlined">add</span>
            Start new shift
        </button>
    </div>

    {{-- Summary stat cards --}}
    <div class="stat-grid">
        <div class="stat-card">
            <div class="stat-icon open">
                {{-- <i class="ti ti-lock-open"></i> --}}
                <span class="material-symbols-outlined">lock_open</span>
            </div>
            <div>
                <div class="stat-label">Open shifts</div>
                <div class="stat-value">1</div>
            </div>
        </div>
        <div class="stat-card">
            <div class="stat-icon closed">
                {{-- <i class="ti ti-lock"></i> --}}
                <span class="material-symbols-outlined">lock</span>
            </div>
            <div>
                <div class="stat-label">Closed today</div>
                <div class="stat-value">5</div>
            </div>
        </div>
        <div class="stat-card">
            <div class="stat-icon sales">
                {{-- <i class="ti ti-coin"></i> --}}
                <span class="material-symbols-outlined">currency_exchange</span>
            </div>
            <div>
                <div class="stat-label">Today's sales</div>
                <div class="stat-value">$4,820</div>
            </div>
        </div>
        <div class="stat-card">
            <div class="stat-icon diff">
                {{-- <i class="ti ti-alert-triangle"></i> --}}
                {{-- <span class="material-symbols-outlined">alert_triangle</span> --}}
                <span class="material-symbols-outlined">warning</span>
            </div>
            <div>
                <div class="stat-label">Differences</div>
                <div class="stat-value danger">2</div>
            </div>
        </div>
    </div>

    {{-- Table card --}}
    <div class="unit-card">
        <div class="table-responsive">
            <table class="table-custom shift-table" id="shiftTable">
                <thead>
                    <tr>
                        <th class="col-id">Invoice no</th>
                        <th>Cashier</th>
                        <th>Opening</th>
                        <th>Expected</th>
                        <th>Closing</th>
                        <th>Difference</th>
                        <th>Total sales</th>
                        <th>Txn</th>
                        <th>Opened at</th>
                        <th>Status</th>
                        <th class="col-actions">Action</th>
                    </tr>
                </thead>
                <tbody id="shift-table-body">
                    {{-- Row 1 — Closed with difference --}}
                    <tr>
                        <td><span class="invoice-link">#INV-1001</span></td>
                        <td>
                            <div class="cashier-cell">
                                <div class="cashier-avatar">D</div>
                                <span>Dara Sok</span>
                            </div>
                        </td>
                        <td>$200.00</td>
                        <td>$1,440.00</td>
                        <td>$1,420.00</td>
                        <td><span class="diff-badge negative">-$20.00</span></td>
                        <td>$1,240.00</td>
                        <td>24</td>
                        <td class="muted-text">30 May 2026 08:00</td>
                        <td><span class="status-badge closed">Closed</span></td>
                        <td>
                            <button class="btn-view"
                                data-id="1001"
                                data-cashier="Dara Sok"
                                data-status="Closed"
                                data-opened="30 May 2026 — 08:00 AM"
                                data-closed="30 May 2026 — 05:30 PM"
                                data-opening="$200.00"
                                data-sales="$1,240.00"
                                data-expected="$1,440.00"
                                data-closing="$1,420.00"
                                data-diff="-$20.00"
                                data-diff-type="negative"
                                data-txn="24"
                                data-note="Cashier reported small change discrepancy at end of shift."
                                onclick="openDetail(this)">
                                <i class="ti ti-eye"></i> View
                            </button>
                        </td>
                    </tr>

                    {{-- Row 2 — Closed no difference --}}
                    <tr>
                        <td><span class="invoice-link">#INV-1002</span></td>
                        <td>
                            <div class="cashier-cell">
                                <div class="cashier-avatar">M</div>
                                <span>Maly Chan</span>
                            </div>
                        </td>
                        <td>$150.00</td>
                        <td>$980.00</td>
                        <td>$980.00</td>
                        <td><span class="diff-badge zero">$0.00</span></td>
                        <td>$830.00</td>
                        <td>18</td>
                        <td class="muted-text">30 May 2026 08:30</td>
                        <td><span class="status-badge closed">Closed</span></td>
                        <td>
                            <button class="btn-view"
                                data-id="1002"
                                data-cashier="Maly Chan"
                                data-status="Closed"
                                data-opened="30 May 2026 — 08:30 AM"
                                data-closed="30 May 2026 — 05:00 PM"
                                data-opening="$150.00"
                                data-sales="$830.00"
                                data-expected="$980.00"
                                data-closing="$980.00"
                                data-diff="$0.00"
                                data-diff-type="zero"
                                data-txn="18"
                                data-note=""
                                onclick="openDetail(this)">
                                <i class="ti ti-eye"></i> View
                            </button>
                        </td>
                    </tr>

                    {{-- Row 3 — Currently open --}}
                    <tr>
                        <td><span class="invoice-link">#INV-1003</span></td>
                        <td>
                            <div class="cashier-cell">
                                <div class="cashier-avatar">V</div>
                                <span>Visal Pov</span>
                            </div>
                        </td>
                        <td>$200.00</td>
                        <td>—</td>
                        <td>—</td>
                        <td>—</td>
                        <td>$340.00</td>
                        <td>9</td>
                        <td class="muted-text">30 May 2026 09:00</td>
                        <td><span class="status-badge open">Open</span></td>
                        <td>
                            <button class="btn-view"
                                data-id="1003"
                                data-cashier="Visal Pov"
                                data-status="Open"
                                data-opened="30 May 2026 — 09:00 AM"
                                data-closed="—"
                                data-opening="$200.00"
                                data-sales="$340.00"
                                data-expected="$540.00"
                                data-closing="—"
                                data-diff="—"
                                data-diff-type="zero"
                                data-txn="9"
                                data-note=""
                                onclick="openDetail(this)">
                                <i class="ti ti-eye"></i> View
                            </button>
                        </td>
                    </tr>

                </tbody>
            </table>
        </div>

        {{-- Pagination --}}
        <div class="pm-pagination">
            <div class="pm-pagination__meta">
                <span class="pm-pagination__text">Showing 3 of 3 records</span>
                <div class="pm-pagination__per-page">
                    <label for="per-page-select">Show:</label>
                    <select id="per-page-select" class="pm-pagination__select">
                        <option value="15">15</option>
                        <option value="25" selected>25</option>
                        <option value="50">50</option>
                    </select>
                </div>
            </div>
            <div class="pm-pagination__links" id="paginationLinks"></div>
        </div>
    </div>
</div>

{{-- modal start new shift --}}
<div id="shift-modal" class="modal-overlay" style="display: none;">
    <div class="modal-box">
        <div class="modal-header">
            <div>
                <h3 class="modal-title">Start new shift</h3>
                <p class="modal-subtitle">Enter opening cash to begin</p>
            </div>
            <button type="button" class="modal-close-btn" onclick="closeModal('shift-modal')">
                <i class="ti ti-x"></i>
            </button>
        </div>

        <form id="shift-form">
            @csrf
            <input type="hidden" id="shift_id">

            <div class="modal-body">
                <div class="info-row">
                    <span class="info-label">Cashier</span>
                    <span class="info-value">{{ auth()->user()->name ?? 'Dara Sok' }}</span>
                </div>
                <div class="info-row">
                    <span class="info-label">Date & time</span>
                    <span class="info-value" id="current-datetime"></span>
                </div>

                <div class="form-group">
                    <label class="form-label">Opening balance ($) <span class="required">*</span></label>
                    <input type="number" step="0.01" min="0" id="open_balance"
                        class="form-input" placeholder="0.00" required>
                </div>

                <div class="form-group">
                    <label class="form-label">Note (optional)</label>
                    <textarea id="shift_note" class="form-textarea" placeholder="Any remark..."></textarea>
                </div>
            </div>

            <div class="modal-footer">
                <button type="button" class="btn-cancel" onclick="closeModal('shift-modal')">Cancel</button>
                <button type="submit" class="btn-save" id="save-shift-btn">
                    <i class="ti ti-lock-open"></i> Open register
                </button>
            </div>
        </form>
    </div>
</div>

{{-- modal view detail --}}
<div id="detail-modal" class="modal-overlay" style="display: none;">
    <div class="modal-box">
        <div class="modal-header">
            <div>
                <h3 class="modal-title">Register detail</h3>
                <p class="modal-subtitle" id="detail-invoice"></p>
            </div>
            <div style="display:flex;align-items:center;gap:10px;">
                <span id="detail-status-badge"></span>
                <button type="button" class="modal-close-btn" onclick="closeModal('detail-modal')">
                    {{-- <i class="ti ti-x"></i> --}}
                    <span class="material-symbols-outlined">close</span>
                </button>
            </div>
        </div>

        <div class="modal-body">

            {{-- Cashier info --}}
            <div class="detail-section-label">Cashier info</div>
            <div class="detail-grid">
                <div class="detail-row">
                    <span class="info-label">Cashier name</span>
                    <span class="info-value" id="detail-cashier"></span>
                </div>
                <div class="detail-row">
                    <span class="info-label">Opened at</span>
                    <span class="info-value" id="detail-opened"></span>
                </div>
                <div class="detail-row">
                    <span class="info-label">Closed at</span>
                    <span class="info-value" id="detail-closed"></span>
                </div>
            </div>

            {{-- Summary stat cards --}}
            <div class="detail-section-label" style="margin-top:16px;">Summary</div>
            <div class="detail-stat-grid">
                <div class="detail-stat">
                    <div class="detail-stat-label">Opening balance</div>
                    <div class="detail-stat-value" id="detail-opening"></div>
                </div>
                <div class="detail-stat">
                    <div class="detail-stat-label">Total sales</div>
                    <div class="detail-stat-value" id="detail-sales"></div>
                </div>
                <div class="detail-stat">
                    <div class="detail-stat-label">Expected balance</div>
                    <div class="detail-stat-value" id="detail-expected"></div>
                </div>
                <div class="detail-stat">
                    <div class="detail-stat-label">Closing balance</div>
                    <div class="detail-stat-value" id="detail-closing"></div>
                </div>
                <div class="detail-stat">
                    <div class="detail-stat-label">Total transactions</div>
                    <div class="detail-stat-value" id="detail-txn"></div>
                </div>
                <div class="detail-stat">
                    <div class="detail-stat-label">Difference</div>
                    <div class="detail-stat-value" id="detail-diff"></div>
                </div>
            </div>

            {{-- Note --}}
            <div id="detail-note-wrap" style="display:none;">
                <div class="detail-section-label" style="margin-top:16px;">Note</div>
                <div class="detail-note" id="detail-note"></div>
            </div>
        </div>

        <div class="modal-footer">
            <button type="button" class="btn-cancel" onclick="closeModal('detail-modal')">Close</button>
        </div>
    </div>
</div>

@endsection

@push('scripts')
    <script src="{{ asset('assets/js/dashboard/sale/shift-management.js') }}"></script>
    <script>
        // ── Current datetime in open shift modal ──
        function updateDatetime() {
            const now = new Date();
            document.getElementById('current-datetime').textContent =
                now.toLocaleString('en-US', { dateStyle: 'medium', timeStyle: 'short' });
        }
        updateDatetime();

        // ── Open / close modals ──
        document.getElementById('open-shift-modal').addEventListener('click', () => {
            updateDatetime();
            document.getElementById('shift-modal').style.display = 'flex';
        });

        function closeModal(id) {
            document.getElementById(id).style.display = 'none';
        }

        // Close on overlay click
        document.querySelectorAll('.modal-overlay').forEach(overlay => {
            overlay.addEventListener('click', function (e) {
                if (e.target === this) closeModal(this.id);
            });
        });

        // ── Open detail modal ──
        function openDetail(btn) {
            const d = btn.dataset;

            document.getElementById('detail-invoice').textContent  = '#INV-' + d.id;
            document.getElementById('detail-cashier').textContent  = d.cashier;
            document.getElementById('detail-opened').textContent   = d.opened;
            document.getElementById('detail-closed').textContent   = d.closed;
            document.getElementById('detail-opening').textContent  = d.opening;
            document.getElementById('detail-sales').textContent    = d.sales;
            document.getElementById('detail-expected').textContent = d.expected;
            document.getElementById('detail-closing').textContent  = d.closing;
            document.getElementById('detail-txn').textContent      = d.txn;

            // Difference with color
            const diffEl = document.getElementById('detail-diff');
            diffEl.textContent = d.diff;
            diffEl.className = 'detail-stat-value ' +
                (d.diffType === 'negative' ? 'danger' : d.diffType === 'positive' ? 'success' : '');

            // Status badge
            const statusBadge = document.getElementById('detail-status-badge');
            statusBadge.textContent = d.status;
            statusBadge.className = 'status-badge ' + (d.status === 'Open' ? 'open' : 'closed');

            // Note
            const noteWrap = document.getElementById('detail-note-wrap');
            if (d.note && d.note.trim() !== '') {
                document.getElementById('detail-note').textContent = d.note;
                noteWrap.style.display = 'block';
            } else {
                noteWrap.style.display = 'none';
            }

            document.getElementById('detail-modal').style.display = 'flex';
        }

        // ── Search filter ──
        document.getElementById('searchInput').addEventListener('input', function () {
            const q = this.value.toLowerCase();
            document.querySelectorAll('#shift-table-body tr').forEach(row => {
                row.style.display = row.textContent.toLowerCase().includes(q) ? '' : 'none';
            });
        });
    </script>
@endpush
