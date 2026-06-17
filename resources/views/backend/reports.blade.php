@extends('layouts.backend')
@section('content')
<?php
$totalEmployee = employeeCount();
$femaleEmployeeCount = femaleEmployeeCount();
$maleEmployeeCount = maleEmployeeCount();
$countryUserList = getCountryUserList();
$alldepartments = getalldepartments();
?>
<!-- Page Wrapper -->
<div class="page-wrapper">
    <div class="content container-fluid">
        <div class="row">

            @can('Leave Reports')
            <div class="col-xl-3 col-sm-6 col-12">
                <div class="card top-stat-box top-stat-box-5">
                    <div class="card-body">
                        <div class="dash-widget-header">
                            <div class="dash-title">
                                <h6> <a class="@if($activeLink =='leaves_report') active @endif"
                                        href="{{route('backend.reports.leaves_report')}}">{{__trans('Leaves Report')}}</a>
                                </h6>
                            </div>


                        </div>
                    </div>
                </div>
            </div>
            @endcan
            @can('Attendance Reports')
            <div class="col-xl-3 col-sm-6 col-12">
                <div class="card top-stat-box top-stat-box-5">
                    <div class="card-body">
                        <div class="dash-widget-header">
                            <div class="dash-title">
                                <h6> <a class="@if($activeLink =='attendance_report') active @endif"
                                        href="{{route('backend.reports.attendance_report')}}">{{__trans('Attendance Report')}}</a>
                                </h6>
                            </div>


                        </div>
                    </div>
                </div>
            </div>
            @endcan
            @can('Late Comers Reports')
            <div class="col-xl-3 col-sm-6 col-12">
                <div class="card top-stat-box top-stat-box-5">
                    <div class="card-body">
                        <div class="dash-widget-header">
                            <div class="dash-title">
                                <h6> <a class="@if($activeLink =='late_comers_report') active @endif"
                                        href="{{route('backend.reports.late_comers_report')}}">{{__trans('Late Comers Report')}}</a>
                                </h6>
                            </div>


                        </div>
                    </div>
                </div>
            </div>
            @endcan
            @can('Early Comers Reports')
            <div class="col-xl-3 col-sm-6 col-12">
                <div class="card top-stat-box top-stat-box-5">
                    <div class="card-body">
                        <div class="dash-widget-header">
                            <div class="dash-title">
                                <h6> <a class="@if($activeLink =='early_comers_report') active @endif"
                                        href="{{route('backend.reports.early_comers_report')}}">{{__trans('Early Comers Report')}}</a>
                                </h6>
                            </div>


                        </div>
                    </div>
                </div>
            </div>
            @endcan

            @can('Early Outs Reports')
            <div class="col-xl-3 col-sm-6 col-12">
                <div class="card top-stat-box top-stat-box-5">
                    <div class="card-body">
                        <div class="dash-widget-header">
                            <div class="dash-title">
                                <h6> <a class="@if($activeLink =='early_outs_report') active @endif"
                                        href="{{route('backend.reports.early_outs_report')}}">{{__trans('Early Outs Report')}}</a>
                                </h6>
                            </div>


                        </div>
                    </div>
                </div>
            </div>
            @endcan
            @can('Salary Increments Reports')
            <div class="col-xl-3 col-sm-6 col-12">
                <div class="card top-stat-box top-stat-box-5">
                    <div class="card-body">
                        <div class="dash-widget-header">
                            <div class="dash-title">
                                <h6> <a class="@if($activeLink =='increment_report') active @endif"
                                        href="{{route('backend.reports.increment_report')}}">{{__trans('salary_increment_report')}}</a>
                                </h6>
                            </div>


                        </div>
                    </div>
                </div>
            </div>
            @endcan
            @can('Expense Reports')
            <div class="col-xl-3 col-sm-6 col-12">
                <div class="card top-stat-box top-stat-box-5">
                    <div class="card-body">
                        <div class="dash-widget-header">
                            <div class="dash-title">
                                <h6> <a class="@if($activeLink =='expense-report') active @endif"
                                        href="{{route('backend.reports.expense_report')}}">{{__trans('expense_report')}}</a>
                                </h6>
                            </div>


                        </div>
                    </div>
                </div>
            </div>
            @endcan
            @can('Gratuity Reports')
            <div class="col-xl-3 col-sm-6 col-12">
                <div class="card top-stat-box top-stat-box-5">
                    <div class="card-body">
                        <div class="dash-widget-header">
                            <div class="dash-title">
                                <h6> <a class="@if($activeLink =='gratuity-report') active @endif"
                                        href="{{route('backend.reports.gratuity_report')}}">{{__trans('gratuity_report')}}</a>
                                </h6>
                            </div>


                        </div>
                    </div>
                </div>
            </div>
            @endcan

            @can('Accruals Report')
            <div class="col-xl-3 col-sm-6 col-12">
                <div class="card top-stat-box top-stat-box-5">
                    <div class="card-body">
                        <div class="dash-widget-header">
                            <div class="dash-title">
                                <h6> <a class="@if($activeLink =='accruals-report') active @endif"
                                        href="{{route('backend.reports.accruals_report')}}">{{__trans('accruals_report')}}</a>
                                </h6>
                            </div>


                        </div>
                    </div>
                </div>
            </div>
            @endcan
            {{-- @can('Accruals Report')  --}}
            <div class="col-xl-3 col-sm-6 col-12">
                <div class="card top-stat-box top-stat-box-5">
                    <div class="card-body">
                        <div class="dash-widget-header">
                            <div class="dash-title">
                                <h6> <a class="@if($activeLink =='accruals-report') active @endif"
                                        href="{{route('backend.reports.branch_budget_report')}}">{{__trans('branch_budget_report')}}</a>
                                </h6>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            {{-- @endcan  --}}

            <div class="col-xl-3 col-sm-6 col-12">
                <div class="card top-stat-box top-stat-box-5">
                    <div class="card-body">
                        <div class="dash-widget-header">
                            <div class="dash-title">
                                <h6> <a class="@if($activeLink =='air-ticket-report') active @endif"
                                        href="{{route('backend.reports.air_ticket_report')}}">{{__trans('air_ticket_report')}}</a>
                                </h6>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- <div class="col-xl-3 col-sm-6 col-12">
                <div class="card top-stat-box top-stat-box-5">
                    <div class="card-body">
                        <div class="dash-widget-header">
                            <div class="dash-title">
                                <h6> <a class="@if($activeLink =='vacation_leave_report') active @endif"
                                        href="{{route('backend.reports.vacation_leave_report')}}">{{__trans('vacation_leave_report')}}</a>
                                </h6>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-xl-3 col-sm-6 col-12">
                <div class="card top-stat-box top-stat-box-5">
                    <div class="card-body">
                        <div class="dash-widget-header">
                            <div class="dash-title">
                                <h6> <a class="@if($activeLink =='initial_leave_balance') active @endif"
                                        href="{{route('backend.reports.initial_leave_balance')}}">{{__trans('initial_leave_balance')}}</a>
                                </h6>
                            </div>
                        </div>
                    </div>
                </div>
            </div> -->
        </div>

    </div>
</div>
<!-- /Page Wrapper -->
@endsection
@push('scripts')
<script type="text/javascript" src="https://www.gstatic.com/charts/loader.js"></script>
<script type="text/javascript">
    $(document).ready(function() {

    });
</script>

<script>

</script>
@endpush
