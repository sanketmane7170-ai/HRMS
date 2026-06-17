<div class="modal-dialog modal-lg">
    <div class="modal-content">
        <div class="modal-header">
            <h4 class="modal-title">{{__trans('edit_shift')}}</h4>
            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <form action="{{route('backend.shift.update',$shift)}}" datatable="true" method="POST" class="ajax-form-submit">
            @csrf
            @method('PUT')
            <div class="modal-body p-4">
                <div class="row">
                    <div class="col-md-12">
                        <div class="mb-3">
                            <label for="edit-field-1" class="form-label">{{__trans('title')}}</label>
                            <input type="text" name="title" value="{{$shift->title}}" class="form-control" id="edit-field-1" placeholder="" <?= $isAdmin || $hasPermissions_edit_shift  ? "":"readonly"; ?> >
                        </div>
                    </div>
                    <div class="col-md-12">
                        <div class="mb-3">
                            <label for="shift_type" class="form-label">{{__trans('type')}}</label>
                            <input type="text" name='type' value="{{ $shift->type }}" hidden>
                            <select name="type" id="type" class="form-control select" onChange="onChangeShiftType(this)" <?= ($shift->type == 'MS') ? 'disabled' : '' ?>  <?= $isAdmin || $hasPermissions_edit_shift ? "":"disabled"; ?>>
                                <option value="">{{__trans('select_option')}}</option>
                                @foreach ($shift_types as $type)
                                    <option value="{{$type['id']}}" @if($shift->type == $type['id']) selected @endif>{{$type['name']}}</option>
                                @endforeach
                            </select>
                            @if($shift->type == 'MS')
                                <div style="color: red;">It's a multiple shift, you cannot edit. Create another single shift.</div>
                            @endif
                        </div>
                    </div>
                    <div class="col-md-12">
                        <div class="mb-3">
                            <div class="form-check form-switch" style="float: left;font-size: x-large;margin-right:10px;">
                                <input class="form-check-input toggle-switch month2leave" type="checkbox" name="is_weekend" @if($shift->is_weekend==1) value="1" checked @endif >
                                <label class="form-check-label" for="isAllowSwitch">is Day off</label>
                            </div>
                        </div>
                    </div>
                    <div class="row" id="letter" style="display:none;">
                        @foreach ($shift_schedules as $index => $info )
                         <div class="col-md-6">
                            <div class="mb-3">
                                <label for="shift_start" class="form-label">Shift Start</label>
                                <input type="text" name="shifts[{{ $index }}][shift_start]" class="form-control timepicker" id="shift_start" value="{{ $info->shift_start }}" placeholder="shift start time" required>
                            </div>
                        </div>
                        <div class="col-md-5">
                            <div class="mb-3">
                                <label for="shift_end" class="form-label">Shift End</label>
                                <input type="text" name="shifts[{{ $index }}][shift_end]" class="form-control timepicker" id="shift_end" value="{{ $info->shift_end }}" placeholder="shift end time" required>
                            </div>
                        </div>
                        @endforeach
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary waves-effect" data-bs-dismiss="modal">{{__trans('close')}}</button>
                <button type="submit" id="submitButton" class="btn btn-info waves-effect waves-light">{{__trans('update')}} </button>
            </div>
        </form>
    </div>
</div>
<script src="{{asset('assets/backend/js/custom-shift.js')}}"></script>   
<script>
    initselect2();
</script>
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
        //$(".ajax-form-submit").submit();
    });
});
    </script>
<script>
    $(document).ready(function() {
    let text = $('#type :selected').val();
    console.log('text value is', text);

    if (text == 'MS') {
        $("#letter").show();
        var html = `<div class="col-md-1">
                        <div class="mb-3" style="margin-top: 31px !important;">
                            <button type="button" id="addShift" class="btn btn-primary">+</button>
                        </div>
                    </div>`;
        flatpickr('.timepicker', {
            enableTime: true,
            noCalendar: true,
            dateFormat: "H:i",
        })
        $("#letter").append(html);
    } else {
        flatpickr('.timepicker', {
            enableTime: true,
            noCalendar: true,
            dateFormat: "H:i",
        })
        $("#letter").show();
    }

    // Event handler for the plus button
    $(document).on('click', '#addShift', function() {
        // Check if any rows already exist
        if ($('#letter .col-md-6').length === 0) {
            // Append the new shift input fields to the container
            $('#letter').append(createShiftInputs());
            flatpickr('.timepicker', {
                enableTime: true,
                noCalendar: true,
                dateFormat: "H:i",
            });
        }
    });

    // Function to create a new set of shift input fields
    function createShiftInputs() {
        var ShiftsCount = $('#letter .col-md-6').length;
        var newShiftInputs = `
            <div class="row">
                <div class="col-md-6">
                    <div class="mb-3">
                        <label class="form-label">{{__trans('shift_start')}}</label>
                        <input type="text" name="shifts[${ShiftsCount }][shift_start]" class="form-control timepicker" placeholder="{{__trans('shift_start_time')}}">
                    </div>
                </div>
                <div class="col-md-5">
                    <div class="mb-3">
                        <label class="form-label">{{__trans('shift_end')}}</label>
                        <input type="text" name="shifts[${ShiftsCount }][shift_end]" class="form-control timepicker" placeholder="{{__trans('shift_end_time')}}">
                    </div>
                </div>
                <div class="col-md-1">
                    <div class="mb-3" style="margin-top: 31px !important;">
                        <button type="button" class="btn btn-danger cancelShift">Cancel</button>
                    </div>
                </div>
            </div>
        `;

        return newShiftInputs;
    }
});
</script>
