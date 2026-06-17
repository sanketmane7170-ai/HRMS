<div class="modal-dialog modal-lg">
    <div class="modal-content">
        <div class="modal-header">
            <h3>Manage Questions for: {{ $training->title }}</h3>
            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <form action="{{ route('backend.training.questions.store', $training) }}" datatable="true" method="POST"
            class="ajax-form-submit reset">
            @csrf
            <div class="modal-body p-4">
                <div class="row">
                    <div class="mb-3">
                        <label>Question</label>
                        <input type="text" name="question" class="form-control" required>
                    </div>

                     

                    @foreach (['a', 'b', 'c', 'd'] as $label)
                    <div class="mb-2">
                        <label>Option {{ strtoupper($label) }}</label>
                        <input type="text" name="answers[{{ $label }}][option_text]" class="form-control" required>
                        <input type="hidden" name="answers[{{ $label }}][option_label]" value="{{ $label }}">
                    </div>
                    @endforeach

                    <div class="mb-3">
                        <label>Correct Option</label>
                        <select name="correct_option" class="form-control" required>
                            <option value="">--Select--</option>
                            @foreach (['a', 'b', 'c', 'd'] as $label)
                            <option value="{{ $label }}">{{ strtoupper($label) }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="mb-2">
                        <label>Duration (Sec.)</label>
                        <input type="number" value="30" name="duration" class="form-control" required>
                    </div>

                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary"
                            data-bs-dismiss="modal">{{ __trans('close') }}</button>
                        <button type="submit" class="btn btn-primary">{{ __trans('save_question') }}</button>
                    </div>
                </div>

            </div>

        </form>

        <hr>

        <h4>Existing Questions</h4>
        @foreach ($training->questions as $q)
        <div class="card mb-2" id="question-card-{{ $q->id }}">
            <div class="card-body light">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <strong>{{ $q->question }}</strong>
                        <ul class="mb-0">
                            @foreach ($q->answers as $a)
                            <li>
                                ({{ strtoupper($a->option_label) }}) {{ $a->option_text }}
                                @if ($a->is_correct) <strong class="text-success">✔</strong> @endif
                            </li>
                            @endforeach
                        </ul>
                    </div>
                    <button class="btn btn-danger btn-sm ms-2"
                        onclick="deleteQuestion({{ $training->id }}, {{ $q->id }})">Delete</button>
                </div>
            </div>
        </div>
        @endforeach


    </div>
</div>
<script>
    initselect2search();
    loadAjaxSelect2();

    flatpickr("input.datetime", {
        enableTime: true,
        // minDate: "today",
        dateFormat: "Y-m-d",
    });
</script>
<script>
    function deleteQuestion(trainingId, questionId) {
        if (!confirm('Are you sure you want to delete this question?')) return;

        $.ajax({
            url: `/training/${trainingId}/questions/${questionId}`,
            type: 'DELETE',
            data: {
                _token: '{{ csrf_token() }}'
            },
            success: function(res) {
                if (res.success) {
                    $('#question-card-' + questionId).remove();
                } else {
                    alert('Failed to delete question');
                }
            },
            error: function() {
                alert('Error occurred while deleting');
            }
        });
    }
</script>