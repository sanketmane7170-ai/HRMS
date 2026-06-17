<div class="modal-dialog modal-lg">
    <div class="modal-content">

        @if(!$isCompleted)
        <form action="{{ route('backend.employee.reviewresponse.submit', $review->id) }}" method="POST" class="ajax-form-submit reset">
            @csrf
            @endif

            <div class="modal-header">
                <h4 class="modal-title">Respond to: {{ $review->questionSet->name }}</h4>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>

            <div class="modal-body">
                @foreach($review->questionSet->questions as $index => $question)
                <div class="mb-4">
                    <label class="form-label fw-bold">
                        Q{{ $index + 1 }}. {{ $question->question_text }}
                    </label>

                    @foreach($question->options as $opt)
                    @php
                    $isSelected = isset($responses[$question->id]) && $responses[$question->id]->answer == $opt->id;
                    $isCorrect = $opt->is_correct;
                    @endphp

                    <div class="form-check">
                        <input class="form-check-input"
                            type="radio"
                            name="answers[{{ $question->id }}]"
                            id="q{{ $question->id }}_opt{{ $opt->id }}"
                            value="{{ $opt->id }}"
                            @if($isSelected) checked @endif
                            @if($isCompleted) disabled @else required @endif>

                        <label class="form-check-label" for="q{{ $question->id }}_opt{{ $opt->id }}">
                            {{ $opt->option_text }}
                            @if($isCompleted && $isCorrect)
                            <span class="badge bg-success">Correct</span>
                            @endif
                            @if($isCompleted && $isSelected && !$isCorrect)
                            <span class="badge bg-danger">Your Answer</span>
                            @endif
                        </label>
                    </div>
                    @endforeach

                    @if($isCompleted && isset($responses[$question->id]))
                    @php $reviewedResponse = $responses[$question->id]; @endphp
                    <div class="mt-2 ps-3">
                        <p class="mb-1"><strong>Reviewer Score:</strong> {{ $reviewedResponse->score ?? '-' }} / 10</p>
                        <p class="mb-1"><strong>Reviewer Comment:</strong> {{ $reviewedResponse->comment ?? '-' }}</p>
                    </div>
                    @endif
                </div>
                @endforeach
            </div>

            @if(!$isCompleted)
            <div class="modal-footer">
                <button type="submit" class="btn btn-success">Submit</button>
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
            </div>
        </form>
        @else
        <div class="modal-footer">
            <div class="text-end w-100">
                <h5 class="fw-bold mb-1">
                    <span class="text-primary">Self Score:</span> {{ round($totalScore ?? 0, 2) }} / {{$self_total}}
                </h5>
                <h5 class="fw-bold mb-1">
                    <span class="text-warning">Reviewer Avg:</span> {{ round($reviewerAvgScore ?? 0, 2) }} / 10
                </h5>
                <h5 class="fw-bold mb-1">
                    <span class="text-success">HR Avg:</span> {{ round($hrAvgScore ?? 0, 2) }} / 10
                </h5>

                <button type="button" class="btn btn-secondary mt-2" data-bs-dismiss="modal">Close</button>
            </div>
        </div>

        @endif

        @if($status == 'Completed' && $reviewerScore > 0 && $employeePivot->employee_response == 'Pending' && $employeePivot->hr_avg_score)

        <div class="text-end mt-4 px-4 pb-4">
            <form action="{{ route('backend.employee.reviewresponse.accept', [$review->id, auth()->id()]) }}" method="POST" class="d-inline">
                @csrf
                <button type="submit" class="btn btn-success">Accept & View Increment</button>
            </form>

            <form action="{{ route('backend.employee.reviewresponse.decline', [$review->id, auth()->id()]) }}" method="POST" class="d-inline">
                @csrf
                <button type="submit" class="btn btn-danger">Decline</button>
            </form>
        </div>
        @endif

    </div>
</div>