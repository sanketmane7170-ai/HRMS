<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Vacation Leave Report</title>
    <style>
        body {
            font-family: DejaVu Sans, sans-serif;
            font-size: 10px;
        }
        table {
            border-collapse: collapse;
            width: 100%;
            margin-top: 10px;
        }
        th, td {
            border: 1px solid #777;
            padding: 6px;
            text-align: center;
        }
        th {
            background-color: #f2f2f2;
            font-weight: bold;
        }
        .title {
            text-align: center;
            font-size: 14px;
            font-weight: bold;
            margin-bottom: 10px;
        }
        .footer {
            margin-top: 15px;
            font-size: 10px;
            text-align: right;
            color: #666;
        }
    </style>
</head>
<body>
    <div class="title">Vacation Leave Report</div>

    <table>
        <thead>
            <tr>
                <th>#</th>
                <th>Employee ID</th>
                <th>Name</th>
                <th>Department</th>
                <th>Location</th>
                <th>Designation</th>
                <th>Join Date</th>
                <th>Policy Type</th>
                <th>Annual Leave</th>
                <th>Initial Balance</th>
                <th>Initial Balance Date</th>
                <th>Total Leave</th>
                <th>Total Month</th>
                <th>Used Leave</th>
                <th>Balance Leave</th>
                @foreach($months as $month)
                    <th>{{ $month }}</th>
                @endforeach
            </tr>
        </thead>
        <tbody>
            @foreach($data as $row)
                <tr>
                    <td>{{ $row['DT_RowIndex'] }}</td>
                    <td>{{ $row['employee_id'] }}</td>
                    <td>{{ $row['employee_name'] }}</td>
                    <td>{{ $row['department_name'] }}</td>
                    <td>{{ $row['location'] }}</td>
                    <td>{{ $row['designation'] }}</td>
                    <td>{{ $row['join_date'] }}</td>
                    <td>{{ $row['policy_type'] }}</td>
                    <td>{{ $row['annual_leave'] }}</td>
                    <td>{{ $row['initial_balance'] }}</td>
                    <td>{{ $row['initial_balance_date'] }}</td>
                    <td>{{ $row['total_leave'] }}</td>
                    <td>{{ $row['total_month'] }}</td>
                    <td>{{ $row['used_leave'] }}</td>
                    <td>{{ $row['balance_leave'] }}</td>
                    @foreach($months as $month)
                        <td>{{ $row[$month] ?? '-' }}</td>
                    @endforeach
                </tr>
            @endforeach
        </tbody>
    </table>

    <div class="footer">
        Generated on {{ now()->format('d M Y h:i A') }}
    </div>
</body>
</html>
