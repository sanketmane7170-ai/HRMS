@extends('layouts.backend')

@section('content')
<!-- Page Wrapper -->
<div class="page-wrapper">
    <div class="content container-fluid">
        <!-- Page Header -->
        <div class="page-header">
            <div class="row align-items-center">
                <div class="col">
                    <h3 class="page-title">{{__trans('create_document_type')}}</h3>
                    <ul class="breadcrumb">
                        <li class="breadcrumb-item"><a
                                href="{{route('backend.dashboard')}}">{{__trans('dashboard')}}</a></li>
                        <li class="breadcrumb-item"><a
                                href="{{route('backend.document-types.index')}}">{{__trans('document_type')}}</a></li>
                        <li class="breadcrumb-item active">{{__trans('create_document_type')}}</li>
                    </ul>
                </div>
                <div class="col-auto">
                </div>
            </div>
        </div>
        <div class="row">
            <div class="col-sm-12">
                <div class="card card-table">
                    <div class="card-body">
                        <form action="{{route('backend.document-types.store')}}" datatable="true" method="POST"
                            class="ajax-form-submit reset" redirect>
                            @csrf
                            <div class="modal-body p-4">
                                <div class="row">
                                    <div class="col-md-10">
                                        <div class="row">
                                            <div class="col-md-8">
                                                <div class="mb-3">
                                                    <label for="name" class="form-label">{{ __trans('name') }}</label>
                                                    <input type="text" name="name" class="form-control" id="name"
                                                        placeholder="{{ __trans('name') }}">
                                                </div>
                                            </div>
                                            <div class="col-md-4 d-flex align-items-center">
                                                <div class="form-check form-switch mb-3" style="margin-top: auto;">
                                                    <input class="form-check-input" type="checkbox" name="user_visible"
                                                        id="user_visible" value="1" checked>
                                                    <label class="form-check-label ms-2"
                                                        for="user_visible">{{ __trans('Visible to User') }}</label>
                                                </div>
                                            </div>
                                        </div>


                                        <div class="col-md-12">
                                            <div class="mb-3">
                                                <label for="template" class="form-label">{{__trans('template')}}</label>
                                                <textarea name="template" id="template" id="template"></textarea>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-2">
                                        <label for="">{{__trans('available_keywords')}}</label>
                                        @foreach ($keywords as $keyword)
                                        {{$keyword}},
                                        @endforeach
                                        <br>
                                        <label for="">{{__trans('payslip_keyword')}}</label>
                                        [[company_name]],
                                        [[company_address]],
                                        [[payslip_date]],
                                        [[currency]],
                                        [[month]],
                                        [[year]].
                                        [[username]],
                                        [[designation]],
                                        [[emp_code]],
                                        [[location]],
                                        [[basic_salary]],
                                        [[housing_allowance]],
                                        [[transportation_allowance]],
                                        [[other_allowance]],
                                        [[tips]],
                                        [[joining_date]],
                                        [[location]],
                                        [[deduction_allowances]],
                                        [[salary_allowances]],
                                        [[total_earning]],
                                        [[total_deduction]],
                                        [[net_amount]],
                                        [[currency]],
                                        [[present]],
                                        [[off_day]],
                                        [[sick_leave_taken]],
                                        [[extra_leave_taken]],
                                        [[annual_leave_balance]],
                                        [[ph_leave_balance]],
                                        [[sick_leave_balance]],
                                        [[cancel_off_leave_balance]],
                                        [[account_number]],
                                        [[start_date]],
                                        [[end_date]],
                                        [[total_working_days]],
                                        [[attendance_deduction]],
                                        [[gross_salary]],
                                        [[total_deduction_with_attendance]],
                                        [[payable_basic_salary]],
                                        [[payable_housing_allowance]],
                                        [[payable_transportation_allowance]],
                                        [[payable_other_allowance]],
                                        <br>
                                        <small class="text-danger">**
                                            {{__trans('use_keyword_as_it_to_load_user_data')}}</small>
                                    </div>

                                </div>
                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-secondary waves-effect"
                                    data-bs-dismiss="modal">{{__trans('close')}}</button>
                                <button type="submit" class="btn btn-info waves-effect waves-light">{{__trans('save')}}
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
initTextEditorWithSource(['template'])
</script>
@endpush