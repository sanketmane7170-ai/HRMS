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
            <form action="#" method="POST" class="ajax-form-submit reset">
                @csrf
                <div class="row align-items-center">
                    <div class="col">
                        <h3 class="page-title">{{ __trans('employee_visit_history') }}</h3>
                        <ul class="breadcrumb">
                            <li class="breadcrumb-item"><a
                                    href="{{ route('backend.dashboard') }}">{{ __trans('dashboard') }}</a>
                            </li>
                            <li class="breadcrumb-item active">{{__trans('visit')}}</li>
                            <li class="breadcrumb-item active">{{ __trans('history') }}</li>
                        </ul>
                    </div>
            </form>
        </div>
        <form id="visitFilterForm">
            @csrf
            <div class="att-filter-outer">
                <div class="att-filter-box">
                    <div class="att-filter-box-inner">
                        <div class="form-group">
                            <label><strong>{{ __trans('employee_name') }}:</strong></label>
                            <select name="employee[]" class="form-control ajax-select2" data-target="{{ route('ajax.select2.fetch.userswithall') }}" multiple>
                                <option value="">{{ __trans('search_employee ...') }}</option>
                                @foreach ($filterEmployees as $employee)
                                <option value="{{ $employee->id }}" selected>{{ $employee->employee_id }}
                                    {{ $employee->name }}
                                </option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                </div>
                <div class="att-filter-box">
                    <div class="att-filter-box-inner">
                        <div class="form-group">
                            <label><strong>{{ __trans('department') }}:</strong></label>
                            {{--  <select name="department" class="form-control ajax-select2" data-target="{{ route('ajax.select2.fetch.departments') }}">  --}}
                            <select name="department" class="form-control">
                                <option value="0" >All</option>
                                @foreach($filterDepartment as $value)
                                    <option value="{{ $value->id }}" @if($value->id==$department) selected @endif>
                                        {{ $value->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                </div>
                <div class="att-filter-box">
                    <div class="att-filter-box-inner">
                        <div class="form-group">
                            <label><strong>{{ __trans('select_month') }}:</strong></label>
                            <select name="month" class="form-control select-search" id="selected_month_input">
                                @for ($i = 1; $i <= 12; $i++) <option value="{{ $i }}" @if ($month==$i) selected @endif>
                                    {{ date('F', strtotime(date('Y') . '-' . $i)) }}</option>
                                    @endfor
                            </select>
                        </div>
                    </div>
                </div>
                <div class="att-filter-box">
                    <div class="att-filter-box-inner">
                        <div class="form-group">
                            <label><strong>{{ __trans('select_year') }}:</strong></label>
                            <input type="text" name="year" value="{{ $year }}" id="selected_year_input" class="form-control" id="selected_year_input">
                        </div>
                    </div>
                </div>
                <div class="att-filter-box">
                    <div class="att-filter-box-inner">
                        <div class="form-group">
                            <label><strong>{{ __trans('select_start_date') }}:</strong></label>
                            <input type="text" name="start_date" value="{{ $startdate }}" id="startDate" class="form-control datepicker" placeholder="{{__trans('select_start_date')}}">
                        </div>
                    </div>
                </div>
                <div class="att-filter-box">
                    <div class="att-filter-box-inner">
                        <div class="form-group">
                            <label><strong>{{ __trans('select_end_date') }}:</strong></label>
                            <input type="text" name="end_date" value="{{ $enddate }}" id="endDate" class="form-control datepicker" placeholder="{{__trans('select_end_date')}}">
                        </div>
                    </div>
                </div>
                <div class="att-filter-box ">
                    <div class="form-group">
                        <label>&nbsp; </label>
                        <button id="applyFilter" class="btn btn-primary w-100">
                            {{ __trans('apply') }}
                        </button>
                    </div>
                    <div class="form-group">
                        <button type="submit" name="export" value="1" class="btn btn-success">
                            <i class="fa fa-download"></i> {{ __trans('export') }}
                        </button>
                        <button type="submit" name="export" value="2" class="btn btn-success">
                            <i class="fa fa-file-pdf"></i> {{ __trans('export-pdf') }}
                        </button>
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
                                    <th>{{__trans('date')}}</th>
                                    <th>{{__trans('location')}}</th>
                                    <th>{{__trans('visit_purpose')}}</th>
                                    <th>{{__trans('visit_start')}}</th>
                                    <th>{{__trans('visit_end')}}</th>
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
    flatpickr("input.datepicker", {
        dateFormat: "Y-m-d",
    });

    var table = $('#dataTable').DataTable({
        processing: true,
        serverSide: true,
        ajax: {
            url: "{{route('backend.showUserVisitReport')}}",
            data: function (d) {
                let formData = $('#visitFilterForm').serializeArray();
                formData.forEach(function (item) {
                    d[item.name] = item.value;
                });
            }
        },
        columns: [{
                data: 'id',
                name: 'id'
            },
            {
                data: 'date',
            },
            {
                data: 'location',
                name: 'location',
            },
            {
                data: 'visit_purpose',
                name: 'visit_purpose',
            },
            {
                data: 'visit_start',
                name: 'visit_start',
            },
            {
                data: 'visit_end',
                name: 'visit_end',
            },
        ]
    });

    $('#visitFilterForm').on('submit', function (e) {
        e.preventDefault(); // stop page reload
        table.ajax.reload();
    });

    $('#selected_month_input').on('change', function () {
        $('#startDate').val('');
        $('#endDate').val('');
    });
    
    $('#selected_year_input').on('click', function () {
        $('#startDate').val('');
        $('#endDate').val('');
    });

    $('button[id="applyFilter"]').on('click', function () {
        let exportType = $(this).val();

        let form = $('#visitFilterForm');
        form.attr('method', 'POST');
        form.attr('action', "{{ route('backend.showUserVisitReport') }}");

        $('<input>').attr({
            type: 'hidden',
            name: 'export',
            value: exportType
        }).appendTo(form);

        form.off('submit');
        form.submit();
    });

    $('button[name="export"]').on('click', function () {
        let exportType = $(this).val();

        let form = $('#visitFilterForm');
        form.attr('method', 'POST');
        form.attr('action', "{{ route('backend.exportVisitReport') }}");

        $('<input>').attr({
            type: 'hidden',
            name: 'export',
            value: exportType
        }).appendTo(form);

        form.off('submit');
        form.submit();
    });

</script>
@endpush
