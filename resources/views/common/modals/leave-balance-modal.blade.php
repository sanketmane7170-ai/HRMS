<div class="modal-dialog modal-lg">
    <div class="modal-content">
        <div class="modal-header">
            <h4 class="modal-title">{{__trans('edit_leave_balance')}}</h4>
            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <form action="" datatable="true" method="POST" class="ajax-form-submit reset">
            @csrf
            @method('PUT')
            <div class="modal-body p-4">
                <div class="row">
                    <div class="col-md-4">
                        <div class="mb-3">
                            <label class="form-label">{{__trans('first_name')}}</label>
                            <input type="text" name="first_name" class="form-control" id="first_name" placeholder="{{__trans('first_name')}}" value="">
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="mb-3">
                            <label class="form-label">{{__trans('middle_name')}}</label>
                            <input type="text" name="middle_name" class="form-control" id="middle_name" placeholder="{{__trans('middle_name')}}" value="">
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="mb-3">
                            <label class="form-label">{{__trans('last_name')}}</label>
                            <input type="text" name="last_name" class="form-control" id="last_name" placeholder="{{__trans('last_name')}}" value="">
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label class="form-label">{{__trans('contact_details')}}</label>
                            <input type="text" name="contact" class="form-control" id="contact" placeholder="{{__trans('contact')}}" value="">
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label class="form-label">{{__trans('address')}}</label>
                            <input type="text" name="address" class="form-control" id="address" placeholder="{{__trans('address')}}" value="">
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label class="form-label">{{__trans('date_of_birth')}}</label>
                            <input type="date" name="date_of_birth" class="form-control" id="date_of_birth" placeholder="{{__trans('date_of_birth')}}" value="">
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
</script>
