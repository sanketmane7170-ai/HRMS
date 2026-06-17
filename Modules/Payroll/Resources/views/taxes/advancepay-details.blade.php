<div class="modal-dialog modal-lg" style="max-width: 523px !important;">
    <div class="modal-content">
        <div class="modal-header">
            <h4 class="modal-title">{{__trans('loan_approval_form')}} </h4>
            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>

        <div class="modal-body p-4">
            <div class="row">
                <div class="col-md-12">
                    <table class="table table-bordered" style="color: black;">
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
                                <td>{{__trans('type')}}</td>
                                <td>{{ $loan->type }}</td>
                            </tr>
                            <tr>
                                <td>{{__trans('reason')}}</td>
                                <td>{{ $loan->reason }}</td>
                            </tr>
                            <tr>
                                <td>{{__trans('start_month')}}</td>  {{-- Sanket - Updated to use formatted date (DD/MM/YYYY) --}}
                                <td>{{ $loan->formatted_start_month }}</td>
                            </tr>
                            <tr>
                                <td>{{__trans('installments_paid')}}</td>
                                <td>{{ $loan->installments_paid }}</td>
                            </tr>
                            <tr>
                                <td>{{__trans('installments_pending')}}</td>
                                <td>{{ $loan->installments_pending }}</td>
                            </tr>
                            {{-- Sanket - Added date fields to loan details modal for complete request tracking --}}
                            <tr>
                                <td>Requested Date</td>  {{-- Sanket - Shows when user originally submitted the request --}}
                                <td>{{ $loan->formatted_requested_date }}</td>
                            </tr>
                            @if($loan->status === 'approved')
                            <tr>
                                <td>Approved Date</td>   {{-- Sanket - Shows when admin approved request --}}
                                <td>{{ $loan->formatted_approved_date }}</td>
                            </tr>
                            @elseif($loan->status === 'rejected')
                            <tr>
                                <td>Rejected Date</td>   {{-- Sanket - Shows when admin rejected request --}}
                                <td>{{ $loan->formatted_rejected_date }}</td>
                            </tr>
                            @if($loan->rejection_reason)
                            <tr>
                                <td><strong>Rejection Reason</strong></td>   {{-- Sanket - Shows rejection reason if provided --}}
                                <td>
                                    <div class="alert alert-danger mb-0" style="padding: 8px 12px;">
                                        <strong>Rejection Reason:</strong><br>
                                        {{ $loan->rejection_reason }}
                                    </div>
                                </td>
                            </tr>
                            @endif
                            @else
                            <tr>
                                <td>Status</td>   {{-- Sanket - Shows current status if pending --}}
                                <td>{{ ucfirst($loan->status) }}</td>
                            </tr>
                            @endif
                        </tbody>
                    </table>
                </div>
            </div>

        </div>
        <div class="modal-footer">
            <button type="button" class="btn btn-danger waves-effect waves-light" data-bs-dismiss="modal" aria-label="Close">{{__trans('close')}} </button>
        </div>
    </div>
</div>
</div>

<script>
    loadAjaxSelect2()
    
    // Handle rejection reason functionality
    $(document).ready(function() {
        // Handle status change
        $('#status').on('change', function() {
            const status = $(this).val();
            const rejectionSection = $('#rejection-reason-section');
            const rejectionChoiceSection = $('#rejection-choice-section');
            const rejectionTextarea = $('#rejection-reason-textarea');
            
            if (status === 'rejected') {
                rejectionChoiceSection.show();
                rejectionSection.hide();
                rejectionTextarea.find('textarea').val(''); // Clear textarea
                $('input[name="rejection_reason_choice"]').prop('checked', false); // Clear radio buttons
                $('#rejection_reason_choice_no').prop('checked', true); // Default to No
            } else {
                rejectionChoiceSection.hide();
                rejectionSection.hide();
                rejectionTextarea.find('textarea').val('');
            }
        });
        
        // Handle rejection reason choice
        $('input[name="rejection_reason_choice"]').on('change', function() {
            const choice = $(this).val();
            const rejectionSection = $('#rejection-reason-section');
            const textarea = rejectionSection.find('textarea');
            
            if (choice === 'yes') {
                rejectionSection.show();
                textarea.attr('required', true);
            } else {
                rejectionSection.hide();
                textarea.attr('required', false);
                textarea.val(''); // Clear the textarea
            }
        });
        
        // Form submission validation
        $('form').on('submit', function(e) {
            const status = $('#status').val();
            const rejectionChoice = $('input[name="rejection_reason_choice"]:checked').val();
            const rejectionReason = $('#rejection_reason').val();
            
            if (status === 'rejected') {
                if (!rejectionChoice) {
                    e.preventDefault();
                    alert('Please select whether you want to provide a rejection reason.');
                    return false;
                }
                
                if (rejectionChoice === 'yes' && !rejectionReason.trim()) {
                    e.preventDefault();
                    alert('Please provide a rejection reason.');
                    $('#rejection_reason').focus();
                    return false;
                }
            }
        });
    });
</script>