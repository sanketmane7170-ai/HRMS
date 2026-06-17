@extends('layouts.backend')
@section('content')

<!-- Page Wrapper -->
<div class="page-wrapper">
    <div class="content container-fluid">
        <!-- Page Header -->
        <div class="page-header">
            <div class="row align-items-center">
                <div class="col">
                    <h3 class="page-title">{{__trans('leave_list')}}</h3>
                    <ul class="breadcrumb">
                        <li class="breadcrumb-item"><a href="{{route('backend.dashboard')}}">{{__trans('dashboard')}}</a>
                        </li>
                        <li class="breadcrumb-item active">{{__trans('leave_list')}}</li>
                    </ul>
                </div>
                <div class="col-auto">
                    @can('Create Leave')
                    <a href="{{route('backend.employee.leaves.create')}}" class="btn btn-primary me-1 edit-button">
                        <i class="fas fa-plus"></i> {{__trans('new_leave_request')}}
                    </a>
                    @endcan
                </div>
            </div>
        </div>
        @php
        $authUser = auth()->user();
        @endphp
        @can('Dashboard Leave')
        <x-user-leave-balance :user=$authUser />
        @endcan
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
                                        <th>{{__trans('leave_type')}}</th>
                                        <th>{{__trans('start_date')}}</th>
                                        <th>{{__trans('end_date')}}</th>
                                        <th>{{__trans('is_half_day')}}</th>
                                        <th>{{__trans('status')}}</th>
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
        ajax: {
            url: "{{route('backend.employee.leaves.index')}}",
        },
        columns: [{
                data: 'DT_RowIndex',
                name: 'id'
            },
            {
                data: 'type.name',
                name: 'type.name'
            },
            {
                data: 'start_date',
            },
            {
                data: 'end_date',
            },
            {
                data: function(data) {
                    return data.is_half_day ? 'Yes' : 'No'
                },
                name: 'is_half_day'
            },
            {
                data: 'status',
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
