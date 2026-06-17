<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Appraisal Report</title>
    <style>
        body { font-family: sans-serif; font-size: 12px; }
        table { width: 100%; border-collapse: collapse; margin-bottom: 20px; }
        th, td { border: 1px solid #000; padding: 5px; }
    </style>
</head>
<body>

<h2>Performance Appraisal Report</h2>

@foreach($data as $appraisal)
    <h4>{{ $appraisal->employee->name }} ({{ $appraisal->period }})</h4>
    <p>Status: {{ ucfirst($appraisal->status) }} | Reviewer: {{ $appraisal->reviewer->name }}</p>
    <table>
        <thead>
            <tr>
                <th>Criteria</th>
                <th>Weight</th>
                <th>Self Score</th>
                <th>Reviewer Score</th>
            </tr>
        </thead>
        <tbody>
        @foreach($appraisal->criteria as $c)
            <tr>
                <td>{{ $c->criteria_name }}</td>
                <td>{{ $c->weight }}</td>
                <td>{{ $c->self_score }}</td>
                <td>{{ $c->score }}</td>
            </tr>
        @endforeach
        </tbody>
    </table>
@endforeach

</body>
</html>

