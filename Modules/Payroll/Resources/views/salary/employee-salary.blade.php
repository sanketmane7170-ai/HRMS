@extends('layouts.backend')

@push('css')
<style>
    .info {
        margin-top: 0.5rem !important;
    }
</style>

@endpush
@section('content')
<div class="page-wrapper">
    <div class="content container-fluid">
        <!-- Page Header -->
        <div class="page-header">
            <div class="row align-items-center">
                <div class="col">
                    <h3 class="page-title">{{__trans('salary')}}</h3>
                    <ul class="breadcrumb">
                        <li class="breadcrumb-item"><a href="{{route('backend.dashboard')}}">{{__trans('dashboard')}}</a>
                        </li>
                        <li class="breadcrumb-item active">{{__trans('pay_roll')}}</li>
                        <li class="breadcrumb-item">{{__trans('salary')}}</li>
                    </ul>
                </div>
                <div class="col-auto">
                    <h4>{{__trans('basic_salary')}} @if(isset($user->salary->basic)) {{ $user->salary->basic }} @else 0 @endif </h4>
                </div>
                <div class="col-auto">
                    <h4>{{__trans('gross_salary')}} @if(isset($gross_salary )) {{ $gross_salary  }} @else 0 @endif </h4>
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
                                    <h5>{{__trans('allowance')}} ({{ date("F Y") }})</h5>
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
                                        <h5>{{__trans('deduction')}} ({{ date("F Y") }})</h5>
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
            url: "{{route('backend.my-salary.allowance')}}",
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
        ]
    });
    var table = $('#overtime').DataTable({
        processing: true,
        serverSide: true,
        paging: false,
        bInfo: false,
        searching:false,
        ajax: {
            url: "{{route('backend.my-salary.overtime')}}",
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
        ]
    });
    var table = $('#deduction').DataTable({
        processing: true,
        serverSide: true,
        paging: false,
        bInfo: false,
        searching:false,
        ajax: {
            url: "{{route('backend.my-salary.deduction')}}",
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
        ]
    });
</script>
@endpush