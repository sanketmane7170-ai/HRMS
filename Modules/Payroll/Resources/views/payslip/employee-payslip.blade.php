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
            <form action="{{route('backend.payslip.user-payslip.store')}}" method="POST" class="ajax-form-submit reset">
                @csrf
                <div class="row align-items-center">
                    <div class="col">
                        <h3 class="page-title">{{ __trans('payslip') }}</h3>
                        <ul class="breadcrumb">
                            <li class="breadcrumb-item"><a
                                    href="{{ route('backend.dashboard') }}">{{ __trans('dashboard') }}</a>
                            </li>
                            <li class="breadcrumb-item active">{{__trans('pay_roll')}}</li>
                            <li class="breadcrumb-item active">{{ __trans('pay_slip') }}</li>
                        </ul>
                    </div>
            </form>
        </div>
        <form action="{{route('backend.payslip.user-payslip.index')}}" method="GET"
            id="select-month-dropdown select-year-dropdown" class="ajax-form-submit reset">
            @csrf
            <div class="row align-items-center">
                <div class="col">
                    <h5>{{ __trans('employee_payslip') }}</h5>
                </div>
                <div class="col-md-2">
                    <div class="form-group">
                        <label><strong>{{ __trans('select_month') }}:</strong></label>
                        <select name="month" class="form-control select-search" id="select-month">
                            @for ($i = 1; $i <= 12; $i++) <option value="{{ $i }}" @if ($month==$i) selected @endif>
                                {{ date('F', strtotime(date('Y') . '-' . $i)) }}</option>
                                @endfor
                        </select>
                    </div>
                </div>
                <div class="col-md-2">
                    <div class="form-group">
                        <label><strong>{{ __trans('select_year') }}:</strong></label>
                        <select name="year" class="form-control select-search" id="select-year">
                            @php
                            $myear = [2022,2023,2024,2025,2026,2027,2028];
                            @endphp
                            @for ($i = 0; $i <= 6; $i++) <option value="{{ $myear[$i]}}" @if ($year==$myear[$i])
                                selected @endif>
                                {{ $myear[$i] }}</option>
                                @endfor
                        </select>
                    </div>
                </div>
            </div>
        </form>
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
                                    <th>{{__trans('department')}}</th>

                                    @if (getSetting('payroll_calculation') == 'hourly')
                                    <th>{{__trans('basic_hourly_rate')}}</th>
                                    <th>{{__trans('net_salary(hourly)')}}</th>
                                    @else
                                    <th>{{__trans('basic_salary')}}</th>
                                    <th>{{__trans('net_salary(attendance)')}}</th>
                                    @endif
                                   
                                    <th>{{__trans('total_net_salary')}}</th>
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
<div class="modal" id="editModal"></div>
<!-- /Page Wrapper -->
@endsection
@push('scripts')
<script src="{{asset('assets/backend/plugins/flatpickr/flatpickr.min.js')}}"></script>
<script>
loadAjaxSelect2();
var table = $('#dataTable').DataTable({
    processing: true,
    serverSide: true,
    ajax: {
        url: "{{route('backend.my-salary.getViewPayslip')}}",
    },
    columns: [{
            data: 'id',
            name: 'id'
        },
        {
            data: 'name',
        },
        {
            data: 'department_name',
            name: 'department_name',
        },
        {
            data: 'basic_salary',
            name: 'basic_salary',
        },
        {
            data: 'net_salary',
            name: 'net_salary'
        },
        {
            data: 'total_net_salary',
            name: 'total_net_salary'
        },
        {
            data: 'status',
            name: 'status'
        },
        {
            data: 'action',
            orderable: false,
            searchable: false
        },
    ]
});

// Add an event listener for select changes
$('#select-month, #select-year').on('change', function(e) {
    e.preventDefault();

    // Get the selected month and year values
    var selectedMonth = $('#select-month').val();
    var selectedYear = $('#select-year').val();

    // Construct the URL based on the selected values
    const url = "{{ route('backend.payslip.export', ['month' => ':month', 'year' => ':year']) }}"
    .replace(':month', selectedMonth)
    .replace(':year', selectedYear);

    // Assign the URL to the href attribute of the link
    $('#download-link').attr('href', url);

    // Update the DataTable's AJAX URL with the new parameters
    table.ajax.url("{{ route('backend.my-salary.getViewPayslip') }}" + "?month=" + selectedMonth + "&year=" +
        selectedYear).load();
});

</script>
@endpush