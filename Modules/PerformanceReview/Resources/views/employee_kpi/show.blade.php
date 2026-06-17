@extends('layouts.backend')

@section('content')

<div class="page-wrapper">
    <div class="content container-fluid">

        <div class="page-header">
            <h3>Evaluate: {{ $assignment->duration->months }} {{ $assignment->duration->label }}</h3>
        </div>

        @if(session('error'))
            <div class="alert alert-danger">{{ session('error') }}</div>
        @endif

        <form method="POST" action="{{ route('backend.employee.kpi.submit', $assignment->id) }}">
            @csrf

            <div class="card light">
                <div class="card-body light">
                    <div class="table-responsive" style="max-height: 550px; overflow-y: auto;">
                        <table class="table table-bordered light  text-center align-middle" style="min-width: 1000px;">
                            <thead class="table-light sticky-top bg-white">
                                <tr>
                                    <th>#</th>
                                    <th style="width: 250px;">KPI Title</th>
                                    <th style="width: 400px; white-space: normal;">Description</th>

                                    <th>Self Score</th>
                                    <th>Self Remarks</th>

                                    @php
                                    $levels = \Modules\PerformanceReview\Entities\KpiScoreLevel::where('role_id', auth()->user()->roles()->first()?->id)->orderBy('step_number')->get();
                                    @endphp

                                    @foreach ($levels as $level)
                                        <th style="min-width: 120px;">{{ implode(', ', $level->approvers) }}<br>Score</th>
                                        <th style="min-width: 180px;">{{ implode(', ', $level->approvers) }}<br>Remarks</th>
                                    @endforeach

                                    <th style="min-width: 120px;">Avg Score</th>
                                </tr>
                            </thead>

                            <tbody>
                                @foreach($assignment->items as $item)
                                <tr>
                                    <td>{{ $loop->iteration }}</td>
                                    <td class="text-start">{{ $item->kpi->title }}</td>
                                        <td class="text-start" style="min-width: 480px; white-space: normal; word-break: break-word;">{{ $item->kpi->description }}</td>


                                    {{-- Self Score --}}
                                    <td>
                                        @if($assignment->status !== 'self')
                                            <input type="number" name="scores[{{ $item->id }}]" class="form-control"
                                                min="0" max="100" value="{{ $item->self_score ?? '' }}" required>
                                        @else
                                            <input type="text" class="form-control" value="{{ $item->self_score ?? '-' }}" readonly>
                                        @endif
                                    </td>

                                    {{-- Self Remarks --}}
                                    <td>
                                        @if($assignment->status !== 'self')
                                            <textarea name="remarks[{{ $item->id }}]" style="min-width: 200px;" class="form-control" rows="2">{{ $item->self_remarks ?? '' }}</textarea>
                                        @else
                                            <textarea class="form-control" style="min-width: 200px;" rows="2" readonly>{{ $item->self_remarks ?? '-' }}</textarea>
                                        @endif
                                    </td>

                                    {{-- Reviewer Levels --}}
                                    @foreach ($levels as $level)
                                        @php
                                            $review = $item->reviews->where('step_number', $level->step_number)->first();
                                        @endphp
                                        <td>
                                            <input type="text" class="form-control" value="{{ $review?->reviewer_score ?? '-' }}" readonly>
                                        </td>
                                        <td>
                                            <textarea class="form-control" rows="2" readonly>{{ $review?->reviewer_remarks ?? '-' }}</textarea>
                                        </td>
                                    @endforeach

                                    {{-- Avg Score --}}
                                    <td>{{ $item->avg_score }}</td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>

                    @if($assignment->status === 'pending')
                        <div class="text-end mt-3">
                            <button type="submit" class="btn btn-success">Submit Evaluation</button>
                        </div>
                    @else
                        <div class="alert alert-success mt-3">Evaluation already submitted.</div>
                    @endif
                </div>
            </div>
        </form>
    </div>
</div>

@endsection
