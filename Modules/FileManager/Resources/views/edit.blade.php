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
        .file-item {
            margin: 10px;
            max-width: 200px;
        }
        .file-item img {
            max-width: 100%;
            height: auto;
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
                    <h3 class="page-title">{{__trans('Edit FileManager')}}</h3>
                    <ul class="breadcrumb">
                        <li class="breadcrumb-item"><a href="{{route('backend.dashboard')}}">{{__trans('dashboard')}}</a></li>
                        <li class="breadcrumb-item"><a href="{{route('backend.filemanager.index')}}">{{__trans('File Manager')}}</a></li>
                        <li class="breadcrumb-item active">{{__trans('Edit FileManager')}}</li>
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
                        <form action="{{ route('backend.filemanager.fupdate', $filemanager) }}"  id="formDropzone" datatable="true" method="POST" class="ajax-form-submit reset" enctype="multipart/form-data" novalidate>
                            @csrf
                            
                            <div class="modal-body p-4">
                                <div class="row">
                                    <div class="col-md-6">
                                        <label>Department</label>
                                        <select class="form-control" name="branch_id" id="branch_id">
                                            <option value="">Select Department</option>
                                            @foreach ($departments as $department)
                                                <option 
                                                value="{{ $department->id }}" 
                                                @if($department->id == $filemanager->department_id) selected @endif
                                                >{{ $department->name }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label for="title" class="form-label">Title</label>
                                                <input type="text" name="title" class="form-control"  value="{{$filemanager->title}}" placeholder="Title">
                                        </div>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label for="comment" class="form-label">Comment</label>
                                            <textarea name="comment" id="comment" class="form-control" cols="30" rows="6">{{$filemanager->comment}}</textarea>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label for="issue_date" class="form-label">Issue Date</label>
                                            <input type="text" name="issue_date" class="form-control datepicker" value="{{$filemanager->issue_date}}" required>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label for="title" class="form-label">Expire Date</label>
                                                <input type="text" name="expiry_date" class="form-control datepicker" value="{{$filemanager->expiry_date}}" >
                                        </div>
                                        <div class="mb-3">
                                            <label for="title" class="form-label">Notification Days</label>
                                                <input type="number" id="quantity" name="expiry_days" min="0" max="100" class="form-control" value="{{$filemanager->expiry_days}}">
                                        </div>
                                    </div>
                                </div>
                                <div class="row">
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
                                </div>
                                <div class="row">
                                    <div class="col-md-12">
                                    <div class="file-preview">
                                        <!-- Display uploaded files here -->
                                            <div class="file-item">
                                                @if($extension =='png' || $extension =='jpg' || $extension =='jpeg' || $extension =='webp')
                                                    <img src="{{ $previewImage }}" alt="Image">
                                                @else
                                                    <i class="fa fa-file fa-fw" style="font-size:60px"></i> <!-- Use any file icon library or provide your own -->
                                                    <p>{{ $filemanager->file_name }}</p>
                                                @endif
                                            </div>
                                    </div>
                                    </div>
                                </div>
                            </div>
                                <!-- <div class="modal-footer">
                                    <button type="button" class="btn btn-secondary waves-effect" data-bs-dismiss="modal">{{__trans('close')}}</button>
                                    <button type="submit" class="btn btn-info waves-effect waves-light">{{__trans('save')}} </button>
                                </div> -->
                            <div class="modal-footer">
                                    <a href="{{url()->previous()}}" class="btn btn-secondary waves-effect">Cancel</a>
                                    <button class="btn btn-primary fw-medium" id="formSubmit" type="submit"><span class="spinner-border spinner-border-sm d-none me-2"aria-hidden="true"></span>Update</button>
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
            url: "{{ route('backend.filemanager.fupdate', $filemanager) }}",
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
                
                //let imgConf  = {name:'Screenshot.png',size: 5579};
                //myDropzone.files.push(imgConf);
                //myDropzone.displayExistingFile(imgConf, presignedUrl, null, '*');
                this.on("uploadprogress", function(file, progress) {
                   
                });
                // when file is dragged in
                this.on('addedfile', function(file) {
                    $('.dropzone-drag-area').removeClass('is-invalid').next('.invalid-feedback').hide();
                });

                this.on("error", function (file, errorMessage) {
                   console.log("file",file);
                   console.log("errorMessage",errorMessage);
                });
            },
            success: function(file, response) {
                // hide form and show success message
                // $('#formDropzone').fadeOut(600);
                showAlert(response.message, "success");
                setTimeout(function() {
                    window.location.href = '{{ route('backend.filemanager.index') }}'
                }, 600);
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
            if ($('#formDropzone')[0].checkValidity() === false) {
                event.stopPropagation();

                // show error messages & hide button spinner    
                $('#formDropzone').addClass('was-validated');
                $this.children('.spinner-border').addClass('d-none');
            } else {
                // if no files in queue, submit the form directly
                if (myDropzone.getQueuedFiles().length === 0) {
                    console.log("IFFFFFF");
                    $('#formDropzone').unbind('submit').submit();
                } else {
                    console.log("ELSEEEEEEEEEEEEE");
                    // submit the form with files via Dropzone
                    myDropzone.processQueue();
                }
            }
        });

        // $('#formSubmit').on('click', function(event) {
            
        //     event.preventDefault();
        //     var $this = $(this);

        //     // show submit button spinner
        //     $this.children('.spinner-border').removeClass('d-none');
            
        //     // validate form & submit if valid
        //     if ($('#formDropzone')[0].checkValidity() === false) {
        //         console.log("ssssss");
        //         event.stopPropagation();

        //         // show error messages & hide button spinner    
        //         $('#formDropzone').addClass('was-validated');
        //         $this.children('.spinner-border').addClass('d-none');

        //         // if dropzone is empty show error message
        //         if (!myDropzone.getQueuedFiles().length > 0) {
        //             $('.dropzone-drag-area').addClass('is-invalid').next('.invalid-feedback').show();
        //         }
        //     } else {
        //         console.log("formSubmit112");
        //         // if everything is ok, submit the form
        //         myDropzone.processQueue();
        //         console.log("formSubmit1123");
        //     }
        // });
    </script>
@endpush