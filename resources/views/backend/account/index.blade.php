@extends('layouts.backend')
@section('content')


<!-- Page Wrapper -->
<div class="page-wrapper">
    <div class="content container-fluid">
        <div class="page-header">
            <div class="row">
                <div class="col-sm-6">
                    <ul class="breadcrumb">
                        <li class="breadcrumb-item"><a href="{{route('backend.dashboard')}}">{{__trans('dashboard')}}</a>
                        </li>
                        <li class="breadcrumb-item active">{{$title}}</li>
                    </ul>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-xl-3 col-md-4">

                <!-- Settings Menu -->
                <div class="wnameget settings-menu">
                    <ul>
                        <li class="nav-item">
                            <a href="{{route('backend.account')}}" class="nav-link @if($page =='account') active @endif">
                                <i class="far fa-user"></i> <span>{{__trans('profile_setting')}}</span>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="{{route('backend.change-password')}}" class="nav-link @if($page =='change-password') active @endif">
                                <i class="fas fa-unlock-alt"></i> <span>{{__trans('change_password')}}</span>
                            </a>
                        </li>

                    </ul>
                </div>
                <!-- /Settings Menu -->

            </div>

            <div class="col-xl-9 col-md-8">
                @include("backend.account.partials.".$page)
            </div>
        </div>
    </div>
</div>
<!-- /Page Wrapper -->
@endsection
