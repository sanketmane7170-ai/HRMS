<div class="modal-dialog modal-lg">
    <div class="modal-content">
        <div class="modal-header">
            <h4 class="modal-title">{{__trans('edit_role')}}</h4>
            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <form action="{{route('backend.roles.update',$role)}}" datatable="true" method="POST" class="ajax-form-submit">
            @csrf
            @method('PUT')
            <div class="modal-body p-4">
                <div class="row">
                    <div class="col-md-12">
                        <div class="mb-3">
                            <label for="edit-field-1" class="form-label">{{__trans('role_title')}}</label>
                            <input type="text" name="name" value="{{$role->name}}" class="form-control" id="edit-field-1" placeholder="Admin">
                        </div>
                    </div>
                     <div class="col-md-12">
                        <div class="mb-3">
                            <label for="edit-field-1" class="form-label">{{__trans('priority')}}</label>
                            <input type="number" name="priority" value="{{$role->priority}}" class="form-control" id="priority" placeholder="1">
                        </div>
                    </div>
                    <div class="col-md-12">
                        <div class="mb-3">
                            <label for="field-1" class="form-label">{{__trans('roles_permission')}}</label>
                            <select name="permissions[]" id="permissions" class="select-search w-100" multiple>
                                @foreach ($permissions as $permission)
                                <option value="{{$permission}}" @if($role->hasPermissionTo($permission)) selected @endif>{{$permission}}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary waves-effect" data-bs-dismiss="modal">{{__trans('close')}}</button>
                <button type="submit" class="btn btn-info waves-effect waves-light">{{__trans('update')}} </button>
            </div>
        </form>
    </div>
</div>

<script>
    initselect2search();
</script>
