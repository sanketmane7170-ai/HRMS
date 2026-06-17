@extends('layouts.backend')
@section('content')
<div class="page-wrapper">
    <div class="content container-fluid">
        <div class="page-header">
            <div class="row align-items-center">
                <div class="col"><h3 class="page-title">{{ __trans('statutory_bonus') }} — {{ $financialYear }}</h3></div>
            </div>
        </div>

        @if(session('success'))<div class="alert alert-success">{{ session('success') }}</div>@endif
        @if($errors->any())<div class="alert alert-danger"><ul class="mb-0">@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul></div>@endif

        <div class="card"><div class="card-body">
            <form method="POST" action="{{ route('backend.indian-payroll.statutory-bonus.generate') }}" class="row g-2 align-items-end">
                @csrf
                <div class="col-md-3">
                    <label class="form-label">{{ __trans('financial_year') }}</label>
                    <input type="text" name="financial_year" class="form-control" value="{{ $financialYear }}" placeholder="2025-2026" required>
                </div>
                <div class="col-md-3">
                    <label class="form-label">{{ __trans('bonus_percentage') }} (8.33–20)</label>
                    <input type="number" step="0.01" min="8.33" max="20" name="percentage" class="form-control" value="8.33" required>
                </div>
                <div class="col-md-3">
                    <button class="btn btn-primary">{{ __trans('generate_bonus') }}</button>
                </div>
            </form>
            <p class="text-muted small mt-2 mb-0">{{ __trans('bonus_eligibility_note') }}</p>
        </div></div>

        <div class="card card-table"><div class="card-body">
            <table class="table table-hover">
                <thead><tr>
                    <th>{{ __trans('employee') }}</th>
                    <th class="text-end">{{ __trans('monthly_wage') }}</th>
                    <th class="text-end">{{ __trans('bonus_base') }}</th>
                    <th class="text-end">%</th>
                    <th class="text-end">{{ __trans('months') }}</th>
                    <th class="text-end">{{ __trans('bonus_amount') }}</th>
                    <th>{{ __trans('status') }}</th>
                    <th>{{ __trans('action') }}</th>
                </tr></thead>
                <tbody>
                    @forelse ($bonuses as $b)
                    <tr>
                        <td>{{ $b->user->name ?? 'N/A' }}</td>
                        <td class="text-end">{{ number_format($b->monthly_wage, 2) }}</td>
                        <td class="text-end">{{ number_format($b->bonus_wage_base, 2) }}</td>
                        <td class="text-end">{{ number_format($b->percentage, 2) }}</td>
                        <td class="text-end">{{ $b->months_eligible }}</td>
                        <td class="text-end">{{ number_format($b->bonus_amount, 2) }}</td>
                        <td><span class="badge badge-{{ $b->status === 'paid' ? 'success' : ($b->status === 'approved' ? 'info' : 'warning') }}">{{ ucfirst($b->status) }}</span></td>
                        <td>
                            @if($b->status === 'draft')
                            <form method="POST" action="{{ route('backend.indian-payroll.statutory-bonus.approve', $b) }}" class="d-inline">@csrf<button class="btn btn-sm btn-success">{{ __trans('approve') }}</button></form>
                            @endif
                        </td>
                    </tr>
                    @empty
                    <tr><td colspan="8" class="text-center text-muted">{{ __trans('no_bonus_generated_yet') }}</td></tr>
                    @endforelse
                </tbody>
            </table>
            {{ $bonuses->links() }}
        </div></div>
    </div>
</div>
@endsection
