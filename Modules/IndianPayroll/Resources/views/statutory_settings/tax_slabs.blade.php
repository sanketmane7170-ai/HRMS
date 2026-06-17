@extends('layouts.backend')
@section('content')
<div class="page-wrapper">
    <div class="content container-fluid">
        <div class="page-header">
            <div class="row align-items-center">
                <div class="col">
                    <h3 class="page-title">{{ __trans('income_tax_slabs') }}</h3>
                </div>
                <div class="col-auto">
                    <form method="GET" class="d-flex">
                        <select name="financial_year" class="form-control" onchange="this.form.submit()">
                            @foreach ($financialYears as $fy)
                                <option value="{{ $fy }}" @selected($fy == $financialYear)>{{ $fy }}</option>
                            @endforeach
                            <option value="{{ $financialYear }}" @selected(!$financialYears->contains($financialYear))>{{ $financialYear }} ({{ __trans('current') }})</option>
                        </select>
                    </form>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-md-6">
                <div class="card"><div class="card-body">
                    <h5>{{ __trans('new_regime') }}</h5>
                    <table class="table table-sm">
                        <thead><tr><th>{{ __trans('from') }}</th><th>{{ __trans('to') }}</th><th>{{ __trans('rate') }}</th><th></th></tr></thead>
                        <tbody>
                            @foreach ($newRegimeSlabs as $slab)
                            <tr>
                                <td>{{ number_format($slab->slab_from, 0) }}</td>
                                <td>{{ $slab->slab_to ? number_format($slab->slab_to, 0) : __trans('and_above') }}</td>
                                <td>{{ $slab->rate }}%</td>
                                <td><a href="{{ route('backend.indian-payroll.tax-slabs.destroy', $slab) }}" method="DELETE" class="btn btn-sm btn-link text-danger action-button"><i class="fa fa-trash"></i></a></td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                    @include('indianpayroll::statutory_settings.partials.tax_slab_form', ['regime' => 'new'])
                </div></div>
            </div>
            <div class="col-md-6">
                <div class="card"><div class="card-body">
                    <h5>{{ __trans('old_regime') }}</h5>
                    <table class="table table-sm">
                        <thead><tr><th>{{ __trans('from') }}</th><th>{{ __trans('to') }}</th><th>{{ __trans('rate') }}</th><th></th></tr></thead>
                        <tbody>
                            @foreach ($oldRegimeSlabs as $slab)
                            <tr>
                                <td>{{ number_format($slab->slab_from, 0) }}</td>
                                <td>{{ $slab->slab_to ? number_format($slab->slab_to, 0) : __trans('and_above') }}</td>
                                <td>{{ $slab->rate }}%</td>
                                <td><a href="{{ route('backend.indian-payroll.tax-slabs.destroy', $slab) }}" method="DELETE" class="btn btn-sm btn-link text-danger action-button"><i class="fa fa-trash"></i></a></td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                    @include('indianpayroll::statutory_settings.partials.tax_slab_form', ['regime' => 'old'])
                </div></div>
            </div>
        </div>

        <div class="card"><div class="card-body">
            <h5>{{ __trans('surcharge_slabs') }}</h5>
            <table class="table table-sm">
                <thead><tr><th>{{ __trans('regime') }}</th><th>{{ __trans('from') }}</th><th>{{ __trans('to') }}</th><th>{{ __trans('surcharge') }}</th></tr></thead>
                <tbody>
                    @foreach ($surchargeSlabs as $slab)
                    <tr>
                        <td>{{ ucfirst($slab->regime) }}</td>
                        <td>{{ number_format($slab->income_from, 0) }}</td>
                        <td>{{ $slab->income_to ? number_format($slab->income_to, 0) : __trans('and_above') }}</td>
                        <td>{{ $slab->surcharge_rate }}%</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
            <p class="text-muted">{{ __trans('surcharge_slabs_managed_via_seeder_note') }}</p>
        </div></div>
    </div>
</div>
@endsection
