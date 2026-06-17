@extends('layouts.backend')
@section('content')

<!-- Page Wrapper -->
<div class="page-wrapper">
    <div class="content container-fluid">
        <div class="row">

            @can('Leave Policy')
            <div class="col-xl-3 col-sm-6 col-12">
                <div class="card top-stat-box top-stat-box-5">
                    <div class="card-body">
                        <div class="dash-widget-header">
                            <div class="dash-title">
                                <h6> <a style="color: white;" class="@if($activeLink =='leaves_policy') active @endif"
                                        href="{{route('backend.settings.leaves_policy')}}">{{__trans('Leaves Policy')}}</a>
                                </h6>
                            </div>


                        </div>
                    </div>
                </div>
            </div>
            @endcan
            @can('Attendance Policy')
            <div class="col-xl-3 col-sm-6 col-12">
                <div class="card top-stat-box top-stat-box-5">
                    <div class="card-body">
                        <div class="dash-widget-header">
                            <div class="dash-title">
                                <h6> <a style="color: white;"
                                        class="@if($activeLink =='attendance_policy') active @endif"
                                        href="{{route('backend.settings.attendance_policy')}}">{{__trans('Attendance Policy')}}</a>
                                </h6>
                            </div>


                        </div>
                    </div>
                </div>
            </div>
            @endcan
            @can('Late Comers Policy')
            <div class="col-xl-3 col-sm-6 col-12">
                <div class="card top-stat-box top-stat-box-5">
                    <div class="card-body">
                        <div class="dash-widget-header">
                            <div class="dash-title">
                                <h6> <a style="color: white;"
                                        class="@if($activeLink =='late_comers_policy') active @endif"
                                        href="{{route('backend.settings.late_comers_policy')}}">{{__trans('Late Comers Policy')}}</a>
                                </h6>
                            </div>


                        </div>
                    </div>
                </div>
            </div>
            @endcan
            @can('Early Comers Policy')
            <div class="col-xl-3 col-sm-6 col-12">
                <div class="card top-stat-box top-stat-box-5">
                    <div class="card-body">
                        <div class="dash-widget-header">
                            <div class="dash-title">
                                <h6> <a style="color: white;"
                                        class="@if($activeLink =='early_comers_policy') active @endif"
                                        href="{{route('backend.settings.early_comers_policy')}}">{{__trans('Early Comers Policy')}}</a>
                                </h6>
                            </div>


                        </div>
                    </div>
                </div>
            </div>
            @endcan



        </div>

    </div>
    <div id="editModal" class="modal" role="dialog" aria-labelledby="myModalLabel" aria-modal="true">

    </div>
    <!-- /Page Wrapper -->
    @endsection

    @push('scripts')
    <script>
        loadAjaxSelect2();
    </script>
    @endpush