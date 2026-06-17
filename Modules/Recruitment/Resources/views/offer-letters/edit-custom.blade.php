@extends('layouts.backend')

@section('title')
    Edit Custom Offer Letter
@endsection

@section('content')
<div class="page-wrapper">
    <div class="content container-fluid">
        <!-- Page Header -->
        <div class="page-header">
            <div class="row align-items-center">
                <div class="col">
                    <h3 class="page-title">Edit Custom Offer Letter</h3>
                    <ul class="breadcrumb">
                        <li class="breadcrumb-item"><a href="{{ route('backend.dashboard') }}">Dashboard</a></li>
                        <li class="breadcrumb-item"><a href="{{ route('recruitment.offers.index') }}">Offers</a></li>
                        <li class="breadcrumb-item active">Edit Letter</li>
                    </ul>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-md-12">
                <div class="card shadow-lg border-0" style="border-radius: 15px;">
                    <div class="card-header text-white" style="background: linear-gradient(135deg, #1e3a8a 0%, #3b82f6 100%); border-radius: 15px 15px 0 0; padding: 1.5rem;">
                        <div class="d-flex justify-content-between align-items-center">
                            <h4 class="card-title mb-0 fw-bold">
                                <i class="fas fa-file-contract me-2"></i> Custom Letter for: {{ $application->candidate_name }}
                            </h4>
                            <div class="badge bg-light text-primary px-3 py-2">
                                {{ $application->job->title ?? 'N/A' }}
                            </div>
                        </div>
                    </div>
                    <div class="card-body p-0">
                        <form id="saveLetterForm" action="{{ route('recruitment.offer-letters.store') }}" method="POST">
                            @csrf
                            <input type="hidden" name="application_id" value="{{ $application->id }}">
                            <input type="hidden" name="candidate_name" value="{{ $application->candidate_name }}">
                            <input type="hidden" name="job_title" value="{{ $application->job->title ?? 'N/A' }}">
                            <input type="hidden" name="department" value="{{ $application->job->department->name ?? ($application->job->department ?? 'N/A') }}">
                            <!-- Hidden defaults for schema requirements -->
                            <input type="hidden" name="currency" value="USD">
                            <input type="hidden" name="salary_amount" value="0">
                            <input type="hidden" name="payment_period" value="Month">
                            <input type="hidden" name="pay_frequency" value="Monthly">
                            <input type="hidden" name="start_date" value="{{ date('Y-m-d') }}">
                            <input type="hidden" name="expiration_date" value="{{ date('Y-m-d', strtotime('+7 days')) }}">
                            
                            <!-- Editor Toolbar -->
                            <div class="editor-toolbar-top d-flex justify-content-between align-items-center p-3 border-bottom bg-light">
                                <a href="{{ route('recruitment.offer-letters.selection') }}?application_id={{ $application->id }}" class="btn btn-outline-secondary">
                                    <i class="fas fa-arrow-left me-1"></i> Back to Selection
                                </a>
                                <div class="btn-group gap-2">
                                    <button type="button" class="btn btn-outline-info px-4" id="btnSaveTemplate">
                                        <i class="fas fa-save me-1"></i> Save as Template
                                    </button>
                                    <button type="submit" class="btn btn-primary px-4 fw-bold">
                                        <i class="fas fa-check-circle me-1"></i> Finalize & Save Offer
                                    </button>
                                </div>
                            </div>

                            <!-- Editor Area -->
                            <div class="editor-container p-4" style="background: #cbd5e1; display: flex; justify-content: center; min-height: calc(100vh - 250px); overflow-y: auto;">
                                <div class="a4-page shadow-lg bg-white" style="width: 210mm; min-height: 297mm; position: relative;">
                                    <div id="editor_loading" style="position: absolute; top: 0; left: 0; right: 0; bottom: 0; display: flex; align-items: center; justify-content: center; background: rgba(255,255,255,0.9); z-index: 10;">
                                        <div class="text-center">
                                            <div class="spinner-border text-primary" role="status"></div>
                                            <p class="mt-2 fw-bold text-primary">Initializing Editor...</p>
                                        </div>
                                    </div>
                                    <textarea id="offer_editor" name="content" style="width: 100%; min-height: 297mm;">
                                        {!! $htmlContent !!}
                                    </textarea>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@push('css')
<link rel="stylesheet" href="{{ asset('assets/backend/plugins/richtexteditor/rte_theme_default.css') }}" />
<style>
    .a4-page {
        box-shadow: 0 0 20px rgba(0,0,0,0.15);
    }
    .rte-modern.rte-desktop {
        border: none !important;
    }
    .rte-toolbar {
        position: sticky !important;
        top: 0 !important;
        z-index: 100 !important;
        background: #f8f9fa !important;
        border-bottom: 1px solid #e2e8f0 !important;
    }
</style>
@endpush

@push('scripts')
<script src="{{ asset('assets/backend/plugins/richtexteditor/rte.js') }}"></script>
<script src="{{ asset('assets/backend/plugins/richtexteditor/plugins/all_plugins.js') }}"></script>
<script>
    var editor;
    
    function initEditor() {
        if (typeof RichTextEditor === "undefined") {
            setTimeout(initEditor, 500);
            return;
        }

        try {
            editor = new RichTextEditor("#offer_editor", {
                skin: "rounded-corner",
                toolbar: "full",
                height: "297mm",
                width: "100%",
                editorResizeMode: "none",
                url_base: "{{ asset('assets/backend/plugins/richtexteditor') }}",
            });
            $('#editor_loading').fadeOut(400, function() { $(this).remove(); });
        } catch (e) {
            console.error("Editor initialization failed:", e);
        }
    }

    $(document).ready(function() {
        initEditor();

        $('#btnSaveTemplate').on('click', function() {
            var templateName = prompt("Enter a name for this template:");
            if (templateName) {
                var content = editor ? editor.getHTMLCode() : $('#offer_editor').val();
                $.ajax({
                    url: "{{ route('recruitment.offer-letters.store-template') }}",
                    method: "POST",
                    data: {
                        _token: "{{ csrf_token() }}",
                        name: templateName,
                        content: content
                    },
                    success: function(response) {
                        if (response.success) {
                            alert(response.message);
                        } else {
                            alert('Error: ' + response.message);
                        }
                    },
                    error: function() {
                        alert('An error occurred while saving the template.');
                    }
                });
            }
        });

        $('#saveLetterForm').on('submit', function(e) {
            e.preventDefault();
            
            if (editor) {
                $('#offer_editor').val(editor.getHTMLCode());
            }

            var $form = $(this);
            var $submitBtn = $form.find('button[type="submit"]');
            var originalBtnText = $submitBtn.html();

            // Disable button and show loading
            $submitBtn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin me-1"></i> Saving...');

            $.ajax({
                url: $form.attr('action'),
                method: "POST",
                data: $form.serialize(),
                success: function(response) {
                    if (response.success) {
                        toastr.success(response.message);
                        if (response.redirect) {
                            setTimeout(function() {
                                window.location.href = response.redirect;
                            }, 1500);
                        }
                    } else {
                        toastr.error(response.message || 'An error occurred.');
                        $submitBtn.prop('disabled', false).html(originalBtnText);
                    }
                },
                error: function(xhr) {
                    $submitBtn.prop('disabled', false).html(originalBtnText);
                    var errorMessage = 'An error occurred while saving.';
                    if (xhr.status === 422 && xhr.responseJSON && xhr.responseJSON.errors) {
                        var errors = xhr.responseJSON.errors;
                        errorMessage = 'Validation failed: ' + Object.values(errors).flat().join(' ');
                    } else if (xhr.responseJSON && xhr.responseJSON.message) {
                        errorMessage = xhr.responseJSON.message;
                    }
                    toastr.error(errorMessage);
                }
            });
        });
    });
</script>
@endpush
@endsection
