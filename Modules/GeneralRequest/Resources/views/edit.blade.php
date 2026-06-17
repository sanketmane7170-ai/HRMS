<div class="modal-dialog ">
    <div class="modal-content">
        <div class="modal-header">
            <h4 class="modal-title">{{__trans('add_apparel')}}</h4>
            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <form action="{{route('backend.general.update',$general->id)}}" datatable="true" method="POST" class="ajax-form-submit reset">
            @csrf
            <div class="modal-body p-4">
                <div class="row">
                    <div class="col-md-12">
                        <div class="mb-3">
                            <label for="name" class="form-label">{{__trans('name')}}</label>
                            <input type="text" name="name"  value="{{ $general->name }}" class="form-control datetime" placeholder="{{__trans('name')}}">
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
    $(document).ready(function () {
        toggleNoOfLeaves();
        $('#is_recurring').change(function () {
            toggleNoOfLeaves();
        });
        function toggleNoOfLeaves() {
            var isRecurringValue = $('#is_recurring').val();
            if (isRecurringValue === '1') {
                $('#no_of_leaves_container').show();
            } else {
                $('#no_of_leaves_container').hide();
            }
        }
    });
</script>
