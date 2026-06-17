@extends('layouts.backend')
@section('content')

<!-- Page Wrapper -->
<div class="page-wrapper">
    <div class="content container-fluid">
        <!-- Page Header -->
        <div class="page-header">
            <div class="row align-items-center">
                <div class="col">
                    <h3 class="page-title">{{__trans('mass_allowance')}}</h3>
                    <ul class="breadcrumb">
                        <li class="breadcrumb-item"><a href="{{route('backend.dashboard')}}">{{__trans('dashboard')}}</a>
                        </li>
                        <li class="breadcrumb-item active">{{__trans('mass_allowance')}}</li>
                    </ul>
                </div>
                <div class="col-auto">
                </div>
            </div>
        </div>
        {{--  search  --}}
        <form action="{{route('backend.payroll.user.search.bulk.allowance')}}" id="search" enctype="multipart/form-data" method="POST">
            @csrf
            <div class="row">
                <div class="col-xl-8 col-12">
                    <div class="card bg-white">
                        <div class="card-body">
                            <div class="row" id="custom_container">
                                <div class="col-lg-5">
                                    <div class="form-group">
                                        <label>{{__trans('Employee')}}</label>
                                        <select name="search_emp" id="search_emp" class="form-control select">
                                            <option value="">{{__trans('select_employee')}}</option>
                                            @foreach($userslist as $employee)
                                                <option @if($employee->id == $searchEmp) selected @endif value="{{$employee->id}}">{{$employee->name}}</option>
                                            @endforeach
                                        </select>
                                        {{--  <input type="text" name="search_emp" id="search_emp" class="form-control" value="{{@$searchEmp}}">  --}}
                                    </div>
                                </div>
                                <div class="col-lg-5">
                                    <div class="form-group">
                                        <label>{{__trans('department')}}</label>
                                        <select name="department_id" id="department" class="form-control select">
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
                                        <a id="searchdepart" class="btn btn-primary">
                                            <i class="fa fa-search mr-2" style="display: inline"></i>Search
                                        </a>
                                        {{--  @if (count($users) > 0)
                                            <a href="{{route('backend.apparel.report.print', [$departmentId, $searchEmp])}}" class="btn btn-sm btn-success mt-2">
                                                <i class="fa fa-file-excel mr-2" style="display: inline"></i>Export
                                            </a>
                                        @endif  --}}
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </form>
        {{--  end  --}}
        @if($search=='true')
        <!-- /Page Header -->
        <div class="row">
            <div class="col-sm-12">
                <div class="card card-table">
                    <div class="card-body">
                        <div class="table-responsive">
                            <form action="{{route('backend.payroll.user.bulk.allowance.store')}}" id="storeallowance" method="POST">
                                @csrf
                                <table class="table text-center table-hover" id="dataTable">
                                    <thead class="thead-light">
                                        <tr>
                                            <th style="background-color: #626568" class="text-white">Employee Name</th>
                                            <th style="background-color: #97a2b3" class="text-white">Allowance</th>
                                            <th style="background-color: #97a2b3" class="text-white">Amount</th>
                                            <th style="background-color: #97a2b3" class="text-white">Deduction</th>
                                            <th style="background-color: #97a2b3" class="text-white">Amount</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($users as $employee)
                                            @php
                                                $current_month = date('m');
                                                $current_year = date('Y');

                                                $deduction = Modules\Payroll\Entities\SetAllowanceDeducation::where('type', 2)->orderBy('id', 'desc')->get();
                                                $allowance = Modules\Payroll\Entities\SetAllowanceDeducation::where('type', 1)->orderBy('id', 'desc')->get();
                                            @endphp
                                            <tr>
                                                <th style="background-color: #97a2b3" class="text-white">
                                                    <input type="hidden" name="emp_id[]" id="emp_id" value="{{ $employee->id }}" class="form-control">
                                                    <strong>{{ $employee->name }}</strong><br>
                                                </th>
                                                <td class="addnewallti">
                                                    <select name="new_al_title_{{ $employee->id }}[]" id="allow_id" class="form-control allow_select">
                                                        <option value="" style="background-color: #7db4eb" >{{__trans('select_allowance')}}</option>
                                                        @foreach($allowance as $allowa)
                                                                <option @if($allowa->id == 0) selected @endif value="{{$allowa->name}}">{{$allowa->name}}</option>
                                                        @endforeach
                                                    </select>
                                                </td>
                                                <td class="addnewallam">
                                                    <input type="number" name="new_al_amount_{{ $employee->id }}[]" id="amount" placeholder="Enter allowance amount" class="form-control">
                                                </td>
                                                <td>
                                                    <a class="add_newAL"> <i class="fa fa-plus"></i></a>
                                                    <br>
                                                    <a class="remove_newAL"> <i class="fa fa-minus"></i></a>
                                                </td>
                                                <td class="addnewdeduti">
                                                    <select name="new_dedu_title_{{ $employee->id }}[]" id="dedu_id" class="form-control dedu_select">
                                                        <option value="" style="background-color: #7db4eb" >{{__trans('select_deduction')}}</option>
                                                        @foreach($deduction as $dedu)
                                                            <option @if($dedu->id == 0) selected @endif value="{{$dedu->name}}">{{$dedu->name}}</option>
                                                        @endforeach
                                                    </select>
                                                </td>
                                                <td class="addnewdeduam">
                                                    <input type="number" name="new_dedu_amount_{{ $employee->id }}[]" id="deduamount" placeholder="Enter deduction amount" class="form-control">
                                                </td>
                                                <td>
                                                    <a class="add_newdedu"> <i class="fa fa-plus"></i></a>
                                                    <br>
                                                    <a class="remove_newdedu"> <i class="fa fa-minus"></i></a>
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                                <br>
                                <br>
                                <div class="col-auto" style="float: right">
                                    <a id="storeAllo" class="btn btn-sm inline-block me-2  btn-success"> <i class="fa fa-save"></i> Save Bulk Allowance</a>
                                </div>
                                <br>
                                <br>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        @endif
    </div>
</div>
<!-- /Page Wrapper -->
<div id="editModal" class="modal" role="dialog" aria-labelledby="myModalLabel" aria-modal="true">

</div>
@endsection

@push('css')
<link rel="stylesheet" href="{{asset('assets/backend/plugins/flatpickr/flatpickr.min.css')}}">
@endpush

@push('scripts')
<script src="{{asset('assets/backend/plugins/flatpickr/flatpickr.min.js')}}"></script>
<script>
    $('#searchdepart').on('click', function(event) {
        event.preventDefault(); // Prevent default behavior
        $('#search').submit();  // Submit the search form only
    });
    
    // For Store Allowance Form (Second Form)
    $('#storeAllo').on('click', function(event) {
        event.preventDefault(); // Prevent default behavior
        $('#storeallowance').submit();  // Submit the store allowance form only
    });
    
    $(document).on('click', '.add_newAL', function (e) {
        e.preventDefault();

        const parentTr = $(this).closest('tr');
        const emp_id = parentTr.find('#emp_id').val();
        const newAllowanceField = parentTr.find('.addnewallti');
        const newAllowanceamField = parentTr.find('.addnewallam');

        const clonedSelect = parentTr.find('select[id="allow_id"]').first().clone();
        clonedSelect.val('');
        clonedSelect.attr('name', `new_al_title_${emp_id}[]`);
        const selectWrapper = $('<div class="form-group mt-2 new_allowance_field"></div>').append(clonedSelect);
        const amountInput = `
            <div class="form-group mt-2 new_amount_field">
                <input type="text" name="new_al_amount_${emp_id}[]" class="form-control" placeholder="{{ __trans('Enter allowance amount') }}">
            </div>
        `;
        newAllowanceField.append(selectWrapper);
        newAllowanceamField.append(amountInput);
    });
    
    $(document).on('click', '.remove_newAL', function (e) {
        e.preventDefault();

        const parentTr = $(this).closest('tr');
        const fieldsToRemove = parentTr.find('.new_allowance_field');
        const fieldsToRemoveam = parentTr.find('.new_amount_field');

        // Remove the last element from the matched fields
        if (fieldsToRemove.length > 0) {
            fieldsToRemove.last().remove();
        }
        if (fieldsToRemoveam.length > 0) {
            fieldsToRemoveam.last().remove();
        }
    });

    //deduction
    $(document).on('click', '.add_newdedu', function (e) {
        e.preventDefault();

        const parentTr = $(this).closest('tr');
        const emp_id = parentTr.find('#emp_id').val();
        const newAllowanceField = parentTr.find('.addnewdeduti');
        const newAllowanceamField = parentTr.find('.addnewdeduam');

        const clonedSelect = parentTr.find('select[id="dedu_id"]').first().clone();
        // Remove the selected value and reset it
        clonedSelect.val('');
        // Update the name attribute so each employee’s field stays unique
        clonedSelect.attr('name', `new_dedu_title_${emp_id}[]`);
        // Wrap it with a form-group
        const selectWrapper = $('<div class="form-group mt-2 new_dedu_field"></div>').append(clonedSelect);
        // ✅ Keep your amount input
        const amountInput = `
            <div class="form-group mt-2 new_deduam_field">
                <input type="text" name="new_dedu_amount_${emp_id}[]" class="form-control" placeholder="{{ __trans('Enter deduction amount') }}">
            </div>
        `;
        // Append both elements
        newAllowanceField.append(selectWrapper);
        newAllowanceamField.append(amountInput);
    });
    $(document).on('click', '.remove_newdedu', function (e) {
        e.preventDefault();

        const parentTd = $(this).closest('tr');
        const fieldsToRemove = parentTd.find('.new_dedu_field');
        const fieldsToRemoveam = parentTd.find('.new_deduam_field');
        
        // Remove the last element from the matched fields
        if (fieldsToRemove.length > 0) {
            fieldsToRemove.last().remove();
        }
        if (fieldsToRemoveam.length > 0) {
            fieldsToRemoveam.last().remove();
        }
    });
    
loadAjaxSelect2();
</script>
@endpush
