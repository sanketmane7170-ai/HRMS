<div class="modal-dialog ">
    <div class="modal-content">
        <div class="modal-header">
            <h4 class="modal-title">{{__trans('edit_document_request')}}</h4>
            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <form action="{{route('backend.employee.document-requests.update',$documentRequest)}}" datatable="true" method="POST" class="ajax-form-submit reset">
            @csrf
            @method('PUT')
            <div class="modal-body p-4">
                <div class="row">
                    <div class="col-md-12">
                        <div class="mb-3">
                            <label for="document_type_id" class="form-label">{{__trans('document_type')}}</label>
                            <select name="document_type_id" id="document_type_id" class="form-control select" onChange="onChangeDocumentType(this)">
                                <option value="">{{__trans('select_option')}}</option>
                                @foreach ($types as $type)
                                <option value="{{$type->id}}" @if($type->id == $documentRequest->document_type_id) selected @endif>{{$type->name}}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div class="col-md-12" id="letter" style="display:none;">
                        <div class="mb-3">
                        <label for="letter_addressed_to" class="form-label">{{__trans('letter_addressed_to')}}</label>
                            <input name="letter_addressed_to" id="letter_addressed_to" value="{{ $documentRequest->letter_addressed_to }}" class="form-control" placeholder="{{__trans('enter_addressed_to')}}">
                        </div>
                    </div>
                    <div class="col-md-12">
                        <div class="mb-3">
                            <label for="reason" class="form-label">{{__trans('reason')}}</label>
                            <textarea name="reason" id="reason" cols="30" rows="6" class="form-control" placeholder="{{__trans('enter_reason')}}">{{$documentRequest->reason}}</textarea>
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
    initselect2();
</script>
<script>
    $( document ).ready(function() {
        let text = $('#document_type_id :selected').text();
        if(text == 'salary certificate' || text == 'Salary Certificate' || text == 'SALARY CERTIFICATE'){
            $("#letter").show();
        } else {
            $("#letter_addressed_to").val('');
            $("#letter").hide();
        }
    });
    function onChangeDocumentType(data) {
        var text = data.options[data.selectedIndex].text;
        if(text == 'salary certificate' || text == 'Salary Certificate' || text == 'SALARY CERTIFICATE'){
            $("#letter").show();
        } else {
            $("#letter_addressed_to").val('');
            $("#letter").hide();
        }
    }
</script>