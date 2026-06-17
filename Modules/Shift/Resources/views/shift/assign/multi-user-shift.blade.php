<div class="modal-dialog modal-lg">
    <div class="modal-content">
        <div class="modal-header">
            <h4 class="modal-title">{{__trans('assign_shift_to_multiple_employee')}} </h4>
            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <form action="{{ route('backend.assign_shift.toMultipleUser',['id'=>$shift_id]) }}" datatable="true"
            method="POST" class="ajax-form-submit reset">
            @csrf
            <div class="modal-body p-4">
                <div class="row">
                    <div class="col-md-12">
                        <div class="mb-3">
                            <label for="edit-field-1" class="form-label">{{__trans('title')}}</label>
                            <input type="text" name="title" value="{{$shifts->title}}-{{$shifts->type}}"
                                class="form-control" id="edit-field-1" placeholder="" readonly>
                            <input type="hidden" name="shift_id" value="{{$shifts->id}}" class="form-control"
                                id="edit-field-1" placeholder="" readonly>
                        </div>
                    </div>

                    <div class="row" id="letter">
                        @foreach ($shift_schedules as $index => $info )
                        <input type="hidden" name="shift_schedule_id[{{ $index }}]" value="{{$info->id}}"
                            class="form-control" id="edit-field-1" placeholder="" readonly>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="shift_start" class="form-label">Shift Start</label>
                                <input type="text" name="shifts[{{ $index }}][shift_start]"
                                    class="form-control timepicker" id="shift_start" value="{{ $info->shift_start }}"
                                    placeholder="shift start time" readonly required>
                            </div>
                        </div>
                        <div class="col-md-5">
                            <div class="mb-3">
                                <label for="shift_end" class="form-label">Shift End</label>
                                <input type="text" name="shifts[{{ $index }}][shift_end]"
                                    class="form-control timepicker" id="shift_end" value="{{ $info->shift_end }}"
                                    placeholder="shift end time" readonly required>
                            </div>
                        </div>
                        @endforeach
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label for="date" class="form-label">{{__trans('start_date')}}</label>
                            <div class="mb-3">
                                <input required type="text" name="start_date" class="form-control datepicker"
                                    placeholder="{{__trans('select_start_date')}}">
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label for="date" class="form-label">{{__trans('end_date')}}</label>
                            <div class="mb-3">
                                <input required type="text" name="end_date" class="form-control datepicker"
                                    placeholder="{{__trans('select_end_date')}}">
                            </div>
                        </div>
                    </div>


                    <div class="col-md-5">
                        <div class="form-group">
                            <label>{{__trans('department')}}</label>
                            <select name="department_id" id="department" class="form-control select">
                                {{-- <option value="{{$user->department->id}}">{{$user->department?->name ?? 'NA'}}</option> --}}
                                <option value="">{{__trans('select_a_option')}}</option>
                                @if(auth()->user()->hasRole(App\Models\User::ROLE_ADMIN) || auth()->user()->hasRole(App\Models\User::ROLE_SUPER_ADMIN))<option value="0">{{__trans('all')}}</option>@endif
                                @foreach ($departments as $department)
                                <option value="{{$department->id}}">{{$department->name}}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    <div class="col-md-5">
                        <div class="form-group">
                            <label>{{__trans('employee')}}</label>
                            <input type="text" name="search_emp" id="search_emp" placeholder="{{__trans('Employee')}}"
                                class="form-control" value="">
                        </div>
                    </div>
                    <div class="col-md-2">
                        <div class="form-group">
                        
                            <button type="button" name="search" id="search" style="margin-top: 31px;" class="btn btn-primary">
                                <i class="fa fa-search mr-2" style="display: inline"></i>Search
                            </button>
                        </div>
                    </div>

                </div>
                <div id="employee-list" class="mt-4">
                    <h5>Employees</h5>

                    <!-- Select All Checkbox -->
                    <input required type="hidden" name="employee_ids" class="form-control"
                        placeholder="{{__trans('employee_ids')}}">
                    <div class="form-check mb-3">
                        <input class="form-check-input" type="checkbox" id="select-all">
                        <label class="form-check-label" for="select-all">Select All</label>
                    </div>


                    <!-- Employee List in Rows -->
                    <div id="employees-container" class="row g-3">
                        <!-- Individual checkboxes will be dynamically loaded here -->
                    </div>
                </div>

            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary waves-effect"
                    data-bs-dismiss="modal">{{__trans('close')}}</button>
                <button type="submit" class="btn btn-info waves-effect waves-light">{{__trans('add')}} </button>
            </div>
        </form>
    </div>
</div>


<script>
loadAjaxSelect2();
initselect2();
flatpickr("input.datepicker", {
    dateFormat: "Y-m-d",
});
</script>

<script>
$(document).ready(function() {
    // Set CSRF token for AJAX
    $.ajaxSetup({
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        }
    });

    // On department select change
    $('#search').click(function() {
        const departmentId = $("#department").val();
        const search = $("#search_emp").val();
        const employeesContainer = $('#employees-container');

        // Clear previous employees
        employeesContainer.html('');
        if (departmentId) {
            // Fetch employees for the selected department
            $.ajax({
                url: `/get-employees/${departmentId}/${search}`,
                method: 'GET',
                success: function(employees) {
                    if (employees.length > 0) {
                        employees.forEach(employee => {
                            const checkbox = `
                                    <div class="col-md-4">
                                        <div class="form-check">
                                            <input class="form-check-input  employee-checkbox" type="checkbox" name="employee_ids[]" value="${employee.id}" id="employee-${employee.id}">
                                            <label class="form-check-label" for="employee-${employee.id}">
                                                ${employee.name}
                                            </label>
                                        </div>
                                    </div>
                                `;
                            employeesContainer.append(checkbox);
                        });
                    } else {
                        employeesContainer.html(
                            '<p>No employees found for this department.</p>');
                    }
                },
                error: function() {
                    alert('Failed to fetch employees. Please try again.');
                }
            });
        }
    });

    $('#department1').change(function() {
        const departmentId = $(this).val();
        const employeesContainer = $('#employees-container');

        // Clear previous employees
        employeesContainer.html('');

        if (departmentId) {
            // Fetch employees for the selected department
            $.ajax({
                url: `/get-employees/${departmentId}`,
                method: 'GET',
                success: function(employees) {
                    if (employees.length > 0) {
                        employees.forEach(employee => {
                            const checkbox = `
                                    <div class="col-md-4">
                                        <div class="form-check">
                                            <input class="form-check-input  employee-checkbox" type="checkbox" name="employee_ids[]" value="${employee.id}" id="employee-${employee.id}">
                                            <label class="form-check-label" for="employee-${employee.id}">
                                                ${employee.name}
                                            </label>
                                        </div>
                                    </div>
                                `;
                            employeesContainer.append(checkbox);
                        });
                    } else {
                        employeesContainer.html(
                            '<p>No employees found for this department.</p>');
                    }
                },
                error: function() {
                    alert('Failed to fetch employees. Please try again.');
                }
            });
        }
    });

    // Select All/Deselect All functionality
    $('#select-all').on('change', function() {
        const isChecked = $(this).is(':checked');
        $('.employee-checkbox').prop('checked', isChecked);
    });

    // Update "Select All" checkbox when individual checkboxes are toggled
    $(document).on('change', '.employee-checkbox', function() {
        const allChecked = $('.employee-checkbox').length === $('.employee-checkbox:checked').length;
        $('#select-all').prop('checked', allChecked);
    });
});
</script>