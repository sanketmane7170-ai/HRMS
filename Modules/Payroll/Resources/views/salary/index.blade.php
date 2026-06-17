@extends('layouts.backend')
@section('content')

<!-- Page Wrapper -->
<div class="page-wrapper">
    <div class="content container-fluid">
        <!-- Page Header -->
        <div class="page-header">
            <div class="row align-items-center">
                <div class="col">
                    <h3 class="page-title">{{__trans('user_salary_list')}} ({{$monthName}})</h3>
                    <ul class="breadcrumb">
                        <li class="breadcrumb-item"><a href="{{route('backend.dashboard')}}">{{__trans('dashboard')}}</a>
                        </li>
                        <li class="breadcrumb-item active">{{__trans('user_salary_list')}}</li>
                    </ul>
                </div>
                <!-- <div class="col-auto">
                    
                </div> -->
                <div class="col-auto">
                    <a href="{{route('backend.users.import.excel')}}" class="btn btn-sm me-1 " style="background-color:#951181; color:white;" data-bs-target="#import-allowance-modal" data-bs-toggle="modal">
                        <i class="fas fa-file-excel"></i> {{__trans('import_allowance')}}
                    </a>
                    <a href="{{route('backend.users.import.excel')}}" class="btn btn-sm me-1 " style="background-color:#951181; color:white;" data-bs-target="#import-deduction-modal" data-bs-toggle="modal">
                        <i class="fas fa-file-excel"></i> {{__trans('import_deduction')}}
                    </a>
                    <a href="{{route('backend.users.import.excel')}}" class="btn btn-warning btn-sm me-1 " data-bs-target="#import-modal" data-bs-toggle="modal">
                        <i class="fas fa-file-excel"></i> {{__trans('import_salary')}}
                    </a>
                </div>
            </div>
        </div>
        <!-- /Page Header -->
        <div class="row">
            <div class="col-sm-12">
                <div class="card card-table">
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table text-center table-hover" id="dataTable">
                                <thead class="thead-light">
                                    <tr>
                                        <th>#</th>
                                        <th>{{__trans('name')}}</th>
                                        <th>{{__trans('department')}}</th>
                                        @if (getSetting('payroll_calculation') == 'hourly')
                                        <th>{{__trans('no_of_working_hours')}}</th>
                                        <th>{{__trans('basic_hourly_rate')}}</th>
                                        <th>{{__trans('gross_hourly_rate')}}</th>
                                        <th>{{__trans('net_salary(hourly)')}}</th>
                                        @else
                                        <th>{{__trans('no_of_working_days')}}</th>
                                        <th>{{__trans('basic_salary')}}</th>
                                        <th>{{__trans('gross_salary')}}</th>
                                        <th>{{__trans('net_salary(attendance)')}}</th>
                                        @endif
                                        <th>{{__trans('total_net_salary')}}</th>
                                        <th>{{__trans('actions')}}</th>
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
<!-- /Page Wrapper -->
<div id="editModal" class="modal" role="dialog" aria-labelledby="myModalLabel" aria-modal="true">

</div>
@include('common.modals.basic-salary-add-modal',['importUrl'=> route('backend.users.bsimport.excel'),'flag'=>true])
@include('common.modals.allowance-add-modal',['importUrl'=> route('backend.users.allowance.import.excel'),'flag'=>true])
@include('common.modals.deduction-add-modal',['importUrl'=> route('backend.users.deduction.import.excel'),'flag'=>true])
@endsection
@push('scripts')

<script type="text/javascript">
    var table = $('#dataTable').DataTable({
        processing: true,
        serverSide: true,
        ajax: {
            url: "{{route('backend.payroll.user-salaries.index')}}",
        },
        columns: [{
                data: 'id',
                name: 'id'
            },
            {
                data: 'name',
            },
            {
                data: 'department',
                name: 'department.name',
            },
            {
                name: 'working_days',
                data: 'working_days',
            },
            {
                data: 'basic',
                name: 'salary.basic',
            },
            {
                data: 'gross',
                name: 'salary.gross',
                orderable: false,
                searchable: false
            },
            {
                data: 'net_salary',
                name: 'net_salary'
            },
            {
                data: 'total_net_salary',
                name: 'total_net_salary'
            },
            {
                data: 'action',
                orderable: false,
                searchable: false
            },
        ]
    });
</script>
@endpush
