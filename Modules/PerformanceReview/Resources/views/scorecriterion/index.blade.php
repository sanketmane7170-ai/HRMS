@extends('layouts.backend')
@section('content')

<div class="page-wrapper">
    <div class="content container-fluid">
        <div class="page-header">
            <div class="row align-items-center">
                <div class="col">
                    <h3 class="page-title">{{ __trans('Score Criteria List') }}</h3>
                    <ul class="breadcrumb">
                        <li class="breadcrumb-item"><a href="{{ route('backend.dashboard') }}">{{ __trans('dashboard') }}</a></li>
                        <li class="breadcrumb-item active">{{ __trans('Score Criteria List') }}</li>
                    </ul>
                </div>
                <div class="col-auto">
                    <a href="{{ route('scorecriterion.create') }}" class="btn btn-primary me-1 edit-button">
                        <i class="fas fa-plus"></i>
                    </a>
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
                                        <th>{{ __trans('Title') }}</th>
                                        <th>{{ __trans('Min Score') }}</th>
                                        <th>{{ __trans('Max Score') }}</th>
                                        <th>{{ __trans('Description') }}</th>
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

@push('scripts')
<script>
    var table = $('#dataTable').DataTable({
        processing: true,
        serverSide: true,
        order: [[0, 'desc']],
        ajax: "{{ route('scorecriterion.index') }}",
        columns: [
            { data: 'DT_RowIndex', name: 'id' },
            { data: 'title', name: 'title' },
            { data: 'min_score', name: 'min_score' },
            { data: 'max_score', name: 'max_score' },
            { data: 'description', name: 'description' },
            { data: 'action', orderable: false, searchable: false }
        ]
    });
</script>
@endpush
