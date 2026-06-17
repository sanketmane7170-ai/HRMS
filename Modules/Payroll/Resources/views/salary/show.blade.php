@extends('layouts.backend')

@push('css')
<style>
    .info {
        margin-top: 0.5rem !important;
    }
</style>

@endpush
@section('content')
<!-- Page Wrapper -->
<div class="page-wrapper">
    <div class="content container-fluid">
        <!-- Page Header -->
        <div class="page-header">
            <div class="row align-items-center">
                <div class="col">
                    <h3 class="page-title">{{__trans('employee_set_salary')}} : {{$user->name}}</h3>
                    <ul class="breadcrumb">
                        <li class="breadcrumb-item"><a href="{{route('backend.dashboard')}}">{{__trans('dashboard')}}</a>
                        </li>
                        <li class="breadcrumb-item active"><a href="{{route('backend.payroll.user-salaries.index')}}">{{__trans('set_salary')}}</a></li>
                        <li class="breadcrumb-item">{{__trans('employee_set_salary')}}</li>
                    </ul>
                </div>
                <div class="col-auto">
                    <h4>{{__trans('basic_salary')}} @if(isset($user->salary->basic)) {{ $user->salary->basic }} @else 0 @endif </h4>
                </div>
                <div class="col-auto">
                    @if(isset($user->salary))
                         <a href="{{route('backend.payroll.user.user-salaries.edit', [$user, $user->salary])}}" title="Edit Salary" class="btn btn-sm inline-block me-2  btn-success edit-button"> <i class="fa fa-edit"></i></a>
                    @else
                         <a href="{{route('backend.payroll.user.user-salaries.create', $user)}}" title="Add Salary" class="btn btn-sm inline-block me-2  btn-success edit-button"> <i class="fa fa-plus"></i></a>
                    @endif
                </div>
                <div class="col-auto">
                    <h4>{{__trans('gross_salary')}} @if(isset($gross_salary )) {{ $gross_salary  }} @else 0 @endif </h4>
                </div>
            </div>
        </div>
        <div class="col-xl-12">
            <div class="row ">
                <div class="col-sm-12 col-md-12">
                    <div class="card ">
                        <div class="card-header">
                            <div class="row">
                                <div class="col">
                                    <h5>@if(request()->getHttpHost()=="cakesocial.momdigital.io") {{__trans('addition')}} ({{ date("F Y") }}) @else {{__trans('allowance')}} ({{ date("F Y") }})@endif</h5>
                                </div>
                                <div class="col-auto">
                                    <a href="{{route('backend.payroll.user.user-salaries.createallowance', $user)}}" class="btn btn-sm inline-block me-2  btn-success edit-button"> <i class="fa fa-plus"></i></a>
                                </div>
                            </div>
                        </div>
                        <div class="card-body" style=" overflow:auto">
                            <div class="table-responsive">
                                <table class="table text-left table-hover" id="dataTable">
                                    <thead class="thead-light">
                                        <tr>
                                            <th>{{__trans('employee_name')}}</th>
                                            <th>{{__trans('title')}}</th>
                                            <th>{{__trans('type')}}</th>
                                            <th>{{__trans('amount')}}</th>
                                            <th>{{__trans('monthly_fixed')}}</th>
                                            <th>{{__trans('action')}}</th>
                                        </tr>
                                    </thead>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-xl-12 col-12">
                    <div class="card">
                        <div class="card-header">
                            <div class="row">
                                <div class="col">
                                    <h5>{{__trans('overtime')}} ({{ date("F Y") }})</h5>
                                </div>
                                <div class="col-auto">
                                    <a href="{{route('backend.payroll.user.user-salaries.createovertime', $user)}}" class="btn btn-sm inline-block me-2  btn-success edit-button"> <i class="fa fa-plus"></i></a>
                                </div>
                            </div>
                        </div>
                        <div class="card-body" style=" overflow:auto">
                            <div class="table-responsive">
                                <table class="table text-left table-hover" id="overtime">
                                    <thead class="thead-light">
                                        <tr>
                                            <th>{{__trans('employee_name')}}</th>
                                            <th>{{__trans('title')}}</th>
                                            <th>{{__trans('hours')}}</th>
                                            <th>{{__trans('rate_per_hour')}}</th>
                                            <th>{{__trans('amount')}}</th>
                                            <th>{{__trans('action')}}</th>
                                        </tr>
                                    </thead>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
                {{--- <div class="col-xl-12 col-12">
                    <div class="card">
                        <div class="card-header">
                            <div class="row">
                                <div class="col">
                                    <h5>{{__trans('hourly_rate')}}</h5>
                                </div>
                                <!-- <div class="col-auto">
                                    <a href="{{route('backend.payroll.user.user-salaries.createovertime', $user)}}" class="btn btn-sm inline-block me-2  btn-success edit-button"> <i class="fa fa-plus"></i></a>
                                </div> -->
                            </div>
                        </div>
                        <div class="card-body" style=" overflow:auto">
                            <div class="table-responsive">
                                <table class="table text-left table-hover" id="hourly_rate">
                                    <thead class="thead-light">
                                        <tr>
                                            <th>{{__trans('date')}}</th>
                                            <th>{{__trans('total_worked').'(Hours)'}}</th>
                                            <th>{{__trans('hourly_rate')}}</th>
                                            <th>{{__trans('Total_Income_Hourly_Basis')}}</th>
                                        </tr>
                                    </thead>
                                </table>
                            </div>
                        </div>
                    </div>
                </div> ---}}
                <div class="col-xl-12 col-12">
                        <div class="card">
                            <div class="card-header">
                                <div class="row">
                                    <div class="col">
                                        <h5>{{__trans('deduction')}} ({{ date("F Y") }})</h5>
                                    </div>
                                    <div class="col-auto">
                                        <a href="{{route('backend.payroll.user.user-salaries.creatededuction', $user)}}" class="btn btn-sm inline-block me-2  btn-success edit-button"> <i class="fa fa-plus"></i></a>
                                    </div>
                                </div>
                            </div>
                            <div class="card-body" style=" overflow:auto">
                                <div class="table-responsive">
                                    <table class="table text-left table-hover" id="deduction">
                                        <thead class="thead-light">
                                            <tr>
                                                <th>{{__trans('employee_name')}}</th>
                                                <th>{{__trans('title')}}</th>
                                                <th>{{__trans('type')}}</th>
                                                <th>{{__trans('amount')}}</th>
                                                <th>{{__trans('monthly_fixed')}}</th>
                                                <th>{{__trans('action')}}</th>
                                            </tr>
                                        </thead>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
            </div>
        </div>
    </div>
</div>

<div id="editModal" class="modal"></div>
@endsection

@push('scripts')

<script type="text/javascript">
    var table = $('#dataTable').DataTable({
        processing: true,
        serverSide: true,
        paging: false,
        bInfo: false,
        searching:false,
        ajax: {
            url: "{{route('backend.payroll.user.user-salaries.show', [$user,$user->id])}}",
        },
        columns: [{
                data: 'employee_name',
                name: 'user.name'
            },
            {
                data: 'title',
                name: 'title',
            },
            {
                data: 'type',
                name: 'type'
            },
            {
                data: 'amount',
                name: 'amount',
            },
            {
                data: 'monthly_fixed',
                name: 'monthly_fixed'
            },
            {
                data: 'action',
            },
        ]
    });
    var table = $('#hourly_rate').DataTable({
        processing: true,
        serverSide: true,
        paging: false,
        bInfo: false,
        searching:false,
        ajax: {
            url: "{{route('backend.payroll.user.user-salaries.hourlysalary', [$user,$user->id])}}",
        },
        columns: [{
                data: 'date',
                name: 'date'
            },
           {
                data: 'total_worked',
                name: 'total_worked'
            },
            {
                data: 'hourly_rate',
                name: 'hourly_rate',
            },
            {
                data: 'total',
                name: 'total'
            }
        ]
    });
    var table = $('#overtime').DataTable({
        processing: true,
        serverSide: true,
        paging: false,
        bInfo: false,
        searching:false,
        ajax: {
            url: "{{route('backend.payroll.user.user-salaries.userovertimelist', [$user,$user->id])}}",
        },
        columns: [{
                data: 'employee_name',
                name: 'user.name'
            },
            {
                data: 'overtime_type',
                name: 'overtime_type',
            },
            {
                data: 'hours',
                name: 'hours'
            },
            {
                data: 'rate_per_hour',
                name: 'rate_per_hour',
            },
            {
                data: 'calculated_amount',
                name: 'calculated_amount'
            },
            {
                data: 'action',
            },
        ]
    });
    var table = $('#deduction').DataTable({
        processing: true,
        serverSide: true,
        paging: false,
        bInfo: false,
        searching:false,
        ajax: {
            url: "{{route('backend.payroll.user.user-salaries.userdeductionlist', [$user,$user->id])}}",
        },
        columns: [{
                data: 'employee_name',
                name: 'user.name'
            },
            {
                data: 'title',
                name: 'title',
            },
            {
                data: 'deduction_type',
                name: 'deduction_type'
            },
            {
                data: 'amount',
                name: 'amount',
            },
            {
                data: 'monthly_fixed',
                name: 'monthly_fixed'
            },
            {
                data: 'action',
            },
        ]
    });
</script>
@endpush