<header class="pos-nav">

    {{-- Left Side: Brand --}}
    <div class="pos-nav__left">
        <div class="pos-nav__brand">
            {{-- <span class="pos-nav__brand-icon"> --}}
                <img src="{{ asset('assets/images/logo.png') }}" style="width:120px; padding:10px" alt="{{ config('app.name') }}">
                {{-- <i data-lucide="shopping-bag"></i> --}}
            {{-- </span> --}}
            {{-- <span class="pos-nav__brand-name">POS System</span> --}}
        </div>
    </div>

    {{-- Right Side: Actions --}}
    <div class="pos-nav__right">

        <button class="pos-nav__action-btn" id="fsBtn" aria-label="Toggle screen">
            <i class="ti ti-arrows-maximize" id="fsIcon"></i>
            {{-- <i data-lucide="scan"></i> --}}
        </button>
        {{-- Reports --}}
        <button class="pos-nav__action-btn" title="Reports" aria-label="View Reports">
            <i data-lucide="bar-chart-2"></i>
            <span>Report</span>
        </button>

        {{-- Customers --}}
        <button class="pos-nav__action-btn" title="Customers" aria-label="Manage Customers">
            <i data-lucide="users"></i>
            {{-- <span>Customer</span> --}}
            <select name="" id="" class="select-buyer">
                <option value="default">Wakin</option>
                <option value="default">Other</option>
            </select>
        </button>

        {{-- User Avatar (Fixed stray structural markup character) --}}
        <div class="pos-nav__avatar" title="{{ Auth::user()->name ?? 'User' }}">
            @if(Auth::check() && Auth::user()->avatar)
                <img src="{{ Storage::url(Auth::user()->avatar) }}"
                     alt="{{ Auth::user()->name }}"
                     class="pos-nav__avatar-img">
            @else
                <span class="pos-nav__avatar-initials">
                    {{ strtoupper(substr(Auth::user()->name ?? 'U', 0, 2)) }}
                </span>
            @endif
            <span class="pos-nav__avatar-status"></span>
        </div>

        {{-- Logout Button --}}
        <form method="POST" action="{{ route('auth.logout') }}" class="pos-nav__logout-form">
            @csrf
            <button type="submit" class="pos-nav__logout-btn"  title="Logout" aria-label="Logout Panel"
                    onclick="return confirm('Are you sure you want to logout?')">
                <i data-lucide="log-out"></i>
            </button>
        </form>
    </div>
</header>
