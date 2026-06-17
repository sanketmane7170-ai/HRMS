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
            <div class="row">
                <div class="col-sm-12">
                    <h3 class="page-title">{{__trans('add_employee_form')}}</h3>
                    <ul class="breadcrumb">
                        <li class="breadcrumb-item"><a
                                href="{{route('backend.dashboard')}}">{{__trans('dashboard')}}</a></li>
                        <li class="breadcrumb-item"><a
                                href="{{route('backend.users.index')}}">{{__trans('employee_list')}}</a></li>
                        <li class="breadcrumb-item active">{{__trans('add_employee')}}</li>
                    </ul>
                </div>
            </div>
        </div>
        <!-- /Page Header -->
        <div class="page-content">
            <form action="{{route('backend.users.store')}}" class="ajax-form-submit reset" redirect method="POST">
                <div class="row">
                    @csrf
                    <div class="col-xl-6 col-12">
                        <div class="card bg-white">
                            <div class="card-header">
                                <h5 class="card-title">{{__trans('personal_details')}}</h5>
                            </div>
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-lg-6">
                                        <div class="form-group">
                                            <label>{{__trans('first_name')}}</label>
                                            <input type="text" class="form-control" name="first_name">
                                        </div>
                                    </div>
                                    <div class="col-lg-6">
                                        <div class="form-group">
                                            <label>{{__trans('last_name')}}</label>
                                            <input type="text" class="form-control" name="last_name">
                                        </div>
                                    </div>
                                    <div class="col-lg-6">
                                        <div class="form-group">
                                            <label>{{__trans('dob')}}</label>
                                            <input type="text" class="form-control datepickerbirth"
                                                name="date_of_birth">
                                        </div>
                                    </div>
                                    <div class="col-lg-6">
                                        <div class="form-group">
                                            <label>{{__trans('nationality')}}</label>
                                            <select id="flag_country_id" name="country_id"
                                                class="form-control select-search flag_country">
                                                <option value="">{{__trans('select_a_option')}}</option>
                                                @foreach (getCountryListwithFlag() as $country)
                                                <option data-flag="{{ $country['flag_url'] }}"
                                                    value="{{$country['id']}}">{{$country['name']}}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                    </div>


                                    <div class="col-lg-6">
                                        <div class="form-group">
                                            <label>{{__trans('gender')}}</label>

                                            <div class="pt-3">
                                                <div class="form-check form-check-inline">
                                                    <input class="form-check-input" type="radio" name="gender"
                                                        id="gender_male" value="Male" checked="">
                                                    <label class="form-check-label" for="gender_male">
                                                        {{__trans('male')}}
                                                    </label>
                                                </div>
                                                <div class="form-check form-check-inline">
                                                    <input class="form-check-input" type="radio" name="gender"
                                                        id="gender_female" value="Female">
                                                    <label class="form-check-label" for="gender_female">
                                                        {{__trans('female')}}
                                                    </label>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-lg-6">
                                        <div class="form-group">
                                            <label>{{__trans('marital_status')}}</label>
                                            <select name="martial_status" class="form-control select">
                                                <option value="">{{__trans('select_a_option')}}</option>
                                                @foreach (\App\Enums\MartialStatus::cases() as $status)
                                                <option value="{{$status->value}}">{{$status->name}}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                    </div>
                                    <div class="col-lg-6">
                                        <div class="form-group">
                                            <label>Work Email</label>
                                            <input type="email" class="form-control" name="email">
                                        </div>
                                    </div>
                                    <div class="col-lg-6">
                                        <div class="form-group">
                                            <label>{{__trans('work_phone')}}</label>
                                            <input type="tel" class="form-control" name="phone"
                                                onKeyPress="if(this.value.length==10) return false">
                                        </div>
                                    </div>
                                    <div class="col-lg-6">
                                        <div class="form-group">
                                            <label>{{__trans('personal_email')}}</label>
                                            <input type="email" class="form-control" name="personal_email">
                                        </div>
                                    </div>
                                    <div class="col-lg-6">
                                        <div class="form-group">
                                            <label>{{__trans('personal_phone')}}</label>
                                            <input type="tel" class="form-control" name="personal_phone"
                                                onKeyPress="if(this.value.length==10) return false">
                                        </div>
                                    </div>
                                    <div class="col-lg-6">
                                        <div class="form-group">
                                            <label>Employee Status</label>
                                            <select name="status" class="form-control select">
                                                <option value="active">Active</option>
                                                <option value="in-active">Inactive</option>
                                                <option value="resigned">Resigned</option>
                                                <option value="terminated">Terminated</option>
                                            </select>
                                        </div>
                                    </div>
                                    <div class="col-lg-6">
                                        <div class="form-group">
                                            <label>{{__trans('emp_id')}} (Optional)</label>
                                            <input type="text" class="form-control" name="emp_id">
                                        </div>
                                    </div>
                                    <div class="col-lg-6">
                                        <div class="form-group">
                                            <label>{{__trans('biometric_user_id')}} (Optional)</label>
                                            <input type="number" class="form-control" name="biometric_user_id">
                                        </div>
                                    </div>
                                    <div class="col-lg-6">
                                        <div class="form-group">
                                            <label>{{ __trans('visa_category') }}</label>
                                            <select name="visa_category" class="form-control select-search">
                                                <option value="">{{ __trans('select_a_option') }}</option>
                                                <option value="golden_visa">Golden Visa</option>
                                                <option value="company_sponsored">Company Sponsored Visa</option>
                                                <option value="family_sponsored">Family Sponsored Visa</option>
                                                <option value="partner_investor">Partner / Investor Visa</option>
                                                <option value="freelance">Freelance Visa</option>
                                                <option value="student">Student Visa</option>
                                                <option value="visit">Visit Visa</option>
						<option value="work_permit">Work Permit</option>
                                            </select>
                                        </div>
                                    </div>
                                    <div class="col-lg-6">
                                        <div class="form-group">
                                            <label>{{__trans('visa_type')}}</label>
                                            <input type="text" class="form-control" name="visa_type" value="{{ old('visa_type') }}">
                                        </div>
                                    </div>
                                    <div class="col-lg-6">
                                        <div class="form-group">
                                            <label>{{__trans('visa_designation')}}</label>
                                            <input type="text" class="form-control" name="visa_designation" value="{{ old('visa_designation') }}">
                                        </div>
                                    </div>
                                    <div class="col-lg-12">
                                        <div class="form-group">
                                            <label>{{__trans('address')}}</label>
                                            <textarea rows="5" name="address" cols="5" class="form-control"
                                                placeholder="938 Green Acres Road"></textarea>
                                        </div>
                                    </div>
                                    <div class="col-lg-6">
                                        <label>{{__trans('image')}}</label>
                                        <div class="d-flex align-items-center">
                                            <label class="avatar avatar-xxl profile-cover-avatar m-0" for="edit_img">
                                                <img id="avatarImg" class="avatar-img" src="{{asset('assets/backend/img/profiles/boy-avtar.png')}}" alt="Profile Image">
                                                <input type="file" name="profile_image" id="profile-image" accept="image/*" onchange="previewImage('profile-image','avatarImg')">
                                                <span class="avatar-edit" onclick="$('#profile-image').click()">
                                                    <i data-feather="edit-2" class="avatar-uploader-icon shadow-soft"></i>
                                                </span>
                                            </label>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
@if(false) Commented out by Sanket as per user request
                        <div class="card bg-white">
                            <div class="card-header">
                                <h5 class="card-title"> {{__trans('passport_details')}}</h5>
                            </div>
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-lg-6">
                                        <div class="form-group">
                                            <label>{{__trans('passport_number')}}</label>
                                            <input type="text" class="form-control" name="passport_number">
                                        </div>
                                    </div>
                                    <div class="col-lg-6">
                                        <div class="form-group">
                                            <label>{{__trans('passport_country')}}</label>
                                            <select name="passport_country" class="form-control select-search flag_country">
                                                <option value="">{{__trans('select_a_option')}}</option>
                                                @foreach (getCountryListwithFlag() as $country)
                                                <option data-flag="{{ $country['flag_url'] }}"
                                                    value="{{$country['name']}}">{{$country['name']}}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                    </div>
                                    <div class="col-lg-4">
                                        <div class="form-group">
                                            <label>{{__trans('passport_issue_date')}}</label>
                                            <input type="text" class="form-control datepicker" name="passport_issue_date">
                                        </div>
                                    </div>
                                    <div class="col-lg-4">
                                        <div class="form-group">
                                            <label>{{__trans('passport_expiry_date')}}</label>
                                            <input type="text" class="form-control datepicker" name="passport_expiry_date">
                                        </div>
                                    </div>
                                    <div class="col-lg-4">
                                        <div class="form-group">
                                            <label>{{__trans('passport_place_of_issue')}}</label>
                                            <input type="text" class="form-control" name="passport_place_of_issue">
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="card bg-white">
                            <div class="card-header">
                                <h5 class="card-title"> {{__trans('visa_details')}}</h5>
                            </div>
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-lg-6">
                                        <div class="form-group">
                                            <label>{{__trans('visa_number')}}</label>
                                            <input type="text" class="form-control" name="visa_number">
                                        </div>
                                    </div>
                                    <div class="col-lg-6">
                                        <div class="form-group">
                                            <label>{{__trans('visa_country')}}</label>
                                            <select name="visa_country" class="form-control select-search flag_country">
                                                <option value="">{{__trans('select_a_option')}}</option>
                                                @foreach (getCountryListwithFlag() as $country)
                                                <option data-flag="{{ $country['flag_url'] }}"
                                                    value="{{$country['name']}}">{{$country['name']}}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                    </div>
                                    <div class="col-lg-4">
                                        <div class="form-group">
                                            <label>{{__trans('visa_issue_date')}}</label>
                                            <input type="text" class="form-control datepicker" name="visa_issue_date">
                                        </div>
                                    </div>
                                    <div class="col-lg-4">
                                        <div class="form-group">
                                            <label>{{__trans('visa_expiry_date')}}</label>
                                            <input type="text" class="form-control datepicker" name="visa_expiry_date">
                                        </div>
                                    </div>
                                    <div class="col-lg-4">
                                        <div class="form-group">
                                            <label>{{__trans('visa_place_of_issue')}}</label>
                                            <input type="text" class="form-control" name="visa_place_of_issue">
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="card bg-white">
                            <div class="card-header">
                                <h5 class="card-title"> {{__trans('labor_card_details')}}</h5>
                            </div>
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-lg-6">
                                        <div class="form-group">
                                            <label>{{__trans('labor_card_number')}}</label>
                                            <input type="text" class="form-control" name="labor_card_number">
                                        </div>
                                    </div>
                                    <div class="col-lg-6">
                                        <div class="form-group">
                                            <label>{{__trans('labor_card_personal_number')}}</label>
                                            <input type="text" class="form-control" name="labor_card_personal_number">
                                        </div>
                                    </div>
                                    <div class="col-lg-6">
                                        <div class="form-group">
                                            <label>{{__trans('labor_card_issue_date')}}</label>
                                            <input type="text" class="form-control datepicker" name="labor_card_issue_date">
                                        </div>
                                    </div>
                                    <div class="col-lg-6">
                                        <div class="form-group">
                                            <label>{{__trans('labor_card_expiry_date')}}</label>
                                            <input type="text" class="form-control datepicker" name="labor_card_expiry_date">
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="card bg-white">
                            <div class="card-header">
                                <h5 class="card-title"> {{__trans('emirates_id_details')}}</h5>
                            </div>
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-lg-12">
                                        <div class="form-group">
                                            <label>{{__trans('emirates_id_number')}}</label>
                                            <input type="text" class="form-control" name="emirates_id_number">
                                        </div>
                                    </div>
                                    <div class="col-lg-6">
                                        <div class="form-group">
                                            <label>{{__trans('emirates_id_issue_date')}}</label>
                                            <input type="text" class="form-control datepicker" name="emirates_id_issue_date">
                                        </div>
                                    </div>
                                    <div class="col-lg-6">
                                        <div class="form-group">
                                            <label>{{__trans('emirates_id_expiry_date')}}</label>
                                            <input type="text" class="form-control datepicker" name="emirates_id_expiry_date">
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
@endif
                    </div>
                    <div class="col-xl-6 col-12">
                        <div class="card bg-white">
                            <div class="card-header">
                                <h5 class="card-title">{{__trans('social_details')}}</h5>
                            </div>
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-lg-12">
                                        <div class="form-group">
                                            <label>{{__trans('linkedin_url')}}</label>
                                            <input type="text" class="form-control" name="linkedin_profile_url">
                                        </div>
                                    </div>
                                    <div class="col-lg-12">
                                        <div class="form-group">
                                            <label>{{__trans('skills')}}</label>
                                            <textarea rows="9" cols="5" class="form-control" name="skills"></textarea>
                                        </div>
                                    </div>
                                    <div class="col-lg-12">
                                        <div class="form-group">
                                            <label>{{__trans('hobbies')}}</label>
                                            <textarea rows="8" cols="5" class="form-control" name="hobbies"></textarea>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-xl-6 col-12">
                        <div class="card bg-white">
                            <div class="card-header">
                                <h5 class="card-title">{{__trans('work_details')}}</h5>
                            </div>
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-lg-6">
                                        <div class="form-group">
                                            <label>{{__trans('branch')}}</label>
                                            <select name="department_id" class="ajax-select2" id="department"
                                                data-target="{{route('ajax.select2.fetch.departments')}}">

                                            </select>
                                        </div>
                                    </div>
                                    <div class="col-lg-6">
                                        <div class="form-group">
                                            <label>{{__trans('department')}}</label>
                                            <select name="division_id" class="form-control select" id="division_id">

                                            </select>
                                        </div>
                                    </div>
                                    <div class="col-lg-6">
                                        <div class="form-group">
                                            <label for="">{{__trans('designation')}}</label>
                                            <select name="designation_id" class="ajax-select2" id="designations"
                                                data-target="{{route('ajax.select2.fetch.designations')}}"
                                                data-dependent="department_id">
                                            </select>
                                        </div>
                                    </div>
                                    <div class="col-lg-6">
                                        <div class="form-group">
                                            <label>{{__trans('grade')}}</label>
                                            <input type="text" class="form-control" id="grade" name="grade" readonly>
                                        </div>
                                    </div>
                                    <div class="col-lg-6">
                                        <div class="form-group">
                                            <label>{{__trans('role')}}</label>
                                            <select name="role_id" class="ajax-select2" id="role_id"
                                                data-target="{{route('ajax.select2.fetch.roles')}}">

                                            </select>
                                        </div>
                                    </div>

                                    <div class="col-lg-6">
                                        <div class="form-group">
                                            <label>MOL Location</label>
                                            <select name="company_document_id" class="ajax-select2"
                                                id="company_document_id"
                                                data-target="{{route('ajax.select2.fetch.companydocument')}}">

                                            </select>
                                        </div>
                                    </div>
                                    <div class="col-lg-6">
                                        <div class="form-group">
                                            <label>{{__trans('company_name')}}</label>
                                            <input type="text" class="form-control" name="company_name"
                                                value="{{getSetting('site_title')}}">
                                        </div>
                                    </div>
                                    <div class="col-lg-6">
                                        <div class="form-group">
                                            <label>{{__trans('date_of_joining')}}</label>
                                            <input type="text" class="form-control datepickerjoining"
                                                name="date_of_joining" id="date_of_joining">
                                        </div>
                                    </div>
                                    <div class="col-lg-6">
                                        <div class="form-group">
                                            <label>{{ __trans('probation_month') }}</label>
                                            <input type="number" min="0" max="24" class="form-control datepickerjoining"
                                                name="probation_month" id="probation_month" placeholder="e.g. 3">
                                        </div>
                                    </div>
                                    <div class="col-lg-6">
                                        <div class="form-group">
                                            <label>{{__trans('probation_end_date')}}</label>
                                            <input type="text" class="form-control datepicker" name="probation_end_date"
                                                id="probation_end_date" readonly>
                                        </div>
                                    </div>
                                    <div class="col-lg-6">
                                        <div class="form-group">
                                            <label>{{__trans('last_working_day')}}</label>
                                            <input type="text" class="form-control datepicker"
                                                name="last_working_day" id="last_working_day" value="{{ old('last_working_day') }}">
                                        </div>
                                    </div>
                                    <div class="col-lg-6">
                                        <div class="form-group">
                                            <label>{{__trans('last_working_day')}}</label>
                                            <input type="text" class="form-control datepicker"
                                                name="last_working_day" id="last_working_day" value="{{ old('last_working_day') }}">
                                        </div>
                                    </div>
                                    <div class="col-lg-4">
                                        <div class="form-group">
                                            <label>{{__trans('report_to')}}</label>
                                            <select name="report_to_id[]" class="ajax-select2" id="report_to_id"
                                                data-target="{{route('ajax.select2.fetch.users')}}" multiple>

                                            </select>
                                        </div>
                                        <div class="form-group mt-2">
                                            <div class="form-check">
                                                <input type="checkbox" name="approved_first_level"
                                                    id="approved_first_level" class="form-check-input" value="1">
                                                <label class="form-check-label" for="approved_first_level">Approved
                                                    in
                                                    First Level</label>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-lg-2">
                                        <div class="form-group">
                                            <label>{{ __trans('work_week') }}</label>
                                            <input type="number" min="1" max="7" class="form-control" id="work_week"
                                                name="work_week">
                                        </div>
                                    </div>

                                    <div class="col-lg-4" id="weekend-box" style="display:none;">
                                        <div class="form-group">
                                            <label>{{ __trans('weekend_days') }}</label>
                                            <select id="weekend" name="weekend[]" class="form-control select-search"
                                                multiple></select>
                                            <small id="weekend-hint" class="form-text text-muted"></small>
                                        </div>
                                    </div>

                                    <div class="col-lg-6">
                                        <div class="form-group">
                                            <label>{{ __trans('calculated_workdays') }}</label>
                                            <input type="text" id="workdays-container" name="calculated_workdays" class="form-control" style="height:auto; min-height:38px;" value="{{ old('calculated_workdays') }}">
                                        </div>
                                    </div>
                                    <div class="col-lg-6">
                                        <div class="form-group">
                                            <label>{{__trans('location')}}</label>
                                            <input type="text" class="form-control" name="location" value="{{ old('location') }}">
                                        </div>
                                    </div>
                                    <div class="col-lg-6">
                                        <div class="form-group">
                                            <label>{{__trans('mol_number')}}</label>
                                            <input type="text" class="form-control" name="mol_number" value="{{ old('mol_number') }}">
                                        </div>
                                    </div>
                                    <div class="col-lg-6">
                                        <div class="form-group">
                                            <label>{{__trans('entity')}}</label>
                                            <input type="text" class="form-control" name="entity">
                                        </div>
                                    </div>
                                    @if (auth()->user()->hasRole(\App\Models\User::ROLE_ADMIN) ||
                                    auth()->user()->hasRole(\App\Models\User::ROLE_SUPER_ADMIN))
                                    <div class="col-lg-6">
                                        <div class="form-group">
                                            <label>{{__trans('medical_insurance_provided')}}</label>
                                            <select id="medical_insurance_provided" name="medical_insurance_provided"
                                                class="form-control" onchange="togglePremiumInput()">
                                                <option value="0"
                                                    {{ old('medical_insurance_provided') == '0' ? 'selected' : '' }}>
                                                    No
                                                </option>
                                                <option value="1"
                                                    {{ old('medical_insurance_provided') == '1' ? 'selected' : '' }}>
                                                    Yes
                                                </option>
                                            </select>
                                        </div>
                                    </div>
                                    <div id="insurance_details_div" class="col-lg-12 row" style="display: none;">
                                        <div class="col-lg-6">
                                            <div class="form-group">
                                                <label for="annual_premium">Annual Premium:</label>
                                                <input type="number" id="annual_premium" name="annual_premium"
                                                    class="form-control" min="0" step="0.01"
                                                    value="{{ old('annual_premium') }}">
                                            </div>
                                        </div>
                                        <div class="col-lg-6">
                                            <div class="form-group">
                                                <label>{{__trans('insurance_number')}}</label>
                                                <input type="text" class="form-control" name="insurance_number" value="{{ old('insurance_number') }}">
                                            </div>
                                        </div>
                                        <div class="col-lg-6">
                                            <div class="form-group">
                                                <label>{{__trans('insurance_expiry')}}</label>
                                                <input type="text" class="form-control datepicker"
                                                    name="insurance_expiry" value="{{ old('insurance_expiry') }}">
                                            </div>
                                        </div>
                                    </div>
                                    @endif

                                    <div class="col-lg-6">
                                        <div class="form-group">
                                            <label>{{__trans('salary_mode')}}</label>
                                            <select id="salary_mode" name="salary_mode" class="form-control">
                                                <option value="account">Account</option>
                                                <option value="cash">Cash</option>

                                            </select>
                                        </div>
                                    </div>
                                    <div class="col-lg-6">
                                        <div class="form-group">
                                            <label>{{__trans('air_ticket_policy')}}</label>
                                            <select name="air_ticket_setting_id" class="form-control select-search">
                                                <option value="">{{__trans('select_a_option')}}</option>
                                                @foreach (getAirTicketSettingsList() as $AirTicketSetting)
                                                <option value="{{$AirTicketSetting->id}}">
                                                    {{$AirTicketSetting->policy_name}}
                                                </option>
                                                @endforeach
                                            </select>
                                        </div>
                                    </div>

                                    <div class="col-lg-6">
                                        <div class="form-group">
                                            <label for="air_ticket_count">{{__trans('air_ticket_count')}}:</label>
                                            <input type="number" id="air_ticket_count" name="air_ticket_count"
                                                class="form-control" min="0" step="1">
                                        </div>
                                    </div>

                                    <div class="col-lg-6">
                                        <div class="form-group">
                                            <label>{{__trans('renewal_air_ticket')}}</label>
                                            <select name="renewal_air_ticket" class="form-control select-search">
                                                <option value="">{{__trans('select_a_option')}}</option>
                                                <option value="">{{__trans('1_year')}}</option>
                                                <option value="">{{__trans('2_year')}}</option>

                                            </select>
                                        </div>
                                    </div>
                                    <div class="row" id="air_ticket_container">
                                        <div class="col-12 ticket-row" data-index="0">
                                            <div class="row g-2 align-items-end">
                                                <div class="col-md-4">
                                                    <div class="mb-3">
                                                        <label for="title_0" class="form-label">Title</label>
                                                        <input type="text" name="tickets[0][title]" id="title_0"
                                                            class="form-control"
                                                            placeholder="Ticket title / description">
                                                    </div>
                                                </div>

                                                <div class="col-md-4">
                                                    <div class="mb-3">
                                                        <label for="qty_0" class="form-label">Qty</label>
                                                        <input type="number" step="0.01" name="tickets[0][qty]"
                                                            id="qty_0" class="form-control" placeholder="1">
                                                    </div>
                                                </div>

                                                <div class="col-md-3">
                                                    <div class="mb-3">
                                                        <label for="percentage_0" class="form-label">Percentage</label>
                                                        <input type="number" step="0.01" name="tickets[0][percentage]"
                                                            id="percentage_0" class="form-control" placeholder="0.00%">
                                                    </div>
                                                </div>

                                                <div class="col-md-1">
                                                    <div class="mb-3">
                                                        <button type="button" id="addTicket"
                                                            class="btn btn-primary">+</button>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>



                                    <div class="col-lg-6">
                                        <div class="form-group">
                                            <!-- <label>{{__trans('is_rider')}}</label> -->

                                            <div class="pt-3">
                                                <div class="form-check form-check-inline">
                                                    <input class="form-check-input" type="checkbox" name="is_rider"
                                                        id="is_rider" value="0">
                                                    <label class="form-check-label" for="is_rider">
                                                        {{__trans('is_rider')}}
                                                    </label>
                                                </div>

                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-lg-6">
                                        <div class="form-group">
                                            <label>{{__trans('attendance_base')}}</label>
                                            <select name="attendance_base" class="form-control">
                                                <option value="yes">Yes</option>
                                                <option value="no">No</option>
                                            </select>
                                        </div>
                                    </div>

                                    <div class="col-lg-6">
                                        <div class="form-group">
                                            <label>{{__trans('company_accommodation')}}</label>
                                            <select name="company_accommodation" id="company_accommodation"
                                                class="form-control ">
                                                <option value="">{{__trans('select_a_option')}}</option>
                                                <option value="yes">Yes</option>
                                                <option value="no">No</option>
                                            </select>
                                        </div>
                                    </div>
                                    <div class="col-lg-12" style="display: none" id="accommodation_location">
                                        <div class="form-group">
                                            <label>{{__trans('accommodation_location')}}</label>
                                            <input class="form-control" type="text" name="accommodation_location">
                                        </div>
                                    </div>

                                    <div class="col-lg-6">
                                        <div class="form-group">
                                            <label
                                                for="free_document_request">{{__trans('free_document_request')}}:</label>
                                            <input type="number" id="free_document_request" name="free_document_request"
                                                class="form-control" min="0" step="1">
                                        </div>
                                    </div>

                                    <div class="col-lg-6">
                                        <div class="form-group">
                                            <label
                                                for="document_request_charge">{{__trans('document_request_charge')}}:</label>
                                            <input type="number" id="document_request_charge"
                                                name="document_request_charge" class="form-control" min="0" step="1">
                                        </div>
                                    </div>
                                    <div class="col-lg-12">
                                        <div class="form-group">
                                            <label>{{__trans('remarks')}}</label>
                                            <textarea rows="3" name="remarks" class="form-control">{{ old('remarks') }}</textarea>
                                        </div>
                                    </div>
                                    <div class="col-lg-12">
                                        <div class="form-group">
                                            <label>{{__trans('remarks')}}</label>
                                            <textarea rows="3" name="remarks" class="form-control">{{ old('remarks') }}</textarea>
                                        </div>
                                    </div>
                                </div>
                                <div class="row" id="custom_container">
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label for="shift_start"
                                                class="form-label">{{__trans('shift_start')}}</label>
                                            <input type="text" name="shifts[0][shift_start]"
                                                class="form-control timepicker" id="shift_start"
                                                placeholder="{{__trans('shift_start_time')}}">
                                        </div>
                                    </div>
                                    <div class="col-md-5">
                                        <div class="mb-3">
                                            <label for="shift_end" class="form-label">{{__trans('shift_end')}}</label>
                                            <input type="text" name="shifts[0][shift_end]"
                                                class="form-control timepicker" id="shift_end"
                                                placeholder="{{__trans('shift_end_time')}}">
                                        </div>
                                    </div>
                                    <div class="col-md-1">
                                        <div class="mb-3" style="margin-top: 31px !important;">
                                            <button type="button" id="addShift" class="btn btn-primary">+</button>
                                        </div>
                                    </div>
                                </div>




                            </div>
                        </div>
                    </div>

                    <div class="col-xl-6 col-12">
                        <div class="card bg-white">
                            <div class="card-header">
                                <h5 class="card-title"> {{__trans('emergency_contact_details')}}</h5>
                            </div>
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-lg-6">
                                        <div class="form-group">
                                            <label>{{__trans('name')}}</label>
                                            <input type="text" class="form-control" name="emergency_name">
                                        </div>
                                    </div>
                                    <div class="col-lg-6">
                                        <div class="form-group">
                                            <label>{{__trans('local_person_name')}}</label>
                                            <input type="text" class="form-control" name="local_person_name">
                                        </div>
                                    </div>
                                    <div class="col-lg-6">
                                        <div class="form-group">
                                            <label>{{__trans('relation')}}</label>
                                            <select id="emergency_relation" name="emergency_relation"
                                                class="form-control select-search">
                                                <option value="">{{__trans('select_a_option')}}</option>
                                                @foreach (\App\Enums\Relation::cases() as $relation)
                                                @if($relation->value == 'wife' || $relation->value == 'husband')
                                                @else
                                                <option value="{{$relation->value}}">{{$relation->name}}</option>
                                                @endif
                                                @endforeach

                                            </select>
                                        </div>
                                    </div>
                                    <div class="col-lg-6">
                                        <div class="form-group">
                                            <label>{{__trans('local_person_relation')}}</label>
                                            <select id="emergency_relation" name="local_person_relation"
                                                class="form-control select-search">
                                                <option value="">{{__trans('select_a_option')}}</option>
                                                @foreach (\App\Enums\Relation::cases() as $relation)
                                                @if($relation->value == 'wife' || $relation->value == 'husband')
                                                @else
                                                <option value="{{$relation->value}}">{{$relation->name}}</option>
                                                @endif
                                                @endforeach

                                            </select>
                                        </div>
                                    </div>
                                    <div class="col-lg-6">
                                        <div class="form-group">
                                            <label>{{__trans('isd_code')}}</label>
                                            <select name="emergency_isd_code" id="emergency_isd_code"
                                                class="form-control">
                                                <option value="">Select ISD Code</option>
                                                @foreach (config('isd_codes') as $isd)
                                                <option value="{{ $isd['code'] }}">{{ $isd['code'] }}
                                                    ({{ $isd['country'] }})</option>
                                                @endforeach
                                            </select>
                                        </div>
                                    </div>
                                    <div class="col-lg-6">
                                        <div class="form-group">
                                            <label>{{__trans('local_person_phone')}}</label>
                                            <input type="text" class="form-control" name="local_person_phone">
                                        </div>
                                    </div>
                                    <div class="col-lg-6">
                                        <div class="form-group">
                                            <label>{{__trans('phone')}}</label>
                                            <input type="text" class="form-control" name="emergency_phone">
                                        </div>
                                    </div>

                                    <div class="col-lg-12">
                                        <div class="form-group">
                                            <label>{{__trans('email')}}</label>
                                            <input type="email" class="form-control" name="emergency_email">
                                        </div>
                                    </div>

                                    <div class="col-lg-6">
                                        <div class="form-group">
                                            <label>{{__trans('home_country')}}</label>
                                            <select id="emergency_home_country" name="emergency_home_country"
                                                class="form-control select-search flag_country">
                                                <option value="">{{__trans('select_a_option')}}</option>
                                                @foreach (getCountryListwithFlag() as $country)
                                                <option data-flag="{{ $country['flag_url'] }}"
                                                    value="{{$country['name']}}">{{$country['name']}}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                    </div>
                                    <div class="col-lg-6">
                                        <div class="form-group">
                                            <label>{{__trans('local_country')}}</label>
                                            <select id="emergency_local_country" name="emergency_local_country"
                                                class="form-control select-search flag_country">
                                                <option value="">{{__trans('select_a_option')}}</option>
                                                @foreach (getCountryListwithFlag() as $country)
                                                <option data-flag="{{ $country['flag_url'] }}"
                                                    value="{{$country['name']}}">{{$country['name']}}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                    </div>
                                    <div class="col-lg-6">
                                        <div class="form-group">
                                            <label>{{__trans('home_address')}}</label>
                                            <!-- <input type="text" class="form-control" name="home_address"> -->
                                            <textarea rows="5" name="emergency_home_address" cols="5"
                                                class="form-control" placeholder="938 Green Acres Road"></textarea>
                                        </div>
                                    </div>



                                    <div class="col-lg-6">
                                        <div class="form-group">
                                            <label>{{__trans('local_address')}}</label>
                                            <!-- <input type="text" class="form-control" name="local_address"> -->
                                            <textarea rows="5" name="emergency_local_address" cols="5"
                                                class="form-control" placeholder="938 Green Acres Road"></textarea>
                                        </div>
                                    </div>

                                </div>
                            </div>
                        </div>
                    </div>


                    <div class="col-xl-6 col-12">
                        <div class="card bg-white">
                            <div class="card-header">
                                <h5 class="card-title"> {{__trans('bank_details')}}</h5>
                            </div>
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-lg-4">
                                        <div class="form-group">
                                            <label>{{__trans('bank_name')}}</label>
                                            <input type="text" class="form-control" name="bank_name">
                                        </div>
                                    </div>
                                    <div class="col-lg-4">
                                        <div class="form-group">
                                            <label>{{__trans('account_number')}}</label>
                                            <input type="number" class="form-control" name="account_number"
                                                onKeyPress="if(this.value.length==11) return false">
                                        </div>
                                    </div>
                                    <div class="col-lg-4">
                                        <div class="form-group">
                                            <label>{{__trans('routing_number')}}</label>
                                            <input type="number" class="form-control" name="routing_number"
                                                onKeyPress="if(this.value.length==9) return false">
                                        </div>
                                    </div>
                                    <div class="col-lg-6">
                                        <div class="form-group">
                                            <label>{{__trans('international_Bank_Account_Number')}}</label>
                                            <input type="number" class="form-control" name="iba_number"
                                                onKeyPress="if(this.value.length==11) return false">
                                        </div>
                                    </div>
                                    <div class="col-lg-6">
                                        <div class="form-group">
                                            <label>{{__trans('swift_code')}}</label>
                                            <input type="text" class="form-control" name="swift_code">
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-xl-6 col-12">
                        <div class="card bg-white">
                            <div class="card-header">
                                <h5 class="card-title">PIC Certification</h5>
                            </div>
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-lg-4">
                                        <div class="form-group">
                                            <label>{{__trans('issue_date')}}</label>
                                            <input type="date" class="form-control" name="pic_issue_date">
                                        </div>
                                    </div>
                                    <div class="col-lg-4">
                                        <div class="form-group">
                                            <label>{{__trans('expiry_date')}}</label>
                                            <input type="date" class="form-control" name="pic_expiry_date">
                                        </div>
                                    </div>
                                    <div class="col-lg-4">
                                        <div class="form-group">
                                            <label>{{__trans('upload_document')}}</label>
                                            <input type="file" class="form-control" name="pic_doc">
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-12">
                        <div class="text-end mt-4 mb-4">
                            <button type="submit" class="btn btn-primary">{{__trans('create_user')}}</button>
                        </div>
                    </div>
                </div>
            </form>
        </div>


    </div>
</div>
<!-- /Page Wrapper -->

@endsection

@push('scripts')
<script src="{{asset('assets/backend/plugins/flatpickr/flatpickr.min.js')}}"></script>
<script>
    loadAjaxSelect2();
    flatpickr("input.datepicker", {
        dateFormat: "d/m/Y", // Updated by Sanket
    });
    flatpickr("input.datepickerbirth, input.datepickerjoining", {
        dateFormat: "d/m/Y", // Updated by Sanket
        maxDate: new Date()
    });

flatpickr('.timepicker', {
    enableTime: true,
    noCalendar: true,
    dateFormat: "H:i",
})
</script>
<script>
    function previewImage(id, previewImage) {
        src = '';
        var total_file = document.getElementById(id).files.length;
        for (var i = 0; i < total_file; i++) {
            src += URL.createObjectURL(event.target.files[i]);
        }
        $('#' + previewImage).attr('src', src);
    }
</script>
<script>
    $(document).ready(function() {

        // Counter for unique IDs
        var shiftCounter = 1;
        // Function to create a new set of shift input fields
        function createShiftInputs() {
            var newShiftInputs = `
                <div class="col-md-6">
                    <div class="mb-3">
                        <label for="shift_start_${shiftCounter}" class="form-label">{{__trans('shift_start')}}</label>
                        <input type="text" name="shifts[${shiftCounter}][shift_start]" class="form-control timepicker" id="shift_start_${shiftCounter}" placeholder="{{__trans('shift_start_time')}}">
                    </div>
                </div>
                <div class="col-md-5">
                    <div class="mb-3">
                        <label for="shift_end_${shiftCounter}" class="form-label">{{__trans('shift_end')}}</label>
                        <input type="text" name="shifts[${shiftCounter}][shift_end]" class="form-control timepicker" id="shift_end_${shiftCounter}" placeholder="{{__trans('shift_end_time')}}">
                    </div>
                </div>
            `;

        // Increment the counter for unique IDs
        shiftCounter++;

        return newShiftInputs;
    }

    // Event handler for the plus button
    $('#addShift').click(function() {
        // Append the new shift input fields to the container
        $('#custom_container').append(createShiftInputs());
        flatpickr('.timepicker', {
            enableTime: true,
            noCalendar: true,
            dateFormat: "H:i",
        })
    });
});
</script>
<script>
    function togglePremiumInput() {
        const insuranceElement = document.getElementById('medical_insurance_provided');
        const premiumDiv = document.getElementById('insurance_details_div');

        if (insuranceElement && premiumDiv) {
            const insuranceProvided = insuranceElement.value == '1';
            premiumDiv.style.display = insuranceProvided ? 'flex' : 'none';
        }
    }

// Initialize the state of the premium field
document.addEventListener("DOMContentLoaded", function() {
    togglePremiumInput();
});

$(document).ready(function() {
    function toggleInput() {
        if ($('#company_accommodation').val() === 'yes') {
            $('#div_accommodation_location').show();
        } else {
            $('#div_accommodation_location').hide();
        }
    }

    // Run on page load in case a value is already selected
    toggleInput();

    // Run on change
    $('#company_accommodation').change(function() {
        toggleInput();
    });

    $(document).on('change', '#date_of_joining', function() {
        var joiningDate = $(this).val();

        if (joiningDate !== '') {
            $.ajax({
                url: '{{ route("backend.getProbationEndDate") }}',
                type: 'GET',
                data: {
                    joining_date: joiningDate
                },
                success: function(response) {
                    // $('#probation_end_date').val(response.probation_end_date);
                     var parts = response.probation_end_date.split('-');
                    var formattedDate = parts[2] + '/' + parts[1] + '/' + parts[0];

                    $('#probation_end_date').val(formattedDate);
                },
                error: function() {
                    alert('Unable to calculate probation end date.');
                }
            });
        }
    });
});
$(document).ready(function() {
    // Trigger when branch (department) changes
    $('#department').on('change', function() {
        var branch_id = $(this).val();

        if (branch_id) {
            $.ajax({
                url: '{{ route("backend.getDivisions") }}',
                type: 'GET',
                data: {
                    branch_id: branch_id
                },
                success: function(data) {
                    $('#division_id').empty(); // clear old options

                    $('#division_id').append('<option value="">Select Division</option>');

                    $.each(data, function(key, value) {
                        $('#division_id').append('<option value="' + value.id +
                            '">' + value.name + '</option>');
                    });
                }
            });
        } else {
            $('#division_id').empty().append('<option value="">Select Division</option>');
        }
    });
});
</script>

<script>
const weekdays = ["Monday", "Tuesday", "Wednesday", "Thursday", "Friday", "Saturday", "Sunday"];
const workWeekInput = document.getElementById("work_week");
const weekendBox = document.getElementById("weekend-box");
const weekendSelect = document.getElementById("weekend");
const workdaysContainer = document.getElementById("workdays-container");
const weekendHint = document.getElementById("weekend-hint");


// Fill weekend selector with all 7 days
weekdays.forEach(day => {
    const option = document.createElement("option");
    option.value = day;
    option.text = day;
    weekendSelect.appendChild(option);
});

workWeekInput.addEventListener("input", function() {
    const workDaysCount = parseInt(this.value);
    $('#weekend').val(null).trigger('change');
    workdaysContainer.innerHTML = "";
    if (workDaysCount === 5) {
        weekendBox.style.display = "block";
        weekendHint.innerText = "{{ __trans('select_exactly_2_weekend_days') }}";
        $('#weekend').select2({
            maximumSelectionLength: 2
        });
        // weekendSelect.multiple = true;
    } else if (workDaysCount === 6) {
        weekendBox.style.display = "block";
        weekendHint.innerText = "{{ __trans('select_exactly_1_weekend_day') }}";
        $('#weekend').select2({
            maximumSelectionLength: 1
        });
        // weekendSelect.multiple = false;
    } else {
        weekendBox.style.display = "none";
        workdaysContainer.innerHTML = "";
    }
});

weekendSelect.addEventListener("change", function() {
    const selected = Array.from(this.selectedOptions).map(opt => opt.value);
    const workDaysCount = parseInt(workWeekInput.value);

        if ((workDaysCount === 5 && selected.length === 2) ||
            (workDaysCount === 6 && selected.length === 1)) {
            const workDays = weekdays.filter(day => !selected.includes(day));
            if (workdaysContainer.tagName === 'INPUT') {
            workdaysContainer.value = workDays.join(", ");
        } else {
            workdaysContainer.innerHTML = workDays.join(", ");
        }
        } else {
            workdaysContainer.innerHTML = "<span class='text-danger'>{{ __trans('please_select_correct_weekend_days') }}</span>";
        }
    });
    $(document).ready(function() {
        // Add new ticket row
        $('#addTicket').on('click', function() {
            const $newRow = $(createTicketRow());
            $('#air_ticket_container').append($newRow);
            $newRow.find('input').first().focus();
        });

    // Remove ticket row
    $('#air_ticket_container').on('click', '.removeTicket', function() {
        $(this).closest('.ticket-row').remove();
        reindexTickets();
    });

    // Create new ticket row HTML
    function createTicketRow() {
        const nextIndex = $('#air_ticket_container .ticket-row').length;
        const titleId = `title_${nextIndex}`;
        const percentageId = `percentage_${nextIndex}`;
        const qtyId = `qty_${nextIndex}`;

        return `
        <div class="col-12 ticket-row" data-index="${nextIndex}">
            <div class="row g-2 align-items-end">
                <div class="col-md-4">
                    <div class="mb-3">
                        <label for="${titleId}" class="form-label">Title</label>
                        <input type="text" name="tickets[${nextIndex}][title]" id="${titleId}" class="form-control" placeholder="Ticket title / description">
                    </div>
                </div>

                  <div class="col-md-4">
                    <div class="mb-3">
                       <label for="${qtyId}" class="form-label">Qty</label>
                        <input type="number" step="1" name="tickets[${nextIndex}][qty]" id="${qtyId}" class="form-control" placeholder="1">

                    </div>
                </div>

                  <div class="col-md-3">
                    <div class="mb-3">
                        <label for="${percentageId}" class="form-label">Percentage</label>
                        <input type="number" step="0.01" name="tickets[${nextIndex}][percentage]" id="${percentageId}" class="form-control" placeholder="0.00%">
                    </div>
                </div>



                <div class="col-md-1">
                    <div class="mb-3">
                        <button type="button" class="btn btn-danger removeTicket">-</button>
                    </div>
                </div>
            </div>
        </div>
        `;
    }

    // Reindex rows after removal
    function reindexTickets() {
        $('#air_ticket_container .ticket-row').each(function(i) {
            const $row = $(this);
            $row.attr('data-index', i);

            // Title
            const $title = $row.find('input[name*="[title]"]');
            $title.attr('name', `tickets[${i}][title]`);
            $title.attr('id', `title_${i}`);
            $row.find(`label[for*="title_"]`).attr('for', `title_${i}`);

            // Percentage
            const $perc = $row.find('input[name*="[percentage]"]');
            $perc.attr('name', `tickets[${i}][percentage]`);
            $perc.attr('id', `percentage_${i}`);
            $row.find(`label[for*="percentage_"]`).attr('for', `percentage_${i}`);
        });
    }
});

// Auto-populate grade when designation changes
$('#designations').on('select2:select', function(e) {
    var designationId = e.params.data.id;
    if (designationId && designationId !== 'NA') {
        // Fetch designation details including grade
        $.ajax({
            url: '{{ route("ajax.get.designation.grade") }}',
            type: 'GET',
            data: {
                designation_id: designationId
            },
            success: function(response) {
                if (response.success) {
                    $('#grade').val(response.grade || '');
                }
            },
            error: function() {
                console.log('Error fetching designation grade');
                $('#grade').val('');
            }
        });
    } else {
        $('#grade').val('');
    }
});
</script>
<script>
function calculateProbationEndDate() {
    const joiningDate = document.getElementById('date_of_joining').value;
    const probationMonths = document.getElementById('probation_month').value;

    if (!joiningDate || !probationMonths) {
        document.getElementById('probation_end_date').value = '';
        return;
    }

    let parts, date;

    // Handle YYYY-MM-DD
    if (/^\d{4}-\d{2}-\d{2}$/.test(joiningDate)) {
        parts = joiningDate.split('-');
        date = new Date(parts[0], parts[1] - 1, parts[2]);
    }
    // Handle DD-MM-YYYY
    else if (/^\d{2}-\d{2}-\d{4}$/.test(joiningDate)) {
        parts = joiningDate.split('-');
        date = new Date(parts[2], parts[1] - 1, parts[0]);
    } else {
        return;
    }

    date.setMonth(date.getMonth() + parseInt(probationMonths));

    const year = date.getFullYear();
    const month = String(date.getMonth() + 1).padStart(2, '0');
    const day = String(date.getDate()).padStart(2, '0');

    // ✅ d/m/Y format - Updated by Sanket
    document.getElementById('probation_end_date').value =
        `${day}/${month}/${year}`;
}

document.getElementById('date_of_joining')
    .addEventListener('change', calculateProbationEndDate);

document.getElementById('probation_month')
    .addEventListener('input', calculateProbationEndDate);
</script>


@endpush
