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
                    <a href="{{route('backend.leaves.create')}}" class="btn btn-primary me-1 edit-button">
                        <i class="fas fa-plus"></i> {{__trans('add_leave_request')}}
                    </a>
                    @endcan
                    @can('View Leave Update Logs EditUpdateLeave')
                    <a href="{{route('backend.leave-balance.update.logs')}}" class="btn btn-warning me-1">
                        <i class="fas fa-history"></i> {{__trans('leave_update_logs')}}
                    </a>
                    @endcan
                    @can('View Leave Update Logs EditUpdateLeave')
                    <a href="{{route('backend.leave-balance.update.transaction')}}" class="btn btn-info me-1">
                        <i class="fa fa-list"></i> {{__trans('show_leave_transaction')}}
                    </a>
                    @endcan
                </div>
            </div>
            <div class="mt-3 row">
                <div class="col-lg-12">
                    <form action="">
                        <div class="align-items-center att-indicators justify-content-end row">
                            <div class="col-lg-3">
                                <div class="form-group">
                                    <label><strong>{{ __trans('employee_name') }}:</strong></label>
                                    <select name="employee[]" class="form-control ajax-select2" data-target="{{ route('ajax.select2.fetch.users') }}" multiple>
                                        <option value="">{{ __trans('search_employee ...') }}</option>
                                        @foreach ($filterEmployees as $employee)
                                        <option value="{{ $employee->id }}" selected>{{ $employee->employee_id }}
                                            {{ $employee->name }}
                                        </option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                            <div class="col-lg-2">
                                <div class="form-group">
                                        <label><strong>{{ __trans('leave_type') }}:</strong></label>
                                        <select name="leave_type" class="select-search">
                                            <option value="">All</option>
                                            @foreach ($types as $type)
                                            <option value="{{$type->id}}">{{$type->name}}</option>
                                            @endforeach
                                        </select>
                                    </div>
                            </div>
                            <?php
                                $currentDate = new DateTime();
                                $endDate = new DateTime();
                                $endDate->add(new DateInterval('P1M'));

                                $currentDate->sub(new DateInterval('P30D'));

                                $last30thDate = $currentDate->format('Y-m-d');

                            ?>
                            <div class="col-lg-2">
                                <div class="form-group">
                                        <label><strong>{{ __trans('start_date') }}:</strong></label>
                                        <input type="text" name="start_date" value="{{$last30thDate}}" class="form-control datetime" placeholder="{{__trans('start_date')}}">
                                    </div>
                            </div>
                            <div class="col-lg-2">
                                <div class="form-group">
                                        <label><strong>{{ __trans('end_date') }}:</strong></label>
                                        <input type="text" name="end_date" value="{{$endDate->format('Y-m-d')}}" class="form-control datetime" placeholder="{{__trans('end_date')}}">
                                    </div>
                            </div>
                            <div class="col-lg-1 text-lg-end">
                                <button formaction="{{ route('backend.leave.report.generate') }}" class="btn btn-success">
                                    <i class="fa fa-download"></i> {{ __trans('export') }}
                                </button>
                            </div>
                        </div>
                    </form>
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
                                        <th>{{__trans('leave_type')}}</th>
                                        <th>{{__trans('start_date')}}</th>
                                        <th>{{__trans('end_date')}}</th>
                                        <th>
                                            <select id="status_filter" class="form-control">
                                                <option value="">All Status</option>
                                                <option value="pending">Pending</option>
                                                <option value="approved">Approved</option>
                                                <option value="rejected">Rejected</option>
                                            </select>
                                        </th>
                                        {{--  <th>{{__trans('status')}}</th>  --}}
                                        <th>{{__trans('created_by')}}</th>
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
            url: "{{ route('backend.leaves.index') }}",
            data: function (d) {
                d.leave_status_type = $('#status_filter').val();
            }
        },
        columns: [
            { data: 'DT_RowIndex', name: 'id' },
            { data: 'type.name' },
            { data: 'start_date', render: function(data) { return moment(data).format('DD-MM-YYYY'); }},
            { data: 'end_date', render: function(data) { return moment(data).format('DD-MM-YYYY'); }},
            { data: 'status', name: 'status' }, // Status column
            { data: 'user.name' },
            { data: 'action', orderable: false, searchable: false }
        ]
    });
    
    $(document).on('change', '#status_filter', function() {
        let selectedStatus = $(this).val();
        table.ajax.reload();
    });

    flatpickr("input.datetime", {
        //enableTime: true,
        // maxDate: today,
        dateFormat: "Y-m-d",
    });
</script>
<script>
loadAjaxSelect2();
</script>
@endpush
