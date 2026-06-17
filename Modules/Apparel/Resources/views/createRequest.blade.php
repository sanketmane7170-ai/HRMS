<div class="modal-dialog modal-lg">
    <div class="modal-content">
        <div class="modal-header">
            <h4 class="modal-title">New Uniform Request</h4>
            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <form action="{{route('backend.admin.apparelRequest.store')}}" datatable="true" method="POST" class="ajax-form-submit reset">
            @csrf
            <div class="modal-body p-4">
                <div class="row">
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label><strong>{{ __trans('user') }}:</strong></label>
                            <select name="user_id" class="select-search" id="user_id">
                                <option value="">Select User</option>
                                @foreach ($users as $user)
                                    <option value="{{$user->id}}">{{$user->employee_id}} {{$user->name}}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label><strong>Uniform:</strong></label>
                            <select name="apparel_id" class="select-search getTotal">
                                <option value="">Select Uniform</option>
                                @foreach ($requestApp as $type)
                                    <option value="{{$type->id}}">{{$type->name}}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label><strong>{{ __trans('how_many_apparel') }}:</strong></label>
                            <input type="text" name="number_of_apparel" class="form-control" placeholder="{{__trans('how_many_apparel')}}">
                            <span id="showlimit" style="color: red"></span>
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
        enableTime: false,
        // minDate: "today",
        dateFormat: "Y-m-d",
    });

    $(document).on('change', '#user_id', function () {
        $('#showlimit').text('');
    });
    // apparel remove
    $(document).on('change', '.getTotal', function () {
        var app_id = $(this).val();
        var user_id = $("#user_id").val();
        if(user_id != ''){
            $.ajax({
                url: "{{ route('backend.apparel.getapparelTotal') }}",
                type: 'POST',
                data: {
                    app_id: app_id,
                    user_id: user_id,
                    _token: '{{ csrf_token() }}'
                },
                success: function (response) {
                    if (response.success) {
                        $('#showlimit').text('Limit ' + response.limit);
                    }
                },
                error: function (xhr) {
                    toastr.error('An error occurred while processing your request.');
                }
            });
        }
        
    });
</script>
