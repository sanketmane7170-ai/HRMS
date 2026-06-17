<style>
.btn-option-leave,
.btn-option-recurring {
    border: 1px solid #ddd;
    border-radius: 20px;
    padding: 10px 20px;
    position: relative;
    cursor: pointer;
    /* background-color: #042356; */
}

.btn-option-leave.active,
.btn-option-recurring.active {
    border-color: red;
    color: #fff;
    background-color: #042356;
}

.btn-option-leave.active::after,
.btn-option-recurring.active::after {
    content: '✔';
    /* position: absolute; */
    top: 50%;
    right: 10px;
    transform: translateY(-50%);
    color: white;
    font-weight: bold;
}
</style>


<div class="modal-dialog modal-lg" id="editModal">
    <form action="{{route('backend.settings.edit_leaves_policy',[$leavepolicy->id])}}" datatable="true" method="POST"
        class="ajax-form-submit reset">
        @csrf
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="secondModalLabel">Edit Leave Policy</h5>
                <button type="button" class="btn-close editmodalclose" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="container mt-4">
                    <div class="accordion" id="newpolicyDetailsAccordion">
                        <div class="accordion-item">
                            <h2 class="accordion-header" id="createpolicyHeading">
                                <button class="accordion-button" type="button" data-bs-toggle="collapse"
                                    data-bs-target="#createpolicydetail" aria-expanded="true"
                                    aria-controls="createpolicydetail">
                                    Policy Details
                                </button>
                            </h2>
                            <div id="createpolicydetail" class="accordion-collapse collapse show"
                                aria-labelledby="createpolicyHeading" data-bs-parent="#newpolicyDetailsAccordion">
                                <div class="accordion-body">
                                    <div class="accordion-body">
                                        <div class="mb-3">
                                            <!-- Policy Name -->
                                            <div class="mb-3">
                                                <label for="name" class="form-label">Name your policy</label>
                                                <input type="text" value="{{$leavepolicy->name}}" class="form-control"
                                                    id="name" name="name" placeholder="Enter policy name" required>
                                            </div>

                                            <!-- Description -->
                                            <div class="mb-3">
                                                <label for="description" class="form-label">Description</label>
                                                <textarea class="form-control" id="description" name="description"
                                                    rows="4" placeholder="Enter policy description"
                                                    required>{{$leavepolicy->description}}</textarea>
                                            </div>

                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        @php
                        $policy= json_decode($leavepolicy->policy);

                        @endphp

                        <div class="accordion-item">
                            <h2 class="accordion-header" id="createleaveallownceheading">
                                <button class="accordion-button" type="button" data-bs-toggle="collapse"
                                    data-bs-target="#createleaveallowncadetailnew" aria-expanded="true"
                                    aria-controls="createleaveallowncadetailnew">
                                    Leave Allowance
                                </button>
                            </h2>
                            <div id="createleaveallowncadetailnew" class="accordion-collapse collapse show"
                                aria-labelledby="createleaveallownceheading"
                                data-bs-parent="#newpolicyDetailsAccordion">
                                <div class="accordion-body">

                                    <div class="mb-4">
                                        <div class="form-check form-switch">
                                            <input class="form-check-input"
                                                name="policy[leave_allowance][conditionalleaveallowance]" value="1"
                                                {{ isset($policy->leave_allowance->conditionalleaveallowance ) && $policy->leave_allowance->conditionalleaveallowance ? 'checked' : '' }}
                                                type="checkbox" id="conditionalLeaveSwitch">
                                            <label class="form-check-label" for="conditionalLeaveSwitch">
                                                Set conditional leave allowance based on <strong>employee's job
                                                    tenure</strong>
                                                <i class="bi bi-info-circle ms-2" data-bs-toggle="tooltip"
                                                    title="An employee will see 0 allowance days if they do not match any condition."></i>
                                            </label>
                                        </div>
                                        <a href="#" class="text-decoration-none">Learn more about this</a>
                                    </div>

                                    <!-- Days to Consider as Leave Days -->
                                    <div class="mb-4">
                                        <label class="form-label">Which days to consider as leave days?</label>
                                        </br>
                                        <div class="btn-group" role="group" aria-label="Leave Days Options">
                                            <input type="radio" class="btn-check"
                                                name="policy[leave_allowance][leave_days]" value="calendar_days"
                                                {{ $policy->leave_allowance->leave_days == 'calendar_days' ? 'checked' : '' }}
                                                id="calendarDays" autocomplete="off">
                                            <label class="btn  btn-option-leave active" id="lblcalendarDays"
                                                onclick="selectleavedaysOption('lblcalendarDays')"
                                                for="calendarDays">Calendar
                                                days</label>

                                            <input type="radio" class="btn-check "
                                                name="policy[leave_allowance][leave_days]" value="working_days"
                                                {{ $policy->leave_allowance->leave_days == 'working_days' ? 'checked' : '' }}
                                                id="workingDays" autocomplete="off">
                                            <label class="btn  btn-option-leave" id="lblworkingDays"
                                                onclick="selectleavedaysOption('lblworkingDays')"
                                                for="workingDays">Working
                                                days</label>
                                        </div>
                                        <div class="form-check form-switch mt-2">

                                            <input class="form-check-input" type="checkbox"
                                                name="policy[leave_allowance][exclude_public_holidays]"
                                                {{ $policy->leave_allowance->exclude_public_holidays ? 'checked' : '' }}
                                                id="excludePublicHolidays">
                                            <label class="form-check-label" for="excludePublicHolidays">
                                                Do not consider public holidays as leave days
                                            </label>
                                        </div>
                                    </div>


                                    <!-- Recurring Policy -->
                                    <div class="mb-4">
                                        <label class="form-label">Is this a recurring policy?</label>
                                        </br>
                                        <div class="btn-group" role="group" aria-label="Recurring Policy Options">
                                            <input type="radio" class="btn-check" value="annual_recurring"
                                                {{ $policy->leave_allowance->recurring_policy == 'annual_recurring' ? 'checked' : '' }}
                                                name="policy[leave_allowance][recurring_policy]" id="annuallyRecurring"
                                                autocomplete="off" checked>
                                            <label class="btn  btn-option-recurring active" id="annualrecurring"
                                                onclick="selectrecurringOption('annualrecurring')"
                                                for="annuallyRecurring">Annually
                                                recurring</label>

                                            <input type="radio" class="btn-check"
                                                {{ $policy->leave_allowance->recurring_policy == 'one_time_only' ? 'checked' : '' }}
                                                name="policy[leave_allowance][recurring_policy]" value="one_time_only"
                                                id="oneTimeOnly" autocomplete="off">
                                            <label class="btn  btn-option-recurring" id="onetimeonly"
                                                onclick="selectrecurringOption('onetimeonly')" for="oneTimeOnly">One
                                                time
                                                only</label>
                                        </div>
                                        <div class="form-check form-switch mt-2">
                                            <input class="form-check-input" type="checkbox"
                                                {{ $policy->leave_allowance->carry_over_leave  ? 'checked' : '' }}
                                                name="policy[leave_allowance][carry_over_leave]" id="carryOverLeave">
                                            <label class="form-check-label" for="carryOverLeave">
                                                Carry over paid leave allowance each cycle
                                            </label>
                                        </div>
                                        <div id="carryOverInput" class="mt-3"
                                            style="display: {{ $policy->leave_allowance->carry_over_leave ? 'block' : 'none' }}; ">
                                            <label class="form-label" for="carryOverDays">Carry over days</label>
                                            <input type="number" class="form-control"
                                                name="policy[leave_allowance][carry_over_days]" id="carryOverDays"
                                                value="{{$policy->leave_allowance->carry_over_days}}" placeholder="0"
                                                value="0">
                                            <small class="text-muted">You can change this number for individual
                                                employees.</small>
                                        </div>
                                    </div>
                                    <div class="accordion" id="leaveAllowanceAccordion">
                                        @foreach($policy->leave_allowance as $r=> $leave_allowances)
                                        @if(is_object($leave_allowances))
                                        <div class="accordion-item" id="leaveAllowanceItem{{$r}}">
                                            <h2 class="accordion-header" id="leaveAllowanceHeading{{$r}}">
                                                <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse"
                                                    data-bs-target="#leaveAllowanceCollapse{{$r}}" aria-expanded="false"
                                                    aria-controls="leaveAllowanceCollapse{{$r}}">
                                                    {{__trans($r)}}
                                                </button>
                                            </h2>
                                            <div id="leaveAllowanceCollapse{{$r}}"
                                                class="accordion-collapse collapse"
                                                aria-labelledby="leaveAllowanceHeading{{$r}}"
                                                data-bs-parent="#leaveAllowanceAccordion">
                                                <div class="accordion-body">
                                                    <!-- Accordion body content goes here -->
                                                    <!-- This will be dynamically duplicated -->
                                                    <div class="mb-3">
                                                        <label class="form-label">If employee's job tenure
                                                            is</label>
                                                        <div class="row g-2">
                                                            <div class="col-md-3">
                                                                <select id="tenureCondition"
                                                                    name="policy[leave_allowance][{{$r}}][condition]"
                                                                    class="form-select tenureCondition">
                                                                    <option value="between"
                                                                        {{ $leave_allowances->condition == "between" ? 'selected' : '' }}>
                                                                        Between
                                                                    </option>
                                                                    <option value="less_equal"
                                                                        {{ $leave_allowances->condition == "less_equal" ? 'selected' : '' }}>
                                                                        Less than and equal to
                                                                    </option>
                                                                    <option value="greater_equal"
                                                                        {{ $leave_allowances->condition == "greater_equal" ? 'selected' : '' }}>
                                                                        Greater than and equal to
                                                                    </option>
                                                                </select>

                                                            </div>
                                                            <div class="col-md-3">
                                                                <input id="fromInput"
                                                                    name="policy[leave_allowance][{{$r}}][from]"
                                                                    value="{{$leave_allowances->from}}" type="number"
                                                                    class="form-control" placeholder="0">
                                                            </div>
                                                            <div class="col-md-3 toInputContainer"
                                                                id="toInputContainer">
                                                                <input id="toInput"
                                                                    name="policy[leave_allowance][{{$r}}][to]"
                                                                    value="{{$leave_allowances->to}}" type="number"
                                                                    class="form-control" placeholder="12">
                                                            </div>
                                                            <div class="col-md-3">
                                                                <select class="form-select"
                                                                    name="policy[leave_allowance][{{$r}}][tenure]">
                                                                    <option
                                                                        {{ $leave_allowances->tenure == "less_equal" ? 'selected' : '' }}>
                                                                        Month(s)</option>
                                                                    <option
                                                                        {{ $leave_allowances->tenure == "less_equal" ? 'selected' : '' }}>
                                                                        Year(s)</option>
                                                                </select>
                                                            </div>
                                                        </div>
                                                    </div>


                                                    <div class="mb-3">
                                                        <label class="form-label">Then update the leave allowance
                                                            to</label>
                                                        <div class="input-group">
                                                            <input type="number"
                                                                name="policy[leave_allowance][{{$r}}][update_to_days]"
                                                                value="{{$leave_allowances->update_to_days}}"
                                                                class="form-control" placeholder="24">
                                                            <span class="input-group-text">Day(s)</span>
                                                        </div>
                                                    </div>
                                                    <div class="mb-3">
                                                        <h6>Should leave allowance be accrued?</h6>
                                                        <div class="form-check form-switch mb-2">
                                                            <input class="form-check-input" type="checkbox"
                                                                name="policy[leave_allowance][{{$r}}][accrue_allowance_monthly]"
                                                                {{ $leave_allowances->accrue_allowance_monthly ? 'checked' : '' }}
                                                                id="accrueAllowance1">
                                                            <label class="form-check-label"
                                                                for="accrueAllowance1">Accrue allowance on a monthly
                                                                basis</label>
                                                            <small class="form-text text-muted">ON: 2 days will be
                                                                added to available days each month</small>
                                                        </div>
                                                        <div class="form-check mb-2">
                                                            <input class="form-check-input" type="checkbox"
                                                                name="policy[leave_allowance][{{$r}}][accrue_allow_negative]"
                                                                id="allowNegative1"
                                                                {{ $leave_allowances->accrue_allow_negative ? 'checked' : '' }}>
                                                            <label class="form-check-label" for="allowNegative1">Allow
                                                                negative balance</label>
                                                            <small class="form-text text-muted">
                                                                ON: Employees can request more than accrued days but
                                                                not more than the total allowance
                                                            </small>
                                                        </div>

                                                        <div class="form-check">
                                                            <input class="form-check-input" type="checkbox"
                                                                name="policy[leave_allowance][{{$r}}][accrue_reduce_accrued]"
                                                                id="reduceAccrued1"
                                                                {{ !empty($leave_allowances->accrue_reduce_accrued) && $leave_allowances->accrue_reduce_accrued ? 'checked' : '' }}>
                                                            <label class="form-check-label" for="reduceAccrued1">
                                                                Reduce accrued days if employee takes a leave from a
                                                                different policy
                                                            </label>
                                                            <small class="form-text text-muted">OFF: No effect on
                                                                allowance accrual</small>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        @endif
                                        @endforeach

                                    </div>
                                    <button type="button" class="btn btn-primary mt-3" id="addConditionButton">Add
                                        Another
                                        Condition
                                    </button>
                                </div>
                            </div>
                        </div>

                        <div class="accordion-item">
                            <h2 class="accordion-header" id="createpayRateHeading">
                                <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse"
                                    data-bs-target="#createleavePayRate" aria-expanded="false"
                                    aria-controls="createleavePayRate">
                                    Leave Pay Rate
                                </button>
                            </h2>
                            <div id="createleavePayRate" class="accordion-collapse collapse"
                                aria-labelledby="createpayRateHeading" data-bs-parent="#newpolicyDetailsAccordion">
                                <div class="accordion-body">
                                    <div class="mb-3">
                                        <div class="d-flex align-items-center mb-3">
                                            <!-- Toggle Switch -->


                                            <div class="form-check form-switch me-2">
                                                <input class="form-check-input" type="checkbox"
                                                    name="policy[leave_pay_rate][conditional_leave_pay_rate]"
                                                    {{isset($policy->leave_pay_rate->conditional_leave_pay_rate ) && $policy->leave_pay_rate->conditional_leave_pay_rate ? 'checked' : '' }}
                                                    id="conditionalPayToggle">
                                                <label class="form-check-label" for="conditionalPayToggle">
                                                    Set conditional pay rate based on employee's leave allowance
                                                    used
                                                </label>
                                            </div>
                                            <!-- Info Icon -->
                                            <span data-bs-toggle="tooltip" data-bs-placement="top"
                                                title="Additional information about conditional pay rate">
                                                <i class="bi bi-info-circle text-muted"></i>
                                            </span>
                                        </div>

                                        <div class="btn-group" role="group" aria-label="Leave Pay Rate Options">
                                            <input type="radio" class="btn-check" value="paid_leave"
                                                name="policy[leave_pay_rate][leave_pay_rate]" id="paidLeave"
                                                autocomplete="off"
                                                {{$policy->leave_pay_rate->leave_pay_rate== 'paidLeave' ? 'checked' : '' }}>
                                            <label class="btn btn-outline-primary" for="paidLeave">Paid
                                                leave</label>

                                            <input type="radio" class="btn-check"
                                                name="policy[leave_pay_rate][leave_pay_rate]"
                                                value="partially_paid_leave" id="partiallyPaidLeave" autocomplete="off"
                                                {{$policy->leave_pay_rate->leave_pay_rate== 'partially_paid_leave' ? 'checked' : '' }}>
                                            <label class="btn btn-outline-primary" for="partiallyPaidLeave">Partially
                                                paid leave</label>

                                            <input type="radio" class="btn-check"
                                                name="policy[leave_pay_rate][leave_pay_rate]" value="unpaid_leave"
                                                id="unpaidLeave" autocomplete="off"
                                                {{$policy->leave_pay_rate->leave_pay_rate== 'unpaid_leave' ? 'checked' : '' }}>
                                            <label class="btn btn-outline-primary" for="unpaidLeave">Unpaid
                                                leave</label>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="accordion-item">
                            <h2 class="accordion-header" id="createleavesalaryHeading">
                                <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse"
                                    data-bs-target="#createleaveSalarySetting" aria-expanded="false"
                                    aria-controls="createleaveSalarySetting">
                                    Leave salary settings
                                </button>
                            </h2>
                            <div id="createleaveSalarySetting" class="accordion-collapse collapse"
                                aria-labelledby="createleavesalaryHeading" data-bs-parent="#newpolicyDetailsAccordion">
                                <div class="accordion-body">
                                    <div class="form-check form-switch mt-3">
                                        <input class="form-check-input" type="checkbox"
                                            name="policy[leave_salary_setting][enable_leave_salary]"
                                            {{isset($policy->leave_salary_setting->enable_leave_salary) && $policy->leave_salary_setting->enable_leave_salary ? 'checked' : '' }}
                                            id="enableLeaveSalary">
                                        <label class="form-check-label" for="enableLeaveSalary">Enable leave
                                            salary</label>
                                        <p class="text-muted mt-1">Employees included in this leave policy will be
                                            eligible for leave salary</p>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="accordion-item">
                            <h2 class="accordion-header" id="createrestrictionsHeading">
                                <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse"
                                    data-bs-target="#createpolicyRestrictions" aria-expanded="false"
                                    aria-controls="createpolicyRestrictions">
                                    Policy Restrictions
                                </button>
                            </h2>
                            <div id="createpolicyRestrictions" class="accordion-collapse collapse"
                                aria-labelledby="createrestrictionsHeading" data-bs-parent="#newpolicyDetailsAccordion">
                                <div class="accordion-body">
                                    <div class="form-check form-switch mt-3">
                                        <input class="form-check-input" type="checkbox"
                                            name="policy[policy_restriction][restrict_editing_leave_days]"
                                            {{isset($policy->policy_restriction->restrict_editing_leave_days) && $policy->policy_restriction->restrict_editing_leave_days ? 'checked' : '' }}
                                            id="restrictEditingLeaveDays">
                                        <label class="form-check-label" for="restrictEditingLeaveDays">
                                            Restrict editing of leave days
                                        </label>
                                        <p class="text-muted mt-1">
                                            OFF: Leave days calculated at the time of leave request can be changed
                                            by
                                            the employee and the approvers (only if request is in pending state)
                                        </p>
                                    </div>
                                    <div class="form-check form-switch mt-3">
                                        <input class="form-check-input" type="checkbox"
                                            name="policy[policy_restriction][restrict_during_probation]"
                                            id="restrictDuringProbation"
                                            {{ isset($policy->policy_restriction->restrict_during_probation) &&  $policy->policy_restriction->restrict_during_probation ? 'checked' : '' }}>
                                        <label class="form-check-label" for="restrictDuringProbation">
                                            Restrict during probation
                                        </label>
                                        <p class="text-muted mt-1">
                                            ON: Employees are not able to create leave request during their
                                            probation
                                            period
                                        </p>
                                    </div>
                                </div>
                            </div>
                        </div>

                    </div>
                </div>
            </div>

            <div class="modal-footer">
                <button type="submit" class="btn btn-info waves-effect waves-light">{{__trans('update')}} </button>
                <button type="button" id="editmodalclose" class="btn btn-secondary editmodalclose">Close</button>
            </div>
        </div>
    </form>
</div>

<script>
$('.editmodalclose').on('click', function() {
    $('#editModal').modal('hide');
});
document.getElementById('carryOverLeave').addEventListener('change', function() {
    const carryOverInput = document.getElementById('carryOverInput');
    carryOverInput.style.display = this.checked ? 'block' : 'none';
});
</script>

<script>
function selectleavedaysOption(optionId) {
    const options = document.querySelectorAll('.btn-option-leave');
    options.forEach(option => {
        option.classList.remove('active');
    });
    document.getElementById(optionId).classList.add('active');
}

function selectrecurringOption(optionId) {
    const options = document.querySelectorAll('.btn-option-recurring');
    options.forEach(option => {
        option.classList.remove('active');
    });
    document.getElementById(optionId).classList.add('active');
}
</script>

<script>
$(document).ready(function() {
    // let allowanceCounter = 1; // Start with the initial allowance count
    let allowanceCounter = $('#leaveAllowanceAccordion .accordion-item').length;
    $("#addConditionButton").on("click", function() {
        allowanceCounter++;
        // Create a new accordion-item dynamically
        const newAllowanceItem = `
            <div class="accordion-item" id="leaveAllowanceItem${allowanceCounter}">
                <h2 class="accordion-header" id="leaveAllowanceHeading${allowanceCounter}">
                    <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse"
                        data-bs-target="#leaveAllowanceCollapse${allowanceCounter}" aria-expanded="false"
                        aria-controls="leaveAllowanceCollapse${allowanceCounter}">
                        Leave Allowance ${allowanceCounter}
                    </button>
                </h2>
                <div id="leaveAllowanceCollapse${allowanceCounter}" class="accordion-collapse collapse"
                    aria-labelledby="leaveAllowanceHeading${allowanceCounter}" data-bs-parent="#leaveAllowanceAccordion">
                    <div class="accordion-body">
                       <div class="mb-3">
                                                        <label class="form-label">If employee's job tenure is</label>
                                                        <div class="row g-2">
                                                            <div class="col-md-3">
                                                            <select id="tenureCondition"
                                                                    name="policy[leave_allowance][leave_allownace_${allowanceCounter}][condition]"
                                                                        class="form-select tenureCondition">
                                                                        <option value="between" selected>Between</option>
                                                                        <option value="less_equal">Less than and equal to </option>
                                                                        <option value="greater_equal">Greater than and equal to</option>
                                                                    </select>
                                                            </div>
                                                            <div class="col-md-3">
                                                                <input id="fromInput" name="policy[leave_allowance][leave_allownace_${allowanceCounter}][from]" type="number"
                                                                    class="form-control" placeholder="0">
                                                            </div>
                                                            <div class="col-md-3 toInputContainer" id="toInputContainer">
                                                                <input id="toInput" name="policy[leave_allowance][leave_allownace_${allowanceCounter}][to]" type="number"
                                                                    class="form-control" placeholder="12">
                                                            </div>
                                                            <div class="col-md-3">
                                                                <select class="form-select" name="policy[leave_allowance][leave_allownace_${allowanceCounter}][tenure]">
                                                                    <option selected>Month(s)</option>
                                                                    <option>Year(s)</option>
                                                                </select>
                                                            </div>
                                                        </div>
                                                    </div>
                        <div class="mb-3">
                            <label class="form-label">Then update the leave allowance to</label>
                            <div class="input-group">
                                <input type="number" name="policy[leave_allowance][leave_allownace_${allowanceCounter}][update_to_days]" class="form-control" placeholder="24">
                                <span class="input-group-text">Day(s)</span>
                            </div>
                        </div>
                        <div class="mb-3">
                            <h6>Should leave allowance be accrued?</h6>
                            <div class="form-check form-switch mb-2">
                                <input class="form-check-input" name="policy[leave_allowance][leave_allownace_${allowanceCounter}][accrue_allowance_monthly]" type="checkbox" id="accrueAllowance${allowanceCounter}" checked>
                                <label class="form-check-label" for="accrueAllowance${allowanceCounter}">
                                    Accrue allowance on a monthly basis
                                </label>
                                <small class="form-text text-muted">ON: 2 days will be added to available days each month</small>
                            </div>
                            <div class="form-check mb-2">
                                <input class="form-check-input" name="policy[leave_allowance][leave_allownace_${allowanceCounter}][accrue_allow_negative]" type="checkbox" id="allowNegative${allowanceCounter}" checked>
                                <label class="form-check-label" for="allowNegative${allowanceCounter}">
                                    Allow negative balance
                                </label>
                                <small class="form-text text-muted">
                                    ON: Employees can request more than accrued days but not more than the total allowance
                                </small>
                            </div>
                            <div class="form-check">
                                <input class="form-check-input" name="policy[leave_allowance][leave_allownace_${allowanceCounter}][accrue_reduce_accrued]" type="checkbox" id="reduceAccrued${allowanceCounter}">
                                <label class="form-check-label" for="reduceAccrued${allowanceCounter}">
                                    Reduce accrued days if employee takes a leave from a different policy
                                </label>
                                <small class="form-text text-muted">OFF: No effect on allowance accrual</small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            `;

        // Append the new item to the accordion
        $("#leaveAllowanceAccordion").append(newAllowanceItem);
    });
});
</script>
<script>
$(document).ready(function() {

    $(document).on('change', '.tenureCondition', function() {
        const condition = $(this).val(); // Get the selected value
        const parentRow = $(this).closest('.row'); // Get the closest row containing this dropdown

        // Show or hide inputs based on the selected condition
        if (condition === 'between') {
            parentRow.find('.fromInput').show();
            parentRow.find('.toInputContainer').show();
        } else {
            parentRow.find('.fromInput').show();
            parentRow.find('.toInputContainer').hide();
        }
    });

});
</script>