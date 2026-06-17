@extends('layouts.backend')

@section('title')
    Edit Probation Confirmation Letter
@endsection

@section('content')
<div class="page-wrapper">
    <div class="content container-fluid">
        <!-- Page Header -->
        <div class="page-header">
            <div class="row align-items-center">
                <div class="col">
                    <h3 class="page-title">Edit Confirmation Letter</h3>
                    <ul class="breadcrumb">
                        <li class="breadcrumb-item"><a href="{{ route('backend.dashboard') }}">Dashboard</a></li>
                        <li class="breadcrumb-item"><a href="{{ route('backend.analytic.probation.upcoming.list') }}">Probation List</a></li>
                        <li class="breadcrumb-item active">Edit Letter</li>
                    </ul>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-md-12">
                <div class="card shadow-lg">
                    <div class="card-header bg-primary text-white">
                        <h4 class="card-title mb-0">Confirming Probation for: {{ $user->name }}</h4>
                    </div>
                    <div class="card-body">
                        <form id="saveLetterForm" action="{{ route('backend.analytic.probation.save') }}" method="POST">
                            @csrf
                            <input type="hidden" name="user_id" value="{{ $user->id }}">
                            <input type="hidden" name="action" id="formAction" value="save">
                            
                            <!-- Top Toolbar -->
                            <div class="editor-toolbar-top">
                                <a href="{{ route('backend.analytic.probation.upcoming.list') }}" class="btn btn-link mr-auto text-muted">
                                    <i class="fas fa-arrow-left"></i> Back
                                </a>
                                <button type="button" class="btn btn-premium btn-save-template" id="btnSaveTemplate">
                                    <i class="fas fa-copy"></i> Save as Template
                                </button>
                                <div class="btn-group">
                                    <button type="button" class="btn btn-premium btn-primary" onclick="submitConfirmation('save')">
                                        <i class="fas fa-save"></i> Save Only
                                    </button>
                                    <button type="button" class="btn btn-premium btn-info" onclick="submitConfirmation('pdf')">
                                        <i class="fas fa-file-pdf"></i> Download PDF
                                    </button>
                                </div>
                            </div>

                            <!-- Editor Page -->
                            <div class="editor-container">
                                <div class="a4-page shadow" id="editor_parent">
                                    <div id="editor_loading" style="position: absolute; top: 0; left: 0; right: 0; bottom: 0; display: flex; align-items: center; justify-content: center; background: rgba(255,255,255,0.9); z-index: 10;">
                                        <div class="text-center">
                                            <div class="spinner-border text-primary" role="status"></div>
                                            <p class="mt-2" style="font-weight: 500; color: var(--primary-dark);">Initializing Editor...</p>
                                        </div>
                                    </div>
                                    <textarea id="letter_editor" name="content" style="width: 100%; min-height: 257mm;">
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
@endsection

@push('css')
<style>
    @import url('https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600&display=swap');

    :root {
        --primary-dark: #0f172a;
        --secondary-dark: #1e293b;
        --accent-blue: #3b82f6;
    }

    body {
        font-family: 'Inter', sans-serif;
        background-color: #f8fafc;
    }

    .page-wrapper {
        background: #f1f5f9;
        min-height: 100vh;
    }

    .card {
        border: none;
        border-radius: 12px;
        overflow: hidden;
        margin-bottom: 80px; /* Space for sticky footer */
    }

    .card-header.bg-primary {
        background: linear-gradient(135deg, var(--primary-dark) 0%, var(--secondary-dark) 100%) !important;
        padding: 20px 30px;
    }

    .card-body {
        padding: 0 !important;
    }

    .editor-toolbar-top {
        padding: 12px 24px;
        background: #ffffff;
        border-bottom: 1px solid #e2e8f0;
        display: flex;
        justify-content: flex-end;
        align-items: center;
        gap: 12px;
    }

    .editor-container {
        padding: 40px 20px;
        background: #cbd5e1;
        display: flex;
        justify-content: center;
        min-height: calc(100vh - 200px);
        overflow-y: auto;
    }

    .a4-page {
        width: 210mm;
        min-height: 297mm;
        background: white;
        box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.25);
        position: relative;
        transition: transform 0.3s ease;
    }

    .footer-actions {
        display: flex;
        gap: 12px;
    }

    .btn-premium {
        font-weight: 600;
        letter-spacing: 0.3px;
        padding: 10px 24px;
        border-radius: 8px;
        transition: all 0.2s ease;
        display: inline-flex;
        align-items: center;
        gap: 8px;
    }

    .btn-premium:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 12px rgba(0,0,0,0.2);
    }

    .btn-save-template {
        background: #f1f5f9;
        color: #475569;
        border: 1px solid #e2e8f0;
    }

    .btn-save-template:hover {
        background: #e2e8f0;
        color: #1e293b;
    }

    /* Override RTE Styles */
    .rte-modern.rte-desktop {
        border: none !important;
    }
    
    .rte-modern .rte-toolbar {
        background: #fafafa !important;
        border-bottom: 1px solid #eee !important;
        padding: 10px 20px !important;
        position: sticky !important;
        top: 0 !important;
        z-index: 100 !important;
    }

    /* COMPREHENSIVE FIX FOR ALL RTE TEXT VISIBILITY - Author: Sanket */
    /* Force all RTE elements to have dark text on light backgrounds */
    
    /* Global RTE text color override */
    div[class*="rte"],
    div[id*="rte"],
    span[class*="rte"],
    button[class*="rte"],
    select[class*="rte"],
    option[class*="rte"],
    label[class*="rte"],
    a[class*="rte"] {
        color: #1e293b !important;
    }

    /* All nested elements within RTE components */
    div[class*="rte"] *,
    div[id*="rte"] * {
        color: #1e293b !important;
    }

    /* Specific toolbar fixes */
    .rte-toolbar,
    .rte-toolbar *,
    .rte-tb,
    .rte-tb * {
        color: #1e293b !important;
        background-color: transparent;
    }

    /* Dropdown and popup fixes */
    .rte-popup,
    .rte-popup *,
    .rte-menu,
    .rte-menu *,
    .rte-dropdown,
    .rte-dropdown *,
    .rte-dropdown-menu,
    .rte-dropdown-menu * {
        color: #1e293b !important;
        background: #ffffff !important;
    }

    /* List and formatting menus */
    .rte-list,
    .rte-list *,
    .rte-format,
    .rte-format * {
        color: #1e293b !important;
    }

    /* Hover states */
    div[class*="rte"]:hover,
    button[class*="rte"]:hover {
        background: #f1f5f9 !important;
        color: #0f172a !important;
    }

    /* Ensure backgrounds are light */
    div[class*="rte-popup"],
    div[class*="rte-menu"],
    div[class*="rte-dropdown"] {
        background: #ffffff !important;
        border: 1px solid #e2e8f0 !important;
    }

    #editor_loading {
        border-radius: 4px;
    }
</style>
@endpush

@push('scripts')
<script>
    var editor;
    var initRetries = 0;
    
    function initEditor() {
        console.log("Attempting to initialize RichTextEditor... Attempt: " + (initRetries + 1));
        
        if (typeof RichTextEditor === "undefined") {
            initRetries++;
            if (initRetries < 20) { // Limit retries to 10 seconds
                setTimeout(initEditor, 500);
            } else {
                console.error("RichTextEditor failed to load after 20 attempts.");
                $('#editor_loading').html('<div class="text-danger">Failed to load editor script. Please refresh the page.</div>');
            }
            return;
        }

        try {
            editor = new RichTextEditor("#letter_editor", {
                skin: "rounded-corner",
                toolbar: "full",
                height: "850px",
                width: "100%",
                editorResizeMode: "none",
                url_base: "{{ asset('assets/backend/plugins/richtexteditor') }}",
                contentCssUrl: "{{ asset('assets/backend/plugins/richtexteditor/runtime/richtexteditor_content.css') }}"
            });
            $('#editor_loading').fadeOut(400, function() { $(this).remove(); });
            $('#letter_editor').css('visibility', 'visible').css('border', 'none'); 
            console.log("RichTextEditor successfully initialized");
        } catch (e) {
            console.error("Failed to initialize RichTextEditor constructor:", e);
            $('#editor_loading').html('<div class="text-danger">Error initializing editor: ' + e.message + '</div>');
        }
    }

    // Using window.onload to ensure all assets are fully loaded and parsed
    window.onload = function() {
        initEditor();
    };

    function submitConfirmation(action) {
        console.log("Submitting probation confirmation with action: " + action);
        
        var form = $('#saveLetterForm');
        if (form.length === 0) {
            alert("DEBUG ERROR: Form #saveLetterForm not found in DOM!");
            return;
        }
        
        $('#formAction').val(action);
        $('#js_execution').val('success');
        
        var content = "";
        if (editor) {
            content = editor.getHTMLCode();
            $('#letter_editor').val(content);
            console.log("Editor content synced. Length: " + content.length);
        } else {
            content = $('#letter_editor').val();
            console.log("Editor not found, using raw textarea content. Length: " + content.length);
        }
        
        if (!content || content.length < 10) {
            if (!confirm("The letter content seems very short or empty. Do you want to proceed?")) {
                return;
            }
        }
        
        console.log("Triggering form submit now...");
        form.submit();
    }

    $(document).ready(function() {
        // Keeping the delegation as backup, but using onclick for primary reliability
        $(document).on('click', '.btn-submit-action-legacy', function() {
            var action = $(this).data('action');
            submitConfirmation(action);
        });

        $('#btnSaveTemplate').on('click', function() {
            var templateName = prompt("Enter a name for this template:");
            if (templateName) {
                var content = editor ? editor.getHTMLCode() : $('#letter_editor').val();
                $.ajax({
                    url: "{{ route('backend.analytic.probation.template.store') }}",
                    method: "POST",
                    data: {
                        _token: "{{ csrf_token() }}",
                        name: templateName,
                        content: content
                    },
                    success: function(response) {
                        if (response.success) {
                            Swal.fire({
                                icon: 'success',
                                title: 'Saved!',
                                text: response.message,
                                timer: 2000,
                                showConfirmButton: false
                            });
                        } else {
                            Swal.fire('Error', response.message, 'error');
                        }
                    },
                    error: function(xhr) {
                        Swal.fire('Error', 'An error occurred while saving the template.', 'error');
                    }
                });
            }
        });
    });
</script>
@endpush
