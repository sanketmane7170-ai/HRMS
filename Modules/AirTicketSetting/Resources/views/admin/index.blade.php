@extends('layouts.backend')
@section('content')

<!-- Page Wrapper -->
<div class="page-wrapper">
    <div class="content container-fluid">
        <!-- Page Header -->
        <div class="page-header">
            <div class="row align-items-center">
                <div class="col">
                    <h3 class="page-title">{{__trans('Air_Ticket_Request_List')}}</h3>
                    <ul class="breadcrumb">
                        <li class="breadcrumb-item"><a href="{{route('backend.dashboard')}}">{{__trans('dashboard')}}</a>
                        </li>
                        <li class="breadcrumb-item active">{{__trans('request_ticket_list')}}</li>
                    </ul>
                </div>
                <div class="col-auto">
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
                                        <th>{{__trans('Employee')}}</th>
                                        <th>{{__trans('booking_type')}}</th>
                                        <th>{{__trans('journey_date')}}</th>
                                        <th>{{__trans('return_date')}}</th>
                                        <th>{{__trans('amount')}}</th>
                                        <th>{{__trans('payment_mode')}}</th>
                                        <th>{{__trans('approved_date')}}</th>
                                        <th>{{__trans('status')}}</th>
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
            url: "{{route('backend.air-ticket.request')}}",
        },
        order: [[0, 'desc']],
        columns: [{
                data: 'DT_RowIndex',
                name: 'id'
            },
            {
                data: 'employee',
            },
            {
                data: 'request_type',
            },
            {
                data: 'journey_date',
            },
            {
                data: 'return_date',
            },
            {
                data: 'requested_amount',
            },
            {
                data: 'payment_mode',
            },
            {
                data: 'approved_date',
            },
            {
                data: 'status',
            },
            {
                data: 'action',
                name: 'action',
                orderable: false,
                searchable: false
            },
        ]
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
