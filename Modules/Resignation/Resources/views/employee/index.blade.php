@extends('layouts.backend')

@push('css')
<style>
    /* Scoped Square Border Overrides - ONLY for Resignation Module */
    #resignation_module_container * {
        border-radius: 0 !important;
    }
    
    #resignation_module_container .card, 
    #resignation_module_container .form-control, 
    #resignation_module_container .btn, 
    #resignation_module_container .badge, 
    #resignation_module_container .progress, 
    #resignation_module_container .modal-content, 
    #resignation_module_container .alert {
        border-radius: 0 !important;
    }

    /* Scoped visible borders and dark text */
    #resignation_module_container .form-control, 
    #resignation_module_container select.form-control {
        border: 1px solid #ced4da !important;
        background-color: #ffffff !important;
        color: #000000 !important;
    }
    
    #resignation_module_container .text-dark {
        color: #000000 !important;
    }
    #resignation_module_container .table td {
        color: #000000 !important;
    }

    /* Force black text on badges for maximum readability */
    #resignation_module_container .badge {
        color: #000000 !important;
        border: 1px solid #ced4da !important;
    }

    /* Scoped Select2 forcing square */
    #resignation_module_container .select2-container--default .select2-selection--single {
        border-radius: 0 !important;
        border: 1px solid #ced4da !important;
        height: 40px !important;
    }
</style>
@endpush

@section('content')
<div id="resignation_module_container">
    <div class="page-wrapper">
        <div class="content container-fluid">
            <!-- Page Header -->
            <div class="page-header mb-4">
                <div class="row align-items-center">
                    <div class="col">
                        <h3 class="page-title text-dark">My Resignation 📄</h3>
                        <ul class="breadcrumb bg-transparent p-0">
                            <li class="breadcrumb-item"><a href="{{ route('backend.dashboard') }}" class="text-secondary">Dashboard</a></li>
                            <li class="breadcrumb-item active text-dark">Resignation</li>
                        </ul>
                    </div>
                    <div class="col-auto float-right ml-auto">
                        <div class="row align-items-center">
                            <div class="col">
                                <button type="button" class="btn btn-info btn-sm px-4 shadow-sm" 
                                        data-toggle="modal" data-target="#policy_modal" 
                                        data-bs-toggle="modal" data-bs-target="#policy_modal"
                                        onclick="$('#policy_modal').modal('show');"
                                        style="border-radius: 20px !important;">
                                    <i class="fas fa-info-circle mr-1"></i> How Resignation Process Works 💡
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <!-- /Page Header -->

            <!-- Status Section (Visible if Applied) -->
            <div class="row" id="status-section" style="display: none;">
                <div class="col-md-12">
                    <div class="card border shadow-sm bg-white">
                        <div class="card-body p-4">
                            <h4 class="card-title text-dark mb-4 border-bottom pb-2">Current Application Status</h4>
                            
                            <div class="row align-items-center mb-4">
                                <div class="col-md-3">
                                    <span class="badge p-3" id="status-badge" style="font-size: 1rem; width: 100%; display: block; text-align: center;">Pending</span>
                                </div>
                                <div class="col-md-9">
                                    <div class="progress" style="height: 15px;">
                                        <div class="progress-bar" role="progressbar" style="width: 0%;" id="progress-bar"></div>
                                    </div>
                                    <small id="progress-text" class="text-muted mt-1 d-block text-right text-dark">Processing...</small>

                                    <!-- Manager/HR Comments (Visible if provided) -->
                                    <div id="action-comments-div" style="display: none;" class="mt-3 p-3 bg-light rounded border-left border-info shadow-sm">
                                        <p class="mb-1 text-muted small"><i class="fas fa-comment-alt mr-1"></i> Manager/HR Remarks:</p>
                                        <p class="text-dark font-italic mb-0" id="action-comments-text"></p>
                                    </div>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-3">
                                    <p class="text-muted mb-1">Applied Date</p>
                                    <h5 class="text-dark font-weight-bold" id="applied-date">-</h5>
                                </div>
                                <div class="col-md-3">
                                    <p class="text-muted mb-1">Preferred Last Day</p>
                                    <h5 class="text-dark font-weight-bold" id="preferred-date">-</h5>
                                </div>
                                <div class="col-md-3">
                                    <p class="text-muted mb-1">Approved Last Day</p>
                                    <h5 class="text-dark font-weight-bold" id="approved-date">-</h5>
                                </div>
                                <div class="col-md-3">
                                    <p class="text-muted mb-1">Notice End Date</p>
                                    <h5 class="text-dark font-weight-bold" id="notice-end-date">-</h5>
                                </div>
                            </div>

                            <hr>

                            <div class="text-right d-flex justify-content-end gap-2">
                                <a href="#" class="btn btn-outline-info" id="download-doc-btn" style="display: none;" target="_blank">
                                    <i class="fas fa-file-download mr-1"></i> Download Submitted Document
                                </a>
                                <button class="btn btn-outline-danger" id="withdraw-btn" style="display: none;">
                                    <i class="fas fa-undo mr-1"></i> Withdraw Application
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Apply Form (Visible if Not Applied) -->
            <div class="row" id="apply-section">
                <div class="col-md-12">
                    <div class="card border shadow-sm bg-white">
                        <div class="card-header bg-transparent border-bottom-0 pt-4 pb-0">
                             <h4 class="card-title text-dark">Submit New Resignation</h4>
                             <p class="text-muted">Fill out the form below to initiate the resignation process.</p>
                        </div>
                        <div class="card-body">
                            <form id="apply-form">
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label class="text-dark font-weight-bold">Reason for Resignation <span class="text-danger">*</span></label>
                                            <!-- Inline onchange for absolute reliability -->
                                            <select class="form-control" name="reason" id="reason_select" required 
                                                    onchange="if(this.value === 'Other') { $('#other_reason_div').show(); $('#other_reason_input').attr('required', true).focus(); } else { $('#other_reason_div').hide(); $('#other_reason_input').removeAttr('required'); }">
                                                <option value="">-- Select Reason --</option>
                                                <option value="Better Opportunity">Found a better opportunity</option>
                                                <option value="Health Issues">Health Issues</option>
                                                <option value="Personal Reasons">Personal Reasons</option>
                                                <option value="Relocation">Relocation</option>
                                                <option value="Other">Other (Please Specify)</option>
                                            </select>
                                        </div>
                                        <!-- Other Reason Input (Toggled) -->
                                        <div class="form-group mt-2" id="other_reason_div" style="display: none;">
                                            <label class="text-dark font-weight-bold">Specify Reason <span class="text-danger">*</span></label>
                                            <textarea class="form-control" name="other_reason" id="other_reason_input" rows="2" placeholder="Please type your reason here..."></textarea>
                                        </div>
                                    </div>

                                    <div class="col-md-3">
                                        <div class="form-group">
                                            <label class="text-dark font-weight-bold">Preferred Last Working Date <span class="text-danger">*</span></label>
                                            <input type="date" class="form-control" name="preferred_last_working_date" required min="{{ date('Y-m-d') }}">
                                        </div>
                                    </div>
                                    <div class="col-md-3">
                                        <div class="form-group">
                                            <label class="text-dark font-weight-bold">Notice Period (Days)</label>
                                            <input type="number" class="form-control bg-light" name="notice_period_days" value="{{ $noticePeriod ?? 30 }}" readonly>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label class="text-dark font-weight-bold">Signed Document (Optional)</label>
                                            <input type="file" class="form-control" name="signed_document" accept=".pdf,.jpg,.png,.docx">
                                            <small class="text-muted">Upload signed resignation letter (PDF, Image, or DOCX)</small>
                                        </div>
                                    </div>
                                </div>

                                <div class="form-group">
                                    <label class="text-dark font-weight-bold">Additional Comments / Handover Plan</label>
                                    <textarea rows="4" class="form-control" name="comments" placeholder="Enter any additional details..."></textarea>
                                </div>

                                <div class="submit-section mt-4 text-left">
                                    <button type="submit" class="btn btn-primary px-5">Submit Application</button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Resignation History (Always Visible if history exists) -->
            <div class="row mt-4" id="history-section" style="display: none;">
                <div class="col-md-12">
                    <div class="card border shadow-sm bg-white">
                        <div class="card-header bg-transparent border-bottom-0 pt-4 pb-0">
                             <h4 class="card-title text-dark">Resignation History 📂</h4>
                             <p class="text-muted">A record of all your previous resignation applications.</p>
                        </div>
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table table-hover bg-white text-dark border">
                                    <thead class="bg-light">
                                        <tr>
                                            <th>Applied Date</th>
                                            <th>Reason</th>
                                            <th>Document</th>
                                            <th>Status</th>
                                            <th>Remarks</th>
                                        </tr>
                                    </thead>
                                    <tbody id="history-table-body">
                                        <!-- Data will be populated by JS -->
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
        </div>
    </div>

    <!-- Policy Modal -->
    <div class="modal fade" id="policy_modal" tabindex="-1" role="dialog" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-lg" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Resignation Process & Policy</h5>
                    <!-- Removed the 'X' button as requested -->
                </div>
                <div class="modal-body text-dark">
                    @if(isset($policy) && $policy)
                        <div class="policy-content p-3">
                            {!! nl2br(e($policy)) !!}
                        </div>
                    @else
                        <div class="alert alert-info">
                            No specific policy content has been set by HR yet.
                        </div>
                    @endif
                </div>
                <div class="modal-footer">
                    <!-- Added both dismiss attributes and a manual class for fallback -->
                    <button type="button" class="btn btn-secondary manual-close" data-dismiss="modal" data-bs-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    $(document).ready(function() {
        const baseUrl = "{{ url('/') }}";
        
        // Manual Modal Close Fallback
        $('.manual-close').click(function(){
            $(this).closest('.modal').modal('hide');
        });
        // 1. Check Status on Load
        fetchStatus();

        function fetchStatus() {
            $.ajax({
                url: '/api/v1/resignation/my',
                method: 'GET',
                success: function(response) {
                    const data = response.data;
                    if(data.length > 0) {
                        const latest = data[0]; 
                        
                        // Show active status if NOT withdrawn or rejected
                        if (latest.status !== 'withdrawn' && latest.status !== 'rejected') {
                            $('#apply-section').hide();
                            $('#status-section').fadeIn();
                            updateStatusUI(latest);
                        } else {
                            $('#apply-section').fadeIn();
                            $('#status-section').hide();
                        }

                        // Populate History
                        updateHistoryTable(data);
                    } else {
                        $('#apply-section').fadeIn();
                        $('#status-section').hide();
                        $('#history-section').hide();
                    }
                },
                error: function(xhr) {
                    console.log('Error fetching status', xhr);
                }
            });
        }

        function updateHistoryTable(data) {
            const tableBody = $('#history-table-body');
            tableBody.empty();
            
            if(data.length > 0) {
                $('#history-section').fadeIn();
                data.forEach(item => {
                    let badgeClass = 'badge-secondary';
                    if(item.status === 'pending') badgeClass = 'badge-info';
                    else if(item.status === 'approved') badgeClass = 'badge-success';
                    else if(item.status === 'rejected') badgeClass = 'badge-danger';
                    else if(item.status === 'completed') badgeClass = 'badge-dark';
                    else if(item.status === 'withdrawn') badgeClass = 'badge-secondary';
                    else if(item.status === 'on_hold') badgeClass = 'badge-info'; // Author: Sanket - Changed from warning to info for better visibility

                    const remarks = (item.actions && item.actions.length > 0) 
                        ? (item.actions[0].comments || '-') 
                        : (item.comments || '-');

                    const row = `
                        <tr>
                            <td>${formatDate(item.applied_date)}</td>
                            <td>${item.reason}</td>
                            <td>${item.signed_document ? `<a href="${baseUrl}/storage/${item.signed_document}" target="_blank" class="text-info"><i class="fas fa-file-pdf"></i> View</a>` : '-'}</td>
                            <td><span class="badge ${badgeClass}">${item.status.toUpperCase().replace('_', ' ')}</span></td>
                            <td>${remarks}</td>
                        </tr>
                    `;
                    tableBody.append(row);
                });
            } else {
                $('#history-section').hide();
            }
        }

        function updateStatusUI(resignation) {
            // Badges
            let badgeClass = 'badge-secondary';
            if(resignation.status === 'pending') badgeClass = 'badge-info';
            else if(resignation.status === 'approved') badgeClass = 'badge-success';
            else if(resignation.status === 'rejected') badgeClass = 'badge-danger';
            else if(resignation.status === 'completed') badgeClass = 'badge-light';
            else if(resignation.status === 'on_hold') badgeClass = 'badge-info'; // Author: Sanket - Changed from warning to info
            
            $('#status-badge').removeClass().addClass('badge p-3 text-white ' + badgeClass).text(resignation.status.toUpperCase().replace('_', ' '));
            
            // Dates
            const approvedDate = resignation.approved_last_working_date ? formatDate(resignation.approved_last_working_date) : '-';
            $('#applied-date').text(formatDate(resignation.applied_date));
            $('#preferred-date').text(formatDate(resignation.preferred_last_working_date));
            $('#approved-date').text(approvedDate);
            
            // Ensure Notice End Date loads if available, fallback to approved date for completed/approved
            if(resignation.notice_period) {
                $('#notice-end-date').text(formatDate(resignation.notice_period.end_date));
            } else if (resignation.status === 'approved' || resignation.status === 'completed') {
                $('#notice-end-date').text(approvedDate);
            } else {
                $('#notice-end-date').text('-');
            }

            // Progress
            let width = '0%';
            let text = 'Application Submitted';
            let barClass = 'bg-primary';

            if(resignation.status === 'pending') { width = '25%'; text = 'Pending Manager/HR Review'; }
            else if(resignation.status === 'approved') { 
                width = '75%'; text = 'Approved - Serving Notice Period'; barClass = 'bg-success';
            }
            else if(resignation.status === 'on_hold') { width = '50%'; text = 'Application On Hold'; barClass = 'bg-info'; } // Author: Sanket - Changed from warning to info
            else if(resignation.status === 'completed') { width = '100%'; text = 'Resignation Process Completed'; barClass = 'bg-dark'; }
            else if(resignation.status === 'rejected') { width = '100%'; text = 'Application Rejected'; barClass = 'bg-danger'; }
            else if(resignation.status === 'withdrawn') { width = '100%'; text = 'Application Withdrawn'; barClass = 'bg-secondary'; }

            $('#progress-bar').css('width', width).removeClass().addClass('progress-bar ' + barClass);
            $('#progress-text').text(text);

            // Handle Comments/Remarks
            if (resignation.actions && resignation.actions.length > 0) {
                // Get the latest meaningful action with comments
                const latestAction = resignation.actions.filter(a => a.comments).shift();
                if (latestAction) {
                    $('#action-comments-text').text(latestAction.comments);
                    $('#action-comments-div').show();
                    
                    // Specific styling for "On Hold" - Author: Sanket
                    if(resignation.status === 'on_hold') {
                        $('#action-comments-div').removeClass('border-info').addClass('border-info bg-info-light');
                    } else {
                        $('#action-comments-div').removeClass('border-info bg-info-light').addClass('border-info');
                    }
                } else {
                    $('#action-comments-div').hide();
                }
            } else {
                $('#action-comments-div').hide();
            }

            if(resignation.status === 'pending') {
                $('#withdraw-btn').show().data('id', resignation.id);
            } else {
                $('#withdraw-btn').hide();
            }

            if(resignation.signed_document) {
                const docUrl = `${baseUrl}/storage/${resignation.signed_document}`;
                $('#download-doc-btn').attr('href', docUrl).show();
            } else {
                $('#download-doc-btn').hide();
            }
        }

        function formatDate(dateString) {
            if(!dateString) return '-';
            return new Date(dateString).toLocaleDateString();
        }

        // 2. Submit Application with Confirmation
        $('#apply-form').on('submit', function(e) {
            e.preventDefault();
            
            Swal.fire({
                title: 'Confirm Submission',
                text: "Are you sure you want to submit your resignation application?",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#3085d6',
                confirmButtonText: 'Yes, Submit'
            }).then((result) => {
                if (result.isConfirmed) {
                    processSubmission(this);
                }
            });
        });

        function processSubmission(form) {
            let formData = new FormData(form);
            if($('#reason_select').val() === 'Other') {
                const otherReason = $('#other_reason_input').val();
                formData.set('reason', 'Other: ' + otherReason);
            }

            $.ajax({
                url: '/api/v1/resignation/apply',
                method: 'POST',
                data: formData,
                processData: false,
                contentType: false,
                headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') },
                success: function(response) {
                    Swal.fire('Submitted!', 'Your resignation application has been submitted.', 'success')
                        .then(() => { fetchStatus(); });
                },
                error: function(xhr) {
                    const msg = xhr.responseJSON?.message || 'Something went wrong';
                    Swal.fire('Error', msg, 'error');
                }
            });
        }

        // 3. Withdraw Action
        $('#withdraw-btn').on('click', function() {
            const id = $(this).data('id');
            Swal.fire({
                title: 'Confirm Withdrawal',
                text: "Are you sure you want to withdraw?",
                icon: 'question',
                showCancelButton: true,
                confirmButtonText: 'Yes, Withdraw'
            }).then((result) => {
                if (result.isConfirmed) {
                    $.ajax({
                        url: '/api/v1/resignation/' + id + '/withdraw',
                        method: 'DELETE',
                        headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') },
                        success: function(response) {
                            Swal.fire('Withdrawn', 'Resignation withdrawn successfully.', 'success').then(() => fetchStatus());
                        }
                    });
                }
            })
        });
    });
</script>
@endpush
