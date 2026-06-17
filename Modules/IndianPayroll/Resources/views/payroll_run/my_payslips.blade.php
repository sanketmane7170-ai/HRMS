@extends('layouts.backend')
@section('content')
<div class="page-wrapper">
    <div class="content container-fluid">
        <div class="page-header">
            <div class="row align-items-center">
                <div class="col"><h3 class="page-title">{{ __trans('my_payslips') }}</h3></div>
            </div>
        </div>

        <div class="card card-table">
            <div class="card-body">
                <table class="table table-hover">
                    <thead><tr><th>{{ __trans('period') }}</th><th>{{ __trans('net_pay') }}</th><th>{{ __trans('status') }}</th><th>{{ __trans('action') }}</th></tr></thead>
                    <tbody>
                        @foreach ($payslips as $payslip)
                        <tr>
                            <td>{{ \Carbon\Carbon::create($payslip->run->year, $payslip->run->month, 1)->format('F Y') }}</td>
                            <td>{{ number_format($payslip->net_pay, 2) }}</td>
                            <td><span class="badge badge-success">{{ $payslip->status }}</span></td>
                            <td><a href="{{ route('backend.my-indian-payroll.payslips.download', $payslip) }}" class="btn btn-sm btn-primary"><i class="fa fa-download"></i></a></td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
                {{ $payslips->links() }}
            </div>
        </div>
    </div>
</div>
@endsection
