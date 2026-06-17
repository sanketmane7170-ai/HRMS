@extends('layouts.backend')

@push('css')
<style>
    .info {
        margin-top: 0.5rem !important;
    }
</style>
@endpush
@section('content')
<!-- Page Wrapper -->
<div class="page-wrapper">
    <div class="content container-fluid">
        <!-- Page Header -->
        <div class="page-header">
            <div class="row align-items-center">
                <div class="col">
                    <h3 class="page-title mt-5 {{$user->status=='in-active' ? 'text-danger' : ''}}" style="{{$user->status=='in-active' ? 'text-decoration: line-through;' : ''}}">{{$user->name}}</h3>
                </div>
                <div class="col-auto mt-5">
                    @can('Edit User')
                    <a href="{{route('backend.users.send-welcome-notification',$user)}}"
                        class="btn btn-sm btn-success action-button" method="POST"
                        data-alert="{{__trans('are_you_sure_want_to_send_welcome_notification?')}}">
                        {{__trans('send_welcome_notification')}}
                    </a>
                    <a href="{{route('backend.users.edit',$user)}}"
                        class="btn btn-sm btn-warning">{{__trans('edit_user')}}</a>
                    @endcan
                </div>
            </div>
        </div>
        <div class="col-xl-12">
            <ul class="nav nav-tabs nav-justified">
                <li class="nav-item">
                    <a class="nav-link @if(!request()->type) active @endif" href="#basic-details"
                        data-bs-toggle="tab">{{__trans('basic_details')}}</a>
                </li>
                @can('Dependent Details User')
                <li class="nav-item">
                    <a class="nav-link  @if(request()->type =='dependent') active @endif" href="#dependent-details"
                        data-bs-toggle="tab">{{__trans('dependent_details')}}</a>
                </li>
                @endcan
                @if(isModuleEnabled('Asset'))
                @can('Assets Details User')
                <li class="nav-item">
                    <a class="nav-link  @if(request()->type =='asset') active @endif" href="#asset-details"
                        data-bs-toggle="tab">{{__trans('asset_details')}}</a>
                </li>
                @endcan
                @endif
                @if (hasPermission('Documents User'))
                <li class="nav-item">
                    <a class="nav-link  @if(request()->type =='document') active @endif" href="#document-details"
                        data-bs-toggle="tab">{{__trans('documents')}}</a>
                </li>
                @endif
                @can('Issued Documents User')
                <li class="nav-item">
                    <a class="nav-link  @if(request()->type =='documentrequests') active @endif" href="#document-requests"
                        data-bs-toggle="tab">{{__trans('issued_documents')}}</a>
                </li>
                @endcan
                @if (hasPermission('End of Service User'))
                <li class="nav-item">
                    <a class="nav-link  @if(request()->type =='offboarding') active @endif" href="#offboarding"
                        data-bs-toggle="tab">{{__trans('offboarding')}}</a>
                </li>

                <li class="nav-item">
                    <a class="nav-link  @if(request()->type =='endservice') active @endif" href="#endservice" data-bs-toggle="tab">{{__trans('end_of_service')}}</a>
                </li>
                @endif
                @can('View Salary User')
                <li class="nav-item">
                    <a class="nav-link  @if(request()->type =='showSalaryDetails') active @endif" href="#showSalaryDetails" data-bs-toggle="tab">{{__trans('salary_details')}}</a>
                </li>
                @endcan
                @can('Leave User')
                <li class="nav-item">
                    <a class="nav-link  @if(request()->type =='leave') active @endif" href="#leave"
                        data-bs-toggle="tab">{{__trans('leave')}}</a>
                </li>
                @endcan
                @can('Increments User')
                <li class="nav-item">
                    <a class="nav-link  @if(request()->type =='increments') active @endif" href="#increments"
                        data-bs-toggle="tab">{{__trans('increments')}}</a>
                </li>
                @endcan
                @can('Service History User')
                <li class="nav-item">
                    <a class="nav-link  @if(request()->type =='service_history') active @endif" href="#service_history"
                        data-bs-toggle="tab">{{__trans('service_history')}}</a>
                </li>
                @endcan

            </ul>
            <div class="tab-content">
                <div class="tab-pane  @if(!request()->type) show active @endif" id="basic-details">
                    @include('backend.users.partials.basic-details')
                </div>
                <div class="tab-pane  @if(request()->type =='dependent') show active @endif" id="dependent-details">
                    @include('backend.users.partials.dependent-details')
                </div>
                @if(isModuleEnabled('Asset'))
                <div class="tab-pane  @if(request()->type =='asset') show active @endif" id="asset-details">
                    @include('backend.users.partials.asset-details')
                </div>
                @endif
                <div class="tab-pane  @if(request()->type =='document') show active @endif" id="document-details">
                    @include('backend.users.partials.document-details')
                </div>
                <div class="tab-pane  @if(request()->type =='documentrequests') show active @endif" id="document-requests">
                    @include('backend.users.partials.document-requests')
                </div>
                <div class="tab-pane  @if(request()->type =='offboarding') show active @endif" id="offboarding">
                    @include('backend.users.partials.offBoarding')
                </div>
                @if (hasPermission('End of Service User'))
                <div class="tab-pane  @if(request()->type =='endservice') show active @endif" id="endservice">
                    @include('backend.users.partials.endservice-details', ['offboard' => $offboard,'settlement' => $settlement])
                </div>
                @endif
                @can('View Salary User')
                <div class="tab-pane  @if(request()->type =='showSalaryDetails') show active @endif" id="showSalaryDetails">
                    @include('backend.users.partials.salary-details')
                </div>
                @endcan
                <div class="tab-pane  @if(request()->type =='leave') show active @endif" id="leave">
                    <x-user-leave-balance :user=$user />
                </div>

                <div class="tab-pane  @if(request()->type =='increments') show active @endif" id="increments">
                    @include('backend.users.partials.increments')
                </div>

                <div class="tab-pane  @if(request()->type =='service_history') show active @endif" id="service_history">
                    @include('backend.users.partials.service_history')
                </div>
            </div>
        </div>
    </div>
</div>
<div class="modal" id="editModal"></div>
<div class="modal fade" id="loaderModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content text-center p-4">
            <span class="mt-3">Please wait...</span>
            <div class="spinner-border text-primary" role="status">
            </div>
        </div>
    </div>
</div>
@push('scripts')


<script>
    var getResponseAPI = "{{route('backend.users.leave-calculate',[$user->id])}}";
    var getpolicyUrl = "{{route('backend.getSettlementLeavePolicy')}}";
    var storeAbsentDaysUrl = "{{ route('backend.storeAbsentDays') }}";
    var settlementSubmitAPI = "{{ route('backend.users.finalsettlement', [$user->id]) }}";
    var getsalaryUrl = "{{ route('backend.getoffBoarding') }}";
    var getaddMonthDayUrl = "{{ route('backend.addMonthDay') }}";
    var userId = "{{ $user->id }}";
    
    var gross_salary = $('#gross_salary').val();
    var basic_salary = $('#basic_salary').val();
    let total_leave_amount = 0;
    $(document).ready(function() {
        $('#month_select').select2({
            placeholder: 'Select months',
            width: '100%'
        });
    });

    $(document).on('change', '.addleave', function () {
        var basic_salary = $('#basic_salary').val();
        var gross_salary = $('#gross_salary').val();

        const leave_type_id = $(this).data('leave-id');
        const leaveName = $(this).data('leave-name');
        const leaveBalance = $(this).data('leave-balance');
        const isChecked = $(this).is(':checked');
        let leaveAmount = 0;
        let monthday = 30;
        let salaryType = gross_salary;

        if (isChecked) {
            $.ajax({
                url: "{{ route('backend.getSettlementLeavePolicy') }}",
                type: 'POST',
                data: {
                    _token: '{{ csrf_token() }}',
                    leave_type_id: leave_type_id,
                    user_id: {{ $user->id }}
                },
                success: function (response) {
                    let monthday, salaryType;
                    if (response.data && response.data.spolicy.id) {
                        monthday = response.data.spolicy.month_day;
        
                        salaryType = (response.data.spolicy.salary_type === 'Basic') ? basic_salary : gross_salary;
        
                    } else {
                        console.warn('Using default monthday and salaryType');
                        monthday = 30;
                        salaryType = gross_salary;
                    }

                    // Calculate leave amount
                    let leaveAmount = ((salaryType / monthday) * leaveBalance).toFixed(2);

                    if(response.data && response.data.spolicy.id && response.data.spolicy.month_day=='365.0'){
                        leaveAmount = ((salaryType * 12 / 365) * leaveBalance).toFixed(2);
                    }
                    
                    const newRow = $("<tr class='leave-row' data-leave-name='" + leaveName + "'>");
                    newRow.append("<td><input type='text' value='" + leaveName + "' class='leave-name' style='display: none;' />" + leaveName + "</td>");
                    newRow.append("<td>" + leaveBalance + " Days</td>");
                    newRow.append("<td><input type='number' value='" + leaveAmount + "' class='leave-sum' /></td>");
                    $("#leaveTable tr:contains('Leave encashments')").after(newRow);
        
                    // Update total
                    var total = $('#total_amount').text();
                    var total_calculated_amount = parseFloat(total) + parseFloat(leaveAmount);
                    $('#total_amount').text(total_calculated_amount.toFixed(2));
                },
                error: function (xhr) {
                    console.error('Error fetching leave policy:', xhr);
                }
            });
        } else {
            // Remove Row
            const row = $("tr.leave-row[data-leave-name='" + leaveName + "']");
            const inputVal = parseFloat(row.find('.leave-sum').val()) || 0;

            var total = $('#total_amount').text();
            total_calculated_amount = parseFloat(total) - parseFloat(inputVal);
            total_calculated_amount = total_calculated_amount.toFixed(2);
            $('#total_amount').text(total_calculated_amount);

            row.remove();
        }
    });

    function addMonthRow(checkbox) {
        const isChecked = checkbox.is(':checked');
        const monthName = checkbox.data('month-name');
        const monthId = checkbox.data('month-id');
        const day = checkbox.data('day') || '';

        // Remove if unchecked
        if (!isChecked) {
            $("#leaveTable tr[data-month-name='" + monthName + "']").remove();
            return;
        }

        // Prevent duplicates
        if ($("#leaveTable tr[data-month-name='" + monthName + "']").length) return;

        // Create row
        const newRow = $("<tr class='month-row' data-month-name='" + monthName + "'>");
        newRow.append("<td><input type='text' value='" + monthName + "' class='month-name' style='display: none;' />" + monthName + "</td>");
        newRow.append("<td>Working Days</td>");
        newRow.append("<td><input type='number' data-month-number='" + monthId + "' class='month-day' value='" + day + "' /></td>");

        $("#leaveTable tr:contains('Selected month for salary')").after(newRow);
    }

    // Handle checkbox toggle
    {{--  $(document).on('change', '.addmonthday', function () {
        addMonthRow($(this));
    });  --}}

    // On document ready — check all pre-checked checkboxes
    $(document).ready(function () {
        $('.addmonthday:checked').each(function () {
            addMonthRow($(this));
        });
    });

    $(document).on('change', '.addmonthday', function () {

        const monthID = $(this).data('month-id');
        const monthName = $(this).data('month-name');
        const isChecked = $(this).is(':checked');

        if (isChecked) {
            const newRow = $("<tr class='month-row' data-month-name='" + monthID + "'>");
            newRow.append("<td><input type='text' value='" + monthID + "' class='month-name' style='display: none;' />" + monthName + "</td>");
            newRow.append("<td>Working Days</td>");
            newRow.append("<td><input type='number' data-month-number='" + monthID + "' class='month-day' /></td>");
            $("#leaveTable tr:contains('Selected month for salary')").after(newRow);
        } else {
            $.ajax({
                url: "{{ route('backend.removeMonthDay') }}",
                type: 'POST',
                data: {
                    _token: '{{ csrf_token() }}',
                    user_id: userId,
                    month: monthID,
                },
                success: function (response) {
                    console.log('Remove days!');
                },
                error: function (xhr) {
                    console.error('Error fetching leave policy:', xhr);
                }
            });
            // Remove Row
            const row = $("tr.month-row[data-month-name='" + monthID + "']");
            row.remove();
            location.reload();
        }
    });
$(document).ready(function() {
    $(document).on('change', '.month-day', function () {

        const monthdayval = $(this).val();
        const monthnumber = $(this).data('month-number');
        
        var promise = $.ajax({
            url: getaddMonthDayUrl,
            type: 'POST',
            data: {
                _token: $('meta[name="csrf-token"]').attr('content'),
                user_id: userId,
                monthday: monthdayval,
                month: monthnumber,
            }
        }).then(function (response) {
            if(response.data !== null) {
                if (response.data.settlementSalary > 0) {

                    let oldamount = $('.settlementSalary-sum').val();
                    let oldtotal = parseFloat($('#total_amount').text()) || 0;
                    let oldtotal_calculated_amount = oldtotal - parseFloat(oldamount);
                    $('#total_amount').text(oldtotal_calculated_amount.toFixed(2));

                    total_salary_amount = response.data.settlementSalary;
                    $('.settlementSalary-sum').val(total_salary_amount.toFixed(2));
                    
                    const monthNumbers = response.data.off.salary_month.split(',');
                    const monthNames = [
                    "January", "February", "March", "April", "May", "June",
                    "July", "August", "September", "October", "November", "December"
                    ];

                    const readableMonths = monthNumbers.map(m => {
                    const index = parseInt(m.trim()) - 1;
                    return monthNames[index] || '';
                    }).filter(Boolean).join(', ');

                    // === Final Salary Row ===
                    let salaryRow = $(".row-final-salary");
                    if (!salaryRow.length) {
                        salaryRow = $("<tr class='row-final-salary'>");
                        salaryRow.append("<td><input type='text' class='month-name' style='display: none;' /> Final Salary </td>");
                        salaryRow.append("<td class='month-list'></td>");
                        salaryRow.append("<td><input type='number' class='settlementSalary-sum' disabled /></td>");
                        $("#leaveTable #salaryRow").after(salaryRow);
                    }
                    salaryRow.find('.month-list').text(readableMonths + " Months (" + response.data.working_day + ") working day");
                    salaryRow.find('.settlementSalary-sum').val(response.data.settlementSalary.toFixed(2));

                    {{--  // === Gross Salary ===
                    let grossRow = $("#leaveTable .row-gross-salary");
                    if (!grossRow.length) {
                        grossRow = $("<tr class='row-gross-salary'>");
                        grossRow.append("<td><input type='text' class='month-name' style='display: none;' /> Gross Salary </td>");
                        grossRow.append("<td>-</td>");
                        grossRow.append("<td><input type='number' class='gross_salary-sum' disabled /></td>");
                        $("#leaveTable #salaryRow").after(grossRow);
                    }
                    grossRow.find('.gross_salary-sum').val((parseFloat(response.data.gross_salary) || 0).toFixed(2));  --}}

                    // === total deduction ===
                    let deductionRow = $("#leaveTable .row-total-deduction");
                    if (!deductionRow.length) {
                        deductionRow = $("<tr class='row-total-deduction'>");
                        deductionRow.append("<td><input type='text' class='month-name' style='display: none;' /> Total Deduction (not fix deduction) </td>");
                        deductionRow.append("<td>-</td>");
                        deductionRow.append("<td><input type='number' class='total_deduction-sum' disabled /></td>");
                        $("#leaveTable #salaryRow").after(deductionRow);
                    }
                    deductionRow.find('.gross_salary-sum').val((parseFloat(response.data.total_deduction) || 0).toFixed(2));

                    // === Overtime Amount ===
                    let overtimeRow = $("#leaveTable .row-overtime-amount");
                    if (!overtimeRow.length) {
                        overtimeRow = $("<tr class='row-overtime-amount'>");
                        overtimeRow.append("<td><input type='text' class='month-name' style='display: none;' /> Total Overtime Amount </td>");
                        overtimeRow.append("<td>+</td>");
                        overtimeRow.append("<td><input type='number' class='overtime_amount-sum' disabled /></td>");
                        $("#leaveTable #salaryRow").after(overtimeRow);
                    }
                    overtimeRow.find('.overtime_amount-sum').val(response.data.overtime_amount.toFixed(2));

                    // === monthly expense Amount ===
                    let expenseRow = $(".row-monthly-expense");
                    if (!expenseRow.length) {
                        expenseRow = $("<tr class='row-monthly-expense'>");
                        expenseRow.append("<td><input type='text' class='month-name' style='display: none;' /> Total Monthly Expense </td>");
                        expenseRow.append("<td>+</td>");
                        expenseRow.append("<td><input type='number' class='monthly_expense-sum' disabled /></td>");
                        $("#leaveTable #salaryRow").after(expenseRow);
                    }
                    expenseRow.find('.monthly_expense-sum').val(response.data.monthly_expense.toFixed(2));

                    // === Total Update ===
                    const total = parseFloat($('#total_amount').text()) || 0;
                    const total_calculated_amount = total + parseFloat(total_salary_amount);
                    $('#total_amount').text(total_calculated_amount.toFixed(2));
                }
            }
        }).fail(function (xhr) {
            console.error('Error fetching leave policy:', xhr);
        });
    });
});
</script>
<script src="{{asset('assets/backend/js/endofservice.min.js')}}"></script>
@endpush
@endsection
