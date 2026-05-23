@extends('layouts.auth')

@section('title', 'Forgot Password | Point of Sale')

@section('content')
  <div class="panel-form">
    <div class="form-center">

      <div class="form-head">
        <h1>Forgot Password</h1>
        <p>Enter your phone number to receive an OTP</p>
      </div>

      {{-- General Error --}}
      <div id="general-error" style="
        display: none;
        background: #fef2f2;
        border: 1px solid #fecaca;
        border-radius: .55rem;
        padding: .75rem 1rem;
        margin-bottom: 1.2rem;
        font-size: .84rem;
        color: #dc2626;
      "></div>

      <form id="forgotPasswordForm" method="POST" novalidate>
        @csrf

        {{-- Phone --}}
        <div class="field-group">
          <label class="field-label" for="phone">Phone Number</label>
          <div class="input-icon">
            <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
              <path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07A19.5 19.5 0 0 1 4.69 12a19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 3.59 1h3a2 2 0 0 1 2 1.72c.127.96.361 1.903.7 2.81a2 2 0 0 1-.45 2.11L7.91 8.56a16 16 0 0 0 5.53 5.53l.94-.93a2 2 0 0 1 2.11-.45c.907.339 1.85.573 2.81.7A2 2 0 0 1 21 16.92z"/>
            </svg>
            <input
              type="tel"
              id="phone"
              name="phone"
              placeholder="e.g. +85512345678"
              autocomplete="tel"
              autofocus
            >
          </div>
          <div id="phone-error" class="message" style="color: red; font-size: 10px;"></div>
        </div>

        <button type="submit" id="submitBtn" class="btn-login">Continue</button>
      </form>

      <a href="{{ route('auth.login') }}" class="forgot" style="margin-top: 1rem; display: block;">
        ← Back to Login
      </a>

    </div>
  </div>
@endsection
@push('scripts')
<script>
$(document).ready(function () {

    $.ajaxSetup({
        headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') }
    });

    $('#phone').on('input', function () {
        $('#phone-error').text('');
        $('#general-error').hide().text('');
    });

    $('#forgotPasswordForm').on('submit', function (e) {
        e.preventDefault();

        const $btn = $('#submitBtn');

        $('#phone-error').text('');
        $('#general-error').hide().text('');

        $.ajax({
            url: "{{ route('auth.forgot-password.send-otp') }}",
            type: 'POST',
            data: {
                phone: $('#phone').val().trim(),
            },

            beforeSend: function () {
                $btn.prop('disabled', true).text('Sending OTP...');
            },

            success: function (res) {
                if (res.success) {
                    window.location.href = res.redirect;
                } else {
                    $('#general-error').text(res.message).show();
                    $btn.prop('disabled', false).text('Continue');
                }
            },

            error: function (xhr) {
                const json   = xhr.responseJSON;
                const errors = json?.errors;
                const status = xhr.status;

                if (errors?.phone) {
                    $('#phone-error').text(errors.phone[0]);
                }

                if (status === 429 && json?.message) {
                    $('#general-error').text(json.message).show();
                }

                if (status === 500) {
                    $('#general-error').text('Something went wrong. Please try again.').show();
                }

                $btn.prop('disabled', false).text('Continue');
            },
        });
    });
});
</script>
@endpush
