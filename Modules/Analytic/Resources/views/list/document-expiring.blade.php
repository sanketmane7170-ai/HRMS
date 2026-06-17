@extends('layouts.backend')

@section('content')
<div class="page-wrapper">
    <div class="content container-fluid">

        {{-- Page Header --}}
        <div class="page-header">
            <div class="row align-items-center">
                <div class="col">
                    <h3 class="page-title">{{ __trans('Document Expiring List') }}</h3>
                    <ul class="breadcrumb">
                        <li class="breadcrumb-item">
                            <a href="{{ route('backend.dashboard') }}">{{ __trans('Dashboard') }}</a>
                        </li>
                        <li class="breadcrumb-item active">
                            {{ __trans('Document Expiring List') }}
                        </li>
                    </ul>
                </div>
            </div>
        </div>
        {{-- /Page Header --}}

        <div class="row">
            <div class="col-sm-12">
                <div class="card card-table">
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-hover table-bordered w-100" id="dataTable">
                                <thead class="thead-light">
                                    <tr>
                                        <th>#</th>
                                        <th>{{ __trans('Employee') }}</th>
                                        <th>{{ __trans('Document Type') }}</th>
                                        <th>{{ __trans('Expiry Date') }}</th>
                                        <th>{{ __trans('Status') }}</th>
                                        <th>{{ __trans('Action') }}</th>
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
@endsection

@push('scripts')
<script>
$(function () {
    $('#dataTable').DataTable({
        processing: true,
        serverSide: true,
        responsive: true,
        ajax: "{{ route('backend.analytic.documetsexpiring.list') }}",
        columns: [
            { data: 'DT_RowIndex', name: 'DT_RowIndex', orderable: false, searchable: false },
            { data: 'user.name', name: 'user.name' },
            { data: 'type', name: 'type' },
            { data: 'expiry_date', name: 'expiry_date' },
            { data: 'status', name: 'status', orderable: false, searchable: false },
            { data: 'action', name: 'action', orderable: false, searchable: false }
        ]
    });
});
</script>
@endpush
