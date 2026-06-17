<div class="modal-dialog">
    <div class="modal-content">
        <div class="modal-header">
            <h4 class="modal-title">{{__trans('unassign_asset')}}</h4>
            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <form action="{{route('backend.asset.un-assign.user',$asset)}}" datatable="true" method="POST" class="ajax-form-submit reset">
            @csrf
            <div class="modal-body p-4">
                <div class="row">
                    <div class="col-md-12">
                        <p>{{__trans('assigned_user')}} : {{$asset->activeAssignment->user?->name}} | {{$asset->activeAssignment->user->department?->name}}</p>
                        <p>{{__trans('serial_number')}} : {{$asset->unique_id}}</p>
                        <p>{{__trans('model')}} : {{$asset->manufacturer->name}} - {{$asset->type->name}} - {{$asset->model}}</p>
                    </div>
                    <div class="col-lg-12 mt-4">
                        <div class="form-group">
                            <label>{{__trans('reason')}}</label>
                            <textarea name="comment" id="comment" cols="30" rows="5" class="form-control"></textarea>
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
    loadAjaxSelect2()
</script>
