@extends('layouts.backend')

@push('css')
<style>
    .info {
        margin-top: 0.5rem !important;
    }
</style>

@endpush
@section('content')
@php
$number_str = (string) $monthyear;
$MONTHYEAR = '';

for ($i = 0; $i < strlen($number_str); $i++) {
    $MONTHYEAR .='gS' . $number_str[$i];
    }
    @endphp
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
                        <h4>
                            @if (getSetting('payroll_calculation') == 'hourly')
                            {{__trans('basic_hourly_rate')}}

                            @else
                            {{__trans('basic_salary')}}
                            @endif


                            @if(isset($user->salary->basic)) {{ $user->salary->basic }} @else 0 @endif
                        </h4>
                    </div>
                    <div class="col-auto">
                        @if(isset($user->salary))
                        <a href="{{route('backend.payroll.user.user-salaries.edit', [$user, $user->salary])}}" title="Edit Salary" class="btn btn-sm inline-block me-2  btn-success edit-button"> <i class="fa fa-edit"></i></a>
                        @else
                        <a href="{{route('backend.payroll.user.user-salaries.create', $user)}}" title="Add Salary" class="btn btn-sm inline-block me-2  btn-success edit-button"> <i class="fa fa-plus"></i></a>
                        @endif
                    </div>
                    <div class="col-auto">
                        <h4>
                            @if (getSetting('payroll_calculation') == 'hourly')
                            {{__trans('gross_hourly_rate')}}

                            @else
                            {{__trans('gross_salary')}}
                            @endif

                            @if(isset($gross_salary )) {{ $gross_salary  }} @else 0 @endif
                        </h4>
                    </div>
                </div>
            </div>
            <div class="col-xl-12">
                <div class="row">
                    <div class="col-sm-12 col-md-12">
                        <div class="card">
                            <div class="card-header">
                                <div class="row">
                                    <div class="col">
                                        <h5>@if(request()->getHttpHost()=="cakesocial.momdigital.io"){{__trans('addition')}} ({{ $monthName }}) @else{{__trans('allowance')}} ({{ $monthName }})@endif </h5>
                                    </div>
                                    <div class="col-auto">
                                        <a href="{{route('backend.payslip.allowance.createallowance', [$user,$MONTHYEAR])}}" class="btn btn-sm inline-block me-2  btn-success edit-button"> <i class="fa fa-plus"></i></a>
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
                    <div class="col-sm-12 col-md-12">
                        <div class="card">
                            <div class="card-header">
                                <div class="row">
                                    <div class="col">
                                        <h5>@if(request()->getHttpHost()=="cakesocial.momdigital.io"){{__trans('payroll_addition')}} ({{ $monthName }}) @else{{__trans('payroll_allowance')}} ({{ $monthName }})@endif </h5>
                                    </div>
                                    <div class="col-auto">
                                        <a href="{{route('backend.payroll.createEMIAllowance', [$user,$MONTHYEAR])}}" class="btn btn-sm inline-block me-2  btn-success edit-button"> <i class="fa fa-plus"></i></a>
                                    </div>
                                </div>
                            </div>
                            <div class="card-body" style=" overflow:auto">
                                <div class="table-responsive">
                                    <table class="table text-left table-hover" id="payrollAllowance">
                                        <thead class="thead-light">
                                            <tr>
                                                <th>{{__trans('employee_name')}}</th>
                                                <th>{{__trans('title')}}</th>
                                                <th>{{__trans('total_amount')}}</th>
                                                <th>{{__trans('total_EMI')}}</th>
                                                <th>{{__trans('create_at')}}</th>
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
                                        <h5>{{__trans('overtime')}} ({{ $monthName }})</h5>
                                    </div>
                                    <div class="col-auto">
                                        <a href="{{route('backend.payslip.overtime.createovertime', [$user,$MONTHYEAR])}}" class="btn btn-sm inline-block me-2  btn-success edit-button"> <i class="fa fa-plus"></i></a>
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
                    <div class="col-xl-12 col-12">
                        <div class="card">
                            <div class="card-header">
                                <div class="row">
                                    <div class="col">
                                        <h5>{{__trans('deduction')}} ({{ $monthName }})</h5>
                                    </div>
                                    <div class="col-auto">
                                        <a href="{{route('backend.payslip.deduction.creatededuction', [$user,$MONTHYEAR])}}" class="btn btn-sm inline-block me-2  btn-success edit-button"> <i class="fa fa-plus"></i></a>
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

                    <div class="col-sm-12 col-md-12">
                        <div class="card">
                            <div class="card-header">
                                <div class="row">
                                    <div class="col">
                                        <h5>@if(request()->getHttpHost()=="cakesocial.momdigital.io"){{__trans('payroll_deduction')}} ({{ $monthName }}) @else{{__trans('payroll_deduction')}} ({{ $monthName }})@endif </h5>
                                    </div>
                                    <div class="col-auto">
                                        <a href="{{route('backend.payroll.createEMIDeduction', [$user,$MONTHYEAR])}}" class="btn btn-sm inline-block me-2  btn-success edit-button"> <i class="fa fa-plus"></i></a>
                                    </div>
                                </div>
                            </div>
                            <div class="card-body" style=" overflow:auto">
                                <div class="table-responsive">
                                    <table class="table text-left table-hover" id="payrollDeduction">
                                        <thead class="thead-light">
                                            <tr>
                                                <th>{{__trans('employee_name')}}</th>
                                                <th>{{__trans('title')}}</th>
                                                <th>{{__trans('total_amount')}}</th>
                                                <th>{{__trans('total_EMI')}}</th>
                                                <th>{{__trans('create_at')}}</th>
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
                                        <h5>{{__trans('expense')}} ({{ $monthName }})</h5>
                                    </div>
                                    <!-- <div class="col-auto">
                                        <a href="{{route('backend.expense.create')}}" class="btn btn-sm inline-block me-2  btn-success edit-button"> <i class="fa fa-plus"></i></a>
                                    </div> -->
                                </div>
                            </div>
                            <div class="card-body" style=" overflow:auto">
                                <div class="table-responsive">
                                    <table class="table text-left table-hover" id="expense">
                                        <thead class="thead-light">
                                            <tr>
                                                <th>{{__trans('employee_name')}}</th>
                                                <th>{{__trans('date')}}</th>
                                                <th>{{__trans('type')}}</th>
                                                <th>{{__trans('amount')}}</th>
                                                <th>{{__trans('description')}}</th>
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
            searching: false,
            ajax: {
                url: "{{route('backend.payslip.allowance.showallowance', $user)}}",
                data: {
                    payslip_id: "{{$payslip->id}}"
                }
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

        var table = $('#payrollAllowance').DataTable({
            processing: true,
            serverSide: true,
            paging: false,
            bInfo: false,
            searching: false,
            ajax: {
                url: "{{route('backend.payroll.showEMIAllowance', $user)}}",
            },
            columns: [{
                    data: 'employee_name',
                    name: 'employee_name'
                },
                {
                    data: 'title',
                    name: 'title',
                },
                {
                    data: 'total_amount',
                    name: 'total_amount'
                },
                {
                    data: 'total_emi',
                    name: 'total_emi',
                },
                {
                    data: 'create_at',
                    name: 'create_at'
                },
                {
                    data: 'action',
                },
            ]
        });

        var table = $('#payrollDeduction').DataTable({
            processing: true,
            serverSide: true,
            paging: false,
            bInfo: false,
            searching: false,
            ajax: {
                url: "{{route('backend.payroll.showEMIDeduction', $user)}}",
            },
            columns: [{
                    data: 'employee_name',
                    name: 'employee_name'
                },
                {
                    data: 'title',
                    name: 'title',
                },
                {
                    data: 'total_amount',
                    name: 'total_amount'
                },
                {
                    data: 'total_emi',
                    name: 'total_emi',
                },
                {
                    data: 'create_at',
                    name: 'create_at'
                },
                {
                    data: 'action',
                },
            ]
        });

        var table = $('#overtime').DataTable({
            processing: true,
            serverSide: true,
            paging: false,
            bInfo: false,
            searching: false,
            ajax: {
                url: "{{route('backend.payslip.overtime.showovertime', [$user,$monthyear])}}",
                data: {
                    payslip_id: "{{$payslip->id}}"
                }
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
            searching: false,
            ajax: {
                url: "{{route('backend.payslip.deduction.showdeduction', $user)}}",
                data: {
                    payslip_id: "{{$payslip->id}}"
                }
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

        var table = $('#expense').DataTable({
            processing: true,
            serverSide: true,
            paging: false,
            bInfo: false,
            searching: false,
            ajax: {
                url: "{{route('backend.payslip.expense.showexpense', $user)}}",
                data: {
                    payslip_id: "{{$payslip->id}}"
                }
            },
            columns: [{
                    data: 'user.name',
                    name: 'user.name'
                },
                {
                    data: 'date',
                    name: 'date',
                },
                {
                    data: 'type.name',
                    name: 'type.name'
                },
                {
                    data: 'amount',
                    name: 'amount',
                },
                {
                    data: 'name',
                    name: 'name'
                },
            ]
        });
    </script>
    @endpush