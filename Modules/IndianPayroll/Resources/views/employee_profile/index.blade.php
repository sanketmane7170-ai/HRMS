@extends('layouts.backend')
@section('content')
<div class="page-wrapper">
    <div class="content container-fluid">
        <div class="page-header">
            <div class="row align-items-center">
                <div class="col">
                    <h3 class="page-title">{{ __trans('employee_statutory_profiles') }}</h3>
                    <ul class="breadcrumb">
                        <li class="breadcrumb-item"><a href="{{ route('backend.indian-payroll.dashboard') }}">{{ __trans('indian_payroll') }}</a></li>
                        <li class="breadcrumb-item active">{{ __trans('employee_statutory_profiles') }}</li>
                    </ul>
                </div>
                <div class="col-auto">
                    <div class="dropdown">
                        <a href="#" class="btn btn-primary dropdown-toggle" data-bs-toggle="dropdown">
                            <i class="fas fa-plus"></i> {{ __trans('add_profile_for') }}
                        </a>
                        <div class="dropdown-menu" style="max-height:300px;overflow:auto;">
                            @foreach ($usersWithoutProfile as $user)
                                <a class="dropdown-item" href="{{ route('backend.indian-payroll.employee-profiles.create', $user) }}">{{ $user->name }}</a>
                            @endforeach
                        </div>
                    </div>
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
                                        <th>{{ __trans('name') }}</th>
                                        <th>{{ __trans('pan') }}</th>
                                        <th>{{ __trans('uan') }}</th>
                                        <th>{{ __trans('state') }}</th>
                                        <th>{{ __trans('action') }}</th>
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
@endsection

@push('scripts')
<script type="text/javascript">
    $('#dataTable').DataTable({
        processing: true,
        serverSide: true,
        ajax: { url: "{{ route('backend.indian-payroll.employee-profiles.index') }}" },
        columns: [
            { data: 'name' },
            { data: 'pan' },
            { data: 'uan' },
            { data: 'state' },
            { data: 'action', orderable: false, searchable: false },
        ]
    });
</script>
@endpush
