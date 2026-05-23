@extends('layouts.auth')
@section('title', 'POS Login | Point of Sale')

@section('content')
  <div class="panel-form">
    <div class="form-center">
      <div class="form-head">
        <h1>Welcome Back</h1>
        <p>Sign in to POS System</p>
      </div>

      <form id="loginForm" method="POST">
        @csrf

        <!-- Email -->
        <div class="field-group">
          <label class="field-label" for="email">Email</label>
          <div class="input-icon">
            <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
              <path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07A19.5 19.5 0 0 1 4.69 12a19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 3.59 1h3a2 2 0 0 1 2 1.72c.127.96.361 1.903.7 2.81a2 2 0 0 1-.45 2.11L7.91 8.56a16 16 0 0 0 5.53 5.53l.94-.93a2 2 0 0 1 2.11-.45c.907.339 1.85.573 2.81.7A2 2 0 0 1 21 16.92z"/>
            </svg>
            <input type="email" id="email" name="email" value="{{ old('email') }}" placeholder="Enter your email" required autofocus>
          </div>
          <div id="email-error" class="message" style="color: red; font-size:10px" ></div>
        </div>

        <!-- Password -->
        <div class="field-group">
          <label class="field-label" for="password">Password</label>
          <div class="pw-wrap">
            <input type="password" id="password" name="password" placeholder="Enter password" required>
            <button type="button" class="pw-toggle" id="pw-toggle" aria-label="Toggle password visibility">
              <svg id="eye-icon" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/>
                <circle cx="12" cy="12" r="3"/>
              </svg>
            </button>
            <div id="password-error" class="message" style="color: red; font-size:10px" ></div>
          </div>
          {{-- <div id="password-error" class="message" style="color: red; font-size:10px" ></div> --}}
        </div>

        {{-- <div id="message" class="message"></div> --}}

        <button type="submit" id="loginBtn" class="btn-login">Login to POS</button>
      </form>

      <a href="{{ route('auth.forgot-password') }}" class="forgot">Forgot your password?</a>
    </div>
  </div>
@endsection
@push('scripts')
<script>
$(document).ready(function () {

    $.ajaxSetup({
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        }
    });

    // Password Visibility Toggle
    $('#pw-toggle').on('click', function () {
        const passwordField = $('#password');
        const eyeIcon = $('#eye-icon');

        if (passwordField.attr('type') === 'password') {
            passwordField.attr('type', 'text');
            eyeIcon.html(`<path d="M17.94 17.94A10.07 10.07 0 0 1 12 20c-7 0-11-8-11-8a18.45 18.45 0 0 1 5.06-5.94M9.9 4.24A9.12 9.12 0 0 1 12 4c7 0 11 8 11 8a18.5 18.5 0 0 1-2.16 3.19m-6.72-1.06a3 3 0 1 1-4.24-4.24"/><line x1="1" y1="1" x2="23" y2="23"/>`);
        } else {
            passwordField.attr('type', 'password');
            eyeIcon.html(`<path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/>`);
        }
    });

    // Login Form Submission
    $('#loginForm').on('submit', function (e) {
        e.preventDefault();

        const $btn = $('#loginBtn');

        $('#email-error').text('');
        $('#password-error').text('');

        $.ajax({
            url: "{{ route('auth.login.post') }}",
            type: "POST",
            data: {
                email: $('#email').val(),
                password: $('#password').val()
            },

            beforeSend: function () {
                $btn.prop('disabled', true).text('Logging in...');
            },

            success: function (res) {
                $btn.text('Success!');

                window.location.href = res.redirect;
            },

            error: function (xhr) {
                const errors = xhr.responseJSON?.errors;

                if (errors?.email) {
                    $('#email-error').text(errors.email[0]);
                }

                if (errors?.password) {
                    $('#password-error').text(errors.password[0]);
                }

                if (!errors && xhr.responseJSON?.message) {
                    $('#password-error').text(xhr.responseJSON.message);
                }
            },

            complete: function () {
                $btn.prop('disabled', false).text('Login to POS');
            }
        });
    });
});
</script>
@endpush
