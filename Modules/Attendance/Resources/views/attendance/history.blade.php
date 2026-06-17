@extends('layouts.backend')

@push('css')
<link rel="stylesheet" href="{{asset('assets/backend/plugins/flatpickr/flatpickr.min.css')}}">
@endpush
@section('content')
<!-- Page Wrapper -->
<div class="page-wrapper bg-white">
    <div class="content container-fluid">
        <!-- Page Header -->
        <div class="page-header">
            <!-- <form action="{{route('backend.payslip.user-payslip.store')}}" method="POST" class="ajax-form-submit reset">
                @csrf -->
            <div class="row align-items-center">
                <div class="col">
                    <h3 class="page-title">{{ __trans('attendance_history') }}</h3>
                    <ul class="breadcrumb">
                        <li class="breadcrumb-item"><a
                                href="{{ route('backend.dashboard') }}">{{ __trans('dashboard') }}</a>
                        </li>
                        <li class="breadcrumb-item active">{{__trans('attendance')}}</li>
                        <li class="breadcrumb-item active">{{ __trans('checkin_history') }}</li>
                    </ul>

                </div>

                <div class="col-auto">
                    <div class="d-flex align-items-center gap-2">



                        {{-- Import --}}
                        <button class="btn btn-warning btn-sm" data-bs-toggle="modal" data-bs-target="#import-modal">
                            <i class="fas fa-file-excel me-1"></i> {{ __trans('import') }}
                        </button>

                        {{-- Export --}}
                        <form action="{{ route('backend.user.attendance.history.export', [$user]) }}" method="POST"
                            class="m-0">
                            @csrf
                            <button type="submit" class="btn btn-success btn-sm">
                                <i class="fa fa-file-excel me-1"></i> Export
                            </button>
                        </form>

                    </div>
                </div>


            </div>
            <!-- </form> -->
            <form action="{{route('backend.payslip.user-payslip.index')}}" method="GET"
                id="select-month-dropdown select-year-dropdown" class="ajax-form-submit reset">
                @csrf
                <div class="row align-items-center">
                    <div class="col">
                        <h5>{{ __trans('employee_checkin_history') }}: {{ $userName }}</h5>
                    </div>
                </div>
            </form>
        </div>

        <!-- /Page Header -->
        <div class="row">
            <div class="col-sm-12">
                <div class="card card-table">
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table text-center table-hover" id="dataTable">
                                <thead class="thead-light">
                                    <tr>
                                        <th>#</th>
                                        <th>{{__trans('date')}}</th>
                                        <th>{{__trans('time')}}</th>
                                        <th>{{__trans('type')}}</th>
                                        <th>{{__trans('mode')}}</th>
                                        <th>{{__trans('checkout_reason')}}</th>
                                        <th>{{__trans('is_rider')}}</th>
                                        <th>{{__trans('location')}}</th>
                                        <th>{{__trans('branch')}}</th>
                                        <!-- <th>{{__trans('latitude')}}</th> -->
                                        <!-- <th>{{__trans('longitude')}}</th> -->
                                    </tr>
                                </thead>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<div class="modal" id="editModal"></div>
<div class="modal fade" id="import-modal" tabindex="-1" aria-labelledby="importModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">

            <form action="{{ route('backend.attendance.import') }}" method="POST" enctype="multipart/form-data"
                class="ajax-form-submit reset">
                @csrf

                <div class="modal-header">
                    <h5 class="modal-title">{{ __trans('import_records') }}</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>

                <div class="modal-body">

                    {{-- Month --}}
                    <div class="mb-3">
                        <label class="form-label">{{ __trans('month') }}</label>
                        <select name="month" id="import_month" class="form-control" required>
                            @for($m=1;$m<=12;$m++) <option value="{{ $m }}">{{ date('F', mktime(0,0,0,$m,1)) }}</option>
                                @endfor
                        </select>
                    </div>

                    {{-- Year --}}
                    <div class="mb-3">
                        <label class="form-label">{{ __trans('year') }}</label>
                        <select name="year" id="import_year" class="form-control" required>
                            @for($y = now()->year; $y >= now()->year - 3; $y--)
                            <option value="{{ $y }}">{{ $y }}</option>
                            @endfor
                        </select>
                    </div>

                    {{-- File --}}
                    <div class="mb-3">
                        <label class="form-label">
                            {{ __trans('Upload_excel_file') }}
                            (
                            <a href="#" id="downloadSample">
                                {{ __trans('download_sample') }}
                            </a>
                            )
                        </label>
                        <input type="file" name="import_file" class="form-control" accept=".xlsx" required>
                    </div>

                </div>

                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                        {{ __trans('close') }}
                    </button>
                    <button type="submit" class="btn btn-primary">
                        {{ __trans('save') }}
                    </button>
                </div>

            </form>
        </div>
    </div>
</div>


<!-- /Page Wrapper -->
@endsection
@push('scripts')
<script src="{{asset('assets/backend/plugins/flatpickr/flatpickr.min.js')}}"></script>
<script>
loadAjaxSelect2();
var table = $('#dataTable').DataTable({
    processing: true,
    serverSide: true,
    ajax: {
        url: "{{route('backend.user.attendance.history',[$user])}}",
    },
    columns: [{
            data: 'id',
            name: 'id'
        },
        {
            data: 'date',
        },
        {
            data: 'time',
            name: 'time',
        },
        {
            data: 'type',
            name: 'type',
        },
        {
            data: 'mode',
            name: 'mode',
        },
        {
            data: 'checkout_reason',
            name: 'checkout_reason',
        },
        {
            data: 'is_rider',
            name: 'is_rider',
        },
        {
            data: 'location',
            name: 'location',
        },
        {
            data: 'branch',
            name: 'branch',
        },
        // {
        //     data: 'latitude',
        //     name: 'latitude',
        // },
        // {
        //     data: 'longitude',
        //     name: 'longitude',
        // },
    ]
});
</script>
<script>
document.addEventListener('DOMContentLoaded', function() {

    function updateSampleLink() {
        let month = document.getElementById('import_month').value;
        let year = document.getElementById('import_year').value;

        let url = "{{ route('backend.attendance.sample.export') }}" +
            "?month=" + month +
            "&year=" + year +
            "&user_id={{ $user }}";


        document.getElementById('downloadSample').setAttribute('href', url);
    }

    document.getElementById('import_month').addEventListener('change', updateSampleLink);
    document.getElementById('import_year').addEventListener('change', updateSampleLink);

    updateSampleLink(); // initial load
});
</script>


@endpush
