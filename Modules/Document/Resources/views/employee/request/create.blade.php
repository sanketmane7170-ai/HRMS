<div class="modal-dialog ">
    <div class="modal-content">
        <div class="modal-header">
            <h4 class="modal-title">{{__trans('create_document_request')}}</h4>
            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
       
        <div id="doc-count-box"></div>

        <form action="{{route('backend.employee.document-requests.store')}}" datatable="true" method="POST" class="ajax-form-submit reset">
            @csrf
            <div class="modal-body p-4">
                <div class="row">
                    <div class="col-md-12">
                        <div class="mb-3">
                            <label for="document_type_id" class="form-label">{{__trans('document_type')}}</label>
                            <select name="document_type_id" id="document_type_id" class="form-control select" onChange="onChangeDocumentType(this)">
                                <option value="">{{__trans('select_option')}}</option>
                                @foreach ($types as $type)
                                <option value="{{$type->id}}">{{$type->name}}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div class="col-md-12" id="letter" style="display:none;">
                        <div class="mb-3">
                            <label for="document_type_id" class="form-label">{{__trans('letter_addressed_to')}}</label>
                            <input name="letter_addressed_to" id="letter_addressed_to" class="form-control" placeholder="{{__trans('enter_addressed_to')}}">
                        </div>
                    </div>
                    <div class="col-md-12">
                        <div class="mb-3">
                            <label for="reason" class="form-label">{{__trans('Reason')}}</label>
                            <textarea name="reason" id="reason" cols="30" rows="6" class="form-control" placeholder="{{__trans('enter_reason')}}"></textarea>
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
    function onChangeDocumentType(data) {
        var text = data.options[data.selectedIndex].text;
        if (text == 'salary certificate' || text == 'Salary Certificate' || text == 'SALARY CERTIFICATE') {
            $("#letter").show();
        } else {
            $("#letter").hide();
        }
    }
</script>
<script>
    initselect2();

    function onChangeDocumentType(data) {
        var docTypeId = data.value;

        // Show/hide salary letter field
        var text = data.options[data.selectedIndex].text;
        if (text.toLowerCase() === 'salary certificate') {
            $("#letter").show();
        } else {
            $("#letter").hide();
        }

        if (docTypeId === "") {
            $("#doc-count-box").html('');
            return;
        }

        // AJAX request to get count
        $.ajax({
            url: "{{ route('backend.employee.document-type.count') }}",
            type: "POST",
            data: {
                _token: "{{ csrf_token() }}",
                document_type_id: docTypeId,
                user_id: "{{ auth()->id() }}"
            },
            success: function(response) {
                let count = Number(response.count); // total requests of this type
                let completedCount = Number(response.complitedCount);
                let pendingCount = Number(response.pendingCount);
                let freeLimit = Number(response.free_limit);
                let charge = Number(response.charge);
                let amount = Number(response.amount) || 0;

                let remaining = freeLimit - count;
if(charge>0){
                if (remaining > 0) {

                    $("#doc-count-box").html(`
            <div class="alert alert-info">
                <strong>${remaining}</strong> free document${remaining > 1 ? 's' : ''} remaining for this type.
            </div>
        `);

                } else {

                    $("#doc-count-box").html(`
            <div class="alert alert-warning">
                <strong>Note:</strong> No free requests left for this document type.
                Charge applicable: <strong>${amount.toFixed(2)}</strong><br>
                <hr>
                <strong>Total Requests for this document type:</strong> ${count} 
                <strong> Completed:</strong> ${completedCount} 
                <strong> Pending:</strong> ${pendingCount}
            </div>
        `);

                }
            }

        }

        });
    }
</script>