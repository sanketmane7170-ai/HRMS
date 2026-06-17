@extends('layouts.backend')
@section('content')

<!-- Page Wrapper -->
<div class="page-wrapper">
    <div class="content container-fluid">
        <!-- Page Header -->
        <div class="page-header">
            <div class="row align-items-center">
                <div class="col">
                    <h3 class="page-title">{{ __trans('user_promotion_list') }}</h3>
                    <ul class="breadcrumb">
                        <li class="breadcrumb-item">
                            <a href="{{ route('backend.dashboard') }}">{{ __trans('dashboard') }}</a>
                        </li>
                        <li class="breadcrumb-item active">{{ __trans('user_promotion_list') }}</li>
                    </ul>
                </div>
                <div class="col-auto">
                    @can('Create User Promotion')
                    <a href="{{ route('backend.user-promotions.create') }}" data-bs-toggle="modal" data-bs-target="#addResourceModal" class="btn btn-primary btn-sm me-1">
                        <i class="fas fa-plus"></i>
                    </a>
                    @endcan
                </div>
            </div>
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
                                        <th>{{ __trans('employee_name') }}</th>
                                        <th>{{ __trans('old_designation') }}</th>
                                        <th>{{ __trans('new_designation') }}</th>
                                        <th>{{ __trans('promotion_date') }}</th>
                                        <th>{{ __trans('remarks') }}</th>
                                        <th>{{ __trans('actions') }}</th>
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
<!-- /Page Wrapper -->

@include('backend.user-promotions.create')
<div id="editModal" class="modal" role="dialog" aria-labelledby="myModalLabel" aria-modal="true"></div>

@endsection

@push('scripts')
<script type="text/javascript">
    var table = $('#dataTable').DataTable({
        processing: true,
        serverSide: true,
        ajax: {
            url: "{{ route('backend.user-promotions.index') }}",
        },
        columns: [{
                data: 'DT_RowIndex',
                name: 'id'
            },
            {
                data: 'user',
                name: 'user.name'
            },
            {
                data: 'old_designation',
                name: 'oldDesignation.name'
            },
            {
                data: 'new_designation',
                name: 'newDesignation.name'
            },
            {
                data: 'promotion_date',
                name: 'promotion_date'
            },
            {
                data: 'remarks',
                name: 'remarks'
            },
            {
                data: 'action',
                name: 'action',
                orderable: false,
                searchable: false
            },
        ]
    });
</script>
<script>
    $(document).ready(function() {

        // When user changes
        $('#user_id').on('change', function() {
            var userId = $(this).val();

            if (userId) {
                var url = "{{ route('backend.users.designation', ':id') }}";
                url = url.replace(':id', userId);

                $.get(url, function(res) {
                    if (res.success) {
                        $('#old_designation_id').val(res.designation_id).trigger('change');
                    }
                });


            } else {
                // Reset old designation if no user selected
                $('#old_designation_id').val('').trigger('change');
            }
        });

    });
</script>
@endpush
