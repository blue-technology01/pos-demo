@extends('layouts.auth')

@section('title', 'OTP Verification | Point of Sale')

@section('content')
  <div class="panel-form">
    <div class="form-center">

      <div class="form-head">
        <h1>OTP Verification</h1>
        <p>Enter the 6-digit code sent to your phone</p>
      </div>

      {{-- Session Error --}}
      @if (session('error'))
        <div style="
            background: #fef2f2;
            border: 1px solid #fecaca;
            border-radius: .55rem;
            padding: .75rem 1rem;
            margin-bottom: 1.2rem;
            font-size: .84rem;
            color: #dc2626;
        ">{{ session('error') }}</div>
      @endif

      {{-- ✅ FIXED: uncommented so JS can use it --}}
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

      <form id="otpForm" method="POST" novalidate>
        @csrf

        <input type="hidden" id="phone" name="phone" value="{{ session('otp_phone') }}">

        <div class="field-group">
          <label class="field-label">Enter OTP Code</label>
          <div id="otp-inputs" style="display: flex; gap: .6rem; justify-content: center; margin: 1rem 0;">
            {{-- ✅ FIXED: removed stray "// loop 6 input" text --}}
            @for ($i = 0; $i < 6; $i++)
              <input
                type="text"
                class="otp-box"
                maxlength="1"
                inputmode="numeric"
                pattern="[0-9]"
                style="
                    width: 46px;
                    height: 52px;
                    text-align: center;
                    font-size: 1.4rem;
                    font-weight: 600;
                    border: 1.5px solid #d1d5db;
                    border-radius: .5rem;
                    outline: none;
                    transition: border-color .2s;
                "
              >
            @endfor
          </div>
          <div id="otp-error" class="message" style="color: red; font-size: 10px; text-align: center;"></div>
        </div>

        <div style="text-align: center; margin-bottom: 1rem; font-size: .84rem; color: #6b7280;">
          Didn't receive the code?
          <a href="#" id="resendOtp" style="color: #4f46e5; font-weight: 500;">Resend OTP</a>
          <span id="resendTimer" style="color: #9ca3af;"></span>
        </div>

        <button type="submit" id="submitBtn" class="btn-login">Verify OTP</button>
      </form>

      <a href="{{ route('auth.forgot-password') }}" class="forgot" style="margin-top: 1rem; display: block;">
        ← Back
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

    // OTP box navigation
    $(document).on('input', '.otp-box', function () {
        const val = $(this).val().replace(/\D/g, '');
        $(this).val(val);
        if (val && $(this).next('.otp-box').length) {
            $(this).next('.otp-box').focus();
        }
    });

    $(document).on('keydown', '.otp-box', function (e) {
        if (e.key === 'Backspace' && !$(this).val()) {
            $(this).prev('.otp-box').focus();
        }
    });

    $('.otp-box').first().focus();

    // Resend timer
    let countdown = 60;

    function startResendTimer() {
        $('#resendOtp').hide();
        const interval = setInterval(function () {
            $('#resendTimer').text(`(${countdown}s)`);
            countdown--;
            if (countdown < 0) {
                clearInterval(interval);
                $('#resendTimer').text('');
                $('#resendOtp').show();
                countdown = 60;
            }
        }, 1000);
    }

    startResendTimer();

    $('#resendOtp').on('click', function (e) {
        e.preventDefault();
        $.ajax({
            url: "{{ route('auth.otp.resend') }}",
            type: 'POST',
            data: { phone: $('#phone').val() },
            success: function (res) {
                if (res.success) {
                    $('#general-error').hide().text('');
                    startResendTimer();
                }
            },
            error: function (xhr) {
                const json = xhr.responseJSON;
                $('#general-error').text(json?.message || 'Failed to resend OTP.').show();
            }
        });
    });

    // OTP submit
    $('#otpForm').on('submit', function (e) {
        e.preventDefault();

        const $btn = $('#submitBtn');
        const otp  = $('.otp-box').map(function () {
            return $(this).val();
        }).get().join('');

        $('#otp-error').text('');
        $('#general-error').hide().text('');

        if (otp.length < 6) {
            $('#otp-error').text('Please enter all 6 digits.');
            return;
        }

        $.ajax({
            url: "{{ route('auth.otp.verify') }}",
            type: 'POST',
            data: {
                otp: otp,
                //controller reads it from session
            },
            beforeSend: function () {
                $btn.prop('disabled', true).text('Verifying...');
            },
            success: function (res) {
                if (res.success) {
                    $btn.text('Verified! Redirecting...');
                    window.location.href = res.redirect;
                }
            },
            error: function (xhr) {
                const json   = xhr.responseJSON;
                const errors = json?.errors;
                const status = xhr.status;

                if (errors?.otp) {
                    $('#otp-error').text(errors.otp[0]);
                } else if (status === 429 && json?.message) {
                    $('#general-error').text(json.message).show();
                } else {
                    $('#general-error').text(json?.message || 'Something went wrong.').show();
                }

                $('.otp-box').val('');
                $('.otp-box').first().focus();
                $btn.prop('disabled', false).text('Verify OTP');
            },
        });
    });
});
</script>
@endpush
