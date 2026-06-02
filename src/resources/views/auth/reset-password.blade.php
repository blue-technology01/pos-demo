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

        <form method="POST"
              action="{{ route('auth.reset-password.post') }}"
              id="reset-form"
              autocomplete="off">

            @csrf

            {{-- Password --}}
            <div class="field-group" style="margin-bottom: 1.2rem;">

                <label class="field-label" for="password">
                    New Password
                </label>

                <div class="pw-wrap">

                    <input type="password"
                           id="password"
                           name="password"
                           placeholder="Enter new password"
                           minlength="8"
                           required
                           value="{{ old('password') }}">
                    @error('password')
                        <div style="color:red;font-size:10px;margin-top:4px;">
                            {{ $message }}
                        </div>
                    @enderror
                    <button type="button" id="toggle-password" class="pw-toggle">
                        <svg id="eye-1" width="18" height="18" viewBox="0 0 24 24"
                             fill="none" stroke="currentColor" stroke-width="2"
                             stroke-linecap="round" stroke-linejoin="round">
                            <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/>
                            <circle cx="12" cy="12" r="3"/>
                        </svg>
                    </button>

                </div>

                <div id="password-error" style="color:#dc2626;font-size:10px;margin-top:4px;"></div>

            </div>

            {{-- Confirm --}}
            <div class="field-group" style="margin-bottom: 1.5rem;">
                <label class="field-label" for="password_confirmation">
                    Confirm Password
                </label>
                <div class="pw-wrap">

                    <input type="password"
                           id="password_confirmation"
                           name="password_confirmation"
                           placeholder="Confirm new password"
                           minlength="8"
                           required
                           value="{{ old('password_confirmation') }}">
                    @error('password_confirmation')
                        <div style="color:red;font-size:10px;margin-top:4px;">
                            {{ $message }}
                        </div>
                    @enderror
                    <button type="button" id="toggle-confirm" class="pw-toggle">
                        <svg id="eye-2" width="18" height="18" viewBox="0 0 24 24"
                             fill="none" stroke="currentColor" stroke-width="2"
                             stroke-linecap="round" stroke-linejoin="round">
                            <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/>
                            <circle cx="12" cy="12" r="3"/>
                        </svg>
                    </button>
                </div>
                <div id="confirm-error" style="color:#dc2626;font-size:10px;margin-top:4px;"></div>
            </div>
            <button type="submit" id="submitBtn" class="btn-login">
                Reset Password
            </button>
            <div class="otp-footer" style="margin-top: 1rem;">
                <p>
                    Remember your password?
                    <a href="{{ route('auth.login') }}">Login</a>
                </p>
            </div>

        </form>

    </div>
</div>

@endsection

@push('scripts')

<script>
document.addEventListener('DOMContentLoaded', function () {

    const password = document.getElementById('password');
    const confirm  = document.getElementById('password_confirmation');

    const passwordError = document.getElementById('password-error');
    const confirmError  = document.getElementById('confirm-error');

    const submitBtn = document.getElementById('submitBtn');

    const eyeOpen = `
        <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/>
        <circle cx="12" cy="12" r="3"/>
    `;

    const eyeClosed = `
        <path d="M17.94 17.94A10.07 10.07 0 0 1 12 20c-7 0-11-8-11-8a18.45 18.45 0 0 1 5.06-5.94"/>
        <path d="M9.9 4.24A9.12 9.12 0 0 1 12 4c7 0 11 8 11 8a18.5 18.5 0 0 1-2.16 3.19"/>
        <line x1="1" y1="1" x2="23" y2="23"/>
    `;

    function toggle(input, iconId) {
        const isPassword = input.type === 'password';
        input.type = isPassword ? 'text' : 'password';
        document.getElementById(iconId).innerHTML =
            isPassword ? eyeClosed : eyeOpen;
    }

    document.getElementById('toggle-password')
        .addEventListener('click', () => toggle(password, 'eye-1'));

    document.getElementById('toggle-confirm')
        .addEventListener('click', () => toggle(confirm, 'eye-2'));

    password.addEventListener('input', () => passwordError.textContent = '');
    confirm.addEventListener('input', () => confirmError.textContent = '');

    document.getElementById('reset-form').addEventListener('submit', function (e) {

        passwordError.textContent = '';
        confirmError.textContent = '';

        let valid = true;

        if (password.value.length < 8) {
            passwordError.textContent = 'Password must be at least 8 characters.';
            valid = false;
        }

        if (password.value !== confirm.value) {
            confirmError.textContent = 'Passwords do not match.';
            valid = false;
        }

        if (!valid) {
            e.preventDefault();
            return;
        }

        submitBtn.disabled = true;
        submitBtn.textContent = 'Resetting...';
    });

});
</script>

@endpush
