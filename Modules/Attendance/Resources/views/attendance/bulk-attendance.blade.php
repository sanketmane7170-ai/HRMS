<div class="modal-dialog modal-lg">
    <div class="modal-content">
        <div class="modal-header">
            <h4 class="modal-title">{{__trans('bulk_attendance')}}</h4>
            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <form action="{{route('backend.attendance.sampleDownload')}}" method="POST" class="ajax-form-submit">
            @csrf
            <div class="modal-body p-4">
                <div class="row">
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label for="user_ids" class="form-label">{{__trans('select_employee')}}</label>
                            <select name="user_ids[]" class="form-control ajax-select2" data-target="{{ route('ajax.select2.fetch.userswithall') }}" multiple>
                                <option value="">{{ __trans('search_employee ...') }}</option>
                                <option value="all">{{__trans('all')}}</option>
                            </select>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label for="start_date" class="form-label">{{__trans('clock_out_date')}}</label>
                            <input type="text" id="start_date" class="form-control datepicker" name="start_date" placeholder="{{__trans('start_date')}}">
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label for="end_date" class="form-label">{{__trans('clock_out_date')}}</label>
                            <input type="text" id="end_date" class="form-control datepicker" name="end_date" placeholder="{{__trans('end_date')}}">
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label class="form-label">Upload Excel File</label>
                            <input type="file" name="file" class="form-control attendance-file" accept=".xlsx">
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary waves-effect" data-bs-dismiss="modal">{{__trans('close')}}</button>
                <button data-url="{{ route('backend.attendance.updateBulkUserAttendance') }}" class="btn me-1 import-data" style="background-color: #702c81; color:white;">
                    <i class="fa fa-file-excel"></i> {{ __trans('bulk_mark_attendance') }}
                </button>
                <button type="button" class="btn btn-info waves-effect waves-light sample-download">{{__trans('sample_download')}} </button>
            </div>
        </form>
    </div>
</div>
<div id="ajax-loader" style="display: none;position: fixed;top: 0; left: 0;width: 100%; height: 100%;background: rgba(255,255,255,0.7);z-index: 9999;justify-content: center;align-items: center;">
    <div class="spinner-border text-primary" role="status" style="position: relative;left: 50%;top: 50%;">
        <span class="visually-hidden">Loading...</span>
    </div>
</div>
<script>
    initselect2();
    loadAjaxSelect2();
    flatpickr("input.datepicker", {
        dateFormat: "Y-m-d",
        //maxDate: new Date()
    });
   
    $(document).on("click", ".sample-download", function(e) {
        e.preventDefault();

        let form = $(this).closest("form");
        let startInput = form.find('[name="start_date"]');
        let endInput = form.find('[name="end_date"]');

        let startDate = startInput.val();
        let endDate = endInput.val();

        form.find('.text-danger.validation-error').remove();
        startInput.removeClass('is-invalid');
        endInput.removeClass('is-invalid');

        let hasError = false;


        if (!startDate) {
            startInput.addClass('is-invalid');
            startInput.after('<div class="text-danger validation-error">Please select a start date.</div>');
            hasError = true;
        }

        if (!endDate) {
            endInput.addClass('is-invalid');
            endInput.after('<div class="text-danger validation-error">Please select an end date.</div>');
            hasError = true;
        }

        if (startDate && endDate && new Date(endDate) < new Date(startDate)) {
            endInput.addClass('is-invalid');
            endInput.after('<div class="text-danger validation-error">End Date cannot be earlier than Start Date.</div>');
            hasError = true;
        }

        if (hasError) return;


        let formData = new FormData(form[0]);

        $.ajax({
            url: form.attr("action"),
            type: form.attr("method"),
            data: formData,
            processData: false,
            contentType: false,
            xhrFields: {
                responseType: 'blob'
            },
            success: function (data, status, xhr) {
                
                let disposition = xhr.getResponseHeader('Content-Disposition');
                let filename = "download.xlsx";

                if (disposition) {
                    
                    let utf8FilenameRegex = /filename\*\=UTF-8''(.+)/i;
                    let matches = utf8FilenameRegex.exec(disposition);
                    if (matches != null && matches[1]) {
                        filename = decodeURIComponent(matches[1]);
                    } else {
                        // Fallback to plain filename=
                        let fallbackRegex = /filename="?([^"]+)"?/;
                        matches = fallbackRegex.exec(disposition);
                        if (matches != null && matches[1]) {
                            filename = matches[1];
                        }
                    }
                }

                // Create a temporary link
                let link = document.createElement('a');
                let url = window.URL.createObjectURL(data);
                link.href = url;
                link.download = filename;
                document.body.appendChild(link);
                link.click();

                // Cleanup
                window.URL.revokeObjectURL(url);
                document.body.removeChild(link);
            }
        });
    });

    $(document).on("click", ".import-data", function(e) {
        e.preventDefault();

        let form = $(this).closest("form");
        let urldata = $(this).data("url");
        let fileInput = form.find('.attendance-file');
        let file = fileInput[0].files[0];

        fileInput.removeClass('is-invalid');
        fileInput.next('.invalid-feedback').remove();

        if (!file) {
            fileInput.addClass('is-invalid');
            fileInput.after('<div class="invalid-feedback">Please upload an Excel file before importing.</div>');
            return;
        }
        let formData = new FormData(form[0]);

        $.ajax({
            url: urldata,
            type: form.attr("method"),
            data: formData,
            processData: false,
            contentType: false,
            beforeSend: function() {
                $("#ajax-loader").fadeIn();
            },
            success: function (res) {
                if(res.success == true){
                    window.alert("Attendance marked successfully.");
                    window.location.reload();
                }
                
            }
        });
    });
</script>
