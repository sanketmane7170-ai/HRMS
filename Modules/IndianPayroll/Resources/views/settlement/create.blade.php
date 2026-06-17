@extends('layouts.backend')
@section('content')
<div class="page-wrapper">
    <div class="content container-fluid">
        <div class="page-header">
            <div class="row align-items-center">
                <div class="col"><h3 class="page-title">{{ __trans('full_and_final_settlement') }} — {{ $user->name }}</h3></div>
            </div>
        </div>

        <div class="alert alert-info">
            {{ __trans('date_of_joining') }}: {{ $profile->date_of_joining->format('d-M-Y') }} |
            {{ __trans('last_drawn_basic') }}: {{ number_format($structure->componentAmount('BASIC'), 2) }}
        </div>

        <div class="card"><div class="card-body">
            <form method="POST" action="{{ route('backend.indian-payroll.settlements.store', $user) }}">
                @csrf
                <div class="row">
                    <div class="col-md-4 form-group">
                        <label>{{ __trans('last_working_day') }}</label>
                        <input type="date" name="last_working_day" class="form-control" required value="{{ now()->format('Y-m-d') }}">
                    </div>
                    <div class="col-md-4 form-group">
                        <label>{{ __trans('pending_salary_amount') }} <small class="text-muted">({{ __trans('leave_blank_to_auto_calculate_from_attendance') }})</small></label>
                        <input type="number" step="0.01" name="pending_salary_amount" class="form-control" placeholder="{{ __trans('auto') }}">{{-- left blank: computed from attendance for the exit month, same as a regular payroll run --}}
                    </div>
                    <div class="col-md-4 form-group">
                        <label>&nbsp;</label><br>
                        <input type="checkbox" name="is_death_or_disablement" value="1"> {{ __trans('exit_due_to_death_or_disablement') }}
                    </div>
                </div>

                <h6>{{ __trans('leave_encashment') }}</h6>
                <div class="row">
                    @foreach ($leaveTypes as $type)
                    <div class="col-md-3 form-check">
                        <input type="checkbox" class="form-check-input" name="encash_leave_type_ids[]" value="{{ $type->id }}">
                        <label class="form-check-label">{{ $type->name }} ({{ $leaveBalances[$type->id]->available ?? 0 }} {{ __trans('days') }})</label>
                    </div>
                    @endforeach
                </div>

                <div class="row mt-3">
                    <div class="col-md-4 form-group">
                        <label>{{ __trans('notice_pay_recovery') }}</label>
                        <input type="number" step="0.01" name="notice_pay_recovery" class="form-control" value="0">
                    </div>
                    <div class="col-md-4 form-group">
                        <label>{{ __trans('other_deductions') }}</label>
                        <input type="number" step="0.01" name="other_deductions" class="form-control" value="0">
                    </div>
                </div>

                <button type="submit" class="btn btn-primary">{{ __trans('compute_settlement') }}</button>
            </form>
        </div></div>
    </div>
</div>
@endsection
