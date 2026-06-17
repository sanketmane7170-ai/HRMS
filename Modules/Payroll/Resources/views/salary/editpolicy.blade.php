<div class="modal-dialog modal-lg" style="max-width: 523px !important;">
    <div class="modal-content">
        <div class="modal-header">
            <h4 class="modal-title">{{__trans('add_employee_salary')}} : {{$user->name}}</h4>
            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <form action="{{route('backend.payroll.user.user-salaries.store',$user)}}" datatable="true" method="POST" class="ajax-form-submit reset">
            @csrf
            <div class="modal-body p-4">
                <div class="row">
                    <div class="col-md-12">
                        <div class="mb-3">
                            <label for="basic" class="form-label">{{__trans('payslip_type')}}</label>
                            <select name="payslip_type" class="form-control select-search" id="payslip_type">
                                <option value="monthly">Monthly Payslip</option>
                            </select>
                        </div>
                    </div>
                    <div class="col-md-12">
                        <div class="mb-3">
                            <label for="basic" class="form-label">{{__trans('amount')}}</label>
                            <input type="number" step="0.01" name="basic" class="form-control" id="basic" placeholder="{{__trans('basic_salary')}}">
                        </div>
                    </div>
                    <div class="col-md-12">
                        <div class="mb-3">
                            <label for="total_working_day" class="form-label">{{__trans('Total Working Days')}}</label> : &nbsp;
                            <output name="res" for="total_working_days">0</output> </br>
                            <input type="range" name="total_working_days" min="0" max="31" step="0" value="0"> 
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
