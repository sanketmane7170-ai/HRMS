@php
$formulas = [];
$policy = null;

if(isset($payrollpolicy) && $payrollpolicy->count()){
    $policy = $payrollpolicy->first();
    $formulas = json_decode($policy->policy,true);
}
@endphp

<div class="modal-dialog modal-lg">
<div class="modal-content">

<div class="modal-header">
<h4 class="modal-title">Payroll Policy</h4>
<button type="button" class="btn-close" data-bs-dismiss="modal"></button>
</div>

<form action="{{ route('backend.settings.payrollPolicy') }}" method="POST"
datatable="true"
class="ajax-form-submit reset">

@csrf

<div class="modal-body p-4">

<div class="row mb-3">

<div class="col-md-6">
<label>Policy Name</label>
<input type="text" name="name" class="form-control"
value="{{ $policy->name ?? '' }}" required>
</div>

<div class="col-md-6">
<label>Description</label>
<input type="text" name="description" class="form-control"
value="{{ $policy->description ?? '' }}">
</div>

</div>

<hr>

<h5>Salary Formula Builder</h5>

<table class="table table-bordered light" id="formulaTable">

<thead>
<tr>
<th width="20%">Result</th>
<th width="20%">Source</th>
<th width="15%">Operator</th>
<th width="25%">Value</th>
<th width="10%">Action</th>
</tr>
</thead>

<tbody>

@if(!empty($formulas))

@foreach($formulas as $key => $row)

<tr>

<td>
<select name="policy[{{$key}}][result]" class="form-control">
<option value="one_day_salary" {{ $row['result']=='one_day_salary' ? 'selected' : '' }}>One Day Salary</option>
<option value="absent_salary" {{ $row['result']=='absent_salary' ? 'selected' : '' }}>Absent Salary</option>
<option value="net_salary" {{ $row['result']=='net_salary' ? 'selected' : '' }}>Net Salary</option>
</select>
</td>

<td>
<select name="policy[{{$key}}][source]" class="form-control">
<option value="gross" {{ $row['source']=='gross' ? 'selected' : '' }}>Gross</option>
<option value="gross_plus_allowance" {{ $row['source']=='gross_plus_allowance' ? 'selected' : '' }}>Gross + Allowance</option>
<option value="one_day_salary" {{ $row['source']=='one_day_salary' ? 'selected' : '' }}>One Day Salary</option>
<option value="absent_salary" {{ $row['source']=='absent_salary' ? 'selected' : '' }}>Absent Salary</option>
<option value="days_in_month" {{ $row['source']=='days_in_month' ? 'selected' : '' }}>Days In Month</option>
<option value="working_days" {{ $row['source']=='working_days' ? 'selected' : '' }}>Working Days</option>
<option value="absent_days" {{ $row['source']=='absent_days' ? 'selected' : '' }}>Absent Days</option>
</select>
</td>

<td>
<select name="policy[{{$key}}][operator]" class="form-control">
<option value="/" {{ $row['operator']=='/' ? 'selected' : '' }}>Divide</option>
<option value="*" {{ $row['operator']=='*' ? 'selected' : '' }}>Multiply</option>
<option value="-" {{ $row['operator']=='-' ? 'selected' : '' }}>Minus</option>
<option value="+" {{ $row['operator']=='+' ? 'selected' : '' }}>Plus</option>
</select>
</td>

<td>
<select name="policy[{{$key}}][value]" class="form-control">
<option value="30" {{ $row['value']=='30' ? 'selected' : '' }}>30</option>
<option value="one_day_salary" {{ $row['value']=='one_day_salary' ? 'selected' : '' }}>One Day Salary</option>
<option value="absent_salary" {{ $row['value']=='absent_salary' ? 'selected' : '' }}>Absent Salary</option>
<option value="days_in_month" {{ $row['value']=='days_in_month' ? 'selected' : '' }}>Days In Month</option>
<option value="working_days" {{ $row['value']=='working_days' ? 'selected' : '' }}>Working Days</option>
<option value="absent_days" {{ $row['value']=='absent_days' ? 'selected' : '' }}>Absent Days</option>
</select>
</td>

<td>
@if($key==0)
<button type="button" class="btn btn-success addRow">+</button>
@else
<button type="button" class="btn btn-danger removeRow">X</button>
@endif
</td>

</tr>

@endforeach

@else

<tr>

<td>
<select name="policy[0][result]" class="form-control">
<option value="one_day_salary">One Day Salary</option>
<option value="absent_salary">Absent Salary</option>
<option value="net_salary">Net Salary</option>
</select>
</td>

<td>
<select name="policy[0][source]" class="form-control">
<option value="gross">Gross</option>
<option value="gross_plus_allowance">Gross + Allowance</option>
<option value="one_day_salary">One Day Salary</option>
<option value="absent_salary">Absent Salary</option>
<option value="days_in_month">Days In Month</option>
<option value="working_days">Working Days</option>
<option value="absent_days">Absent Days</option>
</select>
</td>

<td>
<select name="policy[0][operator]" class="form-control">
<option value="/">Divide</option>
<option value="*">Multiply</option>
<option value="-">Minus</option>
<option value="+">Plus</option>
</select>
</td>

<td>
<select name="policy[0][value]" class="form-control">
<option value="30">30</option>
<option value="one_day_salary">One Day Salary</option>
<option value="absent_salary">Absent Salary</option>
<option value="days_in_month">Days In Month</option>
<option value="working_days">Working Days</option>
<option value="absent_days">Absent Days</option>
</select>
</td>

<td>
<button type="button" class="btn btn-success addRow">+</button>
</td>

</tr>

@endif

</tbody>
</table>

</div>

<div class="modal-footer">

<button type="submit" class="btn btn-info">Save Policy</button>

@if(isset($policy))
<button type="button" 
        class="btn btn-danger deletePolicy" 
        data-id="{{ $policy->id }}">
    Delete Policy
</button>
@endif

<button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
Close
</button>

</div>

</form>

</div>
</div>

<script>

let row = $('#formulaTable tbody tr').length;

$(document).on('click','.addRow',function(){

let html = `
<tr>

<td>
<select name="policy[${row}][result]" class="form-control">
<option value="one_day_salary">One Day Salary</option>
<option value="absent_salary">Absent Salary</option>
<option value="net_salary">Net Salary</option>
</select>
</td>

<td>
<select name="policy[${row}][source]" class="form-control">
<option value="gross">Gross</option>
<option value="gross_plus_allowance">Gross + Allowance</option>
<option value="one_day_salary">One Day Salary</option>
<option value="absent_salary">Absent Salary</option>
<option value="days_in_month">Days In Month</option>
<option value="working_days">Working Days</option>
<option value="absent_days">Absent Days</option>
</select>
</td>

<td>
<select name="policy[${row}][operator]" class="form-control">
<option value="/">Divide</option>
<option value="*">Multiply</option>
<option value="-">Minus</option>
<option value="+">Plus</option>
</select>
</td>

<td>
<select name="policy[${row}][value]" class="form-control">
<option value="30">30</option>
<option value="one_day_salary">One Day Salary</option>
<option value="absent_salary">Absent Salary</option>
<option value="days_in_month">Days In Month</option>
<option value="working_days">Working Days</option>
<option value="absent_days">Absent Days</option>
</select>
</td>

<td>
<button type="button" class="btn btn-danger removeRow">X</button>
</td>

</tr>
`;

$('#formulaTable tbody').append(html);

row++;

});

$(document).on('click','.removeRow',function(){
$(this).closest('tr').remove();
});
$(document).on('click', '.deletePolicy', function () {

    let id = $(this).data('id');

    if (!confirm('Are you sure you want to delete this policy?')) {
        return;
    }

    $.ajax({
        url: "{{ route('backend.settings.deletePayrollPolicy', '') }}/" + id,
        type: "DELETE",
        data: {
            _token: "{{ csrf_token() }}"
        },
        success: function (response) {

            if (response.success) {
                alert(response.message);

                // close modal
                $('.modal').modal('hide');

                // reload page or refresh list
                location.reload();
            } else {
                alert(response.message);
            }
        }
    });

});
</script>

