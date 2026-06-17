@extends('layouts.backend')
@section('content')

<div class="page-wrapper">
    <div class="content container-fluid">
        <div class="page-header">
            <div class="row align-items-center">
                <div class="col">
                    <h3 class="page-title">{{ __trans('Question List') }}</h3>
                    <ul class="breadcrumb">
                        <li class="breadcrumb-item"><a href="{{ route('backend.dashboard') }}">{{ __trans('dashboard') }}</a></li>
                        <li class="breadcrumb-item active">{{ __trans('Question List') }}</li>
                    </ul>
                </div>
                <div class="col-auto">
                    @can('Manage Performance Review')
                    <a href="{{ route('question.create') }}" class="btn btn-primary me-1 edit-button">
                        <i class="fas fa-plus"></i>
                    </a>
                    <a href="#" class="btn btn-warning btn-sm me-1" data-bs-toggle="modal" data-bs-target="#import-modal">

                        <i class="fas fa-file-excel"></i> {{__trans('import')}}
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
                                        <th>{{ __trans('Question Set') }}</th>
                                        <th>{{ __trans('Question Text') }}</th>
                                        <th>{{ __trans('Max Score') }}</th>
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
<div class="modal fade" id="import-modal" tabindex="-1" aria-labelledby="importModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <form action="{{ route('importQuesFromExcel')}}" method="POST" enctype="multipart/form-data" class="ajax-form-submit reset">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title" id="importModalLabel">{{ __trans('import_records') }}</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <label class="form-label">{{ __trans('Upload_excel_file') }}
                        ( <a href="{{ route('samplequesexportexcel') }}"> {{ __trans('download_sample') }} </a> )
                    </label>
                    <input type="file" name="import_file" class="form-control" accept=".xlsx">

                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">{{ __trans('close') }}</button>
                    <button type="submit" class="btn btn-primary">{{ __trans('save') }}</button>
                </div>
            </form>
        </div>
    </div>
</div>


@endsection

@push('scripts')
<script>
    var table = $('#dataTable').DataTable({
        processing: true,
        serverSide: true,
        order: [
            [0, 'desc']
        ],
        ajax: "{{ route('question.index') }}",
        columns: [{
                data: 'DT_RowIndex',
                name: 'id'
            },
            {
                data: 'question_set',
                name: 'questionSet.name'
            },
            {
                data: 'question_text',
                name: 'question_text'
            },
            {
                data: 'max_score',
                name: 'max_score'
            },
            {
                data: 'action',
                orderable: false,
                searchable: false
            }
        ]
    });

    loadAjaxSelect2();
</script>
@endpush