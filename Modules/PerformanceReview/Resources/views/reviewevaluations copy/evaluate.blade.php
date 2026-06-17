@extends('layouts.backend')
@section('content')
<style>
    .table td, .table th {
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

        <form method="POST" action="{{ route('evaluate.submit', [$review->id, $user->id]) }}">
            @csrf
            <table class="table table-bordered light">
                <thead>
                    <tr>
                        <th>Question</th>
                        <th>User Answer</th>
                        <th>Correct Answer</th>
                        <th>User Score</th>
                        <th>Reviewer Score (0–100)</th>
                        <th>Comment</th> <!-- NEW COLUMN -->
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
                            <input type="number"
                                name="scores[{{ $response->id }}]"
                                value="{{ $response->score ?? '' }}"
                                class="form-control"
                                min="0" max="10" step="0.1">
                        </td>
                        <td>
                            <textarea name="comments[{{ $response->id }}]"
                                rows="2"
                                class="form-control"
                                placeholder="Add comment...">{{ $response->comment ?? '' }}</textarea>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>



            <button type="submit" class="btn btn-success">Save & Send</button>
        </form>
    </div>
</div>
@endsection