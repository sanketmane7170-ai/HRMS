@extends('layouts.backend')
@section('content')

<!-- Page Wrapper -->
<div class="page-wrapper">
    <div class="content container-fluid">
        <!-- Page Header -->
        <div class="page-header">
            <div class="row align-items-center">
                <div class="col">
                    <h3 class="page-title">{{__trans('announcement_list')}}</h3>
                    <ul class="breadcrumb">
                        <li class="breadcrumb-item"><a href="{{route('backend.dashboard')}}">{{__trans('dashboard')}}</a>
                        </li>
                        <li class="breadcrumb-item active">{{__trans('announcement_list')}}</li>
                    </ul>
                </div>
                <div class="col-auto">
                    @can('Create Announcement')
                    <a href="{{route('backend.announcements.create')}}" class="btn btn-primary me-1 edit-button">
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
                                        <th>{{__trans('department')}}</th>
                                        <th>{{__trans('user')}}</th>
                                        <th>{{__trans('content')}}</th>
                                        <th>{{__trans('start_at')}}</th>
                                        <th>{{__trans('end_at')}}</th>
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
@endsection
@push('css')
<link rel="stylesheet" href="{{asset('assets/backend/plugins/flatpickr/flatpickr.min.css')}}">
@endpush

@push('scripts')
<script src="{{asset('assets/backend/plugins/flatpickr/flatpickr.min.js')}}"></script>
<script type="text/javascript">
    var table = $('#dataTable').DataTable({
        processing: true,
        serverSide: true,
        order: [[0, 'desc']],
        ajax: {
            url: "{{route('backend.announcements.index')}}",
        },
        columns: [{
                data: 'DT_RowIndex',
                name: 'id'
            },
            {
                data: 'department_name',
                name: 'department_id'
            },
            {
                data: 'user_name',
                name: 'user_id',
            },
            {
                data: 'body',
            },
            {
                data: 'start_at',
            },
            {
                data: 'end_at',
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
