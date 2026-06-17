<div class="modal-dialog modal-lg">
    <div class="modal-content">
        <div class="modal-header">
            <h4 class="modal-title re d-none">{{__trans('reimbursement_air_ticket_request')}}</h4>
            <h4 class="modal-title boo d-none">{{__trans('air_ticket_booking_request')}}</h4>
            <h4 class="modal-title erboo d-none">{{__trans('early_air_ticket_booking_request')}}</h4>
            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body p-4" id="requestTypeStep">
            <div class="row text-center">
                @if($getPolicy->allow_reimbursement == 1)
                <div class="col-md-6">
                    <button type="button"
                            class="btn btn-outline-primary w-100 py-4 selectType"
                            data-type="reimbursement">
                        <i class="fa fa-money-bill fa-2x mb-2"></i><br>
                        {{ __trans('air_ticket_reimbursement') }}
                    </button>
                </div>
                @endif
                @if($getPolicy->allow_ticket_booking == 1)
                <div class="col-md-6">
                    <button type="button"
                            class="btn btn-outline-secondary w-100 py-4 selectType"
                            data-type="booking">
                        <i class="fa fa-plane fa-2x mb-2"></i><br>
                        {{ __trans('air_ticket_booking') }}
                    </button>
                </div>
                @endif
                @if($getPolicy->early_allow_ticket == 1)
                <div class="col-md-6">
                    <button type="button"
                            class="btn btn-outline-warning w-100 py-4 selectType"
                            data-type="earlybooking">
                        <i class="fa fa-plane-departure fa-2x mb-2"></i><br>
                        {{ __trans('early_air_ticket_booking') }}
                    </button>
                </div>
                @endif
            </div>
        </div>
        <form action="{{route('backend.employee.air-ticket.store')}}" datatable="true" method="POST" class="ajax-form-submit reset">
            @csrf
            <div class="modal-body p-4 d-none" id="reimbursementForm">
                <div class="row">
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label><strong>{{ __trans('journey_date') }}:</strong></label>
                            <input type="date" name="journey_date" class="form-control" placeholder="{{__trans('journey_date')}}">
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label><strong>{{ __trans('return_date') }}:</strong></label>
                            <input type="date" name="return_date" class="form-control" placeholder="{{__trans('return_date')}}">
                        </div>
                    </div>
                    <div class="col-md-6 booLoc d-none">
                        <div class="mb-3">
                            <label><strong>{{ __trans('location_from') }}:</strong></label>
                            <input type="text" name="location_from" class="form-control" placeholder="{{__trans('location_from')}}">
                        </div>
                    </div>
                    <div class="col-md-6 booLocTo d-none">
                        <div class="mb-3">
                            <label><strong>{{ __trans('location_to') }}:</strong></label>
                            <input type="text" name="location_to" class="form-control" placeholder="{{__trans('location_to')}}">
                        </div>
                    </div>
                    <div class="col-md-6 Ramount d-none">
                        <div class="mb-3">
                            <label><strong>{{ __trans('amount') }}:</strong></label>
                            <input type="number" name="requested_amount" class="form-control policyAmount" placeholder="{{__trans('amount')}}">
                            <span id="showlimit" style="color: red"></span>
                        </div>
                    </div>
                    <div class="col-md-6 Rfile d-none">
                        <div class="mb-3">
                            <label><strong>{{ __trans('upload_ticket_proof') }}:</strong></label>
                            <input type="file" name="ticket_proof" class="form-control">
                        </div>
                    </div>
                    <div class="col-md-12">
                        <div class="mb-3">
                            <label><strong>{{ __trans('remark') }}:</strong></label>
                            <textarea name="admin_remark" class="form-control"></textarea>
                        </div>
                    </div>
                </div>
                <input type="hidden" name="request_type" id="request_type">
                <div class="modal-footer">
                    <button type="button" class="btn btn-light" id="backToChoice">← {{ __trans('back') }}</button>
                    <button type="button" class="btn btn-secondary waves-effect" data-bs-dismiss="modal">{{__trans('close')}}</button>
                    <button type="submit" class="btn btn-info waves-effect waves-light">{{__trans('save')}} </button>
                </div>
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

    $(document).on('click', '.selectType', function () {
        let type = $(this).data('type');

        $('#request_type').val(type);

        $('#requestTypeStep').addClass('d-none');

        if (type === 'reimbursement') {
            $('#reimbursementForm').removeClass('d-none');
            $('.re').removeClass('d-none');
            $('.Ramount').removeClass('d-none');
            $('.Rfile').removeClass('d-none');

            $('.boo').addClass('d-none');
            $('.erboo').addClass('d-none');
            $('.booLoc').addClass('d-none');
            $('.booLocTo').addClass('d-none');
        }

        if (type === 'booking') {
            $('#reimbursementForm').removeClass('d-none');
            $('.boo').removeClass('d-none');
            $('.booLoc').removeClass('d-none');
            $('.booLocTo').removeClass('d-none');

            $('.re').addClass('d-none');
            $('.erboo').addClass('d-none');
            $('.Ramount').addClass('d-none');
            $('.Rfile').addClass('d-none');
        }

        if (type === 'earlybooking') {
            $('#reimbursementForm').removeClass('d-none');
            $('.erboo').removeClass('d-none');
            $('.boo').addClass('d-none');
            $('.re').addClass('d-none');

            $('.booLoc').addClass('d-none');
            $('.booLocTo').addClass('d-none');
            $('.re').addClass('d-none');
            $('.Ramount').addClass('d-none');
            $('.Rfile').addClass('d-none');
        }
    });

    $(document).on('click', '#backToChoice', function () {
        $('#reimbursementForm').addClass('d-none').find('input, select').val('');
        $('#showlimit').text('');
        $('#requestTypeStep').removeClass('d-none');
        $('#submitBtn').addClass('d-none');
        $('#request_type').val('');
        $('.invalid-feedback').addClass('d-none');
        $('#reimbursementForm').find('input, select').removeClass('is-invalid');
        $('.re').addClass('d-none');
        $('.boo').addClass('d-none');
        $('.erboo').addClass('d-none');
    });
</script>
