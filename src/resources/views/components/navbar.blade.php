@php
    $user = auth()->user();
    $avatarUrl = $user?->avatar
        ? asset('storage/' . $user->avatar)
        : 'https://ui-avatars.com/api/?name=' . urlencode($user?->name ?? 'Guest') . '&background=2563a8&color=fff&size=80&bold=true';
@endphp

@push('styles')
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined" />
    <style>
        /* Container for the right side of the navbar */
        .header-actions,
        .navbar-right {
            display: flex;
            align-items: center;
            gap: 12px; /* Uniform spacing between all elements */
        }

        /* Enforce unified rules for all top navbar action buttons and Material Symbols */
        .nav-pill-btn,
        .navbar-toggle,
        .action-btn {
            position: relative;
            display: flex;
            align-items: center;
            justify-content: center;
            width: 40px !important;
            height: 40px !important;
            min-width: 40px !important;
            min-height: 40px !important;
            background-color: #f1f3f5; /* Light grey circle background */
            border-radius: 50%;
            border: none;
            color: #495057;
            cursor: pointer;
            transition: background-color 0.2s ease, color 0.2s ease;
            padding: 0 !important; /* Resets unexpected browser padding */
        }

        .nav-pill-btn:hover,
        .navbar-toggle:hover,
        .action-btn:hover {
            background-color: #e9ecef;
            color: #2563a8;
        }

        /* Restrict all navbar icon font dimensions to exactly 20px */
        .nav-pill-btn .material-symbols-outlined,
        .navbar-toggle .material-symbols-outlined,
        .navbar-user .material-symbols-outlined,
        .action-btn .material-symbols-outlined {
            font-size: 20px !important;
            width: 20px;
            height: 20px;
            line-height: 1;
            flex-shrink: 0;
            display: inline-block;
        }

        /* Specific link-wrapper correction for POS link */
        .nav-pill-wrap a {
            text-decoration: none;
            display: inline-block;
        }

        /* Notification Badge position adjustment */
        .nav-pill-btn .notif-badge,
        .action-btn.has-badge .badge {
            position: absolute;
            top: 10px;
            right: 10px;
            width: 8px;
            height: 8px;
            background-color: #fa5252; /* Red dot badge */
            border-radius: 50%;
            border: 1.5px solid #ffffff; /* Clean edge */
        }

        /* User profile button layout alignment */
        .navbar-user,
        .user-profile-toggle {
            display: flex;
            align-items: center;
            gap: 10px;
            padding: 4px 12px;
            border-radius: 30px; /* Pill layout shape */
            background-color: #f1f3f5;
            cursor: pointer;
            user-select: none;
            transition: background-color 0.2s ease;
        }

        .navbar-user:hover,
        .user-profile-toggle:hover {
            background-color: #e9ecef;
        }

        /* Avatar Image sizing constraints */
        .avatar-img-sm,
        .profile-avatar {
            width: 30px !important;
            height: 30px !important;
            min-width: 30px !important;
            min-height: 30px !important;
            border-radius: 50%;
            object-fit: cover;
        }

        /* Stack the username and role text properly */
        .user-info,
        .user-meta {
            display: flex;
            flex-direction: column;
            justify-content: center;
            line-height: 1.3;
        }

        .user-name {
            font-size: 13px;
            font-weight: 600;
            color: #212529;
        }

        .user-position,
        .user-role {
            font-size: 10px;
            color: #868e96;
        }

        /* Dropdown interior items size normalization */
        .dropdown-item .material-symbols-outlined {
            font-size: 18px !important;
            width: 18px;
            height: 18px;
            margin-right: 8px;
            vertical-align: middle;
        }
    </style>
@endpush

<nav class="navbar">
    <div class="navbar-left">
        <button id="sidebarToggle" class="navbar-toggle" aria-label="Toggle sidebar">
            <span class="material-symbols-outlined">menu</span>
        </button>
        <div class="panel-logo">
            <img src="{{ asset('assets/images/logo.png') }}" alt="{{ config('app.name') }}">
        </div>
    </div>

    <div class="navbar-right">

        {{-- POS Terminal Button --}}
        <div class="nav-pill-wrap" id="posWrap">
            <a href="{{ route('cashier.pos') }}">
                <button class="nav-pill-btn" id="posBtn" title="POS Terminal">
                    <span class="material-symbols-outlined">desktop_windows</span>
                </button>
            </a>
        </div>

        {{-- Language Switcher --}}
        <div class="nav-pill-wrap" id="langWrap">
            <button class="nav-pill-btn icon-only" id="langBtn" aria-label="Switch Language">
                <img src="{{ asset('assets/images/icon-flag/cambodia.svg') }}" style="width: 20px" alt="Khmer" class="flag-icon">
            </button>

            <div class="nav-pill-dropdown lang-dropdown-flags" id="langDropdown">
                <button class="pill-dropdown-item" data-lang="km">
                    <img src="{{ asset('assets/images/icon-flag/cambodia.svg') }}" style="width:20px" alt="Khmer">
                    <span>ខ្មែរ</span>
                </button>
                <button class="pill-dropdown-item active" data-lang="en">
                    <img src="{{ asset('assets/images/icon-flag/english.svg') }}" style="width:20px" alt="English">
                    <span>English</span>
                </button>
            </div>
        </div>

        {{-- Fullscreen Toggle --}}
        <button class="nav-pill-btn icon-only" id="fsBtn" aria-label="Toggle fullscreen">
            <span class="material-symbols-outlined">fullscreen</span>
        </button>

        {{-- Notifications --}}
        <div class="nav-pill-wrap" id="notifWrap">
            <button class="nav-pill-btn icon-only" id="notifBtn" aria-label="Notifications">
                <span class="material-symbols-outlined">notifications</span>
                <span class="notif-badge" id="notifBadge"></span>
            </button>
            <div class="nav-pill-dropdown notif-dropdown" id="notifDropdown">
                <div class="pill-dropdown-header">Notifications</div>
                @for($i = 0; $i < 5; $i++)
                    <div class="notif-item">
                        <div class="notif-title">Low stock: Coca-Cola 330ml</div>
                        <div class="notif-time">{{ $i+10 }} min ago</div>
                    </div>
                @endfor
            </div>
        </div>

        {{-- Profile --}}
        @auth
        <div class="navbar-user" id="userDropdownToggle">
            <img src="{{ $avatarUrl }}" alt="Avatar" class="avatar-img-sm">
            <div class="user-info">
                <span class="user-name">{{ $user->name }}</span>
                <span class="user-position">{{ $user->roles->first()?->name ?? 'No Role' }}</span>
            </div>
            <span class="material-symbols-outlined">arrow_drop_down</span>

            <div class="navbar-dropdown" id="userDropdown">
                <div class="dropdown-user-header">
                    <img src="{{ $avatarUrl }}" alt="" class="avatar-img-lg">
                    <div>
                        <div class="dropdown-user-name">{{ $user->name }}</div>
                        <div class="dropdown-user-role">{{ $user->roles->first()?->name ?? 'No Role' }}</div>
                    </div>
                </div>
                <hr>
                <a href="" class="dropdown-item">
                    <span class="material-symbols-outlined">person</span>
                    Profile
                </a>
                <a href="" class="dropdown-item">
                    <span class="material-symbols-outlined">settings</span>
                    Settings
                </a>
                <hr>

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
@endpush
