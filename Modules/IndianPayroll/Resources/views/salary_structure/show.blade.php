@extends('layouts.backend')
@section('content')
<div class="page-wrapper">
    <div class="content container-fluid">
        <div class="page-header">
            <div class="row align-items-center">
                <div class="col">
                    <h3 class="page-title">{{ $user->name }} — {{ __trans('salary_structure') }}</h3>
                </div>
                <div class="col-auto">
                    <a href="{{ route('backend.indian-payroll.employee-salary-structures.revise', $user) }}" class="btn btn-primary">{{ __trans('revise_ctc') }}</a>
                </div>
            </div>
        </div>

        @if($structure)
        <div class="card"><div class="card-body">
            <h5>{{ __trans('current_structure') }} ({{ $structure->template->name ?? '-' }})</h5>
            <p>{{ __trans('annual_ctc') }}: <strong>{{ number_format($structure->annual_ctc, 2) }}</strong> | {{ __trans('monthly_ctc') }}: <strong>{{ number_format($structure->monthly_ctc, 2) }}</strong> | {{ __trans('effective_from') }}: {{ $structure->effective_from->format('d-M-Y') }}</p>
            <table class="table table-sm">
                <thead><tr><th>{{ __trans('component') }}</th><th>{{ __trans('monthly_amount') }}</th><th>{{ __trans('annual_amount') }}</th></tr></thead>
                <tbody>
                    @foreach ($structure->components as $sc)
                    <tr><td>{{ $sc->component->name }}</td><td>{{ number_format($sc->monthly_amount, 2) }}</td><td>{{ number_format($sc->annual_amount, 2) }}</td></tr>
                    @endforeach
                </tbody>
            </table>
        </div></div>
        @else
        <div class="alert alert-warning">{{ __trans('no_salary_structure_assigned_yet') }}</div>
        @endif

        @if($history->isNotEmpty())
        <div class="card"><div class="card-body">
            <h5>{{ __trans('history') }}</h5>
            <table class="table table-sm">
                <thead><tr><th>{{ __trans('annual_ctc') }}</th><th>{{ __trans('effective_from') }}</th><th>{{ __trans('effective_to') }}</th></tr></thead>
                <tbody>
                    @foreach ($history as $h)
                    <tr><td>{{ number_format($h->annual_ctc, 2) }}</td><td>{{ $h->effective_from->format('d-M-Y') }}</td><td>{{ $h->effective_to?->format('d-M-Y') ?? '-' }}</td></tr>
                    @endforeach
                </tbody>
            </table>
        </div></div>
        @endif
    </div>
</div>
@endsection
