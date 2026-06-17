<div class="modal-dialog modal-lg">
    <div class="modal-content">
        <div class="modal-header">
            <h4 class="modal-title">{{__trans('new_leave_request')}}</h4>
            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <form action="{{route('backend.leaves.store')}}" datatable="true" method="POST" class="ajax-form-submit reset">
            @csrf
            <div class="modal-body p-4">
                <div class="row">
                    <div class="col-md-12">
                        <div class="mb-3">
                            <label for="user_id" class="form-label">{{__trans('User')}}</label>
                            <select name="user_id" id="user_id" class="select-search">
                                <option value="">{{__trans('select_employee')}}</option>
                                @foreach ($users as $user)
                                    <option value="{{$user->id}}">{{$user->employee_id}} {{$user->name}}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label for="start_date" class="form-label">{{__trans('start_date')}}</label>
                            <input type="text" id="start_date" name="start_date" class="form-control datetime" placeholder="{{__trans('select_date')}}">
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label for="end_date" class="form-label">{{__trans('end_date')}}</label>
                            <input type="text" id="end_date" name="end_date" class="form-control datetime" placeholder="{{__trans('select_date')}}">
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label for="leave_type_id" class="form-label">{{__trans('leave_type')}}</label>
                            <select name="leave_type_id" id="leave_type_id" class="select-search">
                                <option value="">{{__trans('select_option')}}</option>
                                @foreach ($leaveTypes as $type)
                                <option value="{{$type->id}}">{{$type->name}}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label for="is_half_day">
                                <br>
                                <input type="checkbox" value="1" class="form-check-input" name="is_half_day" id="is_half_day">{{__trans('is_half_day_leave')}}
                            </label>
                        </div>
                    </div>
                    <div class="col-md-12">
                        <div class="mb-3">
                            <label for="document">{{__trans('attach_document')}}</label>
                            <input type="file" name="document" id="document" class="form-control">
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label for="status" class="form-label">{{__trans('Status')}}</label>
                            <select name="status" id="status" class="select-search">
                                <option value="pending">{{__trans('pending')}}</option>
                                <option value="approved">{{__trans('approved')}}</option>
                                <option value="rejected">{{__trans('rejected')}}</option>
                                <option value="cancelled">{{__trans('cancelled')}}</option>
                            </select>
                        </div>
                    </div>
                    <div class="col-md-12">
                        <div class="mb-3">
                            <label for="reason">{{__trans('details')}}</label>
                            <textarea name="reason" id="reason" class="form-control" cols="30" rows="6"></textarea>
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
    var today = new Date();
    today.setHours(0, 0, 0, 0);

    initselect2search();
    flatpickr("input.datetime", {
        enableTime: false,
        dateFormat: "Y-m-d",
        startDate: today
    });
    $(document).ready(function () {
    $('#user_id').select2({
        placeholder: "{{__trans('select_employee')}}",
        allowClear: true,
        width: '100%',
        dropdownParent: $('.modal-content') // VERY IMPORTANT for modal
    });
});

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
</script>
