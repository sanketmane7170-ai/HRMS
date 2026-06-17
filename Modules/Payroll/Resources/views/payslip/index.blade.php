@extends('layouts.backend')

@push('css')
<link rel="stylesheet" href="{{asset('assets/backend/plugins/flatpickr/flatpickr.min.css')}}">
@endpush
@section('content')
<!-- Page Wrapper -->
<div class="page-wrapper bg-white">
    <div class="content container-fluid">
        <!-- Page Header -->

        <div class="row ">
            <!-- Left Section -->
            <div class="col-md-2">
                <h3 class="page-title">{{ __trans('payslip') }}</h3>
                <ul class="breadcrumb">
                    <li class="breadcrumb-item">
                        <a class="light" href="{{ route('backend.dashboard') }}">{{ __trans('dashboard') }}</a>
                    </li>
                    <li class="breadcrumb-item active">{{ __trans('payslip') }}</li>
                </ul>
            </div>

            @if (getSetting('payroll_calculation') != 'hourly')
            <!-- Middle Section -->
            <div class="col-md-3 text-center">
                <div>
                    <h5 class="light">
                        {{ __trans('find_employee_payslip') }}
                        @if(getSetting('attendance_base_payroll')=='true')
                        <span class="badge badge-pill badge-success">
                            Attendance Base <i class="fa fa-check" aria-hidden="true"></i>
                        </span>
                        @else
                        <span class="badge badge-pill badge-primary ">
                            Attendance Base <i class="fa fa-times" aria-hidden="true"></i>
                        </span>
                        <a href="{{ route('backend.working_day_page') }}" class="btn btn-link">
                            (Assign Working Days)
                        </a>
                        @endif
                    </h5>
                </div>
            </div>
            @endif
            <!-- Right Section -->
            <div class="col-md-7 ">
                <div class="row">
                    <div class="col-auto">
                        <button type="button" class="btn btn-primary dark" data-bs-toggle="modal"
                            data-bs-target="#downloadAccrualReportModal">
                            Accrual Reports
                        </button>
                    </div>
                    <div class="col-auto">
                        <button type="button" class="btn btn-primary dark" data-bs-toggle="modal"
                            data-bs-target="#geneatePayrollModal">
                            Generate Payroll
                        </button>
                    </div>
                    <div class="col-auto">
                        <button type="button" class="btn btn-primary dark" data-bs-toggle="modal"
                            data-bs-target="#exportPayrollModal">
                            {{ __trans('export_&_close_payroll') }}
                        </button>
                    </div>
                    <div class="col-auto">
                        <button type="button" class="btn btn-primary dark">
                            <a href="{{ route('backend.payslip.showSalaryTransaction') }}" style="color: white;">
                                <i class="fa fa-list"></i> {{ __trans('role_adjustment_log') }}
                            </a>
                        </button>
                    </div>
                </div>
            </div>
        </div>
        <form action="{{route('backend.payslip.user-payslip.index')}}" method="GET"
            id="select-month-dropdown select-year-dropdown" class="ajax-form-submit reset">
            @csrf
            <div class="row">
                <div class="col-md-2">
                    <label for="company_document_id">Company</label>
                    <select name="company_document_id" class="form-select" id="select-company_document_id">
                        <option value="0">All</option>
                        @foreach($companyDocuments as $doc)
                        <option value="{{ $doc->id }}">{{ $doc->legal_trade_name }}</option>
                        @endforeach
                    </select>
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
            <div class="row align-items-center">
                @if(getSetting('show_basic_salary')==1 )
                <div class="col-xl-4 col-sm-6 col-12">
                    <div class="card top-stat-box top-stat-box-5">
                        <div class="card-body">
                            <div class="dash-widget-header">
                                <span class="dash-widget-icon">
                                    <!-- <i class="fas fa-money-bill-wave"></i> -->
                                    <h5>
                                        @if(str_contains(getSetting('currency'), 'AED'))
                                            <img src="{{ asset('assets/currency/aed.png') }}" alt="AED" style="width:18px; height:18px; vertical-align:middle;">
                                        @else
                                            {{ getSetting('currency') }}
                                        @endif
                                    </h5>
                                </span>
                                <div class="dash-count">
                                    <div class="dash-counts">
                                        <h5 id="totalBasicSalary"> </h5>
                                    </div>
                                    <div class="dash-title">
                                        @if (getSetting('payroll_calculation') == 'hourly')
                                        <p>Total Basic Hourly Rate</p>
                                        @else
                                        <p>Total Basic Salary</p>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                @endif
                @if(getSetting('show_gross_salary')==1 )
                <div class="col-xl-4 col-sm-6 col-12">
                    <div class="card top-stat-box top-stat-box-5">
                        <div class="card-body">
                            <div class="dash-widget-header">
                                <span class="dash-widget-icon">
                                    <!-- &#x62F;&#x2E;&#x625;; -->
                                    <h5> 
                                        @if(str_contains(getSetting('currency'), 'AED'))
                                            <img src="{{ asset('assets/currency/aed.png') }}" alt="AED" style="width:18px; height:18px; vertical-align:middle;">
                                        @else
                                            {{ getSetting('currency') }}
                                        @endif
                                    </h5>
                                </span>
                                <div class="dash-count">
                                    <div class="dash-counts">
                                        <h5 id="totalGrossSalary"></h5>
                                    </div>
                                    <div class="dash-title">
                                        @if (getSetting('payroll_calculation') == 'hourly')
                                        <p>Total Gross Hourly Rate</p>
                                        @else
                                        <p>Total Gross Salary</p>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                @endif
                @if(getSetting('show_net_salary')==1 )
                <div class="col-xl-4 col-sm-6 col-12">
                    <div class="card top-stat-box top-stat-box-5">
                        <div class="card-body">
                            <div class="dash-widget-header">
                                <span class="dash-widget-icon">
                                    <!-- <i class="fas fa-money-bill"></i> -->
                                    <h5> 
                                        @if(str_contains(getSetting('currency'), 'AED'))
                                            <img src="{{ asset('assets/currency/aed.png') }}" alt="AED" style="width:18px; height:18px; vertical-align:middle;">
                                        @else
                                            {{ getSetting('currency') }}
                                        @endif
                                    </h5>
                                </span>
                                <div class="dash-count">
                                    <div class="dash-counts">
                                        <h5 id="netSalaryTotal"></h5>
                                    </div>
                                    <div class="dash-title">
                                        <p></p>
                                        @if (getSetting('payroll_calculation') == 'hourly')
                                        <p>Net Salary(Hourly Rate)</p>
                                        @else
                                        <p>Net Salary(Attendance)</p>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                @endif
                @if(getSetting('show_total_net_salary')==1 )
                <div class="col-xl-4 col-sm-6 col-12">
                    <div class="card top-stat-box top-stat-box-5">
                        <div class="card-body">
                            <div class="dash-widget-header">
                                <span class="dash-widget-icon">
                                    <!-- <i class="fas fa-wallet"></i> -->
                                    <h5> 
                                        @if(str_contains(getSetting('currency'), 'AED'))
                                            <img src="{{ asset('assets/currency/aed.png') }}" alt="AED" style="width:18px; height:18px; vertical-align:middle;">
                                        @else
                                            {{ getSetting('currency') }}
                                        @endif
                                    </h5>
                                </span>
                                <div class="dash-count">
                                    <div class="dash-counts">
                                        <h5 id="totalNetSalary"></h5>
                                    </div>
                                    <div class="dash-title">
                                        <p>Total Net Salary</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                @endif
                @if(getSetting('show_total_allowance')==1 )
                <div class="col-xl-4 col-sm-6 col-12">
                    <div class="card top-stat-box top-stat-box-5">
                        <div class="card-body">
                            <div class="dash-widget-header">
                                <span class="dash-widget-icon">
                                    <!-- <i class="fas fa-hand-holding-usd"></i> -->
                                    <h5>
                                        @if(str_contains(getSetting('currency'), 'AED'))
                                            <img src="{{ asset('assets/currency/aed.png') }}" alt="AED" style="width:18px; height:18px; vertical-align:middle;">
                                        @else
                                            {{ getSetting('currency') }}
                                        @endif
                                    </h5>
                                </span>
                                <div class="dash-count">
                                    <div class="dash-counts">
                                        <h5 id="totalAllowance"></h5>
                                    </div>
                                    <div class="dash-title">
                                        <p>Total Allowance</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                @endif
                @if(getSetting('show_total_deduction')==1 )
                <div class="col-xl-4 col-sm-6 col-12">
                    <div class="card top-stat-box top-stat-box-5">
                        <div class="card-body">
                            <div class="dash-widget-header">
                                <span class="dash-widget-icon">
                                    <!-- <i class="fas fa-minus-circle"></i> -->
                                    <h5> 
                                        @if(str_contains(getSetting('currency'), 'AED'))
                                            <img src="{{ asset('assets/currency/aed.png') }}" alt="AED" style="width:18px; height:18px; vertical-align:middle;">
                                        @else
                                            {{ getSetting('currency') }}
                                        @endif
                                    </h5>
                                </span>
                                <div class="dash-count">
                                    <div class="dash-counts">
                                        <h5 id="totalDeduction"></h5>
                                    </div>
                                    <div class="dash-title">
                                        <p>Total Deduction</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                @endif
                @if(getSetting('show_total_expense')==1 )
                <div class="col-xl-4 col-sm-6 col-12">
                    <div class="card top-stat-box top-stat-box-5">
                        <div class="card-body">
                            <div class="dash-widget-header">
                                <span class="dash-widget-icon">
                                    <!-- <i class="fas fa-minus-circle"></i> -->
                                    <h5>
                                        @if(str_contains(getSetting('currency'), 'AED'))
                                            <img src="{{ asset('assets/currency/aed.png') }}" alt="AED" style="width:18px; height:18px; vertical-align:middle;">
                                        @else
                                            {{ getSetting('currency') }}
                                        @endif
                                    </h5>
                                </span>
                                <div class="dash-count">
                                    <div class="dash-counts">
                                        <h5 id="totalExpense"></h5>
                                    </div>
                                    <div class="dash-title">
                                        <p>Total Expense</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                @endif
                @if(getSetting('show_total_overtime_amount')==1 )
                <div class="col-xl-4 col-sm-6 col-12">
                    <div class="card top-stat-box top-stat-box-5">
                        <div class="card-body">
                            <div class="dash-widget-header">
                                <span class="dash-widget-icon">
                                    <!-- <i class="fas fa-minus-circle"></i> -->
                                    <h5>
                                        @if(str_contains(getSetting('currency'), 'AED'))
                                            <img src="{{ asset('assets/currency/aed.png') }}" alt="AED" style="width:18px; height:18px; vertical-align:middle;">
                                        @else
                                            {{ getSetting('currency') }}
                                        @endif
                                    </h5>
                                </span>
                                <div class="dash-count">
                                    <div class="dash-counts">
                                        <h5 id="total_overtime_amount"></h5>
                                    </div>
                                    <div class="dash-title">
                                        <p>Total overtime amount</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                @endif
                @if(getSetting('show_total_fixed_allowance')==1 )
                <div class="col-xl-4 col-sm-6 col-12">
                    <div class="card top-stat-box top-stat-box-5">
                        <div class="card-body">
                            <div class="dash-widget-header">
                                <span class="dash-widget-icon">
                                    <!-- <i class="fas fa-minus-circle"></i> -->
                                    <h5>
                                        @if(str_contains(getSetting('currency'), 'AED'))
                                            <img src="{{ asset('assets/currency/aed.png') }}" alt="AED" style="width:18px; height:18px; vertical-align:middle;">
                                        @else
                                            {{ getSetting('currency') }}
                                        @endif
                                    </h5>
                                </span>
                                <div class="dash-count">
                                    <div class="dash-counts">
                                        <h5 id="total_fixed_allowance"></h5>
                                    </div>
                                    <div class="dash-title">
                                        <p>Total fixed allowance</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                @endif
            </div>
        </form>
        <!-- <div class="row align-items-center">
            <div class="col">
                <h3 class="page-title">{{ __trans('payslip') }}</h3>
                <ul class="breadcrumb">
                    <li class="breadcrumb-item"><a
                            class="light" href="{{ route('backend.dashboard') }}">{{ __trans('dashboard') }}</a>
                    </li>
                    <li class="breadcrumb-item active">{{ __trans('payslip') }}</li>
                </ul>

            </div>
            <div class="col">
                {{ __trans('find_employee_payslip') }} @if(getSetting('attendance_base_payroll')=='true' ) <span
                    class="badge badge-pill badge-success">Attendance Base <i class="fa fa-check"
                        aria-hidden="true"></i> @else
                    <span class="badge badge-pill badge-primary">Attendance Base <i class="fa fa-times"
                            aria-hidden="true"></i> </span> <a href="{{ route('backend.working_day_page') }}"
                        class="btn btn-link">(Assign Working Days) </a> @endif
            </div>
            <div class="col">
                <div class="col-auto">
                    <button type="button" class="btn btn-primary" data-bs-toggle="modal"
                        data-bs-target="#downloadAccrualReportModal">
                        Accrual Reports
                    </button>
                </div>

                <div class="col-auto">
                    <button type="button" class="btn btn-primary" data-bs-toggle="modal"
                        data-bs-target="#geneatePayrollModal">
                        Genearte Payroll
                    </button>
                </div>

                <div class="col-auto">
                    <button type="button" class="btn btn-primary" data-bs-toggle="modal"
                        data-bs-target="#exportPayrollModal">
                        Export
                    </button>
                </div>

            </div>

        </div> -->
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
                                        <th>{{__trans('start_date')}}</th>
                                        <th>{{__trans('end_date')}}</th>
                                        @if (getSetting('payroll_calculation') == 'hourly')
                                        <th>{{__trans('total_working_hours')}}</th>
                                        <th>{{__trans('basic_hourly_rate')}}</th>
                                        <th>{{__trans('gross_hourly_rate')}}</th>
                                        <th>{{__trans('net_salary(hourly)')}}</th>
                                        <th>{{__trans('total_allowance')}}</th>
                                        <th>{{__trans('total_deduction')}}</th>
                                        <th>{{__trans('total_overtime')}}</th>
                                        <th>{{__trans('total_net_salary')}}</th>
                                        @else
                                        <th>{{__trans('total_working_days')}}</th>
                                        <th>{{__trans('basic_salary')}}</th>
                                        <th>{{__trans('gross_salary')}}</th>
                                        <th>{{__trans('net_salary(attendance)')}}</th>
                                        <th>{{__trans('total_allowance')}}</th>
                                        <th>{{__trans('total_deduction')}}</th>
                                        <th>{{__trans('total_overtime')}}</th>
                                        <th>{{__trans('total_net_salary')}}</th>
                                        @endif
                                        <th>{{__trans('status')}}</th>
                                        <th>{{__trans('actions')}}</th>
                                    </tr>
                                </thead>
                                <tfoot>
                                    <tr>
                                        <th>Total</th>
                                        <th></th>
                                        <th></th>
                                        <th></th>
                                        <th></th>
                                        <th></th>
                                        <th id="basic-salary-total"></th>
                                        <th></th>
                                        <th></th>
                                    </tr>
                                </tfoot>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="modal" id="editModal"></div>

<div class="modal fade" id="exportPayrollModal" tabindex="-1" aria-labelledby="exportPayrollModalLabel"
    aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title light" id="exportPayrollModalLabel">
                    {{__trans('Export')}}
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form action="{{ route('backend.payslip.user-payslip.index') }}" method="GET"
                    id="select-month-dropdown select-year-dropdown" class="ajax-form-submit reset">
                    @csrf

                    <div class="row mb-3">
                        <div class="col-md-4">
                            <label for="company_document_id">Company</label>
                            <select name="company_document_id" class="form-select" id="pay-select-company_document_id">
                                <option value="0">All</option>
                                @foreach($companyDocuments as $doc)
                                <option value="{{ $doc->id }}">{{ $doc->legal_trade_name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label><strong>{{ __trans('select_month') }}:</strong></label>
                                <select name="month" class="form-control select-search" id="pay-select-month">
                                    @for ($i = 1; $i <= 12; $i++) <option value="{{ $i }}" @if($month==$i) selected
                                        @endif>
                                        {{ date('F', strtotime(date('Y') . '-' . $i)) }}
                                        </option>
                                        @endfor
                                </select>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label><strong>{{ __trans('select_year') }}:</strong></label>
                                <select name="year" class="form-control select-search" id="pay-select-year">
                                    @php
                                    $myear = [2022, 2023, 2024, 2025, 2026, 2027, 2028];
                                    @endphp
                                    @foreach ($myear as $item)
                                    <option value="{{ $item }}" @if ($year==$item) selected @endif>
                                        {{ $item }}
                                    </option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                    </div>

                    <!-- Second Row: Buttons -->
                    <div class="row">
                        @if (isModuleEnabled('Payroll'))
                        <div class="col-12 d-flex flex-wrap justify-content-start gap-2">
                            @can('Generate SIF Payroll')
                            <a href="{{ route('backend.payslip.export', [$month, $year,$company]) }}" id="download-link"
                                class="btn btn-success flex-grow-1" method="GET"
                                data-alert="{{ __trans('are_you_sure_want_to_generate_payslip?') }}">
                                {{ __trans('export') }}
                            </a>
                            @endcan

                            @can('Export Payroll')
                            <a href="{{ route('backend.payslip.sif.export.xls', [$month, $year,$company]) }}" id="download-link-sif-xls"
                                class="btn btn-danger flex-grow-1" method="GET"
                                data-alert="{{ __trans('are_you_sure_want_to_generate_payslip?') }}">
                                SIF <i class="fa fa-download" aria-hidden="true"></i>
                            </a>
                            @endcan

                            <div class="col-md-3">
                                @if (isModuleEnabled('Payroll'))
                                @can('Export Payroll')
                                <a href="{{ route('backend.payslip.sif.export.sif', [$month, $year,$company]) }}"
                                    id="download-link-sif-sif" class="btn btn-danger waves-effect waves-light" method="GET"
                                    redirect data-alert="{{ __trans('are_you_sure_want_to_generate_payslip?') }}">
                                    SIF(sif) <i class="fa fa-download" aria-hidden="true"></i>
                                </a>
                                @endcan
                                @endif
                            </div>

                            @can('Export Payroll')
                            <button type="button" id="download-sif-btn" class="btn btn-danger flex-grow-1"
                                data-bs-toggle="modal" data-bs-target="#sifDocumentModal">
                                Company Wise SIF <i class="fa fa-download" aria-hidden="true"></i>
                            </button>
                            @endcan

                            @can('Set Salary and PayRoll Payroll')
                            <a href="#" id="close-link"
                                class="btn btn-warning flex-grow-1">
                                {{ __trans('close_payroll') }}
                            </a>
                            @endcan
                        </div>
                        @endif
                    </div>

                </form>
            </div>

        </div>
    </div>
</div>

<div class="modal fade" id="downloadAccrualReportModal" tabindex="-1" aria-labelledby="downloadAccrualReportModalLabel"
    aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title light" id="downloadAccrualReportModalLabel">Accrual
                    Report</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="row">

                    <div class="col-md-12">
                        <form id="gratuityReportForm" method="POST"
                            action="{{ route('backend.payslip.gratuity_report_download') }}">
                            @csrf
                            <div class="row align-items-end">
                                <!-- Date Picker -->
                                <input type="hidden" class="form-control" id="report_type" name="report_type" required
                                    value="gratuity_report">
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <input type="date" class="form-control" id="chosen_date" name="chosen_date"
                                            required value="{{ now()->format('Y-m-d') }}">
                                    </div>
                                </div>
                                <!-- Generate Report Button -->
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <button type="submit" class="btn btn-primary w-100">Gratuity
                                            Report</button>
                                    </div>
                                </div>
                            </div>

                        </form>
                    </div>

                </div>
                <div class="row">

                    <div class="col-md-6">
                        <form id="MedicalInsuranceReportForm" method="POST"
                            action="{{ route('backend.payslip.medical_insurance_report_download') }}">
                            @csrf
                            <input type="hidden" class="form-control" id="report_type" name="report_type" required
                                value="medical_insurance_report">
                            <button type="submit" class="btn btn-primary w-100">Medical
                                Insurance
                                Accrual</button>
                        </form>
                    </div>
                    <div class="col-md-6">
                        <form id="MedicalInsuranceReportForm" method="POST"
                            action="{{ route('backend.payslip.air_ticket_report_download') }}">
                            @csrf
                            <input type="hidden" class="form-control" id="report_type" name="report_type" required
                                value="air_ticket_report">
                            <button type="submit" class="btn btn-primary w-100">Air ticket
                                Accrual</button>
                        </form>
                    </div>

                </div>
                </br>

                <div class="row">
                    @if(getSetting('leave_salary')=='yes' )
                    <div class="col-md-6">
                        <form id="LeaveSalaryReportForm" method="POST"
                            action="{{ route('backend.payslip.leave_salary_report_download') }}">
                            @csrf
                            <input type="hidden" class="form-control" id="report_type" name="report_type" required
                                value="medical_insurance_report">
                            <input type="hidden" id="report_type" name="report_type" value="leave_salary_report">
                            <button type="submit" class="btn btn-primary w-100">Leave Salary
                                Accrual Report</button>
                        </form>
                    </div>
                    @endif

                </div>
            </div>
        </div>
    </div>
</div>
<div class="modal fade" id="geneatePayrollModal" tabindex="-1" aria-labelledby="geneatePayrollModalLabel"
    aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title light" id="geneatePayrollModalLabel">Generate Payroll
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form action="{{route('backend.payslip.user-payslip.store')}}" method="POST" class="ajax-form-submit">
                    @csrf

                    <!-- First Row: Date Selection -->
                    <div class="row mb-3">

                        <div class="col-md-6">
                            <label for="company_document_id">Company</label>
                            <select name="company_document_id" id="store_company_id"class="form-select">
                                <option value="0">All</option>
                                @foreach($companyDocuments as $doc)
                                <option value="{{ $doc->id }}">{{ $doc->legal_trade_name }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="col-md-6">
                            <div class="form-group">
                                <label><strong>{{ __trans('select_month') }}:</strong></label>
                                <select name="month" class="form-control select-search" id="payroll_month">
                                    @for ($i = 1; $i <= 12; $i++) <option value="{{ $i }}" @if($month==$i) selected
                                        @endif>
                                        {{ date('F', strtotime(date('Y') . '-' . $i)) }}
                                        </option>
                                        @endfor
                                </select>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label><strong>{{ __trans('select_year') }}:</strong></label>
                                <select name="year" class="form-control select-search">
                                    @php
                                    $myear = [2022,2023,2024,2025,2026,2027,2028];
                                    @endphp
                                    @foreach ($myear as $item)
                                    <option value="{{ $item }}" @if ($year==$item) selected @endif>
                                        {{ $item }}
                                    </option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label><strong>{{ __trans('start_date') }}:</strong></label>

                                <input type="text" name="start_date" class=" form-control flatpickr" placeholder="Select start date">
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="form-group">
                                <label><strong>{{ __trans('end_date') }}:</strong></label>

                                <input type="text" name="end_date" class=" form-control flatpickr" placeholder="Select start date">
                            </div>
                        </div>
                    </div>

                    <!-- Second Row: Submit Button -->
                    <div class="row">
                        <div class="col-12 text-center">
                            @if(isModuleEnabled('Payroll'))
                            @can('Set Salary and PayRoll Payroll')
                            <button type="submit" class="btn btn-success waves-effect waves-light">
                                {{__trans('generate_payroll')}}
                            </button>
                            @if(auth()->user()->hasRole('admin') || auth()->user()->hasRole('superadmin'))
                            <button type="button" id="delete_payroll_btn" onclick="deletePayroll()" style="display: none" class="btn btn-danger waves-effect waves-light ms-2">
                                {{ __trans('delete_payroll') }}
                            </button>
                            @endif
                            @endcan
                            @endif
                        </div>
                    </div>
                </form>
            </div>

        </div>
    </div>
</div>

<!-- SIF Document Modal -->
<!-- <div class="modal fade" id="sifDocumentModal" tabindex="-1" aria-labelledby="sifDocumentModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="sifDocumentModalLabel">Select Company Document</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="form-group">
                    <label for="company_document_id">Company Document</label>
                    <select class="form-select" id="company_document_id">
                        @foreach($companyDocuments as $doc)
                        <option value="{{ $doc->id }}">{{ $doc->legal_trade_name }}</option>
                        @endforeach
                    </select>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" id="download-sif-final" class="btn btn-primary">Download SIF</button>
            </div>
        </div>
    </div>
</div> -->

<!-- SIF Document Modal -->
<!-- <div class="modal fade" id="sifDocumentModal" tabindex="-1" aria-labelledby="sifDocumentModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="sifDocumentModalLabel">Select Company Document & Columns</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="form-group">
                    <label for="company_document_id">Company Document</label>
                    <select class="form-select" id="company_document_id">
                        @foreach($companyDocuments as $doc)
                        <option value="{{ $doc->id }}">{{ $doc->legal_trade_name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="form-group mt-3">
                    <label>Select Columns to Export:</label>
                     <div class="form-check">
                        <input class="form-check-input" type="checkbox" value="record_type" id="col_record_type" checked>
                        <label class="form-check-label" for="col_record_type">{{__trans('record_type')}}</label>
                    </div>
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" value="labour_id" id="col_labour_id" checked>
                        <label class="form-check-label" for="col_labour_id">{{__trans('labour_id')}}</label>
                    </div>
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" value="emp_name" id="col_emp_name" checked>
                        <label class="form-check-label" for="col_emp_name">{{__trans('emp_name')}}</label>
                    </div>
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" value="routing_number" id="col_routing_number" checked>
                        <label class="form-check-label" for="col_routing_number">{{__trans('routing_number')}}</label>
                    </div>
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" value="iban_number" id="col_iban_number" checked>
                        <label class="form-check-label" for="col_iban_number">{{__trans('iban_number')}}</label>
                    </div>
                     <div class="form-check">
                        <input class="form-check-input" type="checkbox" value="pay_start_date" id="col_pay_start_date" checked>
                        <label class="form-check-label" for="col_pay_start_date">{{__trans('pay_start_date')}}</label>
                    </div>
                     <div class="form-check">
                        <input class="form-check-input" type="checkbox" value="pay_end_date" id="col_pay_end_date" checked>
                        <label class="form-check-label" for="col_pay_end_date">{{__trans('pay_end_date')}}</label>
                    </div>
                     <div class="form-check">
                        <input class="form-check-input" type="checkbox" value="number_of_days" id="col_number_of_days" checked>
                        <label class="form-check-label" for="col_number_of_days">{{__trans('number_of_days')}}</label>
                    </div>
                     
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" value="fixed_income_amount" id="col_fixed_income_amount" checked>
                        <label class="form-check-label" for="col_fixed_income_amount">{{__trans('fixed_income_amount')}}</label>
                    </div>
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" value="variable_income_amount" id="col_variable_income_amount" checked>
                        <label class="form-check-label" for="col_variable_income_amount">{{__trans('variable_income_amount')}}</label>
                    </div>
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" value="days_on_leave" id="col_days_on_leave" checked>
                        <label class="form-check-label" for="col_days_on_leave">{{__trans('days_on_leave')}}</label>
                    </div>
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" value="variable_income_amount" id="col_variable_income_amount" checked>
                        <label class="form-check-label" for="col_variable_income_amount">{{__trans('variable_income_amount')}}</label>
                    </div>
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" value="housing_allowance" id="col_housing_allowance" checked>
                        <label class="form-check-label" for="col_housing_allowance">{{__trans('housing_allowance')}}</label>
                    </div>
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" value="variable_income_amount" id="col_variable_income_amount" checked>
                        <label class="form-check-label" for="col_variable_income_amount">{{__trans('variable_income_amount')}}</label>
                    </div>
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" value="conveyance_allowance" id="col_conveyance_allowance" checked>
                        <label class="form-check-label" for="col_conveyance_allowance">{{__trans('conveyance_allowance')}}</label>
                    </div>
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" value="variable_income_amount" id="col_variable_income_amount" checked>
                        <label class="form-check-label" for="col_variable_income_amount">{{__trans('variable_income_amount')}}</label>
                    </div>
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" value="medical_allowance" id="col_medical_allowance" checked>
                        <label class="form-check-label" for="col_medical_allowance">{{__trans('medical_allowance')}}</label>
                    </div>
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" value="annual_passage_allowance" id="col_annual_passage_allowance" checked>
                        <label class="form-check-label" for="col_annual_passage_allowance">{{__trans('annual_passage_allowance')}}</label>
                    </div>
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" value="overtime_allowance" id="col_overtime_allowance" checked>
                        <label class="form-check-label" for="col_overtime_allowance">{{__trans('overtime_allowance')}}</label>
                    </div>
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" value="other_allowance" id="col_other_allowance" checked>
                        <label class="form-check-label" for="col_other_allowance">{{__trans('other_allowance')}}</label>
                    </div>
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" value="leave_encashment" id="col_leave_encashment" checked>
                        <label class="form-check-label" for="col_leave_encashment">{{__trans('leave_encashment')}}</label>
                    </div>
                   
                   
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" id="download-sif-final" class="btn btn-primary">Download SIF</button>
            </div>
        </div>
    </div>
</div> -->
<!-- <div class="modal fade" id="sifDocumentModal" tabindex="-1" aria-labelledby="sifDocumentModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="sifDocumentModalLabel">Select Company Document & Columns</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="form-group">
                    <label for="company_document_id">Company Document</label>
                    <select class="form-select" id="company_document_id">
                        @foreach($companyDocuments as $doc)
                        <option value="{{ $doc->id }}">{{ $doc->legal_trade_name }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="form-group mt-3">
                    <label>Select Columns to Export (with Priority):</label>

                    @php
                    $columns = [
                    'record_type',
                    'labour_id',
                    'emp_name',
                    'routing_number',
                    'iban_number',
                    'pay_start_date',
                    'pay_end_date',
                    'number_of_days',
                    'fixed_income_amount',
                    'variable_income_amount',
                    'days_on_leave',
                    'housing_allowance',
                    'conveyance_allowance',
                    'medical_allowance',
                    'annual_passage_allowance',
                    'overtime_allowance',
                    'other_allowance',
                    'leave_encashment'
                    ];
                    $priority = 1;
                    @endphp

                    @foreach($columns as $col)
                    <div class="form-check d-flex align-items-center mb-2">
                        <input class="form-check-input me-2" type="checkbox" value="{{ $col }}" id="col_{{ $col }}"
                            checked>
                        <label class="form-check-label me-2" for="col_{{ $col }}">{{ __trans($col) }}</label>
                        <input class="form-control form-control-sm w-auto priority-input" type="number" min="1"
                            value="{{ $priority++ }}" id="priority_{{ $col }}" name="priority_{{ $col }}"
                            style="width:60px;">
                    </div>
                    @endforeach

                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" id="download-sif-final" class="btn btn-primary">Download SIF</button>
            </div>
        </div>
    </div>
</div> -->

<!-- <div class="modal fade" id="sifDocumentModal" tabindex="-1" aria-labelledby="sifDocumentModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg"> 
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="sifDocumentModalLabel">Select Company Document & Columns</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">

                <div class="form-group mb-3">
                    <label for="company_document_id">Company Document</label>
                    <select class="form-select" id="company_document_id">
                        @foreach($companyDocuments as $doc)
                        <option value="{{ $doc->id }}">{{ $doc->legal_trade_name }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="form-group mb-4">
                    <label class="fw-bold">Data Columns to Export (with Priority):</label>
                    <div class="row">
                        @php
                        $columns = [
                        'record_type',
                        'labour_id',
                        'emp_name',
                        'routing_number',
                        'iban_number',
                        'pay_start_date',
                        'pay_end_date',
                        'number_of_days',
                        'fixed_income_amount',
                        'variable_income_amount',
                        'days_on_leave',
                        'housing_allowance',
                        'conveyance_allowance',
                        'medical_allowance',
                        'annual_passage_allowance',
                        'overtime_allowance',
                        'other_allowance',
                        'leave_encashment'
                        ];
                        $priority = 1;
                        @endphp

                        @foreach($columns as $col)
                        <div class="col-md-6 mb-2 d-flex align-items-center">
                            <input class="form-check-input me-2" type="checkbox" value="{{ $col }}" id="col_{{ $col }}" checked>
                            <label class="form-check-label me-2" for="col_{{ $col }}">{{ __trans($col) }}</label>
                            <input class="form-control form-control-sm w-auto priority-input" type="number" min="1"
                                value="{{ $priority++ }}" id="priority_{{ $col }}" name="priority_{{ $col }}" style="width:60px;">
                        </div>
                        @endforeach
                    </div>
                </div>

                <div class="form-group">
                    <label class="fw-bold">SCR Summary Columns (with Priority):</label>
                    <div class="row">
                        @php
                        $scrColumns = [
                        'record_type',
                        'mol_company_number',
                        'routing_bank_code',
                        'file_creation_date',
                        'file_creation_time',
                        'salary_month',
                        'edr_count',
                        'total_salary',
                        'payment_currency',
                        'employer_reference'
                        ];
                        $scrPriority = 1;
                        @endphp

                        @foreach($scrColumns as $col)
                        <div class="col-md-6 mb-2 d-flex align-items-center">
                            <input class="form-check-input me-2" type="checkbox" value="{{ $col }}" id="scr_col_{{ $col }}" checked>
                            <label class="form-check-label me-2" for="scr_col_{{ $col }}">{{ ucwords(str_replace('_', ' ', $col)) }}</label>
                            <input class="form-control form-control-sm w-auto priority-input" type="number" min="1"
                                value="{{ $scrPriority++ }}" id="scr_priority_{{ $col }}" name="scr_priority_{{ $col }}" style="width:60px;">
                        </div>
                        @endforeach
                    </div>
                </div>

            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" id="download-sif-final" class="btn btn-primary">Download SIF</button>
            </div>
        </div>
    </div>
</div> -->
<div class="modal fade" id="sifDocumentModal" tabindex="-1" aria-labelledby="sifDocumentModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg"> <!-- modal-lg for large size -->
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="sifDocumentModalLabel">Select Company Document & Columns</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">

                <!-- Company Document Selection -->
                <div class="form-group mb-3">
                    <label for="company_document_id">Company Document</label>
                    <select class="form-select" id="company_document_id">
                        @foreach($companyDocuments as $doc)
                        <option value="{{ $doc->id }}">{{ $doc->legal_trade_name }}</option>
                        @endforeach
                    </select>
                </div>

                <!-- Data Columns -->
                <div class="form-group mb-4">
                    <label class="fw-bold">Data Columns to Export (with Priority):</label>
                    <div class="form-check mb-2">
                        <input class="form-check-input" type="checkbox" id="show_data_headers" checked>
                        <label class="form-check-label" for="show_data_headers">
                            Show Data Headers
                        </label>
                    </div>
                    <div class="row">
                        @php
                            $columns = [
                                'no_value',
                                'record_type',
                                'labour_id',
                                'emp_name',
                                'routing_number',
                                'account_number',
                                'iban_number',
                                'pay_start_date',
                                'pay_end_date',
                                'number_of_days',
                                'fixed_income_amount',
                                'variable_income_amount',
                                'days_on_leave',
                                'housing_allowance',
                                'conveyance_allowance',
                                'medical_allowance',
                                'annual_passage_allowance',
                                'overtime_allowance',
                                'other_allowance',
                                'leave_encashment',
                                'employee_code',
                                'total_salary',
                                'gross_salary',
                                'total_deduction',
                            ];
                            $priority = 1;
                        @endphp
                        <div class="container mt-2">
                            <div id="dynamic_rows">
                                <!-- First Row -->
                                <div class="row mb-2 dynamic-row">
                                    <div class="col-md-4">
                                        <select class="form-select column-select">
                                            <option value="">Select Field</option>
                                            @foreach($columns as $col)
                                            <option value="{{ $col }}">{{ __trans($col) }}</option>
                                            @endforeach
                                        </select>
                                    </div>

                                    <div class="col-md-4">
                                        <input type="text" class="form-control custom-name" placeholder="Enter Name">
                                    </div>

                                    <div class="col-md-2">
                                        <input type="number" min="1" class="form-control custom-index" placeholder="Index">
                                    </div>

                                    <div class="col-md-2 d-flex">
                                        <button class="btn btn-success add-row w-100 me-1">+</button>
                                        <button class="btn btn-danger remove-row w-100">X</button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- SCR Summary Columns -->
                <div class="form-group">
                    <label class="fw-bold">SCR Summary Columns (with Priority):</label>
                    <div class="form-check mb-2">
                        <input class="form-check-input" type="checkbox" id="show_scr_headers" checked>
                        <label class="form-check-label" for="show_scr_headers">
                            Show SCR Headers
                        </label>
                    </div>
                    <div class="row">
                        @php
                            $scrColumns = [
                                'no_value',
                                'record_type',
                                'mol_company_number',
                                'routing_bank_code',
                                'file_creation_date',
                                'file_creation_time',
                                'salary_month',
                                'edr_count',
                                'total_salary',
                                'payment_currency',
                                'employer_reference'
                            ];
                        @endphp

                        <div class="container mt-2">
                            <h5>SCR Fields</h5>

                            <div id="scr_dynamic_rows">
                                <!-- First Row -->
                                <div class="row mb-2 scr-row">
                                    <div class="col-md-4">
                                        <select class="form-select scr-select">
                                            <option value="">Select SCR Field</option>
                                            @foreach($scrColumns as $col)
                                            <option value="{{ $col }}">{{ ucwords(str_replace('_', ' ', $col)) }}</option>
                                            @endforeach
                                        </select>
                                    </div>

                                    <div class="col-md-4">
                                        <input type="text" class="form-control scr-name" placeholder="Enter Name">
                                    </div>

                                    <div class="col-md-2">
                                        <input type="number" min="1" class="form-control scr-index" placeholder="Index">
                                    </div>

                                    <div class="col-md-2 d-flex">
                                        <button class="btn btn-success scr-add w-100 me-1">+</button>
                                        <button class="btn btn-danger scr-remove w-100">X</button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" id="download-sif-final" class="btn btn-primary">Download SIF</button>
            </div>
        </div>
    </div>
</div>





</div>
<!-- /Page Wrapper -->
@endsection
@push('scripts')
<script src="{{asset('assets/backend/plugins/flatpickr/flatpickr.min.js')}}"></script>
<script>
    let allColumns = @json($columns);
    let scrAllColumns = @json($scrColumns);

    loadAjaxSelect2();
    var table = $('#dataTable').DataTable({
        processing: true,
        serverSide: true,
        ajax: {
            url: "{{route('backend.payslip.user-payslip.index')}}",
            dataSrc: function(json) {
                // Update the total net salary display when data is loaded
                $('#totalNetSalary').text(json.totalNetSalary.toFixed(2));
                $('#totalBasicSalary').text(json.totalBasicSalary.toFixed(2));
                $('#totalGrossSalary').text(json.totalGrossSalary.toFixed(2));
                $('#netSalaryTotal').text(json.netSalaryTotal.toFixed(2));
                $('#totalAllowance').text(json.totalAllowance.toFixed(2));
                $('#totalDeduction').text(json.totalDeduction.toFixed(2));
                $('#totalExpense').text(json.totalExpense.toFixed(2));
                $('#total_overtime_amount').text(json.total_overtime_amount.toFixed(2));
                $('#total_fixed_allowance').text(json.total_fixed_allowance.toFixed(2));
                return json.data;
            }
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
                data: 'start_date',
            },
            {
                data: 'end_date',
            },
            {
                data: 'total_working_days',
                name: 'total_working_days',
            },
            {
                data: 'basic_salary',
                name: 'basic_salary',
            },
            {
                data: 'gross_salary',
                name: 'gross_salary',
            },
            {
                data: 'net_salary',
                name: 'net_salary'
            },
            {
                data: 'total_allowance',
                name: 'total_allowance'
            },
            {
                data: 'total_deduction',
                name: 'total_deduction'
            },
            {
                data: 'total_overtime',
                name: 'total_overtime'
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
        ],
        footerCallback: function(row, data, start, end, display) {
            var api = this.api();

            // Remove the formatting to get integer data for summation
            var intVal = function(i) {
                return typeof i === 'string' ?
                    i.replace(/[\$,]/g, '') * 1 :
                    typeof i === 'number' ?
                    i : 0;
            };

            // Calculate the sum of the basic salary column
            var basicSalaryTotal = api
                .column(4, {
                    page: 'current'
                })
                .data()
                .reduce(function(a, b) {
                    return intVal(a) + intVal(b);
                }, 0);

            // Calculate the sum of the gross salary column
            var grossSalaryTotal = api
                .column(5, {
                    page: 'current'
                })
                .data()
                .reduce(function(a, b) {
                    return intVal(a) + intVal(b);
                }, 0);

            // Calculate the sum of the net salary column
            var netSalaryTotal = api
                .column(6, {
                    page: 'current'
                })
                .data()
                .reduce(function(a, b) {
                    return intVal(a) + intVal(b);
                }, 0);

            // Calculate the sum of the net salary column
            var totalnetSalaryTotal = api
                .column(7, {
                    page: 'current'
                })
                .data()
                .reduce(function(a, b) {
                    return intVal(a) + intVal(b);
                }, 0);

            // Set the calculated totals in the last row of the table
            $(api.column(4).footer()).html(parseFloat(basicSalaryTotal).toFixed(2));
            $(api.column(5).footer()).html(parseFloat(grossSalaryTotal).toFixed(2));
            $(api.column(6).footer()).html(parseFloat(netSalaryTotal).toFixed(2));
            $(api.column(7).footer()).html(parseFloat(totalnetSalaryTotal).toFixed(2));
        }
    });

    // Add an event listener for select changes
    $('#select-month, #select-year,#select-company_document_id').on('change', function(e) {
        e.preventDefault();

        // Get the selected month and year values
        var selectedMonth = $('#select-month').val();
        var selectedYear = $('#select-year').val();
        var selectedCompanyDocumentId = $('#select-company_document_id').val();

        // Construct the URL based on the selected values
        const url1 = "{{ route('backend.payslip.export', ['month' => ':month', 'year' => ':year', 'company' => ':company_document_id']) }}"
            .replace(':month', selectedMonth)
            .replace(':year', selectedYear)
            .replace(':company_document_id', selectedCompanyDocumentId);

        const url2 = "{{ route('backend.payslip.sif.export.xls', ['month' => ':month', 'year' => ':year', 'company' => ':company_document_id']) }}"
            .replace(':month', selectedMonth)
            .replace(':year', selectedYear)
            .replace(':company_document_id', selectedCompanyDocumentId);

        const url3 = "{{ route('backend.payslip.close.report', ['month' => ':month', 'year' => ':year', 'company' => ':company_document_id']) }}"
            .replace(':month', selectedMonth)
            .replace(':year', selectedYear)
            .replace(':company_document_id', selectedCompanyDocumentId);
        changeURL(url3);
        const url4 = "{{ route('backend.payslip.sif.export.sif', ['month' => ':month', 'year' => ':year', 'company' => ':company_document_id']) }}"
            .replace(':month', selectedMonth)
            .replace(':year', selectedYear)
            .replace(':company_document_id', selectedCompanyDocumentId);

        // Assign the URL to the href attribute of the link
        $('#download-link').attr('href', url1);
        $('#download-link-sif-xls').attr('href', url2);
        {{--  $('#close-link').attr('href', url3);  --}}
        $('#download-link-sif-sif').attr('href', url4);

        // Update the DataTable's AJAX URL with the new parameters
        table.ajax.url("{{ route('backend.payslip.user-payslip.index') }}" + "?month=" + selectedMonth + "&year=" +
            selectedYear + "&company_document_id=" +
            selectedCompanyDocumentId).load();
    });

    $('#pay-select-month, #pay-select-year,#pay-select-company_document_id').on('change', function(e) {
        e.preventDefault();

        // Get the selected month and year values
        var selectedMonth = $('#pay-select-month').val();
        var selectedYear = $('#pay-select-year').val();
        var selectedCompanyDocumentId = $('#pay-select-company_document_id').val();
        console.log(selectedMonth);
        // Construct the URL based on the selected values
        const url1 = "{{ route('backend.payslip.export', ['month' => ':month', 'year' => ':year', 'company' => ':company']) }}"
            .replace(':month', selectedMonth)
            .replace(':year', selectedYear)
            .replace(':company', selectedCompanyDocumentId);
        console.log(url1);
        const url2 = "{{ route('backend.payslip.sif.export.xls', ['month' => ':month', 'year' => ':year', 'company' => ':company']) }}"
            .replace(':month', selectedMonth)
            .replace(':year', selectedYear)
            .replace(':company', selectedCompanyDocumentId);
        console.log(url2);
        const url3 = "{{ route('backend.payslip.close.report', ['month' => ':month', 'year' => ':year', 'company' => ':company']) }}"
            .replace(':month', selectedMonth)
            .replace(':year', selectedYear)
            .replace(':company', selectedCompanyDocumentId);
        changeURL(url3);
        const url4 = "{{ route('backend.payslip.sif.export.sif', ['month' => ':month', 'year' => ':year', 'company' => ':company']) }}"
            .replace(':month', selectedMonth)
            .replace(':year', selectedYear)
            .replace(':company', selectedCompanyDocumentId);
        // Assign the URL to the href attribute of the link
        $('#download-link').attr('href', url1);
        $('#download-link-sif-xls').attr('href', url2);
        {{--  $('#close-link').attr('href', url3);  --}}
        $('#download-link-sif-sif').attr('href', url4);

    });
</script>
<!-- <script>
document.getElementById('download-sif-final').addEventListener('click', function() {
    const selectedDocId = document.getElementById('company_document_id').value;
    if (!selectedDocId) {
        alert('Please select a company document.');
        return;
    }

    // Use Blade's route helper with placeholder values, and then replace them in JavaScript

    var selectedMonth = $('#pay-select-month').val();
    var selectedYear = $('#pay-select-year').val();

    // Replace placeholders with actual month and year
    downloadUrl = downloadUrl
        .replace('__MONTH__', selectedMonth)
        .replace('__YEAR__', selectedYear);

    // Append query parameter
    downloadUrl += '?company_document_id=' + selectedDocId;

    window.location.href = downloadUrl;
});
</script> -->
{{--  <!-- <script>
document.getElementById('download-sif-final').addEventListener('click', function() {
    const selectedDocId = document.getElementById('company_document_id').value;
    if (!selectedDocId) {
        alert('Please select a company document.');
        return;
    }

    const selectedMonth = $('#pay-select-month').val();
    const selectedYear = $('#pay-select-year').val();

    if (!selectedMonth || !selectedYear) {
        alert('Please select a month and year.');
        return;
    }

    // Collect selected columns and their priorities
    let selectedColumns = [];
    document.querySelectorAll('.form-check-input:checked').forEach(function(checkbox) {
        const column = checkbox.value;
        const priorityInput = document.getElementById('priority_' + column);
        const priority = priorityInput ? parseInt(priorityInput.value, 10) : 9999;
        selectedColumns.push({ column: column, priority: priority });
    });

    // Sort by priority
    selectedColumns.sort((a, b) => a.priority - b.priority);

    // Extract just the column names in order
    const columns = selectedColumns.map(item => item.column);

    // Build the export URL
    downloadUrl = downloadUrl
        .replace('__MONTH__', selectedMonth)
        .replace('__YEAR__', selectedYear);

    // Append query parameters
    downloadUrl += '?company_document_id=' + encodeURIComponent(selectedDocId);
    downloadUrl += '&columns=' + encodeURIComponent(columns.join(','));

    // Navigate to export URL
    window.location.href = downloadUrl;
});
</script> -->  --}}

<script>
    // document.getElementById('download-sif-final').addEventListener('click', function() {
    //     const selectedDocId = document.getElementById('company_document_id').value;
    //     if (!selectedDocId) {
    //         alert('Please select a company document.');
    //         return;
    //     }

    //     const selectedMonth = $('#pay-select-month').val();
    //     const selectedYear = $('#pay-select-year').val();

    //     if (!selectedMonth || !selectedYear) {
    //         alert('Please select a month and year.');
    //         return;
    //     }

    //     // Collect selected DATA columns and their priorities
    //     let selectedDataColumns = [];
    //     document.querySelectorAll('.form-check-input:checked').forEach(function(checkbox) {
    //         const id = checkbox.id;
    //         if (id.startsWith('col_')) {
    //             const column = checkbox.value;
    //             const priorityInput = document.getElementById('priority_' + column);
    //             const priority = priorityInput ? parseInt(priorityInput.value, 10) : 9999;
    //             selectedDataColumns.push({
    //                 column: column,
    //                 priority: priority
    //             });
    //         }
    //     });

    //     // Sort DATA columns by priority
    //     selectedDataColumns.sort((a, b) => a.priority - b.priority);

    //     // Extract just the column names in order
    //     const dataColumns = selectedDataColumns.map(item => item.column);

    //     // Collect selected SCR summary columns and their priorities
    //     let selectedScrColumns = [];
    //     document.querySelectorAll('.form-check-input:checked').forEach(function(checkbox) {
    //         const id = checkbox.id;
    //         if (id.startsWith('scr_col_')) {
    //             const column = checkbox.value;
    //             const priorityInput = document.getElementById('scr_priority_' + column);
    //             const priority = priorityInput ? parseInt(priorityInput.value, 10) : 9999;
    //             selectedScrColumns.push({
    //                 column: column,
    //                 priority: priority
    //             });
    //         }
    //     });

    //     // Sort SCR columns by priority
    //     selectedScrColumns.sort((a, b) => a.priority - b.priority);

    //     // Extract just the SCR column names in order
    //     const scrColumns = selectedScrColumns.map(item => item.column);

    //     // Build the export URL
    //     downloadUrl = downloadUrl
    //         .replace('__MONTH__', selectedMonth)
    //         .replace('__YEAR__', selectedYear);

    //     // Append query parameters
    //     downloadUrl += '?company_document_id=' + encodeURIComponent(selectedDocId);
    //     downloadUrl += '&columns=' + encodeURIComponent(dataColumns.join(','));
    //     downloadUrl += '&scr_columns=' + encodeURIComponent(scrColumns.join(','));

    //     // Navigate to export URL
    //     window.location.href = downloadUrl;
    // });


    // old code
    {{--  document.getElementById('download-sif-final').addEventListener('click', function() {
        const selectedDocId = document.getElementById('company_document_id').value;
        if (!selectedDocId) {
            alert('Please select a company document.');
            return;
        }

        const selectedMonth = $('#pay-select-month').val();
        const selectedYear = $('#pay-select-year').val();
        // const selectedCompanyDocumentId = $('#pay-select-company_document_id').val();
        const selectedCompanyDocumentId = $('#company_document_id').val();

        if (!selectedMonth || !selectedYear) {
            alert('Please select a month and year.');
            return;
        }

        // Collect selected DATA columns and their priorities
        let selectedDataColumns = [];
        document.querySelectorAll('.form-check-input:checked').forEach(function(checkbox) {
            const id = checkbox.id;
            if (id.startsWith('col_')) {
                const column = checkbox.value;
                const priorityInput = document.getElementById('priority_' + column);
                const priority = priorityInput ? parseInt(priorityInput.value, 10) : 9999;
                selectedDataColumns.push({
                    column: column,
                    priority: priority
                });
            }
        });

        // Sort DATA columns by priority
        selectedDataColumns.sort((a, b) => a.priority - b.priority);
        const dataColumns = selectedDataColumns.map(item => item.column);

        // Collect selected SCR summary columns and their priorities
        let selectedScrColumns = [];
        document.querySelectorAll('.form-check-input:checked').forEach(function(checkbox) {
            const id = checkbox.id;
            if (id.startsWith('scr_col_')) {
                const column = checkbox.value;
                const priorityInput = document.getElementById('scr_priority_' + column);
                const priority = priorityInput ? parseInt(priorityInput.value, 10) : 9999;
                selectedScrColumns.push({
                    column: column,
                    priority: priority
                });
            }
        });

        // Sort SCR columns by priority
        selectedScrColumns.sort((a, b) => a.priority - b.priority);
        const scrColumns = selectedScrColumns.map(item => item.column);

        // Collect header checkboxes
        const showDataHeaders = document.getElementById('show_data_headers').checked ? 1 : 0;
        const showScrHeaders = document.getElementById('show_scr_headers').checked ? 1 : 0;

        // Build the export URL
        // let downloadUrlTemplate = "{{ route('backend.payslip.sif.export', ['month' => '__MONTH__', 'year' => '__YEAR__', 'company' => '__COMPANY__']) }}";
        let downloadUrlTemplate = "{{ route('backend.payslip.sif.export.xls', ['month' => '__MONTH__', 'year' => '__YEAR__', 'company' => '__COMPANY__']) }}";


        let downloadUrl = downloadUrlTemplate
            .replace('__MONTH__', selectedMonth)
            .replace('__YEAR__', selectedYear)
            .replace('__COMPANY__', selectedCompanyDocumentId);


        // Append query parameters
        // downloadUrl += '?company_document_id=' + encodeURIComponent(selectedDocId);
        downloadUrl += '&columns=' + encodeURIComponent(dataColumns.join(','));
        downloadUrl += '&scr_columns=' + encodeURIComponent(scrColumns.join(','));
        downloadUrl += '&show_data_headers=' + showDataHeaders;
        downloadUrl += '&show_scr_headers=' + showScrHeaders;

        // Navigate to export URL
        window.location.href = downloadUrl;
    });  --}}
    // end

    // new code
    document.getElementById('download-sif-final').addEventListener('click', function () {

        const selectedCompanyDocumentId = document.getElementById('company_document_id').value;
        if (!selectedCompanyDocumentId) {
            alert("Please select a company document.");
            return;
        }

        //--------------------------
        // 1️⃣ Collect Dynamic DATA Columns
        //--------------------------
        let dataColumns = [];

        document.querySelectorAll("#dynamic_rows .dynamic-row").forEach(row => {
            let field = row.querySelector(".column-select").value;
            let name = row.querySelector(".custom-name").value;
            let index = row.querySelector(".custom-index").value;

            if (field && index) {
                dataColumns.push({
                    field: field,
                    name: name,
                    index: parseInt(index)
                });
            }
        });

        //--------------------------
        // 2️⃣ Collect SCR Columns
        //--------------------------
        let scrColumns = [];

        document.querySelectorAll("#scr_dynamic_rows .scr-row").forEach(row => {
            let field = row.querySelector(".scr-select").value;
            let name = row.querySelector(".scr-name").value;
            let index = row.querySelector(".scr-index").value;

            if (field && index) {
                scrColumns.push({
                    field: field,
                    name: name,
                    index: parseInt(index)
                });
            }
        });

        //--------------------------
        // 3️⃣ Save JSON in CompanyDocument (AJAX)
        //--------------------------
        let jsonToSave = {
            columns: dataColumns,
            scr: scrColumns
        };

        $.ajax({
            url: "/payslip/company-document/" + selectedCompanyDocumentId + "/save_sif_settings",
            type: "POST",
            data: {
                _token: "{{ csrf_token() }}",
                settings: JSON.stringify(jsonToSave)
            },
            success: function () {
                console.log("Settings saved successfully!");

                //------------------------------
                // 4️⃣ After save → Download SIF
                //------------------------------
                let selectedMonth = $('#pay-select-month').val();
                let selectedYear = $('#pay-select-year').val();

                let downloadUrl = "{{ route('backend.payslip.sif.export.xls', ['month' => '__MONTH__', 'year' => '__YEAR__', 'company' => '__COMPANY__']) }}"
                    .replace('__MONTH__', selectedMonth)
                    .replace('__YEAR__', selectedYear)
                    .replace('__COMPANY__', selectedCompanyDocumentId);

                window.location.href = downloadUrl;
            }
        });
    });
    var selectedMonth = $('#pay-select-month').val();
    var selectedYear = $('#pay-select-year').val();
    var selectedCompanyDocumentId = $('#pay-select-company_document_id').val();
    var defaultURL = "{{ route('backend.payslip.close.report', ['month' => ':month', 'year' => ':year', 'company' => ':company']) }}"
            .replace(':month', selectedMonth)
            .replace(':year', selectedYear)
            .replace(':company', selectedCompanyDocumentId);
    var closeURL = defaultURL;

    $("#payroll_month").on("change", function () {
        let monthid = $(this).val();
        let companyId = $("#store_company_id").val();

        $.get("/payslip/get_payroll_details/" + monthid + "/" + companyId, function (res) {
            if (res.is_close == 1) {
                $("#delete_payroll_btn").hide();
            } else {
                $("#delete_payroll_btn").show();
            }
        });
    });

    function changeURL(url3) {
        closeURL = url3;
    }
    
    checkPayrolldata();
    function checkPayrolldata() {
        let monthid = $("#payroll_month").val();
        let companyId = $("#store_company_id").val();

        $.get("/payslip/get_payroll_details/" + monthid + "/" + companyId, function (res) {
            if (res.is_close == 1) {
                $("#delete_payroll_btn").hide();
            } else {
                $("#delete_payroll_btn").show();
            }
        });
    }

    $('#close-link').click(function () {
        Swal.fire({
            title: "Are you sure?",
            text: "Payroll cannot be modified once it’s closed for the month.",
            icon: "warning",
            showCancelButton: true,
            confirmButtonText: "Yes, close it!",
            cancelButtonText: "No"
        }).then((result) => {
            if (result.isConfirmed) {
                window.location.href = closeURL;
            }
        });
    });

    $("#company_document_id").on("change", function () {
        let companyId = $(this).val();

        $.get("/payslip/company-document/" + companyId + "/sif_settings", function (res) {

            // Only clear rows if we have saved data
            if (res.columns && res.columns.length > 0) {
                $("#dynamic_rows").html(""); // clear for data columns
            }

            if (res.scr && res.scr.length > 0) {
                $("#scr_dynamic_rows").html(""); // clear for SCR columns
            }

            //-----------------------------
            // DATA COLUMN PREFILL
            //-----------------------------
            res.columns.forEach(col => {
                addDataRow(col.field, col.name, col.index);
            });

            //-----------------------------
            // SCR COLUMN PREFILL
            //-----------------------------
            res.scr.forEach(col => {
                addScrRow(col.field, col.name, col.index);
            });

            updateDropdownOptions();
            updateScrDropdowns();
        });
    });

    $("#sifDocumentModal").on("show.bs.modal", function () {

        let companyId = $("#company_document_id").val();

        $.get("/payslip/company-document/" + companyId + "/sif_settings", function (res) {

            // Only clear rows if we have saved data
            if (res.columns && res.columns.length > 0) {
                $("#dynamic_rows").html(""); // clear for data columns
            }

            if (res.scr && res.scr.length > 0) {
                $("#scr_dynamic_rows").html(""); // clear for SCR columns
            }
            //-----------------------------
            // DATA COLUMN PREFILL
            //-----------------------------
            res.columns.forEach(col => {
                addDataRow(col.field, col.name, col.index);
            });

            //-----------------------------
            // SCR COLUMN PREFILL
            //-----------------------------
            res.scr.forEach(col => {
                addScrRow(col.field, col.name, col.index);
            });

            updateDropdownOptions();
            updateScrDropdowns();
        });
    });

    window.dataColumnsList = [
        'no_value',
        'record_type',
        'labour_id',
        'emp_name',
        'routing_number',
        'account_number',
        'iban_number',
        'pay_start_date',
        'pay_end_date',
        'number_of_days',
        'fixed_income_amount',
        'variable_income_amount',
        'days_on_leave',
        'housing_allowance',
        'conveyance_allowance',
        'medical_allowance',
        'annual_passage_allowance',
        'overtime_allowance',
        'other_allowance',
        'leave_encashment',
        'employee_code',
        'total_salary',
        'gross_salary',
        'total_deduction',
    ];

    window.scrColumnsList = [
        'no_value',
        'record_type',
        'mol_company_number',
        'routing_bank_code',
        'file_creation_date',
        'file_creation_time',
        'salary_month',
        'edr_count',
        'total_salary',
        'payment_currency',
        'employer_reference'
    ];

    function addDataRow(field = "", name = "", index = "") {
        let row = `
            <div class="row mb-2 dynamic-row">
                <div class="col-md-4">
                    <select class="form-select column-select">
                        <option value="">Select Field</option>
                        ${window.dataColumnsList.map(col => `
                            <option value="${col}" ${field === col ? "selected" : ""}>
                                ${col.replace(/_/g, ' ').toUpperCase()}
                            </option>
                        `).join('')}
                    </select>
                </div>

                <div class="col-md-4">
                    <input type="text" class="form-control custom-name" value="${name}" placeholder="Enter Name">
                </div>

                <div class="col-md-2">
                    <input type="number" min="1" value="${index}" class="form-control custom-index" placeholder="Index">
                </div>

                <div class="col-md-2 d-flex">
                    <button class="btn btn-success add-row w-100 me-1">+</button>
                    <button class="btn btn-danger remove-row w-100">X</button>
                </div>
            </div>
        `;

        $("#dynamic_rows").append(row);
    }

    function addScrRow(field = "", name = "", index = "") {
        let row = `
            <div class="row mb-2 scr-row">
                <div class="col-md-4">
                    <select class="form-select scr-select">
                        <option value="">Select SCR Field</option>
                        ${window.scrColumnsList.map(col => `
                            <option value="${col}" ${field === col ? "selected" : ""}>
                                ${col.replace(/_/g, ' ').toUpperCase()}
                            </option>
                        `).join('')}
                    </select>
                </div>

                <div class="col-md-4">
                    <input type="text" class="form-control scr-name" value="${name}" placeholder="Enter Name">
                </div>

                <div class="col-md-2">
                    <input type="number" min="1" value="${index}" class="form-control scr-index" placeholder="Index">
                </div>

                <div class="col-md-2 d-flex">
                    <button class="btn btn-success scr-add w-100 me-1">+</button>
                    <button class="btn btn-danger scr-remove w-100">X</button>
                </div>
            </div>
        `;

        $("#scr_dynamic_rows").append(row);
    }


    //end
    flatpickr(".flatpickr", {
        dateFormat: "Y-m-d"
    });
</script>
<script>
    function deletePayroll() {
        let modal = $("#geneatePayrollModal"); // scope only to this modal

        let company = modal.find("select[name='company_document_id']").val();
        let month = modal.find("select[name='month']").val();
        let year = modal.find("select[name='year']").val();

        if (!month || !year) {
            alert("Please select month and year first.");
            return;
        }

        if (confirm("Are you sure you want to delete the payroll?")) {
            $.ajax({
                url: "{{ route('backend.payslip.user-payslip.destroy') }}",
                type: "POST",
                data: {
                    _token: "{{ csrf_token() }}",
                    company_document_id: company,
                    month: month,
                    year: year
                },
                success: function(response) {
                    alert(response.message || "Payroll deleted successfully.");
                    location.reload();
                },
                error: function(xhr) {
                    alert("Error deleting payroll!");
                }
            });
        }
    }
</script>

<script>

    function updateDropdownOptions() {
        // Collect selected values
        let selected = [];
        document.querySelectorAll(".column-select").forEach(function (select) {
            if (select.value !== "") {
                selected.push(select.value);
            }
        });

        // Update each dropdown
        document.querySelectorAll(".column-select").forEach(function (select) {
            let currentValue = select.value;
            select.innerHTML = `<option value="">Select Field</option>`;

            allColumns.forEach(function (col) {
                // If it is selected in another row, hide it
                if (selected.includes(col) && col !== currentValue && col !== "no_value") return;

                let option = document.createElement("option");
                option.value = col;
                option.textContent = col.replace('_', ' ');
                if (col === currentValue) option.selected = true;

                select.appendChild(option);
            });
        });
    }

    // Add new row
    document.addEventListener("click", function (e) {
        if (e.target.classList.contains("add-row")) {
            let row = document.querySelector(".dynamic-row").cloneNode(true);

            row.querySelector(".column-select").value = "";
            row.querySelector(".custom-name").value = "";
            row.querySelector(".custom-index").value = "";

            document.getElementById("dynamic_rows").appendChild(row);

            updateDropdownOptions();
        }
    });

    // Remove row
    document.addEventListener("click", function (e) {
        if (e.target.classList.contains("remove-row")) {
            let rows = document.querySelectorAll(".dynamic-row");
            if (rows.length > 1) {
                e.target.closest(".dynamic-row").remove();
                updateDropdownOptions();
            }
        }
    });

    // On dropdown change → update all
    document.addEventListener("change", function (e) {
        if (e.target.classList.contains("column-select")) {
            updateDropdownOptions();
        }
    });

    // Initial processing
    updateDropdownOptions();
</script>

<script>

    function updateScrDropdowns() {
        let selected = [];

        document.querySelectorAll(".scr-select").forEach(function (select) {
            if (select.value !== "") {
                selected.push(select.value);
            }
        });

        document.querySelectorAll(".scr-select").forEach(function (select) {
            let currentValue = select.value;

            select.innerHTML = `<option value="">Select SCR Field</option>`;

            scrAllColumns.forEach(function (col) {
                if (selected.includes(col) && col !== currentValue && col !== "no_value") return;


                let option = document.createElement("option");
                option.value = col;
                option.textContent = col.replace(/_/g, " ").replace(/\b\w/g, l => l.toUpperCase());

                if (col === currentValue) option.selected = true;

                select.appendChild(option);
            });
        });
    }

    // Add new SCR row
    document.addEventListener("click", function (e) {
        if (e.target.classList.contains("scr-add")) {
            let row = document.querySelector(".scr-row").cloneNode(true);

            row.querySelector(".scr-select").value = "";
            row.querySelector(".scr-name").value = "";
            row.querySelector(".scr-index").value = "";

            document.getElementById("scr_dynamic_rows").appendChild(row);

            updateScrDropdowns();
        }
    });

    // Remove SCR row
    document.addEventListener("click", function (e) {
        if (e.target.classList.contains("scr-remove")) {
            let rows = document.querySelectorAll(".scr-row");

            if (rows.length > 1) {
                e.target.closest(".scr-row").remove();
                updateScrDropdowns();
            }
        }
    });

    // Update dropdowns when selecting
    document.addEventListener("change", function (e) {
        if (e.target.classList.contains("scr-select")) {
            updateScrDropdowns();
        }
    });

    updateScrDropdowns();
</script>

@endpush