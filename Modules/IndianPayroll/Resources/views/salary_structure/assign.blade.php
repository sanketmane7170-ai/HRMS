@extends('layouts.backend')
@section('content')
<div class="page-wrapper">
    <div class="content container-fluid">
        <div class="page-header">
            <div class="row align-items-center">
                <div class="col">
                    <h3 class="page-title">{{ __trans('assign_salary_structure') }} — {{ $user->name }}</h3>
                </div>
            </div>
        </div>

        @if(session('error'))<div class="alert alert-danger">{{ session('error') }}</div>@endif

        @if($current)
        <div class="alert alert-info">
            {{ __trans('current_ctc') }}: <strong>{{ number_format($current->annual_ctc, 2) }}</strong>
            ({{ __trans('effective_from') }} {{ $current->effective_from->format('d-M-Y') }})
            — {{ __trans('assigning_a_new_structure_below_will_supersede_it') }}
        </div>
        @endif

        <div class="card"><div class="card-body">
            <form method="POST" action="{{ route('backend.indian-payroll.employee-salary-structures.store', $user) }}">
                @csrf
                <div class="form-group">
                    <label>{{ __trans('ctc_template') }}</label>
                    <select name="template_id" class="form-control" required>
                        @foreach ($templates as $template)
                            <option value="{{ $template->id }}">{{ $template->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="form-group">
                    <label>{{ __trans('annual_ctc') }} (Rs.)</label>
                    <input type="number" step="0.01" name="annual_ctc" class="form-control" required value="{{ old('annual_ctc', $current->annual_ctc ?? '') }}">
                </div>
                <div class="form-group">
                    <label>{{ __trans('effective_from') }}</label>
                    <input type="date" name="effective_from" class="form-control" required value="{{ old('effective_from', now()->format('Y-m-d')) }}">
                </div>
                <button type="submit" class="btn btn-primary">{{ __trans('assign') }}</button>
            </form>
        </div></div>
    </div>
</div>
@endsection
