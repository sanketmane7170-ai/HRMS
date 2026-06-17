<div class="modal-dialog modal-lg">
    <div class="modal-content">
        <div class="modal-header">
            <h4 class="modal-title">{{__trans('Leave_Policy')}}</h4>
            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <form action="#" datatable="true" class="ajax-form-submit reset">
            @csrf
            <div class="modal-body p-4">
                @can('Create Leave Type')

                    {{-- ACCRUAL POLICIES --}}
                    <div class="card mb-3 shadow-sm">
                        <div class="card-header fw-bold">
                            Leave Accrual Policy
                        </div>
                        <div class="card-body">
                            <div class="row g-3">
                                {{-- DAILY --}}
                                <div class="col-md-4">
                                    <div class="form-check form-switch fs-6">
                                        <input class="form-check-input accrual-switch"
                                            type="checkbox"
                                            id="dailyAccrual"
                                            data-url="{{ route('backend.dailyLeavePolicy') }}"
                                            @if($daily_leave_policy && $daily_leave_policy->value==1) checked @endif>
                                        <label class="form-check-label" for="dailyAccrual">
                                            Daily Accrual
                                        </label>
                                    </div>
                                </div>

                                {{-- MONTHLY --}}
                                <div class="col-md-4">
                                    <div class="form-check form-switch fs-6">
                                        <input class="form-check-input accrual-switch"
                                            type="checkbox"
                                            id="monthlyAccrual"
                                            data-url="{{ route('backend.isMonthWiseShowLeave') }}"
                                            @if($monthwiseLeaveSetting && $monthwiseLeaveSetting->value==1) checked @endif>
                                        <label class="form-check-label" for="monthlyAccrual">
                                            Monthly Accrual
                                        </label>
                                    </div>
                                </div>

                                {{-- ANNUAL --}}
                                <div class="col-md-4">
                                    <div class="form-check form-switch fs-6">
                                        <input class="form-check-input accrual-switch"
                                            type="checkbox"
                                            id="annualAccrual"
                                            data-url="{{ route('backend.annualLeavePolicy') }}"
                                            @if($annual_leave_policy && $annual_leave_policy->value==1) checked @endif >
                                        <label class="form-check-label" for="annualAccrual">
                                            Annual Accrual
                                        </label>
                                    </div>
                                </div>

                            </div>
                        </div>
                    </div>

                    {{-- NEW USER ACCRUAL POLICIES --}}
                    <div class="card mb-3 shadow-sm newUserPolicy" >
                        <div class="card-header fw-bold">
                            New User Leave Accrual Policy (upto 1 year)
                        </div>
                        <div class="card-body">
                            <div class="row g-3">
                                {{-- DAILY --}}
                                <div class="col-md-4">
                                    <div class="form-check form-switch fs-6">
                                        <input 
                                            class="form-check-input toggle-switch newuserdailypolicy" @if($newUserDailyLeavePolicy && $newUserDailyLeavePolicy->value==1) checked @endif
                                            type="checkbox" 
                                            id="isAllowSwitch" 
                                            data-url="{{ route('backend.newUserdailyLeavePolicy') }}"
                                            data-token="{{ csrf_token() }}"
                                        >
                                        <label class="form-check-label" for="dailyAccrual">
                                            Daily Accrual
                                        </label>
                                    </div>
                                </div>

                                {{-- MONTHLY --}}
                                <div class="col-md-4">
                                    <div class="form-check form-switch fs-6">
                                        <input 
                                            class="form-check-input toggle-switch newusermonthlypolicy" @if($newUserMonthlyLeavePolicy && $newUserMonthlyLeavePolicy->value==1) checked @endif
                                            type="checkbox" 
                                            id="isAllowSwitch" 
                                            data-url="{{ route('backend.newUserMonthlyLeavePolicy') }}"
                                            data-token="{{ csrf_token() }}"
                                        >
                                        <label class="form-check-label" for="monthlyAccrual">
                                            Monthly Accrual
                                        </label>
                                    </div>
                                </div>

                                {{-- ANNUAL --}}
                                {{--  <div class="col-md-4">
                                    <div class="form-check form-switch fs-6">
                                        <input class="form-check-input"
                                            type="checkbox"
                                            id="annualAccrual"
                                            data-url="{{ route('backend.annualLeavePolicy') }}"
                                            @if($annual_leave_policy && $annual_leave_policy->value==1) checked @endif >
                                        <label class="form-check-label" for="annualAccrual">
                                            Annual Accrual
                                        </label>
                                    </div>
                                </div>  --}}

                            </div>
                        </div>
                    </div>

                    {{-- ADVANCED OPTIONS  --}}
                    <div class="card shadow-sm d-none" id="advancedPolicy">
                        <div class="card-header fw-bold">
                            Accrual Setting For New User (upto 1 years)
                        </div>
                        <div class="card-body">
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <div class="form-check form-switch">
                                        <input 
                                            class="form-check-input toggle-switch month2leave" @if($monthwise2leave && $monthwise2leave->value==1) checked @endif
                                            type="checkbox" 
                                            id="isAllowSwitch" 
                                            data-url="{{ route('backend.isMonthWise2LeaveAdd') }}"
                                            data-token="{{ csrf_token() }}"
                                        >
                                        <label class="form-check-label">
                                            Within 6 months accrual of 2 days
                                        </label>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-check form-switch">
                                        <input 
                                            class="form-check-input toggle-switch year2leave" @if($yearGiven2Leave && $yearGiven2Leave->value==1) checked @endif
                                            type="checkbox" 
                                            id="isAllowSwitch" 
                                            data-url="{{ route('backend.1yearGiven2Leave') }}"
                                            data-token="{{ csrf_token() }}"
                                        >
                                        <label class="form-check-label">
                                            Within 1 year accrual of 2 days
                                        </label>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    {{-- Recurring Leave Rules --}}
                    <div class="card shadow-sm">
                        <div class="card-header fw-bold">
                            Recurring Policy
                        </div>
                        <div class="card-body">
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <div class="form-check form-switch">
                                        <input 
                                            class="form-check-input toggle-switch recurringJoiningPolicy" @if($leaveRecurringPolicy && $leaveRecurringPolicy->value=='joining_to_joining') checked @endif
                                            type="checkbox" 
                                            name="joining_to_joining"
                                            data-url="{{ route('backend.leaveRecurringPolicy') }}"
                                            data-token="{{ csrf_token() }}"
                                        >
                                        <label class="form-check-label">
                                            Based on Joining Date to Joining Date
                                        </label>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-check form-switch">
                                        <input 
                                            class="form-check-input toggle-switch recurringAnnualPolicy" @if($leaveRecurringPolicy && $leaveRecurringPolicy->value=='annual_calendar') checked @endif
                                            type="checkbox" 
                                            name="annual_calendar"
                                            data-url="{{ route('backend.leaveRecurringPolicy') }}"
                                            data-token="{{ csrf_token() }}"
                                        >
                                        <label class="form-check-label">
                                            Based on Annual Calendar Setting
                                        </label>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- Additional Leave Rules --}}
                    <div class="card shadow-sm">
                        <div class="card-header fw-bold">
                            Additional Leave Rules
                        </div>
                        <div class="card-body">
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <div class="form-check form-switch">
                                        <input 
                                            class="form-check-input toggle-switch year2leave" @if($leaveAllowInProbation && $leaveAllowInProbation->value==1) checked @endif
                                            type="checkbox" 
                                            id="isAllowSwitch" 
                                            data-url="{{ route('backend.leaveAllowInProbation') }}"
                                            data-token="{{ csrf_token() }}"
                                        >
                                        <label class="form-check-label">
                                            Leave allow in probation period
                                        </label>
                                    </div>
                                </div>

                                <div class="col-md-6">
                                    <div class="form-check form-switch">
                                        <input 
                                            class="form-check-input toggle-switch year2leave" @if($allowNegativeLeave && $allowNegativeLeave->value==1) checked @endif
                                            type="checkbox" 
                                            id="isAllowSwitch" 
                                            data-url="{{ route('backend.isAllowNegativeLeave') }}"
                                            data-token="{{ csrf_token() }}"
                                        >
                                        <label class="form-check-label">
                                            Allow negative leave
                                        </label>
                                    </div>
                                </div>
                                
                            </div>
                        </div>
                    </div>
                
                @endcan
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary waves-effect" data-bs-dismiss="modal">{{__trans('close')}}</button>
            </div>
        </form>
    </div>
</div>

<script>
    $(document).ready(function () {

        function toggleAdvancedPolicy() {
            let anyChecked = $('.accrual-switch:checked').length > 0;
            $('#advancedPolicy').toggleClass('d-none', !anyChecked);
        }

        // Initial load
        toggleAdvancedPolicy();
        $('.accrual-switch').on('change', function () {
            // Allow only ONE accrual
            if (this.checked) {
                $('.accrual-switch').not(this).prop('checked', false);
            }
            toggleAdvancedPolicy();
            // OPTIONAL: AJAX CALL
            let url = $(this).data('url');
            let value = this.checked ? true : false;

            fetch(url, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
                body: JSON.stringify({ allow: value })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    alert('Policy setting updated successfully!');
                } else {
                    alert('Error updating status.');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('An error occurred.');
            });
        });
    });

    function toggleNewUserPolicy() {
        const annualChecked  = $('#annualAccrual').is(':checked');
        const dailyChecked   = $('#dailyAccrual').is(':checked');
        const monthlyChecked = $('#monthlyAccrual').is(':checked');

        if (annualChecked && !dailyChecked && !monthlyChecked) {
            $('.newUserPolicy').removeClass('d-none');
        } else {
            $('.newUserPolicy').addClass('d-none');
        }
    }

    // Run on page load
    $(document).ready(function () {
        toggleNewUserPolicy();
    });

    // Run on change
    $(document).on('change', '#annualAccrual, #dailyAccrual, #monthlyAccrual', function () {
        toggleNewUserPolicy();
    });


    $(document).on('change', '.newuserdailypolicy', function () {
        if (this.checked) {
            $('.newusermonthlypolicy').prop('checked', false);
        }
        let isChecked = this.checked;
        let url = this.dataset.url;
        let token = this.dataset.token;

        // Send the request to the server
        fetch(url, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': token
            },
            body: JSON.stringify({ allow: isChecked })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                alert('Policy setting updated successfully!');
            } else {
                alert('Error updating status.');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('An error occurred.');
        });
    });

    $(document).on('change', '.newusermonthlypolicy', function () {
        if (this.checked) {
            $('.newuserdailypolicy').prop('checked', false);
        }
        let isChecked = this.checked; // true or false
        let url = this.dataset.url; // The route URL from the data attribute
        let token = this.dataset.token; // CSRF token for the request

        // Send the request to the server
        fetch(url, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': token
            },
            body: JSON.stringify({ allow: isChecked })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                alert('Policy setting updated successfully!');
            } else {
                alert('Error updating status.');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('An error occurred.');
        });
    });

    $(document).on('change', '.recurringJoiningPolicy', function() {

        let isChecked = this.checked;
        let url = this.dataset.url;
        let token = this.dataset.token;

        // Send the request to the server
        fetch(url, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': token
            },
            body: JSON.stringify({ allow: isChecked,recurring_policy:this.name })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                $('.recurringAnnualPolicy').not(this).prop('checked', false);
                alert('Policy setting updated successfully!');
            } else {
                alert('Error updating status.');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('An error occurred.');
        });
    });

    $(document).on('change', '.recurringAnnualPolicy', function() {

        let isChecked = this.checked;
        let url = this.dataset.url;
        let token = this.dataset.token;

        // Send the request to the server
        fetch(url, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': token
            },
            body: JSON.stringify({ allow: isChecked,recurring_policy:this.name })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                $('.recurringJoiningPolicy').not(this).prop('checked', false);
                alert('Policy setting updated successfully!');
            } else {
                alert('Error updating status.');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('An error occurred.');
        });
    });
</script>
