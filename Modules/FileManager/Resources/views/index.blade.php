@extends('layouts.backend')

@push('css')
<link rel="stylesheet" href="{{asset('assets/backend/plugins/flatpickr/flatpickr.min.css')}}">
@endpush
@section('content')

<!-- Page Wrapper -->
<div class="page-wrapper bg-white">
    <div class="content container-fluid">
        <!-- Page Header -->
        <div class="page-header">
            <div class="row align-items-center">
                <div class="col">
                    <h3 class="page-title">{{ __trans('File Manager') }}</h3>
                    <ul class="breadcrumb">
                        <li class="breadcrumb-item"><a
                                href="{{ route('backend.dashboard') }}">{{ __trans('dashboard') }}</a>
                        </li>
                        <li class="breadcrumb-item active">{{ __trans('File Manager') }}</li>
                    </ul>
                </div>
                <div class="col-auto">
                @can('Create Department')
                    <a href="{{route('backend.departments.index')}}" class="btn btn-primary btn-sm me-1">
                        <i class="fas fa-plus"></i> Add Branch
                    </a>
                    @endcan
                    <a href="#" class="btn btn-info btn-sm me-1 " data-bs-target="#edit-import-modal" data-bs-toggle="modal">
                        <i class="fas fa-file-excel"></i> {{__trans('import_file_details')}}
                    </a>
                </div>
            </div>
        </div>
        <div class="row">
            <div class="col-sm-12">
                <div class="card card-table">
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-hover" id="dataTable">
                                <thead class="thead-light">
                                    <tr>
                                        <th>Id's</th>
                                        <th>{{__trans('name')}}</th>
                                        <th>{{__trans('code')}}</th>
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
@endsection
<div id="editModal" class="modal" role="dialog" aria-labelledby="myModalLabel" aria-modal="true">

</div>
@include('common.modals.import-file-details-modal',['importUrl'=> route('backend.updateFileDetailsToExcel'),'flag'=>true])

@push('scripts')

<script type="text/javascript">
    var table = $('#dataTable').DataTable({
        processing: true,
        serverSide: true,
        ajax: {
            url: "{{route('backend.filemanager.index')}}",
        },
        columns: [{
                data: 'DT_RowIndex',
                name: 'id'
            },
            // {
            //     data: 'name',
            //     name: 'name'
            // },
            // {
            //     data: 'file_real_name',
            //     name: 'file_real_name'
            // },
            // {
            //     data: 'department.name',
            //     name: 'department.name'
            // },
            // {
            //     data:'upload_date',
            //     name:'Created At'
            // },
            // {
            //     data: 'action',
            //     orderable: false,
            //     searchable: false
            // }
            {
                data: 'name',
                name: 'name'
            },
            {
                data: 'code',
                name: 'code'
            },
            {
                data: 'action',
                orderable: false,
                searchable: false
            }
        ]
    });
</script>
@endpush