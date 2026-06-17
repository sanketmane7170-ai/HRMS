@extends('layouts.app')
@section('content')

<div class="auth-wrapper auth-v3">
    <div class="auth-content">
        <div class="login-header" style="height: 60px;">

        </div>
        <div class="card">
            <div class="row align-items-center text-start">
                <div class="card">
                    <div class="row align-items-center text-start">
                        <div class="col-lg-2"></div>
                        <div class="col-lg-8">
                            <div class="card-body">
                                <div class="text-center">
                                    <a href="{{url('/')}}" class="d-lg-block d-none"><img class="img-fluid logo logo-lg" src="{{getLogo()}}" alt="Logo" style="height: 52px;"></a>
                                    <a href="{{route('backend.dashboard')}}" class="logo logo-small d-lg-none d-block"><img src="{{getSmallLogo()}}" alt="Logo" alt="Logo" style="height: 52px;"></a>
                                </div>
                                <div class="mt-3">
                                    <h2 class="mb-3 f-w-600 text-center">400 | Wrong request</h2>
                                </div>
                                <div style="text-align:center">
                                    <p class="mb-3 f-w-600 text-center">Sorry something wrong with URL or parameters </p>
                                </div>
                                <div style="text-align: center;">
                                    <a href="{{url('/')}}" class="btn-primary btn-block login-do-btn btn" style="width: 40% !important;">Login again</a>
                                </div>
                            </div>
                        </div>
                        <div class="col-lg-2"></div>
                    </div>
                </div>

            </div>
        </div>
        <div class="auth-footer">
            <div class="container-fluid">
                <div class="row">
                    <div class="col-12 text-center">
                        <p>© 2024 WorkPilot. All rights reserved.</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
