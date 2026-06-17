<div class="modal-dialog modal-lg">
    <div class="modal-content">
        <div class="modal-header">
            <h4 class="modal-title">{{__trans('update_air_ticket_request')}}</h4>
            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <form action="{{route('backend.employee.air-ticket.update',$airTicket->id)}}" datatable="true" method="POST" class="ajax-form-submit reset">
            @csrf
            <div class="modal-body p-4">
                <div class="row">
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label><strong>{{ __trans('journey_date') }}:</strong></label>
                            <input type="date" value="{{ $airTicket->journey_date }}" name="journey_date" class="form-control" placeholder="{{__trans('journey_date')}}">
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label><strong>{{ __trans('return_date') }}:</strong></label>
                            <input type="date" value="{{ $airTicket->return_date }}" name="return_date" class="form-control" placeholder="{{__trans('return_date')}}">
                        </div>
                    </div>
                    @if($airTicket->request_type == 'booking')
                    <div class="col-md-6 booLoc">
                        <div class="mb-3">
                            <label><strong>{{ __trans('location_from') }}:</strong></label>
                            <input type="text" name="location_from" value="{{ $airTicket->location_from }}" class="form-control" placeholder="{{__trans('location_from')}}">
                        </div>
                    </div>
                    <div class="col-md-6 booLocTo">
                        <div class="mb-3">
                            <label><strong>{{ __trans('location_to') }}:</strong></label>
                            <input type="text" name="location_to" value="{{ $airTicket->location_to }}" class="form-control" placeholder="{{__trans('location_to')}}">
                        </div>
                    </div>
                    @endif
                    @if($airTicket->request_type == 'reimbursement')
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label><strong>{{ __trans('amount') }}:</strong></label>
                            <input type="number" name="requested_amount" value="{{ $airTicket->requested_amount }}" class="form-control policyAmount" placeholder="{{__trans('amount')}}">
                            <span id="showlimit" style="color: red"></span>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label><strong>{{ __trans('upload_ticket_proof') }}:</strong></label>
                            <input type="file" name="ticket_proof" class="form-control">
                            <br>
                            @php
                                $file = $airTicket->ticket_proof;
                                $ext  = strtolower(pathinfo($file, PATHINFO_EXTENSION));
                                $imageExt = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
                            @endphp
                            @if(in_array($ext, $imageExt))
                                <img src="{{ asset($file) }}" style="width:100%; height:80px;">
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
                            <textarea name="admin_remark" class="form-control">{{ $airTicket->admin_remark }}</textarea>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary waves-effect" data-bs-dismiss="modal">{{__trans('close')}}</button>
                <button type="submit" class="btn btn-info waves-effect waves-light">{{__trans('Update')}} </button>
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
                url: "{{ route('backend.employee.air-ticket.policy') }}",
                type: 'get',
                data: {
                    _token: '{{ csrf_token() }}'
                },
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
