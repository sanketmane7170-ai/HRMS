@extends('layouts.backend')
@section('content')
<div class="page-wrapper">
    <div class="content container-fluid">
        <div class="page-header">
            <div class="row align-items-center">
                <div class="col"><h3 class="page-title">{{ __trans('professional_tax_slabs') }}</h3></div>
                <div class="col-auto">
                    <form method="GET" class="d-flex">
                        <select name="state_id" class="form-control" onchange="this.form.submit()">
                            @foreach ($states as $state)
                                <option value="{{ $state->id }}" @selected($state->id == $stateId)>{{ $state->name }}</option>
                            @endforeach
                        </select>
                    </form>
                </div>
            </div>
        </div>

        <div class="card"><div class="card-body">
            <table class="table table-sm">
                <thead><tr><th>{{ __trans('gender') }}</th><th>{{ __trans('salary_from') }}</th><th>{{ __trans('salary_to') }}</th><th>{{ __trans('monthly_tax') }}</th><th>{{ __trans('effective_from') }}</th><th></th></tr></thead>
                <tbody>
                    @foreach ($slabs as $slab)
                    <tr>
                        <td>{{ ucfirst($slab->gender) }}</td>
                        <td>{{ number_format($slab->salary_from, 0) }}</td>
                        <td>{{ $slab->salary_to ? number_format($slab->salary_to, 0) : __trans('and_above') }}</td>
                        <td>{{ number_format($slab->monthly_tax, 2) }}</td>
                        <td>{{ $slab->effective_from->format('d-M-Y') }}</td>
                        <td><a href="{{ route('backend.indian-payroll.professional-tax.destroy', $slab) }}" method="DELETE" class="btn btn-sm btn-link text-danger action-button"><i class="fa fa-trash"></i></a></td>
                    </tr>
                    @endforeach
                </tbody>
            </table>

            <form method="POST" action="{{ route('backend.indian-payroll.professional-tax.store') }}" class="row g-2">
                @csrf
                <input type="hidden" name="state_id" value="{{ $stateId }}">
                <div class="col-md-2">
                    <select name="gender" class="form-control"><option value="all">{{ __trans('all') }}</option><option value="male">{{ __trans('male') }}</option><option value="female">{{ __trans('female') }}</option></select>
                </div>
                <div class="col-md-2"><input type="number" step="0.01" name="salary_from" class="form-control" placeholder="{{ __trans('from') }}" required></div>
                <div class="col-md-2"><input type="number" step="0.01" name="salary_to" class="form-control" placeholder="{{ __trans('to_blank_for_above') }}"></div>
                <div class="col-md-2"><input type="number" step="0.01" name="monthly_tax" class="form-control" placeholder="{{ __trans('monthly_tax') }}" required></div>
                <div class="col-md-2"><input type="date" name="effective_from" class="form-control" required></div>
                <input type="hidden" name="frequency" value="monthly">
                <div class="col-md-2"><button type="submit" class="btn btn-outline-primary w-100">{{ __trans('add') }}</button></div>
            </form>
        </div></div>
    </div>
</div>
@endsection
