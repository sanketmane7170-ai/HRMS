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
                    <h3 class="page-title">{{__trans('accruals_report')}}</h3>
                    <ul class="breadcrumb">
                        <li class="breadcrumb-item"><a href="{{route('backend.reports')}}">{{__trans('Reports')}}</a>
                        </li>
                        <li class="breadcrumb-item active">{{__trans('accruals_report')}}</li>
                    </ul>

                </div>
                {{-- <div class="col-auto">
                    <a href="{{route('backend.reports.accruals_report_export')}}" class="btn btn-sm btn-success mt-2">
                <i class="fa fa-file-excel mr-2" style="display: inline"></i>Export
                </a>
            </div> --}}
        </div>
        <div class="row ">


            <div class="">
                <form action="{{route('backend.reports.accruals_report_search')}}" enctype="multipart/form-data"
                    method="POST">
                    @csrf

                    <div class="row">
                        <div class="col-sm-2">
                            <div class="form-group ">

                                <select name="department_id" id="department" class="form-control select">

                                    <option value="">{{__trans('select_branch')}}</option>
                                    <option @if($departmentId=='all' ) selected @endif value="all">
                                        {{__trans('all')}}
                                    </option>
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
                                    <input type="text" name="search_emp" id="search_emp"
                                        placeholder="{{__trans('Employee')}}" class="form-control"
                                        value="{{$searchEmp}}">
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
                </form>
            </div>
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
                        <thead>
                            <tr>
                                <th scope="col" style="background-color: #042356;" class="text-white">
                                    <strong>S.No.</strong>
                                </th>
                                <th scope="col" style="background-color: #042356;" class="text-white">
                                    <strong>Employee ID</strong>
                                </th>
                                <th scope="col" style="background-color: #042356;" class="text-white">
                                    <strong>Employee Name</strong>
                                </th>
                                <th scope="col" style="background-color: #042356;" class="text-white">
                                    <strong>Department</strong>
                                </th>
                                <th scope="col" style="background-color: #042356;" class="text-white">
                                    <strong>Designation</strong>
                                </th>
                                <th scope="col" style="background-color: #042356;" class="text-white">
                                    <strong>Basic</strong>
                                </th>
                                <th scope="col" style="background-color: #042356;" class="text-white">
                                    <strong>HRA</strong>
                                </th>
                                <th scope="col" style="background-color: #042356;" class="text-white">
                                    <strong>TA</strong>
                                </th>
                                <th scope="col" style="background-color: #042356;" class="text-white">
                                    <strong>Other Allow</strong>
                                </th>

                                <th scope="col" style="background-color: #042356;" class="text-white">
                                    <strong>Salary Gross</strong>
                                </th>
                                <th scope="col" style="background-color: #042356;" class="text-white">
                                    <strong>Gratuity</strong>
                                </th>
                                <th scope="col" style="background-color: #042356;" class="text-white">
                                    <strong>Leave Salary</strong>
                                </th>
                                <th scope="col" style="background-color: #042356;" class="text-white">
                                    <strong>Air Fare</strong>
                                </th>
                                <th scope="col" style="background-color: #042356;" class="text-white">
                                    <strong>Medical Insurance</strong>
                                </th>
                                <th scope="col" style="background-color: #042356;" class="text-white">
                                    <strong>Visa</strong>
                                </th>
                                <th scope="col" style="background-color: #042356;" class="text-white">
                                    <strong>Bonus</strong>
                                </th>
                                <th scope="col" style="background-color: #042356;" class="text-white">
                                    <strong>Total Accruals</strong>
                                </th>
                                <th scope="col" style="background-color: #042356;" class="text-white">
                                    <strong>Total {{ \Carbon\Carbon::now()->format('F Y') }}</strong>
                                </th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($accruals as $row=> $accrual)


                            <tr>
                                <td>
                                    <small>{{$row+1 }}</small>
                                </td>

                                <td>
                                    <small>{{ $accrual['employee_id'] }}</small>
                                </td>
                                <td>
                                    <small>{{ $accrual['name'] }}</small>
                                </td>
                                <td>
                                    <small>{{ $accrual['department_name'] }}</small>
                                </td>
                                <td>
                                    <small>{{ $accrual['designation_name'] }}</small>
                                </td>
                                <td>
                                    <small>{{ $accrual['baisc'] }}</small>
                                </td>
                                <td>
                                    <small>{{ $accrual['hra'] }}</small>
                                </td>
                                <td>
                                    <small>{{ $accrual['travel_allowance'] }}</small>
                                </td>
                                <td>
                                    <small>{{ $accrual['other_allowance'] }}</small>
                                </td>

                                <td>
                                    <small>{{ $accrual['gross'] }}</small>
                                </td>
                                <td>
                                    <small>{{ $accrual['gratuity'] }}</small>
                                </td>
                                <td>
                                    <small>{{ $accrual['leave_salary'] }}</small>
                                </td>
                                <td>
                                    <small>{{ $accrual['air_fair'] }}</small>
                                </td>
                                <td>
                                    <small>{{ $accrual['medical_insurance'] }}</small>
                                </td>
                                <td>
                                    <small>{{ $accrual['visa'] }}</small>
                                </td>
                                <td>
                                    <small>{{ $accrual['bonus'] }}</small>
                                </td>
                                <td>
                                    <small>{{ $accrual['total_accruals'] }}</small>
                                </td>
                                <td>
                                    <small>{{ $accrual['month_accruals'] }}</small>
                                </td>


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
</div>

@endsection


@push('scripts')
<script src="{{asset('assets/backend/plugins/flatpickr/flatpickr.min.js')}}"></script>
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/plugins/monthSelect/style.css">
<script src="https://cdn.jsdelivr.net/npm/flatpickr/dist/plugins/monthSelect/index.js"></script>

<script>
flatpickr("input.datepicker", {
    dateFormat: "Y-M",
    enableTime: false,
    plugins: [
        new monthSelectPlugin({ // Use the month selection plugin
            dateFormat: "Y-m", // Display format for the selected month
            // altFormat: "F Y", // Alternate format for display
            theme: "light" // Optional: choose a theme
        })
    ],
});
</script>
<!-- <script>
$(document).ready(function() {
    $('#example').DataTable({
        scrollY: '400px', // Set vertical scroll height
        scrollX: true, // Enable horizontal scrolling
        scrollCollapse: true, // Collapse height if fewer rows exist
        paging: true // Enable pagination
    });
});
</script> -->
<script>
$(document).ready(function() {
    $('#datatable').DataTable({
        scrollY: '400px', // Set vertical scroll height
        scrollX: true, // Enable horizontal scrolling
        scrollCollapse: true,
        paging: true,
        fixedColumns: {
            leftColumns: 2 // Freeze the first 2 columns
        }
    });
});
</script>
@endpush
