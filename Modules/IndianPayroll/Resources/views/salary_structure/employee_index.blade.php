@extends('layouts.backend')
@section('content')
<div class="page-wrapper">
    <div class="content container-fluid">
        <div class="page-header">
            <div class="row align-items-center">
                <div class="col">
                    <h3 class="page-title">{{ __trans('employee_salary_structures') }}</h3>
                </div>
            </div>
        </div>
        <div class="card card-table">
            <div class="card-body">
                <table class="table table-hover">
                    <thead><tr><th>{{ __trans('name') }}</th><th>{{ __trans('template') }}</th><th>{{ __trans('annual_ctc') }}</th><th>{{ __trans('monthly_ctc') }}</th><th>{{ __trans('effective_from') }}</th><th>{{ __trans('action') }}</th></tr></thead>
                    <tbody>
                        @foreach ($structures as $structure)
                        <tr>
                            <td>{{ $structure->user->name ?? 'N/A' }}</td>
                            <td>{{ $structure->template->name ?? '-' }}</td>
                            <td>{{ number_format($structure->annual_ctc, 2) }}</td>
                            <td>{{ number_format($structure->monthly_ctc, 2) }}</td>
                            <td>{{ $structure->effective_from->format('d-M-Y') }}</td>
                            <td><a href="{{ route('backend.indian-payroll.employee-salary-structures.show', $structure->user_id) }}" class="btn btn-sm btn-info"><i class="fa fa-eye"></i></a></td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
                {{ $structures->links() }}
            </div>
        </div>
    </div>
</div>
@endsection
