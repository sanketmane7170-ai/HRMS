<div class="modal-dialog modal-lg" style="max-width: 523px !important;">
    <div class="modal-content">
        <div class="modal-header">
            <h4 class="modal-title">{{__trans('loan_approval_form')}} </h4>
            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <form action="{{ route('backend.payroll.advance.approvepay-advancepay',[$loan->reference_number]) }}" datatable="true" method="POST" class="ajax-form-submit reset">
            @csrf
            @method('POST')
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
                                {{-- Sanket - Added requested date display in approval form --}}
                                <tr>
                                    <td>Requested Date</td>  {{-- Sanket - Shows when user originally submitted this request --}}
                                    <td>{{ $loan->formatted_requested_date }}</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-12">
                        <div class="mb-3">
                            <div class="form-group">
                                <label for="loan_amount">Requested Loan Amount</label>
                                <input type="text" class="form-control" id="loan_amount" name="loan_amount" value="{{ $loan->amount }}" readonly required>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-12">
                        <div class="mb-3">
                            <div class="form-group">
                                <label for="approved_amount">Approval Amount</label>
                                <input type="text" class="form-control" id="approved_amount" name="approved_amount" value="{{ $loan->amount }}" required>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-12">
                        <div class="mb-3">
                            <div class="form-group">
                            <label for="status">Loan mode</label>
                            <select name="loan_mode" class="form-control" required>
                                <option value="payroll">Payroll</option>
                                <option value="cash">Cash</option>
                            </select>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-12">
                        <div class="mb-3">
                            <div class="form-group">
                            <label for="status">Approval Status</label>
                            <select name="status" class="form-control" id="approval_status" required>
                                <option value="approved">Approve</option>
                                <option value="rejected">Reject</option>
                            </select>
                            </div>
                        </div>
                    </div>
                    {{-- Sanket - Rejection reason section (shown only when rejected is selected) --}}
                    <div class="col-md-12" id="rejection_reason_section" style="display: none;">
                        <div class="mb-3">
                            <div class="form-group">
                                <label>Do you want to specify a rejection reason?</label>
                                <div class="mt-2">
                                    <div class="form-check form-check-inline">
                                        <input class="form-check-input" type="radio" name="rejection_reason_choice" id="reason_yes" value="yes">
                                        <label class="form-check-label" for="reason_yes">Yes</label>
                                    </div>
                                    <div class="form-check form-check-inline">
                                        <input class="form-check-input" type="radio" name="rejection_reason_choice" id="reason_no" value="no">
                                        <label class="form-check-label" for="reason_no">No</label>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    {{-- Sanket - Rejection reason textarea (shown only when Yes is selected) --}}
                    <div class="col-md-12" id="rejection_reason_input" style="display: none;">
                        <div class="mb-3">
                            <div class="form-group">
                                <label for="rejection_reason">Rejection Reason <span class="text-danger">*</span></label>
                                <textarea name="rejection_reason" id="rejection_reason" class="form-control" rows="4" placeholder="Please enter the reason for rejecting this loan request..."></textarea>
                                <small class="text-muted">This reason will be visible to the employee who applied for the loan.</small>
                            </div>
                        </div>
                    </div>
                    {{-- Sanket - Dynamic date field that changes based on approval status --}}
                    <div class="col-md-12">
                        <div class="mb-3">
                            <div class="form-group">
                                <label for="action_date_display" id="date_label">Action Date</label>  {{-- Sanket - Dynamic label --}}
                                <input type="text" class="form-control" id="action_date_display" value="{{ date('d/m/Y') }}" readonly>
                                <small class="text-muted" id="date_help">This will be set automatically when you process the request.</small>  {{-- Sanket - Dynamic help text --}}
                            </div>
                        </div>
                    </div>
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
    loadAjaxSelect2();
    
    // Sanket - Update form labels and sections based on approval status selection
    document.getElementById('approval_status').addEventListener('change', function() {
        const status = this.value;
        const dateLabel = document.getElementById('date_label');
        const dateHelp = document.getElementById('date_help');
        const rejectionSection = document.getElementById('rejection_reason_section');
        const rejectionInput = document.getElementById('rejection_reason_input');
        
        if (status === 'approved') {
            dateLabel.textContent = 'Approved Date';
            dateHelp.textContent = 'This will be set automatically when you approve the request.';
            rejectionSection.style.display = 'none';
            rejectionInput.style.display = 'none';
            // Clear rejection reason fields
            document.querySelectorAll('input[name="rejection_reason_choice"]').forEach(radio => radio.checked = false);
            document.getElementById('rejection_reason').value = '';
        } else if (status === 'rejected') {
            dateLabel.textContent = 'Rejected Date';
            dateHelp.textContent = 'This will be set automatically when you reject the request.';
            rejectionSection.style.display = 'block';
        }
    });
    
    // Sanket - Handle rejection reason radio buttons
    document.querySelectorAll('input[name="rejection_reason_choice"]').forEach(function(radio) {
        radio.addEventListener('change', function() {
            const rejectionInput = document.getElementById('rejection_reason_input');
            const rejectionTextarea = document.getElementById('rejection_reason');
            
            if (this.value === 'yes') {
                rejectionInput.style.display = 'block';
                rejectionTextarea.setAttribute('required', 'required');
            } else {
                rejectionInput.style.display = 'none';
                rejectionTextarea.removeAttribute('required');
                rejectionTextarea.value = '';
            }
        });
    });
    
    // Sanket - Form validation before submission
    document.querySelector('form').addEventListener('submit', function(e) {
        const status = document.getElementById('approval_status').value;
        const rejectionChoice = document.querySelector('input[name="rejection_reason_choice"]:checked');
        const rejectionReason = document.getElementById('rejection_reason').value.trim();
        
        if (status === 'rejected') {
            if (!rejectionChoice) {
                e.preventDefault();
                alert('Please select whether you want to provide a rejection reason.');
                return false;
            }
            
            if (rejectionChoice.value === 'yes' && !rejectionReason) {
                e.preventDefault();
                alert('Please enter a rejection reason.');
                document.getElementById('rejection_reason').focus();
                return false;
            }
        }
    });
</script>