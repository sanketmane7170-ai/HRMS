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
    /* Ensure borders are visible */
    #resignation_module_container .form-control {
        border: 1px solid #ced4da !important;
        background-color: #ffffff !important;
        color: #000000 !important;
    }
    #resignation_module_container .form-control:focus {
        border-color: #80bdff !important;
        box-shadow: none !important;
    }
    #resignation_module_container .page-wrapper {
        background: #f7f7f7;
    }
    #resignation_module_container .text-dark {
        color: #000000 !important;
    }
    #resignation_module_container .table td {
        color: #000000 !important;
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
                        <h3 class="page-title text-dark">Team Resignations 👥</h3>
                        <ul class="breadcrumb bg-transparent p-0">
                            <li class="breadcrumb-item"><a href="{{ route('backend.dashboard') }}" class="text-secondary">Dashboard</a></li>
                            <li class="breadcrumb-item active text-dark">Team Resignations</li>
                        </ul>
                    </div>
                </div>
            </div>
            <!-- /Page Header -->

            <div class="row">
                <div class="col-md-12">
                    <div class="card border shadow-sm bg-white">
                        <div class="card-body p-0">
                            <div class="table-responsive">
                                <table class="table table-hover table-nowrap custom-table mb-0" id="manager-table">
                                    <thead class="thead-light">
                                        <tr>
                                            <th class="text-dark font-weight-bold">Employee</th>
                                            <th class="text-dark font-weight-bold">Branch</th> <!-- Author: Sanket - Renamed from Department -->
                                            <th class="text-dark font-weight-bold">Applied Date</th>
                                            <th class="text-dark font-weight-bold">Reason</th>
                                            <th class="text-dark font-weight-bold">Preferred Last Day</th>
                                            <th class="text-dark font-weight-bold">Status</th>
                                            <th class="text-right text-dark font-weight-bold">Action</th>
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

    <!-- Action Modal -->
    <div id="action_modal" class="modal custom-modal fade" role="dialog">
        <div class="modal-dialog modal-dialog-centered" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Take Action</h5>
                    <!-- Removed the 'X' button as requested -->
                </div>
                <div class="modal-body">
                    <form id="action-form">
                        <input type="hidden" id="resignation-id" name="id">
                        
                        <div class="form-group">
                            <label>Action <span class="text-danger">*</span></label>
                            <select class="form-control" name="action_type" id="action-select" required>
                                <option value="">Select Action</option>
                                <option value="approve">Approve</option>
                                <option value="reject">Reject</option>
                                <option value="hold">Hold</option>
                            </select>
                        </div>

                        <div id="date-section" style="display:none;">
                            <div class="form-group">
                                <label>Approved Last Working Day <span class="text-danger">*</span></label>
                                <input type="date" class="form-control" name="approved_last_working_date" id="approved-date">
                            </div>
                        </div>

                        <div class="form-group">
                            <label>Remarks <span class="text-danger">*</span></label>
                            <textarea class="form-control" name="comments" rows="3" required></textarea>
                        </div>

                        <div class="submit-section">
                            <button type="submit" class="btn btn-primary submit-btn">Submit</button>
                            <button type="button" class="btn btn-secondary manual-close" data-dismiss="modal" data-bs-dismiss="modal">Close</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    $(document).ready(function() {
        // Manual Modal Close Fallback
        $('.manual-close').click(function(){
            $(this).closest('.modal').modal('hide');
        });
        loadTable();

        function loadTable() {
            $.ajax({
                url: "/api/v1/resignation/team", 
                method: "GET",
                success: function(response) {
                    let rows = '';
                    if(response.data.length === 0) {
                        rows = '<tr><td colspan="7" class="text-center">No resignations found for your team.</td></tr>';
                    } else {
                        response.data.forEach(function(item) {
                            let statusBadge = '';
                            switch(item.status) {
                                case 'pending': statusBadge = '<span class="badge badge-info">Pending</span>'; break;
                                case 'approved': statusBadge = '<span class="badge badge-success">Approved</span>'; break;
                                case 'rejected': statusBadge = '<span class="badge badge-danger">Rejected</span>'; break;
                                case 'withdrawn': statusBadge = '<span class="badge badge-secondary">Withdrawn</span>'; break;
                                default: statusBadge = '<span class="badge badge-dark">'+item.status+'</span>';
                            }

                            rows += `
                                <tr>
                                    <td>
                                        <h2 class="table-avatar">
                                            <a href="#">${item.employee?.name || 'Unknown'}</a>
                                        </h2>
                                    </td>
                                    <td>${item.employee?.department?.name || 'N/A'}</td>
                                    <td>${new Date(item.applied_date).toLocaleDateString()}</td>
                                    <td>${item.reason}</td>
                                    <td>${new Date(item.approved_last_working_date || item.preferred_last_working_date).toLocaleDateString()}</td> <!-- Author: Sanket - Show approved date, fallback to preferred -->
                                    <td>${statusBadge}</td>
                                    <td class="text-right">
                                        ${item.status === 'pending' ? `
                                        <button class="btn btn-sm btn-primary open-action-modal" 
                                            data-id="${item.id}" 
                                            data-date="${item.preferred_last_working_date}">
                                            Action
                                        </button>` : '-'}
                                    </td>
                                </tr>
                            `;
                        });
                    }
                    $('#manager-table tbody').html(rows);
                },
                error: function() {
                     $('#manager-table tbody').html('<tr><td colspan="7" class="text-center text-danger">Failed to load data.</td></tr>');
                }
            });
        }

        $(document).on('click', '.open-action-modal', function() {
            const id = $(this).data('id');
            const date = $(this).data('date');
            $('#resignation-id').val(id);
            // Default to preferred date
            $('#approved-date').val(date); 
            $('#action_modal').modal('show');
        });

        $('#action-select').change(function() {
            if($(this).val() === 'approve') {
                $('#date-section').show();
                $('#approved-date').prop('required', true);
            } else {
                $('#date-section').hide();
                $('#approved-date').prop('required', false);
            }
        });

        $('#action-form').submit(function(e) {
            e.preventDefault();
            const id = $('#resignation-id').val();
            const formData = $(this).serialize();

            $.ajax({
                url: "/api/v1/resignation/" + id + "/action",
                method: "POST",
                data: formData,
                headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') },
                success: function(response) {
                    $('#action_modal').modal('hide');
                    Swal.fire('Success', 'Action taken successfully', 'success');
                    loadTable();
                },
                error: function(xhr) {
                    Swal.fire('Error', xhr.responseJSON?.message || 'Failed', 'error');
                }
            });
        });
    });
</script>
@endpush
