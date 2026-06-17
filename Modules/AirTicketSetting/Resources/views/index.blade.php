@extends('layouts.backend')
@section('content')

<!-- Page Wrapper -->
<div class="page-wrapper">
    <div class="content container-fluid">
        <!-- Page Header -->
        <div class="page-header">
            <div class="row align-items-center">
                <div class="col">
                    <h3 class="page-title">{{__trans('Air_Ticket_Setting_List')}}</h3>
                    <ul class="breadcrumb">
                        <li class="breadcrumb-item"><a href="{{route('backend.dashboard')}}">{{__trans('dashboard')}}</a>
                        </li>
                        <li class="breadcrumb-item active">{{__trans('Air_Ticket_Setting_List')}}</li>
                    </ul>
                </div>
                <div class="col-auto">
                    <!-- @can('Create AirTicketSetting') -->
                    <a href="{{route('backend.settings.air-ticket-setting.create')}}" class="btn btn-primary me-1 edit-button">
                        <i class="fas fa-plus"></i>
                    </a>
                    <a href="{{route('backend.airTicketReport')}}" class="btn btn-primary me-1">
                        Air-Ticket Report
                    </a>
                    <!-- @endcan -->
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
                                        <th>{{__trans('policy_name')}}</th>
                                        <th>{{__trans('currency')}}</th>
                                        <th>{{__trans('allowance_amount')}}</th>
                                        <th>{{__trans('request_after_no.months')}}</th>
                                        <!-- <th>{{__trans('request_after_months_date')}}</th> -->
                                        <!-- <th>{{__trans('policy_renewal_months')}}</th>
                                        <th>{{__trans('request_limit_per_cycle')}}</th>
                                        <th>{{__trans('allow_reimbursement')}}</th>
                                        <th>{{__trans('allow_encashment')}}</th>
                                        <th>{{__trans('encashment_amount')}}</th>
                                        <th>{{__trans('allow_ticket_booking')}}</th> -->
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
<script src="https://cdn.jsdelivr.net/npm/sortablejs@latest/Sortable.min.js"></script>

<script type="text/javascript">
    var table = $('#dataTable').DataTable({
        processing: true,
        serverSide: true,
        order: [
            [0, 'desc']
        ],
        ajax: {
            url: "{{route('backend.settings.air-ticket-setting.index')}}",
        },
        columns: [{
                data: 'DT_RowIndex',
                name: 'id'
            },
            {
                data: 'policy_name',
                name: 'policy_name'
            },
            {
                data: 'allowance_currency',
                name: 'allowance_currency',
            },
            {
                data: 'allowance_amount',
                name: 'allowance_amount',
            },
            {
                data: 'request_after_months',
                name: 'request_after_months',
            },
            /*{
                data: 'request_after_months_date',
                name: 'request_after_months_date',
            },
            {
                data: 'policy_renewal_months',
                name: 'policy_renewal_months',
            },
            {
                data: 'request_limit_per_cycle',
                name: 'request_limit_per_cycle',
            },
            {
                data: 'allow_reimbursement',
                name: 'allow_reimbursement',
            },
            {
                data: 'allow_encashment',
                name: 'allow_encashment',
            },
            {
                data: 'allow_ticket_booking',
                name: 'allow_ticket_booking',
            },
            {
                data: 'encashment_amount',
                name: 'encashment_amount',
            },*/
            {
                data: 'status',
                name: 'status',
            },
            {
                data: 'action',
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