@extends('layouts.backend')

@push('css')
<link rel="stylesheet" href="{{asset('assets/backend/plugins/flatpickr/flatpickr.min.css')}}">
@endpush
@section('content')
<?php
$monthDays = now()->month($month)->daysInMonth;
?>
<!-- Page Wrapper -->
<div class="page-wrapper bg-white">
    <div class="content container-fluid">
        <!-- Page Header -->
        <div class="page-header">
            <div class="row align-items-center">
                <div class="col">
                    <h3 class="page-title">{{ __trans('marked_attendance') }}</h3>
                    <ul class="breadcrumb">
                        <li class="breadcrumb-item"><a
                                href="{{ route('backend.dashboard') }}">{{ __trans('dashboard') }}</a>
                        </li>
                        <li class="breadcrumb-item active">{{ __trans('marked_attendance') }}</li>
                    </ul>
                </div>
                {{--  <div class="col-auto">
                    @can('Generate Attendance')
                    <a href="{{ route('backend.attendances.generate') }}" class="btn btn-success action-button"
                        method=GET redirect
                        data-alert="{{ __trans('are_you_sure_want_to_generate_todays_attendance?') }}">{{ __trans('generate_attendance') }}</a>
                    @endcan
                </div>  --}}
            </div>
        </div>
        <!-- /Page Header -->
        <div class="card_">
            <div class="card-body_ pb-0">
                @include('attendance::attendance.partials.attendance-filter')
            </div>
        </div>
        <div class="row">
            <div class="col-sm-12">

                <div class="card_ card-table">
                    <div class="card-body_">

                        <div class="att-wrapper1">
                            <div class="att-div1">
                            </div>
                        </div>

                        <div class="att-wrapper2">
                            <div class="att-div2">
                            <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <td>{{ __trans('employee') }}</td>
                                        @for ($i = 1; $i <= $monthDays; $i++) <td><?= $i ?></td>
                                            @endfor
                                            <td class="text-end">@if(getSetting('payroll_calculation') == 'hourly'){{ __trans('total_hrs') }} @else {{ __trans('total') }}@endif</td>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($users as $user)
                                    @include('attendance::attendance.partials.user-attendance-row')
                                    @endforeach
                                </tbody>
                            </table>
                            {{ $users->withQueryString()->links() }}
                        </div>
                            </div>
                        </div>

                        
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<div class="modal" id="editModal"></div>
<!-- /Page Wrapper -->
@endsection
@push('scripts')
<script src="{{asset('assets/backend/plugins/flatpickr/flatpickr.min.js')}}"></script>
<script>
loadAjaxSelect2();
</script>
<script>
    flatpickr("input.datepicker", {
        dateFormat: "Y-m-d",
    });
</script>
<script>
    document.addEventListener('DOMContentLoaded', function () {
        const urlParams = new URLSearchParams(window.location.search);
        const page = urlParams.get('page') || 1;
        document.getElementById('current_page_input').value = page;
    });
    document.getElementById('per_page').addEventListener('change', function () {
        document.getElementById('current_page_input').value = 1; // reset page to 1
        document.getElementById('perPageForm').submit();
    });
</script>
@endpush