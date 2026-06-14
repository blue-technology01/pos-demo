@extends('layouts.app')

@push('styles')
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined" />
    <link rel="stylesheet" href="{{ asset('assets/css/dashboard/sale/shift.css') }}">
@endpush

@section('title', 'Cash Register')

@section('content')
<div class="unit-section">

    {{-- ========================================================= --}}
    {{-- Search & Filter --}}
    {{-- ========================================================= --}}
    <div class="header-actions">
        <form action="{{ route('admin.shift') }}" method="GET" class="search-wrap">

            <div class="search-box">
                <span class="material-symbols-outlined">search</span>
                <input type="text" name="search" id="searchInput"
                    value="{{ request('search') }}"
                    placeholder="Search by cashier or invoice...">
            </div>

            <input type="date" name="start_date" value="{{ request('start_date') }}"
                class="filter-date" id="startDate">

            <input type="date" name="end_date" value="{{ request('end_date') }}"
                class="filter-date" id="endDate">

            <button type="submit" class="btn-filter">Filter</button>
            <a href="{{ route('admin.shift') }}" class="btn-reset">Reset</a>

        </form>
    </div>

    {{-- ========================================================= --}}
    {{-- summary start cards    --}}
    {{-- ========================================================= --}}
    <div class="stat-grid">
        {{-- shift start --}}
        <div class="stat-card">
            <div class="stat-icon open">
                <span class="material-symbols-outlined">lock_open</span>
            </div>
            <div>
                <div class="stat-label">Open shifts</div>
                <div class="stat-value">{{ $currentRegister ? 1 : 0 }}</div>
            </div>
        </div>

        {{-- Close shfit today --}}
        <div class="stat-card">
            <div class="stat-icon closed">
                <span class="material-symbols-outlined">lock</span>
            </div>
            <div>
                <div class="stat-label">Closed today</div>
                <div class="stat-value">
                    {{ $history->where('status', 'closed')->where('closed_at', '>=', now()->startOfDay())->count() }}
                </div>
            </div>
        </div>

        {{-- Total today --}}
        <div class="stat-card">
            <div class="stat-icon sales">
                <span class="material-symbols-outlined">currency_exchange</span>
            </div>
            <div>
                <div class="stat-label">Today's sales</div>
                <div class="stat-value">
                    ${{ number_format($history->where('created_at', '>=', now()->startOfDay())->sum('total_sales'), 2) }}
                </div>
            </div>
        </div>

        <div class="stat-card">
            <div class="stat-icon diff">
                <span class="material-symbols-outlined">warning</span>
            </div>
            <div>
                <div class="stat-label">Differences</div>
                @php
                    $diffCount = $history->where('status', 'closed')->where('difference_amount', '!=', 0)->count();
                @endphp
                <div class="stat-value {{ $diffCount > 0 ? 'danger' : '' }}">{{ $diffCount }}</div>
            </div>
        </div>
    </div>

    {{-- ========================================================= --}}
    {{-- Shift History Table --}}
    {{-- ========================================================= --}}
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
                    @forelse($history as $reg)
                        <tr>
                            <td><span class="invoice-link">#INV-{{ $reg->id }}</span></td>
                            <td>
                                <div class="cashier-cell">
                                    <div class="cashier-avatar">{{ strtoupper(substr($reg->user->name ?? 'U', 0, 1)) }}</div>
                                    <span>{{ $reg->user->name ?? 'N/A' }}</span>
                                </div>
                            </td>
                            <td>${{ number_format($reg->opening_balance, 2) }}</td>
                            <td>{{ $reg->status === 'open' ? '—' : '$' . number_format($reg->expected_balance, 2) }}</td>
                            <td>{{ $reg->status === 'open' ? '—' : '$' . number_format($reg->closing_balance, 2) }}</td>
                            <td>
                                @if($reg->status === 'open')
                                    —
                                @else
                                    @if($reg->difference_amount < 0)
                                        <span class="diff-badge negative">-${{ number_format(abs($reg->difference_amount), 2) }}</span>
                                    @elseif($reg->difference_amount > 0)
                                        <span class="diff-badge positive">+${{ number_format($reg->difference_amount, 2) }}</span>
                                    @else
                                        <span class="diff-badge zero">$0.00</span>
                                    @endif
                                @endif
                            </td>
                            <td>${{ number_format($reg->total_sales, 2) }}</td>
                            <td>{{ $reg->total_transactions ?? 0 }}</td>
                            <td class="muted-text">{{ $reg->opened_at ? $reg->opened_at->format('d M Y H:i') : '—' }}</td>
                            <td>
                                <span class="status-badge {{ $reg->status === 'open' ? 'open' : 'closed' }}">
                                    {{ ucfirst($reg->status) }}
                                </span>
                            </td>
                            <td>
                                <button class="btn-view"
                                    data-id="{{ $reg->id }}"
                                    data-cashier="{{ $reg->user->name ?? 'N/A' }}"
                                    data-status="{{ ucfirst($reg->status) }}"
                                    data-opened="{{ $reg->opened_at ? $reg->opened_at->format('d M Y — h:i AM') : '—' }}"
                                    data-closed="{{ $reg->closed_at ? $reg->closed_at->format('d M Y — h:i AM') : '—' }}"
                                    data-opening="${{ number_format($reg->opening_balance, 2) }}"
                                    data-sales="${{ number_format($reg->total_sales, 2) }}"
                                    data-expected="{{ $reg->status === 'open' ? '—' : '$' . number_format($reg->expected_balance, 2) }}"
                                    data-closing="{{ $reg->status === 'open' ? '—' : '$' . number_format($reg->closing_balance, 2) }}"
                                    data-diff="{{ $reg->status === 'open' ? '—' : ($reg->difference_amount < 0 ? '-$' . number_format(abs($reg->difference_amount), 2) : '$' . number_format($reg->difference_amount, 2)) }}"
                                    data-diff-type="{{ $reg->difference_amount < 0 ? 'negative' : ($reg->difference_amount > 0 ? 'positive' : 'zero') }}"
                                    data-txn="{{ $reg->total_transactions ?? 0 }}"
                                    data-note="{{ $reg->note ?? '' }}"
                                    onclick="openDetail(this)">
                                    <span class="material-symbols-outlined" style="font-size: 16px; margin-right: 4px; vertical-align: middle;">visibility</span> View
                                </button>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="11" style="text-align: center; padding: 24px; color: #161e30;">មិនមានទិន្នន័យវេនលក់ស្របតាមលក្ខខណ្ឌស្វែងរកឡើយ។</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div

        {{-- ========================================================= --}}
        {{--  dynamic Pagination  --}}
        {{-- ========================================================= --}}
        <div class="pm-pagination">
            <div class="pm-pagination__meta">
                <span class="pm-pagination__text">
                    Showing {{ $history->firstItem() ?? 0 }} to {{ $history->lastItem() ?? 0 }} of {{ $history->total() }} records
                </span>
            </div>
            <div class="pm-pagination__links" id="paginationLinks">
                {{ $history->appends(request()->query())->links() }}
            </div>
        </div>
    </div>
</div>

{{-- ========================================================= --}}
{{-- Modal View Detail --}}
{{-- ========================================================= --}}
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
    <script>

        function closeModal(id) {
            document.getElementById(id).style.display = 'none';
        }

        document.querySelectorAll('.modal-overlay').forEach(overlay => {
            overlay.addEventListener('click', function (e) {
                if (e.target === this) closeModal(this.id);
            });
        });

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

            // កំណត់ពណ៌ទៅតាម Difference Type (ខ្វះពណ៌ក្រហម, លើសពណ៌បៃតង)
            const diffEl = document.getElementById('detail-diff');
            diffEl.textContent = d.diff;
            diffEl.className = 'detail-stat-value ' +
                (d.diffType === 'negative' ? 'danger' : d.diffType === 'positive' ? 'success' : '');

            // ស្ថានភាព Badge (Open/Closed)
            const statusBadge = document.getElementById('detail-status-badge');
            statusBadge.textContent = d.status;
            statusBadge.className = 'status-badge ' + (d.status === 'Open' ? 'open' : 'closed');

            // បង្ហាញកំណត់សម្គាល់បើមាន
            const noteWrap = document.getElementById('detail-note-wrap');
            if (d.note && d.note.trim() !== '') {
                document.getElementById('detail-note').textContent = d.note;
                noteWrap.style.display = 'block';
            } else {
                noteWrap.style.display = 'none';
            }

            document.getElementById('detail-modal').style.display = 'flex';
        }
    </script>
@endpush
