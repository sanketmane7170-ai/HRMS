<div class="modal-dialog modal-lg" style="max-width: 523px !important;">
    <div class="modal-content">
        <div class="modal-header">
            <h4 class="modal-title">{{__trans('loan_approval_form')}} </h4>
            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <form action="{{ route('backend.payroll.advance.processUpdate-advancepay',[$loan->reference_number]) }}" datatable="true" method="POST" class="ajax-form-submit reset">
            @csrf
            @method('POST')
            <div class="modal-body p-4">
                <div class="row">
                    <div class="col-md-12">
                        <table class="table table-bordered">
                            <thead>
                                <tr>
                                    <th><b>Loan Keys</b></th>
                                    <th><b>Loan Details</b></th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td>{{__trans('reference_number')}}</td>
                                    <td>{{ $loan->reference_number }}</td>
                                </tr>
                                <tr>
                                    <td>{{__trans('start_month')}}</td>
                                    <td>{{ $loan->start_month }}</td>
                                </tr>
                                <tr>
                                    <td>{{__trans('installments_paid')}}</td>
                                    <td>{{ $loan->installments_paid }}</td>
                                </tr>
                                <tr>
                                    <td>{{__trans('installments_pending')}}</td>
                                    <td>{{ $loan->installments_pending }}</td>
                                </tr>
                                @if (($loan->status == 'approved') && ($loan->installments_pending > 0))
                                <tr>
                                    <td>{{__trans('pay_installment')}}</td>
                                    <td> {!!createActionButton(route('backend.payroll.advance.advancepay-installment', $loan->reference_number), 'Pay Installment', 'btn-danger action-button', 'fa fa-money', '', 'Do you want to update installment','GET')!!}</td>
                                </tr>
                                @endif

                            </tbody>
                        </table>
                    </div>
                </div>
                <div class="row">

                    {{--  <div class="col-md-12">
                        <div class="mb-3">
                            <div class="form-group">
                                <label for="status">Loan Status</label>
                                <select name="status" class="form-control" required>
                                    <option value="closed">Close</option>
                                    <option value="cancelled">Cancel</option>
                                    <option value="hold">Hold</option>
                                    <option value="approved">Approve</option>
                                </select>
                            </div>
                        </div>
                    </div>  --}}

                </div>
            </div>
            <div class="modal-footer">
                <div class="form-group">
                    <button type="submit" class="btn btn-success">Submit</button>
                </div>
            </div>
    </div>
    </form>
</div>
</div>

<script>
    loadAjaxSelect2()
</script>