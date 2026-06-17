@extends('layouts.backend')

@section('content')
<style>
    .table td,
    .table th {
        white-space: normal !important;
        word-break: break-word;
        vertical-align: top;
    }

    textarea.form-control {
        min-height: 60px;
        min-width: 100px;
    }
</style>



<div class="page-wrapper">
    <div class="content container-fluid">
        <h3>Evaluate {{ $user->name }}</h3>

        <div class="mb-3">
            <h5 class="text-primary fw-bold">User's Total Score: {{ $totalScore }}</h5>
        </div>

        @php
        $pivot = $review->employees->find($user->id)?->pivot;
        $reviewerAvg = $pivot->reviewer_avg_score ?? null;
        $hrAvg = $pivot->hr_avg_score ?? null;

        use Modules\PerformanceReview\Entities\IncrementCriteria;
        $reviewerCriteria = $reviewerAvg ? IncrementCriteria::where('min_score', '<=', $reviewerAvg)->where('max_score', '>=', $reviewerAvg)->first() : null;
            $hrCriteria = $hrAvg ? IncrementCriteria::where('min_score', '<=', $hrAvg)->where('max_score', '>=', $hrAvg)->first() : null;
                @endphp

                @if(is_null($reviewerAvg))
                <form method="POST" action="{{ route('evaluate.submit', [$review->id, $user->id]) }}">
                    @csrf
                    @endif

                    <table class="table table-bordered light">
                        <thead>
                            <tr>
                                <th>Question</th>
                                <th>User Answer</th>
                                <th>Correct Answer</th>
                                <th>User Score</th>
                                <th>Reviewer Score (0–10)</th>
                                <th>Comment</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($responses as $response)
                            @php
                            $userOption = $response->question->options->firstWhere('id', $response->answer);
                            $correctOption = $response->question->options->firstWhere('is_correct', 1);
                            @endphp
                            <tr>
                                <td>{{ $response->question->question_text }}</td>
                                <td>{{ $userOption->option_text ?? '-' }}</td>
                                <td>{{ $correctOption->option_text ?? '-' }}</td>
                                <td>{{ $response->user_score }}</td>
                                <td>
                                    @if(is_null($reviewerAvg))
                                    <input type="number" name="scores[{{ $response->id }}]" value="{{ $response->score ?? '' }}" class="form-control" min="0" max="10" step="0.1">
                                    @else
                                    {{ $response->score ?? '-' }}
                                    @endif
                                </td>
                                <td>
                                    @if(is_null($reviewerAvg))
                                    <textarea name="comments[{{ $response->id }}]" rows="2" class="form-control" placeholder="Add comment...">{{ $response->comment ?? '' }}</textarea>
                                    @else
                                    {{ $response->comment ?? '-' }}
                                    @endif
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>

                    <div class="mt-4 mb-3">
                        <h5 class="text-success">Reviewer Avg Score: <strong>{{ $reviewerAvg !== null ? number_format($reviewerAvg, 2) : 'Not Submitted' }}</strong></h5>
                        <h5 class="text-info">HR Avg Score: <strong>{{ $hrAvg !== null ? number_format($hrAvg, 2) : 'Pending' }}</strong></h5>
                    </div>

                    @if($reviewerCriteria)
                    <div class="card p-3 border-success mb-3 light">
                        <h5 class="text-success fw-bold">Reviewer-Based Increment Criteria</h5>
                        <p><strong>Level:</strong> {{ $reviewerCriteria->label }}</p>
                        <p><strong>Increment %:</strong> {{ $reviewerCriteria->increment_percent }}%</p>
                        <ul>
                            <li>Basic: {{ $reviewerCriteria->basic_percent }}%</li>
                            <li>Housing: {{ $reviewerCriteria->housing_percent }}%</li>
                            <li>Transport: {{ $reviewerCriteria->transport_percent }}%</li>
                            <li>Other: {{ $reviewerCriteria->other_percent }}%</li>
                            <li>Incentive: {{ $reviewerCriteria->incentive_percent }}%</li>
                        </ul>
                    </div>
                    @endif

                    @if($pivot && $pivot->hr_avg_score)
                    <div class="card p-3 border-info mb-3 light">
                        <h5 class="text-info fw-bold">HR-Based Increment Criteria (Final)</h5>
                        <p><strong>HR Avg Score:</strong> {{ number_format($pivot->hr_avg_score, 2) }}</p>
                        <p><strong>Increment %:</strong> {{ $pivot->hr_increment_percent ?? '-' }}%</p>
                        <ul>
                            <li>Basic: {{ $pivot->hr_basic_percent ?? '-' }}%</li>
                            <li>Housing: {{ $pivot->hr_housing_percent ?? '-' }}%</li>
                            <li>Transport: {{ $pivot->hr_transport_percent ?? '-' }}%</li>
                            <li>Other: {{ $pivot->hr_other_percent ?? '-' }}%</li>
                            <li>Incentive: {{ $pivot->hr_incentive_percent ?? '-' }}%</li>
                        </ul>
                        <p><strong>HR Reviewed At:</strong> {{ \Carbon\Carbon::parse($pivot->hr_reviewed_at)->format('d M Y h:i A') ?? '-' }}</p>
                    </div>
                    @endif

                    @if(auth()->user()->hasRole(\App\Models\User::ROLE_HR) || auth()->user()->hasRole(\App\Models\User::ROLE_ADMIN) || auth()->user()->hasRole(\App\Models\User::ROLE_SUPER_ADMIN))
                    <form method="POST" action="{{ route('evaluate.hr.submit', [$review->id, $user->id]) }}">
                        @csrf
                        <div class="card p-3 border-info mb-3 light">
                            <h5 class="text-info fw-bold">HR Review Input</h5>

                            @php
                            $fields = ['basic', 'housing', 'transport', 'other', 'incentive'];
                            $isReadonly = !is_null($pivot->hr_avg_score);
                            $avgScoreValue = old('hr_avg_score', $pivot->hr_avg_score ?? '');
                            $incrementValue = old('hr_increment_percent', $pivot->hr_increment_percent ?? '');
                            @endphp

                            {{-- HR Avg Score on Top --}}
                            <div class="row mb-3">
                                <div class="col-md-4">
                                    <label class="form-label">HR Avg Score</label>
                                    <input type="number"
                                        step="0.1"
                                        name="hr_avg_score"
                                        id="hr_avg_score"
                                        class="form-control"
                                        value="{{ $avgScoreValue }}"
                                        {{ $isReadonly ? 'readonly' : '' }}
                                        required>
                                </div>
                            </div>

                            {{-- HR Percent Inputs --}}
                            <div class="row">
                                @foreach($fields as $field)
                                @php
                                $default = $pivot->{'hr_'.$field.'_percent'} ?? null;
                                $fallback = $reviewerCriteria->{$field . '_percent'} ?? '';
                                $value = old('hr_'.$field.'_percent', $default ?? $fallback);
                                @endphp
                                <div class="col-md-4 mb-2">
                                    <label class="form-label text-capitalize">{{ $field }} %</label>
                                    <input type="number"
                                        step="0.1"
                                        name="hr_{{ $field }}_percent"
                                        id="hr_{{ $field }}_percent"
                                        class="form-control hr-input"
                                        value="{{ $value }}"
                                        {{ $isReadonly ? 'readonly' : '' }}
                                        required>
                                </div>
                                @endforeach

                                {{-- Auto-calculated Increment % --}}
                                <div class="col-md-4 mb-2">
                                    <label class="form-label">Increment % (Auto)</label>
                                    <input type="number"
                                        step="0.1"
                                        name="hr_increment_percent"
                                        id="hr_increment_percent"
                                        class="form-control"
                                        value="{{ $incrementValue }}"
                                        readonly>
                                </div>
                            </div>

                            @if(!$isReadonly)
                            <button type="submit" class="btn btn-info mt-2">Submit HR Review</button>
                            @endif
                        </div>
                    </form>

                    @endif



                    @if(is_null($reviewerAvg))
                    <button type="submit" class="btn btn-success">Save & Send</button>
                </form>
                @endif

    </div>
</div>
@endsection
@push('scripts')
<script>
    function updateIncrementPercent() {
        let total = 0;
        document.querySelectorAll('.hr-input').forEach(input => {
            let val = parseFloat(input.value) || 0;
            total += val;
        });
        document.getElementById('hr_increment_percent').value = total.toFixed(2);
    }

    document.addEventListener('DOMContentLoaded', function() {
        updateIncrementPercent(); // initial fill
        document.querySelectorAll('.hr-input').forEach(input => {
            input.addEventListener('input', updateIncrementPercent);
        });
    });
</script>
@endpush