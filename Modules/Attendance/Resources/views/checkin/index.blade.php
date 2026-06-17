@extends('layouts.backend')
@section('content')

<!-- Page Wrapper -->
<div class="page-wrapper">
    <div class="content container-fluid">
        <!-- Page Header -->
        <div class="page-header">
            <div class="row align-items-center">
                <div class="col">
                    <h3 class="page-title">{{__trans('marked_attendance')}}</h3>
                    <ul class="breadcrumb">
                        <li class="breadcrumb-item"><a href="{{route('backend.dashboard')}}">{{__trans('dashboard')}}</a>
                        </li>
                        <li class="breadcrumb-item active">{{__trans('marked_attendance')}}</li>
                    </ul>
                </div>
                <div class="col-auto">
                    @can('Create Attendance')
                    <a href="{{route('backend.attendances.create')}}" class="edit-button btn btn-primary d-none">
                        <i class="fa fa-plus"></i> {{__trans('add_attendance')}}</a>
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
                                        <th>{{__trans('user')}}</th>
                                        <th>{{__trans('date')}}</th>
                                        <th>{{__trans('status')}}</th>
                                        <th>{{__trans('clock_in')}}</th>
                                        <th>{{__trans('clock_out')}}</th>
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
            url: "{{route('backend.attendances.index')}}",
        },
        columns: [{
                data: 'DT_RowIndex',
                name: 'id'
            },
            {
                data: 'user.name',
                name: 'user.name'
            },
            {
                data: 'date',
            },
            {
                data: 'status',
            },
            {
                data: 'clock_in',
            },
            {
                data: 'clock_out',
            },
            {
                data: 'action'
            }
        ]
    });
</script>
@endpush
