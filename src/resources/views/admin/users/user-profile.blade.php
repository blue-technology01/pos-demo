@extends('layouts.app')
@push('styles')
    <link rel="stylesheet" href="{{ asset('assets/css/dashboard/user/user-profile.css') }}">
@endpush
@section('title', 'User profile')
@php
    $user = auth()->user();
    $avatarUrl = $user?->avatar
        ? asset('storage/' . $user->avatar)
        : 'https://ui-avatars.com/api/?name=' . urlencode($user?->name ?? 'Guest') . '&background=2563a8&color=fff&size=80&bold=true';
@endphp
@section('content')

<div class="profile-wrapper">
    <div class="profile-card">
        <div class="profile-header"></div>
        <div class="profile-body">
            <!-- USE THE $avatarUrl VARIABLE -->
            <img src="{{ $avatarUrl }}" class="profile-avatar" alt="{{ $user->name }}">

            <div class="profile-name">{{ $user->name }}</div>
            <div class="profile-role">{{ $user->role }}</div>

            <div class="profile-info">
                <div class="info-box">
                    <div class="info-label">Email</div>
                    <div class="info-value">{{ $user->email }}</div>
                </div>

                <div class="info-box">
                    <div class="info-label">Phone</div>
                    <div class="info-value">{{ $user->phone }}</div>
                </div>

                <div class="info-box">
                    <div class="info-label">Member Since</div>
                    <div class="info-value">{{ $user->created_at->format('M d, Y') }}</div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
    {{-- script --}}
@endpush
