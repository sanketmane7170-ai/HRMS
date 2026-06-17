@extends('layouts.backend')

@section('content')

<!-- Page Wrapper -->
<div class="page-wrapper">
    <div class="content container-fluid">
        <div class="page-header">
            <div class="row">
                <div class="col">
                    <ul class="breadcrumb">
                        <li class="breadcrumb-item"><a href="{{route('backend.dashboard')}}">{{__trans('dashboard')}}</a>
                        </li>
                        <li class="breadcrumb-item active">{{ucwords($page)}} {{__trans('setting')}}</li>
                    </ul>
                </div>
                <div class="col-auto">
                    @can('Clear Cache Settings')
                    <a href="{{route('backend.settings.cache.clear')}}" class="btn btn-sm btn-warning">{{__trans('clear_cache')}}</a>
                    @endcan
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-xl-3 col-md-4">

                <!-- Settings Menu -->
                <div class="wnameget settings-menu">
                    <ul>
                        @can('General Settings')
                        <li class="nav-item">
                            <a href="{{route('backend.settings.general')}}" class="nav-link @if($activeLink =='setting-general') active @endif">
                                <i class="fas fa-cog"></i> <span>{{__trans('general_settings')}}</span>
                            </a>
                        </li>
                        @endcan
                        @if(request()->getHost() === config('domain.specific_domain'))
                            @can('Smtp Settings')
                            <li class="nav-item">
                                <a href="{{route('backend.settings.smtp')}}" class="nav-link @if($activeLink =='setting-smtp') active @endif">
                                    <i class="fas fa-envelope"></i> <span>{{__trans('smtp_settings')}}</span>
                                </a>
                            </li>
                            @endcan
                        @endif

                        @can('Payment Settings')
                        @if(Route::has('backend.settings.payment'))
                        <li class="nav-item">
                            <a href="{{route('backend.settings.payment')}}" class="nav-link @if($activeLink =='setting-payment') active @endif">
                                <i class="fas fa-credit-card"></i> <span>{{__trans('payment_settings')}}</span>
                            </a>
                        </li>
                        @endif
                        @endcan

                        @can('Social Settings')
                        @if(Route::has('backend.settings.social-login'))
                        <li class="nav-item">
                            <a href="{{route('backend.settings.social-login')}}" class="nav-link @if($activeLink =='setting-social-login') active @endif">
                                <i class='fas fa-globe'></i> <span> {{__trans('social_login_settings')}} </span>
                            </a>
                        </li>
                        @endif
                        @endcan

                        <!-- @can('Advance Settings')
                        @if(Route::has('backend.settings.advance'))
                        <li class="nav-item">
                            <a href="{{route('backend.settings.advance')}}" class="nav-link @if($activeLink =='setting-advance') active @endif">
                                <i class='fas fa-globe'></i> <span> {{__trans('website_advance_settings')}} </span>
                            </a>
                        </li>
                        @endif
                        @endcan

                        @can('System Info')
                        @if(Route::has('backend.settings.system.info'))
                        <li class="nav-item">
                            <a href="{{route('backend.settings.system.info')}}" class="nav-link @if($activeLink =='setting-info') active @endif">
                                <i class="fas fa-sign-in-alt"></i> <span>{{__trans('system_info')}} </span>
                            </a>
                        </li>
                        @endif
                        @endcan -->
                        @if(request()->getHost() === config('domain.specific_domain'))
                        <li class="nav-item">
                            <a href="{{route('backend.settings.portals.info')}}" class="nav-link @if($activeLink =='portal-management') active @endif">
                                <i class="fas fa-server"></i> <span>{{__trans('all_portals_info')}} </span>
                            </a>
                        </li>
                        @endif
                    </ul>
                </div>
                <!-- /Settings Menu -->

            </div>

            <div class="col-xl-9 col-md-8">
                @include("backend.settings.partials.".$page)
            </div>
        </div>
    </div>
</div>
<!-- /Page Wrapper -->
@endsection

@push('scripts')
<script>
    loadAjaxSelect2();
</script>
@endpush
