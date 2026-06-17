@extends('layouts.app')

@push('css')
<link rel="stylesheet" href="{{ asset('assets/backend/css/auth.css') }}">
@endpush

@section('content')
<div class="auth-wrapper">
    <!-- Left Panel: Brand Immersion -->
    <div class="auth-brand-side">
        <div class="brand-content">
            <img src="{{ getSmallLogo() }}" alt="WorkPilot" style="height: 60px; margin-bottom: 40px; filter: brightness(0) invert(1);">
            <h1>Secure Your Account</h1>
            <p>Don't worry, it happens to the best of us. Let's get you back into your workspace quickly and securely.</p>
        </div>
        <div class="auth-footer-text d-none d-lg-block">
            <p>© 2026 WorkPilot. Developed By Sanket (<a href="mailto:contactsanket1@gmail.com" style="color: inherit; text-decoration: underline;">contactsanket1@gmail.com</a>) | Version: 2.5.0</p>
        </div>
    </div>

    <!-- Right Panel: Reset Request Form -->
    <div class="auth-form-side">
        <div class="auth-card">
            <div class="auth-header">
                {{-- Mobile Logo --}}
                <img src="{{ getLogo() }}" alt="WorkPilot" class="d-lg-none">
                <h2>Reset Password</h2>
                <p>Enter your email address to receive a recovery link</p>
            </div>

            @if (session('status'))
            <div class="alert alert-success bg-success-soft border-0 text-success small mb-4" role="alert">
                {{ session('status') }}
            </div>
            @endif

            <form method="POST" action="{{ route('password.email') }}" class="animation-fade-in">
                @csrf

                <div class="form-group">
                    <label class="form-label" for="email">Email Address</label>
                    <input id="email" type="email" class="form-control auth-input @error('email') is-invalid @enderror" 
                           name="email" value="{{ old('email') }}" placeholder="Enter your registered email" required autocomplete="email" autofocus>
                    
                    @error('email')
                    <span class="invalid-feedback" role="alert">
                        <strong>{{ $message }}</strong>
                    </span>
                    @enderror
                </div>

                <div class="d-grid gap-3">
                    <button type="submit" class="btn btn-primary auth-btn-primary w-100">
                        {{ __('Send Reset Link') }}
                    </button>
                    
                    <a class="btn btn-secondary auth-btn-secondary w-100" href="{{ route('login') }}">
                        Back to Login
                    </a>
                </div>
            </form>
        </div>
    </div>
</div>

<style>
    .animation-fade-in {
        animation: fadeIn 0.4s ease-out;
    }
    @keyframes fadeIn {
        from { opacity: 0; transform: translateY(10px); }
        to { opacity: 1; transform: translateY(0); }
    }
</style>
@endsection
