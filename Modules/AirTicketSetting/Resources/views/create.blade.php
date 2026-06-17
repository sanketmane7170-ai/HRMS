<div class="modal-dialog modal-lg">
    <div class="modal-content">
        <div class="modal-header">
            <h4 class="modal-title">{{__trans('add_Air_Ticket_Setting')}}</h4>
            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <form action="{{route('backend.settings.air-ticket-setting.store')}}" datatable="true" method="POST"
            class="ajax-form-submit reset">
            @csrf
            <div class="modal-body p-4">

                <div class="mb-4">
                    <label for="name" class="form-label fw-bold">{{__trans('policy_name')}}</label>
                    <input type="text" name="policy_name" class="form-control"
                        placeholder="{{__trans('policy_name')}}">
                </div>

                <!-- Allowance Limit Per Cycle -->
                <div class="mb-3">
                    <label class="form-label fw-bold">Allowance Limit Per Cycle</label>
                    <div class="row g-3">
                        <!-- Currency Dropdown -->
                        <div class="col-md-6">
                            <label for="currency" class="form-label">Currency</label>
                            <select name="allowance_currency" class="form-control select-search" id="allowance_currency">
                                @foreach ($currencies as $code => $name)
                                <option value="{{ $code }}">{{ $name }} ({{ $code }})</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label for="country" class="form-label">Country</label>
                            <select id="country" name="country" class="form-control select-search flag_country">
                                <option value="">{{__trans('select_a_option')}}</option>
                                <option value="0">{{__trans('all')}}</option>
                                @foreach (getCountryListwithFlag() as $country)
                                <option data-flag="{{ $country['flag_url'] }}" value="{{$country['id']}}">{{$country['name']}}</option>
                                @endforeach
                            </select>
                        </div>
                        <!-- Allowance Amount -->
                        <div class="col-md-6">
                            <label for="allowance-amount" class="form-label">Allowance Amount</label>
                            <input type="text" id="allowance_amount" name="allowance_amount" class="form-control"
                                placeholder="{{__trans('allowance_amount')}}" onchange="updateEncashment()">
                        </div>
                    </div>
                </div>

                <div class="mb-3">
                    <label class="form-label fw-bold">Employees can request an air ticket after</label>
                    <div class="row g-3">
                        <!-- Currency Dropdown -->
                        <div class="col-md-6">
                            <label for="currency" class="form-label">No. of Months</label>
                            <input type="text" name="request_after_months" class="form-control"
                                placeholder="{{__trans('No. of Months')}}">
                        </div>
                        <!-- Allowance Amount -->
                        <div class="col-md-6">
                            <label for="request_after_months_date"
                                class="form-label">{{__trans('from')}}</label>
                            <select name="request_after_from" class="form-control select-search"
                                id="request_after_from">
                                <option value="hiring_date">Hiring Date</option>
                                <option value="probation_date">Probation Date</option>
                            </select>
                        </div>
                    </div>
                </div>

                <div class="mb-3">
                    <label class="form-label fw-bold">Air ticket policy renewal occurs in </label>
                    <div class="row g-3">
                        <!-- Currency Dropdown -->
                        <div class="col-md-12">
                            <label for="currency" class="form-label">No. of Months</label>
                            <input type="text" name="policy_renewal_months" class="form-control"
                                placeholder="{{__trans('No. of Months')}}">
                        </div>
                        <!-- Allowance Amount -->

                    </div>
                </div>

                <div class="mb-3">
                    <label class="form-label fw-bold">No. of requests employee can make every cycle</label>
                    <div class="row g-3">
                        <!-- Currency Dropdown -->
                        <div class="col-md-12">
                            <label for="currency" class="form-label">Quantity</label>
                            <input type="text" name="request_limit_per_cycle" class="form-control"
                                placeholder="{{__trans('Quantity')}}">
                        </div>
                        <!-- Allowance Amount -->

                    </div>
                </div>
                <div class="col-md-12">
                    <div class="form-check form-switch mb-3">
                        <input class="form-check-input" type="checkbox" id="allow_reimbursement" name="allow_reimbursement"
                            value="1">
                        <label class="form-check-label" for="allow_reimbursement">
                            <strong>Reimbursement</strong><br>
                            <span class="text-muted">Employees can request a reimbursement of an air ticket they
                                purchased</span>
                        </label>
                    </div>
                </div>
                <div class="col-md-12">
                    <div class="form-check form-switch mb-3">
                        <input class="form-check-input" type="checkbox" id="allow_encashment" name="allow_encashment" value="1"
                            checked>
                        <label class="form-check-label" for="allow_encashment">
                            <strong>Encashment</strong><br>
                            <span class="text-muted">Employees can request for an encashment of their allowed air
                                ticket
                                amount - <strong id="encashment-amount">AED 1,650.00</strong></span>
                        </label>
                    </div>
                </div>
                <div class="col-md-12">
                    <div class="form-check form-switch">
                        <input class="form-check-input" type="checkbox" id="allow_ticket_booking"
                            name="allow_ticket_booking" value="1">
                        <label class="form-check-label" for="allow_ticket_booking">
                            <strong>Air Ticket Booking</strong><br>
                            <span class="text-muted">Employees can request the company to book an air ticket for
                                them</span>
                        </label>
                    </div>
                </div>
                <div class="col-md-12">
                    <div class="form-check form-switch">
                        <input class="form-check-input" type="checkbox" id="early_allow_ticket" name="early_allow_ticket" value="1">
                        <label class="form-check-label" for="early_allow_ticket">
                            <strong>Early Air Ticket Booking</strong><br>
                            <span class="text-muted">Employees can request an air ticket early for them</span>
                        </label>
                    </div>
                    <div class="col-md-6" id="early_month" style="display: none">
                        <label for="currency" class="form-label">Early Month</label>
                        <input type="number" name="early_month" id="early_month_val" class="form-control" placeholder="{{__trans('early month')}}">
                    </div>
                </div>

            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary waves-effect"
                    data-bs-dismiss="modal">{{__trans('close')}}</button>
                <button type="submit" class="btn btn-info waves-effect waves-light">{{__trans('save')}} </button>
            </div>
        </form>
    </div>
</div>
<script>
    flatpickr("input.datetime", {
        enableTime: true,
        // minDate: "today",
        dateFormat: "Y-m-d",
    });
    // initTextEditor(['airticketsetting_body']);
</script>
<script>
    $(function() {
        $("#department_id").change(function() {
            $("#user_id").prop("disabled", false);
            if ($(this).val() > 0) {
                $("#user_id").prop("disabled", true);
                $("#user_id").val('0').change();
            }

        });
    });
    $(function() {
        $("#user_id").change(function() {
            $("#department_id").prop("disabled", false);
            if ($(this).val() > 0) {
                $("#department_id").prop("disabled", true);
                $("#department_id").val('0').change();
            }

        });
    });
    $(function () {
        $("#early_allow_ticket").on('change', function () {
            if ($(this).is(':checked')) {
                $("#early_month").show();
            } else {
                $("#early_month").hide();
                $("#early_month_val").val('');
            }
        });
    });
    
</script>

<script>
    var room = 1;
    var srNo = 1;

    function documentSrNo() {
        srNo++;
    }

    function decrementSrNo() {
        srNo--;
    }

    function addFields() {
        documentSrNo();
        room++;

        var objTo = document.getElementById('section');
        var tr = document.createElement("tr");
        tr.setAttribute("class", "removeclass" + room);
        tr.setAttribute("id", "removeclass" + room);

        var rdiv = 'removeclass' + room;

        tr.innerHTML = `<td style="min-width: auto;">
                               <input required type="text" id="document_name_${srNo}" name="document_name[]" class="form-control" placeholder="{{__trans('document_name')}}">
                            </td>

                            <td style="min-width: auto;">
                                <input required type="file" id="document_${srNo}" name="document[]" id="document" class="form-control" placeholder="{{__trans('document')}}" >
                            </td>
                        
                            
                            <td style="min-width: auto;">
                                <button type="button" class="btn btn-danger form-control text-white" onclick="removeSection(${srNo})">-</button>
                            </td>
                                    `;

        objTo.appendChild(tr);
    }

    function removeSection(rid) {
        var elementToRemove = document.querySelector('.removeclass' + rid);
        elementToRemove.parentNode.removeChild(elementToRemove);
    }
</script>
<script>
    function updateEncashment() {
        // Get the Allowance Amount value
        const allowance = parseFloat(document.getElementById('allowance_amount').value);
        const allowance_currency = document.getElementById('allowance_currency').value;
        document.getElementById('encashment-amount').textContent = `${allowance_currency}-${allowance.toFixed(2)}`;
    }
    
</script>