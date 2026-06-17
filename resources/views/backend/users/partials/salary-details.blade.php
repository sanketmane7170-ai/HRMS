<div class="col-xl-12 col-12">
    <div class="card bg-white">
        <div class="card-header">
            <div class="row">
                <div class="col">
                    <h5>{{__trans('salary_&_Fixed_allowances_deductions')}}</h5>
                </div>
                <div class="col-auto">
                </div>
            </div>
        </div>
        <div class="card-body light" style="padding-bottom: 10px !important;">
            <div class="project-info d-flex text-sm">
                <div class="project-info-inner mr-3 col-4">
                    <b class="m-0"> Payslip Type </b>
                    <div class="project-amnt pt-1">Monthly Payslip</div>
                </div>
                <div class="project-info-inner mr-3 col-4">
                    <b class="m-0">
                        @if (getSetting('payroll_calculation') == 'hourly')
                        Basic Hourly Rate
                        @else
                        Basic Salary
                        @endif
                    </b>
                    <div class="project-amnt pt-1">@if(isset($user->salary->basic))
                        {{ $user->salary->basic }} @endif
                    </div>
                </div>
                <div class="project-info-inner mr-3 col-4">
                    <b class="m-0">
                        Gross Salary
                    </b>
                    <div class="project-amnt pt-1">
                        @if(isset($salary['gross']))
                            {{ $salary['gross'] }}
                        @endif
                    </div>
                </div>
            </div>
            @php
            $fixed_allowance = isset($user->salary->fixed_allowances) ?
            json_decode($user->salary->fixed_allowances, true) : 0;
            $fixed_deduction = isset($user->salary->fixed_deductions) ?
            json_decode($user->salary->fixed_deductions, true) : 0;

            if (is_array($fixed_allowance)) {
            foreach ($fixed_allowance as $key => $value) {
            $fixed_allowance[$key] = $value ?? 0;
            }
            }

            if (is_array($fixed_deduction)) {
            foreach ($fixed_deduction as $key => $value) {
            $fixed_deduction[$key] = $value ?? 0;
            }
            }
            @endphp

            <div class="col-auto">
                @if(is_array($fixed_allowance))
                <div class="project-info d-flex text-sm" style="padding-top: 35px !important;">
                    <u><b>{{ __trans('allowances') }}</b></u>
                </div>

                <!-- Start Allowance Section -->
                <div class="project-info text-sm">
                    <div class="row">
                        <div class="form-group col-4">
                            <b class="m-0">{{ __trans('housing_allowance') }}</b>
                            <div class="project-amnt pt-1">
                                {{ $fixed_allowance['housing_allowance'] }}
                            </div>
                        </div>
                        <div class="form-group col-4">
                            <b class="m-0">{{ __trans('transportation_allowance') }}</b>
                            <div class="project-amnt pt-1">
                                {{ $fixed_allowance['transportation_allowance'] }}
                            </div>
                        </div>
                        <div class="form-group col-4">
                            <b class="m-0">{{ __trans('functional_allowance') }}</b>
                            <div class="project-amnt pt-1">
                                {{ !empty($fixed_allowance['functional_allowance']) ? $fixed_allowance['functional_allowance'] : '0' }}
                            </div>
                        </div>
                        <div class="form-group col-4">
                            <b class="m-0">{{ __trans('other_allowance') }}</b>
                            <div class="project-amnt pt-1">
                                {{ $fixed_allowance['other_allowance'] }}
                            </div>
                        </div>
                    </div>
                    <div class="row mt-3">
                        <div class="form-group col-4">
                            <b class="m-0">{{ __trans('tips') }}</b>
                            <div class="project-amnt pt-1">
                                {{ $fixed_allowance['tips'] }}
                            </div>
                        </div>
                        {{-- @foreach ($allowance as $alldeduvalue)
                        @if ($alldeduvalue->type == 1)
                        @php
                        $allvalue = isset($fixed_allowance[$alldeduvalue->name]) ?
                        $fixed_allowance[$alldeduvalue->name] :
                        $alldeduvalue->amount;
                        @endphp
                        <div class="form-group col-4">
                            <b class="m-0">{{ __trans($alldeduvalue->name) }}</b>
                            <div class="project-amnt pt-1">{{ $allvalue }}</div>
                        </div>
                        @endif
                        @endforeach --}}
                    </div>
                </div>
                @endif

                @if(is_array($fixed_deduction))
                <!-- Start Deduction Section -->
                <div class="project-info text-sm mt-4">
                    <u><b>{{ __trans('deductions') }}</b></u>
                    <div class="row">
                        <div class="form-group col-4">
                            <b class="m-0">{{ __trans('advance_salary') }}</b>
                            <div class="project-amnt pt-1">{{ $fixed_deduction['advance_salary'] }}</div>
                        </div>
                        <div class="form-group col-4">
                            <b class="m-0">{{ __trans('loan_deduction') }}</b>
                            <div class="project-amnt pt-1">{{ $fixed_deduction['loan_deduction'] }}</div>
                        </div>
                        <div class="form-group col-4">
                            <b class="m-0">{{ __trans('other_deduction') }}</b>
                            <div class="project-amnt pt-1">{{ $fixed_deduction['other_deduction'] }}</div>
                        </div>
                    </div>
                    {{-- <div class="row mt-3">
                        @foreach ($allowance as $alldeduvalue)
                        @if ($alldeduvalue->type == 2)
                        @php
                        $deduvalue = isset($fixed_deduction[$alldeduvalue->name]) ?
                        $fixed_deduction[$alldeduvalue->name] :
                        $alldeduvalue->amount;
                        @endphp
                        <div class="form-group col-4">
                            <b class="m-0">{{ __trans($alldeduvalue->name) }}</b>
                            <div class="project-amnt pt-1">{{ $deduvalue }}</div>
                        </div>
                        @endif
                        @endforeach
                    </div> --}}
                    <div class="project-info-inner mr-3 col-4">
                        <b class="m-0"> @if (getSetting('payroll_calculation') == 'hourly')
                            Total Working Hour
                            @else
                            Total Working Days
                            @endif
    
                        </b>
                        <div class="project-amnt pt-1"> @if(isset($user->salary->total_working_days))
                            {{ $user->salary->total_working_days }} @endif
                        </div>
                    </div>
                </div>
                @endif
            </div>

        </div>

    </div>
</div>
