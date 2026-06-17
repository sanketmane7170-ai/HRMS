<div class="card">
    <div class="card-header">
        <div class="row">
            <div class="col">
                <h4>{{__trans('general_settings')}}</h4>
            </div>
            <div class="col-auto">

            </div>
        </div>
    </div>
    <div class="card-body light">
        <form action="{{route('backend.settings.general.post')}}" method="POST" enctype="multipart/form-data"
            class="ajax-form-submit">
            @csrf
            <div class="row">
                <div class="col-md-6">
                    <div class="form-group">
                        <label> {{__trans('company_name')}}</label>
                        <input type="text" name="site_title"
                            class="form-control @error('site_title') is-invalid @enderror"
                            value="{{old('site_title',getSetting('site_title'))}}" required>

                    </div>
                </div>
                <div class="col-md-6">
                    <div class="form-group">
                        <label> {{__trans('company_email')}}</label>
                        <input type="email" name="site_email"
                            class="form-control @error('site_email') is-invalid @enderror"
                            value="{{old('site_email',getSetting('site_email'))}}" required>

                    </div>
                </div>
                <div class="col-md-6">
                    <div class="form-group">
                        <label> {{__trans('company_address')}}</label>
                        <input type="text" name="site_address"
                            class="form-control @error('site_address') is-invalid @enderror"
                            value="{{old('site_address',getSetting('site_address'))}}">

                    </div>
                </div>


                <div class="col-md-6">
                    <div class="form-group">
                        <label> {{__trans('company_phone')}}</label>
                        <input type="text" name="site_phone"
                            class="form-control @error('site_phone') is-invalid @enderror"
                            value="{{old('site_phone',getSetting('site_phone'))}}">

                    </div>
                </div>
                <div class="col-md-6">
                    <div class="form-group">
                        <label> {{__trans('company_support_email')}}</label>
                        <input type="email" name="site_support_email"
                            class="form-control @error('site_support_email') is-invalid @enderror"
                            value="{{old('site_support_email',getSetting('site_support_email'))}}">
                    </div>
                </div>

                <div class="col-md-6">
                    <label>{{__trans('debug_mode')}}</label>
                    <select name="site_debug_mode" class="form-control select" id="site_debug_mode">
                        <option value="false" @if(getSetting('site_debug_mode')=='false' ) selected @endif>Disable
                        </option>
                        <option value="true" @if(getSetting('site_debug_mode')=='true' ) selected @endif>Enable</option>
                    </select>
                </div>


                <div class="col-md-6">
                    <label>Select Shift Hierarchy Roles</label>
                    <select name="shift_hierarchy_roles[]" class="ajax-select2" id="shift_hierarchy_roles"
                        data-target="{{route('ajax.select2.fetch.roles')}}" multiple>
                        @foreach ($roleList as $role)
                        <option value="{{ $role['id'] }}" selected>
                            {{ $role['text'] }}
                        </option>
                        @endforeach
                    </select>
                </div>




                <div class="col-md-6" id="auto_attendance_user_warnings" @if(getSetting('payroll_calculation')=='hourly'
                    ) style="display: none;" @endif>
                    <label>{{__trans('auto_attendance_user_warnings')}}</label>
                    <select name="auto_attendance_user_warnings" class="form-control select"
                        id="auto_attendance_user_warnings">
                        <option value="false" @if(getSetting('auto_attendance_user_warnings')=='false' ) selected
                            @endif>
                            Disable</option>
                        <option value="true" @if(getSetting('auto_attendance_user_warnings')=='true' ) selected @endif>
                            Enable
                        </option>
                    </select>
                </div>

                <div class="col-md-6">
                    <label>Currency</label>

                    <select name="currency" class="ajax-select2" id="currency"
                        data-target="{{route('ajax.select2.fetch.currency')}}">
                        @foreach ($currencyList as $currency)
                        <option value="{{ $currency['id']}}" @if(getSetting('currency')==$currency['id']) selected
                            @endif>
                            {{ $currency['text'] }}
                        </option>
                        @endforeach
                    </select>
                </div>


                <div class="col-md-6">
                    <div class="form-group">
                        <label> {{__trans('document_expiry_days')}}</label>
                        <input type="number" name="document_expiry_days"
                            class="form-control @error('document_expiry_days') is-invalid @enderror"
                            value="{{old('document_expiry_days',getSetting('document_expiry_days'))}}">
                    </div>
                </div>




                <div class="col-md-6">
                    <label> {{__trans('select probation period')}}</label>
                    <select name="probation_period_month" class="form-control select-search" id="site_timezone">
                        <option value="1_month" @if(getSetting('probation_period_month')=='1_month' ) selected @endif> 1
                            Month </option>
                        <option value="3_month" @if(getSetting('probation_period_month')=='3_month' ) selected @endif> 3
                            Month </option>
                        <option value="6_month" @if(getSetting('probation_period_month')=='6_month' ) selected @endif> 6
                            Month </option>
                    </select>
                </div>

                <div class="col-md-6">
                    <div class="form-group">
                        <label> {{__trans('free_document_request')}}</label>
                        <input type="number" name="free_document_request"
                            class="form-control @error('free_document_request') is-invalid @enderror"
                            value="{{old('free_document_request',getSetting('free_document_request'))}}">
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="form-group">
                        <label> {{__trans('document_request_charge')}}</label>
                        <input type="number" name="document_request_charge"
                            class="form-control @error('document_request_charge') is-invalid @enderror"
                            value="{{old('document_request_charge',getSetting('document_request_charge'))}}">
                    </div>
                </div>
            </div>
            <br>

            <div class="row">
                <div class="card-header">
                    {{__trans('leave_settings')}}
                </div>


                <div class="col-md-6">
                    <label>{{__trans('cancel_off_leave_module')}}</label>
                    <select name="cancel_off_leave_module" class="form-control select" id="cancel_off_leave_module">
                        <option value="false" @if(getSetting('cancel_off_leave_module')=='false' ) selected @endif>
                            Disable</option>
                        <option value="true" @if(getSetting('cancel_off_leave_module')=='true' ) selected @endif>Enable
                        </option>
                    </select>
                </div>

                <div class="mb-3">
                    <label class="form-label fw-bold">Leave salary policy</label>
                    <div class="row g-3">

                        <div class="col-md-6">
                            <label for="request_after_months_date"
                                class="form-label">{{__trans('leave_salary')}}</label>
                            <select name="leave_salary" class="form-control select-search" id="leave_salary">
                                <option @if(getSetting('leave_salary')=='0' ) selected @endif value="0">Select</option>
                                <option @if(getSetting('leave_salary')=='yes' ) selected @endif value="yes">Yes</option>
                                <option @if(getSetting('leave_salary')=='no' ) selected @endif value="no">No</option>

                            </select>
                        </div>
                        <div id="salary_paid_on_container" style="display: none;" @if(getSetting('leave_salary')=='yes'
                            ) style="display:block" @endif class="col-md-6">
                            <label for="request_after_months_date"
                                class="form-label">{{__trans('Salary_paid_on')}}</label>
                            <select name="salary_paid_on" class="form-control select-search" id="salary_paid_on">
                                <option value="gross">Gross</option>
                                <option value="basic">Basic</option>
                                <option value="basic_housing">Basic + Housing</option>
                            </select>
                        </div>
                    </div>
                </div>

            </div>
            <br>
            <div class="row">
                <div class="card-header">
                    {{__trans('attedance_settings')}}
                </div>
                <div class="col-md-6">
                    <div class="form-group">
                        <label> {{__trans('company_maximum_working_hours')}}</label>
                        <input type="number" name="minumum_working_hour"
                            class="form-control @error('minumum_working_hour') is-invalid @enderror"
                            value="{{old('minumum_working_hour',getSetting('minumum_working_hour'))}}">
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="form-group">
                        <label> {{__trans('new_attendance_hours')}}</label>
                        <input type="number" name="new_attendance_hours"
                            class="form-control @error('new_attendance_hours') is-invalid @enderror"
                            value="{{old('new_attendance_hours',getSetting('new_attendance_hours'))}}">
                    </div>
                </div>

                <div class="col-md-6">
                    <div class="form-group">
                        <label> {{__trans('Maximum Permitted Late Arrival Time')}}</label>
                        <input type="number" name="maximum_late_come_minute"
                            class="form-control @error('maximum_late_come_minute') is-invalid @enderror"
                            value="{{old('maximum_late_come_minute',getSetting('maximum_late_come_minute'))}}">
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="form-group">
                        <label> {{__trans('Maximum Allowed Early Departure Time')}}</label>
                        <input type="number" name="maximum_early_out_minute"
                            class="form-control @error('maximum_early_out_minute') is-invalid @enderror"
                            value="{{old('maximum_early_out_minute',getSetting('maximum_early_out_minute'))}}">
                    </div>
                </div>
                <div class="col-md-6">
                    <label>{{__trans('Attendance Check-In Notification Time')}}</label>
                    <input type="time" name="attendance_checkin_time"
                        class="form-control @error('attendance_checkin_time') is-invalid @enderror"
                        value="{{old('attendance_checkin_time',getSetting('attendance_checkin_time'))}}">
                </div>
            </div>
            <br>

            <div class="row">
                <div class="card-header">
                    {{__trans('payroll_settings')}}
                </div>

                <div class="col-md-6">
                    <div class="form-group">
                        <label> {{__trans('roundoff')}}</label>
                        <input type="number" name="roundoff"
                            class="form-control @error('roundoff') is-invalid @enderror" min="0" step="1" max="2"
                            value="{{old('roundoff',getSetting('roundoff'))}}">

                    </div>
                </div>

                <div class="col-md-6">
                    <label>{{__trans('payslip_email')}}</label>
                    <select name="payslip_email" class="form-control select" id="payslip_email">
                        <option value="" @if(getSetting('payslip_email')=='' ) selected @endif>
                            {{__trans('None')}}
                        <option value="personal_email" @if(getSetting('payslip_email')=='personal_email' ) selected
                            @endif>
                            {{__trans('personal_email')}}
                        </option>
                        <option value="work_email" @if(getSetting('payslip_email')=='work_email' ) selected @endif>
                            {{__trans('work_email')}}
                        </option>
                    </select>
                </div>

                <div class="col-md-6">
                    <div class="form-group">
                        <label> {{__trans('employer_unique_id')}}</label>
                        <input type="text" name="employer_unique_id"
                            class="form-control @error('employer_unique_id') is-invalid @enderror"
                            value="{{old('employer_unique_id',getSetting('employer_unique_id'))}}">

                    </div>
                </div>


                <div class="col-md-6">
                    <div class="form-group">
                        <label> {{__trans('bank_code(Routing_number)')}}</label>
                        <input type="text" name="bank_code"
                            class="form-control @error('bank_code') is-invalid @enderror"
                            value="{{old('bank_code',getSetting('bank_code'))}}">
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="form-group">
                        <label> {{__trans('employer_reference_number')}}</label>
                        <input type="text" name="employer_reference_number"
                            class="form-control @error('employer_reference_number') is-invalid @enderror"
                            value="{{old('employer_reference_number',getSetting('employer_reference_number'))}}">
                    </div>
                </div>
                <div class="col-md-6">
                    <label>{{__trans('payroll_calculation')}}</label>
                    <select name="payroll_calculation" class="form-control select" id="payroll_calculation">
                        <option value="salary" @if(getSetting('payroll_calculation')=='salary' ) selected @endif>
                            Salary Basis</option>
                        <option value="hourly" @if(getSetting('payroll_calculation')=='hourly' ) selected @endif>
                            Hourly Basis
                        </option>
                    </select>
                </div>
                <div class="col-md-6">
                    <label>{{__trans('multi_branch_wise_payroll')}}</label>
                    <select name="multi_branch_wise_payroll" class="form-control select" id="multi_branch_wise_payroll">
                        <option value="false" @if(getSetting('multi_branch_wise_payroll')=='false' ) selected @endif>
                            Disable</option>
                        <option value="true" @if(getSetting('multi_branch_wise_payroll')=='true' ) selected @endif>
                            Enable
                        </option>
                    </select>
                </div>

                <div class="col-md-6" id="attendance_base_payroll" @if(getSetting('payroll_calculation')=='hourly' )
                    style="display: none;" @endif>
                    <label>{{__trans('attendance_base_payroll')}}</label>
                    <select name="attendance_base_payroll" class="form-control select" id="attendance_base_payroll">
                        <option value="false" @if(getSetting('attendance_base_payroll')=='false' ) selected @endif>
                            Disable</option>
                        <option value="true" @if(getSetting('attendance_base_payroll')=='true' ) selected @endif>Enable
                        </option>
                    </select>
                </div>
                <div class="col-md-12">
                    <div class="row">
                        <div class="form-group">
                            <label> {{__trans('Payroll_setting')}}</label><br>
                            <input type="hidden" name="show_basic_salary" value="0">
                            Basic Salary <input class="form-check-input" type="checkbox" id="show_basic_salary"
                                name="show_basic_salary" @if(getSetting('show_basic_salary')==1 ) checked @endif
                                value="1">
                            <input type="hidden" name="show_gross_salary" value="0">
                            Gross Salary <input class="form-check-input" type="checkbox" id="show_gross_salary"
                                name="show_gross_salary" @if(getSetting('show_gross_salary')==1 ) checked @endif
                                value="1">
                            <input type="hidden" name="show_net_salary" value="0">
                            Net Salary <input class="form-check-input" type="checkbox" id="show_net_salary"
                                name="show_net_salary" @if(getSetting('show_net_salary')==1 ) checked @endif value="1">
                            <input type="hidden" name="show_total_net_salary" value="0">
                            Total Net Salary <input class="form-check-input" type="checkbox" id="show_total_net_salary"
                                name="show_total_net_salary" @if(getSetting('show_total_net_salary')==1 ) checked @endif
                                value="1">
                            <input type="hidden" name="show_total_allowance" value="0">
                            Total Allowance <input class="form-check-input" type="checkbox" id="show_total_allowance"
                                name="show_total_allowance" @if(getSetting('show_total_allowance')==1 ) checked @endif
                                value="1"><br>
                            <input type="hidden" name="show_total_deduction" value="0">
                            Total Deduction <input class="form-check-input" type="checkbox" id="show_total_deduction"
                                name="show_total_deduction" @if(getSetting('show_total_deduction')==1 ) checked @endif
                                value="1">
                            <input type="hidden" name="show_total_expense" value="0">
                            Total Expense <input class="form-check-input" type="checkbox" id="show_total_expense"
                                name="show_total_expense" @if(getSetting('show_total_expense')==1 ) checked @endif
                                value="1">
                            <input type="hidden" name="show_total_overtime_amount" value="0">
                            Total Overtime Amount <input class="form-check-input" type="checkbox"
                                id="show_total_overtime_amount" name="show_total_overtime_amount"
                                @if(getSetting('show_total_overtime_amount')==1 ) checked @endif value="1">
                            <input type="hidden" name="show_total_fixed_allowance" value="0">
                            Total Fixed Allowance <input class="form-check-input" type="checkbox"
                                id="show_total_fixed_allowance" name="show_total_fixed_allowance"
                                @if(getSetting('show_total_fixed_allowance')==1 ) checked @endif value="1">
                        </div>
                    </div>
                </div>

            </div>
            <br>

            <div class="row">
                <div class="card-header">
                    {{__trans('kisok_app_settings')}}
                </div>
                <div class="col-md-6">
                    <div class="form-group">
                        <label> {{__trans('check_location_radius_on_kiosk_app')}}</label>
                        <select name="is_check_location_radius" class="form-control select" id="site_debug_mode">
                            <option value="false" @if(getSetting('is_check_location_radius')=='false' ) selected @endif>
                                Disable
                            </option>
                            <option value="true" @if(getSetting('is_check_location_radius')=='true' ) selected @endif>
                                Enable</option>
                        </select>

                    </div>
                </div>
                <div class="col-md-6">
                    <div class="form-group">
                        <label> {{__trans('deep_face_check_on_kiosk_app')}}</label>
                        <select name="shouldPerformLivenessCheck" class="form-control select" id="site_debug_mode">
                            <option value="false" @if(getSetting('shouldPerformLivenessCheck')=='false' ) selected
                                @endif>
                                Disable
                            </option>
                            <option value="true" @if(getSetting('shouldPerformLivenessCheck')=='true' ) selected @endif>
                                Enable</option>
                        </select>
                    </div>
                </div>
                <div class="col-md-6">
                    <label>{{__trans('branch_wise_login')}}</label>
                    <select name="branch_wise_login" class="form-control select" id="branch_wise_login">
                        <option value="false" @if(getSetting('branch_wise_login')=='false' ) selected @endif>
                            Disable</option>
                        <option value="true" @if(getSetting('branch_wise_login')=='true' ) selected @endif>Enable
                        </option>
                    </select>
                </div>

                <div class="col-md-6">
                    <label>{{__trans('user_wise_login')}}</label>
                    <select name="user_wise_login" class="form-control select" id="user_wise_login">
                        <option value="false" @if(getSetting('user_wise_login')=='false' ) selected @endif>
                            Disable</option>
                        <option value="true" @if(getSetting('user_wise_login')=='true' ) selected @endif>Enable
                        </option>
                    </select>
                </div>

                <div class="col-md-6">
                    <label>{{__trans('break_in_out')}}</label>
                    <select name="break_in_out" class="form-control select" id="break_in_out">
                        <option value="false" @if(getSetting('break_in_out')=='false' ) selected @endif>
                            Disable</option>
                        <option value="true" @if(getSetting('break_in_out')=='true' ) selected @endif>Enable
                        </option>
                    </select>
                </div>

                <div class="col-md-6">
                    <label>{{__trans('auto_face_scan')}}</label>
                    <select name="auto_face_scan" class="form-control select" id="auto_face_scan">
                        <option value="false" @if(getSetting('auto_face_scan')=='false' ) selected @endif>
                            Disable</option>
                        <option value="true" @if(getSetting('auto_face_scan')=='true' ) selected @endif>Enable
                        </option>
                    </select>
                </div>

                <div class="col-md-6">
                    <label>{{__trans('auto_face_scan_with_list')}}</label>
                    <select name="auto_face_scan_with_list" class="form-control select" id="auto_face_scan_with_list">
                        <option value="false" @if(getSetting('auto_face_scan_with_list')=='false' ) selected @endif>
                            Disable</option>
                        <option value="true" @if(getSetting('auto_face_scan_with_list')=='true' ) selected @endif>Enable
                        </option>
                    </select>
                </div>
            </div>
            <br>
            <div class="row">
                <div class="card-header">
                    {{__trans('digital_app_settings')}}
                </div>
                <div class="col-md-6">
                    <div class="form-group">
                        <label> {{__trans('visiting_area_radius')}}</label>
                        <input type="number" name="radius" class="form-control @error('radius') is-invalid @enderror"
                            value="{{old('radius',getSetting('radius'))}}">
                    </div>
                </div>
                <div class="col-md-6">
                    <label>{{__trans('Attendance_module')}}</label>
                    <select name="attendance_module" class="form-control select" id="attendance_module">
                        <option value="false" @if(getSetting('attendance_module')=='false' ) selected @endif>
                            Disable</option>
                        <option value="true" @if(getSetting('attendance_module')=='true' ) selected @endif>Enable
                        </option>
                    </select>
                </div>

                <div class="col-md-6">
                    <label>{{__trans('Timesheet_module')}}</label>
                    <select name="timesheet_module" class="form-control select" id="timesheet_module">
                        <option value="false" @if(getSetting('timesheet_module')=='false' ) selected @endif>
                            Disable</option>
                        <option value="true" @if(getSetting('timesheet_module')=='true' ) selected @endif>Enable
                        </option>
                    </select>
                </div>

                <div class="col-md-6">
                    <label>{{__trans('auto_clockout')}}</label>
                    <select name="auto_clockout" class="form-control select" id="auto_clockout">
                        <option value="false" @if(getSetting('auto_clockout')=='false' ) selected @endif>
                            Disable</option>
                        <option value="true" @if(getSetting('auto_clockout')=='true' ) selected @endif>Enable
                        </option>
                    </select>
                </div>
            </div>
            <br>
            <div class="row">
                <div class="card-header">
                    {{__trans('image')}}
                </div>
                <div class="col-md-6 mt-2">
                    <div class="form-group">
                        <label> {{__trans('logo')}}</label>
                        <input type="file" name="logo" id="logo" accept="image/*"
                            class="form-control @error('logo') is-invalid @enderror"
                            onchange="previewImage('logo','preview_logo')">
                        <div class="preview-image" id="preview_logo">
                            <div class="col-md-3 mt-2">
                                <img width="100" src="{{getLogo()}}">
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-6 mt-2">
                    <div class="form-group">
                        <label>{{__trans('small_logo')}}</label>
                        <input type="file" name="small_logo" id="small_logo" accept="image/*"
                            class="form-control @error('small_logo') is-invalid @enderror"
                            onchange="previewImage('small_logo','preview_small_logo')">
                        <div class="preview-image" id="preview_small_logo">
                            <div class="col-md-3 mt-2">
                                <img width="100" src="{{getSmallLogo()}}">
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="form-group">
                        <label>{{__trans('favicon')}}</label>
                        <input type="file" name="favicon" id="favicon" accept="image/*"
                            class="form-control @error('favicon') is-invalid @enderror"
                            onchange="previewImage('favicon','preview_favicon')">
                        <div class="preview-image" id="preview_favicon">
                            <div class="col-md-3 mt-2">
                                <img width="100" src="{{getFavicon()}}">
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class=" text-end mt-4">
                <button type="submit" class="btn btn-primary">{{__trans('save_general_settings')}} </button>
            </div>
        </form>
    </div>
</div>
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script>
$(document).ready(function() {
    // When the policy dropdown changes
    $('#leave_salary').change(function() {
        if ($(this).val() === 'yes') {
            $('#salary_paid_on_container').show(); // Show the Salary_Paid_On dropdown
        } else {
            $('#salary_paid_on_container').hide(); // Hide the Salary_Paid_On dropdown
            $('#salary_paid_on').val("");

        }
    });

    // Initialize: Hide or Show based on the current selection
    if ($('#leave_salary').val() === 'yes') {
        $('#salary_paid_on_container').show();
    } else {
        $('#salary_paid_on_container').hide();

    }
});

$(document).ready(function() {

    // Run when dropdown value changes
    $('#payroll_calculation').change(function() {
        if ($('#payroll_calculation').val() === 'hourly') {
            $('#attendance_base_payroll').hide();
        } else {
            $('#attendance_base_payroll').show();
        }
    });
});
</script>
