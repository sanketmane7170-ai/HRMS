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
                                    <h2 class="mb-4 f-w-600">{{ __('Verify Your Email Address') }}</h2>
                                </div>


                                @if (session('resent'))
                                <div class="alert alert-success" role="alert">
                                    {{ __('A fresh verification link has been sent to your email address.') }}
                                </div>
                                @endif

                                {{ __('Before proceeding, please check your email for a verification link.') }}
                                {{ __('If you did not receive the email') }},
                                <form class="d-inline" method="POST" action="{{ route('verification.resend') }}">
                                    @csrf
                                    <button type="submit" class="btn btn-link p-0 m-0 align-baseline">{{ __('click here to request another') }}</button>.
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
                        © 2026 WorkPilot. Developed By Sanket (<a href="mailto:contactsanket1@gmail.com" style="color: #007bff; text-decoration: none;">contactsanket1@gmail.com</a>) | Version: 2.5.0
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
                <div class="card-header">{{ __('Verify Your Email Address') }}</div>

                <div class="card-body">
                    @if (session('resent'))
                        <div class="alert alert-success" role="alert">
                            {{ __('A fresh verification link has been sent to your email address.') }}
                        </div>
                    @endif

                    {{ __('Before proceeding, please check your email for a verification link.') }}
                    {{ __('If you did not receive the email') }},
                    <form class="d-inline" method="POST" action="{{ route('verification.resend') }}">
                        @csrf
                        <button type="submit" class="btn btn-link p-0 m-0 align-baseline">{{ __('click here to request another') }}</button>.
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>-->
@endsection
