<div class="modal-dialog modal-lg" style="max-width: 523px !important;">
    <div class="modal-content">
        <div class="modal-header">
            <h4 class="modal-title">{{__trans('edit_taxes')}} </h4>
            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <form action="{{ route('backend.payroll.employee-taxes.store') }}" datatable="true" method="POST" class="ajax-form-submit reset" oninput="res.value = total_working_days.value">
            @csrf
            @method('PUT')
            <div class="modal-body p-4">
                <div class="row">
                    <div class="col-md-12">
                        <div class="mb-3">
                            <label for="taxtype">Tax Name</label>
                            <input type="text" name="taxtype" class="form-control" required>
                        </div>
                    </div>
                    <div class="col-md-12">
                        <div class="mb-3">
                            <label for="taxunit">Tax Unit</label>
                            <select name="taxunit" class="form-control select" id="taxunit">
                                <option value="percent">Percentage</option>
                                <option value="flat">Flat</option>
                            </select>
                        </div>
                    </div>
                    <div class="col-md-12">
                        <div class="mb-3">
                            <label for="taxamount">Tax Amount</label>
                            <input type="text" name="taxamount" class="form-control" pattern="\d+(\s\d+\/\d+)?" title="Enter a whole number or a whole number with fraction" required>
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