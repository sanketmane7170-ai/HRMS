<div class="modal-dialog modal-xl">
    <div class="modal-content">
        <div class="modal-header">
            <h4 class="modal-title">{{ __trans('Create Appraisal Template') }}</h4>
            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
        </div>

        <form action="{{ route('performance.template.store') }}" method="POST" class="ajax-form-submit reset"
            datatable="true" id="createTemplateForm">
            @csrf

            <div class="modal-body p-4">

                {{-- Template Name --}}
                <div class="mb-4">
                    <label class="form-label fw-bold">{{ __trans('Template Name') }}</label>
                    <input type="text" name="name" class="form-control" required>
                </div>

                {{-- Period Type --}}
                <div class="mb-4">
                    <label class="form-label fw-bold">{{ __trans('Period Type') }}</label>
                    <select name="period_type" class="form-control select-search" required>
                        <option value="">{{ __trans('select_a_option') }}</option>
                        <option value="daily">Daily</option>
                        <option value="weekly">Weekly</option>
                        <option value="monthly">Monthly</option>
                        <option value="quarterly">Quarterly</option>
                        <option value="half_yearly">Half Yearly</option>
                        <option value="yearly">Yearly</option>
                    </select>
                </div>

                {{-- Branch --}}
                <div class="mb-4">
                    <label class="form-label fw-bold">{{ __trans('Branch') }}</label>
                    <select name="branch_id" class="form-control select-search">
                        <option value="">{{ __trans('select_a_option') }}</option>
                        @foreach($branches as $branch)
                        <option value="{{ $branch->id }}">{{ $branch->name }}</option>
                        @endforeach
                    </select>
                </div>

                {{-- Department --}}
                <div class="mb-4">
                    <label class="form-label fw-bold">{{ __trans('Department') }}</label>
                    <select name="department_id" class="form-control select-search">
                        <option value="">{{ __trans('select_a_option') }}</option>
                        @foreach($departments as $department)
                        <option value="{{ $department->id }}">{{ $department->name }}</option>
                        @endforeach
                    </select>
                </div>


                <hr>

                {{-- Criteria Table --}}
                <h5>{{ __trans('Criteria') }}</h5>
                <table class="table table-bordered" id="criteriaTable">
                    <thead>
                        <tr class="light">
                            <th>{{ __trans('Name') }}</th>
                            <th>{{ __trans('Description') }}</th>
                            <th>{{ __trans('Weight') }}</th>
                            <th>{{ __trans('Max Score') }}</th>
                            <th>{{ __trans('Comments') }}</th>
                            <th width="50">{{ __trans('Action') }}</th>
                        </tr>
                    </thead>
                    <tbody></tbody>
                </table>
                <button type="button" class="btn btn-sm btn-secondary" id="addCriteria">
                    {{ __trans('Add Criteria') }}
                </button>

            </div>

            <div class="modal-footer">
                <button class="btn btn-secondary" data-bs-dismiss="modal">
                    {{ __trans('close') }}
                </button>
                <button type="submit" class="btn btn-info">
                    {{ __trans('save') }}
                </button>
            </div>
        </form>
    </div>
</div>

<script>
initselect2search();

let index = 0;

$('#addCriteria').click(function() {
    $('#criteriaTable tbody ').append(`
            <tr>
                <td><input type="text" name="criteria[${index}][name]" class="form-control" required></td>
                <td><input type="text" name="criteria[${index}][description]" class="form-control"></td>
                <td><input type="number" name="criteria[${index}][weight]" class="form-control" required></td>
                <td><input type="number" name="criteria[${index}][max_score]" class="form-control" required></td>
                <td><input type="text" name="criteria[${index}][comments]" class="form-control"></td>
                <td>
                    <button class="btn btn-danger btn-sm removeRow">×</button>
                </td>
            </tr>
        `);
    index++;
});

// Remove row
$(document).on('click', '.removeRow', function(e) {
    e.preventDefault();
    $(this).closest('tr').remove();
});
</script>