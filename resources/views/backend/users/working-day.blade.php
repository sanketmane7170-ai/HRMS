@extends('layouts.backend')
@section('content')

<!-- Page Wrapper -->
<div class="page-wrapper">
    <div class="content container-fluid">
        <!-- Page Header -->
        <div class="page-header">
         <form action="{{route('backend.working_day_page')}}" method="GET"
            id="select-month-dropdown select-year-dropdown" class="ajax-form-submit reset">
            @csrf
            <div class="row align-items-center">
                <div class="col">
                    <h3 class="page-title">{{__trans('employee_working_days')}}</h3>
                    <ul class="breadcrumb">
                        <li class="breadcrumb-item"><a href="{{route('backend.dashboard')}}">{{__trans('dashboard')}}</a>
                        </li>
                        <li class="breadcrumb-item active">{{__trans('employee_working_days')}}</li>
                    </ul>
                </div>
                <div class="col-md-2">
                    <div class="form-group">
                        <label><strong>{{ __trans('select_month') }}:</strong></label>
                        <select name="month" class="form-control select-search" id="select-month">
                            @for ($i = 1; $i <= 12; $i++) <option value="{{ $i }}" @if ($month==$i) selected @endif>
                                {{ date('F', strtotime(date('Y') . '-' . $i)) }}</option>
                                @endfor
                        </select>
                    </div>
                </div>
                <div class="col-md-2">
                    <div class="form-group">
                        <label><strong>{{ __trans('select_year') }}:</strong></label>
                        <select name="year" class="form-control select-search" id="select-year">
                            @php
                            $myear = [2022,2023,2024,2025,2026,2027,2028];
                            @endphp
                            @for ($i = 0; $i <= 6; $i++) <option value="{{ $myear[$i]}}" @if ($year==$myear[$i])
                                selected @endif>
                                {{ $myear[$i] }}</option>
                                @endfor
                        </select>
                    </div>
                </div>
                <div class="col-auto">
                    @can('Import User')
                    <a href="" class="btn btn-warning btn-sm me-1 " data-bs-target="#import-modal" data-bs-toggle="modal">
                        <i class="fas fa-file-excel"></i> {{__trans('import')}}
                    </a>
                    @endcan
                </div>
            </div>
          </form> 
        </div>
        <!-- /Page Header -->
        <div class="row">
            <div class="col-sm-12">
                <div class="card ">
                    <div class="card-table">
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table table-center table-hover" id="dataTable">
                                    <thead class="thead-light">
                                        <tr>
                                            <th>#</th>
                                            <th>{{__trans('name')}}</th>
                                            <th>{{__trans('email')}}</th>
                                            <th>{{__trans('total_working_days')}}</th>
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

    <div class="modal fade" id="viewProfile" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="exampleModalLabel">&nbsp;</h5>
                    <a href="#" onclick="closeModal()">
                        <i class="fa fa-times"></i>
                    </a>
                </div>
                <div class="modal-body text-center">
                    <img src="" id="profile" width="300px" height="100%;">
                </div>
            </div>
        </div>
    </div>
    <!-- /Page Wrapper -->
    @include('common.modals.import-workingday-modal',['flag'=>true])
    @endsection

    @push('scripts')
    <script type="text/javascript">
        var table = $('#dataTable').DataTable({
            processing: true,
            serverSide: true,
            ajax: {
                url: "{{route('backend.working_day_page')}}",
            },
            columns: [{
                    data: 'id',
                    name: 'id'
                },
                {
                    data: 'name',
                    name: 'name'
                },
                {
                    data: 'email',
                    name: 'email'
                },
                {
                    data: 'total_working_days',
                    name: 'total_working_days'
                }
            ]
        });
    </script>
    <script>
       $('#select-month, #select-year').on('change', function(e) {
        e.preventDefault();

        // Get the selected month and year values
        var selectedMonth = $('#select-month').val();
        var selectedYear = $('#select-year').val();

        const export_url = "{{ route('backend.workingday.export.excel', ['month' => ':month', 'year' => ':year']) }}"
        .replace(':month', selectedMonth)
        .replace(':year', selectedYear);

        const import_url = "{{ route('backend.workingday.import.excel', ['month' => ':month', 'year' => ':year']) }}"
        .replace(':month', selectedMonth)
        .replace(':year', selectedYear);

        $('#download_sample_unique_url').attr('href', export_url);
        $('#import_working_day_url').attr('action', import_url);

        // Update the DataTable's AJAX URL with the new parameters
        table.ajax.url("{{ route('backend.working_day_page') }}" + "?month=" + selectedMonth + "&year=" +
            selectedYear).load();
    });
    </script>
    @endpush
