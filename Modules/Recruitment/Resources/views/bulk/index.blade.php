@extends('layouts.backend')

@section('content')
<div class="page-wrapper bg-white">
    <div class="content container-fluid">
        <!-- Page Header -->
        <div class="page-header border-0 pb-0 mb-5">
            <div class="row align-items-center">
                <div class="col">
                    <h4 class="page-title text-dark font-weight-bold mb-1">{{ __trans('bulk_operations_center') }}</h4>
                    <p class="text-muted small mb-0">{{ __trans('manage_candidates_at_scale_with_precision') }}</p>
                </div>
                <div class="col-auto">
                    <div class="d-flex align-items-center gap-3">
                        <a href="{{ route('recruitment.applications.index') }}" class="btn btn-sm btn-light px-3 fw-bold shadow-none" style="border-radius: 8px;">
                            <i class="fas fa-list me-2"></i> {{ __trans('standard_view') }}
                        </a>
                        <button type="button" class="btn btn-sm btn-dark px-4 fw-bold shadow-none" id="btn-process-bulk" disabled style="border-radius: 8px; transition: all 0.3s ease;">
                            <i class="fas fa-bolt me-2 text-warning"></i> {{ __trans('execute_batch') }}
                        </button>
                    </div>
                </div>
            </div>
        </div>
        <!-- /Page Header -->

        <div class="row">
            <!-- Left Side: Interactive Table -->
            <div class="col-xl-8 col-lg-7">
                <div class="card border border-light shadow-none mb-4" style="border-radius: 16px;">
                    <div class="card-header bg-transparent border-0 py-4 px-4 d-flex justify-content-between align-items-center">
                        <h6 class="m-0 fw-black text-dark text-uppercase ls-1">{{ __trans('candidate_selection') }}</h6>
                        <div class="form-check m-0 d-flex align-items-center">
                            <input class="form-check-input border-secondary custom-checkbox" type="checkbox" id="select-all">
                            <label class="form-check-label smaller fw-bold text-muted ms-2 mt-1 cursor-pointer" for="select-all">
                                {{ __trans('select_all') }}
                            </label>
                        </div>
                    </div>
                    <div class="card-body p-0">
                        <!-- Advanced Search Integration -->
                        <div class="px-4 py-3 bg-light-subtle border-top border-bottom border-light">
                            <div class="input-group input-group-sm border-0 bg-white px-3 py-1 shadow-none" style="border-radius: 10px; border: 1px solid #f3f4f6 !important;">
                                <span class="input-group-text bg-white border-0 text-muted pe-2">
                                    <i class="fas fa-search smaller"></i>
                                </span>
                                <input type="text" id="custom-search" class="form-control border-0 ps-0 smaller fw-medium shadow-none" placeholder="{{ __trans('filter_by_name_job_or_status') }}...">
                            </div>
                        </div>

                        <div class="table-responsive">
                            <table class="table table-hover align-middle mb-0" id="bulk-applications-table" style="width: 100%;">
                                <thead class="bg-light-subtle text-muted smaller text-uppercase fw-bold ls-1">
                                    <tr>
                                        <th class="ps-4 py-3 border-0" width="50"></th>
                                        <th class="py-3 border-0">{{ __trans('candidate') }}</th>
                                        <th class="py-3 border-0">{{ __trans('position') }}</th>
                                        <th class="text-center py-3 border-0">{{ __trans('current_stage') }}</th>
                                        <th class="text-end pe-4 py-3 border-0">{{ __trans('applied_on') }}</th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white">
                                    <!-- Dynamic Table Content -->
                                </tbody>
                            </table>
                        </div>
                    </div>
                    <div class="card-footer bg-transparent border-0 py-4 px-4">
                        <div class="d-flex justify-content-between align-items-center">
                            <div id="selection-info" class="text-muted smaller fw-bold d-flex align-items-center">
                                <span id="selected-count-badge" class="badge bg-dark text-white me-2 px-2 py-1" style="border-radius: 4px;">0</span>
                                <span id="selection-text">{{ __trans('candidates_selected_for_batch') }}</span>
                            </div>
                            <div id="pagination-container"></div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Right Side: Workflow Control Center -->
            <div class="col-xl-4 col-lg-5">
                <div class="sticky-top" style="top: 100px;">
                    <div class="card border border-light shadow-none" style="border-radius: 16px;">
                        <div class="card-header bg-transparent border-0 py-4 px-4">
                            <h6 class="m-0 fw-black text-dark text-uppercase ls-1">{{ __trans('workflow_actions') }}</h6>
                        </div>
                        <div class="card-body bg-white px-4 pb-4">
                            <form id="bulk-action-form">
                                <div class="mb-4">
                                    <label class="form-label smaller fw-bold text-muted text-uppercase ls-1 mb-3">{{ __trans('identify_operation') }}</label>
                                    <select name="action" class="form-select border-0 bg-light shadow-none fw-semibold p-3" id="bulk-action-select" style="border-radius: 12px;">
                                        <option value="">{{ __trans('select_a_high_level_action') }}...</option>
                                        <optgroup label="{{ __trans('pipeline_status') }}">
                                            <option value="update_stage">{{ __trans('advance_to_next_stage') }}</option>
                                            <option value="bulk_reject">{{ __trans('reject_applications') }}</option>
                                        </optgroup>
                                        <optgroup label="{{ __trans('coordination') }}">
                                            <option value="assign_interviewer">{{ __trans('assign_point_of_contact') }}</option>
                                            <option value="send_emails">{{ __trans('dispatch_communications') }}</option>
                                        </optgroup>
                                        <optgroup label="{{ __trans('intelligence') }}">
                                            <option value="export_data">{{ __trans('export_batch_intelligence') }}</option>
                                        </optgroup>
                                    </select>
                                </div>

                                <div id="action-details" class="d-none transition-all">
                                    <div class="py-2 mb-4 border-top border-light"></div>

                                    <!-- Pipeline Stage -->
                                    <div id="field-stage" class="action-field d-none mb-4">
                                        <label class="form-label smaller fw-bold text-dark text-uppercase ls-1 mb-2">{{ __trans('destination_stage') }}</label>
                                        <select name="stage" class="form-select border-0 bg-light shadow-none p-3" style="border-radius: 12px;">
                                            @foreach(\Modules\Recruitment\Entities\Application::getStages() as $stage)
                                                <option value="{{ $stage }}">{{ ucfirst(str_replace('_', ' ', $stage)) }}</option>
                                            @endforeach
                                        </select>
                                    </div>

                                    <!-- Lead Assignment -->
                                    <div id="field-interviewer" class="action-field d-none mb-4">
                                        <label class="form-label smaller fw-bold text-dark text-uppercase ls-1 mb-2">{{ __trans('assignee') }}</label>
                                        <select name="interviewer_id" class="form-select border-0 bg-light shadow-none p-3" style="border-radius: 12px;">
                                            <option value="">{{ __trans('choose_team_member') }}...</option>
                                            @foreach(\App\Models\User::all() as $user)
                                                <option value="{{ $user->id }}">{{ $user->name }}</option>
                                            @endforeach
                                        </select>
                                    </div>

                                    <!-- Narrative/Protocol -->
                                    <div id="field-notes" class="action-field mb-4">
                                        <label class="form-label smaller fw-bold text-dark text-uppercase ls-1 mb-2">{{ __trans('internal_narrative') }}</label>
                                        <textarea name="notes" class="form-control border-0 bg-light shadow-none p-3" rows="5" style="border-radius: 12px;" placeholder="{{ __trans('add_strategic_notes_here') }}..."></textarea>
                                    </div>

                                    <div class="form-check form-switch mb-4 ps-5">
                                        <input class="form-check-input shadow-none" type="checkbox" id="notify-check" name="notify_candidates" checked>
                                        <label class="form-check-label smaller fw-bold text-muted mt-1" for="notify-check">
                                            {{ __trans('automate_candidate_notifications') }}
                                        </label>
                                    </div>
                                </div>
                                
                                <div class="bg-light-subtle p-3 border-0 d-flex align-items-start mb-0" style="border-radius: 12px;">
                                    <i class="fas fa-shield-alt mt-1 me-2 text-dark opacity-50 small"></i>
                                    <span class="smaller fw-medium text-muted lh-base">{{ __trans('bulk_actions_are_irreversible_please_verify_selection') }}</span>
                                </div>
                            </form>
                        </div>
                    </div>

                    <!-- Batch History -->
                    <div class="card border border-light shadow-none mt-4" style="border-radius: 16px;">
                        <div class="card-header bg-transparent border-0 py-4 px-4">
                            <h6 class="m-0 fw-black text-dark text-uppercase ls-1 smaller opacity-50">{{ __trans('recent_batch_executions') }}</h6>
                        </div>
                        <div class="card-body p-0 bg-white">
                            <div id="recent-bulk-logs" class="list-group list-group-flush smaller">
                                <div class="list-group-item text-center py-5 text-muted border-0 bg-transparent">
                                    <i class="fas fa-history d-block mb-3 opacity-25 fa-2x"></i>
                                    {{ __trans('no_recent_executions_recorded') }}
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
    @import url('https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;600;700;800&display=swap');
    
    .page-wrapper.bg-white { 
        background-color: #fff !important; 
        font-family: 'Plus Jakarta Sans', sans-serif;
    }
    .fw-black { font-weight: 800; }
    .ls-1 { letter-spacing: 0.05em; }
    .smaller { font-size: 0.75rem; }
    .text-dark { color: #111827 !important; }
    .text-muted { color: #6b7280 !important; }
    .border-light { border-color: #f3f4f6 !important; }
    .bg-light { background-color: #f9fafb !important; }
    .bg-light-subtle { background-color: #f9fafb !important; }
    .lh-base { line-height: 1.5; }
    
    .custom-checkbox {
        width: 1.25rem;
        height: 1.25rem;
        border-radius: 6px !important;
        cursor: pointer;
    }
    .custom-checkbox:checked {
        background-color: #111827;
        border-color: #111827;
    }
    
    #bulk-applications-table thead th { border: none !important; }
    .table-hover tbody tr:hover { background-color: #f9fafb !important; transition: background 0.2s ease; }
    
    .btn-dark { background-color: #111827; border-color: #111827; }
    .btn-dark:hover { background-color: #1f2937; border-color: #1f2937; transform: translateY(-1px); }
    .btn-dark:disabled { background-color: #e5e7eb; border-color: #e5e7eb; color: #9ca3af !important; }
    
    .cursor-pointer { cursor: pointer; }
    .transition-all { transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1); }
</style>

@push('scripts')
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css"/>
<script>
    $(document).ready(function() {
        // High-Fidelity DataTable Initialization
        const table = $('#bulk-applications-table').DataTable({
            processing: true,
            serverSide: true,
            dom: 'tip',
            ajax: {
                url: "{{ route('recruitment.applications.index') }}",
                data: function(d) {
                    d.source = 'bulk_operations';
                }
            },
            columns: [
                {data: 'checkbox', name: 'checkbox', orderable: false, searchable: false, className: 'ps-4'},
                {data: 'candidate', name: 'candidate'},
                {data: 'job_title', name: 'job_title'},
                {data: 'stage_badge', name: 'stage_badge', className: 'text-center'},
                {data: 'applied_date', name: 'applied_date', className: 'text-end pe-4'}
            ],
            language: {
                paginate: {
                    first: '<i class="fas fa-angle-double-left smaller"></i>',
                    last: '<i class="fas fa-angle-double-right smaller"></i>',
                    previous: '<i class="fas fa-chevron-left smaller"></i>',
                    next: '<i class="fas fa-chevron-right smaller"></i>'
                }
            },
            drawCallback: function(settings) {
                // Style the standard DataTables pagination to fit our premium design
                $('.dataTables_paginate').appendTo('#pagination-container').addClass('m-0');
                $('.paginate_button').addClass('btn btn-sm btn-white border border-light mx-1 shadow-none fw-bold').css('border-radius', '8px');
                $('.paginate_button.current').addClass('btn-dark text-white border-dark').removeClass('btn-white border-light');
                updateSelectionState();
            }
        });

        // Refined Search Logic
        $('#custom-search').keyup(function(){
            table.search($(this).val()).draw();
        });

        // Batch Selection Logic
        $('#select-all').on('click', function() {
            $('.application-checkbox').prop('checked', this.checked);
            updateSelectionState();
        });

        $(document).on('change', '.application-checkbox', function() {
            updateSelectionState();
            const allChecked = $('.application-checkbox:checked').length === $('.application-checkbox').length && $('.application-checkbox').length > 0;
            $('#select-all').prop('checked', allChecked);
        });

        function updateSelectionState() {
            const count = $('.application-checkbox:checked').length;
            $('#selected-count-badge').text(count);
            $('#btn-process-bulk').prop('disabled', count === 0 || !$('#bulk-action-select').val());
            
            if (count > 0) {
                $('#selection-info').addClass('text-dark').removeClass('text-muted');
                $('#selected-count-badge').addClass('animate__animated animate__bounceIn');
            } else {
                $('#selection-info').removeClass('text-dark').addClass('text-muted');
                $('#selected-count-badge').removeClass('animate__animated animate__bounceIn');
            }
        }

        // Action Trigger Orchestration
        $('#bulk-action-select').on('change', function() {
            const action = $(this).val();
            if (action) {
                $('#action-details').removeClass('d-none').addClass('animate__animated animate__fadeInUp');
                $('.action-field').addClass('d-none');
                
                if (action === 'update_stage') $('#field-stage').removeClass('d-none');
                if (action === 'assign_interviewer') $('#field-interviewer').removeClass('d-none');
                
                $('#field-notes').removeClass('d-none');
            } else {
                $('#action-details').addClass('d-none');
            }
            updateSelectionState();
        });

        // Execution Protocol
        $('#btn-process-bulk').on('click', function() {
            const applicationIds = $('.application-checkbox:checked').map(function() { return $(this).val(); }).get();
            const formData = new FormData($('#bulk-action-form')[0]);
            formData.append('application_ids', JSON.stringify(applicationIds));

            Swal.fire({
                title: "<span class='fw-black text-dark'>{{ __trans('execute_batch_operation') }}</span>",
                html: "<p class='smaller text-muted'>{{ __trans('you_are_about_to_apply_this_action_to') }} <span class='fw-black text-dark'>:count</span> {{ __trans('candidates') }}.</p>".replace(':count', applicationIds.length),
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#111827',
                cancelButtonColor: '#fff',
                confirmButtonText: "{{ __trans('confirm_and_execute') }}",
                cancelButtonText: "<span class='text-dark fw-bold'>{{ __trans('cancel') }}</span>",
                reverseButtons: true,
                padding: '2.5rem',
                customClass: {
                    popup: 'rounded-4 border-0 shadow-lg',
                    confirmButton: 'btn btn-dark px-4 py-2 rounded-3 fw-bold',
                    cancelButton: 'btn btn-light border border-light px-4 py-2 rounded-3 fw-bold ms-3'
                }
            }).then((result) => {
                if (result.isConfirmed) {
                    executeBatch(formData);
                }
            });
        });

        function executeBatch(formData) {
            Swal.fire({
                title: "{{ __trans('executing_protocol') }}...",
                allowOutsideClick: false,
                didOpen: () => { Swal.showLoading(); }
            });

            $.ajax({
                url: "{{ route('recruitment.bulk.process') }}",
                method: "POST",
                data: formData,
                processData: false,
                contentType: false,
                headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') },
                success: function(response) {
                    if (response.success) {
                        Swal.fire({
                            title: "<span class='fw-black text-dark'>{{ __trans('batch_success') }}</span>",
                            text: response.message,
                            icon: 'success',
                            confirmButtonColor: '#111827',
                            customClass: {
                                popup: 'rounded-4 border-0 shadow-lg',
                                confirmButton: 'btn btn-dark px-5 py-2 rounded-3 fw-bold'
                            }
                        });
                        table.ajax.reload();
                        $('#bulk-action-select').val('').trigger('change');
                    } else {
                        Swal.fire("{{ __trans('execution_error') }}", response.message, 'error');
                    }
                },
                error: function(xhr) {
                    Swal.fire("{{ __trans('critical_failure') }}", "{{ __trans('failed_to_sync_with_server_protocol') }}", 'error');
                }
            });
        }
    });
</script>
@endpush
@endsection
