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
                    <h3 class="page-title">{{__trans('advance_salary_request')}}</h3>
                    <ul class="breadcrumb">
                        <li class="breadcrumb-item"><a href="{{route('backend.dashboard')}}">{{__trans('dashboard')}}</a>
                        </li>
                        <li class="breadcrumb-item active">{{__trans('advance_salary_request')}}</li>
                    </ul>
                </div>
                <div class="col-auto">
                    <a href="{{ route('backend.payroll.advance.createRequest', ['userid' => auth()->user()->id]) }}" class="btn btn-primary me-1 edit-button">
                        <i class="fas fa-plus"></i> {{ __trans('advance_salary_request') }}
                    </a>
                    <a href="{{ route('backend.payroll.advance.advanceRequestReport') }}" class="btn btn-info">
                        <i class="fas fa-file"></i> {{ __trans('advance_salary_report') }}
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
                                        <th>{{__trans('user')}}</th>
                                        <th>{{__trans('reference_number')}}</th>
                                        <th>{{__trans('type')}}</th>
                                        <th>{{__trans('reason')}}</th>
                                        <th>{{__trans('amount')}}</th>
                                        <th>{{__trans('instalments')}}</th>
                                        <th>{{__trans('start_month')}}</th>
                                        {{-- Sanket - Added new date columns for tracking request lifecycle --}}
                                        <th>Requested Date</th>  {{-- Sanket - Shows when user submitted the request --}}
                                        <th>{{__trans('status')}}</th>
                                        <th>Approved Date</th>   {{-- Sanket - Shows when admin approved the request, blank for rejected --}}
                                        <th>{{__trans('payment_mode')}}</th>
                                        <th>{{__trans('approved_amount')}}</th>
                                        <th>{{__trans('loan_months')}}</th>
                                        <th>{{__trans('installment_amount')}}</th>
                                        <th>{{__trans('installments_paid')}}</th>
                                        <th>{{__trans('installments_pending')}}</th>
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
<div id="editModal" class="modal"></div>

@endsection
@push('scripts')

<script type="text/javascript">
    var table = $('#dataTable').DataTable({
        processing: true,
        serverSide: true,
        ajax: {
            url: "{{route('backend.payroll.advance-request.index')}}",
        },
        columns: [{
                data: 'id',
                name: 'id'
            },
            { data: 'user_name', name: 'user_name' },
            {
                data: 'reference_number',
            },
            {
                data: 'type',
            },
            {
                data: 'reason',
            },
            {
                data: 'amount',
            },
            {
                data: 'instalments',
            },
            // Sanket - Updated start_month to use formatted date (DD/MM/YYYY) to match other date columns
            {
                data: 'formatted_start_month',     // Sanket - Display formatted start month (DD/MM/YYYY)
                name: 'start_month'
            },
            // Sanket - Added new date columns to DataTables configuration
            // These columns use formatted accessors from the model (DD/MM/YYYY format)
            {
                data: 'formatted_requested_date',  // Sanket - Display formatted requested date
                name: 'requested_date'
            },
            {
                data: 'status',
            },
            {
                data: 'formatted_approved_date',   // Sanket - Display approved date only (blank for rejected)
                name: 'approved_date'
            },
            {
                data: 'loan_mode',
            },
            {
                data: 'approved_amount',
            },
            {
                data: 'loan_months',
            },
            {
                data: 'installment_amount',
            },
            {
                data: 'installments_paid',
            },
            {
                data: 'installments_pending',
            },
           
            {
                data: 'approval',
                orderable: false,
                searchable: false
            },

        ]
    });
</script>
@endpush