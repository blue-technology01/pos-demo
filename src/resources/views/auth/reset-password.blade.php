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
                <div id="password-error" style="color: #dc2626; font-size: 10px; margin-top: 4px;"></div>
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
                <div id="confirm-error" style="color: #dc2626; font-size: 10px; margin-top: 4px;"></div>
            </div>
            {{-- Submit --}}
            <button type="submit" id="submitBtn" class="btn-login">Reset Password</button>

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
$(document).ready(function () {

    $.ajaxSetup({
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content'),
            'Accept': 'application/json',
        }
    });

    const eyeOpen = `
        <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/>
        <circle cx="12" cy="12" r="3"/>
    `;
    const eyeClosed = `
        <path d="M17.94 17.94A10.07 10.07 0 0 1 12 20c-7 0-11-8-11-8a18.45 18.45 0 0 1 5.06-5.94"/>
        <path d="M9.9 4.24A9.12 9.12 0 0 1 12 4c7 0 11 8 11 8a18.5 18.5 0 0 1-2.16 3.19"/>
        <line x1="1" y1="1" x2="23" y2="23"/>
    `;

    $('#toggle-password').on('click', function () {
        const input = $('#password');
        const isPassword = input.attr('type') === 'password';
        input.attr('type', isPassword ? 'text' : 'password');
        $('#eye-1').html(isPassword ? eyeClosed : eyeOpen);
    });

    $('#toggle-confirm').on('click', function () {
        const input = $('#password_confirmation');
        const isPassword = input.attr('type') === 'password';
        input.attr('type', isPassword ? 'text' : 'password');
        $('#eye-2').html(isPassword ? eyeClosed : eyeOpen);
    });

    $('#password').on('input', function () {
        $('#password-error').text('');
        $('#general-error').hide().text('');
    });

    $('#password_confirmation').on('input', function () {
        $('#confirm-error').text('');
    });

    $('#reset-form').on('submit', function (e) {
        e.preventDefault();

        const $btn   = $('#submitBtn');
        const pw     = $('#password').val();
        const pwConf = $('#password_confirmation').val();

        // Clear all errors
        $('#password-error').text('');
        $('#confirm-error').text('');
        $('#general-error').hide().text('');
        $('#general-success').hide().text('');

        // Client-side checks
        if (pw.length < 8) {
            $('#password-error').text('Password must be at least 8 characters.');
            return;
        }

        if (pw !== pwConf) {
            $('#confirm-error').text('Passwords do not match.');
            return;
        }

        $.ajax({
            url: "{{ route('auth.reset-password.post') }}",
            type: 'POST',
            contentType: 'application/json',
            data: JSON.stringify({
                password: pw,
                password_confirmation: pwConf,
            }),

            beforeSend: function () {
                $btn.prop('disabled', true).text('Resetting...');
            },

            success: function (res) {
                if (res.success) {
                    $('#general-success').text(res.message).show();
                    $btn.text('Redirecting...');
                    setTimeout(function () {
                        window.location.href = "{{ route('auth.login') }}";
                    }, 1500);
                }
            },

            error: function (xhr) {
                const json   = xhr.responseJSON;
                const errors = json?.errors;
                const status = xhr.status;

                if (errors?.password) {
                    $('#password-error').text(errors.password[0]);
                } else if (status === 422 && json?.message) {
                    $('#general-error').text(json.message).show();
                } else {
                    $('#general-error').text('Something went wrong. Please try again.').show();
                }

                $btn.prop('disabled', false).text('Reset Password');
            },
        });
    });

});
</script>
@endpush
