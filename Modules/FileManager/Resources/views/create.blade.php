@extends('layouts.backend')

@push('css')
    <link rel="stylesheet" href="{{ asset('assets/backend/plugins/flatpickr/flatpickr.min.css') }}">
    <style>
        .h1 {
            letter-spacing: -0.02em;
        }

        .dropzone {
            overflow-y: auto;
            border: 0;
            background: transparent;
        }

        .dz-preview {
            width: 100%;
            margin: 0 !important;
            height: 100%;
            padding: 15px;
            position: absolute !important;
            top: 0;
        }

        .dz-photo {
            height: 100%;
            width: 100%;
            overflow: hidden;
            border-radius: 12px;
            background: #eae7e2;
        }

        .dz-drag-hover .dropzone-drag-area {
            border-style: solid;
            border-color: #86b7fe;
            ;
        }

        .dz-thumbnail {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        .dz-image {
            width: 90px !important;
            height: 90px !important;
            border-radius: 6px !important;
        }

        .dz-remove {
            display: none !important;
        }

        .progress {
            width: 300px;
            border: 1px solid #ddd;
            padding: 5px;
        }

        .progress-bar {
            width: 0%;
            height: 20px;
            background-color: #4CAF50;
        }

        .dz-delete {
            width: 24px;
            height: 24px;
            background: rgba(0, 0, 0, 0.57);
            position: absolute;
            opacity: 0;
            transition: all 0.2s ease;
            top: 30px;
            right: 30px;
            border-radius: 100px;
            z-index: 9999;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .dz-delete>svg {
            transform: scale(0.75);
            cursor: pointer;
        }

        .dz-preview:hover .dz-delete,
        .dz-preview:hover .dz-remove-image {
            opacity: 1;
        }

        .dz-message {
            height: 100%;
            margin: 0 !important;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .dropzone-drag-area {
            height: 300px;
            position: relative;
            padding: 0 !important;
            border-radius: 10px;
            border: 3px dashed #dbdeea;
        }

        .was-validated .form-control:valid {
            border-color: #dee2e6 !important;
            background-image: none;
        }
    </style>
@endpush
@section('content')
    <div class="page-wrapper">
        <div class="content container-fluid">
            <!-- Page Header -->
            <div class="page-header">
                <div class="row align-items-center">
                    <div class="col">
                        <h3 class="page-title">{{ __trans('Create FileManager') }}</h3>
                        <ul class="breadcrumb">
                            <li class="breadcrumb-item"><a
                                    href="{{ route('backend.dashboard') }}">{{ __trans('dashboard') }}</a></li>
                            <li class="breadcrumb-item"><a
                                    href="{{ route('backend.filemanager.index') }}">{{ __trans('File Manager') }}</a></li>
                            <li class="breadcrumb-item active">{{ __trans('Create FileManager') }}</li>
                        </ul>
                    </div>
                    <div class="col-auto">
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col-sm-12">
                    <div class="card card-table">
                        <div class="card-body">
                            {{-- <form id="upload-form" action="{{ route('backend.filemanager.store') }}" datatable="true"
                                method="POST" enctype="multipart/form-data" redirect> --}}
                            <form class="dropzone overflow-visible p-0" id="formDropzone" method="POST"
                                enctype="multipart/form-data" novalidate>
                                @csrf
                                <div class="modal-body">
                                    <!-- <div class="col-md-10"> -->
                                    <div class="row">
                                        <div class="col-md-6">
                                            <label>Department</label>
                                            <select class="form-control" name="branch_id" id="branch_id" required>
                                                <option value="">Select Department</option>
                                                @foreach ($departments as $department)
                                                    <option value="{{ $department->id }}">{{ $department->name }}</option>
                                                @endforeach
                                            </select>
                                            @error('branch_id')
                                                <span class="text-danger">{{ $message }}</span>
                                            @enderror
                                        </div>
                                        <div class="col-md-6">
                                            <div class="mb-3">
                                                <label for="title" class="form-label">Title</label>
                                                <input type="text" name="title" class="form-control" required
                                                    placeholder="Title">
                                                @error('title')
                                                    <span class="text-danger">{{ $message }}</span>
                                                @enderror
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="mb-3">
                                                <label for="comment" class="form-label">Comment</label>
                                                <textarea name="comment" id="comment" class="form-control" required cols="30" rows="6"></textarea>
                                                @error('comment')
                                                    <span class="text-danger">{{ $message }}</span>
                                                @enderror
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="mb-3">
                                                <label for="issue_date" class="form-label">issue_date</label>
                                                <input type="text" name="issue_date" class="form-control datepicker"
                                                    required>
                                                @error('issue_date')
                                                    <span class="text-danger">{{ $message }}</span>
                                                @enderror
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="mb-3">
                                                <label for="expiry_date" class="form-label">Expire Date</label>
                                                <input type="text" name="expiry_date" class="form-control datepicker"
                                                    required>
                                                @error('expiry_date')
                                                    <span class="text-danger">{{ $message }}</span>
                                                @enderror
                                            </div>
                                            <div class="mb-3">
                                                <label for="expiry_days" class="form-label">Notification Days</label>
                                                <input type="number" id="quantity" name="expiry_days" required
                                                    min="0" max="100" class="form-control">
                                                @error('expiry_days')
                                                    <span class="text-danger">{{ $message }}</span>
                                                @enderror
                                            </div>
                                        </div>



                                        <div class="col-md-12">
                                            <label for="">File Upload & PDF</label>
                                            <div class="form-group mb-4">
                                                <!-- <label class="form-label text-muted opacity-75 fw-medium" for="formImage">Image</label> -->
                                                <div class="dropzone-drag-area form-control" id="previews">
                                                    <div class="dz-message text-muted opacity-50" data-dz-message>
                                                        <span>Drag file here to upload</span>
                                                    </div>
                                                    <div class="d-none" id="dzPreviewContainer">
                                                        <div class="dz-preview dz-file-preview">
                                                            <div class="dz-photo">
                                                                <img class="dz-thumbnail" data-dz-thumbnail>
                                                            </div>
                                                            <button class="dz-delete border-0 p-0" type="button"
                                                                data-dz-remove>
                                                                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24"
                                                                    id="times">
                                                                    <path fill="#FFFFFF"
                                                                        d="M13.41,12l4.3-4.29a1,1,0,1,0-1.42-1.42L12,10.59,7.71,6.29A1,1,0,0,0,6.29,7.71L10.59,12l-4.3,4.29a1,1,0,0,0,0,1.42,1,1,0,0,0,1.42,0L12,13.41l4.29,4.3a1,1,0,0,0,1.42,0,1,1,0,0,0,0-1.42Z">
                                                                    </path>
                                                                </svg>
                                                            </button>
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="invalid-feedback fw-bold">Please upload an image.</div>
                                            </div>
                                        </div>
                                        <!-- </div> -->
                                    </div>
                                    <div class="modal-footer">
                                        <a href="{{ url()->previous() }}" class="btn btn-secondary waves-effect">Cancel</a>
                                        <button class="btn btn-primary fw-medium" id="formSubmit" type="submit"><span
                                                class="spinner-border spinner-border-sm d-none me-2"
                                                aria-hidden="true"></span>Submit</button>
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
    <link rel="stylesheet" href="{{ asset('assets/backend/plugins/flatpickr/flatpickr.min.css') }}">
@endpush
@push('scripts')
<script src="{{ asset('assets/backend/plugins/flatpickr/flatpickr.min.js') }}"></script>
<script>
    flatpickr("input.datepicker", {
        dateFormat: "Y-m-d",
    });

    Dropzone.autoDiscover = false;

    /**
     * Setup dropzone
     */
    $('#formDropzone').dropzone({
        previewTemplate: $('#dzPreviewContainer').html(),
        url: "{{ route('backend.filemanager.store') }}",
        addRemoveLinks: true,
        autoProcessQueue: false,
        uploadMultiple: false,
        parallelUploads: 1,
        maxFiles: 1,
        thumbnailWidth: 900,
        thumbnailHeight: 600,
        previewsContainer: "#previews",
        maxFilesize: 1024,
        timeout: 0,
        init: function() {
            myDropzone = this;
            this.on("uploadprogress", function(file, progress) {

            });
            // when file is dragged in
            this.on('addedfile', function(file) {
                $('.dropzone-drag-area').removeClass('is-invalid').next('.invalid-feedback').hide();
            });

            this.on("error", function(file, errorMessage) {

            });
        },
        success: function(file, response) {
            console.log(response);
            showAlert(response.message, "success");
            setTimeout(function() {
                window.location.href = '{{ route('backend.filemanager.index') }}'
            }, 600);
        },
        error: function(file, errorMessage, xhr) {
        if (xhr.status === 422) {
            console.log(xhr.responseText); 
            var response = JSON.parse(xhr.responseText);
            handleValidationErrors(response.error);
        }
    }
    });

    /**
     * Form on submit
     */
    $('#formSubmit').on('click', function(event) {
        event.preventDefault();
        var $this = $(this);

        // show submit button spinner
        $this.children('.spinner-border').removeClass('d-none');

        // validate form
        var form = $('#formDropzone')[0];
        if (form.checkValidity() === false) {
            event.stopPropagation();

            // show error messages & hide button spinner    
            form.classList.add('was-validated');
            $this.children('.spinner-border').addClass('d-none');

            // if dropzone is empty show error message
            if (!myDropzone.getQueuedFiles().length > 0) {
                $('.dropzone-drag-area').addClass('is-invalid').next('.invalid-feedback').show();
            }
        } else {
            // if form is valid, submit the form
            // Perform AJAX submission or let Dropzone handle it based on your needs
            // Here, we're letting Dropzone handle the form submission
            myDropzone.processQueue();
        }
    });

    function handleValidationErrors(errors) {
    // Loop through each validation error and display it next to its corresponding field
    $.each(errors, function(key, value) {
        var input = document.querySelector('[name="' + key + '"]');

        if (!input.nextElementSibling || input.nextElementSibling.tagName.toLowerCase() !== 'span') {
            var errorMessageSpan = document.createElement('span');
            errorMessageSpan.classList.add('text-danger');
            input.parentNode.insertBefore(errorMessageSpan, input.nextElementSibling);
        }
        input.nextElementSibling.textContent = value[0];
        input.nextElementSibling.style.display = 'block';
    });
}

    // Function to clear validation errors
    function clearValidationErrors() {
        $('.is-invalid').removeClass('is-invalid');
        $('.invalid-feedback').hide();
    }

</script>

@endpush
