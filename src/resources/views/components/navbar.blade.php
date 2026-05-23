@php
    $user = auth()->user();
    $avatarUrl = $user?->avatar
        ? asset('storage/' . $user->avatar)
        : 'https://ui-avatars.com/api/?name=' . urlencode($user?->name ?? 'Guest') . '&background=2563a8&color=fff&size=80&bold=true';
@endphp

<nav class="navbar">
    <div class="navbar-left">
        <button id="sidebarToggle" class="navbar-toggle" aria-label="Toggle sidebar">
            <i class="ti ti-menu-2"></i>
        </button>
        <div class="panel-logo">
            <img src="{{ asset('assets/images/logo.png') }}" alt="{{ config('app.name') }}">
        </div>
    </div>

    <div class="navbar-right">

        {{-- POS Terminal Button --}}
        <div class="nav-pill-wrap" id="posWrap">
            <a href="{{route('cashier.pos')}}">
                <button class="nav-pill-btn" id="posBtn">
                    <i class="ti ti-device-desktop"></i>
                    <span>POS</span>
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
            <i class="ti ti-arrows-maximize" id="fsIcon"></i>
        </button>

        {{-- Notifications --}}
        <div class="nav-pill-wrap" id="notifWrap">
            <button class="nav-pill-btn icon-only" id="notifBtn" aria-label="Notifications">
                <i class="ti ti-bell"></i>
                <span class="notif-badge" id="notifBadge"></span>
            </button>
            <div class="nav-pill-dropdown notif-dropdown" id="notifDropdown">
                <div class="pill-dropdown-header">Notifications</div>
                @for($i = 0; $i < 5; $i++)
                    <div class="notif-item">
                        <div class="notif-title">Low stock: Coca-Cola 330ml</div>
                        <div class="notif-time">{{$i+10}} min ago</div>
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
            <i class="ti ti-chevron-down"></i>

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
                    <i class="ti ti-user"></i> Profile
                </a>
                <a href="" class="dropdown-item">
                    <i class="ti ti-settings"></i> Settings
                </a>
                <hr>

                <form method="POST" action="{{ route('auth.logout') }}">
                    @csrf
                    <button type="submit" class="dropdown-item text-danger">
                        <i class="ti ti-logout"></i> Logout
                    </button>
                </form>
            </div>
        </div>
        @endauth
    </div>
</nav>
