<div class="modal-dialog modal-lg" style="max-width: 523px !important;">
    <div class="modal-content">
        <div class="modal-header">
            <h4 class="modal-title">{{__trans('add_deduction')}} : {{$user->name}}</h4>
            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <form action="{{route('backend.payroll.user.user-salaries.storededuction',$user)}}" datatable="true" method="POST" class="ajax-form-submit reset">
            @csrf
            <div class="modal-body p-4">
                <div class="row">
                    <div class="col-md-12">
                        <div class="mb-3">
                            <label for="title" class="form-label">{{__trans('title')}}</label>
                            <select name="title" class="form-control select-search" id="title">
                                <option value="" selected>Select deduction</option>
                                @foreach ($fixedDeduction as $item)
                                    <option value="{{ $item->name }}">{{ $item->name }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div class="col-md-12">
                        <div class="mb-3">
                            <label for="deduction_option" class="form-label">{{__trans('Type')}}</label>
                            <select name="deduction_type" class="form-control select-search" id="deduction_type">
                                <option value="" selected>Select Type</option>
                                @php
                                    $types = [
                                        'fixed' => 'Fixed',
                                        'percentage' => 'Percentage'
                                    ];
                                @endphp
                                @foreach($types as $key => $type)
                                    <option value="{{ $key }}">{{ $type }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div class="col-md-12">
                        <div class="mb-3">
                            <label for="amount" class="form-label">{{__trans('amount')}}</label>
                            <input type="number" step="0.01"  name="amount" class="form-control" id="amount" placeholder="{{__trans('amount')}}">
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label for="is_recurring">
                            Assign For Months <input type="checkbox" class="form-check-input" name="is_fixed_for_current_month" id="is_fixed_for_current_month" onchange="toggleMonthInput()">  
                            </label>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div id="monthInputContainer" style="display:none;">
                            <select class="form-select" name="monthCount" id="monthCount">
                            </select>
                        </div>
                    </div>
                    <div class="col-md-12">
                        <div class="mb-3">
                            <p id="applicableMonthsNote">
                                <span style="color: black; font-weight: bold;">Note:</span> 
                                <span style="color: red;">If Checkbox not checked then deduction will be consider for every month.</span>
                            </p>
                        </div>
                    </div>
                    <div class="col-md-12">
                        <div class="mb-3">
                            <label for="remark" class="form-label">{{__trans('remark')}}</label>
                            <textarea name="remark" class="form-control" id="remark" placeholder="{{__trans('remark')}}"></textarea>
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
                <button type="submit" class="btn btn-info waves-effect waves-light">{{__trans('save')}} </button>
            </div>
        </form>
    </div>
</div>

<script>
    loadAjaxSelect2();
    initselect2search();
</script>
<script>
    function setDefaultMonth() {
        var monthSelect = document.getElementById("monthCount");
        var currentMonth = new Date().getMonth() + 1;
        monthSelect.innerHTML = "";
        for (var i = currentMonth; i <= 12; i++) {
            var option = document.createElement("option");
            option.value = i;
            option.text = monthName(i);
            monthSelect.add(option);
        }
    }

    function monthName(monthNumber) {
        var monthNames = ["Jan", "Feb", "Mar", "Apr", "May", "Jun", "Jul", "Aug", "Sep", "Oct", "Nov", "Dec"];
        return monthNames[monthNumber - 1];
    }

    function toggleMonthInput() {
        var monthInputContainer = document.getElementById("monthInputContainer");
        var checkbox = document.getElementById("is_fixed_for_current_month");

        if (checkbox.checked) {
            monthInputContainer.style.display = "block";
            setDefaultMonth();
        } else {
            monthInputContainer.style.display = "none";
        }
    }

    setDefaultMonth();
</script>