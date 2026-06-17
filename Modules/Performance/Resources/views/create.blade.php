<div class="modal-dialog modal-xl">
<div class="modal-dialog modal-xl">
    <div class="modal-content">
        <div class="modal-header">
            <h4 class="modal-title">{{ __trans('Add Performance Appraisal') }}</h4>
            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>

        <form action="{{ route('performance.store') }}" method="POST" class="ajax-form-submit reset" datatable="true">
            @csrf
            <div class="modal-body p-4">

                {{-- Reporting Manager --}}
                

                @php
                $authId = auth()->id();
                $authIsManager = collect($managers)->pluck('id')->contains($authId);
                @endphp

                <div class="mb-4">
                    <label for="reporting_manager_id" class="form-label fw-bold">
                        {{ __trans('Reporting Manager') }}
                    </label>

                    <select name="reporting_manager_id" id="reporting_manager_id" class="form-control select-search"
                        {{ $authIsManager ? 'disabled' : '' }}>

                        <option value="">{{ __trans('All / Default') }}</option>

                        @foreach ($managers as $manager)
                        <option value="{{ $manager->id }}"
                            {{ $authIsManager && $manager->id == $authId ? 'selected' : '' }}>
                            {{ $manager->name }}
                        </option>
                        @endforeach
                    </select>

                    {{-- Hidden field so disabled value is submitted --}}
                    @if($authIsManager)
                    <input type="hidden" name="reporting_manager_id" value="{{ $authId }}">
                    @endif
                </div>



                {{-- Employee Select --}}

                {{-- Reporting Manager --}}
                

                @php
                $authId = auth()->id();
                $authIsManager = collect($managers)->pluck('id')->contains($authId);
                @endphp

                <div class="mb-4">
                    <label for="reporting_manager_id" class="form-label fw-bold">
                        {{ __trans('Reporting Manager') }}
                    </label>

                    <select name="reporting_manager_id" id="reporting_manager_id" class="form-control select-search"
                        {{ $authIsManager ? 'disabled' : '' }}>

                        <option value="">{{ __trans('All / Default') }}</option>

                        @foreach ($managers as $manager)
                        <option value="{{ $manager->id }}"
                            {{ $authIsManager && $manager->id == $authId ? 'selected' : '' }}>
                            {{ $manager->name }}
                        </option>
                        @endforeach
                    </select>

                    {{-- Hidden field so disabled value is submitted --}}
                    @if($authIsManager)
                    <input type="hidden" name="reporting_manager_id" value="{{ $authId }}">
                    @endif
                </div>



                {{-- Employee Select --}}
                <div class="mb-4">
                    <label for="employee_id" class="form-label fw-bold">{{ __trans('Employee') }}</label>
                    <select name="employee_id" id="employee_id" class="form-control select-search" required>
                        <option value="">{{ __trans('select_a_option') }}</option>
                        @foreach ($employees as $employee)
                        <option value="{{ $employee->id }}">{{ $employee->name }}</option>
                        @endforeach
                    </select>
                </div>

                <!-- {{-- Period --}}
                <div class="mb-4">
                    <label for="period" class="form-label fw-bold">{{ __trans('Appraisal Period') }}</label>
                    <input type="text" name="period" class="form-control datetime" placeholder="e.g. Q2-2025" required>
                </div> -->
                {{-- Appraisal Date --}}
                <div class="mb-4">
                    <label for="appraisal_date" class="form-label fw-bold">
                        {{ __trans('Appraisal Date') }}
                    </label>
                    <input type="text" name="appraisal_date" id="appraisal_date" class="form-control appraisal-date"
                        placeholder="YYYY-MM-DD" required>
                </div>

                {{-- Branch --}}
                <div class="mb-4">
                    <label for="branch_id" class="form-label fw-bold">{{ __trans('Branch') }}</label>
                    <select name="branch_id" id="branch_id" class="form-control select-search">
                        <option value="">{{ __trans('Branches') }}</option>
                        @foreach ($branches as $branch)
                        <option value="{{ $branch->id }}">{{ $branch->name }}</option>
                        @endforeach
                    </select>
                </div>



                <!-- {{-- Template Select --}}
                <div class="mb-4">
                    <label for="template_id" class="form-label fw-bold">{{ __trans('Appraisal Template') }}</label>
                    <select name="template_id" id="template_id" class="form-control select-search" required>
                        <option value="">{{ __trans('select_a_option') }}</option>
                        @foreach ($templates as $template)
                        <option value="{{ $template->id }}">{{ $template->name }}</option>
                        @endforeach
                    </select>
                </div> -->

                {{-- Template Select --}}
                <div class="mb-4">
                    <label for="template_id" class="form-label fw-bold">{{ __trans('Appraisal Template') }}</label>
                    <select name="template_id" id="template_id" class="form-control select-search" required>
                        <option value="">{{ __trans('select_a_option') }}</option>
                    </select>
                </div>


                {{-- Criteria Table --}}
                <div class="mb-4">
                    <label class="form-label fw-bold">{{ __trans('Appraisal Criteria') }}</label>
                    <table class="table table-bordered" id="criteriaTable">
                        <thead>
                            <tr class="light">
                                <th>{{ __trans('Name') }}</th>
                                <th>{{ __trans('Description') }}</th>
                                <th>{{ __trans('Weight') }}</th>
                                <th>{{ __trans('Max Score') }}</th>
                                <th>{{ __trans('Reviewer Score') }}</th>
                                <th>{{ __trans('Self Score') }}</th>
                            </tr>
                        </thead>
                        <tbody>
                            {{-- Filled dynamically --}}
                        </tbody>
                    </table>
                </div>

                <div class="mb-4">
                    <label class="form-label fw-bold">{{ __trans('Reviewer Comments') }}</label>
                    <textarea name="reviewer_comments" class="form-control"
                        rows="4"></textarea>
                </div>

            </div>

            <div class="modal-footer">
                <button type="button" class="btn btn-secondary waves-effect"
                    data-bs-dismiss="modal">{{ __trans('close') }}</button>
                <button type="submit" class="btn btn-info waves-effect waves-light">{{ __trans('save') }}</button>
            </div>
        </form>
    </div>
</div>

<script>
initselect2search();
loadAjaxSelect2();
flatpickr(".appraisal-date", {
    enableTime: false,
    dateFormat: "Y-m-d",
    defaultDate: "today" // ✅ auto-fill today
});
// When template is selected, load criteria via AJAX
$('#template_id').change(function() {
    let templateId = $(this).val();
    if (!templateId) return;

    $.get(`/performance/template/${templateId}/criteria`, function(res) {
        if (res.success) {
            let tbody = '';
            res.criteria.forEach((c, i) => {
                tbody += `
                    <tr class="light">
                        <td>
                            ${c.name}
                            <input type="hidden" name="criteria[${i}][template_criteria_id]" value="${c.id}">
                        </td>
                        <td>${c.description ?? ''}</td>
                        <td>${c.weight}</td>
                        <td>${c.max_score ?? ''}</td>
                        <td>
                            <input type="number"
                                name="criteria[${i}][score]"
                                class="form-control"
                                min="0"
                                max="${c.max_score ?? 10}"
                                value=${c.max_score ?? ''}>
                        </td>
                        <td>
                            <input type="number"
                                name="criteria[${i}][self_score]"
                                class="form-control"
                                min="0"
                                max="${c.max_score ?? 10}"
                                value="">
                        </td>
                    </tr>`;
            });
            $('#criteriaTable tbody').html(tbody);
        }
    });
});
</script>
<script>
function loadTemplates(branchId = null) {
    $('#template_id').html('<option value="">{{ __trans("select_a_option") }}</option>');

    $.get('{{ url("performance/templates/by-branch") }}', {
        branch_id: branchId
    }, function(res) {
        res.forEach(template => {
            $('#template_id').append(
                `<option value="${template.id}">${template.name}</option>`
            );
        });

        $('#template_id').trigger('change.select2');
    });
}

// Initial load → all templates
loadTemplates();

// On branch change
$('#branch_id').on('change', function() {
    let branchId = $(this).val();
    loadTemplates(branchId);
});
</script>
<script>
$('#reporting_manager_id').on('change', function() {
    let managerId = $(this).val();
    let employeeSelect = $('#employee_id');

    employeeSelect.html('<option value="">Loading...</option>');

    // If manager selected → load manager employees
    if (managerId) {
        $.get(`/performance/employees/by-manager/${managerId}`, function(res) {
            employeeSelect.html('<option value="">{{ __trans("select_a_option") }}</option>');
            res.forEach(emp => {
                employeeSelect.append(
                    `<option value="${emp.id}">${emp.name}</option>`
                );
            });
            employeeSelect.trigger('change.select2');
        });
    }
    // If not selected → reload default employees
    else {
        employeeSelect.html(`
            <option value="">{{ __trans('select_a_option') }}</option>
            @foreach ($employees as $employee)
                <option value="{{ $employee->id }}">{{ $employee->name }}</option>
            @endforeach
        `);
        employeeSelect.trigger('change.select2');
    }
});
</script>