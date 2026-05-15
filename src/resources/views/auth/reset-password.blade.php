@extends('layouts.auth')

@section('title', 'Reset Password | Point of Sale')

@section('content')
<div class="panel-form">
    <div class="form-center">

        {{-- Header --}}
        <div class="form-head">
            <h1>Reset Password</h1>
            <p>Enter and confirm your new password below.</p>
        </div>

        {{-- Error Messages --}}
        @if ($errors->any())
            <div style="
                background: #fef2f2;
                border: 1px solid #fecaca;
                border-radius: .55rem;
                padding: .75rem 1rem;
                margin-bottom: 1.2rem;
                font-size: .84rem;
                color: #dc2626;
            ">
                @foreach ($errors->all() as $error)
                    <div>{{ $error }}</div>
                @endforeach
            </div>
        @endif

        {{-- Success Message --}}
        @if (session('success'))
            <div style="
                background: #f0fdf4;
                border: 1px solid #bbf7d0;
                border-radius: .55rem;
                padding: .75rem 1rem;
                margin-bottom: 1.2rem;
                font-size: .84rem;
                color: #16a34a;
            ">
                {{ session('success') }}
            </div>
        @endif

        <form method="POST" action="{{ route('auth.reset-password.post') }}" id="reset-form">
            @csrf

            {{-- New Password --}}
            <div class="field-group" style="margin-bottom: 1.2rem;">
                <label class="field-label" for="password">New Password</label>
                <div class="pw-wrap" style="margin-bottom: 0;">
                    <input type="password"
                           id="password"
                           name="password"
                           placeholder="Enter new password"
                           required
                           autofocus>
                    <button type="button" class="pw-toggle" id="toggle-password" aria-label="Toggle password">
                        <svg id="eye-1" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/>
                            <circle cx="12" cy="12" r="3"/>
                        </svg>
                    </button>
                </div>
            </div>

            {{-- Confirm Password --}}
            <div class="field-group" style="margin-bottom: 1.5rem;">
                <label class="field-label" for="password_confirmation">Confirm Password</label>
                <div class="pw-wrap" style="margin-bottom: 0;">
                    <input type="password"
                           id="password_confirmation"
                           name="password_confirmation"
                           placeholder="Confirm new password"
                           required>
                    <button type="button" class="pw-toggle" id="toggle-confirm" aria-label="Toggle password">
                        <svg id="eye-2" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/>
                            <circle cx="12" cy="12" r="3"/>
                        </svg>
                    </button>
                </div>
            </div>

            {{-- Submit --}}
            <button type="submit" class="btn-login">Reset Password</button>

            {{-- Back to Login --}}
            <div class="otp-footer" style="margin-top: 1rem;">
                <p>Remember your password? <a href="{{ route('auth.login') }}">Login</a></p>
            </div>

        </form>
    </div>
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', () => {

    const form     = document.getElementById('reset-form');
    const password = document.getElementById('password');
    const confirm  = document.getElementById('password_confirmation');

    // ── Password match validation ──
    form.addEventListener('submit', (e) => {
        if (password.value !== confirm.value) {
            e.preventDefault();
            confirm.style.borderColor = '#e24b4a';
            confirm.setCustomValidity('Passwords do not match.');
            confirm.reportValidity();
            return;
        }

        if (password.value.length < 6) {
            e.preventDefault();
            password.style.borderColor = '#e24b4a';
            password.setCustomValidity('Password must be at least 6 characters.');
            password.reportValidity();
            return;
        }
    });

    confirm.addEventListener('input', () => {
        confirm.style.borderColor = '';
        confirm.setCustomValidity('');
    });

    password.addEventListener('input', () => {
        password.style.borderColor = '';
        password.setCustomValidity('');
    });

    // ── Password toggle visibility ──
    const eyeOpen = `
        <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/>
        <circle cx="12" cy="12" r="3"/>
    `;
    const eyeClosed = `
        <path d="M17.94 17.94A10.07 10.07 0 0 1 12 20c-7 0-11-8-11-8a18.45 18.45 0 0 1 5.06-5.94"/>
        <path d="M9.9 4.24A9.12 9.12 0 0 1 12 4c7 0 11 8 11 8a18.5 18.5 0 0 1-2.16 3.19"/>
        <line x1="1" y1="1" x2="23" y2="23"/>
    `;

    document.getElementById('toggle-password').addEventListener('click', () => {
        const isPassword = password.type === 'password';
        password.type = isPassword ? 'text' : 'password';
        document.getElementById('eye-1').innerHTML = isPassword ? eyeClosed : eyeOpen;
    });

    document.getElementById('toggle-confirm').addEventListener('click', () => {
        const isPassword = confirm.type === 'password';
        confirm.type = isPassword ? 'text' : 'password';
        document.getElementById('eye-2').innerHTML = isPassword ? eyeClosed : eyeOpen;
    });

});
</script>
@endpush
