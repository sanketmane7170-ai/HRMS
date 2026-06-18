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
            <h1>Create New Password</h1>
            <p>Almost there! Choose a strong password to ensure your account remains secure and your data protected.</p>
        </div>
        <div class="auth-footer-text d-none d-lg-block">
            <p>© 2026 SR Global. Developed By Innozia | Version: 2.5.0</p>
        </div>
    </div>

    <!-- Right Panel: Reset Form -->
    <div class="auth-form-side">
        <div class="auth-card">
            <div class="auth-header">
                {{-- Mobile Logo --}}
                <img src="{{ getLogo() }}" alt="WorkPilot" class="d-lg-none">
                <h2>Set New Password</h2>
                <p>Please enter your new security credentials</p>
            </div>

            <form method="POST" action="{{ route('password.update') }}" class="animation-fade-in">
                @csrf

                <input type="hidden" name="token" value="{{ $token }}">

                <div class="form-group">
                    <label class="form-label" for="email">Email Address</label>
                    <input id="email" type="email" class="form-control auth-input @error('email') is-invalid @enderror" 
                           name="email" value="{{ $email ?? old('email') }}" placeholder="Enter Email Address" required autocomplete="email" autofocus>
                    
                    @error('email')
                    <span class="invalid-feedback" role="alert">
                        <strong>{{ $message }}</strong>
                    </span>
                    @enderror
                </div>

                <div class="form-group">
                    <label class="form-label" for="password">New Password</label>
                    <div class="pass-group">
                        <input id="password" type="password" class="form-control auth-input pass-input @error('password') is-invalid @enderror" 
                               name="password" placeholder="••••••••" required autocomplete="new-password">
                        <span class="fas fa-eye toggle-password"></span>
                        
                        @error('password')
                        <span class="invalid-feedback" role="alert">
                            <strong>{{ $message }}</strong>
                        </span>
                        @enderror
                    </div>
                </div>

                <div class="form-group">
                    <label class="form-label" for="password-confirm">Confirm New Password</label>
                    <input id="password-confirm" type="password" class="form-control auth-input" 
                           name="password_confirmation" placeholder="••••••••" required autocomplete="new-password">
                </div>

                <div class="d-grid mt-4">
                    <button type="submit" class="btn btn-primary auth-btn-primary w-100">
                        {{ __('Update & Secure') }}
                    </button>
                </div>

                <div class="text-center mt-4">
                    <a class="forgot-link" href="{{ route('login') }}">Back to Login</a>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const passToggle = document.querySelector('.toggle-password');
        const passInput = document.getElementById('password');

        if (passToggle && passInput) {
            passToggle.addEventListener('click', function() {
                const type = passInput.getAttribute('type') === 'password' ? 'text' : 'password';
                passInput.setAttribute('type', type);
                this.classList.toggle('fa-eye');
                this.classList.toggle('fa-eye-slash');
            });
        }
    });
</script>

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
