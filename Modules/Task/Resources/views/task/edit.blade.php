<div class="modal-dialog modal-md">
    <div class="modal-content">
        <div class="modal-header">
            <h4 class="modal-title">{{__trans('edit_task')}}</h4>
            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <form action="{{route('backend.task.update',$task)}}" datatable="true" method="POST"
            class="ajax-form-submit reset">
            @csrf
            @method('PUT')
            <div class="modal-body p-4">
                <div class="row">



                    <div class="col-md-12">
                        <div class="mb-3">
                            <label for="title" class="form-label">{{__trans('title')}}</label>
                            <input type="text" name="title" class="form-control" placeholder="{{__trans('title')}}"
                                value="{{$task->title}}">
                        </div>
                    </div>

                    <div class="col-md-12">
                        <div class="mb-3">
                            <label for="description" class="form-label">{{__trans('description')}}</label>
                            <input type="text" name="description" class="form-control"
                                placeholder="{{__trans('description')}}" value="{{$task->description}}">
                        </div>
                    </div>
                    <div class="col-md-12">
                        <div class="mb-3">
                            <label for="priority" class="form-label">{{ __trans('priority') }}</label>
                            <select name="priority" class="form-control">
                                <option value="">-- Select Priority --</option>
                                <option value="low" {{ isset($task) && $task->priority == 'low' ? 'selected' : '' }}>
                                    {{ __trans('Low') }}
                                </option>
                                <option value="medium"
                                    {{ isset($task) && $task->priority == 'medium' ? 'selected' : '' }}>
                                    {{ __trans('Medium') }}
                                </option>
                                <option value="high" {{ isset($task) && $task->priority == 'high' ? 'selected' : '' }}>
                                    {{ __trans('High') }}
                                </option>
                                <option value="urgent"
                                    {{ isset($task) && $task->priority == 'urgent' ? 'selected' : '' }}>
                                    {{ __trans('Urgent') }}
                                </option>
                            </select>
                        </div>
                    </div>



                    <div class="col-md-12">
                        <div class="mb-3">
                            <label for="end_date" class="form-label">{{__trans('end_date')}}</label>
                            <input type="text" name="end_date" value="{{$task->end_date}}" class="form-control datetime"
                                placeholder="{{__trans('end_date')}}">
                        </div>
                    </div>


                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary waves-effect"
                    data-bs-dismiss="modal">{{__trans('close')}}</button>
                <button type="submit" class="btn btn-info waves-effect waves-light">{{__trans('save')}} </button>
            </div>
        </form>
    </div>
</div>
<script>
initselect2();
loadAjaxSelect2();
flatpickr("input.datetime", {
    enableTime: true,
    // minDate: "today",
    dateFormat: "Y-m-d",
});
</script>