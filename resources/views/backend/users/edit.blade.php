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
                    <h3 class="page-title">{{__trans('edit_employee')}} : {{$user->name}}</h3>
                    <ul class="breadcrumb">
                        <li class="breadcrumb-item"><a
                                href="{{route('backend.dashboard')}}">{{__trans('dashboard')}}</a></li>
                        <li class="breadcrumb-item"><a
                                href="{{route('backend.users.index')}}">{{__trans('employee_list')}}</a></li>
                        <li class="breadcrumb-item active">{{__trans('edit_employee')}}</li>
                    </ul>
                </div>
            </div>
        </div>
        <!-- /Page Header -->

        <div class="page-content">
            <form action="{{route('backend.users.update',$user)}}" enctype="multipart/form-data"
                class="ajax-form-submit reset" redirect method="POST">
                @method('PUT')
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
                                            <input type="text" class="form-control" name="first_name"
                                                value="{{$user->first_name}}">
                                        </div>
                                    </div>
                                    <div class="col-lg-6">
                                        <div class="form-group">
                                            <label>{{__trans('last_name')}}</label>
                                            <input type="text" class="form-control" name="last_name"
                                                value="{{$user->last_name}}">
                                        </div>
                                    </div>
                                    <div class="col-lg-6">
                                        <div class="form-group">
                                            <label>{{__trans('dob')}}</label>
                                            <input type="text" class="form-control datepicker" name="date_of_birth"
                                                value="{{ $user->profile ? $user->profile->date_of_birth->format('d/m/Y') : NULL}}">
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
                                                    value="{{$country['id']}}" @if(!empty($user->profile) &&
                                                    $user->profile->country_id == $country['id']) selected
                                                    @endif>{{$country['name']}}</option>
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
                                                        id="gender_male" value="Male" @if(!empty($user->profile) &&
                                                    $user->profile->gender =='Male') checked @endif>
                                                    <label class="form-check-label" for="gender_male">
                                                        {{__trans('male')}}
                                                    </label>
                                                </div>
                                                <div class="form-check form-check-inline">
                                                    <input class="form-check-input" type="radio" name="gender"
                                                        id="gender_female" value="Female" @if(!empty($user->profile) &&
                                                    $user->profile->gender =='Female') checked @endif>
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
                                                <option>{{__trans('select_a_option')}}</option>
                                                @foreach (\App\Enums\MartialStatus::cases() as $status)
                                                <option value="{{$status->value}}" @if(!empty($user->profile) &&
                                                    $user->profile->martial_status->value == $status->value) selected
                                                    @endif>{{$status->name}}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                    </div>
                                    <div class="col-lg-6">
                                        <div class="form-group">
                                            <label>Work Email</label>
                                            <input type="email" class="form-control" name="email"
                                                value="{{$user->email}}">
                                        </div>
                                    </div>
                                    <div class="col-lg-6">
                                        <div class="form-group">
                                            <label>{{__trans('work_phone')}}</label>
                                            <input type="text" class="form-control" name="phone"
                                                value="{{$user->phone}}">
                                        </div>
                                    </div>
                                    <div class="col-lg-6">
                                        <div class="form-group">
                                            <label>{{__trans('personal_email')}}</label>
                                            <input type="email" class="form-control" name="personal_email"
                                                value="{{ $user->profile ? $user->profile->personal_email : ''}}">
                                        </div>
                                    </div>
                                    <div class="col-lg-6">
                                        <div class="form-group">
                                            <label>{{__trans('emp_id')}}</label>
                                            <input type="text" class="form-control" name="emp_id"
                                                value="{{$user->profile ? $user->employee_id : ''}}">
                                        </div>
                                    </div>
                                     <div class="col-lg-6">
                                        <div class="form-group">
                                            <label>{{__trans('biometric_user_id')}}</label>
                                            <input type="number" class="form-control" name="biometric_user_id"
                                                value="{{$user->profile ? $user->biometric_user_id : ''}}">
                                        </div>
                                    </div>
                                    <div class="col-lg-6">
                                        <div class="form-group">
                                            <label>{{__trans('personal_phone')}}</label>
                                            <input type="tel" class="form-control" name="personal_phone"
                                                value="{{$user->profile ? $user->profile->personal_phone: ''}}">
                                        </div>
                                    </div>
                                    <div class="col-lg-6">
                                        <div class="form-group">
                                            <label>Employee Status</label>
                                            <select name="status" class="form-control select">
                                                <option value="active" {{ $user->status == 'active' ? 'selected' : '' }}>Active</option>
                                                <option value="in-active" {{ $user->status == 'in-active' ? 'selected' : '' }}>Inactive</option>
                                                <option value="resigned" {{ $user->status == 'resigned' ? 'selected' : '' }}>Resigned</option>
                                                <option value="terminated" {{ $user->status == 'terminated' ? 'selected' : '' }}>Terminated</option>
                                            </select>
                                        </div>
                                    </div>
                                    <div class="col-lg-6">
                                        <div class="form-group">
                                            <label>{{ __trans('visa_category') }}</label>
                                            <select name="visa_category" class="form-control select-search">
                                                <option value="">{{ __trans('select_a_option') }}</option>

                                                @php
                                                $visa = old(
                                                'visa_category',
                                                $user->profile->visa_category ?? ''
                                                );
                                                @endphp

                                                <option value="golden_visa"
                                                    {{ $visa == 'golden_visa' ? 'selected' : '' }}>Golden Visa</option>
                                                <option value="company_sponsored"
                                                    {{ $visa == 'company_sponsored' ? 'selected' : '' }}>Company
                                                    Sponsored Visa</option>
                                                <option value="family_sponsored"
                                                    {{ $visa == 'family_sponsored' ? 'selected' : '' }}>Family Sponsored
                                                    Visa</option>
                                                <option value="partner_investor"
                                                    {{ $visa == 'partner_investor' ? 'selected' : '' }}>Partner /
                                                    Investor Visa</option>
                                                <option value="freelance" {{ $visa == 'freelance' ? 'selected' : '' }}>
                                                    Freelance Visa</option>
                                                <option value="student" {{ $visa == 'student' ? 'selected' : '' }}>
                                                    Student Visa</option>
                                                <option value="visit" {{ $visa == 'visit' ? 'selected' : '' }}>Visit
                                                    Visa</option>
                                                    <option value="work_permit"
                                                    {{ $visa == 'work_permit' ? 'selected' : '' }}>Work Permit</option>


                                            </select>
                                        </div>
                                    </div>
                                    <div class="col-lg-6">
                                        <div class="form-group">
                                            <label>{{__trans('visa_type')}}</label>
                                            <input type="text" class="form-control" name="visa_type" value="{{ $user->profile ? $user->profile->visa_type : '' }}">
                                        </div>
                                    </div>
                                    <div class="col-lg-6">
                                        <div class="form-group">
                                            <label>{{__trans('visa_designation')}}</label>
                                            <input type="text" class="form-control" name="visa_designation" value="{{ $user->profile ? $user->profile->visa_designation : '' }}">
                                        </div>
                                    </div>


                                    <div class="col-lg-6">
                                        <div class="form-group">
                                            <label>{{__trans('address')}}</label>
                                            <textarea rows="5" name="address" cols="5" class="form-control"
                                                placeholder="938 Green Acres Road">{{ $user->profile ? $user->profile->address : ""}}</textarea>
                                        </div>
                                    </div>
                                    <div class="col-lg-6">
                                        <label>{{__trans('image')}}</label>
                                        <div class="d-flex align-items-center">
                                            <label class="avatar avatar-xxl profile-cover-avatar m-0" for="edit_img">
                                                <img id="avatarImg" class="avatar-img"
                                                    src="{{ $user->getProfileImage() }}"
                                                    alt="Profile Image">
                                                <input type="file" name="profile_image" id="profile-image"
                                                    accept="image/*"
                                                    onchange="previewImage('profile-image','avatarImg')">
                                                <span class="avatar-edit" onclick="$('#profile-image').click()">
                                                    <i data-feather="edit-2"
                                                        class="avatar-uploader-icon shadow-soft"></i>
                                                </span>
                                            </label>
                                        </div>
                                    </div>


                                </div>
                            </div>
                        </div>
@if(false) {{-- Commented out by Sanket as per user request --}}
                        <div class="card bg-white">
                            <div class="card-header">
                                <h5 class="card-title"> {{__trans('passport_details')}}</h5>
                            </div>
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-lg-6">
                                        <div class="form-group">
                                            <label>{{__trans('passport_number')}}</label>
                                            <input type="text" class="form-control" name="passport_number" value="{{ $passport ? $passport->serial_number : '' }}">
                                        </div>
                                    </div>
                                    <div class="col-lg-6">
                                        <div class="form-group">
                                            <label>{{__trans('passport_country')}}</label>
                                            <select name="passport_country" class="form-control select-search flag_country">
                                                <option value="">{{__trans('select_a_option')}}</option>
                                                @foreach (getCountryListwithFlag() as $country)
                                                <option data-flag="{{ $country['flag_url'] }}"
                                                    value="{{$country['name']}}" @if($passport && $passport->country_name == $country['name']) selected @endif>{{$country['name']}}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                    </div>
                                    <div class="col-lg-4">
                                        <div class="form-group">
                                            <label>{{__trans('passport_issue_date')}}</label>
                                            <input type="text" class="form-control datepicker" name="passport_issue_date" value="{{ $passport && $passport->issue_date ? \Carbon\Carbon::parse($passport->issue_date)->format('d/m/Y') : '' }}">
                                        </div>
                                    </div>
                                    <div class="col-lg-4">
                                        <div class="form-group">
                                            <label>{{__trans('passport_expiry_date')}}</label>
                                            <input type="text" class="form-control datepicker" name="passport_expiry_date" value="{{ $passport && $passport->expiry_date ? \Carbon\Carbon::parse($passport->expiry_date)->format('d/m/Y') : '' }}">
                                        </div>
                                    </div>
                                    <div class="col-lg-4">
                                        <div class="form-group">
                                            <label>{{__trans('passport_place_of_issue')}}</label>
                                            <input type="text" class="form-control" name="passport_place_of_issue" value="{{ $passport ? $passport->place_of_issue : '' }}">
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
                                            <input type="text" class="form-control" name="visa_number" value="{{ $visa ? $visa->serial_number : '' }}">
                                        </div>
                                    </div>
                                    <div class="col-lg-6">
                                        <div class="form-group">
                                            <label>{{__trans('visa_country')}}</label>
                                            <select name="visa_country" class="form-control select-search flag_country">
                                                <option value="">{{__trans('select_a_option')}}</option>
                                                @foreach (getCountryListwithFlag() as $country)
                                                <option data-flag="{{ $country['flag_url'] }}"
                                                    value="{{$country['name']}}" @if($visa && $visa->country_name == $country['name']) selected @endif>{{$country['name']}}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                    </div>
                                    <div class="col-lg-4">
                                        <div class="form-group">
                                            <label>{{__trans('visa_issue_date')}}</label>
                                            <input type="text" class="form-control datepicker" name="visa_issue_date" value="{{ $visa && $visa->issue_date ? \Carbon\Carbon::parse($visa->issue_date)->format('d/m/Y') : '' }}">
                                        </div>
                                    </div>
                                    <div class="col-lg-4">
                                        <div class="form-group">
                                            <label>{{__trans('visa_expiry_date')}}</label>
                                            <input type="text" class="form-control datepicker" name="visa_expiry_date" value="{{ $visa && $visa->expiry_date ? \Carbon\Carbon::parse($visa->expiry_date)->format('d/m/Y') : '' }}">
                                        </div>
                                    </div>
                                    <div class="col-lg-4">
                                        <div class="form-group">
                                            <label>{{__trans('visa_place_of_issue')}}</label>
                                            <input type="text" class="form-control" name="visa_place_of_issue" value="{{ $visa ? $visa->place_of_issue : '' }}">
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
                                            <input type="text" class="form-control" name="labor_card_number" value="{{ $laborCard ? $laborCard->serial_number : '' }}">
                                        </div>
                                    </div>
                                    <div class="col-lg-6">
                                        <div class="form-group">
                                            <label>{{__trans('labor_card_personal_number')}}</label>
                                            <input type="text" class="form-control" name="labor_card_personal_number" value="{{ $laborCard ? $laborCard->ministry_of_labor_personal_no : '' }}">
                                        </div>
                                    </div>
                                    <div class="col-lg-6">
                                        <div class="form-group">
                                            <label>{{__trans('labor_card_issue_date')}}</label>
                                            <input type="text" class="form-control datepicker" name="labor_card_issue_date" value="{{ $laborCard && $laborCard->issue_date ? \Carbon\Carbon::parse($laborCard->issue_date)->format('d/m/Y') : '' }}">
                                        </div>
                                    </div>
                                    <div class="col-lg-6">
                                        <div class="form-group">
                                            <label>{{__trans('labor_card_expiry_date')}}</label>
                                            <input type="text" class="form-control datepicker" name="labor_card_expiry_date" value="{{ $laborCard && $laborCard->expiry_date ? \Carbon\Carbon::parse($laborCard->expiry_date)->format('d/m/Y') : '' }}">
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
                                            <input type="text" class="form-control" name="emirates_id_number" value="{{ $emiratesId ? $emiratesId->serial_number : '' }}">
                                        </div>
                                    </div>
                                    <div class="col-lg-6">
                                        <div class="form-group">
                                            <label>{{__trans('emirates_id_issue_date')}}</label>
                                            <input type="text" class="form-control datepicker" name="emirates_id_issue_date" value="{{ $emiratesId && $emiratesId->issue_date ? \Carbon\Carbon::parse($emiratesId->issue_date)->format('d/m/Y') : '' }}">
                                        </div>
                                    </div>
                                    <div class="col-lg-6">
                                        <div class="form-group">
                                            <label>{{__trans('emirates_id_expiry_date')}}</label>
                                            <input type="text" class="form-control datepicker" name="emirates_id_expiry_date" value="{{ $emiratesId && $emiratesId->expiry_date ? \Carbon\Carbon::parse($emiratesId->expiry_date)->format('d/m/Y') : '' }}">
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
                                <h5 class="card-title"> {{__trans('work_details')}}</h5>
                            </div>
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-lg-6">
                                        <div class="form-group">
                                            <label>{{__trans('branch')}}</label>
                                            <select name="department_id" class="ajax-select2" id="department"
                                                data-target="{{route('ajax.select2.fetch.departments')}}">
                                                <option
                                                    value="{{ isset($user->department) ? $user->department->id : '' }}">
                                                    {{ isset($user->department) ? $user->department?->name ?? 'NA' : 'Not Selected' }}
                                                </option>
                                            </select>
                                        </div>
                                    </div>
                                    <!-- <div class="col-lg-6">
                                        <div class="form-group">
                                            <label>{{__trans('department')}}</label>
                                            <select name="division_id" class="form-control select" id="division_id">

                                            </select>
                                        </div>
                                    </div> -->
                                    <div class="col-lg-6">
                                        <div class="form-group">
                                            <label>{{__trans('department')}}</label>
                                            <select name="division_id" class="ajax-select2" id="division_id1"
                                                data-target="{{route('ajax.select2.fetch.divisions')}}"
                                                data-dependent="department_id">
                                                <option
                                                    value="{{ isset($user->division) ? $user->division->id : '' }}">
                                                    {{ isset($user->division) ? $user->division->name : 'Not Selected' }}
                                                </option>


                                            </select>
                                        </div>
                                    </div>
                                    <div class="col-lg-6">
                                        <div class="form-group">
                                            <label for="">{{__trans('designation')}}</label>
                                            <select name="designation_id" class="ajax-select2" id="designations"
                                                data-target="{{route('ajax.select2.fetch.designations')}}"
                                                data-dependent="department_id">
                                                <option
                                                    value="{{$user->designation_id ? $user->designation_id : 'NA'}}">
                                                    {{$user->designation_id ? $user->designation->name : 'NA'}}
                                                </option>
                                            </select>
                                        </div>
                                    </div>
                                    <div class="col-lg-6">
                                        <div class="form-group">
                                            <label>{{__trans('grade')}}</label>
                                            <input type="text" class="form-control" id="grade" name="grade"
                                                value="{{ $user->designation ? $user->designation->grade : (isset($user->workDetail) ? $user->workDetail->grade : '') }}">
                                        </div>
                                    </div>
                                    <div class="col-lg-6">
                                        <div class="form-group">
                                            <label>{{__trans('role')}}</label>
                                            <select name="role_id" class="ajax-select2" id="role_id"
                                                data-target="{{route('ajax.select2.fetch.roles')}}">
                                                <option value="{{ isset($current_role->id) ? $current_role->id : '' }}">

                                                    {{ isset($current_role->name) ? ucwords($current_role->name) : 'Not Selected' }}
                                                </option>
                                            </select>

                                        </div>
                                    </div>

                                    <div class="col-lg-6">
                                        <div class="form-group">
                                            <label>MOL Location</label>
                                            <select name="company_document_id" class="ajax-select2"
                                                id="company_document_id"
                                                data-target="{{route('ajax.select2.fetch.companydocument')}}">
                                                <option
                                                    value="{{$user->company_document_id ? $user->company_document_id : 'NA'}}">
                                                    {{$user->company_document_id ? $user->companyDocument->legal_trade_name : 'NA'}}
                                                </option>
                                            </select>


                                        </div>
                                    </div>
                                    <div class="col-lg-12">
                                        <div class="form-group">
                                            <label>{{__trans('company_name')}}</label>
                                            <input type="text" class="form-control" name="company_name"
                                                value="{{!empty($user->workDetail->company_name) ? $user->workDetail->company_name : ''}}">
                                        </div>
                                    </div>
                                    
                                    <div class="col-lg-6">
                                        <div class="form-group">
                                            <label>{{__trans('date_of_joining')}}</label>
                                            <input type="text" class="form-control datepicker"
                                                value="{{!empty($user->workDetail->joining_date) ?  $user->workDetail->joining_date->format('d/m/Y') : ''}}"
                                                name="date_of_joining" id="date_of_joining">
                                        </div>
                                    </div>
                                    <div class="col-lg-6">
                                        <div class="form-group">
                                            <label>{{ __trans('probation_month') }}</label>
                                            <input type="number" min="0" max="24" class="form-control"
                                                name="probation_month" id="probation_month"
                                                value="{{ old('probation_month', $user->workDetail->probation_month ?? '') }}">
                                        </div>
                                    </div>
                                    <div class="col-lg-6">
                                        <div class="form-group">
                                            <label>{{__trans('probation_end_date')}}</label>
                                            <input type="text" class="form-control "
                                                value="{{!empty($user->workDetail->probation_end_date) ? $user->workDetail->probation_end_date->format('d/m/Y') : ''}}"
                                                name="probation_end_date" id="probation_end_date" readonly>
                                        </div>
                                    </div>
                                    <div class="col-lg-6">
                                        <div class="form-group">
                                            <label>{{__trans('last_working_day')}}</label>
                                            <input type="text" class="form-control datepicker"
                                                value="{{!empty($user->workDetail->last_working_day) ? $user->workDetail->last_working_day->format('d/m/Y') : ''}}"
                                                name="last_working_day" id="last_working_day">
                                        </div>
                                    </div>
                                    <div class="col-lg-6">
                                        <div class="form-group">
                                            <label>{{__trans('report_to')}}</label>
                                            <select name="report_to_id[]" class="ajax-select2" id="report_to_id"
                                                data-target="{{ route('ajax.select2.fetch.users') }}" multiple>
                                                @if(isset($user->workDetail) && !empty($user->workDetail->report_to_ids))
                                                    @php
                                                        $reportToIds = is_array($user->workDetail->report_to_ids) ? $user->workDetail->report_to_ids : [$user->workDetail->report_to_ids];
                                                        $reportToUsers = \App\Models\User::whereIn('id', $reportToIds)->get();
                                                    @endphp
                                                    @foreach($reportToUsers as $reportUser)
                                                        <option value="{{ $reportUser->id }}" selected>{{ $reportUser->name }}</option>
                                                    @endforeach
                                                @endif
                                            </select>
                                        </div>
                                        <div class="form-group mt-2">
                                            <div class="form-check">
                                                <input type="checkbox" name="approved_first_level"
                                                    id="approved_first_level" class="form-check-input" value="1"
                                                    @if(isset($user->workDetail) &&
                                                !empty($user->workDetail->approved_first_level)) checked @endif>
                                                <label class="form-check-label" for="approved_first_level">Approved in
                                                    First Level</label>
                                            </div>
                                        </div>
                                    </div>
                                    <!-- <div class="col-lg-2">
                                        <div class="form-group">
                                            <label>{{__trans('work_week')}}</label>
                                            <input type="text" class="form-control" name="work_week"
                                                value="{{!empty($user->workDetail->work_week) ? $user->workDetail->work_week : ''}}">
                                        </div>
                                    </div> -->
                                    @php
                                    $weekdays =
                                    ["Monday","Tuesday","Wednesday","Thursday","Friday","Saturday","Sunday"];
                                    $workWeek = isset($user->workDetail) ? ($user->workDetail->work_week ?? '') : '';
                                    $selectedWeekends = (isset($user->workDetail) && !empty($user->workDetail->weekend))
                                    ? explode(',', $user->workDetail->weekend)
                                    : [];
                                    @endphp

                                    <div class="col-lg-2">
                                        <div class="form-group">
                                            <label>{{ __trans('work_week') }}</label>
                                            <input type="number" min="1" max="7" class="form-control" name="work_week"
                                                id="work_week" value="{{ $workWeek }}">
                                        </div>
                                    </div>

                                    <div class="col-lg-4" id="weekend-box"
                                        style="{{ ($workWeek && $workWeek < 7) ? '' : 'display:none;' }}">
                                        <div class="form-group">
                                            <label>{{ __trans('weekend_days') }}</label>
                                            <select id="weekend" name="weekend[]" class="form-control select-search"
                                                multiple>
                                                @foreach ($weekdays as $day)
                                                <option value="{{ $day }}"
                                                    {{ in_array($day, $selectedWeekends) ? 'selected' : '' }}>
                                                    {{ $day }}
                                                </option>
                                                @endforeach
                                            </select>
                                            <small id="weekend-hint" class="form-text text-muted"></small>
                                        </div>
                                    </div>

                                    <div class="col-lg-6">
                                        <div class="form-group">
                                            <label>{{ __trans('calculated_workdays') }}</label>
                                            <input type="text" id="workdays-container" name="calculated_workdays" class="form-control" style="height:auto; min-height:38px;" value="@if (!empty($selectedWeekends)){{ implode(', ', array_diff($weekdays, $selectedWeekends)) }}@endif">
                                        </div>
                                    </div>

                                    <div class="col-lg-6">
                                        <div class="form-group">
                                            <label>{{__trans('location')}}</label>
                                            <input type="text" class="form-control" name="location"
                                                value="{{!empty($user->workDetail->location) ? $user->workDetail->location : ''}}">
                                        </div>
                                    </div>
                                    <div class="col-lg-6">
                                        <div class="form-group">
                                            <label>{{__trans('mol_number')}}</label>
                                            <input type="text" class="form-control" name="mol_number"
                                                value="{{!empty($user->workDetail->mol_number) ? $user->workDetail->mol_number : ''}}">
                                        </div>
                                    </div>
                                    <div class="col-lg-6">
                                        <div class="form-group">
                                            <label>{{__trans('entity')}}</label>
                                            <input type="text" class="form-control" name="entity"
                                                value="{{!empty($user->workDetail->entity) ? $user->workDetail->entity : ''}}">
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
                                                    {{ (isset($user->workDetail) && $user->workDetail->medical_insurance_provided == '0') ? 'selected' : '' }}>
                                                    No
                                                </option>
                                                <option value="1"
                                                    {{ (isset($user->workDetail) && $user->workDetail->medical_insurance_provided == '1') ? 'selected' : '' }}>
                                                    Yes
                                                </option>
                                            </select>
                                        </div>
                                    </div>

                                    <div class="col-lg-12 row" id="insurance_details_div"
                                        style="{{ (isset($user->workDetail) && $user->workDetail->medical_insurance_provided==1) ? 'display: flex' : 'display: none' }}">
                                        <div class="col-lg-6">
                                            <div class="form-group">
                                                <label for="annual_premium">Annual Premium:</label>
                                                <input type="number" id="annual_premium" name="annual_premium"
                                                    class="form-control" min="0" step="0.01"
                                                    value="{{ isset($user->workDetail) ? $user->workDetail->annual_premium : '' }}">
                                            </div>
                                        </div>
                                        <div class="col-lg-6">
                                            <div class="form-group">
                                                <label>{{__trans('insurance_number')}}</label>
                                                <input type="text" class="form-control" name="insurance_number"
                                                    value="{{ isset($user->workDetail) ? $user->workDetail->insurance_number : '' }}">
                                            </div>
                                        </div>
                                        <div class="col-lg-6">
                                            <div class="form-group">
                                                <label>{{__trans('insurance_expiry')}}</label>
                                                <input type="text" class="form-control datepicker"
                                                    value="{{!empty($user->workDetail->insurance_expiry) ? $user->workDetail->insurance_expiry->format('d/m/Y') : ''}}"
                                                    name="insurance_expiry">
                                            </div>
                                        </div>
                                    </div>
                                    <script>
                                        function togglePremiumInput() {
                                            var statusElement = document.getElementById('medical_insurance_provided');
                                            var div = document.getElementById('insurance_details_div');
                                            if (statusElement && div) {
                                                var status = statusElement.value;
                                                if(status == '1') {
                                                    div.style.display = 'flex';
                                                } else {
                                                    div.style.display = 'none';
                                                }
                                            }
                                        }
                                        document.addEventListener("DOMContentLoaded", function() {
                                            togglePremiumInput();
                                        });
                                    </script>
                                    @endif



                                    <div class="col-lg-6">
                                        <div class="form-group">
                                            <label for="air_ticket_count">{{__trans('air_ticket_count')}}:</label>
                                            <input type="number" id="air_ticket_count" name="air_ticket_count"
                                                value="{{ isset($user->workDetail) ? $user->workDetail->air_ticket_count : '' }}"
                                                class="form-control" min="0" step="1">
                                        </div>
                                    </div>

                                    <div class="col-lg-6">
                                        <div class="form-group">
                                            <label>{{__trans('renewal_air_ticket')}}</label>
                                            <select name="renewal_air_ticket" class="form-control select-search">
                                                <option value="">{{__trans('select_a_option')}}</option>
                                                <option value="1_year"
                                                    {{ (isset($user->workDetail) && $user->workDetail->renewal_air_ticket == "1_year") ? "selected" : "" }}>
                                                    {{__trans('1_year')}}</option>
                                                <option value="2_year"
                                                    {{ (isset($user->workDetail) && $user->workDetail->renewal_air_ticket == "2_year") ? "selected" : "" }}>
                                                    {{__trans('2_year')}}</option>

                                            </select>
                                        </div>
                                    </div>

                                    <div class="col-lg-6">
                                        <div class="form-group">
                                            <label>{{__trans('salary_mode')}}</label>
                                            <select name="salary_mode" class="form-control ">
                                                <option
                                                    {{ (isset($user->workDetail) && $user->workDetail->salary_mode == 'account') ? 'selected' : '' }}
                                                    value="account">Account</option>
                                                <option
                                                    {{ (isset($user->workDetail) && $user->workDetail->salary_mode == 'cash') ? 'selected' : '' }}
                                                    value="cash">Cash</option>

                                            </select>
                                        </div>
                                    </div>



                                    <div class="row g-3" id="ticket_container">
                                        @if(count($user->airTicketsDetail))
                                            <div class="col-12">
                                                <h6 class="text-muted mb-2">{{__trans('existing_tickets')}}</h6>
                                            </div>
                                            @foreach($user->airTicketsDetail as $index => $ticket)
                                                <input type="hidden" name="tickets[{{$index}}][id]" value="{{$ticket->id}}">
                                                <div class="col-12 ticket-row border-bottom pb-3 mb-2" data-index="{{$index}}">
                                                    <div class="row g-2 align-items-end">
                                                        <div class="col-md-4">
                                                            <div class="form-group mb-0">
                                                                <label class="form-label small">{{__trans('title')}}</label>
                                                                <input type="text" name="tickets[{{$index}}][title]"
                                                                    class="form-control" value="{{$ticket->title}}" 
                                                                    placeholder="Ticket title / description">
                                                            </div>
                                                        </div>
                                                        <div class="col-md-3">
                                                            <div class="form-group mb-0">
                                                                <label class="form-label small">{{__trans('qty')}}</label>
                                                                <input type="number" name="tickets[{{$index}}][qty]"
                                                                    class="form-control" value="{{$ticket->qty}}" 
                                                                    placeholder="Qty">
                                                            </div>
                                                        </div>
                                                        <div class="col-md-3">
                                                            <div class="form-group mb-0">
                                                                <label class="form-label small">{{__trans('percentage')}}</label>
                                                                <input type="number" step="0.01" name="tickets[{{$index}}][percentage]"
                                                                    class="form-control" value="{{$ticket->percentage}}" 
                                                                    placeholder="0.00%">
                                                            </div>
                                                        </div>
                                                        <div class="col-md-2">
                                                            <div class="d-flex gap-2">
                                                                <input type="hidden" name="tickets[{{$index}}][_delete]" value="0">
                                                                <button type="button" class="btn btn-outline-danger btn-sm removeTicket" title="Remove">
                                                                    <i class="fa fa-trash"></i>
                                                                </button>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            @endforeach
                                        @endif

                                        <div class="col-12 mt-3">
                                            <h6 class="text-muted mb-2">{{__trans('add_new_ticket')}}</h6>
                                        </div>
                                        <div class="col-12 ticket-row" data-index="{{ count($user->airTicketsDetail) }}">
                                            <div class="row g-2 align-items-end">
                                                <div class="col-md-4">
                                                    <div class="form-group mb-0">
                                                        <label class="form-label small">{{__trans('title')}}</label>
                                                        <input type="text" name="create_tickets[{{ count($user->airTicketsDetail) }}][title]" 
                                                            class="form-control" placeholder="New ticket title">
                                                    </div>
                                                </div>
                                                <div class="col-md-3">
                                                    <div class="form-group mb-0">
                                                        <label class="form-label small">{{__trans('qty')}}</label>
                                                        <input type="number" name="create_tickets[{{ count($user->airTicketsDetail) }}][qty]" 
                                                            class="form-control" placeholder="Qty">
                                                    </div>
                                                </div>
                                                <div class="col-md-3">
                                                    <div class="form-group mb-0">
                                                        <label class="form-label small">{{__trans('percentage')}}</label>
                                                        <input type="number" step="0.01" name="create_tickets[{{ count($user->airTicketsDetail) }}][percentage]" 
                                                            class="form-control" placeholder="0.00%">
                                                    </div>
                                                </div>
                                                <div class="col-md-2">
                                                    <button type="button" class="btn btn-outline-success btn-sm w-100" id="addTicket">
                                                        <i class="fa fa-plus"></i> {{__trans('add')}}
                                                    </button>
                                                </div>
                                            </div>
                                        </div>
                                    </div>


                                    <div class="col-lg-6">
                                        <div class="form-group">
                                            <label>{{__trans('air_ticket_policy')}}</label>
                                            <div class="d-flex align-items-center gap-3">
                                                <select name="air_ticket_setting_id" class="form-control select-search mb-0">
                                                    <option value="">{{__trans('select_a_option')}}</option>
                                                    @foreach (getAirTicketSettingsList() as $AirTicketSetting)
                                                    <option
                                                        {{ (isset($user->workDetail) && $user->workDetail->air_ticket_setting_id == $AirTicketSetting->id) ? 'selected' : '' }}
                                                        value="{{$AirTicketSetting->id}}">{{$AirTicketSetting->policy_name}}
                                                    </option>
                                                    @endforeach
                                                </select>
                                                <div class="form-check mb-0">
                                                    <input class="form-check-input" type="checkbox" name="is_rider"
                                                        id="is_rider" value="1" @if(isset($user->workDetail) &&
                                                    !empty($user->workDetail) &&
                                                    $user->workDetail->is_rider ==1) checked @endif>
                                                    <label class="form-check-label text-nowrap" for="is_rider">
                                                        {{__trans('is_rider')}}
                                                    </label>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-lg-6">
                                        <div class="form-group">
                                            <label>{{__trans('attendance_base')}}</label>
                                            <select name="attendance_base" class="form-control ">
                                                <option
                                                    {{ (isset($user->workDetail) && $user->workDetail->attendance_base == 'yes') ? 'selected' : '' }}
                                                    value="yes">Yes</option>
                                                <option
                                                    {{ (isset($user->workDetail) && $user->workDetail->attendance_base == 'no') ? 'selected' : '' }}
                                                    value="no">No</option>
                                            </select>
                                        </div>
                                    </div>

                                    <div class="col-lg-6">
                                        <div class="form-group">
                                            <label>{{__trans('company_accommodation')}}</label>
                                            <select name="company_accommodation" id="company_accommodation"
                                                class="form-control ">
                                                <option value="">{{__trans('select_a_option')}}</option>
                                                <option
                                                    {{ (isset($user->workDetail) && $user->workDetail->company_accommodation == 'yes') ? 'selected' : '' }}
                                                    value="yes">Yes</option>
                                                <option
                                                    {{ (isset($user->workDetail) && $user->workDetail->company_accommodation == 'no') ? 'selected' : '' }}
                                                    value="no">No</option>
                                            </select>
                                        </div>
                                    </div>
                                    <div class="col-lg-12" style="display: none" id="div_accommodation_location">
                                        <div class="form-group">
                                            <label>{{__trans('accommodation_location')}}</label>
                                            <input class="form-control" type="text" name="accommodation_location"
                                                id="accommodation_location"
                                                value="{{ isset($user->workDetail) ? $user->workDetail->accommodation_location : '' }}">
                                        </div>
                                    </div>
                                    <div class="col-lg-6">
                                        <div class="form-group">
                                            <label
                                                for="free_document_request">{{__trans('free_document_request')}}:</label>
                                            <input type="number" id="free_document_request" name="free_document_request"
                                                value="{{ isset($user->workDetail) ? $user->workDetail->free_document_request : '' }}"
                                                class="form-control" min="0" step="1">
                                        </div>
                                    </div>

                                    <div class="col-lg-6">
                                        <div class="form-group">
                                            <label
                                                for="document_request_charge">{{__trans('document_request_charge')}}:</label>
                                            <input type="number" id="document_request_charge"
                                                name="document_request_charge"
                                                value="{{ isset($user->workDetail) ? $user->workDetail->document_request_charge : '' }}"
                                                class="form-control" min="0" step="1">
                                        </div>
                                    </div>
                                    <div class="col-lg-12">
                                        <div class="form-group">
                                            <label>{{__trans('remarks')}}</label>
                                            <textarea rows="3" name="remarks" class="form-control">{{ isset($user->workDetail) ? $user->workDetail->remarks : '' }}</textarea>
                                        </div>
                                    </div>

                                </div>
                                <div class="row" id="custom_container">
                                    @if(count($user->shifts))
                                    @foreach($user->shifts as $index => $shift)
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label for="shift_start_{{$index}}"
                                                class="form-label">{{__trans('shift_start')}}</label>
                                            <input type="text" name="shifts[{{$index}}][shift_start]"
                                                class="form-control timepicker" id="shift_start_{{$index}}"
                                                value="{{$shift->shift_start}}"
                                                placeholder="{{__trans('shift_start_time')}}">
                                        </div>
                                    </div>
                                    <div class="col-md-5">
                                        <div class="mb-3">
                                            <label for="shift_end_{{$index}}"
                                                class="form-label">{{__trans('shift_end')}}</label>
                                            <input type="text" name="shifts[{{$index}}][shift_end]"
                                                class="form-control timepicker" id="shift_end_{{$index}}"
                                                value="{{$shift->shift_end}}"
                                                placeholder="{{__trans('shift_end_time')}}">
                                        </div>
                                    </div>
                                    @endforeach
                                    @else
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label for="shift_start"
                                                class="form-label">{{__trans('shift_start')}}</label>
                                            <input type="text" name="create_shifts[0][shift_start]"
                                                class="form-control timepicker" id="shift_start"
                                                placeholder="{{__trans('shift_start_time')}}">
                                        </div>
                                    </div>
                                    <div class="col-md-5">
                                        <div class="mb-3">
                                            <label for="shift_end" class="form-label">{{__trans('shift_end')}}</label>
                                            <input type="text" name="create_shifts[0][shift_end]"
                                                class="form-control timepicker" id="shift_end"
                                                placeholder="{{__trans('shift_end_time')}}">
                                        </div>
                                    </div>
                                    @endif
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
                                            <input
                                                value="{{$user->emergencyContacts? $user->emergencyContacts->emergency_name : ''}}"
                                                type="text" class="form-control" name="emergency_name">
                                        </div>
                                    </div>
                                    <div class="col-lg-6">
                                        <div class="form-group">
                                            <label>{{__trans('local_person_name')}}</label>
                                            <input type="text" class="form-control" name="local_person_name"
                                                value="{{$user->emergencyContacts? $user->emergencyContacts->local_person_name : ''}}">
                                        </div>
                                    </div>
                                    <div class="col-lg-6">
                                        <div class="form-group">
                                            <label>{{__trans('relation')}}</label>
                                            <select id="emergency_relation" name="emergency_relation"
                                                class="form-control select-search">

                                                <option value="">{{__trans('select_option')}}</option>
                                                @foreach (\App\Enums\Relation::cases() as $relation)
                                                @if($relation->value == 'wife' || $relation->value == 'husband')
                                                @else
                                                <option value="{{$relation->value}}" @if($user->emergencyContacts &&
                                                    $user->emergencyContacts->emergency_relation == $relation->value)
                                                    selected
                                                    @endif>{{$relation->name}}</option>
                                                @endif
                                                @endforeach

                                            </select>
                                        </div>
                                    </div>
                                    <div class="col-lg-6">
                                        <div class="form-group">
                                            <label>{{__trans('local_person_relation')}}</label>
                                            <select id="local_person_relation" name="local_person_relation"
                                                class="form-control select-search">

                                                <option value="">{{__trans('select_option')}}</option>
                                                @foreach (\App\Enums\Relation::cases() as $relation)
                                                @if($relation->value == 'wife' || $relation->value == 'husband')
                                                @else
                                                <option value="{{$relation->value}}" @if($user->emergencyContacts &&
                                                    $user->emergencyContacts->local_person_relation == $relation->value)
                                                    selected
                                                    @endif>{{$relation->name}}</option>
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
                                                <option value="{{ $isd['code'] }}" @if(!empty($user->emergencyContacts)
                                                    &&
                                                    $user->emergencyContacts->emergency_isd_code == $isd['code'])
                                                    selected
                                                    @endif

                                                    >{{ $isd['code'] }}
                                                    ({{ $isd['country'] }})</option>
                                                @endforeach
                                            </select>
                                        </div>
                                    </div>
                                    <div class="col-lg-6">
                                        <div class="form-group">
                                            <label>{{__trans('local_person_phone')}}</label>
                                            <input type="text" class="form-control" name="local_person_phone"
                                                value="{{$user->emergencyContacts ? $user->emergencyContacts->local_person_phone : ''}}">
                                        </div>
                                    </div>
                                    <div class="col-lg-6">
                                        <div class="form-group">
                                            <label>{{__trans('phone')}}</label>
                                            <input
                                                value="{{$user->emergencyContacts ? $user->emergencyContacts->emergency_phone : ''}}"
                                                type="text" class="form-control" name="emergency_phone">
                                        </div>
                                    </div>

                                    <div class="col-lg-12">
                                        <div class="form-group">
                                            <label>{{__trans('email')}}</label>
                                            <input
                                                value="{{$user->emergencyContacts ? $user->emergencyContacts->emergency_email : ''}}"
                                                type="email" class="form-control" name="emergency_email">
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
                                                    value="{{$country['name']}}" @if(!empty($user->emergencyContacts) &&
                                                    $user->emergencyContacts->emergency_home_country ==
                                                    $country['name']) selected
                                                    @endif
                                                    >{{$country['name']}}</option>
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
                                                    value="{{$country['name']}}" @if(!empty($user->emergencyContacts) &&
                                                    $user->emergencyContacts->emergency_local_country ==
                                                    $country['name']) selected
                                                    @endif
                                                    >{{$country['name']}}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                    </div>
                                    <div class="col-lg-6">
                                        <div class="form-group">
                                            <label>{{__trans('home_address')}}</label>
                                            <!-- <input type="text" class="form-control" name="home_address"> -->
                                            <textarea rows="5" name="emergency_home_address" cols="5"
                                                class="form-control"
                                                placeholder="938 Green Acres Road">{{$user->emergencyContacts ? $user->emergencyContacts->emergency_home_address : ''}}</textarea>
                                        </div>
                                    </div>



                                    <div class="col-lg-6">
                                        <div class="form-group">
                                            <label>{{__trans('local_address')}}</label>
                                            <!-- <input type="text" class="form-control" name="local_address"> -->
                                            <textarea rows="5" name="emergency_local_address" cols="5"
                                                class="form-control"
                                                placeholder="938 Green Acres Road">{{$user->emergencyContacts ? $user->emergencyContacts->emergency_local_address : ''}}</textarea>
                                        </div>
                                    </div>

                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-xl-6 col-6">
                        <div class="card bg-white">
                            <div class="card-header">
                                <h5 class="card-title">{{__trans('social_details')}}</h5>
                            </div>
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-lg-12">
                                        <div class="form-group">
                                            <label>{{__trans('linkedin_url')}}</label>
                                            <input type="text" class="form-control" name="linkedin_profile_url"
                                                value="{{$user->profile ? $user->profile->linkedin_url : ''}}">
                                        </div>
                                    </div>
                                    <div class="col-lg-12">
                                        <div class="form-group">
                                            <label>{{__trans('skills')}}</label>
                                            <input type="text" class="form-control" name="skills"
                                                value="{{$user->profile ? $user->profile->skills : ''}}">
                                            <!-- <textarea rows="9" cols="5" class="form-control" name="skills">{{$user->profile ? $user->profile->skills : ''}}</textarea> -->
                                        </div>
                                    </div>
                                    <div class="col-lg-12">
                                        <div class="form-group">
                                            <label>{{__trans('hobbies')}}</label>
                                            <input type="text" class="form-control" name="hobbies"
                                                value="{{$user->profile ? $user->profile->hobbies : ''}}">
                                            <!-- <textarea rows="8" cols="5" class="form-control" name="hobbies">{{$user->profile ? $user->profile->hobbies : ''}}</textarea> -->
                                        </div>
                                    </div>

                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-xl-6 col-6">
                        <div class="card bg-white">
                            <div class="card-header">
                                <div class="row">
                                    <div class="col">
                                        <h5 class="card-title">{{__trans('leave_balance_detail')}}</h5>
                                    </div>
                                    <div class="col-auto">
                                        <div class="toggle-switch">
                                            <label class="switch">
                                                <input id="toggle-edit" name="is_previous_leave" type="checkbox"
                                                    @if($user->is_previous_leave == '1') checked @endif>
                                                <span class="slider round"></span>
                                            </label>
                                            <span class="toggle-text"></span>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="card-body">
                                <div class="row">
                                    <x-leave-balance :user="$user" :leave_balance="$leave_balance" />
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
                                            <input type="text" class="form-control" name="bank_name"
                                                @if(isset($user->bankDetail->bank_name))
                                            value="{{ $user->bankDetail->bank_name }}" @else value="" @endif>
                                        </div>
                                    </div>
                                    <div class="col-lg-4">
                                        <div class="form-group">
                                            <label>{{__trans('account_number')}}</label>
                                            <input type="text" class="form-control"
                                                @if(isset($user->bankDetail->account_number))
                                            value="{{ $user->bankDetail->account_number }}" @else value="" @endif
                                            name="account_number">
                                        </div>
                                    </div>
                                    <div class="col-lg-4">
                                        <div class="form-group">
                                            <label>{{__trans('routing_number')}}</label>
                                            <input type="text" class="form-control"
                                                @if(isset($user->bankDetail->routing_number))
                                            value="{{ $user->bankDetail->routing_number }}" @else value="" @endif
                                            name="routing_number">
                                        </div>
                                    </div>
                                    <div class="col-lg-6">
                                        <div class="form-group">
                                            <label>{{__trans('international_Bank_Account_Number')}}</label>
                                            <input type="text" class="form-control"
                                                @if(isset($user->bankDetail->iba_number))
                                            value="{{ $user->bankDetail->iba_number }}" @else value="" @endif
                                            name="iba_number">
                                        </div>
                                    </div>
                                    <div class="col-lg-6">
                                        <div class="form-group">
                                            <label>{{__trans('swift_code')}}</label>
                                            <input type="text" class="form-control"
                                                @if(isset($user->bankDetail->swift_code))
                                            value="{{ $user->bankDetail->swift_code }}" @else value="" @endif
                                            name="swift_code">
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                     @if($picdocuments != null) 
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
                                            <input type="date" value="{{$picdocuments?->issue_date}}"
                                                class="form-control" name="pic_issue_date">
                                        </div>
                                    </div>
                                    <div class="col-lg-4">
                                        <div class="form-group">
                                            <label>{{__trans('expiry_date')}}</label>
                                            <input type="date" class="form-control" value="{{$picdocuments?->expiry_date ? \Carbon\Carbon::parse($picdocuments->expiry_date)->format('d/m/Y') : ''}}" name="pic_expiry_date">
                                        </div>
                                    </div>
                                    <div class="col-lg-4">
                                        <div class="form-group">
                                            <label>{{__trans('upload_document')}}</label>
                                            <input type="file" class="form-control" name="pic_doc">
                                            <br>
                                            @if($picdocuments?->path != null)
                                            <a href="{{asset($picdocuments?->path)}}" target="_blank">View Document</a>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                     @endif 
                    @can('View Salary User')
                    <div class="col-xl-12 col-12">
                        <div class="card bg-white">
                            <div class="card-header">
                                <div class="row">
                                    <div class="col">
                                        <h5>{{__trans('employee_salary_&_Fixed_entity')}}</h5>
                                    </div>
                                    <div class="col-auto">
                                        @if(isset($user->salary))
                                        <a href="{{route('backend.payroll.user.user-salaries.edit', [$user, $user->salary])}}"
                                            class="btn btn-sm inline-block me-2  btn-success edit-button"> <i
                                                class="fa fa-edit"></i></a>
                                        @else
                                        <a href="{{route('backend.payroll.user.user-salaries.create', $user)}}"
                                            class="btn btn-sm inline-block me-2  btn-success edit-button"> <i
                                                class="fa fa-plus"></i></a>
                                        @endif
                                    </div>
                                </div>
                            </div>
                            <div class="card-body light" style="padding-bottom: 10px !important;">
                                <div class="project-info d-flex text-sm">
                                    <div class="project-info-inner mr-3 col-4">
                                        <b class="m-0"> Payslip Type </b>
                                        <div class="project-amnt pt-1">Monthly Payslip</div>
                                    </div>
                                    <div class="project-info-inner mr-3 col-4">
                                        <b class="m-0">
                                            @if (getSetting('payroll_calculation') == 'hourly')
                                            Basic Hourly Rate
                                            @else
                                            Basic Salary
                                            @endif
                                        </b>
                                        <div class="project-amnt pt-1">@if(isset($user->salary->basic))
                                            {{ $user->salary->basic }} @endif
                                        </div>
                                    </div>
                                    <div class="project-info-inner mr-3 col-4">
                                        <b class="m-0">

                                            @if (getSetting('payroll_calculation') == 'hourly')
                                            Total Working Hour
                                            @else
                                            Total Working Days
                                            @endif
                                        </b>
                                        <div class="project-amnt pt-1"> @if(isset($user->salary->total_working_days))
                                            {{ $user->salary->total_working_days }} @endif
                                        </div>
                                    </div>
                                </div>
                                @php
                                $fixed_allowance = isset($user->salary->fixed_allowances) ?
                                json_decode($user->salary->fixed_allowances, true) : 0;
                                $fixed_deduction = isset($user->salary->fixed_deductions) ?
                                json_decode($user->salary->fixed_deductions, true) : 0;

                                if (is_array($fixed_allowance)) {
                                foreach ($fixed_allowance as $key => $value) {
                                $fixed_allowance[$key] = $value ?? 0;
                                }
                                }

                                if (is_array($fixed_deduction)) {
                                foreach ($fixed_deduction as $key => $value) {
                                $fixed_deduction[$key] = $value ?? 0;
                                }
                                }
                                @endphp

                                <div class="col-auto">
                                    @if(is_array($fixed_allowance))
                                    <div class="project-info d-flex text-sm" style="padding-top: 35px !important;">
                                        <u><b>{{ __trans('allowances') }}</b></u>
                                    </div>

                                    <!-- Start Allowance Section -->
                                    <div class="project-info text-sm">
                                        <div class="row">
                                            <div class="form-group col-4">
                                                <b class="m-0">{{ __trans('housing_allowance') }}</b>
                                                <div class="project-amnt pt-1">
                                                    {{ $fixed_allowance['housing_allowance'] }}
                                                </div>
                                            </div>
                                            <div class="form-group col-4">
                                                <b class="m-0">{{ __trans('transportation_allowance') }}</b>
                                                <div class="project-amnt pt-1">
                                                    {{ $fixed_allowance['transportation_allowance'] }}
                                                </div>
                                            </div>
                                            <div class="form-group col-4">
                                                <b class="m-0">{{ __trans('functional_allowance') }}</b>
                                                <div class="project-amnt pt-1">
                                                    {{ !empty($fixed_allowance['functional_allowance']) ? $fixed_allowance['functional_allowance'] : '0' }}
                                                </div>
                                            </div>
                                            <div class="form-group col-4">
                                                <b class="m-0">{{ __trans('other_allowance') }}</b>
                                                <div class="project-amnt pt-1">
                                                    {{ $fixed_allowance['other_allowance'] }}
                                                </div>
                                            </div>
                                        </div>
                                        <div class="row mt-3">
                                            <div class="form-group col-4">
                                                <b class="m-0">{{ __trans('tips') }}</b>
                                                <div class="project-amnt pt-1">
                                                    {{ $fixed_allowance['tips'] }}
                                                </div>
                                            </div>
                                            @foreach ($allowance as $alldeduvalue)
                                            @if ($alldeduvalue->type == 1)
                                            @php
                                            $allvalue = isset($fixed_allowance[$alldeduvalue->name]) ?
                                            $fixed_allowance[$alldeduvalue->name] :
                                            $alldeduvalue->amount;
                                            @endphp
                                            <div class="form-group col-4">
                                                <b class="m-0">{{ __trans($alldeduvalue->name) }}</b>
                                                <div class="project-amnt pt-1">{{ $allvalue }}</div>
                                            </div>
                                            @endif
                                            @endforeach
                                        </div>
                                    </div>
                                    @endif

                                    @if(is_array($fixed_deduction))
                                    <!-- Start Deduction Section -->
                                    <div class="project-info text-sm mt-4">
                                        <u><b>{{ __trans('deductions') }}</b></u>
                                        <div class="row">
                                            <div class="form-group col-4">
                                                <b class="m-0">{{ __trans('advance_salary') }}</b>
                                                <div class="project-amnt pt-1">{{ $fixed_deduction['advance_salary'] }}
                                                </div>
                                            </div>
                                            <div class="form-group col-4">
                                                <b class="m-0">{{ __trans('loan_deduction') }}</b>
                                                <div class="project-amnt pt-1">{{ $fixed_deduction['loan_deduction'] }}
                                                </div>
                                            </div>
                                            <div class="form-group col-4">
                                                <b class="m-0">{{ __trans('other_deduction') }}</b>
                                                <div class="project-amnt pt-1">{{ $fixed_deduction['other_deduction'] }}
                                                </div>
                                            </div>
                                        </div>
                                        <div class="row mt-3">
                                            @foreach ($allowance as $alldeduvalue)
                                            @if ($alldeduvalue->type == 2)
                                            @php
                                            $deduvalue = isset($fixed_deduction[$alldeduvalue->name]) ?
                                            $fixed_deduction[$alldeduvalue->name] :
                                            $alldeduvalue->amount;
                                            @endphp
                                            <div class="form-group col-4">
                                                <b class="m-0">{{ __trans($alldeduvalue->name) }}</b>
                                                <div class="project-amnt pt-1">{{ $deduvalue }}</div>
                                            </div>
                                            @endif
                                            @endforeach
                                        </div>
                                    </div>
                                    @endif
                                </div>
                            </div>

                        </div>
                    </div>
                    @endcan
        @can('View Salary User')
        <div class="row">
            <div class="col-xl-12 col-12">
                <div class="card bg-white">
                    <div class="card-header">
                        <div class="row">
                            <div class="col">
                                <h5>{{__trans('allowance/additions') }} ({{ date("F Y") }}) </h5>
                            </div>
                            <div class="col-auto">
                                <a href="{{route('backend.payroll.user.user-salaries.createallowance', $user)}}"
                                    class="btn btn-sm inline-block me-2  btn-success edit-button"> <i
                                        class="fa fa-plus"></i></a>
                            </div>
                        </div>
                    </div>
                    <div class="card-body light" style=" overflow:auto">
                        <div class="table-responsive">
                            <table class="table text-center table-hover light" id="allowance">
                                <thead class="thead-light">
                                    <tr>
                                        <!-- <th>{{__trans('employee_name')}}</th> -->
                                        <th>{{__trans('title')}}</th>
                                        <th>{{__trans('type')}}</th>
                                        <th>{{__trans('amount')}}</th>
                                        <th>{{__trans('monthly_fixed')}}</th>
                                        <th>{{__trans('action')}}</th>
                                    </tr>
                                </thead>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-xl-12 col-12">
                <div class="card bg-white">
                    <div class="card-header">
                        <div class="row">
                            <div class="col">
                                <h5>{{__trans('overtime')}}</h5>
                            </div>
                            <div class="col-auto">
                                <a href="{{route('backend.payroll.user.user-salaries.createovertime', $user)}}"
                                    class="btn btn-sm inline-block me-2  btn-success edit-button"> <i
                                        class="fa fa-plus"></i></a>
                            </div>
                        </div>
                    </div>
                    <div class="card-body light" style=" overflow:auto">
                        <div class="table-responsive">
                            <table class="table text-center table-hover light" id="overtime">
                                <thead class="thead-light">
                                    <tr>
                                        <!-- <th>{{__trans('employee_name')}}</th> -->
                                        <th>{{__trans('title')}}</th>
                                        <th>{{__trans('hours')}}</th>
                                        <th>{{__trans('rate_per_hour')}}</th>
                                        <th>{{__trans('amount')}}</th>
                                        <th>{{__trans('action')}}</th>
                                    </tr>
                                </thead>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-xl-12 col-12">
                <div class="card bg-white">
                    <div class="card-header">
                        <div class="row">
                            <div class="col">
                                <h5>{{__trans('deduction')}}</h5>
                            </div>
                            <div class="col-auto">
                                <a href="{{route('backend.payroll.user.user-salaries.creatededuction', $user)}}"
                                    class="btn btn-sm inline-block me-2  btn-success edit-button"> <i
                                        class="fa fa-plus"></i></a>
                            </div>
                        </div>
                    </div>
                    <div class="card-body light" style=" overflow:auto">
                        <div class="table-responsive">
                            <table class="table text-center table-hover light" id="deduction">
                                <thead class="thead-light">
                                    <tr>
                                        <!-- <th>{{__trans('employee_name')}}</th> -->
                                        <th>{{__trans('title')}}</th>
                                        <th>{{__trans('type')}}</th>
                                        <th>{{__trans('amount')}}</th>
                                        <th>{{__trans('monthly_fixed')}}</th>
                                        <th>{{__trans('action')}}</th>
                                    </tr>
                                </thead>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        @endcan
        <div class="col-md-12">
            <div class="text-end mt-4 mb-4">
                <button type="submit" class="btn btn-primary">{{__trans('update_user')}}</button>
            </div>
        </div>
                </div>
        </form>
    </div>
</div>
</div>
<!-- /Page Wrapper -->
<div id="editModal" class="modal"></div>
@endsection
@push('scripts')
<script src="{{asset('assets/backend/plugins/flatpickr/flatpickr.min.js')}}"></script>
<script>
    loadAjaxSelect2();
    flatpickr("input.datepicker", {
        dateFormat: "d/m/Y",
    });
    var table = $('#allowance').DataTable({
        processing: true,
        serverSide: true,
        paging: false,
        bInfo: false,
        searching: false,
        ajax: {
            url: "{{route('backend.payroll.user.user-salaries.show', [$user,$user->id])}}",
        },
        columns: [
            // {
            //     data: 'employee_name',
            //     name: 'user.name'
            // },
            {
                data: 'title',
                name: 'title',
            },
            {
                data: 'type',
                name: 'type'
            },
            {
                data: 'amount',
                name: 'amount',
            },
            {
                data: 'monthly_fixed',
                name: 'monthly_fixed'
            },
            {
                data: 'action',
            },
        ]
    });
    var table = $('#deduction').DataTable({
        processing: true,
        serverSide: true,
        paging: false,
        bInfo: false,
        searching: false,
        ajax: {
            url: "{{route('backend.payroll.user.user-salaries.userdeductionlist', [$user,$user->id])}}",
        },
        columns: [
            // {
            //     data: 'employee_name',
            //     name: 'user.name'
            // },
            {
                data: 'title',
                name: 'title',
            },
            {
                data: 'deduction_type',
                name: 'deduction_type'
            },
            {
                data: 'amount',
                name: 'amount',
            },
            {
                data: 'monthly_fixed',
                name: 'monthly_fixed'
            },
            {
                data: 'action',
            },
        ]
    });
    var table = $('#overtime').DataTable({
        processing: true,
        serverSide: true,
        paging: false,
        bInfo: false,
        searching: false,
        ajax: {
            url: "{{route('backend.payroll.user.user-salaries.userovertimelist', [$user,$user->id])}}",
        },
        columns: [
            // {
            //     data: 'employee_name',
            //     name: 'user.name'
            // },
            {
                data: 'overtime_type',
                name: 'overtime_type',
            },
            {
                data: 'hours',
                name: 'hours'
            },
            {
                data: 'rate_per_hour',
                name: 'rate_per_hour',
            },
            {
                data: 'calculated_amount',
                name: 'calculated_amount'
            },
            {
                data: 'action',
            },
        ]
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
                        <input type="text" name="create_shifts[${shiftCounter}][shift_start]" class="form-control timepicker" id="shift_start_${shiftCounter}" placeholder="{{__trans('shift_start_time')}}">
                    </div>
                </div>
                <div class="col-md-5">
                    <div class="mb-3">
                        <label for="shift_end_${shiftCounter}" class="form-label">{{__trans('shift_end')}}</label>
                        <input type="text" name="create_shifts[${shiftCounter}][shift_end]" class="form-control timepicker" id="shift_end_${shiftCounter}" placeholder="{{__trans('shift_end_time')}}">
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
    const insuranceProvided = document.getElementById('medical_insurance_provided').value == '1';
    const premiumDiv = document.getElementById('annual_premium_div');
    premiumDiv.style.display = insuranceProvided ? 'block' : 'none';
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

    // $(document).on('change', '#date_of_joining', function() {
    //     var joiningDate = $(this).val();

    //     if (joiningDate !== '') {
    //         $.ajax({
    //             url: '{{ route("backend.getProbationEndDate") }}',
    //             type: 'GET',
    //             data: {
    //                 joining_date: joiningDate
    //             },
    //             success: function(response) {
    //                 $('#probation_end_date').val(response.probation_end_date);
    //             },
    //             error: function() {
    //                 alert('Unable to calculate probation end date.');
    //             }
    //         });
    //     }
    // });
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
    const selectedDepartmentId = "{{ old('department_id', $selectedDepartmentId ?? '') }}";
    const selectedDivisionId = "{{ old('division_id', $selectedDivisionId ?? '') }}";

    if (selectedDepartmentId) {
        loadDivisions(selectedDepartmentId, selectedDivisionId);
    }

    $('#department').on('change', function() {
        const departmentId = $(this).val();
        loadDivisions(departmentId, null);
    });

    function loadDivisions(departmentId, selectedDivisionId = null) {
        if (departmentId) {
            $.ajax({
                url: '{{ route("backend.getDivisions") }}',
                type: 'GET',
                data: {
                    branch_id: departmentId
                },
                success: function(data) {
                    $('#division_id').empty().append('<option value="">Select Department</option>');
                    $.each(data, function(key, division) {
                        $('#division_id').append(
                            `<option value="${division.id}" ${selectedDivisionId == division.id ? 'selected' : ''}>${division.name}</option>`
                        );
                    });
                }
            });
        } else {
            $('#division_id').empty().append('<option value="">Select Division</option>');
        }
    }
});
</script>

<script>
const weekdays = @json($weekdays);
const workWeekInput = document.getElementById("work_week");
const weekendBox = document.getElementById("weekend-box");
const weekendSelect = document.getElementById("weekend");
const workdaysContainer = document.getElementById("workdays-container");
const weekendHint = document.getElementById("weekend-hint");

function setWeekendLimit(workDaysCount) {
    // Reset selection only if user changes manually
    $('#weekend').val(null).trigger('change');
    workdaysContainer.innerHTML = "";

    if (workDaysCount === 5) {
        weekendBox.style.display = "block";
        weekendHint.innerText = "Select exactly 2 weekend days";
        $('#weekend').select2({
            maximumSelectionLength: 2
        });
    } else if (workDaysCount === 6) {
        weekendBox.style.display = "block";
        weekendHint.innerText = "Select exactly 1 weekend day";
        $('#weekend').select2({
            maximumSelectionLength: 1
        });
    } else {
        weekendBox.style.display = "none";
    }
}

    // Update workdays when weekends are selected
    weekendSelect.addEventListener("change", function() {
        const selected = Array.from(this.selectedOptions).map(opt => opt.value);
        const workDays = weekdays.filter(day => !selected.includes(day));
        if (workdaysContainer.tagName === 'INPUT') {
            workdaysContainer.value = workDays.join(", ");
        } else {
            workdaysContainer.innerHTML = workDays.join(", ");
        }
    });

// On page load (edit mode)
$(document).ready(function() {
    if (workWeekInput.value) {
        let workDaysCount = parseInt(workWeekInput.value);
        // initialize select2 with correct limit
        if (workDaysCount === 5) {
            $('#weekend').select2({
                maximumSelectionLength: 2
            });
        } else if (workDaysCount === 6) {
            $('#weekend').select2({
                maximumSelectionLength: 1
            });
        } else {
            $('#weekend').select2();
        }

        // trigger change so select2 shows pre-selected weekends
        $('#weekend').trigger('change');
    }
});

// When work_week changes
workWeekInput.addEventListener("input", function() {
    setWeekendLimit(parseInt(this.value));
});
// Add new ticket
$('#addTicket').click(function() {
    const nextIndex = $('#ticket_container .ticket-row').length;

    const newTicketHtml = `
    <div class="col-12 ticket-row" data-index="${nextIndex}">
        <div class="row g-2 align-items-end">
            <div class="col-md-4">
                <div class="mb-3">
                    <label for="ticket_title_new_${nextIndex}" class="form-label">Title</label>
                    <input type="text" name="create_tickets[${nextIndex}][title]" class="form-control" id="ticket_title_new_${nextIndex}" placeholder="Ticket title / description">
                </div>
            </div>
             <div class="col-md-4">
                <div class="mb-3">
                    <label for="ticket_qty_new_${nextIndex}" class="form-label">Qty</label>
                    <input type="text" name="create_tickets[${nextIndex}][qty]" class="form-control" id="ticket_qty_new_${nextIndex}" placeholder="Ticket qty / description">
                </div>
            </div>
            <div class="col-md-3">
                <div class="mb-3">
                    <label for="ticket_percentage_new_${nextIndex}" class="form-label">Percentage</label>
                    <input type="number" step="0.01" name="create_tickets[${nextIndex}][percentage]" class="form-control" id="ticket_percentage_new_${nextIndex}" placeholder="0.00%">
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

    $('#ticket_container').append(newTicketHtml);
});

// Remove ticket row
$('#ticket_container').on('click', '.removeTicket', function() {
    // $(this).closest('.ticket-row').remove();
    $(this).closest('.ticket-row').find('input[name*="_delete"]').val(1);
    $(this).closest('.ticket-row').hide(); // hide instead of remove
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
    const joiningDate = document.getElementById('date_of_joining')?.value;
    const probationMonths = document.getElementById('probation_month')?.value;

    if (!joiningDate || !probationMonths) {
        return;
    }

    const date = new Date(joiningDate);
    if (isNaN(date)) return;

    date.setMonth(date.getMonth() + parseInt(probationMonths));

    const y = date.getFullYear();
    const m = String(date.getMonth() + 1).padStart(2, '0');
    const d = String(date.getDate()).padStart(2, '0');

    document.getElementById('probation_end_date').value = `${y}-${m}-${d}`;
}

// 🔁 Recalculate on change
const joiningDateEl = document.getElementById('date_of_joining');
if (joiningDateEl) {
    joiningDateEl.addEventListener('change', calculateProbationEndDate);
}

const probationMonthEl = document.getElementById('probation_month');
if (probationMonthEl) {
    probationMonthEl.addEventListener('input', calculateProbationEndDate);
}
</script>

@endpush
