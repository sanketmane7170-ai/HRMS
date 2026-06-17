<div class="modal-dialog modal-md">
    <div class="modal-content">
        <div class="modal-header">
            <h4 class="modal-title">{{ __trans('Add Question') }}</h4>
            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
        </div>

        <form action="{{ route('question.store') }}" method="POST">
            @csrf

            <div class="modal-body">

                <div class="mb-3">
                    <label class="form-label fw-bold">{{ __trans('Question Set') }}</label>
                    <select name="question_set_id" class="form-control select2" required>
                        <option value="">{{ __trans('Select') }}</option>
                        @foreach($questionSets as $id => $name)
                        <option value="{{ $id }}">{{ $name }}</option>
                        @endforeach
                    </select>
                </div>

                <!-- Question Text -->
                <div class="form-group mb-2">
                    <label>{{ __trans('Question') }}</label>
                    <input type="text" name="question_text" class="form-control" required>
                </div>

                <!-- Max Score -->
                <div class="form-group mb-2">
                    <label>{{ __trans('Max Score') }}</label>
                    <input type="number" name="max_score" class="form-control" required>
                </div>

                <!-- Options -->
                <div class="form-group mb-2">
                    <label>{{ __trans('Options') }}</label>
                    <div id="options-wrapper">
                        <div class="option-group d-flex mb-2">
                            <input type="text" name="options[0][text]" placeholder="Option Text" class="form-control me-2" required>
                            <input type="radio" name="correct_option" value="0" class="form-check-input mt-2"> <span class="ms-1 mt-1">Correct</span>
                        </div>
                    </div>
                    <button type="button" class="btn btn-sm btn-success" onclick="addOption()">+ Add Option</button>
                </div>
            </div>

            <div class="modal-footer">
                <button type="submit" class="btn btn-primary">{{ __trans('Save') }}</button>
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">{{ __trans('Close') }}</button>
            </div>
        </form>
    </div>
</div>

<script>
    let optionIndex = 1;

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
