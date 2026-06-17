<div class="modal-dialog modal-lg">
    <div class="modal-content">
        <div class="modal-header">
            <h4 class="modal-title">{{__trans('add_expense')}}</h4>
            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <form action="{{route('backend.expense.store')}}" datatable="true" method="POST" class="ajax-form-submit reset">
            @csrf
            <div class="modal-body p-4">
                <div class="row">





                    <div class="col-md-6">
                        <div class="mb-3">
                            <label for="date" class="form-label">{{__trans('date')}}</label>
                            <input type="text" name="date" class="form-control datetime"
                                placeholder="{{__trans('date')}}">
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label for="expense_type_id" class="form-label">{{__trans('expense_type')}}</label>
                            <select name="expense_type_id" class="form-control select-search" id="expense_type_id">
                                @foreach ($expenseTypes as $expenseType)
                                <option value="{{$expenseType->id}}">{{$expenseType->name}}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    <!-- <div class="col-md-6">
                        <div class="mb-3">
                            <label for="name" class="form-label">{{__trans('Employee Name')}}</label>
                            <select name="user_id" class="form-control">

                                <option value="">{{ __trans('search_employee ...') }}</option>
                                @foreach ($users as $employee)
                                <option value="{{ $employee->id }}" @if( $is_employee && $employee->id == auth()->user()->id) selected @endif>{{ $employee->employee_id }}
                                    {{ $employee->name }}
                                </option>
                                @endforeach
                            </select>
                        </div>
                    </div> -->

                    <div class="col-md-6">
                        <div class="mb-3">
                            <label for="name" class="form-label">{{__trans('Employee Name')}}</label>
                            <select name="user_id" class="form-control ajax-select2"
                                data-target="{{ route('backend.expense.users') }}">

                                <option value="">{{ __trans('search_employee ...') }}</option>
                                @foreach ($users as $employee)
                                <option value="{{ $employee->id }}" @if( $is_employee && $employee->id ==
                                    auth()->user()->id) selected @endif>{{ $employee->employee_id }}
                                    {{ $employee->name }}
                                </option>
                                @endforeach
                            </select>
                        </div>
                    </div>




                    <div class="col-md-6">
                        <div class="mb-3">
                            <label for="name" class="form-label">{{__trans('description')}}</label>
                            <input type="text" name="name" class="form-control"
                                placeholder="{{__trans('description')}}">
                        </div>
                    </div>

                    <div class="col-md-6">
                        <div class="mb-3">
                            <label for="name" class="form-label">{{__trans('amount')}}</label>
                            <input type="number" name="amount" class="form-control" placeholder="{{__trans('amount')}}">
                        </div>
                    </div>

                    <!-- <div class="col-md-6">
                        <div class="mb-3">
                            <label for="remark" class="form-label">{{__trans('remark')}}</label>
                            <input type="text" name="remark" class="form-control" placeholder="{{__trans('remark')}}">
                        </div>
                    </div> -->


                    <div class="col-md-6">
                        <div class="mb-3">
                            <label for="payment_mode" class="form-label">{{__trans('payment_mode')}}</label>
                            <select name="payment_mode" class="form-control select-search" id="payment_mode">
                                <option value="">{{__trans('select')}}</option>
                                <option value="Cash">{{__trans('cash')}}</option>
                                <option value="Cheque">{{__trans('cheque')}}</option>
                                <option value="Online">{{__trans('online')}}</option>
                                <option value="Bank Transfer">{{__trans('Bank Transfer')}}</option>
                                <option value="Payroll">{{__trans('payroll')}}</option>
                            </select>
                        </div>
                    </div>

                    <div id="expense_document_section">
                        <div class="col-md-12">
                            <div class="mb-3">
                                <h6>{{__trans('Expense Documents')}}</h6>
                            </div>
                        </div>
                        <div class=" table-responsive">
                            <table id="myTable" class="w-100 ">
                                <tbody id="section">

                                    <tr class="removeclass0" id="removeclass">
                                        <td style="min-width: auto;">
                                            <label for="document"
                                                class="form-label">{{__trans('document_name')}}</label>
                                            <input type="text" id="document_name_0" name="document_name[]"
                                                class="form-control" placeholder="{{__trans('document_name')}}">
                                        </td>

                                        <td style="min-width: auto;">
                                            <label for="document" class="form-label">{{__trans('document')}}</label>
                                            <input type="file" id="document_0" name="document[]" id="document"
                                                class="form-control" placeholder="{{__trans('document')}}">
                                        </td>

                                        <td style="min-width: 20px;">
                                            <label for="increment_date" class="form-label">Add</label>
                                            <button type="button" class="form-control btn btn-primary"
                                                onclick="addFields();">+</button>
                                        </td>



                                    </tr>
                                </tbody>
                            </table>

                        </div>
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
    initselect2search();
    flatpickr("input.datetime", {
        enableTime: true,
        // minDate: "today",
        dateFormat: "Y-m-d",
    });
    // initTextEditor(['expense_body']);
</script>
<script>
    loadAjaxSelect2();

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