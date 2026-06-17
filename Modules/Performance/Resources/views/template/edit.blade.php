<div class="modal-dialog modal-xl">
    <div class="modal-content">
        <div class="modal-header">
            <h4 class="modal-title">{{ __trans('Edit Appraisal Template') }}</h4>
            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
        </div>

        <form action="{{ route('performance.template.update', $template->id) }}"
              method="POST"
              class="ajax-form-submit reset"
              datatable="true"
              id="editTemplateForm">
            @csrf
            @method('PUT')

            <div class="modal-body p-4">

                {{-- Template Name --}}
                <div class="mb-4">
                    <label class="form-label fw-bold">{{ __trans('Template Name') }}</label>
                    <input type="text" name="name" class="form-control" value="{{ $template->name }}" required>
                </div>

                {{-- Period Type --}}
                <div class="mb-4">
                    <label class="form-label fw-bold">{{ __trans('Period Type') }}</label>
                    <select name="period_type" class="form-control select-search" required>
                        <option value="">{{ __trans('select_a_option') }}</option>
                        @foreach(['daily','weekly','monthly','quarterly','half_yearly','yearly'] as $type)
                            <option value="{{ $type }}" {{ $template->period_type == $type ? 'selected' : '' }}>
                                {{ ucfirst(str_replace('_',' ',$type)) }}
                            </option>
                        @endforeach
                    </select>
                </div>


                {{-- Branch --}}
                <div class="mb-4">
                    <label class="form-label fw-bold">{{ __trans('Branch') }}</label>
                    <select name="branch_id" class="form-control select-search">
                        <option value="">{{ __trans('select_a_option') }}</option>
                        @foreach($branches as $branch)
                        <option value="{{ $branch->id }}" {{ $template->branch_id == $branch->id ? 'selected' : '' }}>{{ $branch->name }}</option>
                        @endforeach
                    </select>
                </div>

                {{-- Department --}}
                <div class="mb-4">
                    <label class="form-label fw-bold">{{ __trans('Department') }}</label>
                    <select name="department_id" class="form-control select-search">
                        <option value="">{{ __trans('select_a_option') }}</option>
                        @foreach($departments as $department)
                        <option value="{{ $department->id }}" {{ $template->department_id == $department->id ? 'selected' : '' }}>{{ $department->name }}</option>
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
                    <tbody>
                        @php $index = 0; @endphp
                        @foreach($template->criteria as $c)
                            <tr>
                                <td>
                                    <input type="text" name="criteria[{{ $index }}][name]" class="form-control" value="{{ $c->criteria_name }}" required>
                                </td>
                                <td>
                                    <input type="text" name="criteria[{{ $index }}][description]" class="form-control" value="{{ $c->description }}">
                                </td>
                                <td>
                                    <input type="number" name="criteria[{{ $index }}][weight]" class="form-control" value="{{ $c->weight }}" required>
                                </td>
                                <td>
                                    <input type="number" name="criteria[{{ $index }}][max_score]" class="form-control" value="{{ $c->max_score }}" required>
                                </td>
                                <td>
                                    <input type="text" name="criteria[{{ $index }}][comments]" class="form-control" value="{{ $c->comments }}">
                                </td>
                                <td>
                                    <button class="btn btn-danger btn-sm removeRow">×</button>
                                </td>
                            </tr>
                            @php $index++; @endphp
                        @endforeach
                    </tbody>
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
                    {{ __trans('update') }}
                </button>
            </div>
        </form>
    </div>
</div>

<script>
    initselect2search();

    (function(){
    let index = {{ $index ?? 0 }};
    $('#addCriteria').click(function(){
        $('#criteriaTable tbody').append(`
            <tr>
                <td><input type="text" name="criteria[${index}][name]" class="form-control" required></td>
                <td><input type="text" name="criteria[${index}][description]" class="form-control"></td>
                <td><input type="number" name="criteria[${index}][weight]" class="form-control" required></td>
                <td><input type="number" name="criteria[${index}][max_score]" class="form-control" required></td>
                <td><input type="text" name="criteria[${index}][comments]" class="form-control"></td>
                <td><button class="btn btn-danger btn-sm removeRow">×</button></td>
            </tr>
        `);
        index++;
    });

    $(document).on('click','.removeRow',function(e){
        e.preventDefault();
        $(this).closest('tr').remove();
    });
})();

</script>
