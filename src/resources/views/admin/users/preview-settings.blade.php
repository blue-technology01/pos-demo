@extends('layouts.app')

@push('styles')
    <link rel="stylesheet" href="{{ asset('assets/css/dashboard/printer/printer.css') }}" data-turbo-track="reload" >
@endpush

@section('title', 'User Preview Settings')

@section('content')
    <main class="page">
        <nav class="breadcrumb">
            <a href="#">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
                    <polyline points="15 18 9 12 15 6"/>
                </svg>
                Printer Settings
            </a>
        </nav>

        {{-- Success Message --}}
        @if(session('success'))
            <div style="margin: 20px 0; padding: 15px 20px; background: #d4edda; color: #167c2e; border: 1px solid #c3e6cb; border-radius: 8px; font-weight: 500;">
                {{ session('success') }}
            </div>
        @endif

        <form id="printer-form" method="POST" action="">
            @csrf
            @method('PUT')

            {{-- User Preview Permissions --}}
            <section class="card">
                <p class="card-title">User Preview Permissions</p>

                <p style="font-size:0.85rem; color:var(--text-muted); margin-bottom:20px; line-height:1.55;">
                    Toggle <strong style="color:var(--accent);">Preview ON</strong> for users who should see the receipt
                    on screen before printing.<br>
                    Users with <strong>Preview OFF</strong> will have the PDF downloaded silently after confirming payment.
                </p>

                <div class="preview-summary">
                    <span class="summary-pill on"><span class="dot"></span><span id="count-on">0</span> with preview</span>
                    <span class="summary-pill off"><span class="dot"></span><span id="count-off">0</span> no preview</span>
                </div>

                <div class="search-wrap">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/>
                    </svg>
                    <input type="text" id="user-search" placeholder="Search by name or email..." oninput="filterUsers(this.value)">
                </div>

                <div class="bulk-bar">
                    <span id="visible-count"></span>
                    <div class="bulk-actions">
                        <button type="button" class="btn-bulk" onclick="bulkSet(true)">Enable All</button>
                        <button type="button" class="btn-bulk" onclick="bulkSet(false)">Disable All</button>
                    </div>
                </div>

                <div class="user-list" id="user-list">
                    @php
                        $users = \App\Models\User::orderBy('name')->get();
                        $palette = ['#6c63e0','#e06c63','#2cc084','#e0a63c','#3c8ce0','#c063e0','#e06363','#63c0e0','#e09c3c','#3ce0c0'];
                    @endphp

                    @foreach($users as $index => $user)
                        @php
                            $color = $palette[$index % count($palette)];
                        @endphp
                        <div class="user-row"
                             data-id="{{ $user->id }}"
                             data-name="{{ strtolower($user->name) }}"
                             data-email="{{ strtolower($user->email ?? '') }}">

                            <div class="user-info">
                                <div class="avatar" style="background-color: {{ $color }};">
                                    {{ strtoupper(substr($user->name, 0, 1)) }}
                                </div>
                                <div>
                                    <span class="user-name">{{ $user->name }}</span>
                                    <span class="user-email">{{ $user->email }}</span>
                                </div>
                            </div>

                            <div class="toggle-switch">
                                <input type="checkbox"
                                       id="preview_{{ $user->id }}"
                                       name="preview_users[{{ $user->id }}]"
                                       value="1"
                                       {{ $user->preview_receipt ? 'checked' : '' }}
                                       onchange="onToggle(this, {{ $user->id }})">
                                <label for="preview_{{ $user->id }}"
                                       class="toggle-label {{ $user->preview_receipt ? 'on' : 'off' }}"
                                       id="lbl-{{ $user->id }}">
                                    {{ $user->preview_receipt ? 'Preview' : 'No Preview' }}
                                </label>
                            </div>
                        </div>
                    @endforeach

                    <div class="no-results" id="no-results" style="display:none;">No users found.</div>
                </div>
            </section>

            {{-- <div style="margin-top: 30px;">
                <button type="submit" class="btn-primary" style="padding:14px 32px; font-size:1.1rem;">
                    Save Preview Settings
                </button>
            </div> --}}
        </form>
    </main>
@endsection

@push('scripts')
<script>

    function onToggle(cb, userId) {

            const lbl = document.getElementById('lbl-' + userId);

            // UI Update
            if (lbl) {
                lbl.textContent = cb.checked ? 'Preview' : 'No Preview';

                lbl.className =
                    'toggle-label ' + (cb.checked ? 'on' : 'off');
            }

           fetch('/admin/preview/update', {

            method: 'POST',

            headers: {
                'Content-Type': 'application/json',

                'X-CSRF-TOKEN': document.querySelector(
                    'meta[name="csrf-token"]'
                ).content
            },

            body: JSON.stringify({
                user_id: userId,
                preview: cb.checked
            })

        })
        .then(async res => {

            const text = await res.text();

            console.log(text);

            return text;

        })
        .catch(err => {

            console.error(err);

        });

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
            const match = !term ||
                         row.dataset.name.includes(term) ||
                         row.dataset.email.includes(term);
            row.classList.toggle('hidden', !match);
            if (match) visible++;
        });

        document.getElementById('visible-count').textContent = term ? `${visible} result${visible !== 1 ? 's' : ''}` : '';
        document.getElementById('no-results').style.display = visible === 0 ? 'block' : 'none';
    }

    function bulkSet(state) {
        document.querySelectorAll('.user-row:not(.hidden) input[type=checkbox]').forEach(cb => {
            cb.checked = state;
            const userId = cb.id.replace('preview_', '');
            const lbl = document.getElementById('lbl-' + userId);
            if (lbl) {
                lbl.textContent = state ? 'Preview' : 'No Preview';
                lbl.className = 'toggle-label ' + (state ? 'on' : 'off');
            }
        });
        updateCounts();
    }

    document.addEventListener('DOMContentLoaded', () => {
        updateCounts();
    });
</script>
@endpush
