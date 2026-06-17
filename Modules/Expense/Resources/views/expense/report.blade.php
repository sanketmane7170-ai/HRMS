@extends('layouts.backend')
@push('css')
<link rel="stylesheet" href="{{asset('assets/backend/plugins/flatpickr/flatpickr.min.css')}}">
@endpush
@section('content')

<!-- Page Wrapper -->
<div class="page-wrapper">
    <div class="content container-fluid">
        <!-- Page Header -->
        <div class="page-header">
            <div class="row align-items-center">
                <div class="col">
                    <h3 class="page-title">{{__trans('leaves_report')}}</h3>
                    <ul class="breadcrumb">
                        <li class="breadcrumb-item"><a href="{{route('backend.dashboard')}}">{{__trans('dashboard')}}</a>
                        </li>
                        <li class="breadcrumb-item active">{{__trans('leaves_report')}}</li>
                    </ul>
                </div>
            </div>
        </div>
        <form action="{{route('backend.leaves.report.search')}}" enctype="multipart/form-data" method="POST">
            @csrf
            <div class="row">
                <div class="col-xl-8 col-12">
                    <div class="card bg-white">
                        <div class="card-body">
                            <div class="row" id="custom_container">
                                <div class="col-lg-5">
                                    <div class="form-group">
                                        <label>{{__trans('Employee')}}</label>
                                        <input type="text" name="search_emp" id="search_emp" class="form-control" value="{{$searchEmp}}">
                                    </div>
                                </div>
                                <div class="col-lg-5">
                                    <div class="form-group">
                                        <label>{{__trans('department')}}</label>
                                        <select name="department_id" id="department" class="form-control select">
                                            {{-- <option value="{{$user->department->id}}">{{$user->department?->name ?? 'NA'}}</option> --}}
                                            <option value="">{{__trans('select_department')}}</option>
                                            <option @if($departmentId == 'all') selected @endif value="all">{{__trans('all')}}</option>
                                                @foreach (\App\Models\Department::all() as $department)
                                                    <option @if($department->id == $departmentId) selected @endif value="{{$department->id}}">{{$department->name}}</option>
                                                @endforeach
                                        </select>
                                    </div>
                                </div>
                                <div class="col-md-2">
                                    <div class="mb-3" style="margin-top: 31px !important;">
                                        <button type="submit" id="searchLeave" class="btn btn-primary">
                                            <i class="fa fa-search mr-2" style="display: inline"></i>Search
                                        </button>
                                        @if (count($users) > 0)
                                            <a href="{{route('backend.leaves.report.print', [$departmentId, $searchEmp])}}" class="btn btn-sm btn-success mt-2">
                                                <i class="fa fa-file-excel mr-2" style="display: inline"></i>Export
                                            </a>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </form>
        <!-- /Page Header -->
        @if (count($users) > 0)
            <div class="row">
                <div class="col-sm-12">
                    <div class="card card-table">
                        <div class="card-body">

                            <table class="table table-bordered table-hover">
                                <thead>
                                    <tr>
                                    <th scope="col" style="background-color: #042356; width:100px" class="text-white">
                                        <strong>Employee Name</strong>
                                    </th>
                                        @foreach($types as $type)
                                            <th class="text-center text-white" style="background-color: #042356">
                                                <strong>
                                                    <small><strong>{{$type->name}} ({{ $type->days }})</strong></small>
                                                </strong>
                                            </th>
                                        @endforeach
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($users as $user)
                                        <tr>
                                            <th style="background-color: #042356" class="text-white">
                                                <strong>{{ Str::limit($user->name, 25) }}</strong><br>
                                                <small>({{ $user->employee_id }})</small>
                                                <small>({{ $user->department?->name ?? 'NA' }})</small>
                                            </th>
                                            @foreach($types as $type)
                                                <td>
                                                    <span class="badge badge-primary mb-2">
                                                        <small>{{ calculatePendingLeave($type, $user->id) }}</small>
                                                    </span>
                                                </td>
                                            @endforeach
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        @else
            @if($search)
                <p class="text-danger">{{__trans('no_employee_found')}}</p>
            @endif
        @endif
    </div>
</div>

@endsection

@push('scripts')
<script src="{{asset('assets/backend/plugins/flatpickr/flatpickr.min.js')}}"></script>
<script>

    flatpickr("input.datepicker", {
        dateFormat: "Y-m-d",
    });
</script>
@endpush
