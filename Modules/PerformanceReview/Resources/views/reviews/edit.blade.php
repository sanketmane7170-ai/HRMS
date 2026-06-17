<div class="modal-dialog modal-md">
    <div class="modal-content">
        <div class="modal-header">
            <h4 class="modal-title">{{ __trans('Edit Performance Review') }}</h4>
            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
        </div>

        <form action="{{ route('performancereview.update', $review->id) }}" method="POST" class="ajax-form-submit reset" datatable="true">
            @csrf
            @method('PUT')
            <div class="modal-body p-4">

                {{-- SCORE CRITERIA --}}
                <div class="mb-3">
                    <label class="form-label fw-bold">Score Grade</label>
                    <select name="score_criteria_id" id="score_criteria_id" class="form-control select2">
                        <option value="">Select Grade</option>
                        @foreach(\Modules\PerformanceReview\Entities\ScoreCriterion::all() as $criterion)
                            <option value="{{ $criterion->id }}"
                                {{ $review->score_criteria_id == $criterion->id ? 'selected' : '' }}>
                                {{ $criterion->title }} — {{ $criterion->description }}
                            </option>
                        @endforeach
                    </select>
                </div>

                {{-- EMPLOYEE NAME --}}
                <div class="mb-3">
                    <label for="user_id" class="form-label">{{ __trans('Employee Name') }}</label>
                    <select id="user_id" name="employees[]" class="form-control ajax-select2" multiple>
                        @foreach($employees as $id => $name)
                            <option value="{{ $id }}" {{ in_array($id, $selectedEmployeeIds) ? 'selected' : '' }}>
                                {{ $name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                {{-- QUESTION SET --}}
                <div class="mb-3">
                    <label class="form-label fw-bold">{{ __trans('Question Set') }}</label>
                    <select name="question_set_id" class="form-control select2" required>
                        <option value="">{{ __trans('Select') }}</option>
                        @foreach($questionSets as $id => $name)
                            <option value="{{ $id }}" {{ $review->question_set_id == $id ? 'selected' : '' }}>
                                {{ $name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                {{-- STATUS --}}
                <div class="mb-3">
                    <label class="form-label fw-bold">{{ __trans('Status') }}</label>
                    <select name="status" class="form-control" required>
                        @foreach(['Pending', 'In Progress', 'Completed', 'Declined'] as $status)
                            <option value="{{ $status }}" {{ $review->status == $status ? 'selected' : '' }}>
                                {{ $status }}
                            </option>
                        @endforeach
                    </select>
                </div>

                {{-- START DATE --}}
                <div class="mb-3">
                    <label class="form-label fw-bold">{{ __trans('Start Date') }}</label>
                    <input type="date" name="start_date" class="form-control" value="{{ $review->start_date->format('Y-m-d') }}" required>
                </div>

            </div>

            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">{{ __trans('close') }}</button>
                <button type="submit" class="btn btn-info">{{ __trans('update') }}</button>
            </div>
        </form>
    </div>
</div>

<script>
    $('#user_id').select2({
        placeholder: '{{ __trans("search_employee ...") }}',
        dropdownParent: $('#user_id').closest('.modal'),
        width: '100%'
    });

    $('#score_criteria_id').on('change', function() {
        let grade = $(this).val();

        $.ajax({
            url: "{{ route('ajax.select2.fetch.usersbygrade') }}",
            data: {
                grade: grade
            },
            success: function(response) {
                let employees = response.data || [];
                $('#user_id').empty();

                employees.forEach(function(item) {
                    let newOption = new Option(item.text, item.id, false, false);
                    $('#user_id').append(newOption);
                });

                $('#user_id').trigger('change');
            }
        });
    });
</script>
