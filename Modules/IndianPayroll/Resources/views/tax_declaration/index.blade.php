@extends('layouts.backend')
@section('content')
<div class="page-wrapper">
    <div class="content container-fluid">
        <div class="page-header">
            <div class="row align-items-center">
                <div class="col">
                    <h3 class="page-title">{{ __trans('tax_declarations') }} — {{ $financialYear }}</h3>
                </div>
                <div class="col-auto">
                    <span class="badge badge-warning">{{ $pendingCount }} {{ __trans('pending_verification') }}</span>
                </div>
            </div>
        </div>

        <div class="card card-table">
            <div class="card-body">
                <table class="table table-hover">
                    <thead><tr><th>{{ __trans('employee') }}</th><th>{{ __trans('regime') }}</th><th>{{ __trans('investment_declarations') }}</th><th>{{ __trans('action') }}</th></tr></thead>
                    <tbody>
                        @foreach ($declarations as $declaration)
                        <tr>
                            <td>{{ $declaration->user->name ?? 'N/A' }}</td>
                            <td><span class="badge badge-info">{{ ucfirst($declaration->regime_choice) }}</span></td>
                            <td>{{ $declaration->investmentDeclarations->count() }} ({{ $declaration->investmentDeclarations->where('status', 'pending')->count() }} {{ __trans('pending') }})</td>
                            <td><a href="{{ route('backend.indian-payroll.tax-declarations.show', $declaration) }}" class="btn btn-sm btn-info"><i class="fa fa-eye"></i></a></td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
                {{ $declarations->links() }}
            </div>
        </div>
    </div>
</div>
@endsection
