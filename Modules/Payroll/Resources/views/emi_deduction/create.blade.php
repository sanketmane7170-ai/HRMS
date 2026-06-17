<div class="modal-dialog modal-lg" style="max-width: 523px !important;">
    <div class="modal-content">
        <div class="modal-header">
            <h4 class="modal-title">@if(request()->getHttpHost()=="cakesocial.momdigital.io"){{__trans('add_employee_addition')}} @else{{__trans('add_employee_deduction')}} @endif  : {{$user->name}}</h4>
            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <form action="{{route('backend.payroll.storeEMIDeduction', [$user,$monthyear])}}" datatable="true" method="POST" class="ajax-form-submit reset">
            @csrf
            <div class="modal-body p-4">
                <div class="row">
                    <div class="col-md-12">
                        <div class="mb-3">
                            <label for="title" class="form-label">{{__trans('Select_deduction')}}</label>
                            <select name="deduction" class="form-control select-search" id="deduction">
                                <option value="" selected>Select deduction</option>
                                @foreach ($fixedDeduction as $item)
                                    <option value="{{ $item->id }}">{{ $item->name }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div class="col-md-12">
                        <div class="mb-3">
                            <label for="amount" class="form-label">{{__trans('total_deduction_amount')}}</label>
                            <input type="text" step="0.01" name="total_amount" class="form-control" id="total_amount" placeholder="{{__trans('total_amount')}}">
                        </div>
                    </div>
                    <div class="col-md-12">
                        <div class="mb-3">
                            <label for="remark" class="form-label">{{__trans('remark')}}</label>
                            <textarea name="remark" class="form-control" id="remark" placeholder="{{__trans('remark')}}"></textarea>
                        </div>
                    </div>
                    <div class="col-md-12">
                        <div id="monthAmountWrapper">
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
                                    <input type="text" step="0.01" name="month_amount[]" class="form-control"
                                        placeholder="{{__trans('amount')}}">
                                </div>
                            </div>
                        </div>
                        <div class="col-md-2 d-flex align-items-center">
                            <button type="button" class="btn btn-success addRow"style="position: relative;top: 15%;">
                                <i class="bi bi-plus"></i> +
                            </button>
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
                <button type="button" class="btn btn-info waves-effect waves-light save-emi-deduction">{{__trans('save')}} </button>
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
    $(document).ready(function () {
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
                <input type="number" step="0.01" name="month_amount[]" class="form-control"
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
</script>
<script>
    $(document).off('click', '.save-emi-deduction').on('click', '.save-emi-deduction', function (e) {

        e.preventDefault();

        let form = $('.ajax-form-submit');
        let totalAmount = parseFloat($('#total_amount').val()) || 0;
        let sum = 0;
        let hasError = false;
        let monthhasError = false;

        $('input[name="month_amount[]"]').each(function () {
            let val = parseFloat($(this).val());
            if (isNaN(val)) {
                hasError = true;
                return false;
            }
            sum += val;
        });

        if (hasError) {
            alert('Please enter amount for all months');
            return;
        }

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

        if (sum.toFixed(2) !== totalAmount.toFixed(2)) {
            alert(
                'Total EMI amount mismatch!\n\n' +
                'Total Deduction: ' + totalAmount + '\n' +
                'Monthly Total: ' + sum
            );
            return;
        }

        form.submit();
    });
</script>

