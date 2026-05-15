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
        @auth
        <div class="navbar-user" id="userDropdownToggle">

            <img src="{{ $avatarUrl }}" alt="Avatar" class="avatar-img-lg">

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

                <button type="button" class="dropdown-item text-danger"
                    onclick="document.getElementById('logout-form').submit()">
                    <i class="ti ti-logout"></i> Logout
                </button>
            </div>
        </div>
        @endauth
    </div>
</nav>

@auth
<form id="logout-form" method="POST" action="{{ route('auth.logout') }}" style="display:none">
    @csrf
</form>
@endauth

<script>
document.addEventListener('DOMContentLoaded', function () {
    const toggle = document.getElementById('userDropdownToggle');
    if (!toggle) return;

    toggle.addEventListener('click', function (e) {
        e.stopPropagation();
        toggle.classList.toggle('open');
    });

    document.addEventListener('click', function (e) {
        if (!toggle.contains(e.target)) {
            toggle.classList.remove('open');
        }
    });

    document.addEventListener('keydown', function (e) {
        if (e.key === 'Escape') toggle.classList.remove('open');
    });
});
</script>
