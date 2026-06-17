@extends('layouts.backend')

@section('title', 'Probation Ending List')

@section('content')
<div class="page-wrapper">
    <div class="content container-fluid">
        <!-- Page Header -->
        <div class="page-header">
            <div class="row align-items-center">
                <div class="col">
                    <h3 class="page-title">{{__trans('probation_ending_soon')}}</h3>
                    <ul class="breadcrumb">
                        <li class="breadcrumb-item"><a href="{{route('backend.dashboard')}}">{{__trans('dashboard')}}</a></li>
                        <li class="breadcrumb-item active">{{__trans('probation_ending_soon')}}</li>
                    </ul>
                </div>
                <div class="col-auto">
                    <button type="button" class="btn btn-sm btn-info" onclick="$('#helpModal').modal('show')">
                        <i class="fas fa-question-circle"></i> Help
                    </button>
                </div>
            </div>
        </div>
        <!-- /Page Header -->

        <div class="row">
            <div class="col-sm-12">
                <div class="card card-table">
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table text-center table-hover" id="dataTable">
                                <thead class="thead-light">
                                    <tr>
                                        <th>#</th>
                                        <th>{{__trans('employee')}}</th>
                                        <th>{{__trans('probation_end_date')}}</th>
                                        <th>{{__trans('department')}}</th>
                                        <th>{{__trans('actions')}}</th>
                                    </tr>
                                </thead>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Probation Confirmation Modal -->
<div class="modal fade" id="probationConfirmModal" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Probation Confirmation - <span id="employeeName"></span></h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            
            <!-- FORM submits to probation.upload route -->
            <form id="probationConfirmForm" action="{{ route('backend.analytic.probation.upload') }}" method="POST" enctype="multipart/form-data">
                @csrf
                <input type="hidden" name="user_id" id="modal_user_id">
                
                <div class="modal-body">
                    <!-- METHOD SELECTION: Upload DOCX or Use Template -->
                    <div class="form-group">
                        <label class="d-block">{{__trans('confirmation_method') ?: 'Confirmation Method'}}</label>
                        <div class="custom-control custom-radio custom-control-inline">
                            <input type="radio" id="methodUpload" name="method" class="custom-control-input" value="upload" checked>
                            <label class="custom-control-label" for="methodUpload">{{__trans('upload_docx') ?: 'Upload Docx'}}</label>
                        </div>
                        <div class="custom-control custom-radio custom-control-inline">
                            <input type="radio" id="methodTemplate" name="method" class="custom-control-input" value="template">
                            <label class="custom-control-label" for="methodTemplate">{{__trans('select_template') ?: 'Select Template'}}</label>
                        </div>
                    </div>

                    <!-- CONFIRMATION DATE PICKER -->
                    <div class="form-group mb-3">
                        <label for="confirmation_date">Confirmation Date <span class="text-danger">*</span></label>
                        <div class="cal-icon">
                            <input type="date" name="confirmation_date" id="confirmation_date" class="form-control" value="{{ date('Y-m-d') }}" required>
                        </div>
                    </div>

                    <!-- UPLOAD FIELD (shown by default) -->
                    <div id="uploadField" class="form-group">
                        <label for="docx_file">{{__trans('upload_docx_file') ?: 'Upload Docx File'}}</label>
                        <input type="file" name="docx_file" id="docx_file" class="form-control" accept=".docx">
                    </div>

                    <!-- TEMPLATE FIELD (hidden by default) -->
                    <div id="templateField" class="form-group d-none">
                        <label for="template_id">{{__trans('select_template') ?: 'Select Template'}}</label>
                        <select name="template_id" id="template_id" class="form-control">
                            <option value="">-- {{__trans('choose_template') ?: 'Choose Template'}} --</option>
                            @foreach($templates as $template)
                                <option value="{{ $template->id }}">{{ $template->name }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
                
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">{{__trans('close') ?: 'Close'}}</button>
                    <button type="submit" class="btn btn-primary">{{__trans('confirm') ?: 'Confirm'}}</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Help Modal -->
<div class="modal fade" id="helpModal" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><i class="fas fa-info-circle"></i> Probation Confirmation Workflow Guide</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <h6 class="font-weight-bold mb-3">How to Confirm Probation</h6>
                <ol class="mb-4">
                    <li class="mb-2"><strong>Click "Confirm" button</strong> for the employee whose probation is ending</li>
                    <li class="mb-2"><strong>Choose confirmation method:</strong>
                        <ul>
                            <li><strong>Upload Docx:</strong> Upload your own custom probation letter in DOCX format</li>
                            <li><strong>Select Template:</strong> Use a pre-saved template from the system</li>
                        </ul>
                    </li>
                    <li class="mb-2"><strong>Select confirmation date</strong> (defaults to today)</li>
                    <li class="mb-2"><strong>Click "Confirm"</strong> to proceed to the letter editor</li>
                    <li class="mb-2"><strong>Edit the letter</strong> in the rich text editor if needed</li>
                    <li class="mb-2"><strong>Choose final action:</strong>
                        <ul>
                            <li><strong>Save Only:</strong> Save the letter to database</li>
                            <li><strong>Download PDF:</strong> Save and download as PDF</li>
                            <li><strong>Email:</strong> Save and send via email to employee</li>
                        </ul>
                    </li>
                </ol>

                <hr>

                <h6 class="font-weight-bold mb-3">Action Buttons Explained</h6>
                <div class="table-responsive">
                    <table class="table table-bordered text-dark">
                        <thead class="thead-light">
                            <tr>
                                <th>Button</th>
                                <th>Description</th>
                                <th>When Visible</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td><button class="btn btn-sm btn-outline-primary" disabled><i class="fas fa-check-circle"></i> Confirm</button></td>
                                <td>Start the probation confirmation process</td>
                                <td>Always visible for all employees</td>
                            </tr>
                            <tr>
                                <td><button class="btn btn-sm btn-outline-info" disabled><i class="fas fa-download"></i></button></td>
                                <td>Download the probation letter as PDF</td>
                                <td>After letter has been created</td>
                            </tr>
                            <tr>
                                <td><button class="btn btn-sm btn-outline-success" disabled><i class="fas fa-paper-plane"></i></button></td>
                                <td>Send probation letter via email (Green = Not sent yet)</td>
                                <td>After letter has been created</td>
                            </tr>
                            <tr>
                                <td><button class="btn btn-sm btn-outline-danger" disabled><i class="fas fa-paper-plane"></i></button></td>
                                <td>Resend probation letter via email (Red = Already sent)</td>
                                <td>After email has been sent once</td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <div class="alert alert-warning mt-3">
                    <i class="fas fa-exclamation-triangle"></i> <strong>Note:</strong> When the email button is red, clicking it will ask for confirmation before resending the email.
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

@endsection

@push('scripts')
<script type="text/javascript">
    // DATATABLE INITIALIZATION
    var table = $('#dataTable').DataTable({
        processing: true,
        serverSide: true,
        ajax: {
            url: "{{route('backend.analytic.probation.upcoming.list')}}",
        },
        columns: [
            { data: 'DT_RowIndex', name: 'id' },
            { data: 'name', name: 'name' },
            { data: 'workDetail.probation_end_date', name: 'workDetail.probation_end_date' },
            { data: 'department.name', name: 'department.name' },
            { data: 'action', name: 'action', orderable: false, searchable: false }
        ]
    });

    // CLICK HANDLER: When "Confirm" button is clicked
    $(document).on('click', '.confirm-probation', function() {
        var id = $(this).data('id');
        var name = $(this).data('name');
        $('#modal_user_id').val(id);        // Set user ID in hidden field
        $('#employeeName').text(name);      // Display employee name in modal title
        $('#probationConfirmModal').modal('show');  // Show the modal
    });

    // TOGGLE FIELDS: Switch between Upload and Template
    $('input[name="method"]').on('change', function() {
        if ($(this).val() === 'upload') {
            $('#uploadField').removeClass('d-none');
            $('#templateField').addClass('d-none');
        } else {
            $('#uploadField').addClass('d-none');
            $('#templateField').removeClass('d-none');
        }
    });

    // DATE FORMAT CONVERSION: Convert Y-m-d to d-m-Y before submit
    $('#probationConfirmForm').on('submit', function(e) {
        var dateInput = $('#confirmation_date');
        if (dateInput.val()) {
            var date = new Date(dateInput.val());
            var day = String(date.getDate()).padStart(2, '0');
            var month = String(date.getMonth() + 1).padStart(2, '0');
            var year = date.getFullYear();
            
            // Create hidden input with formatted date (d-m-Y)
            $('<input>').attr({
                type: 'hidden',
                name: 'confirmation_date',
                value: day + '-' + month + '-' + year
            }).appendTo(this);
            
            // Disable the original input so it doesn't submit
            dateInput.prop('disabled', true);
        }
    });

    // EMAIL CONFIRMATION: Show confirmation dialog if email already sent
    $(document).on('click', '.send-email-btn', function(e) {
        var emailSent = $(this).data('email-sent');
        
        console.log('Email button clicked, emailSent:', emailSent, typeof emailSent);
        
        if (emailSent === true || emailSent === 'true' || emailSent === '1') {
            e.preventDefault();
            var href = $(this).attr('href');
            
            if (confirm('Email has already been sent to this candidate. Do you want to resend it?')) {
                window.location.href = href;
            }
        }
    });

    // Refresh DataTable if coming back from email send
    @if(session('success'))
        setTimeout(function() {
            table.ajax.reload(null, false);
        }, 1000);
    @endif

</script>
@endpush
