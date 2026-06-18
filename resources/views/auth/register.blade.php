@extends('layouts.app')

@section('content')


<div class="auth-wrapper auth-v3">
    <div class="bg-auth-side bg-primary"></div>
    <div class="auth-content">
    <div class="login-header" style="height: 60px;">
            <a href="{{url('/')}}" class="d-lg-block d-none"><img class="img-fluid logo logo-lg" src="{{getLogo()}}" alt="Logo" style="height: 52px;"></a>
            <a href="{{route('backend.dashboard')}}" class="logo logo-small d-lg-none d-block"><img src="{{getSmallLogo()}}" alt="Logo" alt="Logo" style="height: 52px;"></a>
        </div>
        <div class="card">
            <div class="row align-items-center text-start">
                <div class="card">
                    <div class="row align-items-center text-start">
                        <div class="col-lg-6">
                            <div class="card-body">
                                <div class="">
                                    <!--<a href="{{url('/')}}"><img class="img-fluid logo logo-lg" src="{{getLogo()}}" alt="Logo" style="height: 50px;"></a>
                                    <div class="mt-3"></div>-->
                                    <h2 class="mb-4 f-w-600">{{ __('Register') }}</h2>
                                </div>


                                <form method="POST" action="{{ route('register') }}">
                                    @csrf

                                    <div class="form-group">
                                        <!--<label for="name" class="col-md-4 col-form-label text-md-end">{{ __('Name') }}</label>-->
                                        <input id="name" type="text" class="form-control @error('name') is-invalid @enderror" name="name" value="{{ old('name') }}" placeholder="Enter Name" required autocomplete="name" autofocus>

                                        @error('name')
                                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                        @enderror
                                    </div>

                                    <div class="form-group">
                                        <!--<label for="email" class="col-md-4 col-form-label text-md-end">{{ __('Email Address') }}</label>-->
                                        <input id="email" type="email" class="form-control @error('email') is-invalid @enderror" name="email" value="{{ old('email') }}" placeholder="Enter Email Address" required autocomplete="email">

                                        @error('email')
                                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                        @enderror
                                    </div>

                                    <div class="form-group">
                                        <!--<label for="password" class="col-md-4 col-form-label text-md-end">{{ __('Password') }}</label>-->
                                        <input id="password" type="password" class="form-control @error('password') is-invalid @enderror" name="password" placeholder="Enter Password" required autocomplete="new-password">

                                        @error('password')
                                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                        @enderror
                                    </div>

                                    <!--<div class="row mb-3">
                                        <label for="password-confirm" class="col-md-4 col-form-label text-md-end">{{ __('Confirm Password') }}</label>

                                        <div class="col-md-6">
                                            <input id="password-confirm" type="password" class="form-control" name="password_confirmation" required autocomplete="new-password">
                                        </div>
                                    </div>-->

                                    <button type="submit" class="btn-primary btn-block login-do-btn btn w-100">
                                        {{ __('Register') }}
                                    </button>
                                </form>

                            </div>
                        </div>
                        <div class="col-lg-6 img-card-side">
                            <div class="auth-img-content">
                                <div class="auth-img-title text-center">
                                    <h5>Mandatory Organization Module</h5>
                                    <p>Your Digital Parent</p>
                                </div>
                                <img src="{{asset('assets/backend/img/img-pc.png')}}" alt="" class="img-fluid mt-5">
                            </div>
                        </div>
                    </div>
                </div>

            </div>
        </div>
        <div class="auth-footer" style="padding: 20px 0; text-align: center; font-size: 14px; color: #666;">
            <div class="container-fluid">
                <div class="row justify-content-center">
                    <div class="col-12">
                        © 2026 SR Global. Developed By Innozia | Version: 2.5.0
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>








<!--<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">{{ __('Register') }}</div>

                <div class="card-body">
                    <form method="POST" action="{{ route('register') }}">
                        @csrf

                        <div class="row mb-3">
                            <label for="name" class="col-md-4 col-form-label text-md-end">{{ __('Name') }}</label>

                            <div class="col-md-6">
                                <input id="name" type="text" class="form-control @error('name') is-invalid @enderror" name="name" value="{{ old('name') }}" required autocomplete="name" autofocus>

                                @error('name')
                                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                @enderror
                            </div>
                        </div>

                        <div class="row mb-3">
                            <label for="email" class="col-md-4 col-form-label text-md-end">{{ __('Email Address') }}</label>

                            <div class="col-md-6">
                                <input id="email" type="email" class="form-control @error('email') is-invalid @enderror" name="email" value="{{ old('email') }}" required autocomplete="email">

                                @error('email')
                                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                @enderror
                            </div>
                        </div>

                        <div class="row mb-3">
                            <label for="password" class="col-md-4 col-form-label text-md-end">{{ __('Password') }}</label>

                            <div class="col-md-6">
                                <input id="password" type="password" class="form-control @error('password') is-invalid @enderror" name="password" required autocomplete="new-password">

                                @error('password')
                                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                @enderror
                            </div>
                        </div>

                        <div class="row mb-3">
                            <label for="password-confirm" class="col-md-4 col-form-label text-md-end">{{ __('Confirm Password') }}</label>

                            <div class="col-md-6">
                                <input id="password-confirm" type="password" class="form-control" name="password_confirmation" required autocomplete="new-password">
                            </div>
                        </div>

                        <div class="row mb-0">
                            <div class="col-md-6 offset-md-4">
                                <button type="submit" class="btn btn-primary">
                                    {{ __('Register') }}
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>-->
@endsection
