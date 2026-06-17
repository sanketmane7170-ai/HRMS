@extends('layouts.backend')
@section('content')

<div class="page-wrapper">
    <div class="content container-fluid">
        <div class="page-header">
            <div class="row align-items-center">
                <div class="col">
                    <h3 class="page-title">Review KPI: {{ $assignment->user->name }}</h3>
                    <ul class="breadcrumb">
                        <li class="breadcrumb-item"><a href="{{ route('backend.dashboard') }}">Dashboard</a></li>
                        <li class="breadcrumb-item"><a href="{{ route('kpi.assignments.index') }}">KPI Assignments</a></li>
                        <li class="breadcrumb-item active">Review</li>
                    </ul>
                </div>
            </div>
        </div>

        @if (session('error'))
        <div class="alert alert-danger">{{ session('error') }}</div>
        @endif

        @if (!$eligibleStep && !$isAdmin && !$allLevelsCompleted)
        <div class="alert alert-warning">
            You are not authorized to review this KPI or it’s not your turn yet.
        </div>
        @else
        @if ($assignment->status === 'completed')
        <div class="card bg-light border-success mb-4 light">
            <div class="card-body light">
                <h5 class="text-success">🎓 Final Evaluation Complete</h5>
                <p><strong>Grade:</strong> {{ $assignment->grade }}</p>
                <p><strong>Remark:</strong> {{ $assignment->remark }}</p>
            </div>
        </div>
        @endif


        <form action="{{ route('kpi.assignments.submitScore', $assignment->id) }}" method="POST">
            @csrf

            <div class="card light">
                <div class="card-header light bg-primary text-white d-flex justify-content-between align-items-center">
                    <strong>Review KPI for: {{ $assignment->user->name }}</strong>
                </div>

                <div class="card-body p-3 light">

                    <div class="table-responsive" style="max-height: 500px; overflow-y: auto;">
                        <table class="light table table-bordered  align-middle text-center" style="min-width: 1000px;">
                            <thead class="table-light sticky-top bg-white">
                                <tr>
                                    <th>#</th>
                                    <th style="width: 300px;">KPI Title</th>
                                    <th style="width: 400px; white-space: normal;">Description</th>

                                    @foreach ($levelsList as $level)
                                    <th style="min-width: 120px;">{{ $level['label'] }}<br><small>Score</small></th>
                                    <th style="min-width: 150px;">{{ $level['label'] }}<br><small>Remarks</small></th>
                                    @endforeach

                                    <th style="min-width: 120px;">Avg Score</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($assignment->items as $index => $item)
                                <tr>
                                    <td>{{ $index + 1 }}</td>
                                    <td class="text-start">{{ $item->kpi->title }}</td>
                                    <td class="text-start" style="min-width: 480px; white-space: normal; word-break: break-word;">{{ $item->kpi->description }}</td>

                                    @foreach ($levelsList as $level)
                                    @php
                                    $step = $level['step'];
                                    $existingReview = $step == 0
                                    ? ['score' => $item->self_score, 'remarks' => $item->self_remarks]
                                    : $item->reviews->where('step_number', $step)->first();

                                    $isCurrentReviewer = $isAdmin || $eligibleStep === $step;
                                    $alreadyScored = $existingReview && isset($existingReview->reviewer_score);
                                    @endphp

                                    {{-- Score --}}
                                    <td>
                                        @if ($step == 0)
                                        <input type="text" class="form-control" value="{{ $existingReview['score'] ?? '-' }}" readonly>
                                        @elseif ($alreadyScored && !$isCurrentReviewer)
                                        <input type="text" class="form-control" value="{{ $existingReview->reviewer_score }}" readonly>
                                        @elseif ($isCurrentReviewer)
                                        <input type="number"
                                            name="scores[{{ $item->id }}][{{ $step }}]"
                                            class="form-control"
                                            min="0" max="100"
                                            value="{{ $existingReview->reviewer_score ?? '' }}"
                                            required>
                                        @else
                                        <input type="text" class="form-control" value="-" readonly>
                                        @endif
                                    </td>

                                    {{-- Remarks --}}
                                    <td>
                                        @if ($step == 0)
                                        <textarea class="form-control" rows="2" readonly>{{ $existingReview['remarks'] ?? '-' }}</textarea>
                                        @elseif ($alreadyScored && !$isCurrentReviewer)
                                        <textarea class="form-control" rows="2" readonly>{{ $existingReview->reviewer_remarks }}</textarea>
                                        @elseif ($isCurrentReviewer)
                                        <textarea name="remarks[{{ $item->id }}][{{ $step }}]" class="form-control" rows="2" required>{{ $existingReview->reviewer_remarks ?? '' }}</textarea>
                                        @else
                                        <textarea class="form-control" rows="2" readonly>-</textarea>
                                        @endif
                                    </td>
                                    @endforeach

                                    <td>{{ $item->avg_score }}</td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>

                    @if (($eligibleStep || $isAdmin) && $assignment->status !== 'completed')
                    <div class="text-end mt-3">
                        <button type="submit" class="btn btn-primary">
                            Submit Your Score {{ $isAdmin ? '(All Levels)' : "(Level $eligibleStep)" }}
                        </button>
                        <a href="{{ route('kpi.assignments.index') }}" class="btn btn-secondary">Cancel</a>
                    </div>
                    @endif
                </div>
            </div>
        </form>

        @endif
    </div>
</div>

@endsection