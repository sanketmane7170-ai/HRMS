<!DOCTYPE html>
<html>
<head>
    <style>
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
            font-size: 12px;
        }
        table, th, td {
            border: 1px solid #000;
        }
        th {
            background: #f2f2f2;
            font-weight: bold;
        }
        td, th {
            padding: 8px;
        }
        h2 {
            text-align: center;
        }
    </style>
</head>
<body>

<h2>@if(isset($user)){{ $user->name }} - @endif Visit Report</h2>

<table>
    <thead>
        <tr>
            <th>Employee</th>
            <th>Location</th>
            <th>Date</th>
            <th>Visit Purpose</th>
            <th>Visit Start</th>
            <th>Visit End</th>
            <th>Total Worked (Hours:Minutes)</th>
        </tr>
    </thead>
    <tbody>
        @foreach ($reports as $row)
            <tr>
                <td>({{ $row->user?->employee_id }}) {{ $row->user?->name }}</td>
                <td>{{ $row->location }}</td>
                <td>{{ $row->date }}</td>
                <td>{{ $row->visit_purpose }}</td>
                <td>{{ $row->visit_in }}</td>
                <td>{{ $row->visit_out }}</td>
                <td>{{ $row->total_worked }}</td>
            </tr>
        @endforeach
    </tbody>
</table>

</body>
</html>
