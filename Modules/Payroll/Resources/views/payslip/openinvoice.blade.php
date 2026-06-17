@php
$roundoff=getSetting('roundoff') ? getSetting('roundoff') : 0;
@endphp
<div class="modal-dialog modal-lg">
    <div class="modal-content" style="border: var(--bs-modal-border-width) solid var(--bs-modal-border-color);
    border-radius: var(--bs-modal-border-radius);">
        <div class="modal-header">
            <h4 class="modal-title">{{__trans('employee_payslip')}}</h4>
            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <form action="{{route('backend.payroll.user.user-salaries.storeovertime',$user)}}" datatable="true" method="POST" class="ajax-form-submit reset">
            @csrf
            <div class="modal-body">
                <div class="row">
                    <div class="text-md-end mb-2">
                        <a href="#" class="btn btn-sm btn-primary" data-bs-toggle="tooltip" data-bs-placement="bottom" title="Download" onclick="saveAsPDF()"><span class="fa fa-download"></span></a>
                        <!-- <a title="Mail Send" href="#" class="btn btn-sm btn-warning"><span class="fa fa-paper-plane"></span></a> -->
                    </div>
                    <div class="invoice" id="printableArea">
                        <div class="col-form-label">
                            <div class="invoice-number">
                                <img src="{{getLogo()}}" width="115px;">
                                <div class="invoice-info">
                                    <p><span>PaySlip Date : </span>{{ $payslip_date }}</p>
                                </div>
                            </div>
                            <div class="invoice-print">
                                <div class="row">
                                    <div class="col-lg-12">
                                        <div class="invoice-title">
                                        </div>
                                        <hr>
                                        <div class="row text-sm" style="margin:0px;">
                                            <div class="col-md-6">
                                                <address>
                                                    <strong>Name :</strong> {{ $user->name }}<br>
                                                    <strong>Designation :</strong> {{ $user->designation->name }}<br>
                                                </address>
                                            </div>
                                            <div class="col-md-6 text-end">
                                                <address>
                                                    <strong>{{ $setting[0]['value'] }} </strong><br>
                                                    {{ $setting[1]['value'] }} <br>
                                                </address>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="row mt-2">
                                    <div class="col-md-12">
                                        <div class="card dark" style="border-color: gray;">
                                            <div class="card-body p-0">
                                                <div class="table-responsive">
                                                    <table class="table light table-hover table-md text-left">
                                                        <tbody>
                                                            <tr class="font-weight-bold light">
                                                                <th>Earning</th>
                                                                <th width="150" class="text-right">Amount</th>
                                                            </tr>
                                                            <tr class="light">
                                                                <td>
                                                                    @if (getSetting('payroll_calculation') == 'hourly')
                                                                    Basic Hourly Rate
                                                                    @else
                                                                    Basic Salary
                                                                    @endif
                                                                </td>
                                                                @php
                                                                    if(str_contains(getSetting('currency'), 'AED')){
                                                                         $AEDCurrency = '<img src="'. asset("assets/currency/aedb.png") .'" alt="AED" style="width:15px; height:15px; vertical-align:middle;">';
                                                                    } else {
                                                                        $AEDCurrency = getSetting('currency');
                                                                    }
                                                                @endphp
                                                                <td class="text-right">{{ $user->salary->basic }}-{!! $AEDCurrency !!}</td>
                                                            </tr>
                                                        </tbody>
                                                    </table>
                                                </div>
                                            </div>
                                        </div>
                                        @php
                                        $allowances = Modules\Payroll\Entities\SetAllowanceDeducation::get();
                                        @endphp
                                        {{-- @if(!empty($all_fixed_entity['housing_allowance']) || !empty($all_fixed_entity['transportation_allowance']) 
                                            || !empty($all_fixed_entity['other_allowance']) || !empty($all_fixed_entity['tips']))  --}}
                                        <div class="card dark" style="border-color: gray;";>
                                            <div class="card-body p-0">
                                                <div class="table-responsive">
                                                    <table class="table light  table-hover table-md text-left">
                                                        <tbody>
                                                            <tr class="font-weight-bold light">
                                                                <th>Fixed Allowance</th>
                                                                <th width="150">Value</th>
                                                            </tr>
                                                            @if (!empty($all_fixed_entity['housing_allowance']))
                                                            <tr class="light">
                                                                <td>Housing Allowance</td>
                                                                <td class="text-right">{{ $all_fixed_entity['housing_allowance'] }}</td>
                                                            </tr>
                                                            @endif
                                                            @if (!empty($all_fixed_entity['transportation_allowance']))
                                                            <tr class="light">
                                                                <td>Transportation Allowance</td>
                                                                <td class="text-right">{{ $all_fixed_entity['transportation_allowance'] }}</td>
                                                            </tr>
                                                            @endif
                                                            {{-- @foreach ($allowances as $allowance)
                                                            @php
                                                            $allowanceName = $allowance->name;
                                                            @endphp
                                                            @if($allowance->type==1)
                                                            @if (array_key_exists($allowanceName, $all_fixed_entity))
                                                            <tr class="light">
                                                                <td>{{ $allowanceName }}</td>
                                                                <td class="text-right">{{ $all_fixed_entity[$allowanceName] }}</td>
                                                            </tr>
                                                            @endif
                                                            @endif
                                                            @endforeach --}}
                                                            @if (!empty($all_fixed_entity['functional_allowance']))
                                                            <tr class="light">
                                                                <td>Functional Allowance</td>
                                                                <td class="text-right">{{ $all_fixed_entity['functional_allowance'] }}</td>
                                                            </tr>
                                                            @endif
                                                            @if (!empty($all_fixed_entity['other_allowance']))
                                                            <tr class="light">
                                                                <td>Other Allowance</td>
                                                                <td class="text-right">{{ $all_fixed_entity['other_allowance'] }}</td>
                                                            </tr>
                                                            @endif
                                                            @if (!empty($all_fixed_entity['tips']))
                                                            <tr class="light">
                                                                <td>Tips</td>
                                                                <td class="text-right">{{ $all_fixed_entity['tips'] }}</td>
                                                            </tr>
                                                            @endif
                                                        </tbody>
                                                    </table>
                                                </div>
                                            </div>
                                        </div>
                                        {{-- @endif  --}}
                                        @foreach($user_salary as $result)
                                            @php
                                                // all fix allowance
                                                $finalAllowances = [];
                                                $emiData = App\Models\EMIAllowanceData::where('month', $payslip->month_code)
                                                            ->where('year', $payslip->year)
                                                            ->where('is_paid', 0)
                                                            ->whereHas('emiAllowance', function ($q) use ($user) {
                                                                $q->where('user_id', $user->id)
                                                                ->where('fully_paid', 0);
                                                            })
                                                            ->with('emiAllowance')
                                                            ->get();
                                                foreach ($result['all_allowance'] as $allowance) {
                                                    $finalAllowances[$allowance->title] = [
                                                        'title' => $allowance->title,
                                                        'type'  => $allowance->allowance_type,
                                                        'amount'=> $allowance->amount,
                                                        'percentage_amount'=> $allowance->percentage_amount??0,
                                                    ];
                                                }
                                                foreach ($emiData as $emi) {
                                                    $title = $emi->emiAllowance->title;
                                                    if (isset($finalAllowances[$title])) {
                                                        // If title already exists → ADD amount
                                                        $finalAllowances[$title]['amount'] += $emi->month_amount;
                                                    } else {
                                                        // If title does not exist → Add new row
                                                        $finalAllowances[$title] = [
                                                            'title'  => $title,
                                                            'type'   => 'fixed',
                                                            'amount' => $emi->month_amount,
                                                            'percentage_amount' => 0,
                                                        ];
                                                    }
                                                }
                                                // end
                                            @endphp
                                            @if(count($finalAllowances))
                                            <div class="card dark">
                                                <div class="card-body p-0">
                                                    <div class="table-responsive">
                                                        <table class="table  table-hover table-md text-left">
                                                            <tbody>
                                                                <tr class="font-weight-bold light">
                                                                    <th>@if(request()->getHttpHost()=="cakesocial.momdigital.io") Variable Addition @else Variable Allowance @endif</th>
                                                                    <th width="150" >Type (Fixed/Percentage)</th>
                                                                    <th  class="text-right">Amount</th>
                                                                </tr>
                                                                @foreach($finalAllowances as $allowance)
                                                                <tr class="light">
                                                                    <td>{{ $allowance['title'] }}</td>
                                                                    <td>{{ __trans($allowance['type']) }}</td>
                                                                    <!-- <td class="text-right">{{ $allowance['amount'] }}</td> -->
                                                                     @if($allowance['type'] == 'fixed')
                                                                    <td class="text-right">{{  $allowance['amount']  }}</td>
                                                                    @else
                                                                    <td class="text-right">{{  $allowance['percentage_amount'] }} </td>
                                                                    @endif
                                                                </tr>
                                                                @endforeach
                                                                {{--  @foreach($result['all_allowance'] as $allowance)
                                                                <tr class="light">
                                                                    <td>{{ $allowance->title }}</td>
                                                                    @if($allowance->allowance_type == 'fixed')
                                                                    <td>{{ __trans($allowance->allowance_type) }}</td>
                                                                    @else
                                                                    <td>{{ __trans($allowance->allowance_type) }}</td>
                                                                    @endif
                                                                    @if($allowance->allowance_type == 'fixed')
                                                                    <td class="text-right">{{ $totalAmount }}</td>
                                                                    @else
                                                                    <td class="text-right">{{ $allowance->percentage_amount}} </td>
                                                                    @endif
                                                                </tr>
                                                                @endforeach  --}}
                                                            </tbody>
                                                        </table>
                                                    </div>
                                                </div>
                                            </div>
                                            @endif
                                            @if($totalAirTicketAllowance)
                                            <div class="card dark">
                                                <div class="card-body p-0">
                                                    <div class="table-responsive">
                                                        <table class="table  table-hover table-md text-left">
                                                            <tbody>
                                                                <tr class="font-weight-bold light">
                                                                    <th>Air Ticket Allowance</th>
                                                                    <th width="150" class="text-right">Amount</th>
                                                                </tr>
                                                                <tr class="light">
                                                                    <td>User Air Ticket Allowance</td>
                                                                    <td class="text-right">{{ $totalAirTicketAllowance }}</td>
                                                                </tr>
                                                            </tbody>
                                                        </table>
                                                    </div>
                                                </div>
                                            </div>
                                            @endif
                                            <div class="card dark">
                                                <div class="card-body p-0">
                                                    <div class="table-responsive">
                                                        <table class="table  table-hover table-md text-left">
                                                            <tbody>
                                                                <tr class="font-weight-bold light">
                                                                    <th>Advance Salary/Loan Amount</th>
                                                                    <th width="150" class="text-right">Approved Amount</th>
                                                                </tr>
                                                                <tr class="light">
                                                                    <td>Advance Salary/Loan Amount</td>
                                                                    <td class="text-right">{{ $approvedAdvanceLoanAmount }}</td>
                                                                </tr>
                                                            </tbody>
                                                        </table>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="card dark">
                                                <div class="card-body p-0">
                                                    <div class="table-responsive">
                                                        <table class="table  table-hover table-md text-left">
                                                            <tbody>
                                                                <tr class="font-weight-bold light">
                                                                    <th>Fixed Deduction</th>
                                                                    <th width="150">Value</th>
                                                                </tr>
                                                                @if (!empty($all_fixed_entity['advance_salary']))
                                                                <tr class="light">
                                                                    <td>Advance Salary</td>
                                                                    <td class="text-right">{{ $all_fixed_entity['advance_salary'] }}</td>
                                                                </tr>
                                                                @endif
                                                                @if (!empty($all_fixed_entity['loan_deduction']))
                                                                <tr class="light">
                                                                    <td>Loan Deduction</td>
                                                                    <td class="text-right">{{ $all_fixed_entity['loan_deduction'] }}</td>
                                                                </tr>
                                                                @endif
                                                                @if (!empty($all_fixed_entity['other_deduction']))
                                                                <tr class="light">
                                                                    <td>Other Deduction</td>
                                                                    <td class="text-right">{{ $all_fixed_entity['other_deduction'] }}</td>
                                                                </tr>
                                                                @endif
                                                                {{-- @foreach ($allowances as $deduction)
                                                                @php
                                                                $deductionName = $deduction->name;
                                                                @endphp
                                                                @if($deduction->type==2)
                                                                @if (array_key_exists($deductionName, $all_fixed_entity))
                                                                <tr class="light">
                                                                    <td>{{ $deductionName }}</td>
                                                                    <td class="text-right">{{ $all_fixed_entity[$deductionName] }}</td>
                                                                </tr>
                                                                @endif
                                                                @endif
                                                                @endforeach --}}
                                                            </tbody>
                                                        </table>
                                                    </div>
                                                </div>
                                            </div>
                                            {{-- @endif  --}}
                                            @php
                                                // all fix deduction
                                                $finalDeduction = [];
                                                $emiDeduData = App\Models\EMIDeductionData::where('month', $payslip->month_code)
                                                            ->where('year', $payslip->year)
                                                            ->whereHas('emiDeduction', function ($q) use ($user) {
                                                                $q->where('user_id', $user->id)
                                                                ->where('fully_paid', 0);
                                                            })
                                                            ->with('emiDeduction')
                                                            ->get();
                                                foreach ($result['all_deduction'] as $deduction) {

                                                    $finalDeduction[$deduction->title] = [
                                                        'title' => $deduction->title,
                                                        'type'  => $deduction->deduction_type,
                                                        'amount'=> $deduction->amount,
                                                        'percentage_amount'=> $deduction->percentage_amount??0,
                                                    ];
                                                }
                                                foreach ($emiDeduData as $emi) {
                                                    $title = $emi->emiDeduction->title;
                                                    if (isset($finalDeduction[$title])) {
                                                        // If title already exists → ADD amount
                                                        $finalDeduction[$title]['amount'] += $emi->month_amount;
                                                    } else {
                                                        // If title does not exist → Add new row
                                                        $finalDeduction[$title] = [
                                                            'title'  => $title,
                                                            'type'   => 'fixed',
                                                            'amount' => $emi->month_amount,
                                                            'percentage_amount' => 0,
                                                        ];
                                                    }
                                                }
                                                // end
                                            @endphp
                                            @if(count($finalDeduction))
                                            <div class="card dark">
                                                <div class="card-body p-0">
                                                    <div class="table-responsive">
                                                        <table class="table  table-hover table-md text-left">
                                                            <tbody>
                                                                <tr class="font-weight-bold light">
                                                                    <th>Variable Deductions</th>
                                                                    <th>Type (Fixed/Percentage)</th>
                                                                    <th width="150" class="text-right">Amount</th>
                                                                </tr>
                                                                {{--  @foreach($result['all_deduction'] as $deduction)
                                                                <tr class="light">
                                                                    <td>{{ $deduction->title }}</td>
                                                                    @if($deduction->deduction_type == 'fixed')
                                                                    <td>{{ __trans($deduction->deduction_type) }}</td>
                                                                    @else
                                                                    <td>{{ __trans($deduction->deduction_type) }}</td>
                                                                    @endif
                                                                    @if($deduction->deduction_type == 'fixed')
                                                                    <td class="text-right">{{ $deduction->amount }}</td>
                                                                    @else
                                                                    <td class="text-right">{{ $deduction->percentage_amount}} </td>
                                                                    @endif
                                                                </tr>
                                                                @endforeach  --}}
                                                                @foreach($finalDeduction as $deduction)
                                                                <tr class="light">
                                                                    <td>{{ $deduction['title'] }}</td>
                                                                    <td>{{ __trans($deduction['type']) }}</td>
                                                                    <!-- <td class="text-right">{{ $deduction['amount'] }}</td> -->
                                                                     @if($deduction['type'] == 'fixed')
                                                                    <td class="text-right">{{ $deduction['amount']  }}</td>
                                                                    @else
                                                                    <td class="text-right">{{ $deduction['percentage_amount'] }} </td>
                                                                    @endif
                                                                </tr>
                                                                @endforeach
                                                            </tbody>
                                                        </table>
                                                    </div>
                                                </div>
                                            </div>
                                            @endif
                                            @if(count($result['all_overtime']))
                                            <div class="card dark">
                                                <div class="card-body p-0">
                                                    <div class="table-responsive">
                                                        <table class="table  table-hover table-md text-left">
                                                            <tbody>
                                                                <tr class="font-weight-bold light">
                                                                    <th>Overtime Title</th>
                                                                    <th>Rate Per Hour</th>
                                                                    <th>Overtime Hours</th>
                                                                    <th width="150" class="text-right">Amount</th>
                                                                </tr>
                                                                @foreach($result['all_overtime'] as $overtime)
                                                                <tr class="light">
                                                                    <td>{{ __trans($overtime->overtime_type) }}</td>
                                                                    <td>{{ $overtime->rate_per_hour }}</td>
                                                                    <td>{{ $overtime->hours }}</td>
                                                                    <td class="text-right">{{ $overtime->calculated_amount }}</td>
                                                                </tr>
                                                                @endforeach
                                                            </tbody>
                                                        </table>
                                                    </div>
                                                </div>
                                            </div>
                                            @endif
                                        @endforeach
                                        <div class="card dark" style="border-color: gray;">
                                            <div class="card-body p-0">
                                                <div class="table-responsive">
                                                    <table class="table light  table-hover table-md text-left">
                                                        <tbody>
                                                            <tr class="font-weight-bold light">
                                                                <th>Total Advance Salary/Loan</th>
                                                                <th width="150" class="text-right">Deduction Amount</th>
                                                            </tr>
                                                            <tr class="light">
                                                                <td>Advance Salary/Loan</td>
                                                                <td class="text-right">{{ $totaladAdvanceSalary }}</td>
                                                            </tr>
                                                        </tbody>
                                                    </table>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="card dark" style="border-color: gray;">
                                            <div class="card-body p-0">
                                                <div class="table-responsive">
                                                    <table class="table light  table-hover table-md text-left">
                                                        <tbody>

                                                            <tr class="font-weight-bold light">
                                                                <th>Total Last Month Expenses</th>
                                                                <th width="150">Amount</th>
                                                            </tr>

                                                            <tr class="light">
                                                                <td>Expense</td>
                                                                <td class="text-right">{{ $expense }}-{!! $AEDCurrency !!}</td>
                                                            </tr>

                                                        </tbody>
                                                    </table>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="card dark" style="border-color: gray;">
                                            <div class="card-body p-0">
                                                <div class="table-responsive">
                                                    <table class="table light  table-hover table-md text-left">
                                                        <tbody>
                                                            <tr class="font-weight-bold light">
                                                                <th>
                                                                    @if (getSetting('payroll_calculation') == 'hourly')
                                                                    Total Net Salary(Hourly)
                                                                    @else
                                                                    Total Net Salary(Attendance)
                                                                    @endif
                                                                </th>
                                                                <th width="150" class="text-right">Total Net Salary</th>
                                                            </tr>
                                                            <tr class="light">
                                                                <td><strong>{{ round(floatval($calculations['attendance_salary'] ), $roundoff)  }} -{!! $AEDCurrency !!}</strong></td>
                                                                <td class="text-right"><strong>{{ number_format(floatval($calculations['net_salary'] ), $roundoff) }} -{!! $AEDCurrency !!}</strong></td>
                                                            </tr>
                                                        </tbody>
                                                    </table>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <hr>
                            <div class="text-md-right pb-2 text-sm text-center" style="margin:0px;">
                                <div class="float-lg-left mb-lg-0 mb-3 ">
                                    <p class="mt-2"><b>This system-generated payslip does not require a signature</b></p>
                                </div>
                            </div>
                        </div>
                    </div>

                </div>
            </div>
        </form>
    </div>
</div>

<script>
    loadAjaxSelect2();
    initselect2search();
</script>
<script src="{{asset('assets/backend/js/gklveshel.js')}}"></script>
<script>
    function saveAsPDF() {
        var element = document.getElementById('printableArea');
        var opt = {
            margin: 0.3,
            filename: 'PaySlip',
            image: {
                type: 'jpeg',
                quality: 1
            },
            html2canvas: {
                scale: 4,
                dpi: 72,
                letterRendering: true
            },
            jsPDF: {
                unit: 'in',
                format: 'A4'
            }
        };
        html2pdf().set(opt).from(element).save();
    }

    function onChangeOvertime(data) {
        var type = '';
        type = data.options[data.selectedIndex].text;
        switch (type) {
            case 'OT1':
                $('#rate , #rateperhour').val('1.25');
                break;
            case 'OT2':
                $('#rate , #rateperhour').val('1.25');
                break;
            case 'OT3':
                $('#rate , #rateperhour').val('1.50');
                break;
            case 'OT4':
                $('#rate , #rateperhour').val('1.50');
                break;
            default:
                $('#rate , #rateperhour').val('0');
        }
    }
</script>