<div class="modal-dialog modal-lg" style="max-width: 523px !important;">
    <div class="modal-content">
        <div class="modal-header">
            <h4 class="modal-title">@if(request()->getHttpHost()=="cakesocial.momdigital.io"){{__trans('add_employee_addition')}} @else{{__trans('add_employee_allowance')}} @endif  : {{$user->name}}</h4>
            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <form action="{{route('backend.payroll.updateEMIAllowance', $emi_allowance->id)}}" datatable="true" method="POST" class="ajax-form-submit reset">
            @csrf
            <div class="modal-body p-4">
                <div class="row">
                    <div class="col-md-12">
                        <div class="mb-3">
                            <label for="title" class="form-label">{{__trans('Select_allowance')}}</label>
                            <select name="allowance" class="form-control select-search" id="allowance">
                                <option value="" selected>Select allowance</option>
                                @foreach ($fixedAllowance as $item)
                                    <option value="{{ $item->id }}" @if($item->id == $emi_allowance->allowance_id) selected @endif>{{ $item->name }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div class="col-md-12">
                        <div class="mb-3">
                            <label for="amount" class="form-label">{{__trans('total_allowance_amount')}}</label>
                            <input type="text" step="0.01" name="total_amount" value="{{ $emi_allowance->total_amount }}" class="form-control" id="total_amount" placeholder="{{__trans('total_amount')}}">
                        </div>
                    </div>
                    <div class="col-md-12">
                        <div class="mb-3">
                            <label for="remark" class="form-label">{{__trans('remark')}}</label>
                            <textarea name="remark" class="form-control" id="remark" placeholder="{{__trans('remark')}}">{{ $emi_allowance->remark }}</textarea>
                        </div>
                    </div>
                    <div class="col-md-12">
                        <div id="monthAmountWrapper">
                            @foreach ($emiAllowancedata as $item)
                                <input type="hidden" name="AllowanceId[]" value="{{ $item->id }}">
                                <div class="row month-amount-row mb-2">
                                    <div class="col-md-5">
                                        <label class="form-label">{{__trans('Month')}}</label>
                                        <select class="form-select month-select" name="month_year[]" @if($item->is_paid == 1) disabled @endif>
                                            <option value="">Select Month</option>
                                            @for ($i = 0; $i < 8; $i++)
                                                @php
                                                    $date = now()->subMonth()->addMonths($i);
                                                    $value = $date->format('m-Y');
                                                    $selectedValue = sprintf('%02d-%d', $item->month, $item->year);
                                                @endphp
                                                <option value="{{ $value }}"
                                                    @if($value === $selectedValue) selected @endif>
                                                    {{ $date->format('F Y') }}
                                                </option>
                                            @endfor
                                        </select>
                                        {{--  @if($item->is_paid==1)
                                            <input type="hidden" name="month_year[]" value="{{ sprintf('%02d-%d', $item->month, $item->year) }}">
                                        @endif  --}}
                                    </div>

                                    <div class="col-md-5">
                                        <label class="form-label">{{__trans('Amount')}}</label>
                                        <input type="text" step="0.01" @if($item->is_paid == 0) name="month_amount[]" @endif @if($item->is_paid==1) readonly @endif class="form-control month_amount" value="{{ $item->month_amount }}" placeholder="{{__trans('amount')}}">
                                    </div>
                                    
                                    @if(!$loop->first && $item->is_paid==0)
                                        <div class="col-md-2 d-flex align-items-center">
                                            <button type="button"
                                                class="btn btn-danger removeRow" style="position: relative;top: 20%;">−
                                            </button>
                                        </div>
                                    @else
                                        @if($item->is_paid==1)
                                            <div class="col-md-2 d-flex align-items-center">
                                                <button type="button" class="btn btn-success" disabled style="position: relative;top: 20%;">
                                                    <i class="bi bi-check-circle-fill"></i> Paid
                                                </button>
                                            </div>
                                        @endif
                                    @endif
                                </div>
                            @endforeach
                        </div>
                        <div class="col-md-2 d-flex align-items-center">
                            <button type="button" class="btn btn-success addRow">+</button>
                        </div>
                    </div>
                    
                </div>
            </div>
            <div class="modal-footer">
                @if(isset($monthyear))
                <input type="text" name="hidden_my" value="{{ $monthyear }}" hidden>
                @else
                <input type="text" name="hidden_my" value="NA" hidden>
                @endif
                <button type="button" class="btn btn-secondary waves-effect" data-bs-dismiss="modal">{{__trans('close')}}</button>
                <button type="button" class="btn btn-info waves-effect waves-light save-emi-allowance">{{__trans('update')}} </button>
            </div>
        </form>
    </div>
</div>

<script>
    loadAjaxSelect2();
    initselect2search();
    if (typeof window.originalMonthOptions === 'undefined') {
        window.originalMonthOptions = null;
    }

    $(document).on('focus', '.month-select', function () {
        if (!window.originalMonthOptions) {
            window.originalMonthOptions = $(this).clone().html();
        }
    });

    function updateMonthOptions() {
        let selectedMonths = [];

        // collect selected months (ignore empty & disabled)
        $('.month-select').each(function () {
            let val = $(this).val();
            if (val) selectedMonths.push(val);
        });

        $('.month-select').each(function () {
            let current = $(this).val();

            $(this).find('option').each(function () {
                let optionVal = $(this).val();

                if (!optionVal) return; // skip placeholder

                if (selectedMonths.includes(optionVal) && optionVal !== current) {
                    $(this).prop('disabled', true);
                } else {
                    $(this).prop('disabled', false);
                }
            });
        });
    }

    $(document).off('change', '.month-select').on('change', '.month-select', function () {
        updateMonthOptions();
    });

</script>
<script>
    $(document).off('click', '.addRow').on('click', '.addRow', function () {

        let row = `
        <div class="row month-amount-row mb-2">
            <div class="col-md-5">
                <label class="form-label">{{__trans('Month')}}</label>
                <select class="form-select month-select" name="month_year[]">
                    <option value="">Select Month</option>
                    @for ($i = 0; $i < 8; $i++)
                        @php
                            $date = now()->subMonth()->addMonths($i);
                        @endphp
                        <option value="{{ $date->format('m-Y') }}">
                            {{ $date->format('F Y') }}
                        </option>
                    @endfor
                </select>
            </div>

            <div class="col-md-5">
                <label class="form-label">{{__trans('Amount')}}</label>
                <input type="number" step="0.01" name="month_amount[]" class="form-control month_amount"
                    placeholder="{{__trans('amount')}}">
            </div>

            <div class="col-md-2 d-flex align-items-center">
                <button type="button" class="btn btn-danger removeRow" style="position: relative;top: 20%;">
                    <i class="bi bi-dash"></i> −
                </button>
            </div>
        </div>
        `;

        $('#monthAmountWrapper').append(row);
        updateMonthOptions();

    });

    $(document).off('click', '.removeRow').on('click', '.removeRow', function () {
        $(this).closest('.month-amount-row').remove();
        updateMonthOptions();
    });

    $(document).ready(function () {
        updateMonthOptions();
    });
</script>
<script>
    $(document).off('click', '.save-emi-allowance').on('click', '.save-emi-allowance', function (e) {

        e.preventDefault();

        let form = $('.ajax-form-submit');
        let totalAmount = parseFloat($('#total_amount').val()) || 0;
        let sum = 0;
        let hasError = false;
        let monthhasError = false;

        

        $('.month_amount').each(function () {
            let val = parseFloat($(this).val());

            if (isNaN(val)) {
                hasError = true;
                return false;
            }
            sum += val;
        });

        $('select[name="month_year[]"]').each(function () {
            let val = $(this).val();
            if (!val || val.trim() === '') {
                monthhasError = true;
                return false;
            }
        });

        if (monthhasError) {
            alert('Please select month for all rows');
            return;
        }

        if (hasError) {
            alert('Please enter amount for all months');
            return;
        }

        if (sum.toFixed(2) !== totalAmount.toFixed(2)) {
            alert(
                'Total EMI amount mismatch!\n\n' +
                'Total Allowance: ' + totalAmount + '\n' +
                'Monthly Total: ' + sum
            );
            return;
        }
        
        form.submit();
    });
</script>

