@extends('layouts.backend')
@section('content')

<!-- Page Wrapper -->
<div class="page-wrapper">
    <div class="content container-fluid">
        <!-- Page Header -->
        <div class="page-header">
            <div class="row align-items-center">
                <div class="col">
                    <h3 class="page-title">{{__trans('designation_list')}}</h3>
                    <ul class="breadcrumb">
                        <li class="breadcrumb-item"><a href="{{route('backend.dashboard')}}">{{__trans('dashboard')}}</a>
                        </li>
                        <li class="breadcrumb-item active">{{__trans('designation_list')}}</li>
                    </ul>
                </div>
                <div class="col-auto">
                    @can('Import Designation')
                    <a href="{{route('backend.departments.import')}}" class="btn btn-warning btn-sm me-1 " data-bs-target="#import-modal" data-bs-toggle="modal">
                        <i class="fas fa-file-excel"></i> {{__trans('import')}}
                    </a>
                    @endcan
                    @can('Export Designation')
                    <a href="{{route('backend.designations.export')}}" class="btn btn-success btn-sm me-1">
                        <i class="fas fa-file-excel"></i> {{__trans('export')}}
                    </a>
                    @endcan
                    @can('Create Designation')
                    <a href="{{route('backend.designations.create')}}" class="btn btn-primary btn-sm me-1 edit-button" method="GET">
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
                                        <th>{{__trans('code')}}</th>
                                        <th>{{__trans('grade')}}</th>
                                        <!-- <th>{{__trans('department')}}</th> -->
                                        <th>{{__trans('branch')}}</th>
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
@include('common.modals.import-modal',['importUrl'=> route('backend.designations.import')])
<!-- /Page Wrapper -->
<div id="editModal" class="modal" role="dialog" aria-labelledby="myModalLabel" aria-modal="true">

</div>
@endsection

@push('scripts')

<script type="text/javascript">
    var table = $('#dataTable').DataTable({
        processing: true,
        serverSide: true,
        ajax: {
            url: "{{route('backend.designations.index')}}",
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
                data: 'code',
                name: 'code'
            },
            {
                data: 'grade',
                name: 'grade'
            },
            {
                data: 'department',
                name: 'department'
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
