@include('backend.users.partials.settlement-pdf-layout')

<div class="row" id="finalsettlementsection">
    <div class="col-sm-12 col-md-12">
        <div class="card light">
            <div class="card-body">
            <div class="row align-items-center">
                <div class="col">
                    <h5 class="page-title">{{__trans('calculated_data')}}</h3>
                </div>
                <div class="col-auto">
                    <a href="{{route('backend.settlement.transaction')}}" class="btn btn-info waves-effect waves-light text-white customhidingInPDF">
                            View Transaction <i class="fa fa-history" aria-hidden="true"></i>
                        </a>
                </div>
            </div>
                <hr>
                <div class="row">
                    <h6>{{__trans('1.personal_information')}}</h6>
                    <div class="col-lg-4">
                        <div class="form-group">
                            <label>Employee Name</label>
                            <input type="text" class="form-control" name="name" id="name"
                                value="{{$user->name}}"
                                disabled>
                        </div>
                    </div>
                    <div class="col-lg-4">
                        <div class="form-group">
                            <label>Employee ID</label>
                            <input type="text" class="form-control" name="eid" id="eid"
                                value="{{$user->employee_id}}"
                                disabled>
                        </div>
                    </div>
                    <div class="col-lg-4">
                        <div class="form-group">
                            <label>Company Name</label>
                            <input type="text" class="form-control" name="companyname" id="companyname"
                                value="{{getSetting('site_title')}}"
                                disabled>
                        </div>
                    </div>
                    <div class="col-lg-4">
                        <div class="form-group">
                            <label>Department</label>
                            <input type="text" class="form-control" name="department" id="department"
                                value="{{$user->department?->name ?? 'NA'}}"
                                disabled>
                        </div>
                    </div>
                    <div class="col-lg-4">
                        <div class="form-group">
                            <label>Designation</label>
                            <input type="text" class="form-control" name="designation" id="designation"
                                value="{{$user->designation->name}}"
                                disabled>
                        </div>
                    </div>
                    <div class="col-lg-4">
                        <div class="form-group">
                            <label>Address</label>
                            <input type="text" class="form-control" name="address" id="address"
                                value="{{$user->profile->address}}"
                                disabled>
                        </div>
                    </div>
                </div>
                <div class="row">
                    <h6>{{__trans('2.service_information')}}</h6>
                    <div class="col-lg-4">
                        <div class="form-group">
                            <label>Hire Date</label>
                            <input type="text" class="form-control" name="hire_date" id="hire_date"
                                value="{{$user->workDetail?->joining_date ? $user->workDetail?->joining_date->format('d/m/Y') : ''}}"
                                disabled>
                        </div>
                    </div>
                    <div class="col-lg-4">
                        <div class="form-group">
                            <label>Departure Date</label>
                            <input type="date" class="form-control datepickers flatpickr-input" value="{{$settlement && $settlement->departure_date ? $settlement->departure_date : ($offboard && $offboard->departure_date ? \Carbon\Carbon::parse($offboard->departure_date)->format('Y-m-d') : '') }}" disabled id="departure_date" name="departure_date">
                            <!-- Error Message -->
                            <div id="error_message1" style="color: red;"></div>
                        </div>
                    </div>
                    <div class="col-lg-4">
                        <div class="form-group">
                            <label>Reason for departure</label>
                            <select name="reason" id="reason" class="form-control select-search"
                                id="reason" disabled>
                                <option value="">Select Reason</option>
                                @php
                                    $currentReasonId = $settlement?->departure_reason_id ?? $offboard?->departure_reason_id;
                                @endphp
                                @foreach ($reasons as $reason)
                                <option value="{{$reason->id}}" @if($currentReasonId == $reason->id) selected @endif>{{$reason->name}}</option>
                                @endforeach
                            </select>
                            <div id="error_message3" style="color: red;"></div>
                        </div>
                    </div>
                    <div class="col-lg-4">
                        <div class="form-group">
                            <label>Contract Type</label>
                            <input type="text" class="form-control" name="contract_type" id="contract_type"
                                value="{{$settlement ? $settlement->contract_type : ''}}"
                                disabled>
                            <div id="error_message2" style="color: red;"></div>
                        </div>
                    </div>
                    <div class="col-lg-4">
                        <div class="form-group">
                            <label>Basic Salary for departure month</label>
                            <input type="text" class="form-control" id="basic_salary" name="basic_salary" value="{{ $user->salary ? $user->salary?->basic : '' }}" disabled>
                        </div>
                    </div>
                    <div class="col-lg-4">
                        <div class="form-group">
                            <label>Gross Salary per month</label>
                            <input type="text" class="form-control" id="gross_salary" name="gross_salary" value="{{ $gross_value }}" disabled>
                        </div>
                    </div>
                    <div class="col-lg-4">
                        <div class="form-group">
                            <label>Total Service Duration</label>
                            <input type="text" class="form-control" name="total_service_duration" id="total_service_duration" value="NA" disabled>
                        </div>
                    </div>
                    <div class="col-lg-3">
                        <div class="form-group">
                            <label>Add absent days</label>
                            <input type="text" class="form-control" name="absent_days" value="{{ $offboard?->absent_days ?? $absent_count }}" id="absent_days" >
                        </div>
                    </div>
                    <div class="col-lg-12" style="text-align:center">
                        <div class="form-group">
                            <button  id="calculateBtn" class="btn btn-info waves-effect waves-light text-white customhidingInPDF">
                            Calculate <i class="fa fa-handshake" aria-hidden="true"></i> 
                            </button>
                        </div>
                    </div>
                </div>

                <div class="row" style="display:none;" id="finalSettlement">
                    <h6>3.Final Settlement Data</h6>
                    <div class="tableOuter">
                        <table class="table table-bordered"  id="leaveTable">
                            <thead>
                                <tr>
                                    <th>Additions</th>
                                    <th>Remarks</th>
                                    <th>Amount</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td><b>Gratuity</b></td>
                                    <td>5 years on 21 days</td>
                                    <td id="first_gratuity"></td>
                                </tr>
                                <tr>
                                    <td></td>
                                    <td>after 5 years on 30 days</td>
                                    <td id="second_gratuity"></td>
                                </tr>
                                <tr>
                                    <td><b>Leave encashments</b></td>
                                    <td></td>
                                    <td></td>
                                </tr>
                                <tr>
                                    <td><b>Addition List</b></td>
                                    <td></td>
                                    <td></td>
                                </tr>
                            <hr>
                                <tr>
                                    <td><b>Deduction List</b></td>
                                    <td></td>
                                    <td></td>
                                </tr>
                                {{--  <tr>
                                    <td><b>Selected month for salary </b></td>
                                    <td></td>
                                    <td></td>
                                </tr>  --}}
                                <tr id="salaryRow">
                                    <td><b>Salary</b></td>
                                    <td></td>
                                    <td></td>
                                </tr>
                                <tr>
                                    <td></td>
                                    <td>
                                        <h5>Total Amount</h5>
                                    </td>
                                    <td>
                                        <h5 id="total_amount"></h5>
                                    </td>
                                </tr>
                        </table>
                        <form id="myForm" class="customhidingInPDF">
                            <div class="d-flex justify-content-center mt-3">
                                <a href="#" class="btn btn-sm btn-primary" data-bs-toggle="tooltip" data-bs-placement="bottom" title="Download" onclick="SettlementsaveAsPDF()">Download <span class="fa fa-download"></span></a>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@push('scripts')
<script src="{{asset('assets/backend/js/gklveshel.js')}}"></script>
<script>
function SettlementsaveAsPDF() {
        // Guard: prevent double-trigger while PDF is being generated
        if (window._pdfGenerating) return;
        window._pdfGenerating = true;

        var element = document.getElementById('professionalSettlementLayout');

        // 1. Length of service (straight from the hidden input)
        $('#pdf_service_duration').text($('#total_service_duration').val() || 'N/A');

        // 2. Current month settlement salary
        var settlementSalary = parseFloat($('.settlementSalary-sum').val()) || 0;
        $('#pdf_val_basic').text(settlementSalary.toFixed(2));
        $('#pdf_narration_salary').text('BASIC PAY For ' + (settlementSalary > 0 ? 'Settlement Period' : '—'));

        // 3. Gratuity — parse actual service days from '#total_service_duration'
        //    Format example: "4 Years 0 Months 3 Days (Abs: ...) (Total 1458 Days)"
        var serviceDurText = $('#total_service_duration').val() || '';
        var daysMatch      = serviceDurText.match(/Total\s+([\d.]+)\s+Days/i)
                            || serviceDurText.match(/([\d.]+)\s+Days/i);
        var serviceDays    = daysMatch ? parseFloat(daysMatch[1]) : 0;

        var firstGratuity  = parseFloat($('#first_gratuity').text()) || 0;
        var secondGratuity = parseFloat($('#second_gratuity').text()) || 0;
        var totalGratuity  = firstGratuity + secondGratuity;
        $('#pdf_val_gratuity').text(totalGratuity.toFixed(2));
        // Use real service days in narration instead of amount
        $('#pdf_narration_gratuity').text('GRATUITY ' + serviceDays.toFixed(2) + ' Days (Adv 0.00)');

        // 4. Remove ALL previously injected dynamic rows (tagged with pdf-dyn-row class)
        //    This prevents duplicate rows on multiple Download clicks
        $('#pdf_payment_body').find('tr.pdf-dyn-row').remove();

        // 5. Leave encashment — sum all leave-sum inputs
        var totalLeaveEncashment = 0;
        $('.leave-sum').each(function() {
            totalLeaveEncashment += parseFloat($(this).val()) || 0;
        });
        $('#pdf_val_leave').text(totalLeaveEncashment.toFixed(2));

        // Fix the leave narration: read annual balance days from the element text
        // Bug fix: .match() returns Array — use ?.[0] to get the first match safely
        var leaveNarText    = $('#pdf_narration_leave').text() || '';
        var leaveMatchArr   = leaveNarText.match(/[\d.]+/);
        var leaveBalanceDays = leaveMatchArr?.[0] ? parseFloat(leaveMatchArr[0]) : totalLeaveEncashment;
        $('#pdf_narration_leave').text('LEAVE SALARY For ' + leaveBalanceDays.toFixed(2) + ' Days');

        // 6. Inject additions & deductions — each tagged with 'pdf-dyn-row' class for cleanup
        $('#leaveTable tbody tr').each(function() {
            var row = $(this);
            if (row.find('.addition-sum').length > 0) {
                var label  = row.find('td:nth-child(2)').text().trim() || 'Addition';
                var amount = parseFloat(row.find('.addition-sum').val()) || 0;
                $('#pdf_row_gratuity').before(
                    `<tr class="pdf-dyn-row">
                        <td style="padding:2px 5px; border:1px solid #ddd;">ADDITION</td>
                        <td style="padding:2px 5px; border:1px solid #ddd;">Extra Addition</td>
                        <td style="padding:2px 5px; border:1px solid #ddd;">${label}</td>
                        <td style="padding:2px 5px; border:1px solid #ddd; text-align:right;">${amount.toFixed(2)}</td>
                    </tr>`
                );
            } else if (row.find('.deduction-sum').length > 0) {
                var label  = row.find('td:nth-child(2)').text().trim() || 'Deduction';
                var amount = parseFloat(row.find('.deduction-sum').val()) || 0;
                $('#pdf_row_gratuity').before(
                    `<tr class="pdf-dyn-row">
                        <td style="padding:2px 5px; border:1px solid #ddd;">DEDUCTION</td>
                        <td style="padding:2px 5px; border:1px solid #ddd;">Deduction</td>
                        <td style="padding:2px 5px; border:1px solid #ddd;">${label}</td>
                        <td style="padding:2px 5px; border:1px solid #ddd; text-align:right;">-${amount.toFixed(2)}</td>
                    </tr>`
                );
            }
        });

        // 7. Sync total + grand total from existing #total_amount element
        var totalAmount = $('#total_amount').text().trim() || '0.00';
        $('#pdf_val_total').text(totalAmount);
        $('#pdf_val_grand_total').text(totalAmount);

        // 8. Generate PDF — useCORS only (do NOT combine with allowTaint, they conflict)
        element.style.display = 'block';
        var opt = {
            margin: 0,
            filename: 'Final_Settlement_{{ $user->employee_id }}.pdf',
            image: { type: 'jpeg', quality: 0.98 },
            html2canvas: { scale: 3, useCORS: true, letterRendering: true, logging: false },
            jsPDF: { unit: 'in', format: 'a4', orientation: 'portrait' }
        };
        html2pdf().set(opt).from(element).save().then(function() {
            element.style.display = 'none';
            window._pdfGenerating = false; // Release guard after PDF is saved
        });
    }
</script> 
@endpush
