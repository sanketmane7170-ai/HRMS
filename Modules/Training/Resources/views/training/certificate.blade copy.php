<div class="modal-dialog modal-lg">
    <div class="modal-content">
        <div class="modal-header">
            <h3>Certificate Test: {{ $training->title }}</h3>
            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        @if($readonly)
        <a href="{{ route('backend.training.certificate', $training->id) }}" class="btn btn-success btn-sm" target="_blank">
            View Certificate
        </a>
        @endif

        @if ($readonly)
        <div class="alert alert-success">
            You have already submitted this test. Your score: <strong>{{ $score }}</strong>
        </div>
        @endif

        <form action="{{ $readonly ? '#' : route('backend.training.qa.store', $training) }}"
            method="POST"
            @if (!$readonly) class="ajax-form-submit reset" @endif>
            @csrf
            <div class="modal-body p-4">
                <div class="row">
                    @foreach ($training->questions as $index => $question)

                    <div class="mb-3">
                        <strong>Q{{ $index + 1 }}. {{ $question->question }}</strong>
                        @foreach ($question->answers as $answer)
                        @php
                        $selected = $attempts[$question->id]->selected_option ?? null;
                        $isSelected = $selected === $answer->option_label;
                        $isCorrect = $answer->is_correct;
                        @endphp
                        <div class="form-check">
                            <input class="form-check-input"
                                type="radio"
                                name="answers[{{ $question->id }}]"
                                value="{{ $answer->option_label }}"
                                id="q{{ $question->id }}{{ $answer->id }}"
                                {{ $isSelected ? 'checked' : '' }}
                                {{ $readonly ? 'disabled' : '' }}>
                            <label class="form-check-label {{ $readonly && $isCorrect ? 'text-success fw-bold' : '' }}"
                                for="q{{ $question->id }}{{ $answer->id }}">
                                ({{ strtoupper($answer->option_label) }}) {{ $answer->option_text }}
                                @if ($readonly && $isCorrect)
                                <span class="text-success">✔</span>
                                @endif
                            </label>
                        </div>
                        @endforeach
                    </div>
                    @endforeach
                </div>
            </div>

            @if (!$readonly)
            <div class="modal-footer">
                <button type="submit" class="btn btn-primary">Submit Answers</button>
            </div>
            @endif
        </form>


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