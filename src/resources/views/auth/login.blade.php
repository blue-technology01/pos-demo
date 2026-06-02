@extends('layouts.auth')
@section('title', 'POS Login | Point of Sale')
@push('styles')
    <style>
        .error {
            margin-top: 6px;
            font-size: 12px;
            color: #dc2626;
            background: #fef2f2;
            border: 1px solid #fecaca;
            padding: 6px 10px;
            border-radius: 6px;
            display: inline-block;
            width: 100%;
        }
    </style>
@section('content')
  <div class="panel-form">
    <div class="form-center">
      <div class="form-head">
        <h1>Welcome Back</h1>
        <p>Sign in to POS System</p>
      </div>

      <form method="POST" action="{{ route('auth.login.post') }}">
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
           @error('email')
                <div class="error">{{ $message }}</div>
            @enderror
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
            @error('password')
                <div class="error">{{ $message }}</div>
            @enderror
          </div>
        </div>
        <button type="submit" id="loginBtn" class="btn-login">Login to POS</button>
      </form>
      <a href="{{ route('auth.forgot-password') }}" class="forgot">Forgot your password?</a>
    </div>
  </div>
@endsection
