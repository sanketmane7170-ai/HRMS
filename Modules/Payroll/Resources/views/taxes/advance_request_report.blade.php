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
                    <h3 class="page-title">{{__trans('Advance Request Report')}}</h3>
                    <ul class="breadcrumb">
                        <li class="breadcrumb-item"><a href="{{route('backend.payroll.advance-request.index')}}">{{__trans('Advance Request')}}</a>
                        </li>
                        <li class="breadcrumb-item active">{{__trans('Advance Request Report')}}</li>
                    </ul>
                </div>
                <div class="att-filter-outer" style="justify-content:unset">
                    <div class="att-filter-box" style="padding: inherit;">
                        <div class="att-filter-box-inner">
                            <div class="form-group">
                                <label><strong>{{ __trans('start_date') }}:</strong></label>
                                <input type="date" name="start_date" id="start_date" class="form-control datepicker">
                            </div>
                        </div>
                    </div>
                    <div class="att-filter-box" style="padding: inherit;">
                        <div class="att-filter-box-inner">
                            <div class="form-group">
                                <label><strong>{{ __trans('end_date') }}:</strong></label>
                                <input type="date" name="end_date" id="end_date" class="form-control datepicker">
                            </div>
                        </div>
                    </div>
                    <div class="att-filter-box" style="padding: inherit;width: 30%;">
                        <div class="att-filter-box-inner">
                            <div class="form-group">
                                <label><strong>{{ __trans('employee_name') }}:</strong></label>
                                <select id="user_id" name="employee[]" class="form-control ajax-select2" data-target="{{ route('ajax.select2.fetch.users') }}" multiple>
                                    <option value="">{{ __trans('search_employee ...') }}</option>
                                </select>
                            </div>
                        </div>
                    </div>
                    <div class="att-filter-box" style="padding: inherit;">
                        <div class="att-filter-box-inner">
                            <div class="form-group">
                                <label>&nbsp; </label>
                                <button id="filter" class="btn btn-primary w-100">Filter</button>
                            </div>
                        </div>
                    </div>
                    <div class="att-filter-box" style="padding: inherit;width: 15%;">
                        <div class="att-filter-box-inner">
                            <div class="form-group">
                                <label>&nbsp; </label>
                                <a href="#" class="btn btn-danger btn-sm" id="exportPdf">Export PDF</a>
                            </div>
                        </div>
                    </div>
                    <div class="att-filter-box" style="padding: inherit;width: 12%;">
                        <div class="att-filter-box-inner">
                            <div class="form-group">
                                <label>&nbsp; </label>
                                <a href="#" class="btn btn-success btn-sm" id="exportExcel"> Export Excel</a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <!-- /Page Header -->
        <div class="row">
            <div class="col-sm-12">
                <div class="card card-table">
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table text-center table-hover request-datatable">
                                <thead class="thead-light">
                                    <tr>
                                        <th>#</th>
                                        <th>Reference ID</th>
                                        <th>Employee</th>
                                        <th>Action Date</th>
                                        <th>Total Amount</th>
                                        <th>Approved Amount</th>
                                        <th>Installment Amount</th>
                                        <th>Loan Duration (In Month)</th>
                                        <th>Installment</th>
                                        <th>Installment Paid</th>
                                        <th>Installment Pending</th>
                                        <th>Payment Mode</th>
                                        <th>Description</th>
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
<script src="{{asset('assets/backend/plugins/flatpickr/flatpickr.min.js')}}"></script>
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/plugins/monthSelect/style.css">
<script src="https://cdn.jsdelivr.net/npm/flatpickr/dist/plugins/monthSelect/index.js"></script>

<script type="text/javascript">

    var table = $('.request-datatable').DataTable({
        processing: true,
        serverSide: true,
        ajax: {
            url: "{{ route('backend.payroll.advance.advanceRequestReport') }}",
            data: function (d) {
                d.start_date = $('#start_date').val();
                d.end_date   = $('#end_date').val();
                d.user_id    = $('#user_id').val();
            }
        },
        columns: [
            { data: 'DT_RowIndex', name: 'DT_RowIndex', orderable: false,searchable: false },
            { data: 'advance_request_id', name: 'advanceRequest.reference_number' },
            { data: 'employee', name: 'user.name' },
            { data: 'action_date', name: 'action_date' },
            { data: 'total_amount', name: 'advanceRequest.amount' },
            { data: 'approved_amount', name: 'approved_amount' },
            { data: 'installment_amount', name: 'amount' },
            { data: 'loan_duration', name: 'advanceRequest.loan_months' },
            { data: 'total_installment', name: 'advanceRequest.instalments' },
            { data: 'installment_paid', name: 'installments_paid' },
            { data: 'installment_pending', name: 'installments_pending' },
            { data: 'payment_mode', name: 'advanceRequest.loan_mode' },
            { data: 'description', name: 'description' },
        ]
    });

    $('#filter').click(function () {
        table.draw();
    });

    flatpickr(".datepicker", {
        dateFormat: "d-m-Y",
    });
    loadAjaxSelect2();

    $('#exportPdf').on('click', function () {

        let start_date = $('#start_date').val();
        let end_date   = $('#end_date').val();
        let user_id    = $('#user_id').val();

        let url = "{{ route('backend.payroll.advance.request.report.pdf') }}";

        let params = $.param({
            start_date: start_date,
            end_date: end_date,
            user_id: user_id
        });

        window.location.href = url + '?' + params;
    });

    $('#exportExcel').on('click', function () {

        let start_date = $('#start_date').val();
        let end_date   = $('#end_date').val();
        let user_id    = $('#user_id').val();

        let url = "{{ route('backend.payroll.advance.request.report.excel') }}";

        let params = $.param({
            start_date: start_date,
            end_date: end_date,
            user_id: user_id
        });

        window.location.href = url + '?' + params;
    });

</script>
@endpush