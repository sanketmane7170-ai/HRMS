<div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title">{{__trans('add_shift')}}</h4>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="{{route('backend.shift.store')}}" datatable="true" method="POST" class="ajax-form-submit reset">
                @csrf
                <div class="modal-body p-4">
                    <div class="row">
                        <div class="col-md-12">
                            <div class="mb-3">
                                <label for="field-1" class="form-label">{{__trans('title')}}</label>
                                <input type="text" name="title" class="form-control" id="field-1" placeholder="{{__trans('title')}}">
                            </div>
                        </div>
                        <div class="col-md-12">
                            <div class="mb-3">
                                <label for="shift_type" class="form-label">{{__trans('type')}}</label>
                                <select name="type" id="type" class="form-control select" onChange="onChangeShiftType(this)">
                                    <option value="">{{__trans('select_option')}}</option>
                                    @foreach ($shift_types as $type)
                                    <option value="{{$type['id']}}">{{$type['name']}}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        <div class="col-md-12">
                            <div class="mb-3">
                                <div class="form-check form-switch" style="float: left;font-size: x-large;margin-right:10px;">
                                    <input class="form-check-input toggle-switch month2leave" type="checkbox" name="is_weekend" value="1">
                                    <label class="form-check-label" for="isAllowSwitch">is Day off</label>
                                </div>
                            </div>
                        </div>
                        <div class="row" id="letter" style="display:none;">
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary waves-effect" data-bs-dismiss="modal">{{__trans('close')}}</button>
                    <button type="submit" id="submitButton" class="btn btn-info waves-effect waves-light">{{__trans('save')}} </button>
                </div>
            </form>
        </div>
    </div>
<script src="{{asset('assets/backend/js/custom-shift.js')}}"></script>  
<script>
$(document).ready(function () {
   

    // Validate and submit form on button click
    $(".modal-footer").on("click", "button[type='submit']", function () {
        var errors = false;

        // Reset error states
        $(".timepicker").removeClass("is-invalid");
        $(".invalid-feedback").hide();

        // Check each dynamically generated input field
        $(".timepicker").each(function () {
            if ($(this).val() === "") {
                // Add error styling to the input field
                $(this).addClass("is-invalid");
                // Show the adjacent error message
                $(this).next(".invalid-feedback").show();
                errors = true;
            }
        });

        // If there are errors, prevent form submission
        if (errors) {
            return false;
        }

        // Proceed with form submission if no errors
       // $(".ajax-form-submit").submit();
    });
});
    </script>  