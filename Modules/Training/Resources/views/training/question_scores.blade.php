@extends('layouts.backend')
@section('content')

<h3>Per-Question Scores: {{ $training->title }}</h3>

<table class="table table-bordered">
    <thead>
        <tr>
            <th>#</th>
            <th>Question</th>
            <th>Total Attempts</th>
            <th>Correct</th>
            <th>Wrong</th>
        </tr>
    </thead>
    <tbody>
        @foreach ($questionScores as $index => $qs)
            <tr>
                <td>{{ $index + 1 }}</td>
                <td>{{ $qs['question']->question }}</td>
                <td>{{ $qs['total'] }}</td>
                <td class="text-success">{{ $qs['correct'] }}</td>
                <td class="text-danger">{{ $qs['wrong'] }}</td>
            </tr>
        @endforeach
    </tbody>
</table>

@endsection
