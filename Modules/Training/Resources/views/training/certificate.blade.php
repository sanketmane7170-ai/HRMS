<div class="modal-dialog modal-lg">
    <div class="modal-content">
        <div class="modal-header">
            <h3>Certificate Test: {{ $training->title }}</h3>
            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>

        {{-- READ-ONLY actions --}}
        @if($readonly)
            <a href="{{ route('backend.training.certificate', $training->id) }}"
               class="btn btn-success btn-sm mx-3 mt-2"
               target="_blank">
                View Certificate
            </a>
            <div class="alert alert-success m-3">
                You have already submitted this test.
                Your score: <strong>{{ $score }}</strong>
            </div>
        @endif

        <form
            action="{{ $readonly ? '#' : route('backend.training.qa.store', $training) }}"
            method="POST"
            @if (!$readonly) class="ajax-form-submit reset" @endif
        >
            @csrf

            <div class="modal-body p-4">

                {{-- ===== PRE-START PANEL (shows Start button + timer) ===== --}}
                @unless($readonly)
                    <div id="pre-start-block" class="text-center mb-4">
                        <div class="alert alert-warning d-inline-block">
                            Time Remaining: <span id="timer">{{ $formattedDuration }} (mm:ss)</span>
                        </div>
                        <br>
                        <button id="start-test-btn" type="button" class="btn btn-primary mt-3">
                            Start Test
                        </button>
                    </div>
                @endunless

                {{-- ===== QUESTIONS ===== --}}
                <div id="question-block" class="@unless($readonly) d-none @endunless">
                    <div class="row">
                        @foreach ($training->questions as $index => $question)
                            <div class="mb-3">
                                <strong>Q{{ $index + 1 }}. {{ $question->question }}</strong>
                                @foreach ($question->answers as $answer)
                                    @php
                                        $selected  = $attempts[$question->id]->selected_option ?? null;
                                        $isSelected= $selected === $answer->option_label;
                                        $isCorrect = $answer->is_correct;
                                    @endphp
                                    <div class="form-check">
                                        <input
                                            class="form-check-input"
                                            type="radio"
                                            name="answers[{{ $question->id }}]"
                                            value="{{ $answer->option_label }}"
                                            id="q{{ $question->id }}{{ $answer->id }}"
                                            {{ $isSelected ? 'checked' : '' }}
                                            {{ $readonly ? 'disabled' : '' }}
                                        >
                                        <label
                                            class="form-check-label {{ $readonly && $isCorrect ? 'text-success fw-bold' : '' }}"
                                            for="q{{ $question->id }}{{ $answer->id }}"
                                        >
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
                {{-- ===== /QUESTIONS ===== --}}
            </div>

            <!-- {{-- SUBMIT button – only when editable --}}
            @unless($readonly)
                <div class="modal-footer">
                    <button type="submit" class="btn btn-primary">Submit Answers</button>
                </div>
            @endunless -->
            {{-- SUBMIT button – only when editable --}}
            @unless($readonly)
                <div id="submit-footer" class="modal-footer d-none">   {{-- d-none hides it --}}
                    <button type="submit" class="btn btn-primary">Submit Answers</button>
                </div>
            @endunless

        </form>
    </div>
</div>

{{-- ========== JS ========== --}}
<script>
    //--- Select2 / flatpickr initialisers you already had ---
    initselect2search();
    loadAjaxSelect2();
    flatpickr("input.datetime", {
        enableTime: true,
        dateFormat: "Y-m-d",
    });

    @unless($readonly)
    //--- One-minute countdown logic ---
    (function () {
        const startBtn   = document.getElementById('start-test-btn');
        const timerSpan  = document.getElementById('timer');
        const qBlock     = document.getElementById('question-block');
        const form       = startBtn.closest('form');
        let   countdown;       // interval ID
        const footer = document.getElementById('submit-footer');
        startBtn.addEventListener('click', () => {
            // Show questions & disable Start button
            qBlock.classList.remove('d-none');
            startBtn.disabled = true;
            qBlock.classList.remove('d-none');
            startBtn.disabled = true;

            footer.classList.remove('d-none'); 
            // Start 30-second countdown
            let remaining = {{ $totalDuration ?? 30 }};          // seconds
            updateDisplay(remaining);

            countdown = setInterval(() => {
                remaining--;
                updateDisplay(remaining);

                if (remaining <= 0) {
                    clearInterval(countdown);
                    // auto-submit – comment out if you prefer to disable instead
                    // form.submit();
                   // Trigger the normal jQuery-based Ajax flow instead:
                   if (window.jQuery) {
                       $(form).trigger('submit');    // fires your “ajax-form-submit” listener
                   } else if (form.requestSubmit) {  // modern fallback
                       form.requestSubmit();         // simulates clicking a submit button
                   } else {
                       form.querySelector('button[type="submit"]')?.click();
                   }
                }
            }, 1000);
        });

        function updateDisplay(sec) {
            const m = String(Math.floor(sec / 60)).padStart(2, '0');
            const s = String(sec % 60).padStart(2, '0');
            timerSpan.textContent = `${m}:${s}`;
        }
    })();
    @endunless
</script>
