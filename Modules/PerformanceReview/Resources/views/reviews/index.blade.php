@extends('layouts.backend')
@section('content')

<div class="page-wrapper">
    <div class="content container-fluid">
        <div class="page-header">
            <div class="row align-items-center">
                <div class="col">
                    <h3 class="page-title">{{ __trans('Performance Review List') }}</h3>
                    <ul class="breadcrumb">
                        <li class="breadcrumb-item"><a href="{{ route('backend.dashboard') }}">{{ __trans('dashboard') }}</a></li>
                        <li class="breadcrumb-item active">{{ __trans('Performance Review List') }}</li>
                    </ul>
                </div>
                <div class="col-auto">
                    @can('Manage Performance Review')
                        <a href="{{ route('performancereview.create') }}" class="btn btn-primary me-1 edit-button">
                            <i class="fas fa-plus"></i>
                        </a>
                    @endcan
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-sm-12">
                <div class="card card-table">
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table text-center table-hover" id="dataTable">
                                <thead class="thead-light">
                                    <tr>
                                        <th>#</th>
                                        <th>{{ __trans('Employees') }}</th>
                                        <!-- <th>{{ __trans('Review Duration') }}</th> -->
                                        <th>{{ __trans('Grade') }}</th>
                                        <th>{{ __trans('Question Set') }}</th>
                                        <th>{{ __trans('Status') }}</th>
                                        <th>{{ __trans('Actions') }}</th>
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

<div id="editModal" class="modal" role="dialog"></div>
@endsection
@push('css')
<link rel="stylesheet" href="{{asset('assets/backend/plugins/flatpickr/flatpickr.min.css')}}">
@endpush
@push('scripts')
<script src="{{asset('assets/backend/plugins/flatpickr/flatpickr.min.js')}}"></script>

<script>
    
    var table = $('#dataTable').DataTable({
        processing: true,
        serverSide: true,
        order: [[0, 'desc']],
        ajax: "{{ route('performancereview.index') }}",
        columns: [
            { data: 'DT_RowIndex', name: 'id' },
            { data: 'employees', name: 'employees.name' },
            // { data: 'duration', name: 'duration.label' },
            { data: 'grade', name: 'grade' },

            { data: 'question_set', name: 'questionSet.name' },
            { data: 'status', name: 'status' },
            { data: 'action', orderable: false, searchable: false }
        ]
    });

    loadAjaxSelect2();
</script>
@endpush
