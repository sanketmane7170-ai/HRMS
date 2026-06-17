@extends('layouts.backend')
@section('content')
<div class="page-wrapper">
    <div class="content container-fluid">
        <div class="page-header">
            <div class="row align-items-center">
                <div class="col"><h3 class="page-title">{{ __trans('settlement') }} — {{ $settlement->user->name }} <span class="badge badge-{{ $settlement->status === 'approved' ? 'success' : 'warning' }}">{{ $settlement->status }}</span></h3></div>
                <div class="col-auto">
                    @if($settlement->status === 'draft')
                    <form method="POST" action="{{ route('backend.indian-payroll.settlements.approve', $settlement) }}" class="d-inline">
                        @csrf
                        <button type="submit" class="btn btn-success">{{ __trans('approve') }}</button>
                    </form>
                    @endif
                    <a href="{{ route('backend.indian-payroll.settlements.download', $settlement) }}" class="btn btn-primary">{{ __trans('download_pdf') }}</a>
                </div>
            </div>
        </div>

        @if(session('success'))<div class="alert alert-success">{{ session('success') }}</div>@endif

        <div class="card"><div class="card-body">
            @include('indianpayroll::settlement.partials.settlement_body')
        </div></div>
    </div>
</div>
@endsection
