@extends('layouts.auth')

@section('title', 'Forgot Password | Point of Sale')

@section('content')
<div class="panel-form">
    <div class="form-center">

        <div class="form-head">
            <h1>Forgot Password</h1>
            <p>Enter your phone number to receive an OTP</p>
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
            ">
                {{ session('error') }}
            </div>
        @endif

        {{-- Validation Errors --}}
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

        <form method="POST" action="{{ route('auth.forgot-password.send-otp') }}">
            @csrf

            {{-- Phone --}}
            <div class="field-group">
                <label class="field-label" for="phone">Phone Number</label>

                <div class="input-icon">
                    <svg width="15" height="15" viewBox="0 0 24 24" fill="none"
                         stroke="currentColor" stroke-width="2"
                         stroke-linecap="round" stroke-linejoin="round">

                        <path d="M22 16.92v3a2 2 0 0 1-2.18 2
                        19.79 19.79 0 0 1-8.63-3.07A19.5 19.5 0 0 1 4.69 12
                        19.79 19.79 0 0 1 1.62 3.33 2 2 0 0 1 3.59 1h3
                        a2 2 0 0 1 2 1.72c.127.96.361 1.903.7 2.81
                        a2 2 0 0 1-.45 2.11L7.91 8.56
                        a16 16 0 0 0 5.53 5.53l.94-.93
                        a2 2 0 0 1 2.11-.45c.907.339 1.85.573 2.81.7
                        A2 2 0 0 1 21 16.92z"/>
                    </svg>

                    <input
                        type="tel"
                        id="phone"
                        name="phone"
                        placeholder="e.g. +85512345678"
                        autocomplete="tel"
                        value="{{ old('phone') }}"
                        required
                        autofocus
                    >
                </div>

                @error('phone')
                    <div style="color:red;font-size:10px;">
                        {{ $message }}
                    </div>
                @enderror
            </div>

            <button type="submit" class="btn-login">
                Continue
            </button>

        </form>

        <a href="{{ route('auth.login') }}"
           class="forgot"
           style="margin-top: 1rem; display: block;">

            ← Back to Login

        </a>

    </div>
</div>
@endsection
