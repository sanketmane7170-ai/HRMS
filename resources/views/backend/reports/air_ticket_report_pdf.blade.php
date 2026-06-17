<!DOCTYPE html>
<html>
<head>
    <title>Air Ticket Report</title>
    <style>
        table { width: 100%; border-collapse: collapse; font-size: 12px; }
        table, th, td { border: 1px solid #000; }
        th, td { padding: 5px; text-align: left; }
    </style>
</head>
<body>
    <h2>Air Ticket Report</h2>
    <table>
        <thead>
            <tr>
                <th>Emp ID</th>
                <th>Full Name</th>
                <th>Department</th>
                <th>Eligible Date</th>
                <th>Quantity</th>
                <th>Allowance</th>
                <th>Total Amount</th>
                <th>Details</th>
                <th>Status</th>
                <th>Approval Date</th>
            </tr>
        </thead>
        <tbody>
            @foreach($tickets as $ticket)
            <tr>
                <td>{{ $ticket['Emp ID'] }}</td>
                <td>{{ $ticket['Full Name'] }}</td>
                <td>{{ $ticket['Department'] }}</td>
                <td>{{ $ticket['Eligible Date'] }}</td>
                <td>{{ $ticket['Quantity'] }}</td>
                <td>{{ $ticket['Allowance'] }}</td>
                <td>{{ $ticket['Total Amount'] }}</td>
                <td>{{ $ticket['Details'] }}</td>
                <td>{{ $ticket['Status'] }}</td>
                <td>{{ $ticket['Approval Date'] }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>
</body>
</html>
