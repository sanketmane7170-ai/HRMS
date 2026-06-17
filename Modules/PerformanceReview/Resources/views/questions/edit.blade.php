<div class="modal-dialog modal-md">
    <div class="modal-content">
        <div class="modal-header">
            <h4 class="modal-title">{{ __trans('Edit Question') }}</h4>
            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
        </div>

        <form action="{{ route('question.update', $question->id) }}" method="POST">
            @csrf
            @method('PUT')

            <div class="modal-body">
                <div class="mb-3">
                    <label class="form-label fw-bold">{{ __trans('Question Set') }}</label>
                    <select name="question_set_id" class="form-control select2" required>
                        <option value="">{{ __trans('Select') }}</option>
                        @foreach($questionSets as $id => $name)
                            <option value="{{ $id }}" {{ $question->question_set_id == $id ? 'selected' : '' }}>{{ $name }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="form-group mb-2">
                    <label>{{ __trans('Question') }}</label>
                    <input type="text" name="question_text" class="form-control" value="{{ $question->question_text }}" required>
                </div>

                <div class="form-group mb-2">
                    <label>{{ __trans('Max Score') }}</label>
                    <input type="number" name="max_score" class="form-control" value="{{ $question->max_score }}" required>
                </div>

                <div class="form-group mb-2">
                    <label>{{ __trans('Options') }}</label>
                    <div id="options-wrapper">
                        @foreach($question->options as $index => $option)
                            <div class="option-group d-flex mb-2">
                                <input type="text" name="options[{{ $index }}][text]" value="{{ $option->option_text }}" class="form-control me-2" required>
                                <input type="radio" name="correct_option" value="{{ $index }}" class="form-check-input mt-2" {{ $option->is_correct ? 'checked' : '' }}>
                                <span class="ms-1 mt-1">Correct</span>
                            </div>
                        @endforeach
                    </div>
                    <button type="button" class="btn btn-sm btn-success" onclick="addOption()">+ Add Option</button>
                </div>
            </div>

            <div class="modal-footer">
                <button type="submit" class="btn btn-primary">{{ __trans('Update') }}</button>
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">{{ __trans('Close') }}</button>
            </div>
        </form>
    </div>
</div>

<script>
    let optionIndex = {{ count($question->options) }};

    function addOption() {
        const wrapper = document.getElementById('options-wrapper');
        const html = `
            <div class="option-group d-flex mb-2">
                <input type="text" name="options[${optionIndex}][text]" placeholder="Option Text" class="form-control me-2" required>
                <input type="radio" name="correct_option" value="${optionIndex}" class="form-check-input mt-2">
                <span class="ms-1 mt-1">Correct</span>
            </div>
        `;
        wrapper.insertAdjacentHTML('beforeend', html);
        optionIndex++;
    }
</script>
