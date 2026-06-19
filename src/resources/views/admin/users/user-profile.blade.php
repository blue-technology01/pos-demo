@extends('layouts.app')

@push('styles')
    <link rel="stylesheet" href="{{ asset('assets/css/dashboard/user/user-profile.css') }}">
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined" />
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600&display=swap" rel="stylesheet" />
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@tabler/icons-webfont@latest/dist/tabler-icons.min.css" />
@endpush

@section('title', 'My Profile')

@php
    $user = auth()->user();

    $avatarUrl = $user?->avatar
        ? asset('storage/' . $user->avatar)
        : 'https://ui-avatars.com/api/?name=' . urlencode($user?->name ?? 'Guest')
          . '&background=2563a8&color=fff&size=80&bold=true';

    $role = $user?->roles->first()?->name ?? 'none';
@endphp

@section('content')
<div class="profile-wrapper">
    <div class="profile-card">

        {{-- ── Banner ── --}}
        <div class="profile-header"></div>

        {{-- ── Body ── --}}
        <div class="profile-body">

            {{-- Avatar + role badge --}}
            <div class="profile-avatar-row">
                <img src="{{ $avatarUrl }}"
                     class="profile-avatar"
                     alt="{{ $user->name }}">

                <span class="profile-role-badge profile-role-badge--{{ $role }}">
                    {{ ucfirst($role) }}
                </span>
            </div>

            {{-- Name + email hint --}}
            <p class="profile-name">{{ $user->name }}</p>
            <p class="profile-email-hint">{{ $user->email }}</p>

            <hr class="profile-divider">

            {{-- Info rows --}}
            <div class="profile-info">

                <div class="info-row">
                    <div class="info-icon">
                        <span class="material-symbols-outlined" style="font-size:18px">mail</span>
                    </div>
                    <div class="info-content">
                        <div class="info-label">Email</div>
                        <div class="info-value">{{ $user->email }}</div>
                    </div>
                </div>

                <div class="info-row">
                    <div class="info-icon">
                        <span class="material-symbols-outlined" style="font-size:18px">phone</span>
                    </div>
                    <div class="info-content">
                        <div class="info-label">Phone</div>
                        <div class="info-value">{{ $user->phone ?? '—' }}</div>
                    </div>
                </div>

                <div class="info-row">
                    <div class="info-icon">
                        <span class="material-symbols-outlined" style="font-size:18px">calendar_today</span>
                    </div>
                    <div class="info-content">
                        <div class="info-label">Member since</div>
                        <div class="info-value">{{ $user->created_at->format('M d, Y') }}</div>
                    </div>
                </div>

            </div>
        </div>
    </div>
</div>
@endsection
