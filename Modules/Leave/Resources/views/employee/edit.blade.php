<div class="modal-dialog modal-lg">
    <div class="modal-content">
        <div class="modal-header">
            <h4 class="modal-title">{{__trans('edit_leave_request')}}</h4>
            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <form action="{{route('backend.employee.leaves.update',$leave)}}" datatable="true" method="POST" class="ajax-form-submit reset">
            @csrf
            @method('PUT')
            <div class="modal-body p-4">
                <div class="row">
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label for="start_date" class="form-label">{{__trans('start_date')}}</label>
                            <input type="text" id="start_date" name="start_date" class="form-control datetime" value="{{$leave->start_date}}" placeholder="{{__trans('select_date')}}">
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label for="edit_end_date" class="form-label">{{__trans('end_date')}}</label>
                            <input type="text" id="end_date" name="end_date" class="form-control datetime" value="{{$leave->end_date}}" placeholder="{{__trans('select_date')}}">
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label for="leave_type_id" class="form-label">{{__trans('leave_type')}}</label>
                            <select name="leave_type_id" id="leave_type_id" class="select-search">
                                <option value="">{{__trans('select_option')}}</option>
                                @foreach ($leaveTypes as $type)
                                <option value="{{$type->id}}" @if($leave->leave_type_id == $type->id) selected @endif>{{$type->name}}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label for="is_half_day">{{__trans('is_half_day_leave')}}
                                <br>
                                <input type="checkbox" value="1" class="form-check-input" name="is_half_day" id="is_half_day" @if($leave->is_half_day) checked @endif>
                            </label>
                        </div>
                    </div>
                    <div class="col-md-12">
                        <div class="mb-3">
                            <label for="document">{{__trans('attach_document')}}</label>
                            <input type="file" name="document" id="document" class="form-control">
                        </div>
                    </div>
                    <div class="col-md-12">
                        <div class="mb-3">
                            <label for="reason">{{__trans('details')}}</label>
                            <textarea name="reason" id="reason" class="form-control" cols="30" rows="6">{{$leave->reason}}</textarea>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary waves-effect" data-bs-dismiss="modal">{{__trans('close')}}</button>
                <button type="submit" class="btn btn-info waves-effect waves-light">{{__trans('save')}} </button>
            </div>
        </form>
    </div>
</div>

<script>
    initselect2search();
    flatpickr("input.datetime", {
        enableTime: true,
        // minDate: "today",
        dateFormat: "Y-m-d",
    });

    var today = new Date();
    today.setHours(0, 0, 0, 0);
    $(document).ready(function() {
        // Event listener for when the end date changes
        $('#end_date').on('change', function() {
            var startDate = $('#start_date').val();
            var endDate = $(this).val();

            if (new Date(endDate) < new Date(startDate)) {
                alert("End date cannot be earlier than start date");
                $(this).val(''); // Clear the end date input
            }
        });

        // Event listener for when the start date changes
        $('#start_date').on('change', function() {
            var startDate = new Date($(this).val());
            var endDate = $('#end_date').val();

            startDate.setHours(0, 0, 0, 0);

            // Reset the end date if it's earlier than the new start date
            if (new Date(endDate) < new Date(startDate)) {
                alert("End date cannot be earlier than start date");
                $('#end_date').val(''); // Clear the end date input
            }
        });
    });

    $(document).ready(function () {
        $('#is_half_day').change(function () {
            if ($(this).is(':checked')) {
                $(this).val(1); // Set value to 1 when checked
            } else {
                $(this).val(0); // Set value to 0 when unchecked
            }
        });
    });
</script>
