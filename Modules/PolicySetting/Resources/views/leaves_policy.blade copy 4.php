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
<div class="modal-dialog modal-lg">
    <div class="modal-content">
        <div class="modal-header">
            <h4 class="modal-title">{{__trans('leave_policy')}}</h4>
            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>

        <div class="modal-body p-4">
            <div class="row">
                <!-- Sidebar -->
                <div class="col-md-3">
                    <div class="list-group" id="policyList">
                        <button type="button" class="list-group-item list-group-item-action active" data-bs-toggle="tab"
                            data-bs-target="#vacation">Vacation</button>

                        <button type="button" class="list-group-item list-group-item-action" data-bs-toggle="tab"
                            data-bs-target="#sick">Sick</button>
                        <button type="button" class="list-group-item list-group-item-action"
                            data-policy="maternity">Maternity</button>
                    </div>
                </div>
                <div class="col-md-9">
                    <!-- Tab Content -->
                    <div class="tab-content">
                        <!-- Vacation Tab -->
                        <div class="tab-pane fade show active" id="vacation" role="tabpanel"
                            aria-labelledby="vacation-tab">
                            <div class="alert alert-info" role="alert" id="policyAlert">
                                This template is compliant with labor law guidelines in United Arab
                                Emirates.
                            </div>

                            <!-- Policy Details -->
                            <div id="policyDetails">
                                <h5>Policy Details</h5>
                                <div class="mb-2">
                                    <strong>Policy Name:</strong> <span id="policyName">Vacation</span>
                                </div>
                                <div>
                                    <strong>Description:</strong>
                                    <p id="policyDescription">
                                        An employee is entitled to a fully paid annual leave of 30
                                        days, if they have completed one year of service and 2 days
                                        per month, if they have completed six months of service, but
                                        not one year.
                                    </p>
                                </div>
                            </div>

                            <!-- Accordion for Additional Details -->
                            <div class="accordion" id="policyDetailsAccordion">
                                <div class="accordion-item">
                                    <h2 class="accordion-header" id="allowanceHeading">
                                        <button class="accordion-button" type="button" data-bs-toggle="collapse"
                                            data-bs-target="#leaveAllowance" aria-expanded="true"
                                            aria-controls="leaveAllowance">
                                            Leave Allowance
                                        </button>
                                    </h2>
                                    <div id="leaveAllowance" class="accordion-collapse collapse show"
                                        aria-labelledby="allowanceHeading" data-bs-parent="#policyDetailsAccordion">
                                        <div class="accordion-body">
                                            <div class="accordion-body">
                                                <div class="mb-3">
                                                    <strong>Leave Days:</strong> Calendar days
                                                </div>
                                                <div class="mb-3">
                                                    <strong>Policy Recurrence:</strong> Annually recurring
                                                </div>
                                                <div>
                                                    <strong>Conditional Leave Allowance Based on Employee's
                                                        Job Tenure:</strong>
                                                </div>
                                                <div class="table-responsive mt-3">
                                                    <table class="table table-bordered text-wrap light">

                                                        <tbody>
                                                            <tr>
                                                                <td>Leave Allowance 1</td>
                                                                <td>If job tenure is between 0 months – 12
                                                                    months, then update leave allowance to:
                                                                    <br />
                                                                    <strong>24 days</strong><br />
                                                                    <br />
                                                                    Accrue allowance on a monthly basis<br />
                                                                    On: Allow negative balances
                                                                </td>
                                                            </tr>
                                                            <tr>
                                                                <td>Leave Allowance 2</td>
                                                                <td>If job tenure is greater than and equal to
                                                                    12 months, then update leave allowance to:
                                                                    <br />
                                                                    <strong>30 days</strong>
                                                                </td>
                                                            </tr>
                                                        </tbody>
                                                    </table>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="accordion-item">
                                    <h2 class="accordion-header" id="payRateHeading">
                                        <button class="accordion-button collapsed" type="button"
                                            data-bs-toggle="collapse" data-bs-target="#leavePayRate"
                                            aria-expanded="false" aria-controls="leavePayRate">
                                            Leave Pay Rate
                                        </button>
                                    </h2>
                                    <div id="leavePayRate" class="accordion-collapse collapse"
                                        aria-labelledby="payRateHeading" data-bs-parent="#policyDetailsAccordion">
                                        <div class="accordion-body">
                                            <div class="mb-3">
                                                Leave pay rate: <strong>Paid</strong>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="accordion-item">
                                    <h2 class="accordion-header" id="restrictionsHeading">
                                        <button class="accordion-button collapsed" type="button"
                                            data-bs-toggle="collapse" data-bs-target="#policyRestrictions"
                                            aria-expanded="false" aria-controls="policyRestrictions">
                                            Policy Restrictions
                                        </button>
                                    </h2>
                                    <div id="policyRestrictions" class="accordion-collapse collapse"
                                        aria-labelledby="restrictionsHeading" data-bs-parent="#policyDetailsAccordion">
                                        <div class="accordion-body">
                                            <div class="mb-3">
                                                Restict during probation:<strong> on</strong>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Sick Tab -->
                        <div class="tab-pane fade" id="sick" role="tabpanel" aria-labelledby="sick-tab">
                            <div class="alert alert-info" role="alert" id="policyAlert">
                                This template is compliant with labor law guidelines in United Arab
                                Emirates.
                            </div>
                        </div>
                    </div>
                </div>
            </div>

        </div>
        <div class="modal-footer">
            <button type="button" class="btn btn-secondary waves-effect"
                data-bs-dismiss="modal">{{__trans('back')}}</button>
            <!-- <button type="submit" class="btn btn-info waves-effect waves-light">{{__trans('create policy')}} </button> -->
            <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#secondModal">
                {{__trans('create policy')}}
            </button>
        </div>

    </div>
</div>

<div class="modal fade" id="secondModal" tabindex="-1" aria-labelledby="secondModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="secondModalLabel">New Leave Policy</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="container mt-4">


                    <!-- Accordion for Additional Details -->
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
                                                <label for="policy_name" class="form-label">Name your policy</label>
                                                <input type="text" class="form-control" id="policy_name"
                                                    name="policy_name" placeholder="Enter policy name"
                                                    value="{{ old('policy_name') }}" required>
                                            </div>

                                            <!-- Description -->
                                            <div class="mb-3">
                                                <label for="description" class="form-label">Description</label>
                                                <textarea class="form-control" id="description" name="description"
                                                    rows="4" placeholder="Enter policy description"
                                                    required>{{ old('description') }}</textarea>
                                            </div>

                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

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
                                            <input class="form-check-input" type="checkbox" id="conditionalLeaveSwitch">
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
                                            <input type="radio" class="btn-check" name="leaveDays" id="calendarDays"
                                                autocomplete="off" checked>
                                            <label class="btn  btn-option-leave active" id="lblcalendarDays"
                                                onclick="selectleavedaysOption('lblcalendarDays')"
                                                for="calendarDays">Calendar
                                                days</label>

                                            <input type="radio" class="btn-check " name="leaveDays" id="workingDays"
                                                autocomplete="off">
                                            <label class="btn  btn-option-leave" id="lblworkingDays"
                                                onclick="selectleavedaysOption('lblworkingDays')"
                                                for="workingDays">Working
                                                days</label>
                                        </div>
                                        <div class="form-check form-switch mt-2">

                                            <input class="form-check-input" type="checkbox" id="excludePublicHolidays">
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
                                            <input type="radio" class="btn-check" name="recurringPolicy"
                                                id="annuallyRecurring" autocomplete="off" checked>
                                            <label class="btn  btn-option-recurring active" id="annualrecurring"
                                                onclick="selectrecurringOption('annualrecurring')"
                                                for="annuallyRecurring">Annually
                                                recurring</label>

                                            <input type="radio" class="btn-check" name="recurringPolicy"
                                                id="oneTimeOnly" autocomplete="off">
                                            <label class="btn  btn-option-recurring" id="onetimeonly"
                                                onclick="selectrecurringOption('onetimeonly')" for="oneTimeOnly">One
                                                time
                                                only</label>
                                        </div>
                                        <div class="form-check form-switch mt-2">
                                            <input class="form-check-input" type="checkbox" id="carryOverLeave">
                                            <label class="form-check-label" for="carryOverLeave">
                                                Carry over paid leave allowance each cycle
                                            </label>
                                        </div>
                                        <div id="carryOverInput" class="mt-3" style="display: none;">
                                            <label class="form-label" for="carryOverDays">Carry over days</label>
                                            <input type="number" class="form-control" id="carryOverDays" placeholder="0"
                                                value="0">
                                            <small class="text-muted">You can change this number for individual
                                                employees.</small>
                                        </div>
                                    </div>
                                    <!-- Sub-Accordion for Leave Allowances -->
                                    <div class="accordion" id="leaveAllowanceAccordion">
                                        <!-- Leave Allowance 1 -->
                                        <!-- <div class="accordion-item" id="leaveAllowanceItem1">
                                            <h2 class="accordion-header" id="leaveAllowanceHeading1">
                                                <button class="accordion-button" type="button" data-bs-toggle="collapse"
                                                    data-bs-target="#leaveAllowanceCollapse1" aria-expanded="true"
                                                    aria-controls="leaveAllowanceCollapse1">
                                                    Leave Allowance 1
                                                </button>
                                            </h2>
                                            <div id="leaveAllowanceCollapse1" class="accordion-collapse collapse show"
                                                aria-labelledby="leaveAllowanceHeading1"
                                                data-bs-parent="#leaveAllowanceAccordion">
                                                <div class="accordion-body">
                                                    <div class="mb-3">
                                                        <label class="form-label">If employee's job tenure is</label>
                                                        <div class="row g-2">
                                                            <div class="col-md-3">
                                                                <select class="form-select">
                                                                    <option value="between" selected>Between</option>
                                                                    <option value="greater_equal">Less than</option>
                                                                    <option value="greater_equal">Greater than</option>
                                                                    <option value="greater_equal">Greater than and equal
                                                                        to
                                                                    </option>
                                                                    <option value="less_equal">Less than and equal to
                                                                    </option>
                                                                </select>
                                                            </div>
                                                            <div class="col-md-3">
                                                                <input type="number" class="form-control"
                                                                    placeholder="0">
                                                            </div>
                                                            <div class="col-md-3">
                                                                <input type="number" class="form-control"
                                                                    placeholder="12">
                                                            </div>
                                                            <div class="col-md-3">
                                                                <select class="form-select">
                                                                    <option selected>Month(s)</option>
                                                                    <option>Year(s)</option>
                                                                </select>
                                                            </div>
                                                        </div>
                                                    </div>

                                                    <div class="mb-3">
                                                        <label class="form-label">Then update the leave allowance
                                                            to</label>
                                                        <div class="input-group">
                                                            <input type="number" class="form-control" placeholder="24">
                                                            <span class="input-group-text">Day(s)</span>
                                                        </div>
                                                    </div>

                                                    <div class="mb-3">
                                                        <h6>Should leave allowance be accrued?</h6>
                                                        <div class="form-check form-switch mb-2">
                                                            <input class="form-check-input" type="checkbox"
                                                                id="accrueAllowance" checked>
                                                            <label class="form-check-label" for="accrueAllowance">
                                                                Accrue allowance on a monthly basis
                                                            </label>
                                                            <small class="form-text text-muted">ON: 2 days will be added
                                                                to
                                                                available
                                                                days each month</small>
                                                        </div>
                                                        <div class="form-check mb-2">
                                                            <input class="form-check-input" type="checkbox"
                                                                id="allowNegative" checked>
                                                            <label class="form-check-label" for="allowNegative">
                                                                Allow negative balance
                                                            </label>
                                                            <small class="form-text text-muted">
                                                                ON: Employees can request more than accrued days each
                                                                month (2
                                                                days) but
                                                                not more than the total allowance (24 days)
                                                            </small>
                                                        </div>
                                                        <div class="form-check">
                                                            <input class="form-check-input" type="checkbox"
                                                                id="reduceAccrued">
                                                            <label class="form-check-label" for="reduceAccrued">
                                                                Reduce accrued days if employee takes a leave from a
                                                                different
                                                                policy
                                                            </label>
                                                            <small class="form-text text-muted">
                                                                OFF: No effect on allowance accrual
                                                            </small>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div> -->

                                        <div class="accordion" id="leaveAllowanceAccordion">
                                            <!-- Initial Accordion Items -->
                                            <div class="accordion-item" id="leaveAllowanceItem1">
                                                <h2 class="accordion-header" id="leaveAllowanceHeading1">
                                                    <button class="accordion-button" type="button"
                                                        data-bs-toggle="collapse"
                                                        data-bs-target="#leaveAllowanceCollapse1" aria-expanded="true"
                                                        aria-controls="leaveAllowanceCollapse1">
                                                        Leave Allowance 1
                                                    </button>
                                                </h2>
                                                <div id="leaveAllowanceCollapse1"
                                                    class="accordion-collapse collapse show"
                                                    aria-labelledby="leaveAllowanceHeading1"
                                                    data-bs-parent="#leaveAllowanceAccordion">
                                                    <div class="accordion-body">
                                                        <!-- Accordion body content goes here -->
                                                        <!-- This will be dynamically duplicated -->
                                                        <div class="mb-3">
                                                            <label class="form-label">If employee's job tenure
                                                                is</label>
                                                            <div class="row g-2">
                                                                <div class="col-md-3">
                                                                    <select class="form-select">
                                                                        <option value="between" selected>Between
                                                                        </option>
                                                                        <option value="less_than">Less than</option>
                                                                        <option value="greater_than">Greater than
                                                                        </option>
                                                                        <option value="greater_equal">Greater than and
                                                                            equal to</option>
                                                                        <option value="less_equal">Less than and equal
                                                                            to</option>
                                                                    </select>
                                                                </div>
                                                                <div class="col-md-3">
                                                                    <input type="number" class="form-control"
                                                                        placeholder="0">
                                                                </div>
                                                                <div class="col-md-3">
                                                                    <input type="number" class="form-control"
                                                                        placeholder="12">
                                                                </div>
                                                                <div class="col-md-3">
                                                                    <select class="form-select">
                                                                        <option selected>Month(s)</option>
                                                                        <option>Year(s)</option>
                                                                    </select>
                                                                </div>
                                                            </div>
                                                        </div>
                                                        <div class="mb-3">
                                                            <label class="form-label">Then update the leave allowance
                                                                to</label>
                                                            <div class="input-group">
                                                                <input type="number" class="form-control"
                                                                    placeholder="24">
                                                                <span class="input-group-text">Day(s)</span>
                                                            </div>
                                                        </div>
                                                        <div class="mb-3">
                                                            <h6>Should leave allowance be accrued?</h6>
                                                            <div class="form-check form-switch mb-2">
                                                                <input class="form-check-input" type="checkbox"
                                                                    id="accrueAllowance1" checked>
                                                                <label class="form-check-label"
                                                                    for="accrueAllowance1">Accrue allowance on a monthly
                                                                    basis</label>
                                                                <small class="form-text text-muted">ON: 2 days will be
                                                                    added to available days each month</small>
                                                            </div>
                                                            <div class="form-check mb-2">
                                                                <input class="form-check-input" type="checkbox"
                                                                    id="allowNegative1" checked>
                                                                <label class="form-check-label"
                                                                    for="allowNegative1">Allow negative balance</label>
                                                                <small class="form-text text-muted">
                                                                    ON: Employees can request more than accrued days but
                                                                    not more than the total allowance
                                                                </small>
                                                            </div>
                                                            <div class="form-check">
                                                                <input class="form-check-input" type="checkbox"
                                                                    id="reduceAccrued1">
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
                                            <!-- Button to Add Another Condition -->

                                        </div>
                                        <button class="btn btn-primary mt-3" id="addConditionButton">Add Another
                                            Condition</button>

                                    </div>

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
                                        Leave pay rate: <strong>Paid</strong>
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
                                        <input class="form-check-input" type="checkbox" id="enableLeaveSalary">
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
                                        <input class="form-check-input" type="checkbox" id="restrictEditingLeaveDays">
                                        <label class="form-check-label" for="restrictEditingLeaveDays">
                                            Restrict editing of leave days
                                        </label>
                                        <p class="text-muted mt-1">
                                            OFF: Leave days calculated at the time of leave request can be changed by
                                            the employee and the approvers (only if request is in pending state)
                                        </p>
                                    </div>
                                    <div class="form-check form-switch mt-3">
                                        <input class="form-check-input" type="checkbox" id="restrictDuringProbation"
                                            checked>
                                        <label class="form-check-label" for="restrictDuringProbation">
                                            Restrict during probation
                                        </label>
                                        <p class="text-muted mt-1">
                                            ON: Employees are not able to create leave request during their probation
                                            period
                                        </p>
                                    </div>
                                </div>
                            </div>
                        </div>

                    </div>
                </div>
            </div>

        </div>
        <div class="modal-footer">
            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
        </div>
    </div>
</div>
</div>

<script>
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
    let allowanceCounter = 1; // Start with the initial allowance count
    alert(allowanceCounter);

    $("#addConditionButton").on("click", function() {
        allowanceCounter++;
        alert(allowanceCounter);

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
                                    <select class="form-select">
                                        <option value="between" selected>Between</option>
                                        <option value="less_than">Less than</option>
                                        <option value="greater_than">Greater than</option>
                                        <option value="greater_equal">Greater than and equal to</option>
                                        <option value="less_equal">Less than and equal to</option>
                                    </select>
                                </div>
                                <div class="col-md-3">
                                    <input type="number" class="form-control" placeholder="0">
                                </div>
                                <div class="col-md-3">
                                    <input type="number" class="form-control" placeholder="12">
                                </div>
                                <div class="col-md-3">
                                    <select class="form-select">
                                        <option selected>Month(s)</option>
                                        <option>Year(s)</option>
                                    </select>
                                </div>
                            </div>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Then update the leave allowance to</label>
                            <div class="input-group">
                                <input type="number" class="form-control" placeholder="24">
                                <span class="input-group-text">Day(s)</span>
                            </div>
                        </div>
                        <div class="mb-3">
                            <h6>Should leave allowance be accrued?</h6>
                            <div class="form-check form-switch mb-2">
                                <input class="form-check-input" type="checkbox" id="accrueAllowance${allowanceCounter}" checked>
                                <label class="form-check-label" for="accrueAllowance${allowanceCounter}">
                                    Accrue allowance on a monthly basis
                                </label>
                                <small class="form-text text-muted">ON: 2 days will be added to available days each month</small>
                            </div>
                            <div class="form-check mb-2">
                                <input class="form-check-input" type="checkbox" id="allowNegative${allowanceCounter}" checked>
                                <label class="form-check-label" for="allowNegative${allowanceCounter}">
                                    Allow negative balance
                                </label>
                                <small class="form-text text-muted">
                                    ON: Employees can request more than accrued days but not more than the total allowance
                                </small>
                            </div>
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" id="reduceAccrued${allowanceCounter}">
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