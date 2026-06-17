@extends('onboarding::portal.layout')

@section('title', 'Candidate Login')

@section('styles')
<style>
    /* --- Personio Style Overrides for Login --- */
    :root {
        --color-bg-cream: #FAF9F6;
        --color-text-main: #050505;
        --radius-pill: 50px;
        --radius-card: 40px;
        --radius-input: 12px;
    }

    /* Full Page Gradient Background */
    body {
        background: radial-gradient(circle at center top, rgba(255, 192, 98, 0.15) 0%, #FAF9F6 60%);
        min-height: 100vh;
        display: flex;
        flex-direction: column;
    }

    /* Hide standard header/footer if needed, or blend them. 
       For now, we assume layout header is fine but we emphasize the card. */
    
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
            <h2 class="login-title">Welcome Aboard!</h2>
            <p class="text-muted" style="font-size: 1.1rem;">Please log in to start your onboarding journey.</p>
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

        <form action="{{ route('portal.authenticate') }}" method="POST">
            @csrf
            <div class="mb-4">
                <label class="form-label">Email Address</label>
                <input type="email" name="email" class="form-control-personio w-100" placeholder="name@company.com" required autofocus>
            </div>
            
            <div class="mb-5">
                <label class="form-label">Temporary Password</label>
                <div class="position-relative">
                    <input type="password" name="password" id="password_input" class="form-control-personio w-100" placeholder="••••••••" required>
                    <button type="button" class="btn btn-link position-absolute end-0 top-50 translate-middle-y text-muted text-decoration-none pe-3" id="toggle_password">
                        <i class="far fa-eye" id="eye_icon"></i>
                    </button>
                </div>
                <div class="text-end mt-2">
                    <a href="{{ route('portal.password.request') }}" class="small text-muted text-decoration-none">Forgot Password?</a>
                </div>
            </div>

            <button type="submit" class="btn btn-personio-black">
                Sign In to Portal
            </button>
        </form>
        
        <div class="mt-5 pt-4 border-top text-center">
             <p class="small text-muted mb-0">Need help? Contact <a href="mailto:hr@mom-digital.com" class="text-dark font-weight-bold text-decoration-none">HR Support</a></p>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
    $(document).ready(function() {
        $('#toggle_password').on('click', function() {
            const passwordInput = $('#password_input');
            const eyeIcon = $('#eye_icon');
            
            if (passwordInput.attr('type') === 'password') {
                passwordInput.attr('type', 'text');
                eyeIcon.removeClass('fa-eye').addClass('fa-eye-slash');
            } else {
                passwordInput.attr('type', 'password');
                eyeIcon.removeClass('fa-eye-slash').addClass('fa-eye');
            }
        });
    });
</script>
@endsection
