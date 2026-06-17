<div class="modal-dialog modal-lg" style="max-width: 540px !important;">
    <div class="modal-content">
        <div class="modal-header">
            <h4 class="modal-title">{{__trans('edit_employee_salary')}} : {{$user->name}}</h4>
            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <form action="{{route('backend.payroll.user.user-salaries.update',[$user,$userSalary])}}" datatable="true" method="POST" class="ajax-form-submit reset" oninput="res.value = total_working_days.value">
            @csrf
            @method('PUT')
            <div class="modal-body p-4">
                <div class="row">
                    <div class="col-md-12">
                        <div class="mb-3">
                            <label for="basic" class="form-label">{{__trans('payslip_type')}}</label>
                            <select name="payslip_type" class="form-control select" id="payslip_type">
                                <option value="monthly">Monthly Payslip</option>
                            </select>
                        </div>
                    </div>
                    <div id="salry_increment_section">
                        <div class="col-md-12">
                            <div class="mb-3">
                                <h6>{{__trans('Salary Increment')}}</h6>
                            </div>
                        </div>
                        <div class=" table-responsive">
                            <table id="myTable" class="w-100 ">
                                <tbody id="section">

                                    @foreach($userSalaryIncrement as $key => $list)
                                    <tr class="removeclass{{$key}}" id="removeclass">
                                        <input type="hidden" name="increment_id[]" value="{{$list->id}}">

                                        <td style="min-width: auto;"><label for="title" class="form-label">Before Inc.</label>
                                            <input type="text" id="before_increment{{$key}}" name="before_increment[]" class="form-control"
                                                placeholder="Before Inc." value="{{$list->before_increment ?? ''}}">
                                        </td>

                                        <td style="min-width: auto;"><label for="title" class="form-label">Increment</label>
                                            <input type="text" id="increment{{$key}}" name="increment[]" class="form-control"
                                                placeholder="Increment" value="{{$list->increment ?? ''}}">
                                        </td>

                                        <td style="min-width: auto;"><label for="title" class="form-label">After Inc.</label>
                                            <input type="text" id="after_increment{{$key}}" name="after_increment[]" class="form-control"
                                                placeholder="After Inc." value="{{$list->after_increment ?? ''}}">
                                        </td>

                                        <td style="min-width: auto;">
                                            <label for="increment_date" class="form-label">Increment Date</label>
                                            <!-- <input type="text" id="expiry_date_{{$key}}" name="expiry_date[]" value="{{$list->expiry_date ?? ''}}" class="form-control datepicker">     -->
                                            <input type="text" name="increment_date[]"
                                                id="increment_date{{$key}}" class="form-control datepicker" value="{{$list->increment_date ?? ''}}">
                                        </td>

                                        <td style="min-width: 50px;">
                                            <label for="increment_date" class="form-label">Remove</label>
                                            <!-- <a href="{{route('backend.filemanager.delete',$list->id)}}" type="button" class="btn btn-danger form-control text-white" onclick="return confirm('Are you sure you want to proceed?');">-</a> -->
                                            <button type="button" class="form-control btn btn-primary" onclick="removeSection({{$key}});">-</button>
                                        </td>



                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                            <div class="col-md-2 ">

                                <div class="">
                                    <button type="button" class="btn btn-primary" onclick="addFields();">+</button>
                                </div>

                            </div>
                        </div>
                    </div>
                    <div class="col-md-12">
                        <div class="mb-3">
                            <label for="basic" class="form-label">
                                @if (getSetting('payroll_calculation') == 'hourly')
                                {{__trans('hourly_rate')}}
                                @else
                                {{__trans('basic_salary')}}
                                @endif

                            </label>
                            <input type="number" step="0.01" name="basic" class="form-control" value="{{$userSalary->basic}}" id="basic" placeholder="{{__trans('basic_salary')}}">
                        </div>
                    </div>
                    <!-- <div class="col-md-12">
                        <div class="mb-3">
                            <label for="total_working_day" class="form-label">{{__trans('Total Working Days')}}</label> : &nbsp;
                            <output name="res" for="total_working_days">{{$userSalary->total_working_days}}</output> </br>
                            <input type="range" name="total_working_days" min="0" max="31" step="0" value="{{$userSalary->total_working_days}}">
                        </div>
                    </div> -->
                    @php
                    $fixed_allowance = json_decode($userSalary->fixed_allowances, true);
                    $fixed_deduction = json_decode($userSalary->fixed_deductions, true);
                    @endphp
                    <div class="col-md-12">
                        <div class="mb-3">
                            <h6>{{__trans('fixed_allowances')}}</h6>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="mb-3">
                            <label for="housing_allowance" class="form-label">{{__trans('housing_allowance')}}</label>
                            <input type="number" step="0.01" name="housing_allowance" class="form-control" value="{{$fixed_allowance['housing_allowance'] ?? '0'}}" id="housing_allowance" placeholder="{{__trans('housing_allowance')}}">
                        </div>
                    </div>
                    <div class="col-md-5">
                        <div class="mb-3">
                            <label for="transportation_allowance" class="form-label">{{__trans('transportation_allowance')}}</label>
                            <input type="number" step="0.01" name="transportation_allowance" class="form-control" value="{{$fixed_allowance['transportation_allowance'] ?? '0' }}" id="transportation_allowance" placeholder="{{__trans('transportation_allowance')}}">
                        </div>
                    </div>
                    <div class="col-md-5">
                        <div class="mb-3">
                            <label for="functional_allowance" class="form-label">{{__trans('functional_allowance')}}</label>
                            <input type="number" step="0.01" name="functional_allowance" class="form-control" value="{{$fixed_allowance['functional_allowance'] ?? '0' }}" id="functional_allowance" placeholder="{{__trans('functional_allowance')}}">
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="mb-3">
                            <label for="other_allowance" class="form-label">{{__trans('other_allowance')}}</label>
                            <input type="number" step="0.01" name="other_allowance" class="form-control" value="{{$fixed_allowance['other_allowance'] ?? '0'}}" id="other_allowance" placeholder="{{__trans('other_allowance')}}">
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="mb-3">
                            <label for="tips" class="form-label">{{__trans('tips')}}</label>
                            <input type="number" step="0.01" name="tips" class="form-control" value="{{$fixed_allowance['tips'] ?? '0'}}" id="tips" placeholder="{{__trans('tips')}}">
                        </div>
                    </div>
                    {{-- @foreach($allowance as $alldeduvalue)
                    @if($alldeduvalue->type==1)
                    @php
                    $allvalue = isset($fixed_allowance[$alldeduvalue->name]) ?$fixed_allowance[$alldeduvalue->name] : $alldeduvalue->amount;
                    @endphp
                    <div class="col-md-3">
                        <div class="mb-3">
                            <label for="tips" class="form-label">{{__trans($alldeduvalue->name)}}</label>
                            <input type="text" name="{{ $alldeduvalue->name }}" class="form-control" value="{{$allvalue}}" placeholder="{{__trans($alldeduvalue->name)}}">
                        </div>
                    </div>
                    @endif
                    @endforeach --}}
                    <div class="col-md-12">
                        <div class="mb-3">
                            <h6>{{__trans('fixed_deductions')}}</h6>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="mb-3">
                            <label for="advance_salary" class="form-label">{{__trans('advance_salary')}}</label>
                            <input type="number" step="0.01" name="advance_salary" class="form-control" value="{{$fixed_deduction['advance_salary'] ?? '0'}}" id="advance_salary" placeholder="{{__trans('advance_salary')}}">
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="mb-3">
                            <label for="loan_deduction" class="form-label">{{__trans('loan_deduction')}}</label>
                            <input type="number" step="0.01" name="loan_deduction" class="form-control" value="{{$fixed_deduction['loan_deduction'] ?? '0'}}" id="loan_deduction" placeholder="{{__trans('loan_deduction')}}">
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="mb-3">
                            <label for="other_deduction" class="form-label">{{__trans('other_deduction')}}</label>
                            <input type="number" step="0.01" name="other_deduction" class="form-control" value="{{$fixed_deduction['other_deduction'] ?? '0'}}" id="other_deduction" placeholder="{{__trans('other_deduction')}}">
                        </div>
                    </div>
                    {{-- @foreach($allowance as $alldeduvalue)
                    @if($alldeduvalue->type==2)
                    @php
                    $deduvalue = isset($fixed_deduction[$alldeduvalue->name]) ? $fixed_deduction[$alldeduvalue->name] : $alldeduvalue->amount;
                    @endphp
                    <div class="col-md-3">
                        <div class="mb-3">
                            <label for="tips" class="form-label">{{__trans($alldeduvalue->name)}}</label>
                            <input type="text" name="{{ $alldeduvalue->name }}" class="form-control" value="{{$deduvalue}}" placeholder="{{__trans($alldeduvalue->name)}}">
                        </div>
                    </div>
                    @endif
                    @endforeach --}}
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary waves-effect" data-bs-dismiss="modal">{{__trans('close')}}</button>
                <button type="submit" class="btn btn-info waves-effect waves-light">{{__trans('save')}} </button>
            </div>
        </form>
    </div>
</div>

<script src="{{asset('assets/backend/plugins/flatpickr/flatpickr.min.js')}}"></script>
<link rel="stylesheet" href="{{asset('assets/backend/plugins/flatpickr/flatpickr.min.css')}}">

<script>
    loadAjaxSelect2()
    @if(!empty($salary_increments))
    $count = {
        {
            count($salary_increments)
        }
    };
    var room = $count - 1;
    var srNo = $count - 1;
    @else
    var room = 0;
    var srNo = 0;
    @endif

    function incrementSrNo() {
        srNo++;
    }

    function decrementSrNo() {
        srNo--;
    }

    function addFields() {
        if (checkvalidate(srNo) == true) {
            incrementSrNo();
            room++;

            var objTo = document.getElementById('section');
            var tr = document.createElement("tr");
            tr.setAttribute("class", "removeclass" + room);
            tr.setAttribute("id", "removeclass" + room);

            var rdiv = 'removeclass' + room;

            tr.innerHTML = `<td style="min-width: auto;"><label for="title" class="form-label">Before Inc.</label>
                                                <input type="text" id="before_increment_${srNo}" name="before_increment[]" class="form-control"
                                                    placeholder="Before Inc." ></td>
                            <td style="min-width: auto;"><label for="title" class="form-label">Increment</label>
                                                                                <input type="text" id="increment_${srNo}" name="increment[]" class="form-control"
                                                                                placeholder="Increment" ></td>
                            <td style="min-width: auto;"><label for="title" class="form-label">After Inc.</label>
                                                    <input type="text" id="after_increment_${srNo}" name="after_increment[]" class="form-control"
                                                    placeholder="After Inc." ></td>
                                    
                            <td style="min-width: auto;"><label for="increment_date" class="form-label">Increment Date</label>
                                        <input type="text" id="increment_date_${srNo}" name="increment_date[]" value="" class="form-control datepicker"
                                            ></td>
                            
                            <td style="min-width: auto;">
                            
                            <label for="expiry_days" class="form-label text-white" >.</label>
                                                    <button type="button" class="btn btn-danger form-control text-white" onclick="removeSection(${srNo})">-</button>
                                                    
                            </td>
                                    `;

            objTo.appendChild(tr);
            datepicker();
        }
    }


    function removeSection(rid) {
        var elementToRemove = document.querySelector('.removeclass' + rid);
        elementToRemove.parentNode.removeChild(elementToRemove);
    }
    //     $('#myTable tr').click(function(){
    //     $(this).remove();
    //     return false;
    // });

    function checkvalidate(maxSrNo) {
        var errorMessage = "";
        for (var i = 0; i <= maxSrNo; i++) {
            var elementcheck = document.querySelector('.removeclass' + i);
            if (elementcheck) {
                var increment = $('#increment' + i).val();
                var increment_date = $('#increment_date_' + i).val();
                if (increment == '') {
                    errorMessage += "Rate field for Sr No " + i + " is empty or not a number.\n";
                    $('#increment_' + i).next('p.text-danger').remove(); // Remove existing error message if any
                    $('#increment_' + i).after("<p class='text-danger'>Required</p>");
                } else {
                    $('#increment_' + i).next('p.text-danger').remove(); // Remove existing error message if any
                }


                if (increment_date == '') {
                    errorMessage += "Rate field for Sr No " + i + " is empty or not a number.\n";
                    $('#increment_date_' + i).next('p.text-danger').remove(); // Remove existing error message if any
                    $('#increment_date_' + i).after("<p class='text-danger'>Required</p>");
                } else {
                    $('#increment_date_' + i).next('p.text-danger').remove(); // Remove existing error message if any
                }

            }
        }

        if (errorMessage !== "") {
            $('#submit').attr('disabled', false);

            return false;
        }

        return true;

    }

    function datepicker() {
        // Get today's date
        var today = new Date();
        // Calculate tomorrow's date
        var tomorrow = new Date(today);
        tomorrow.setDate(today.getDate() + 1);

        flatpickr("input.datepicker", {
            dateFormat: "Y-m-d",
            // minDate: tomorrow,
        });
    }
    flatpickr("input.datepicker", {
        dateFormat: "Y-m-d",
        // minDate: tomorrow,
    });
</script>