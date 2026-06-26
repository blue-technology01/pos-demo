@php
    $user     = auth()->user();
    $avatarUrl = $user?->avatar
        ? asset('storage/' . $user->avatar)
        : 'https://ui-avatars.com/api/?name=' . urlencode($user?->name ?? 'Guest') . '&background=2563a8&color=fff&size=80&bold=true';
@endphp

{{-- @push('styles') --}}
<link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined" />
<style>
    .navbar {
        display: flex;
        align-items: center;
        justify-content: space-between;
        padding: 0 20px;
        height: 60px;
        background: #ffffff;
        border-bottom: 1px solid #e9ecef;
        position: sticky;
        top: 0;
        z-index: 100;
    }

    .navbar-left,
    .navbar-right {
        display: flex;
        align-items: center;
        gap: 8px;
    }

    .nav-pill-btn {
        position: relative;
        display: flex;
        align-items: center;
        justify-content: center;
        width: 38px;
        height: 38px;
        background: #f1f3f5;
        border-radius: 50%;
        border: none;
        color: #495057;
        cursor: pointer;
        transition: background 0.18s, color 0.18s;
        padding: 0;
        flex-shrink: 0;
    }

    .nav-pill-btn:hover {
        background: #e9ecef;
        color: #2563a8;
    }

    .nav-pill-btn .material-symbols-outlined {
        font-size: 20px;
        line-height: 1;
    }

    .navbar-toggle {
        display: flex;
        align-items: center;
        justify-content: center;
        width: 38px;
        height: 38px;
        background: #f1f3f5;
        border-radius: 50%;
        border: none;
        color: #495057;
        cursor: pointer;
        transition: background 0.18s;
        padding: 0;
    }

    .navbar-toggle:hover {
        background: #e9ecef;
        color: #2563a8;
    }

    .navbar-toggle .material-symbols-outlined {
        font-size: 20px;
    }

    .notif-badge {
        position: absolute;
        top: 7px;
        right: 7px;
        width: 8px;
        height: 8px;
        background: #fa5252;
        border-radius: 50%;
        border: 1.5px solid #ffffff;
        display: none;
    }

    .nav-pill-wrap {
        position: relative;
    }

    .nav-pill-dropdown {
        position: absolute;
        top: calc(100% + 10px);
        right: 0;
        background: #ffffff;
        border: 1px solid #e9ecef;
        border-radius: 12px;
        box-shadow: 0 8px 24px rgba(0, 0, 0, 0.10);
        display: none;
        z-index: 200;
        overflow: hidden;
    }

    .nav-pill-wrap:focus-within .nav-pill-dropdown,
    .nav-pill-wrap.open .nav-pill-dropdown {
        display: block;
    }

    .notif-dropdown {
        width: 320px;
        max-height: 420px;
        overflow-y: auto;
    }

    .notif-dropdown-header {
        display: flex;
        align-items: center;
        justify-content: space-between;
        padding: 14px 16px 10px;
        border-bottom: 1px solid #f1f3f5;
    }

    .notif-dropdown-header h6 {
        font-size: 13px;
        font-weight: 700;
        color: #212529;
        margin: 0;
        letter-spacing: 0.3px;
    }

    .notif-mark-all {
        font-size: 11px;
        color: #2563a8;
        background: none;
        border: none;
        cursor: pointer;
        padding: 0;
        font-weight: 500;
        text-decoration: none;
    }

    .notif-mark-all:hover {
        text-decoration: underline;
    }

    .notif-item {
        display: flex;
        align-items: flex-start;
        gap: 10px;
        padding: 12px 16px;
        border-bottom: 1px solid #f8f9fa;
        transition: background 0.15s;
        cursor: default;
    }

    .notif-item:last-child {
        border-bottom: none;
    }

    .notif-item:hover {
        background: #f8f9fa;
    }

    /* Icon circle per type */
    .notif-icon {
        width: 34px;
        height: 34px;
        min-width: 34px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 16px;
    }

    .notif-icon.expiry {
        background: #fff3cd;
        color: #e67700;
    }

    .notif-icon.low_stock {
        background: #ffe3e3;
        color: #c92a2a;
    }

    .notif-body {
        flex: 1;
        min-width: 0;
    }

    .notif-title {
        font-size: 12.5px;
        font-weight: 600;
        color: #212529;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
    }

    .notif-message {
        font-size: 11.5px;
        color: #868e96;
        margin-top: 2px;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
    }

    .notif-empty,
    .notif-loading,
    .notif-error {
        padding: 28px 16px;
        text-align: center;
        color: #adb5bd;
        font-size: 12.5px;
    }

    .notif-empty .material-symbols-outlined,
    .notif-error .material-symbols-outlined {
        font-size: 32px;
        display: block;
        margin-bottom: 6px;
    }

    .lang-dropdown-flags {
        width: 140px;
        padding: 6px;
    }

    .pill-dropdown-item {
        display: flex;
        align-items: center;
        gap: 8px;
        padding: 7px 10px;
        border-radius: 8px;
        border: none;
        background: none;
        cursor: pointer;
        width: 100%;
        font-size: 13px;
        color: #495057;
        transition: background 0.15s;
    }

    .pill-dropdown-item:hover,
    .pill-dropdown-item.active {
        background: #f1f3f5;
        color: #2563a8;
    }

    .navbar-user {
        position: relative;
        display: flex;
        align-items: center;
        gap: 8px;
        padding: 5px 12px 5px 5px;
        border-radius: 30px;
        background: #f1f3f5;
        cursor: pointer;
        user-select: none;
        transition: background 0.18s;
        border: none;
    }

    .navbar-user:hover {
        background: #e9ecef;
    }

    .avatar-img-sm {
        width: 28px;
        height: 28px;
        border-radius: 50%;
        object-fit: cover;
        flex-shrink: 0;
    }

    .user-info {
        display: flex;
        flex-direction: column;
        line-height: 1.3;
    }

    .user-name {
        font-size: 12.5px;
        font-weight: 600;
        color: #212529;
    }

    .user-position {
        font-size: 10.5px;
        color: #868e96;
    }

    .navbar-user .material-symbols-outlined {
        font-size: 18px;
        color: #868e96;
    }
    /* user dropdown */
    .navbar-dropdown {
        position: absolute;
        top: calc(100% + 10px);
        right: 0;
        width: 220px;
        background: #ffffff;
        border: 1px solid #e9ecef;
        border-radius: 12px;
        box-shadow: 0 8px 24px rgba(0,0,0,0.10);
        display: none;
        z-index: 200;
        overflow: hidden;
        padding: 6px;
    }

    .nav-pill-wrap.open .navbar-dropdown,
    .navbar-user-wrap.open .navbar-dropdown {
        display: block;
    }

    .dropdown-user-header {
        display: flex;
        align-items: center;
        gap: 10px;
        padding: 10px;
        margin-bottom: 4px;
    }

    .avatar-img-lg {
        width: 38px;
        height: 38px;
        border-radius: 50%;
        object-fit: cover;
    }

    .dropdown-user-name {
        font-size: 13px;
        font-weight: 600;
        color: #212529;
    }

    .dropdown-user-role {
        font-size: 11px;
        color: #868e96;
    }

    .dropdown-divider {
        height: 1px;
        background: #f1f3f5;
        margin: 4px 0;
    }

    .dropdown-item {
        display: flex;
        align-items: center;
        gap: 8px;
        padding: 8px 10px;
        border-radius: 8px;
        font-size: 13px;
        color: #495057;
        text-decoration: none;
        background: none;
        border: none;
        cursor: pointer;
        width: 100%;
        transition: background 0.15s;
    }

    .dropdown-item:hover {
        background: #f1f3f5;
        color: #2563a8;
    }

    .dropdown-item.text-danger {
        color: #c92a2a;
    }

    .dropdown-item.text-danger:hover {
        background: #ffe3e3;
        color: #c92a2a;
    }

    .dropdown-item .material-symbols-outlined {
        font-size: 17px;
    }

    .panel-logo img {
        height: 32px;
        object-fit: contain;
    }

    .notif-dropdown::-webkit-scrollbar {
        width: 4px;
    }
    .notif-dropdown::-webkit-scrollbar-track {
        background: transparent;
    }
    .notif-dropdown::-webkit-scrollbar-thumb {
        background: #dee2e6;
        border-radius: 4px;
    }
</style>
{{-- @endpush --}}

<nav class="navbar">

    {{-- Left: Hamburger + Logo --}}
    <div class="navbar-left">
        <button id="sidebarToggle" class="navbar-toggle" aria-label="Toggle sidebar">
            <span class="material-symbols-outlined">menu</span>
        </button>
        <div class="panel-logo">
            <img src="{{ asset('assets/images/logo.png') }}" alt="{{ config('app.name') }}">
        </div>
    </div>

    {{-- Right: Actions + Profile --}}
    <div class="navbar-right">

        {{-- POS Terminal --}}
        <div class="nav-pill-wrap">
            <a href="{{ route('cashier.pos') }}">
                <button class="nav-pill-btn" title="POS Terminal">
                    <span class="material-symbols-outlined">desktop_windows</span>
                </button>
            </a>
        </div>

        {{-- Language Switcher --}}
        <div class="nav-pill-wrap" id="langWrap">
            <button class="nav-pill-btn" id="langBtn" aria-label="Switch Language" aria-expanded="false">
                <img src="{{ asset('assets/images/icon-flag/cambodia.svg') }}" style="width:20px" alt="Khmer">
            </button>
            <div class="nav-pill-dropdown lang-dropdown-flags" id="langDropdown">
                <button class="pill-dropdown-item" data-lang="km">
                    <img src="{{ asset('assets/images/icon-flag/cambodia.svg') }}" style="width:18px" alt="Khmer">
                    <span>ខ្មែរ</span>
                </button>
                <button class="pill-dropdown-item active" data-lang="en">
                    <img src="{{ asset('assets/images/icon-flag/english.svg') }}" style="width:18px" alt="English">
                    <span>English</span>
                </button>
            </div>
        </div>

        {{-- Fullscreen Toggle --}}
        <button class="nav-pill-btn" id="fsBtn" aria-label="Toggle fullscreen">
            <span class="material-symbols-outlined">fullscreen</span>
        </button>

        {{-- Notifications --}}
        <div class="nav-pill-wrap" id="notifWrap">
            <button class="nav-pill-btn" id="notifBtn" aria-label="Notifications" aria-expanded="false">
                <span class="material-symbols-outlined">notifications</span>
                <span class="notif-badge" id="notifBadge"></span>
            </button>

            <div class="nav-pill-dropdown notif-dropdown" id="notifDropdown">

                {{-- Header --}}
                <div class="notif-dropdown-header">
                    <h6>Notifications</h6>
                    <form action="{{ route('notifications.read-all') }}" method="POST" style="margin:0">
                        @csrf
                        @method('PATCH')
                        <button type="submit" class="notif-mark-all">Mark all read</button>
                    </form>
                </div>

                {{-- Items injected by JS --}}
                <div id="notifList">
                    <div class="notif-loading">Loading…</div>
                </div>

            </div>
        </div>

        {{-- User Profile --}}
        @auth
        <div class="navbar-user-wrap nav-pill-wrap" id="userWrap">
            <button class="navbar-user" id="userBtn" aria-expanded="false">
                <img src="{{ $avatarUrl }}" alt="Avatar" class="avatar-img-sm">
                <div class="user-info">
                    <span class="user-name">{{ $user->name }}</span>
                    <span class="user-position">{{ $user->roles->first()?->name ?? 'No Role' }}</span>
                </div>
                <span class="material-symbols-outlined">arrow_drop_down</span>
            </button>

            <div class="navbar-dropdown" id="userDropdown">
                <div class="dropdown-user-header">
                    <img src="{{ $avatarUrl }}" alt="" class="avatar-img-lg">
                    <div>
                        <div class="dropdown-user-name">{{ $user->name }}</div>
                        <div class="dropdown-user-role">{{ $user->roles->first()?->name ?? 'No Role' }}</div>
                    </div>
                </div>
                <div class="dropdown-divider"></div>
                <a href="" class="dropdown-item">
                    <span class="material-symbols-outlined">person</span>
                    Profile
                </a>
                <a href="" class="dropdown-item">
                    <span class="material-symbols-outlined">settings</span>
                    Settings
                </a>
                <div class="dropdown-divider"></div>
                <form method="POST" action="{{ route('auth.logout') }}" data-turbo="false">
                    @csrf
                    <button type="submit" class="dropdown-item text-danger">
                        <span class="material-symbols-outlined">logout</span>
                        Logout
                    </button>
                </form>
            </div>
        </div>
        @endauth

    </div>
</nav>

@push('scripts')
<script src="{{ asset('assets/js/components/togglescreen.js') }}"></script>
<script>
(function () {

    const wrap = document.getElementById('notifWrap');
    const btn = document.getElementById('notifBtn');
    const list = document.getElementById('notifList');
    const badge = document.getElementById('notifBadge');

    const FETCH_URL = "{{ route('notifications.fetch') }}";

    // toggle dropdown
    btn.addEventListener('click', (e) => {
        e.stopPropagation();
        wrap.classList.toggle('open');
    });

    document.addEventListener('click', () => {
        wrap.classList.remove('open');
    });

    function escapeHtml(text) {
        return String(text)
            .replace(/&/g,'&amp;')
            .replace(/</g,'&lt;')
            .replace(/>/g,'&gt;');
    }

    function render(data, count) {

        badge.style.display = count > 0 ? 'flex' : 'none';
        badge.textContent = count > 9 ? '9+' : count;

        if (!data || data.length === 0) {
            list.innerHTML = `<div class="notif-empty">No notifications</div>`;
            return;
        }

        list.innerHTML = data.map(n => `
            <div class="notif-item">
                <div>
                    <div class="notif-title">${escapeHtml(n.title)}</div>
                    <div class="notif-message">${escapeHtml(n.message)}</div>
                </div>
            </div>
        `).join('');
    }

    async function load() {
        try {
            const res = await fetch(FETCH_URL, {
                headers: {
                    'X-Requested-With': 'XMLHttpRequest'
                }
            });

            const json = await res.json();

            render(json.data || [], json.count || 0);

        } catch (e) {
            list.innerHTML = `<div class="notif-error">Failed to load</div>`;
        }
    }

    load();
    setInterval(load, 60000);

})();
</script>
@endpush
