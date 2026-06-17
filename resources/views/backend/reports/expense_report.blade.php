@extends('layouts.backend')
@push('css')
<link rel="stylesheet" href="{{asset('assets/backend/plugins/flatpickr/flatpickr.min.css')}}">
@endpush
@section('content')

<!-- Page Wrapper -->
<div class="page-wrapper">
    <div class="content container-fluid">
        <!-- Page Header -->
        <div class="page-header">
            <div class="row align-items-center">
                <div class="col">
                    <h3 class="page-title">{{__trans('expense_report')}}</h3>
                    <ul class="breadcrumb">
                        <li class="breadcrumb-item"><a href="{{route('backend.reports')}}">{{__trans('Reports')}}</a>
                        </li>
                        <li class="breadcrumb-item active">{{__trans('expense_report')}}</li>
                    </ul>

                </div>

                {{-- <div class="col-auto">
                    <a href="{{route('backend.reports.expense_report_export')}}" class="btn btn-sm btn-success mt-2">
                <i class="fa fa-file-excel mr-2" style="display: inline"></i>Export
                </a>
            </div> --}}

        </div>

        <div class="row ">


            <div class="">
                <form action="{{route('backend.reports.expense_report_search')}}" enctype="multipart/form-data" method="POST">
                    @csrf

                    <div class="row">

                        <div class="col-sm-2">
                            <div class="form-group">
                                <!-- <label><strong>{{ __trans('select_month') }}:</strong></label> -->
                                <input placeholder="Select Month" type="text" class="form-control datepicker" name="month_year" value="{{ isset($month_year) ? $month_year : "" }}">
                            </div>
                        </div>



                        <div class="att-filter-box">
                            <div class="att-filter-box-inner">
                                <div class="form-group">
                                    <!-- <label><strong>{{ __trans('select_employee') }}:</strong></label> -->
                                    <select name="employee[]" class="form-control ajax-select2" data-target="{{ route('ajax.select2.fetch.userswithall') }}">

                                        <option value="">{{ __trans('search_employee ...') }}</option>
                                        @foreach ($filterEmployees as $employee)
                                        <option value="{{ $employee->id }}" selected>{{ $employee->user_id }}
                                            {{ $employee->name }}
                                        </option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                        </div>

                        <!-- <div class="att-filter-box">
                            <div class="att-filter-box-inner">
                                <div class="form-group">
                                    <select  name="department" class="form-control ajax-select2" data-target="{{ route('ajax.select2.fetch.departmentswithall') }}">
                                        <option value="">{{ __trans('search_department ...') }}</option>
                                        @if ($filterDepartment)
                                        <option value="{{ $filterDepartment->id }}" selected>
                                            {{ $filterDepartment->name }}
                                        </option>
                                        @endif
                                    </select>
                                </div>
                            </div>
                        </div> -->
                        <div class="col-sm-3">

                            <button type="submit" name="button" value="submit" id="searchLeave" class="btn btn-primary">
                                <i class="fa fa-search mr-2" style="display: inline"></i>Search
                            </button>
                            <button type="submit" name="button" value="export" id="export" class="btn btn-success">
                                <i class="fa fa-file-excel mr-2" style="display: inline"></i>Export
                            </button>
                        </div>



                    </div>



            </div>
            </form>

        </div>

    </div>



    <div class="row">
        <div class="col-sm-12">

            <div class="card_ card-table">
                <div class="card-body_">

                    <div class="att-wrapper1">
                        <div class="att-div1">
                        </div>
                    </div>

                    <div class="att-wrapper2">
                        <div class="att-div2">
                            <div class="table-responsive">
                                <table class="table table-bordered table-hover">
                                    <thead>
                                        <tr>


                                            <th class="text-center text-white" style="background-color: #042356">
                                                <strong>
                                                    <small><strong>{{__trans('user_id')}}</strong></small>
                                                </strong>
                                            </th>
                                            <th class="text-center text-white" style="background-color: #042356">
                                                <strong>
                                                    <small><strong>{{__trans('creator_name')}}</strong></small>
                                                </strong>
                                            </th>
                                            <th class="text-center text-white" style="background-color: #042356">
                                                <strong>
                                                    <small><strong>{{__trans('employee_name')}}</strong></small>
                                                </strong>
                                            </th>
                                            <th class="text-center text-white" style="background-color: #042356">
                                                <strong>
                                                    <small><strong>{{__trans('date')}}</strong></small>
                                                </strong>
                                            </th>
                                            <th class="text-center text-white" style="background-color: #042356">
                                                <strong>
                                                    <small><strong>{{__trans('expense_types')}}</strong></small>
                                                </strong>
                                            </th>
                                            <th class="text-center text-white" style="background-color: #042356">
                                                <strong>
                                                    <small><strong>{{__trans('name')}}</strong></small>
                                                </strong>
                                            </th>
                                            <th class="text-center text-white" style="background-color: #042356">
                                                <strong>
                                                    <small><strong>{{__trans('amount')}}</strong></small>
                                                </strong>
                                            </th>
                                            <th class="text-center text-white" style="background-color: #042356">
                                                <strong>
                                                    <small><strong>{{__trans('remark')}}</strong></small>
                                                </strong>
                                            </th>
                                            <th class="text-center text-white" style="background-color: #042356">
                                                <strong>
                                                    <small><strong>{{__trans('status')}}</strong></small>
                                                </strong>
                                            </th>

                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($expenses as $expense)
                                        <tr>

                                            <td>
                                                <small>{{ $expense->employee_id }}</small>
                                            </td>
                                            <td>
                                                <small>{{ $expense->creator_name }}</small>
                                            </td>
                                            <td>
                                                <small>{{ $expense->user_name }}</small>
                                            </td>
                                            <td>
                                                <small>{{ $expense->date }}</small>
                                            </td>
                                            <td>
                                                <small>{{ $expense->expense_types }}</small>
                                            </td>
                                            <td>
                                                <small>{{ $expense->name }}</small>
                                            </td>
                                            <td>
                                                <small>{{ $expense->amount }}</small>
                                            </td>
                                            <td>
                                                <small>{{ $expense->remark }}</small>
                                            </td>
                                            <td>
                                                <small>{{ $expense->status }}</small>
                                            </td>

                                        </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>


                </div>
            </div>
        </div>
    </div>
</div>
</div>

@endsection

@push('scripts')
<script src="{{asset('assets/backend/plugins/flatpickr/flatpickr.min.js')}}"></script>
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/plugins/monthSelect/style.css">
<script src="https://cdn.jsdelivr.net/npm/flatpickr/dist/plugins/monthSelect/index.js"></script>

<script>
    flatpickr("input.datepicker", {
        dateFormat: "Y-M",
        enableTime: false,
        plugins: [
            new monthSelectPlugin({ // Use the month selection plugin
                dateFormat: "Y-m", // Display format for the selected month
                // altFormat: "F Y", // Alternate format for display
                theme: "light" // Optional: choose a theme
            })
        ],
    });
</script>
<script>
    loadAjaxSelect2();
</script>
@endpush
