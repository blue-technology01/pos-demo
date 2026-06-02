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

      <form id="otpForm" method="POST" action="{{route('auth.otp.verify') }}" novalidate>
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
          <a href="{{ route('auth.otp.resend') }}" id="resendOtp" style="color: #4f46e5; font-weight: 500;">Resend OTP</a>
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
document.addEventListener('DOMContentLoaded', function () {

    const boxes = document.querySelectorAll('.otp-box');
    const form = document.getElementById('otpForm');

    // Create hidden OTP input
    const hiddenOtp = document.createElement('input');
    hiddenOtp.type = 'hidden';
    hiddenOtp.name = 'otp';
    hiddenOtp.id = 'otp';
    form.appendChild(hiddenOtp);

    // Auto focus first box
    boxes[0].focus();

    // Input handling
    boxes.forEach((box, index) => {

        box.addEventListener('input', function () {

            // Allow numbers only
            this.value = this.value.replace(/\D/g, '');

            // Move next
            if (this.value && boxes[index + 1]) {
                boxes[index + 1].focus();
            }

        });

        // Backspace previous
        box.addEventListener('keydown', function (e) {

            if (e.key === 'Backspace' && !this.value && boxes[index - 1]) {
                boxes[index - 1].focus();
            }

        });

        // Paste full OTP
        box.addEventListener('paste', function (e) {

            e.preventDefault();

            const pasted = (e.clipboardData || window.clipboardData)
                .getData('text')
                .replace(/\D/g, '')
                .slice(0, 6);

            pasted.split('').forEach((char, i) => {
                if (boxes[i]) {
                    boxes[i].value = char;
                }
            });

        });

    });

    // Before submit combine OTP
    form.addEventListener('submit', function (e) {

        let otp = '';

        boxes.forEach(box => {
            otp += box.value;
        });

        // Validate
        if (otp.length !== 6) {

            e.preventDefault();

            document.getElementById('otp-error').textContent =
                'Please enter complete OTP code.';

            return;
        }

        hiddenOtp.value = otp;

        // Disable button
        const btn = document.getElementById('submitBtn');

        btn.disabled = true;
        btn.textContent = 'Verifying...';

    });

});
</script>
@endpush
