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
            <h1>Action Required</h1>
            <p>For your security, please confirm your password before continuing. This helps ensure that only you can make changes to your account settings.</p>
        </div>
        <div class="auth-footer-text d-none d-lg-block">
            <p>© 2026 SR Global. Developed By Innozia | Version: 2.5.0</p>
        </div>
    </div>

    <!-- Right Panel: Confirm Form -->
    <div class="auth-form-side">
        <div class="auth-card">
            <div class="auth-header">
                {{-- Mobile Logo --}}
                <img src="{{ getLogo() }}" alt="WorkPilot" class="d-lg-none">
                <h2>Confirm Access</h2>
                <p>Please enter your password to proceed</p>
            </div>

            <form method="POST" action="{{ route('password.confirm') }}" class="animation-fade-in">
                @csrf

                <div class="form-group">
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <label class="form-label mb-0" for="password">Password</label>
                        @if (Route::has('password.request'))
                        <a class="forgot-link" href="{{ route('password.request') }}">Forgot?</a>
                        @endif
                    </div>
                    <div class="pass-group">
                        <input id="password" type="password" class="form-control auth-input pass-input @error('password') is-invalid @enderror" 
                               name="password" placeholder="••••••••" required autocomplete="current-password">
                        <span class="fas fa-eye toggle-password"></span>
                        
                        @error('password')
                        <span class="invalid-feedback" role="alert">
                            <strong>{{ $message }}</strong>
                        </span>
                        @enderror
                    </div>
                </div>

                <div class="d-grid mt-4">
                    <button type="submit" class="btn btn-primary auth-btn-primary w-100">
                        {{ __('Confirm Password') }}
                    </button>
                </div>

                <div class="text-center mt-4">
                    <a class="forgot-link" href="{{ url()->previous() }}">Go Back</a>
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
