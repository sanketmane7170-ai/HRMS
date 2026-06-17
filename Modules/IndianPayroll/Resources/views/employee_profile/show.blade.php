@extends('layouts.backend')
@section('content')
<div class="page-wrapper">
    <div class="content container-fluid">
        <div class="page-header">
            <div class="row align-items-center">
                <div class="col">
                    <h3 class="page-title">{{ $user->name }} — {{ __trans('statutory_profile') }}</h3>
                </div>
                <div class="col-auto">
                    <a href="{{ route('backend.indian-payroll.employee-profiles.edit', $user) }}" class="btn btn-warning"><i class="fa fa-edit"></i> {{ __trans('edit') }}</a>
                    <a href="{{ route('backend.indian-payroll.employee-salary-structures.show', $user) }}" class="btn btn-info">{{ __trans('salary_structure') }}</a>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-md-6">
                <div class="card"><div class="card-body">
                    <table class="table table-borderless">
                        <tr><th>{{ __trans('pan') }}</th><td>{{ $profile->pan ?? '-' }}</td></tr>
                        <tr><th>{{ __trans('uan') }}</th><td>{{ $profile->uan ?? '-' }}</td></tr>
                        <tr><th>{{ __trans('pf_number') }}</th><td>{{ $profile->pf_number ?? '-' }}</td></tr>
                        <tr><th>{{ __trans('esi_number') }}</th><td>{{ $profile->esi_number ?? '-' }}</td></tr>
                        <tr><th>{{ __trans('state_of_work') }}</th><td>{{ $profile->state->name ?? '-' }}</td></tr>
                        <tr><th>{{ __trans('date_of_joining') }}</th><td>{{ $profile->date_of_joining->format('d-M-Y') }}</td></tr>
                        <tr><th>{{ __trans('employment_type') }}</th><td>{{ ucfirst($profile->employment_type ?? 'permanent') }}</td></tr>
                        <tr><th>{{ __trans('pf') }}/{{ __trans('esi') }}/{{ __trans('pt') }}/{{ __trans('lwf') }}</th>
                            <td>
                                {{ $profile->pf_applicable ? 'PF' : '' }}
                                {{ $profile->esi_applicable ? ' ESI' : '' }}
                                {{ $profile->pt_applicable ? ' PT' : '' }}
                                {{ $profile->lwf_applicable ? ' LWF' : '' }}
                            </td>
                        </tr>
                    </table>
                </div></div>
            </div>
            <div class="col-md-6">
                <div class="card"><div class="card-body">
                    <h5>{{ __trans('bank_details') }}</h5>
                    @if($bankDetail)
                        <table class="table table-borderless">
                            <tr><th>{{ __trans('bank_name') }}</th><td>{{ $bankDetail->bank_name }}</td></tr>
                            <tr><th>{{ __trans('account_number') }}</th><td>{{ $bankDetail->account_number }}</td></tr>
                            <tr><th>{{ __trans('ifsc') }}</th><td>{{ $bankDetail->ifsc }}</td></tr>
                        </table>
                    @else
                        <p class="text-muted">{{ __trans('no_bank_details_added') }}</p>
                    @endif
                </div></div>
            </div>
        </div>
    </div>
</div>
@endsection
