<div class="modal-dialog">
    <div class="modal-content">
        <div class="modal-header">
            <h4 class="modal-title light">{{__trans('add_asset')}}</h4>
            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <form action="{{route('backend.asset.assign-user',$user)}}" datatable="true" method="POST" class="ajax-form-submit reset" @if($user) redirect @else datatable @endif>
            @csrf
            <div class="modal-body p-4">
                <div class="row">
                    <div class="col-lg-12">
                        @if($user->id)
                        <input type="hidden" name="user_id" value="{{$user->id}}">
                        @else
                        <div class="form-group">
                            <label>{{__trans('user')}}</label>
                            <select name="user_id" class="ajax-select2" id="asset_manufacturer_id" data-target="{{route('ajax.select2.fetch.users')}}">
                                <option value="{{$user->id}}">{{$user->name}}</option>
                            </select>
                        </div>
                        @endif
                    </div>
                    <div class="col-lg-12">
                        <div class="form-group">
                            <label>{{__trans('asset')}}</label>
                            <select name="asset_id" class="ajax-select2" id="asset_type_id" data-target="{{route('ajax.select2.fetch.assets-open')}}">
                            </select>
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
