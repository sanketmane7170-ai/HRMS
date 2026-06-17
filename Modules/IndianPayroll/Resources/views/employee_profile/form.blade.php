@extends('layouts.backend')
@section('content')
<div class="page-wrapper">
    <div class="content container-fluid">
        <div class="page-header">
            <div class="row align-items-center">
                <div class="col">
                    <h3 class="page-title">{{ $profile ? __trans('edit_statutory_profile') : __trans('add_statutory_profile') }} — {{ $user->name }}</h3>
                    <ul class="breadcrumb">
                        <li class="breadcrumb-item"><a href="{{ route('backend.indian-payroll.employee-profiles.index') }}">{{ __trans('employee_statutory_profiles') }}</a></li>
                        <li class="breadcrumb-item active">{{ $user->name }}</li>
                    </ul>
                </div>
            </div>
        </div>

        <form method="POST" action="{{ $profile ? route('backend.indian-payroll.employee-profiles.update', $user) : route('backend.indian-payroll.employee-profiles.store', $user) }}">
            @csrf
            @if($profile) @method('PUT') @endif

            <div class="row">
                <div class="col-md-6">
                    <div class="card"><div class="card-body">
                        <h5>{{ __trans('statutory_identity') }}</h5>
                        <div class="form-group">
                            <label>{{ __trans('pan') }}</label>
                            <input type="text" name="pan" maxlength="10" class="form-control text-uppercase" value="{{ old('pan', $profile->pan ?? '') }}">
                            @error('pan')<span class="text-danger">{{ $message }}</span>@enderror
                        </div>
                        <div class="form-group">
                            <label>{{ __trans('aadhaar') }}</label>
                            <input type="text" name="aadhaar" maxlength="12" class="form-control" value="{{ old('aadhaar', $profile->aadhaar ?? '') }}">
                        </div>
                        <div class="form-group">
                            <label>{{ __trans('uan') }}</label>
                            <input type="text" name="uan" maxlength="12" class="form-control" value="{{ old('uan', $profile->uan ?? '') }}">
                        </div>
                        <div class="form-group">
                            <label>{{ __trans('pf_number') }}</label>
                            <input type="text" name="pf_number" class="form-control" value="{{ old('pf_number', $profile->pf_number ?? '') }}">
                        </div>
                        <div class="form-group">
                            <label>{{ __trans('esi_number') }}</label>
                            <input type="text" name="esi_number" maxlength="17" class="form-control" value="{{ old('esi_number', $profile->esi_number ?? '') }}">
                        </div>
                        <div class="form-group">
                            <label>{{ __trans('pt_enrollment_number') }}</label>
                            <input type="text" name="pt_enrollment_number" class="form-control" value="{{ old('pt_enrollment_number', $profile->pt_enrollment_number ?? '') }}">
                        </div>
                    </div></div>
                </div>

                <div class="col-md-6">
                    <div class="card"><div class="card-body">
                        <h5>{{ __trans('work_location_and_applicability') }}</h5>
                        <div class="form-group">
                            <label>{{ __trans('state_of_work') }}</label>
                            <select name="state_id" class="form-control" required>
                                @foreach ($states as $state)
                                    <option value="{{ $state->id }}" @selected(old('state_id', $profile->state_id ?? null) == $state->id)>{{ $state->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="form-group">
                            <label>{{ __trans('gender') }}</label>
                            <select name="gender" class="form-control">
                                <option value="">-</option>
                                <option value="male" @selected(old('gender', $profile->gender ?? '') == 'male')>{{ __trans('male') }}</option>
                                <option value="female" @selected(old('gender', $profile->gender ?? '') == 'female')>{{ __trans('female') }}</option>
                                <option value="other" @selected(old('gender', $profile->gender ?? '') == 'other')>{{ __trans('other') }}</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label>{{ __trans('date_of_joining') }}</label>
                            <input type="date" name="date_of_joining" class="form-control" required value="{{ old('date_of_joining', optional($profile?->date_of_joining)->format('Y-m-d')) }}">
                        </div>
                        <div class="form-group">
                            <label>{{ __trans('employment_type') }}</label>
                            <select name="employment_type" class="form-control" required>
                                @foreach (['permanent', 'contract', 'intern', 'consultant'] as $type)
                                    <option value="{{ $type }}" @selected(old('employment_type', $profile->employment_type ?? 'permanent') == $type)>{{ __trans($type) }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="form-check form-switch mb-2">
                            <input type="checkbox" class="form-check-input" name="pf_applicable" value="1" @checked(old('pf_applicable', $profile->pf_applicable ?? true))>
                            <label class="form-check-label">{{ __trans('pf_applicable') }}</label>
                        </div>
                        <div class="form-check form-switch mb-2">
                            <input type="checkbox" class="form-check-input" name="pf_voluntary_above_ceiling" value="1" @checked(old('pf_voluntary_above_ceiling', $profile->pf_voluntary_above_ceiling ?? false))>
                            <label class="form-check-label">{{ __trans('voluntary_pf_above_ceiling') }}</label>
                        </div>
                        <div class="form-check form-switch mb-2">
                            <input type="checkbox" class="form-check-input" name="esi_applicable" value="1" @checked(old('esi_applicable', $profile->esi_applicable ?? false))>
                            <label class="form-check-label">{{ __trans('esi_applicable') }}</label>
                        </div>
                        <div class="form-check form-switch mb-2">
                            <input type="checkbox" class="form-check-input" name="pt_applicable" value="1" @checked(old('pt_applicable', $profile->pt_applicable ?? true))>
                            <label class="form-check-label">{{ __trans('pt_applicable') }}</label>
                        </div>
                        <div class="form-check form-switch mb-2">
                            <input type="checkbox" class="form-check-input" name="lwf_applicable" value="1" @checked(old('lwf_applicable', $profile->lwf_applicable ?? true))>
                            <label class="form-check-label">{{ __trans('lwf_applicable') }}</label>
                        </div>
                    </div></div>
                </div>
            </div>

            <div class="row">
                <div class="col-md-12">
                    <div class="card"><div class="card-body">
                        <h5>{{ __trans('bank_details') }}</h5>
                        <div class="row">
                            <div class="col-md-3 form-group">
                                <label>{{ __trans('bank_name') }}</label>
                                <input type="text" name="bank_name" class="form-control" value="{{ old('bank_name', $bankDetail->bank_name ?? '') }}">
                            </div>
                            <div class="col-md-3 form-group">
                                <label>{{ __trans('account_number') }}</label>
                                <input type="text" name="account_number" class="form-control" value="{{ old('account_number', $bankDetail->account_number ?? '') }}">
                            </div>
                            <div class="col-md-2 form-group">
                                <label>{{ __trans('ifsc') }}</label>
                                <input type="text" name="ifsc" maxlength="11" class="form-control text-uppercase" value="{{ old('ifsc', $bankDetail->ifsc ?? '') }}">
                            </div>
                            <div class="col-md-2 form-group">
                                <label>{{ __trans('account_type') }}</label>
                                <select name="account_type" class="form-control">
                                    <option value="savings" @selected(old('account_type', $bankDetail->account_type ?? '') == 'savings')>{{ __trans('savings') }}</option>
                                    <option value="current" @selected(old('account_type', $bankDetail->account_type ?? '') == 'current')>{{ __trans('current') }}</option>
                                </select>
                            </div>
                            <div class="col-md-2 form-group">
                                <label>{{ __trans('account_holder_name') }}</label>
                                <input type="text" name="account_holder_name" class="form-control" value="{{ old('account_holder_name', $bankDetail->account_holder_name ?? '') }}">
                            </div>
                        </div>
                    </div></div>
                </div>
            </div>

            <button type="submit" class="btn btn-primary">{{ __trans('save') }}</button>
        </form>
    </div>
</div>
@endsection
