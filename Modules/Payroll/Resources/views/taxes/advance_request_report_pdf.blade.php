<!DOCTYPE html>
<html>
<head>
    <style>
        table { width: 100%; border-collapse: collapse; }
        th, td { border: 1px solid #000; padding: 5px; font-size: 12px; }
    </style>
</head>
<body>

<h3>Advance Request Report</h3>
<table>
    <thead>
        <tr>
            <th>#</th>
            <th>Employee</th>
            <th>Reference ID</th>
            <th>Action Date</th>
            <th>Total Amount</th>
            <th>Approved Amount</th>
            <th>Installment Amount</th>
            <th>Loan Duration (In Month)</th>
            <th>Installment</th>
            <th>Installment Paid</th>
            <th>Installment Pending</th>
            <th>Payment Mode</th>
            <th>Description</th>
        </tr>
    </thead>
    <tbody>
        @foreach($records as $key => $row)
        <tr>
            <td>{{ $key + 1 }}</td>
            <td>({{ $row->user->employee_id ?? 'N/A' }}) {{ $row->user->name ?? 'N/A' }}</td>
            <td>{{ $row->advanceRequest->reference_number ?? 'N/A' }}</td>
            <td>{{ \Carbon\Carbon::parse($row->action_date)->format('d-m-Y') }}</td>
            <td>{{ $row->advanceRequest->amount }}</td>
            <td>{{ $row->advanceRequest->approved_amount }}</td>
            <td>{{ $row->amount }}</td>
            <td>{{ $row->advanceRequest->loan_months }}</td>
            <td>{{ $row->advanceRequest->instalments }}</td>
            <td>{{ $row->installments_paid }}</td>
            <td>{{ $row->installments_pending }}</td>
            <td>{{ $row->advanceRequest->loan_mode }}</td>
            <td>{{ $row->description }}</td>
        </tr>
        @endforeach
    </tbody>
</table>

</body>
</html>
