<div class="modal-dialog modal-lg">
    <div class="modal-content">
        <div class="modal-header">
            <h4 class="modal-title">{{__trans('Assign Policies to Employees')}}</h4>
            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <form action="{{route('backend.settings.air-ticket-setting.toMultipleUser',$id)}}" datatable="true" method="POST"
            class="ajax-form-submit reset">
            @csrf
            <div class="modal-body p-4">
                <div class="row">
                    <div class="col-md-12">
                        <div class="form-group">
                            <label>{{__trans('department')}}</label>
                            <select name="department_id" id="department" class="form-control select">
                                {{-- <option value="{{$user->department->id}}">{{$user->department?->name ?? 'NA'}}</option> --}}
                                <option value="">{{__trans('select_a_option')}}</option>

                                @foreach ($departments as $department)
                                <option value="{{$department->id}}">{{$department->name}}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                </div>
                <div id="employee-list" class="mt-4">
                    <h5>Employees</h5>

                    <!-- Select All Checkbox -->
                    <input required type="hidden" name="employee_ids" class="form-control" placeholder="{{__trans('employee_ids')}}">
                    <div class="form-check mb-3">
                        <input class="form-check-input" type="checkbox" id="select-all">
                        <label class="form-check-label" for="select-all">Select All</label>
                    </div>


                    <!-- Employee List in Rows -->
                    <div id="employees-container" class="row g-3">
                        <!-- Individual checkboxes will be dynamically loaded here -->
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary waves-effect"
                        data-bs-dismiss="modal">{{__trans('close')}}</button>
                    <button type="submit" class="btn btn-info waves-effect waves-light">{{__trans('save')}} </button>
                </div>
            </div>
        </form>
    </div>
</div>

<script>
    $(document).ready(function() {
        // Set CSRF token for AJAX
        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            }
        });

        // On department select change
        $('#department').change(function() {
            const departmentId = $(this).val();
            const employeesContainer = $('#employees-container');
            const id = {{ $id }};
            // Clear previous employees
            employeesContainer.html('');

            if (departmentId) {
                // Fetch employees for the selected department
                $.ajax({
                    url: `air-ticket-setting/get-employees/${departmentId}`,
                    method: 'GET',
                    success: function(employees) {
                        if (employees.length > 0) {
                            employees.forEach(employee => {
                                console.log(employee['work_detail']['air_ticket_setting_id']);
                                const isChecked = employee['work_detail']['air_ticket_setting_id'] === id ? 'checked' : '';

                                const checkbox = `
                                    <div class="col-md-4">
                                        <div class="form-check">
                                            <input class="form-check-input  employee-checkbox" ${isChecked} type="checkbox" name="employee_ids[]" value="${employee.id}" id="employee-${employee.id}">
                                            <label class="form-check-label" for="employee-${employee.id}">
                                                ${employee.name}
                                            </label>
                                        </div>
                                    </div>
                                `;
                                employeesContainer.append(checkbox);
                            });
                        } else {
                            employeesContainer.html('<p>No employees found for this department.</p>');
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