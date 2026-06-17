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
                        <li class="breadcrumb-item"><a href="{{route('backend.reports')}}">{{__trans('Reports')}}</a>
                        </li>
                        <li class="breadcrumb-item active">{{__trans('leaves_report')}}</li>
                    </ul>

                </div>
                {{-- <div class="col-auto">
                    <a href="{{route('backend.reports.leaves_report_export')}}" class="btn btn-sm btn-success mt-2">
                <i class="fa fa-file-excel mr-2" style="display: inline"></i>Export
                </a>
            </div> --}}
        </div>
        <div class="row ">


            <div class="">
                <form action="{{route('backend.reports.leaves_report_search')}}" enctype="multipart/form-data" method="POST">
                    @csrf

                    <div class="row">

                        {{-- <div class="col-sm-2">
                            <div class="form-group">

                                <input type="text" class="form-control datepicker" name="month_year" value="{{ isset($month_year) ? $month_year : "" }}">
                            </div>
                        </div> --}}
                        <div class="col-sm-2">
                            <div class="form-group ">

                                <select name="department_id" id="department" class="form-control select">
                                    
                                    <option value="">{{__trans('select_branch')}}</option>
                                    <option @if($departmentId=='all' ) selected @endif value="all">
                                        {{__trans('all')}}</option>
                                    @foreach (\App\Models\Department::all() as $department)
                                    <option @if($department->id == $departmentId) selected @endif
                                        value="{{$department->id}}">{{$department->name}}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        <div class="col-sm-2">
                            <div class="form-group ">
                                <div class="form-group">
                                    <input type="text" name="search_emp" id="search_emp" placeholder="{{__trans('Employee')}}" class="form-control" value="{{$searchEmp}}">
                                </div>
                            </div>
                        </div>
                        <div class="col-sm-3">

                            <button type="submit" name="button" value="submit" id="searchLeave" class="btn btn-primary">
                                <i class="fa fa-search mr-2" style="display: inline"></i>Search
                            </button>
                            <button type="submit" name="button" value="export" id="export" class="btn btn-success">
                                <i class="fa fa-file-excel mr-2" style="display: inline"></i>Export
                            </button>
                        </div>



                    </div>



            </div>
            </form>

        </div>

    </div>


</div>

<!-- /Page Header -->
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
                                <small>({{ !empty($user->department?->name ?? 'NA') ? $user->department?->name ?? 'NA' : "" }})</small>
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
</div>
</div>

@endsection


@push('scripts')
<script src="{{asset('assets/backend/plugins/flatpickr/flatpickr.min.js')}}"></script>
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/plugins/monthSelect/style.css">
<script src="https://cdn.jsdelivr.net/npm/flatpickr/dist/plugins/monthSelect/index.js"></script>

<script>
    flatpickr("input.datepicker", {
        dateFormat: "Y-M"
        , enableTime: false
        , plugins: [
            new monthSelectPlugin({ // Use the month selection plugin
                dateFormat: "Y-m", // Display format for the selected month
                // altFormat: "F Y", // Alternate format for display
                theme: "light" // Optional: choose a theme
            })
        ]
    , });

</script>
@endpush

