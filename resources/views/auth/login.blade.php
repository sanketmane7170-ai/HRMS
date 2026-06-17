@extends('layouts.app')

@push('css')
<link rel="stylesheet" href="{{ asset('assets/backend/css/auth.css') }}">
@endpush

@section('content')
<div class="auth-wrapper">

    {{-- ============================================================
         LEFT PANEL: Brand Immersion
    ============================================================ --}}
    <div class="auth-brand-side">
        <div class="brand-ring-inner"></div>

        <div class="brand-content">
            {{-- Logo --}}
            <div class="brand-logo-area">
                <img src="{{ getSmallLogo() }}" alt="WorkPilot">
            </div>

            <h1>Smart HR<br>for modern teams</h1>
            <p>Manage your entire workforce — payroll, attendance, leaves, and performance — all in one intelligent platform.</p>

            <div class="brand-features">
                <div class="brand-feature-item">
                    <div class="brand-feature-dot">
                        <i class="fas fa-users-cog"></i>
                    </div>
                    <span>Complete Employee Lifecycle Management</span>
                </div>
                <div class="brand-feature-item">
                    <div class="brand-feature-dot">
                        <i class="fas fa-file-invoice-dollar"></i>
                    </div>
                    <span>Automated Payroll & Compliance</span>
                </div>
                <div class="brand-feature-item">
                    <div class="brand-feature-dot">
                        <i class="fas fa-chart-bar"></i>
                    </div>
                    <span>Real-time HR Analytics & Insights</span>
                </div>
            </div>
        </div>

        <div class="auth-footer-text d-none d-lg-block">
            © 2026 WorkPilot. Developed by Sanket
            (<a href="mailto:contactsanket1@gmail.com">contactsanket1@gmail.com</a>) | Version 2.5.0
        </div>
    </div>

    {{-- ============================================================
         RIGHT PANEL: Login Form
    ============================================================ --}}
    <div class="auth-form-side">
        <div class="auth-card">

            {{-- Mobile logo (only shown when left panel is hidden) --}}
            <div class="auth-mobile-logo">
                <img src="{{ getSmallLogo() }}" alt="WorkPilot">
            </div>

            <div class="auth-header">
                <h2>Welcome back</h2>
                <p>Enter your credentials to access your workspace</p>
            </div>

            {{-- ── Error alert ── --}}
            @if ($errors->any() && !$errors->has('email') && !$errors->has('username') && !$errors->has('password'))
            <div class="alert alert-danger mb-4" role="alert">
                {{ $errors->first() }}
            </div>
            @endif

            {{-- ── Credentials Form ── --}}
            <form action="{{ route('login.perform') }}" method="POST" id="credentials-form"
                  @if(session('otp_sent')) style="display:none;" @endif>
                @csrf

                <div class="form-group">
                    <label class="form-label" for="email">Email or Employee ID</label>
                    <input type="text"
                           id="email"
                           name="email"
                           value="{{ old('email') }}"
                           placeholder="you@company.com"
                           required
                           autocomplete="email"
                           autofocus
                           class="form-control auth-input @error('email') is-invalid @enderror @error('username') is-invalid @enderror">
                    @error('email')
                        <span class="invalid-feedback" role="alert"><strong>{{ $message }}</strong></span>
                    @enderror
                    @error('username')
                        <span class="invalid-feedback" role="alert"><strong>{{ $message }}</strong></span>
                    @enderror
                </div>

                <div class="form-group">
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <label class="form-label mb-0" for="password">Password</label>
                        @if (Route::has('password.request'))
                            <a class="forgot-link" href="{{ route('password.request') }}">Forgot password?</a>
                        @endif
                    </div>
                    <div class="pass-group">
                        <input id="password"
                               type="password"
                               name="password"
                               placeholder="••••••••"
                               required
                               autocomplete="current-password"
                               class="form-control auth-input pass-input @error('password') is-invalid @enderror">
                        <span class="fas fa-eye toggle-password" role="button" aria-label="Toggle password visibility"></span>
                        @error('password')
                            <span class="invalid-feedback" role="alert"><strong>{{ $message }}</strong></span>
                        @enderror
                    </div>
                </div>

                <div class="form-group mb-4">
                    <div class="d-flex align-items-center gap-2">
                        <input class="custom-control-input"
                               id="cb1"
                               type="checkbox"
                               name="remember"
                               {{ old('remember') ? 'checked' : '' }}
                               style="width: 16px; height: 16px; cursor: pointer; accent-color: #4F46E5; flex-shrink: 0;">
                        <label class="custom-control-label" for="cb1" style="margin: 0;">Keep me signed in</label>
                    </div>
                </div>

                <button class="btn auth-btn-primary w-100" type="submit">
                    Sign In
                </button>
            </form>

            {{-- ── OTP Section ── --}}
            @if(session('otp_sent'))
            <div id="otp-section" class="animation-fade-in">
                <div class="text-center mb-4">
                    <div class="otp-icon-wrap">
                        <i class="fas fa-shield-alt"></i>
                    </div>
                    <p class="otp-verify-title">Verify your identity</p>
                    <p class="otp-verify-subtitle">We've sent a 6-digit code to your registered contact.</p>
                </div>

                @if(session('message'))
                <div class="alert alert-success mb-4">{{ session('message') }}</div>
                @endif

                <form action="{{ route('login.verifyOtp') }}" method="POST" id="otp-form" class="mb-4">
                    @csrf
                    <div class="form-group">
                        <label class="form-label text-center d-block">Enter 6-digit code</label>
                        <input name="otp"
                               id="otp"
                               type="text"
                               inputmode="numeric"
                               pattern="\d{6}"
                               maxlength="6"
                               class="form-control auth-input otp-input-single"
                               placeholder="000000"
                               required
                               autofocus>
                        @error('otp')
                        <span class="invalid-feedback d-block text-center">{{ $message }}</span>
                        @enderror
                    </div>
                </form>

                <form id="resend-form" method="POST" action="{{ route('login.resendOtp') }}" style="display:none;">
                    @csrf
                </form>

                <div class="d-grid gap-3">
                    <button type="submit" form="otp-form" class="btn auth-btn-primary">
                        Verify Code
                    </button>
                    <div class="d-flex gap-2">
                        <button type="button" class="btn auth-btn-secondary flex-grow-1" id="resend-btn" data-cooldown="30">
                            Resend Code
                        </button>
                        <button type="button" class="btn auth-btn-secondary flex-grow-1" id="use-different-btn">
                            Switch Account
                        </button>
                    </div>
                </div>
            </div>
            @endif

        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {

    // ── Password toggle ──
    const passToggle = document.querySelector('.toggle-password');
    const passInput  = document.querySelector('.pass-input');
    if (passToggle && passInput) {
        passToggle.addEventListener('click', function () {
            const isPassword = passInput.getAttribute('type') === 'password';
            passInput.setAttribute('type', isPassword ? 'text' : 'password');
            this.classList.toggle('fa-eye',       !isPassword);
            this.classList.toggle('fa-eye-slash',  isPassword);
        });
    }

    // ── Switch account ──
    const useDifferentBtn = document.getElementById('use-different-btn');
    const otpSection      = document.getElementById('otp-section');
    const credForm        = document.getElementById('credentials-form');
    if (useDifferentBtn) {
        useDifferentBtn.addEventListener('click', function () {
            if (otpSection) otpSection.style.display = 'none';
            if (credForm)   { credForm.style.display = 'block'; credForm.classList.add('animation-fade-in'); }
            const otpInput = document.getElementById('otp');
            if (otpInput)   otpInput.value = '';
        });
    }

    // ── Resend cooldown ──
    const resendBtn  = document.getElementById('resend-btn');
    const resendForm = document.getElementById('resend-form');
    if (resendBtn && resendForm) {
        const cooldownDefault = parseInt(resendBtn.getAttribute('data-cooldown')) || 30;
        resendBtn.addEventListener('click', function (e) {
            e.preventDefault();
            startCooldown(resendBtn, cooldownDefault);
            resendForm.submit();
        });
    }

    function startCooldown(button, seconds) {
        button.disabled = true;
        let remaining = seconds;
        button.textContent = `Wait ${remaining}s`;
        const interval = setInterval(function () {
            remaining--;
            if (remaining <= 0) {
                clearInterval(interval);
                button.disabled = false;
                button.textContent = 'Resend Code';
            } else {
                button.textContent = `Wait ${remaining}s`;
            }
        }, 1000);
    }

    // ── OTP autofocus ──
    const otpInput = document.getElementById('otp');
    if (otpInput) otpInput.focus();
});
</script>
@endsection
