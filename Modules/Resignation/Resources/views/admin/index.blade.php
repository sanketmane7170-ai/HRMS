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

    #resignation_module_container .form-control {
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

    /* Modern Badges */
    #resignation_module_container .badge {
        padding: 5px 12px;
        font-weight: 500;
        letter-spacing: 0.3px;
    }
    #resignation_module_container .badge-info { background-color: #e0f2fe; color: #0369a1; border: 1px solid #bae6fd; }
    #resignation_module_container .badge-success { background-color: #dcfce7; color: #15803d; border: 1px solid #bbf7d0; }
    #resignation_module_container .badge-danger { background-color: #fee2e2; color: #b91c1c; border: 1px solid #fecaca; }
    #resignation_module_container .badge-warning { background-color: #fef9c3; color: #a16207; border: 1px solid #fef08a; }
    #resignation_module_container .badge-secondary { background-color: #f3f4f6; color: #374151; border: 1px solid #e5e7eb; }
</style>
@endpush

@section('content')
<div id="resignation_module_container">
    <!-- DEBUG: VERIFYING FILE EDIT -->
    <div class="page-wrapper">
        <div class="content container-fluid">
            <!-- Page Header -->
            <div class="page-header mb-4">
                <div class="row align-items-center">
                    <div class="col">
                        <h3 class="page-title text-dark">Resignation Requests (Admin) 📝</h3>
                        <ul class="breadcrumb bg-transparent p-0">
                            <li class="breadcrumb-item"><a href="{{ route('backend.dashboard') }}" class="text-secondary">Dashboard</a></li>
                            <li class="breadcrumb-item active text-dark">All Resignations</li>
                        </ul>
                    </div>
                    <div class="col-auto float-right ml-auto">
                        <!-- Simplified trigger: removing redundant onclick/data-toggle to prevent double show -->
                        <button class="btn btn-primary btn-sm px-4" id="edit-policy-btn-new">
                            <i class="fas fa-edit mr-1"></i> Edit Policy & Notice Period
                        </button>
                    </div>
                </div>
            </div>
        <!-- /Page Header -->

        <!-- Modernized Widgets -->
        <div class="row">
            <div class="col-xl-4 col-sm-6 col-12">
                <div class="card overflow-hidden border-0 shadow-sm" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);">
                    <div class="card-body">
                        <div class="d-flex align-items-center">
                            <div class="bg-white-transparent p-3 rounded-circle text-white shadow-sm mr-3" style="background: rgba(255,255,255,0.2); margin-right: 20px !important;">
                                <i class="fas fa-file-alt fa-2x"></i>
                            </div>
                            <div class="text-white">
                                <h3 class="mb-0 text-white font-weight-bold" id="stat-total">0</h3>
                                <p class="mb-0 opacity-75">Total Resignations <span style="margin-left: 10px;">📂</span></p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-xl-4 col-sm-6 col-12">
                <div class="card overflow-hidden border-0 shadow-sm" style="background: linear-gradient(135deg, #f6d365 0%, #fda085 100%);">
                    <div class="card-body">
                        <div class="d-flex align-items-center">
                            <div class="bg-white-transparent p-3 rounded-circle text-white shadow-sm mr-3" style="background: rgba(255,255,255,0.2); margin-right: 20px !important;">
                                <i class="fas fa-clock fa-2x"></i>
                            </div>
                            <div class="text-white">
                                <h3 class="mb-0 text-white font-weight-bold" id="stat-pending">0</h3>
                                <p class="mb-0 opacity-75">Pending Review <span style="margin-left: 10px;">⏳</span></p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-xl-4 col-sm-6 col-12">
                <div class="card overflow-hidden border-0 shadow-sm" style="background: linear-gradient(135deg, #84fb48 0%, #20e3b2 100%);">
                    <div class="card-body">
                        <div class="d-flex align-items-center">
                            <div class="bg-white-transparent p-3 rounded-circle text-white shadow-sm mr-3" style="background: rgba(255,255,255,0.2); margin-right: 20px !important;">
                                <i class="fas fa-check-double fa-2x"></i>
                            </div>
                            <div class="text-white">
                                <h3 class="mb-0 text-white font-weight-bold" id="stat-approved">0</h3>
                                <p class="mb-0 opacity-75">Approved / Notice <span style="margin-left: 10px;">✅</span></p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <!-- /Widgets -->

        <div class="row">
            <div class="col-md-12">
                <div class="card card-table">
                    <div class="card-header">
                        <h4 class="card-title mb-0">Organization Resignations</h4>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-hover table-center mb-0" id="admin-table">
                                <thead class="thead-light">
                                    <tr>
                                        <th>Employee</th>
                                        <th>Reporting To</th>
                                        <th>Designation</th>
                                        <th>Location</th>
                                        <th>Applied Date</th>
                                        <th>Status</th>
                                        <th>Document</th>
                                        <th>Notice End</th>
                                        <th class="text-right">HR Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <!-- Populated via AJAX -->
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Admin Action Modal -->
<div id="admin_action_modal" class="modal custom-modal fade" role="dialog">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">HR Override / Action</h5>
                <!-- Removed the 'X' button as requested -->
            </div>
            <div class="modal-body">
                <form id="admin-action-form">
                    <input type="hidden" id="resignation-id" name="id">
                    
                    <div class="form-group">
                        <label>Employee Name: <span id="modal-employee-name" class="font-weight-bold"></span></label>
                    </div>

                    <div id="modal-doc-section" style="display:none;" class="mb-3">
                        <label class="d-block">Submitted Document:</label>
                        <a href="#" id="modal-download-link" target="_blank" class="btn btn-outline-info btn-sm">
                            <i class="fas fa-file-download mr-1"></i> Download Resignation Letter
                        </a>
                    </div>

                    <div class="form-group">
                        <label>Select Action <span class="text-danger">*</span></label>
                        <select class="form-control" name="action_type" id="action-select" required>
                            <option value="">Select...</option>
                            <option value="approve">Approve Resignation</option>
                            <option value="waive">Waive Notice Period (Immediate Release)</option>
                            <option value="update">Update Details (Keep Current Status)</option>
                            <option value="complete">Mark as Completed (FnF Done)</option>
                            <option value="hold">Hold Process</option>
                            <option value="reject">Reject Resignation</option>
                        </select>
                    </div>

                    <div id="date-section" style="display:none;">
                        <div class="form-group">
                            <label>Approved Last Working Day <span class="text-danger">*</span></label>
                            <input type="date" class="form-control" name="approved_last_working_date" id="approved-date">
                        </div>
                    </div>

                    <div class="form-group">
                        <label>Remarks / Settlement Notes <span class="text-danger">*</span></label>
                        <textarea class="form-control" name="comments" rows="3" required placeholder="Enter waive reason or settlement details..."></textarea>
                    </div>

                    <div class="submit-section">
                        <button type="submit" class="btn btn-danger submit-btn">Execute Action</button>
                        <button type="button" class="btn btn-secondary manual-close" data-dismiss="modal" data-bs-dismiss="modal">Close</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Policy Header Edit Modal -->
<div id="policy_edit_modal" class="modal custom-modal fade" role="dialog">
    <div class="modal-dialog modal-dialog-centered modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Edit Resignation Process Policy</h5>
                <!-- Removed the 'X' button as requested -->
            </div>
            <div class="modal-body">
                <form id="policy-form">
                    <div class="form-group">
                        <label>Notice Period (Days) <span class="text-danger">*</span></label>
                        <input type="number" class="form-control" name="notice_period" id="notice-period-input" required min="1">
                    </div>
                    <div class="form-group">
                        <label>Policy Text (Displayed to Employees)</label>
                        <textarea class="form-control" name="policy" id="policy-text" rows="10" required></textarea>
                    </div>
                    <div class="submit-section">
                        <button type="submit" class="btn btn-primary submit-btn">Save Policy</button>
                        <button type="button" class="btn btn-secondary manual-close" data-dismiss="modal" data-bs-dismiss="modal">Close</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    // Robust Policy Editor Trigger
    function openPolicyEditor() {
        $.ajax({
            url: "/api/v1/resignation/policy",
            method: "GET",
            success: function(response) {
                console.log('Policy Data:', response);
                $('#policy-text').val(response.policy);
                $('#notice-period-input').val(response.notice_period);
                $('#policy_edit_modal').modal('show');
            },
            error: function() {
                Swal.fire('Error', 'Failed to fetch policy. Please check connection.', 'error');
            }
        });
    }

    $(document).ready(function() {
        const baseUrl = "{{ url('/') }}";

        // ID based listener for reliability
        $(document).on('click', '#edit-policy-btn-new', function() {
            openPolicyEditor();
        });

        // Manual Modal Close Fallback
        $('.manual-close').click(function(){
            $(this).closest('.modal').modal('hide');
        });
        loadTable();

        function loadTable() {
            $.ajax({
                url: "/api/v1/resignation/all", 
                method: "GET",
                success: function(response) {
                    let rows = '';
                    let stats = { total: 0, pending: 0, approved: 0 };

                    response.data.forEach(function(item) {
                        stats.total++;
                        if(item.status === 'pending') stats.pending++;
                        if(item.status === 'approved') stats.approved++;

                        let statusBadge = '';
                        switch(item.status) {
                            case 'pending': statusBadge = '<span class="badge badge-info">Pending</span>'; break;
                            case 'approved': statusBadge = '<span class="badge badge-success">Approved</span>'; break;
                            case 'rejected': statusBadge = '<span class="badge badge-danger">Rejected</span>'; break;
                            case 'completed': statusBadge = '<span class="badge badge-dark">Completed</span>'; break;
                            case 'withdrawn': statusBadge = '<span class="badge badge-secondary">Withdrawn</span>'; break;
                            default: statusBadge = '<span class="badge badge-light">'+item.status+'</span>';
                        }

                        let noticeEnd = item.notice_period ? new Date(item.notice_period.end_date).toLocaleDateString() : '-';
                        if(item.notice_period && item.notice_period.status === 'waived') noticeEnd += ' <span class="badge badge-warning">Waived</span>';

                        rows += `
                            <tr>
                                <td>
                                    <h2 class="table-avatar">
                                        <a href="#">${item.employee?.name || 'Unknown'}</a>
                                    </h2>
                                </td>
                                <td>${item.reporting_manager_name || 'N/A'}</td>
                                <td>${item.designation_name || '-'}</td>
                                <td>${item.office_location || '-'}</td>
                                <td>${new Date(item.applied_date).toLocaleDateString()}</td>
                                <td>${statusBadge}</td>
                                <td>${item.signed_document ? `<a href="${baseUrl}/storage/${item.signed_document}" target="_blank" class="text-info"><i class="fas fa-file-pdf"></i> View</a>` : '-'}</td>
                                <td>${noticeEnd}</td>
                                <td class="text-right">
                                    <button class="btn btn-sm btn-primary open-action-modal" 
                                            data-data='${JSON.stringify(item)}'
                                            data-toggle="modal" data-target="#admin_action_modal"
                                            data-bs-toggle="modal" data-bs-target="#admin_action_modal">
                                        Manage / Edit
                                    </button>
                                </td>
                            </tr>
                        `;
                    });
                    $('#admin-table tbody').html(rows);

                    // Update Stats
                    $('#stat-total').text(stats.total);
                    $('#stat-pending').text(stats.pending);
                    $('#stat-approved').text(stats.approved);
                },
                error: function() {
                     $('#admin-table tbody').html('<tr><td colspan="8" class="text-center text-danger">Failed to load data.</td></tr>');
                }
            });
        }

        $(document).on('click', '.open-action-modal', function() {
            const data = $(this).data('data');
            $('#resignation-id').val(data.id);
            $('#modal-employee-name').text(data.employee?.name);
            
            // Reset modal state
            $('#action-select').val('');
            $('#date-section').hide();
            $('#approved-date').val(data.preferred_last_working_date ? data.preferred_last_working_date.split('T')[0] : '');
            $('#approved-date').prop('required', false);
            
            // Handle Document Section in Modal
            if (data.signed_document) {
                const docUrl = `${baseUrl}/storage/${data.signed_document}`;
                $('#modal-download-link').attr('href', docUrl);
                $('#modal-doc-section').show();
            } else {
                $('#modal-doc-section').hide();
            }
            
            $('#admin_action_modal').modal('show');
        });

        $('#action-select').change(function() {
            if($(this).val() === 'approve' || $(this).val() === 'waive' || $(this).val() === 'update' || $(this).val() === 'complete') {
                $('#date-section').show();
            } else {
                $('#date-section').hide();
                $('#approved-date').prop('required', false);
            }
        });

        $('#admin-action-form').submit(function(e) {
            e.preventDefault();
            const id = $('#resignation-id').val();
            const formData = $(this).serialize();

            $.ajax({
                url: "/api/v1/resignation/" + id + "/action",
                method: "POST",
                data: formData,
                headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') },
                success: function(response) {
                    $('#admin_action_modal').modal('hide');
                    Swal.fire('Success', 'Admin action executed successfully', 'success');
                    loadTable();
                },
                error: function(xhr) {
                    Swal.fire('Error', xhr.responseJSON?.message || 'Failed', 'error');
                }
            });
        });

        // Policy Edit Logic - now handled by global openPolicyEditor function
        // $('#edit-policy-btn').click(function() {
        //     // Fetch current policy
        //     $.ajax({
        //         url: "{{ url('api/v1/resignation/policy') }}",
        //         method: "GET",
        //         success: function(response) {
        //             $('#policy-text').val(response.policy);
        //             $('#notice-period-input').val(response.notice_period);
        //             $('#policy_edit_modal').modal('show');
        //         }
        //     });
        // });

        $('#policy-form').submit(function(e) {
             e.preventDefault();
             $.ajax({
                url: "/api/v1/resignation/policy",
                method: "POST",
                data: $(this).serialize(),
                headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') },
                success: function(response) {
                    $('#policy_edit_modal').modal('hide');
                    Swal.fire('Success', 'Policy updated successfully', 'success');
                }
             });
        });
    });
</script>
@endpush
