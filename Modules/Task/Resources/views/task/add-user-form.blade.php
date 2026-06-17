<div class="modal-dialog ">
    <div class="modal-content">
        <div class="modal-header">
            <h4 class="modal-title">{{__trans('assign_task_to_user')}} : {{$task->title}}</h4>
            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <form action="{{route('backend.task.user.add',$task)}}" datatable="true" method="POST" class="ajax-form-submit">
            @csrf
            <div class="modal-body p-4">
                <div class="row">
                    <div class="col-md-12">
                        <div class="mb-3">
                            <label for="edit-field-1" class="form-label">{{__trans('name')}}</label>
                            <select name="users" id="users" class="select-search">
                                <option>Select User</option>
                                @foreach ($users as $user)
                                <option value="{{$user->id}}" @if($task->assigned_to == $user->id) selected @endif>{{$user->name}} [{{$user->email}}]</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary waves-effect" data-bs-dismiss="modal">{{__trans('close')}}</button>
                <button type="submit" class="btn btn-info waves-effect waves-light">{{__trans('add')}} </button>
            </div>
        </form>
    </div>
</div>
<script>
    initselect2search();
    initselect2();
</script>
