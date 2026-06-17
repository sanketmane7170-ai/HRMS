@extends('onboarding::portal.layout')

@section('title', 'Forgot Password')

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
        font-size: 1rem;
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
            <h2 class="login-title">Reset Password</h2>
            <p class="text-muted" style="font-size: 1.1rem;">Enter your email to receive an OTP.</p>
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

        <form action="{{ route('portal.password.email') }}" method="POST">
            @csrf
            <div class="mb-5">
                <label class="form-label">Email Address</label>
                <input type="email" name="email" class="form-control-personio w-100" placeholder="name@company.com" required autofocus>
            </div>
            
            <button type="submit" class="btn btn-personio-black">
                Send Reset OTP
            </button>
            <div class="text-center mt-3">
                <a href="{{ route('portal.login') }}" class="small text-muted text-decoration-none"><i class="fas fa-arrow-left mr-1"></i> Back to Login</a>
            </div>
        </form>
    </div>
</div>
@endsection
