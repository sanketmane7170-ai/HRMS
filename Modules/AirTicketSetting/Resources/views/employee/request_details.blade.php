<div class="modal-dialog modal-lg">
    <div class="modal-content">
        <div class="modal-header">
            <h4 class="modal-title">{{__trans('air_ticket_details')}}</h4>
            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <form action="#" datatable="true" class="reset">
            <div class="modal-body p-4">
                <div class="row">
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label><strong>{{ __trans('journey_date') }}:</strong></label>
                            <span class="form-control">{{ $airTicket->journey_date }}</span>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label><strong>{{ __trans('return_date') }}:</strong></label>
                            <span class="form-control">{{ $airTicket->return_date }}</span>
                        </div>
                    </div>
                    @if($airTicket->request_type == 'booking')
                    <div class="col-md-6 booLoc">
                        <div class="mb-3">
                            <label><strong>{{ __trans('location_from') }}:</strong></label>
                            <span class="form-control">{{ $airTicket->location_from }}</span>
                        </div>
                    </div>
                    <div class="col-md-6 booLocTo">
                        <div class="mb-3">
                            <label><strong>{{ __trans('location_to') }}:</strong></label>
                            <span class="form-control">{{ $airTicket->location_to }}</span>
                        </div>
                    </div>
                    @endif
                    @if($airTicket->request_type == 'reimbursement' || $airTicket->request_type == 'earlybooking')
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label><strong>{{ __trans('amount') }}:</strong></label>
                            <span class="form-control">{{ $airTicket->requested_amount }}</span>
                        </div>
                    </div>
                    @endif
                    @if($airTicket->ticket_proof != null)
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label><strong>{{ __trans('uploaded_ticket_proof') }}:</strong></label>
                            <br>
                            @php
                                $file = $airTicket->ticket_proof;
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
                            <span class="form-control">{{ $airTicket->admin_remark }}</span>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary waves-effect" data-bs-dismiss="modal">{{__trans('close')}}</button>
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
</script>
