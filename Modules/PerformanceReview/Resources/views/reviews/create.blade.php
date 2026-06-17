<div class="modal-dialog modal-md">
    <div class="modal-content">
        <div class="modal-header">
            <h4 class="modal-title">{{ __trans('Add Performance Review') }}</h4>
            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
        </div>

        <form action="{{ route('performancereview.store') }}" method="POST" class="ajax-form-submit reset" datatable="true">
            @csrf
            <div class="modal-body p-4">



                {{-- SCORE CRITERIA --}}
                <div class="mb-3">
                    <label class="form-label fw-bold">Score Grade</label>
                    <select name="score_criteria_id" id="score_criteria_id" class="form-control select2">
                        <option value="">Select Grade</option>
                        @foreach(\Modules\PerformanceReview\Entities\ScoreCriterion::all() as $criterion)
                        <option value="{{ $criterion->id }}">{{ $criterion->title }} — {{ $criterion->description }}</option>
                        @endforeach
                    </select>
                </div>


                <!-- {{-- REVIEW DURATION --}}
                <div class="mb-3">
                    <label class="form-label fw-bold">{{ __trans('Review Duration') }}</label>
                    <select name="review_duration_id" id="review_duration_id" class="form-control select2" required>
                        @foreach($durations as $id => $label)
                        @php
                        $duration = \Modules\PerformanceReview\Entities\ReviewDuration::find($id);
                        @endphp
                        <option value="{{ $id }}"
                            data-months="{{ $duration->months }}"
                            data-label="{{ strtolower($duration->label) }}">
                            {{ $duration->months }} {{ ucfirst(strtolower($duration->label)) }}{{ $duration->months > 1 ? 's' : '' }}
                        </option>
                        @endforeach
                    </select>
                </div> -->


                {{-- EMPLOYEE NAME --}}
                <div class="mb-3">
                    <label for="user_id" class="form-label">{{ __trans('Employee Name') }}</label>
                    <select id="user_id" name="employees[]" class="form-control ajax-select2" multiple>
                        <option value="">{{ __trans('search_employee ...') }}</option>
                    </select>
                </div>



                {{-- QUESTION SET --}}
                <div class="mb-3">
                    <label class="form-label fw-bold">{{ __trans('Question Set') }}</label>
                    <select name="question_set_id" class="form-control select2" required>
                        <option value="">{{ __trans('Select') }}</option>
                        @foreach($questionSets as $id => $name)
                        <option value="{{ $id }}">{{ $name }}</option>
                        @endforeach
                    </select>
                </div>

                {{-- STATUS --}}
                <div class="mb-3">
                    <label class="form-label fw-bold">{{ __trans('Status') }}</label>
                    <select name="status" class="form-control" required>
                        @foreach(['Pending', 'In Progress', 'Completed', 'Declined'] as $status)
                        <option value="{{ $status }}">{{ $status }}</option>
                        @endforeach
                    </select>
                </div>

                {{-- START DATE --}}
                <div class="mb-3">
                    <label class="form-label fw-bold">{{ __trans('Start Date') }}</label>
                    <input type="date" name="start_date" class="form-control" required>
                </div>

            </div>

            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">{{ __trans('close') }}</button>
                <button type="submit" class="btn btn-info">{{ __trans('save') }}</button>
            </div>
        </form>
    </div>
</div>

<script>
    $('#user_id').select2({
        placeholder: '{{ __trans("search_employee ...") }}',
        dropdownParent: $('#user_id').closest('.modal'), // this fixes the modal render issue
        width: '100%' // optional, ensure it stretches full
    });

    function loadEmployeesByDuration(label, rawValue) {
        let months = (label === 'year') ? parseInt(rawValue) * 12 : parseInt(rawValue);

        $.ajax({
            url: "{{ route('ajax.select2.fetch.userswithselectbymonth') }}",
            data: {
                months: months,
                label: label
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
    }

    // $('#review_duration_id').on('change', function() {
    //     let selected = $(this).find(':selected');
    //     let rawMonths = selected.data('months') || 1;
    //     let label = (selected.data('label') || 'month').toLowerCase();

    //     // Ensure select2 is initialized
    //     if (!$.fn.select2.defaults) {
    //         $('#user_id').select2({
    //             placeholder: '{{ __trans("search_employee ...") }}'
    //         });
    //     }

    //     loadEmployeesByDuration(label, rawMonths);
    // });

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


    // Trigger once on modal load
    // $('#review_duration_id').trigger('change');
</script>