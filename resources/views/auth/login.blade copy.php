@extends('layouts.app')
@section('content')

<div class="auth-wrapper auth-v3">
    <div class="bg-auth-side"></div>
    <div class="auth-content">
        <div class="login-header" style="height: 60px;">
            <a href="{{url('/')}}" class="d-lg-block d-none"><img class="img-fluid logo logo-lg" src="{{getLogo()}}" alt="Logo" style="height: 52px;"></a>
            <a href="{{route('backend.dashboard')}}" class="logo logo-small d-lg-none d-block"><img src="{{getSmallLogo()}}" alt="Logo" alt="Logo" style="height: 52px;"></a>
        </div>
        <div class="card light">
            <div class="row align-items-center text-start">
                <div class="card light">
                    <div class="row align-items-center text-start">
                        <div class="col-lg-6">
                            <div class="card-body light">
                                <div class="">
                                    <!--<a href="{{url('/')}}"><img class="img-fluid logo logo-lg" src="{{getLogo()}}" alt="Logo" style="height: 50px;"></a>
                                    <div class="mt-3"></div>-->
                                    <h2 style="text-align: center;font-family: emoji;" class="mb-3 f-w-600">Welcome to {{getSetting('site_title')}} </h2>
                                </div>


                                <!-- <form action="{{route('login')}}" method="POST" class="mt-4"> -->
                                <form action="{{route('login.perform')}}" method="POST" class="mt-4">
                                    @csrf
                                    <div class="form-group">
                                        <!--<label class="form-control-label">Email Address</label>-->
                                        <input type="text" id="email" class="form-control @error('email') is-invalid @enderror @error('username') is-invalid @enderror" name="email" value="{{ old('email') }}" placeholder="Enter your Email/Phone/EmpId" required autocomplete="email" autofocus>
                                        @error('email')
                                <span class="invalid-feedback" role="alert">
                                    <strong>{{ $message }}</strong>
                                </span>
                                        @enderror
                                        @error('username')
                                <span class="invalid-feedback" role="alert">
                                    <strong>{{ $message }}</strong>
                                </span>
                                        @enderror
                                    </div>
                                    <div class="form-group">
                                        <!--<label class="form-control-label">Password</label>-->
                                        <div class="pass-group">
                                            <input id="password" type="password" class="form-control pass-input @error('password') is-invalid @enderror" name="password" placeholder="Password" required autocomplete="current-password">
                                            <span class="fas fa-eye toggle-password "></span>
                                            @error('password')
                                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                            @enderror
                                        </div>
                                    </div>
                                    <div class="form-group mb-2">
                                        <div class="row">
                                            <div class="col-6">
                                                <div class="custom-control custom-checkbox">
                                                    <input class="custom-control-input" id="cb1" type="checkbox" name="remember" id="remember" {{ old('remember') ? 'checked' : '' }}>
                                                    <span class="checkmark"></span>
                                                    <label class="custom-control-label" for="cb1">Remember me</label>
                                                </div>
                                            </div>
                                            <div class="col-6 text-end">
                                                @if (Route::has('password.request'))
                                                <a class="forgot-link" href="{{route('password.request')}}">Forgot Password ?</a>
                                                @endif
                                            </div>
                                        </div>
                                    </div>
                                    <button class="btn-primary btn-block login-do-btn btn w-100" type="submit">Login</button>
                                </form>

                            </div>
                        </div>
                        <div class="col-lg-6 img-card-side">
                            <div class="auth-img-content">
                                <div class="auth-img-title text-center">
                                    <h2>Mandatory Organization Module</h2>
                                    <p>Your Digital Parent</p>
                                </div>
                                <img src="{{asset('assets/backend/img/img-pc.png')}}" alt="" class="img-fluid mt-5">
                            </div>
                        </div>
                    </div>
                </div>

            </div>
        </div>
        <div class="auth-footer">
            <div class="container-fluid">
                <div class="row">
                    <div class="col-6 text-center">
                        <p>© 2024 WorkPilot. All rights reserved. | Version: {{ env('APP_VERSION') }}</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>



<!-- Main Wrapper -->
<!--<div class="main-wrapper login-body">
    <div class="login-wrapper">
        <div class="container">

            <a href="{{url('/')}}">
                <img class="img-fluid logo-dark mb-2" src="{{getLogo()}}" alt="Logo">
            </a>
            <div class="loginbox">

                <div class="login-right">
                    <div class="login-right-wrap">
                        <h1>Login</h1>
                        <p class="account-subtitle">Access to our dashboard</p>

                        <form action="{{route('login')}}" method="POST">
                            @csrf
                            <div class="form-group">
                                <label class="form-control-label">Email Address</label>
                                <input type="text" id="email" class="form-control @error('email') is-invalid @enderror @error('username') is-invalid @enderror" name="email" value="{{ old('email') }}" required autocomplete="email" autofocus>
                                @error('email')
                                <span class="invalid-feedback" role="alert">
                                    <strong>{{ $message }}</strong>
                                </span>
                                @enderror
                                @error('username')
                                <span class="invalid-feedback" role="alert">
                                    <strong>{{ $message }}</strong>
                                </span>
                                @enderror
                            </div>
                            <div class="form-group">
                                <label class="form-control-label">Password</label>
                                <div class="pass-group">
                                    <input id="password" type="password" class="form-control pass-input @error('password') is-invalid @enderror" name="password" required autocomplete="current-password">
                                    <span class="fas fa-eye toggle-password "></span>
                                    @error('password')
                                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                    @enderror
                                </div>
                            </div>
                            <div class="form-group">
                                <div class="row">
                                    <div class="col-6">
                                        <div class="custom-control custom-checkbox">
                                            <input class="custom-control-input" id="cb1" type="checkbox" name="remember" id="remember" {{ old('remember') ? 'checked' : '' }}>
                                            <label class="custom-control-label" for="cb1">Remember me</label>
                                        </div>
                                    </div>
                                    <div class="col-6 text-end">
                                        @if (Route::has('password.request'))
                                        <a class="forgot-link" href="{{route('password.request')}}">Forgot Password ?</a>
                                        @endif
                                    </div>
                                </div>
                            </div>
                            <button class="btn btn-lg btn-block btn-primary w-100" type="submit">Login</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>-->
<!-- /Main Wrapper -->
@endsection
