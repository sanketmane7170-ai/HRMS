<div class="modal-dialog modal-lg">
    <div class="modal-content">
        <div class="modal-header">
            <h4 class="modal-title">{{__trans('Assign multiple branch for : ')}} {{$user->name}}</h4>
            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <form action="{{ route('backend.assignBranch',$user->id) }}" datatable="true" method="POST" class="ajax-form-submit reset">
            @csrf
            <div class="modal-body p-4">
                <div class="row">
                    <div class="mb-3">
                        <label class="form-label">Select Branches</label>
                        <div>
                            @foreach($departments as $value)
                            <div class="form-check">
                                <input type="checkbox" name="branches[]" value="{{ $value->id }}" class="form-check-input" id="branch_{{ $value->id }}" {{ in_array($value->id, $selectedBranches) ? 'checked' : '' }}>
                                <label class="form-check-label" for="branch_{{ $value->id }}">{{ $value->name }}</label>
                            </div>
                            @endforeach
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary waves-effect" data-bs-dismiss="modal">{{__trans('close')}}</button>
                <button type="submit" class="btn btn-info waves-effect waves-light">{{__trans('assign')}} </button>
            </div>
        </form>
    </div>
</div>
<script>

</script>
