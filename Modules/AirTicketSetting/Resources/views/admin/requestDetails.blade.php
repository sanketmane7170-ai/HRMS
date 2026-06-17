<div class="modal-dialog modal-lg">
    <div class="modal-content">
        <div class="modal-header">
            <h4 class="modal-title">{{__trans('update_air_ticket_request')}}</h4>
            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <form action="{{route('backend.air-ticket.requestApprove',$airticketRequest->id)}}" datatable="true" method="POST" class="ajax-form-submit reset">
            @csrf
            <div class="modal-body p-4">
                <div class="row">
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label><strong>{{ __trans('journey_date') }}:</strong></label>
                            <input type="date" value="{{ $airticketRequest->journey_date }}" readonly name="journey_date" class="form-control" placeholder="{{__trans('journey_date')}}">
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label><strong>{{ __trans('return_date') }}:</strong></label>
                            <input type="date" value="{{ $airticketRequest->return_date }}" readonly name="return_date" class="form-control" placeholder="{{__trans('return_date')}}">
                        </div>
                    </div>
                    @if($airticketRequest->request_type == 'booking')
                    <div class="col-md-6 booLoc">
                        <div class="mb-3">
                            <label><strong>{{ __trans('location_from') }}:</strong></label>
                            <input type="text" name="location_from" readonly value="{{ $airticketRequest->location_from }}" class="form-control" placeholder="{{__trans('location_from')}}">
                        </div>
                    </div>
                    <div class="col-md-6 booLocTo">
                        <div class="mb-3">
                            <label><strong>{{ __trans('location_to') }}:</strong></label>
                            <input type="text" name="location_to" readonly value="{{ $airticketRequest->location_to }}" class="form-control" placeholder="{{__trans('location_to')}}">
                        </div>
                    </div>
                    @endif
                    @if($airticketRequest->request_type == 'reimbursement' || $airticketRequest->request_type == 'earlybooking')
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label><strong>{{ __trans('amount') }}:</strong></label>
                            <input type="number" name="requested_amount" @if($airticketRequest->request_type == 'earlybooking') value="{{ $getPolicy->allowance_amount }}" @else value="{{ $airticketRequest->requested_amount }}" @endif class="form-control policyAmount" placeholder="{{__trans('amount')}}">
                            <span id="showlimit" style="color: red"></span>
                            <span style="color: rgb(117, 152, 156)">Policy Amount: {{ $getPolicy->allowance_amount }}</span>
                        </div>
                    </div>
                    @endif
                    @if($airticketRequest->request_type == 'booking')
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label><strong>{{ __trans('ticket_proof') }}:</strong></label>
                            <input type="file" name="ticket_proof" class="form-control">
                        </div>
                    </div>
                    @endif
                    @if($airticketRequest->request_type == 'reimbursement' || $airticketRequest->request_type == 'earlybooking')
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label><strong>{{ __trans('payment_mode') }}:</strong></label>
                            <select name="payment_mode" class="form-control">
                                <option disabled selected >{{__trans('select_payment_mode')}}</option>
                                <option value="payroll" {{ $airticketRequest->status == 'payroll' ? 'selected' : '' }}>{{__trans('payroll')}}</option>
                                <option value="cash" {{ $airticketRequest->status == 'cash' ? 'selected' : '' }}>{{__trans('cash')}}</option>
                            </select>
                        </div>
                    </div>
                    @endif
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label><strong>{{ __trans('update_status') }}:</strong></label>
                            <select name="status" class="form-control">
                                <option disabled selected >{{__trans('select_status')}}</option>
                                <option value="approved" {{ $airticketRequest->status == 'approved' ? 'selected' : '' }}>{{__trans('approve')}}</option>
                                <option value="rejected" {{ $airticketRequest->status == 'rejected' ? 'selected' : '' }}>{{__trans('reject')}}</option>
                            </select>
                        </div>
                    </div>
                    @if($airticketRequest->request_type == 'reimbursement' && $airticketRequest->ticket_proof != null)
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label><strong>{{ __trans('uploaded_ticket_proof') }}:</strong></label>
                            <br>
                            @php
                                $file = $airticketRequest->ticket_proof;
                                $ext  = strtolower(pathinfo($file, PATHINFO_EXTENSION));
                                $imageExt = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
                            @endphp
                            @if(in_array($ext, $imageExt))
                                <a href="{{ asset($file) }}" download>
                                    <img src="{{ asset($file) }}" style="width:100%; height:80px;">
                                </a>
                            @else
                                <a href="{{ asset($file) }}" target="_blank">
                                    <i class="fa fa-file-pdf text-danger fa-2x"></i>
                                </a>
                            @endif
                        </div>
                    </div>
                    @endif
                    <div class="col-md-12">
                        <div class="mb-3">
                            <label><strong>{{ __trans('remark') }}:</strong></label>
                            <textarea name="admin_remark" class="form-control">{{ $airticketRequest->admin_remark }}</textarea>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary waves-effect" data-bs-dismiss="modal">{{__trans('close')}}</button>
                <button type="submit" class="btn btn-info waves-effect waves-light">{{__trans('Update status')}} </button>
            </div>
        </form>
    </div>
</div>
<script>
    initselect2search();
    flatpickr("input.datetime", {
        enableTime: false,
        // minDate: "today",
        dateFormat: "Y-m-d",
    });
    $(document).on('change', '.policyAmount', function () {
        var amount = $(this).val();
        if(amount != ''){
            $.ajax({
                url: "{{ route('backend.air-ticket.policyDetails',$airticketRequest->user_id) }}",
                type: 'get',
                success: function (response) {
                    if (response.success) {
                        policyAmount = response.policyAmount;
                        if(parseFloat(amount) > parseFloat(policyAmount)){
                            $('#showlimit').text('Amount exceeds the policy limit of ' + policyAmount);
                            $('.policyAmount').val('');
                        } else {
                            $('#showlimit').text('');
                        }
                    }
                },
                error: function (xhr) {
                    toastr.error('An error occurred while processing your request.');
                }
            });
        }
        
    });
</script>
