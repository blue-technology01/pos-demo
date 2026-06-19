@extends('layouts.app')

@push('styles')
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600&display=swap" rel="stylesheet" />
    <link rel="stylesheet" href="{{ asset('assets/css/dashboard/printer/printer.css') }}" data-turbo-track="reload">
@endpush

@section('title', 'Printer Settings')

@section('content')

<main class="page">

    {{-- ── Breadcrumb ── --}}
    <nav class="breadcrumb">
        <a href="#">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2"
                 stroke-linecap="round" stroke-linejoin="round">
                <polyline points="15 18 9 12 15 6"/>
            </svg>
            Printer settings
        </a>
    </nav>

    {{-- ── Success alert ── --}}
    @if(session('success'))
        <div class="printer-alert">
            {{ session('success') }}
        </div>
    @endif

    <form id="printer-form" method="POST" action="">
        @csrf
        @method('PUT')

        {{-- ── Card ── --}}
        <section class="card">

            <p class="card-title">User preview permissions</p>

            <p class="card-desc">
                Toggle <strong>Preview ON</strong> for users who should see the receipt on screen before printing.<br>
                Users with <strong class="muted">Preview OFF</strong> will have the PDF downloaded silently after confirming payment.
            </p>

            {{-- Summary pills --}}
            <div class="preview-summary">
                <span class="summary-pill on">
                    <span class="dot"></span>
                    <span id="count-on">0</span> with preview
                </span>
                <span class="summary-pill off">
                    <span class="dot"></span>
                    <span id="count-off">0</span> no preview
                </span>
            </div>

            {{-- Search --}}
            <div class="search-wrap">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                     stroke-linecap="round" stroke-linejoin="round">
                    <circle cx="11" cy="11" r="8"/>
                    <line x1="21" y1="21" x2="16.65" y2="16.65"/>
                </svg>
                <input
                    type="text"
                    id="user-search"
                    placeholder="Search by name or email..."
                    oninput="filterUsers(this.value)"
                    autocomplete="off"
                >
            </div>

            {{-- Bulk actions --}}
            <div class="bulk-bar">
                <span id="visible-count"></span>
                <div class="bulk-actions">
                    <button type="button" class="btn-bulk" onclick="bulkSet(true)">Enable all</button>
                    <button type="button" class="btn-bulk" onclick="bulkSet(false)">Disable all</button>
                </div>
            </div>

            {{-- User list --}}
            <div class="user-list" id="user-list">

                @php
                    $users   = \App\Models\User::orderBy('name')->get();
                    $palette = [
                        '#6c63e0','#e06c63','#2cc084','#e0a63c',
                        '#3c8ce0','#c063e0','#e06363','#63c0e0',
                        '#e09c3c','#3ce0c0'
                    ];
                @endphp

                @foreach($users as $index => $user)
                    @php $color = $palette[$index % count($palette)]; @endphp

                    <div
                        class="user-row"
                        data-id="{{ $user->id }}"
                        data-name="{{ strtolower($user->name) }}"
                        data-email="{{ strtolower($user->email ?? '') }}"
                    >
                        <div class="user-info">
                            <div class="avatar" style="background-color: {{ $color }}">
                                {{ strtoupper(substr($user->name, 0, 1)) }}
                            </div>
                            <div>
                                <span class="user-name">{{ $user->name }}</span>
                                <span class="user-email">{{ $user->email }}</span>
                            </div>
                        </div>

                        <div class="toggle-switch">
                            <input
                                type="checkbox"
                                id="preview_{{ $user->id }}"
                                name="preview_users[{{ $user->id }}]"
                                value="1"
                                {{ $user->preview_receipt ? 'checked' : '' }}
                                onchange="onToggle(this, {{ $user->id }})"
                            >
                            <label
                                for="preview_{{ $user->id }}"
                                class="toggle-label {{ $user->preview_receipt ? 'on' : 'off' }}"
                                id="lbl-{{ $user->id }}"
                            >
                                {{ $user->preview_receipt ? 'Preview' : 'No preview' }}
                            </label>
                        </div>
                    </div>
                @endforeach

                <div class="no-results" id="no-results" style="display:none">
                    No users found.
                </div>

            </div>
        </section>

    </form>

</main>

@endsection

@push('scripts')
<script>
(function () {

    window.onToggle = function (cb, userId) {
        const lbl = document.getElementById('lbl-' + userId);

        if (lbl) {
            lbl.textContent = cb.checked ? 'Preview' : 'No preview';
            lbl.className   = 'toggle-label ' + (cb.checked ? 'on' : 'off');
        }

        fetch('/admin/preview/update', {
            method  : 'POST',
            headers : {
                'Content-Type' : 'application/json',
                'X-CSRF-TOKEN' : document.querySelector('meta[name="csrf-token"]').content,
            },
            body: JSON.stringify({ user_id: userId, preview: cb.checked }),
        })
        .then(res => res.text())
        .then(text => console.log('Preview updated:', text))
        .catch(err => console.error('Preview update error:', err));

        updateCounts();
    };

    function updateCounts() {
        const all = document.querySelectorAll('#user-list input[type="checkbox"]');
        const on  = [...all].filter(c => c.checked).length;
        document.getElementById('count-on').textContent  = on;
        document.getElementById('count-off').textContent = all.length - on;
    }

    window.filterUsers = function (q) {
        const rows   = document.querySelectorAll('.user-row');
        const term   = q.toLowerCase().trim();
        let   visible = 0;

        rows.forEach(row => {
            const match = !term ||
                row.dataset.name.includes(term)  ||
                row.dataset.email.includes(term);
            row.classList.toggle('hidden', !match);
            if (match) visible++;
        });

        document.getElementById('visible-count').textContent =
            term ? `${visible} result${visible !== 1 ? 's' : ''}` : '';
        document.getElementById('no-results').style.display =
            visible === 0 ? 'block' : 'none';
    };

    window.bulkSet = function (state) {
        document.querySelectorAll('.user-row:not(.hidden) input[type="checkbox"]')
            .forEach(cb => {
                cb.checked = state;
                const userId = cb.id.replace('preview_', '');
                const lbl    = document.getElementById('lbl-' + userId);
                if (lbl) {
                    lbl.textContent = state ? 'Preview' : 'No preview';
                    lbl.className   = 'toggle-label ' + (state ? 'on' : 'off');
                }
            });
        updateCounts();
    };

    document.addEventListener('DOMContentLoaded', updateCounts);

})();
</script>
@endpush
