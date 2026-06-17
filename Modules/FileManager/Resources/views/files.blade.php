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
            height: 100px;
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
                        </ul><br>
                       <h3>Branch:- </h3> {{$department->name}}
                    </div>
                    <div class="col-auto">
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col-sm-12">
                    <div class="card card-table">
                        <div class="card-body p-5">
                            {{-- <form id="upload-form" action="{{ route('backend.filemanager.store') }}" datatable="true"
                                method="POST" enctype="multipart/form-data" redirect> --}}
                                <form method="GET" action="{{ route('backend.filemanager.file', $department->id) }}" class="mb-3">
                                    <div class="input-group w-50">
                                        <input type="text" name="search" class="form-control" value="{{ request('search') }}" placeholder="Search by title, comment or file name">
                                        <button class="btn btn-primary" type="submit">Search</button>
                                    </div>
                                </form>
                                <form id="myForm" action="{{route('backend.filemanager.store')}}" method="POST" enctype="multipart/form-data">
                                @csrf
                                <div class=" table-responsive">
                                <table class="w-100 ">
                                <tbody id="section" >
                                <input type="hidden" name="branch_id" value="{{$department->id}}">
                                    @foreach($file_managers as $key => $list)
                                        <tr class="removeclass{{$key}}" id="removeclass">
                                            <td style="min-width: 200px;"><label for="title" class="form-label">Title</label>
                                                        <input type="hidden" name="file_id[]" value="{{$list->id}}">
                                                        <input type="text" id="title_{{$key}}" name="title[]" class="form-control"
                                                            placeholder="Title" value="{{$list->title ?? ''}}"></td>
                                            <td style="min-width: 200px;"> <label for="comment" class="form-label">Comment</label>
                                                        <textarea name="comment[]" id="comment_{{$key}}" class="form-control" cols="30" rows="1">{{$list->comment ?? ''}}</textarea></td>
                                            <td style="min-width: 200px;"><label for="issue_date" class="form-label">Issue Date</label>
                                                <input type="text" id="issue_date_{{$key}}" name="issue_date[]" value="{{$list->issue_date ?? ''}}" class="form-control datetime ">
                                            </td>
                                            <td style="min-width: 200px;"><label for="expiry_date" class="form-label">Expire Date</label>
                                                        <input type="text" id="expiry_date_{{$key}}" name="expiry_date[]" value="{{$list->expiry_date ?? ''}}" class="form-control datepicker"
                                                            ></td>
                                            <td style="min-width: 200px;"><label for="expiry_days" class="form-label">Notification Days</label>
                                                <input type="number"` name="expiry_days[]"
                                                    min="0" max="100" id="expiry_days_{{$key}}" class="form-control" value="{{$list->expiry_days ?? ''}}">
                                            </td>
                                            @can('Create FileManager')
                                            <td style="min-width: 200px;">
                                                <label for="file_{{$key}}" class="form-label">File Upload & PDF</label>
                                                <input type="file" id="file_{{$key}}" name="file[]" class="form-control">
                                                <input type="hidden" id="file_old_{{$key}}" value="{{$list->file_name ?? ''}}">
                                            </td>
                                            @endcan
                                            @php
                                                $assetPath = $list->file_path;
                                                $expiry = now()->addHour();
                                                // $previewImage = Storage::disk('s3')->temporaryUrl(
                                                //     $assetPath,
                                                //     $expiry,
                                                //     ['ResponseContentDisposition' => 'inline']
                                                // );
                                            @endphp
                                            @if($list->file_type != null)
                                            @can('Download FileManager')
                                            <td>
                                                <label for="" class="form-label text-white">.</label>
                                                <a href="{{ route('backend.filemanager.download', ['id' => $list->id]) }}" class="btn btn-warning form-control">{{$list->file_type}}</a>
                                            </td>
                                            @endcan
                                            @can('Download FileManager')
                                            <td>
                                                <label for="" class="form-label text-white">.</label>
                                                <a href="{{ route('backend.filemanager.download', ['id' => $list->id]) }}" class="text-white btn btn-danger form-control"><i class="fa fa-download"> Download</i></a>
                                            </td>
                                            @endcan
                                            @endif
                                            @can('Delete FileManager')
                                                <td style="min-width: 200px;">
                                                    <label for="expiry_days" class="form-label text-white" >.</label>
                                                    <a href="{{route('backend.filemanager.delete',$list->id)}}" type="button" class="btn btn-danger form-control text-white" onclick="return confirm('Are you sure you want to proceed?');">- Remove</a>
                                                </td>
                                            @endcan

                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                            <div class="col-md-2 ">
                                @can('Create FileManager')
                                    <div class="mb-3 ms-5 mt-3">
                                        <button type="button" class="btn btn-primary" onclick="addFields();">Add +</button>
                                    </div>
                                @endcan
                            </div>
                            <div class="modal-footer">
                                <a href="{{ url()->previous() }}" class="btn btn-secondary waves-effect">Cancel</a>
                                @can('Create FileManager')
                                    <button class="btn btn-primary fw-medium" id="submit" type="submit"><span
                                        class="spinner-border spinner-border-sm d-none me-2"
                                        aria-hidden="true"></span>Submit</button>
                                @endcan
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

function datepicker(){
    // Get today's date
    var today = new Date();
    // Calculate tomorrow's date
    var tomorrow = new Date(today);
    tomorrow.setDate(today.getDate() + 1);

    flatpickr("input.datepicker", {
        dateFormat: "Y-m-d",
        minDate: tomorrow,
    });
}

flatpickr("input.datetime", {
    enableTime: false,
    // minDate: "today",
    dateFormat: "Y-m-d",
});

var today = new Date();
    // Calculate tomorrow's date
    var tomorrow = new Date(today);
    tomorrow.setDate(today.getDate() + 1);

    flatpickr("input.datepicker", {
        dateFormat: "Y-m-d",
        minDate: tomorrow,
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
<script>
    @if(!empty($file_managers))
    $count = {{ count($file_managers) }};
        var room = $count - 1;
        var srNo = $count - 1;
    @else
        var room = 0;
        var srNo = 0;
    @endif

    function incrementSrNo() {
        srNo++;
    }

    function addFields() {
        if(checkvalidate(srNo) == true){
            incrementSrNo();
            room++;

            var objTo = document.getElementById('section');
            var tr = document.createElement("tr");
            tr.setAttribute("class", "removeclass" + room);
            tr.setAttribute("id", "removeclass" + room);

            var rdiv = 'removeclass' + room;

            tr.innerHTML = `<td style="min-width: 200px;"><label for="title" class="form-label">Title</label>
                                                <input type="text" id="title_${srNo}" name="title[]" class="form-control"
                                                    placeholder="Title" ></td>
                                    <td style="min-width: 200px;"> <label for="comment" class="form-label">Comment</label>
                                                <textarea name="comment[]" id="comment_${srNo}" class="form-control" cols="30" rows="1"></textarea></td>
                                    <td style="min-width: 200px;"><label for="issue_date" class="form-label">Issue Date</label>
                                                <input type="text" id="issue_date_${srNo}" name="issue_date[]" value="" class="form-control datetime"
                                                    ></td>
                                    <td style="min-width: 200px;"><label for="expiry_date" class="form-label">Expire Date</label>
                                                <input type="text" id="expiry_date_${srNo}" name="expiry_date[]" value="" class="form-control datepicker"
                                                    ></td>
                                    <td style="min-width: 200px;"><label for="expiry_days" class="form-label">Notification Days</label>
                                                <input type="number" name="expiry_days[]"
                                                    min="0" max="100" id="expiry_days_${srNo}" class="form-control" value=""></td>
                                    <td style="min-width: 200px;"> <label for="expiry_days" class="form-label">File Upload & PDF</label>
                                                <input type="file" id="file_${srNo}" name="file[]" class="form-control"><input type="hidden" id="file_old_${srNo}"></td>
                                    <td style="min-width: 200px;"><label for="expiry_days" class="form-label text-white" >.</label>
                                                    <button type="button" class="btn btn-danger form-control text-white" onclick="removeSection(${srNo})">- Remove</button></td>
                                    `;

            objTo.appendChild(tr);
            datepicker();
            // reinitialize Flatpickr on the new field
            flatpickr(`#issue_date_${srNo}`, {
                enableTime: false,
                dateFormat: "Y-m-d"
            });
        }
    }
function checkvalidate(maxSrNo) {
    var errorMessage = "";
    for (var i = 0; i <= maxSrNo; i++) {
         var elementcheck = document.querySelector('.removeclass' + i);
         if(elementcheck){
            var title = $('#title_' + i).val();
            var comment = $('#comment_' + i).val();
            var issue_date = $('#issue_date_' + i).val();
            var expiry_date = $('#expiry_date_' + i).val();
            var expiry_days = $('#expiry_days_' + i).val();
            var file = $('#file_' + i)[0];
            var file_old = $('#file_old_' + i).val();

            if (title == '') {
                errorMessage += "title field for Sr No " + i + " is empty or not a number.\n";
                $('#title_' + i).next('p.text-danger').remove(); // Remove existing error message if any
                $('#title_' + i).after("<p class='text-danger'>Required</p>");
            } else {
                $('#title_' + i).next('p.text-danger').remove(); // Remove existing error message if any
            }

            if (comment == '') {
                errorMessage += "comment field for Sr No " + i + " is empty or not a number.\n";
                $('#comment_' + i).next('p.text-danger').remove(); // Remove existing error message if any
                $('#comment_' + i).after("<p class='text-danger'>Required</p>");
            } else {
                $('#comment_' + i).next('p.text-danger').remove(); // Remove existing error message if any
            }

            {{--  if (issue_date == '') {
                errorMessage += "issue date field for Sr No " + i + " is empty or not a number.\n";
                $('#issue_date_' + i).next('p.text-danger').remove(); // Remove existing error message if any
                $('#issue_date_' + i).after("<p class='text-danger'>Required</p>");
            } else {
                $('#issue_date_' + i).next('p.text-danger').remove(); // Remove existing error message if any
            }  --}}
                
            if (expiry_date == '') {
                errorMessage += "expiry date field for Sr No " + i + " is empty or not a number.\n";
                $('#expiry_date_' + i).next('p.text-danger').remove(); // Remove existing error message if any
                $('#expiry_date_' + i).after("<p class='text-danger'>Required</p>");
            } else {
                $('#expiry_date_' + i).next('p.text-danger').remove(); // Remove existing error message if any
            }

            if (expiry_days == '') {
                errorMessage += "expiry days field for Sr No " + i + " is empty or not a number.\n";
                $('#expiry_days_' + i).next('p.text-danger').remove(); // Remove existing error message if any
                $('#expiry_days_' + i).after("<p class='text-danger'>Required</p>");
            } else if (parseFloat(expiry_days) < 0) {
                errorMessage += "expiry days field for Sr No " + i + " cannot be negative.\n";
                $('#expiry_days_' + i).next('p.text-danger').remove(); // Remove existing error message if any
                $('#expiry_days_' + i).after("<p class='text-danger'>Cannot be negative</p>");
            } else {
                $('#expiry_days_' + i).next('p.text-danger').remove(); // Remove existing error message if any
            }

            {{--  if (!file.files || file.files.length === 0 && file_old == '') {
                errorMessage += "No file selected for Sr No " + i + ".\n";
                $(file).next('p.text-danger').remove(); // Remove existing error message if any
                $(file).after("<p class='text-danger'>No file selected</p>");
            } else {
                $(file).next('p.text-danger').remove(); // Remove existing error message if any
            }  --}}


         }
    }

    if (errorMessage !== "") {
        $('#submit').attr('disabled', false);
        return false;
    }

    return true;
}

    function removeSection(rid) {
        var elementToRemove = document.querySelector('.removeclass' + rid);
        elementToRemove.parentNode.removeChild(elementToRemove);
    }

    $(document).ready(function(){
        $('#myForm').submit(function(event){
            event.preventDefault(); // Prevent the default form submission
            $('#submit').attr('disabled', true);
            if(checkvalidate(srNo) == true){
            // var formData = $(this).serialize(); // Serialize the form data
            var formData = new FormData($(this)[0]);
            formData.append('_token', '{{ csrf_token() }}');

            $.ajax({
                url: $(this).attr('action'), // Get the form action URL
                type: 'POST', // Get the form method
                data: formData, // Set the serialized data as the AJAX request data
                processData: false,
                contentType: false,
                headers: {
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },

                success: function(response){
                    if(response.success){
                        toastr.success(response.message);
                        setTimeout(function() {
                            window.location.href = '{{ route('backend.filemanager.index') }}'
                        }, 600);
                        // You can redirect or do any other actions here upon successful form submission
                    } else {
                        toastr.error(response.error);
                    }
                },
                error: function(){
                    toastr.error('An error occurred while submitting the form');
                }
            });
        }



        });
    });
</script>
@endpush
