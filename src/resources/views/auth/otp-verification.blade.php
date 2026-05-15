@extends('layouts.auth')

@section('title', 'OTP Verification | POS System')

@section('content')

<div class="panel-form">
    <div class="form-center otp-center">

        {{-- Header --}}
        <div class="form-head">
            <h1>OTP Verification</h1>
            <p class="otp-text">
                Enter the 6-digit verification code for
                <strong>{{ session('phone') }}</strong>
            </p>
        </div>

        {{-- OTP hint for testing (remove in production) --}}
        @if(session('otp'))
            <div style="
                background: #e8f1f9;
                border: 1px solid #2E6DA4;
                border-radius: .55rem;
                padding: .75rem 1rem;
                margin-bottom: 1.2rem;
                font-size: .84rem;
                color: #2E6DA4;
                text-align: center;
            ">
                Your OTP (testing only): <strong>{{ session('otp') }}</strong>
            </div>
        @endif

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

        {{-- Form --}}
        <form method="POST" action="{{ route('auth.otp.verify') }}" id="otp-form">
            @csrf

            {{-- Hidden fields --}}
            <input type="hidden" name="phone" value="{{ session('phone') }}">
            <input type="hidden" name="otp" id="otp-value">

            {{-- OTP Inputs --}}
            <div class="otp-wrapper">
                @for ($i = 0; $i < 6; $i++)
                    <input
                        type="text"
                        maxlength="1"
                        class="otp-input"
                        inputmode="numeric"
                        pattern="[0-9]"
                        {{ $i === 0 ? 'autofocus' : '' }}
                    >
                @endfor
            </div>

            {{-- Submit --}}
            <button type="submit" class="btn-login">Verify OTP</button>

            {{-- Resend --}}
            <div class="otp-footer">
                <p>
                    Didn't receive code?
                    <a href="{{ route('auth.forgot-password') }}" class="otp-resend">Resend OTP</a>
                </p>
            </div>

        </form>

        <a href="{{ route('auth.login') }}" class="forgot" style="margin-top: 1rem; display:block;">
            ← Back to Login
        </a>

    </div>
</div>

@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', () => {
    const inputs    = document.querySelectorAll('.otp-input');
    const form      = document.getElementById('otp-form');
    const otpHidden = document.getElementById('otp-value');

    inputs.forEach((input, index) => {

        // Numbers only + auto move forward
        input.addEventListener('keyup', (e) => {
            input.value = input.value.replace(/[^0-9]/g, '');

            if (input.value.length === 1 && index < inputs.length - 1) {
                inputs[index + 1].focus();
            }

            // Auto submit when last digit entered
            if (index === inputs.length - 1 && input.value.length === 1) {
                combineAndSubmit();
            }
        });

        // Move backward on backspace
        input.addEventListener('keydown', (e) => {
            if (e.key === 'Backspace' && input.value === '' && index > 0) {
                inputs[index - 1].focus();
            }
        });

        // Handle paste e.g. 123456
        input.addEventListener('paste', (e) => {
            e.preventDefault();
            const pasted = e.clipboardData.getData('text').replace(/[^0-9]/g, '');
            if (pasted.length === 6) {
                inputs.forEach((inp, i) => inp.value = pasted[i] || '');
                inputs[5].focus();
                combineAndSubmit();
            }
        });
    });

    // Combine all 6 inputs into hidden field then submit
    function combineAndSubmit() {
        let otp = '';
        inputs.forEach(inp => otp += inp.value);

        if (otp.length === 6) {
            otpHidden.value = otp;
            form.submit();
        }
    }

    // Manual submit (clicking Verify button)
    form.addEventListener('submit', (e) => {
        let otp = '';
        inputs.forEach(inp => otp += inp.value);

        if (otp.length < 6) {
            e.preventDefault();
            // Shake animation on incomplete OTP
            inputs.forEach(inp => {
                inp.classList.add('error');
                setTimeout(() => inp.classList.remove('error'), 400);
            });
            return;
        }

        otpHidden.value = otp;
    });
});
</script>
@endpush
