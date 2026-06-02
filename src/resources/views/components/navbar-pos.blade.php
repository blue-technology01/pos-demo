{{-- POS HEADER / NAVBAR --}}
<header class="pos-nav">

    {{-- Left Side: Brand --}}
    <div class="pos-nav__left">
        <a href="#" class="pos-nav__brand">
            <img src="{{ asset('assets/images/logo.png') }}" alt="{{ config('app.name') }}">
        </a>
    </div>

    {{-- Right Side: Actions --}}
    <div class="pos-nav__right">

        {{-- Fullscreen Toggle --}}
        <button class="pos-nav__action-btn" id="fsBtn" title="Toggle Fullscreen">
            <span class="material-symbols-outlined">fullscreen</span>
        </button>
       {{-- Selected customer display — click to open dropdown --}}
            <div class="pos-customer__selected" id="customer-selected-display"
                 onclick="toggleCustomerDropdown()">
                <span id="customer-selected-name">Walk-in Customer</span>
                <span class="material-symbols-outlined" style="font-size:16px!important;color:#94a3b8;">
                    expand_more
                </span>
        </div>

        {{-- User Avatar --}}
        <div class="pos-nav__avatar" title="{{ Auth::user()->name ?? 'User' }}">
            @if(Auth::check() && Auth::user()->avatar)
                <img src="{{ Storage::url(Auth::user()->avatar) }}" alt="{{ Auth::user()->name }}" class="pos-nav__avatar-img">
            @else
                <span class="pos-nav__avatar-initials">
                    {{ strtoupper(substr(Auth::user()->name ?? 'U', 0, 2)) }}
                </span>
            @endif
            <span class="pos-nav__avatar-status"></span>
        </div>

        {{-- Logout --}}
        <form method="POST" action="{{ route('auth.logout') }}" class="pos-nav__logout-form">
            @csrf
            <button type="submit" class="pos-nav__logout-btn" onclick="return confirm('Are you sure you want to logout?')">
                <span class="material-symbols-outlined">logout</span>
            </button>
        </form>

    </div>
</header>
