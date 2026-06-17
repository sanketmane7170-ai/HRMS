<div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title">{{__trans('assign_notification_alert')}}</h4>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="{{route('backend.notification.manager.store')}}" datatable="true" method="POST" class="ajax-form-submit reset">
                @csrf
                <div class="modal-body p-4">
                    <div class="row">
                        <div class="col-md-12">
                            <div class="mb-3">
                                <label for="field-1" class="form-label">{{__trans('role')}}</label>
                                <select name="role_id" class="ajax-select2" id="role_id" data-target="{{route('ajax.select2.fetch.roles')}}">
                                </select>
                            </div>
                        </div>
                        <div class="col-md-12">
                            <div class="mb-3">
                                <label for="user_id" class="form-label">{{ __trans('user') }}</label>
                                <!-- <select name="user_id"  id="user_id" class="form-control select">
                                </select> -->
                                <select name="user_id"
        id="user_id"
        class="ajax-select2"
        data-placeholder="Select User">
</select>

                            </div>
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
<script>
    loadAjaxSelect2();
</script>
<!-- <script>
    $(document).ready(function() {
        $('#role_id').change(function() {
            var roleId = $(this).val();
            var url = '{{ route("ajax.select2.fetch.users.by.role", ":roleId") }}';
            url = url.replace(':roleId', roleId);

            $.ajax({
                url: url,
                type: 'GET',
                success: function(data) {
                    $('#user_id').empty();
                    $('#user_id').append('<option value="">Select User</option>');
                    $.each(data.users, function(key, value) {
                        $('#user_id').append('<option value="' + value.id + '">' + value.name + '</option>');
                    });
                },
                error: function(xhr, status, error) {
                    console.log(error);
                }
            });
        });
    });
</script> -->
<!-- <script>
$(document).ready(function() {

    $('#role_id').change(function() {

        var roleId = $(this).val();

        if (!roleId) {
            $('#user_id').empty();
            return;
        }

        var urlTemplate = '{{ route("ajax.select2.fetch.users.by.role", 0) }}';
        var url = urlTemplate.replace('/0', '/' + roleId);

        $.ajax({
            url: url,
            type: 'GET',
            success: function(data) {

                $('#user_id').empty();
                $('#user_id').append('<option value="">Select User</option>');

                $.each(data.users, function(key, value) {
                    $('#user_id').append(
                        '<option value="' + value.id + '">' + value.name + '</option>'
                    );
                });
            },
            error: function(xhr) {
                console.log(xhr.responseText);
            }
        });

    });

});
</script> -->

<script>
$(document).ready(function () {

    // Load Role Select2 normally
    loadAjaxSelect2();

    // Initialize User Select2 separately
    $('#user_id').select2({
        dropdownParent: $('#user_id').closest('.modal'),
        width: '100%',
        placeholder: "Select User",
        allowClear: true,
        ajax: {
            url: function () {

                var roleId = $('#role_id').val();

                if (!roleId) {
                    return null; // stop if no role selected
                }

                var urlTemplate = '{{ route("ajax.select2.fetch.users.by.role", 0) }}';
                return urlTemplate.replace('/0', '/' + roleId);
            },
            dataType: 'json',
            delay: 250,
            data: function (params) {
                return {
                    search: params.term // search keyword
                };
            },
            processResults: function (data) {

                return {
                    results: $.map(data.users, function (item) {
                        return {
                            id: item.id,
                            text: item.name
                        }
                    })
                };
            }
        }
    });

    // Clear user when role changes
    $('#role_id').on('change', function () {
        $('#user_id').val(null).trigger('change');
    });

});
</script>
