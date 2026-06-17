@extends('layouts.backend')
@section('content')

<!-- Page Wrapper -->
<div class="page-wrapper">
    <div class="content container-fluid">
        <!-- Page Header -->
        <div class="page-header">
            <div class="row align-items-center">
                <div class="col">
                    <!-- <h3 class="page-title">{{__trans('department_list')}}</h3> -->
                    <h3 class="page-title">{{__trans('branch_list')}}</h3>
                    <ul class="breadcrumb">
                        <li class="breadcrumb-item"><a href="{{route('backend.dashboard')}}">{{__trans('dashboard')}}</a>
                        </li>
                        <!-- <li class="breadcrumb-item active">{{__trans('department_list')}}</li> -->
                        <li class="breadcrumb-item active">{{__trans('branch_list')}}</li>
                    </ul>
                </div>
                <div class="col-auto">
                    @can('Import Department')
                    <a href="{{route('backend.departments.import')}}" class="btn btn-warning btn-sm me-1 " data-bs-target="#import-modal" data-bs-toggle="modal">
                        <i class="fas fa-file-excel"></i> {{__trans('import')}}
                    </a>
                    @endcan
                    @can('Export Department')
                    <a href="{{route('backend.departments.export')}}" class="btn btn-success btn-sm me-1">
                        <i class="fas fa-file-excel"></i> {{__trans('export')}}
                    </a>
                    @endcan
                    @can('Create Department')
                    <a href="{{route('backend.departments.create')}}" data-bs-toggle="modal" data-bs-target="#addResourceModal" class="btn btn-primary btn-sm me-1">
                        <i class="fas fa-plus"></i>
                    </a>
                    @endcan
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
                                        <th>{{__trans('short_name')}}</th>
                                        <th>{{__trans('start_number')}}</th>
                                        <th>{{__trans('code')}}</th>
                                        <th>{{__trans('Manager')}}</th>
                                        <th>{{__trans('address')}}</th>
                                        <th>{{__trans('login_radius')}}</th>
                                        <th>{{__trans('budget')}}</th>
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

@include('backend.department.create')
@include('common.modals.import-modal',['importUrl'=> route('backend.departments.import')])
<div id="editModal" class="modal" role="dialog" aria-labelledby="myModalLabel" aria-modal="true">

</div>
@endsection

@push('scripts')

<script type="text/javascript">
    var table = $('#dataTable').DataTable({
        processing: true,
        serverSide: true,
        ajax: {
            url: "{{route('backend.departments.index')}}",
        },
        columns: [{
                data: 'DT_RowIndex',
                name: 'id'
            },
            {
                data: 'name',
                name: 'name'
            },
            {
                data: 'short_name',
                name: 'short_name'
            },
            {
                data: 'start_number',
                name: 'start_number'
            },
            {
                data: 'code',
                name: 'code'
            },
            {
                data: 'manager',
                name: 'manager'
            },
            {
                data: 'address',
                name: 'address'
            },
            {
                data: 'login_radius',
                name: 'login_radius'
            },
            {
                data: 'budget',
                name: 'budget'
            },

            {
                data: 'action',
                name: 'action',
                orderable: false,
                searchable: false
            },
        ]
    });
</script>
@endpush
