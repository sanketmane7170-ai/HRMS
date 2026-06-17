@extends('onboarding::portal.layout')

@section('title', 'Verify OTP')

@section('styles')
<style>
    :root {
        --color-bg-cream: #FAF9F6;
        --color-text-main: #050505;
        --radius-pill: 50px;
        --radius-card: 40px;
        --radius-input: 12px;
    }

    body {
        background: radial-gradient(circle at center top, rgba(255, 192, 98, 0.15) 0%, #FAF9F6 60%);
        min-height: 100vh;
        display: flex;
        flex-direction: column;
    }
    
    .login-container {
        flex: 1;
        display: flex;
        align-items: center;
        justify-content: center;
        padding: 4rem 1rem;
    }

    .login-card {
        background: white;
        border-radius: var(--radius-card);
        box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.1);
        border: 1px solid #E5E5E5;
        padding: 4rem;
        width: 100%;
        max-width: 550px;
    }

    .login-title {
        font-weight: 800;
        font-size: 2.5rem;
        color: var(--color-text-main);
        letter-spacing: -0.02em;
        margin-bottom: 0.5rem;
    }

    .form-control-personio {
        background-color: #F9FAFB;
        border: 1px solid #E5E5E5;
        border-radius: var(--radius-input);
        padding: 1rem 1.25rem;
        font-size: 1.5rem;
        letter-spacing: 0.5em;
        text-align: center;
        transition: all 0.2s;
    }

    .form-control-personio:focus {
        background-color: white;
        border-color: var(--color-text-main);
        box-shadow: 0 0 0 4px rgba(0,0,0,0.05);
        outline: none;
    }

    .btn-personio-black {
        background-color: var(--color-text-main);
        color: white;
        border: none;
        padding: 1rem 2rem;
        border-radius: var(--radius-pill);
        font-weight: 700;
        width: 100%;
        transition: transform 0.2s;
    }

    .btn-personio-black:hover {
        background-color: #333;
        transform: translateY(-2px);
        color: white;
    }

    .form-label {
        font-weight: 600;
        font-size: 0.85rem;
        text-transform: uppercase;
        letter-spacing: 0.05em;
        color: #6B7280;
        margin-bottom: 0.5rem;
        display: block;
    }
</style>
@endsection

@section('content')
<div class="login-container">
    <div class="login-card">
        <div class="text-center mb-5">
            <h2 class="login-title">Verify OTP</h2>
            <p class="text-muted" style="font-size: 1.1rem;">Enter the 6-digit code sent to your email.</p>
            <p class="small text-primary font-weight-bold">{{ session('reset_email') }}</p>
        </div>

        @if($errors->any())
            <div class="alert alert-danger mb-4 rounded-3">
                <ul class="mb-0 small list-unstyled">
                    @foreach($errors->all() as $error)
                        <li><i class="fas fa-exclamation-circle mr-2"></i> {{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        @if(session('success'))
            <div class="alert alert-success mb-4 rounded-3">
                <p class="mb-0 small"><i class="fas fa-check-circle mr-2"></i> {{ session('success') }}</p>
            </div>
        @endif

        <form action="{{ route('portal.password.verify-otp') }}" method="POST">
            @csrf
            <div class="mb-5">
                <label class="form-label">OTP CODE</label>
                <input type="text" name="otp" class="form-control-personio w-100" placeholder="000000" maxlength="6" required autofocus autocomplete="off">
            </div>
            
            <button type="submit" class="btn btn-personio-black">
                Verify OTP
            </button>
            <div class="text-center mt-3">
                <a href="{{ route('portal.password.request') }}" class="small text-muted text-decoration-none">Resend OTP</a>
            </div>
        </form>
    </div>
</div>
@endsection
