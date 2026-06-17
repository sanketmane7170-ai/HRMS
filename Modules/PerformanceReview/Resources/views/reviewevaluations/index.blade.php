@extends('layouts.backend')
@section('content')

<!-- Page Wrapper -->
<div class="page-wrapper">
    <div class="content container-fluid">

        <!-- Page Header -->
        <div class="page-header">
            <div class="row align-items-center">
                <div class="col">
                    <h3 class="page-title">{{ __trans('Review Evaluations') }}</h3>
                    <ul class="breadcrumb">
                        <li class="breadcrumb-item">
                            <a href="{{ route('backend.dashboard') }}">{{ __trans('Dashboard') }}</a>
                        </li>
                        <li class="breadcrumb-item active">{{ __trans('Evaluate Submitted Reviews') }}</li>
                    </ul>
                </div>
            </div>
        </div>
        <!-- /Page Header -->

        <div class="row">
            <div class="col-sm-12">
                <div class="card card-table">
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-hover" id="evaluationTable">
                                <thead class="thead-light">
                                    <tr>
                                        <th>#</th>
                                        <th>Review ID</th>
                                        <th>Question Set</th>
                                        <th>Employees Completed</th>
                                        <th>Employee Response</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody></tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>

    </div>
</div>
<!-- /Page Wrapper -->

@endsection

@push('scripts')
<script>
   let table = $('#evaluationTable').DataTable({
    processing: true,
    serverSide: true,
    ajax: "{{ route('evaluate.index') }}",
    order: [[1, 'desc']],
    columns: [
        { data: 'DT_RowIndex', name: 'DT_RowIndex', orderable: false, searchable: false },
        { data: 'id', name: 'id' },
         { data: 'question_set', name: 'question_set' },
        { data: 'employees', name: 'employees' },
        { data: 'employee_responses', name: 'employee_responses' },
        { data: 'action', name: 'action', orderable: false, searchable: false },
    ]
});
</script>
@endpush
