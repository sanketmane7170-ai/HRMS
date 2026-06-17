<div class="modal-dialog modal-lg">
    <div class="modal-content">
        <div class="modal-header">
            <h3>Question List: {{ $training->title }}</h3>
            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>

        @foreach ($training->questions as $question)
        <li class="list-group-item d-flex justify-content-between align-items-center">
            <a class="edit-button" href="{{ route('backend.training.question.view', [$training->id, $question->id]) }}">
                {{ Str::limit($question->question, 100) }}
            </a>

            @php
            $attempted = $userAnswers->has($question->id);
            @endphp

            @if($attempted)
            <span class="badge bg-success">Answered</span>
            @else
            <span class="badge bg-warning text-dark">Pending</span>
            @endif
        </li>
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