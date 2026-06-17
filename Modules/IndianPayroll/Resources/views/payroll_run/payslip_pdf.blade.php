<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Payslip</title>
    <style>
        @page {
            margin: 25px 35px;
        }
        body {
            font-family: 'DejaVu Sans', sans-serif;
            font-size: 10px;
            color: #2D3748;
            line-height: 1.3;
        }
        .payslip-container {
            width: 100%;
        }
        
        /* Branding & Header */
        .company-logo-section {
            border-left: 3px solid #1E3A8A;
            padding-left: 10px;
        }
        .company-name {
            font-size: 18px;
            font-weight: bold;
            color: #1A202C;
        }
        .company-sub {
            font-size: 9px;
            color: #718096;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        .payslip-title {
            font-size: 20px;
            font-weight: bold;
            color: #1E3A8A;
        }
        .payslip-period {
            font-size: 11px;
            font-weight: bold;
            color: #4A5568;
        }

        /* Section Titles */
        .section-title {
            font-size: 9px;
            font-weight: bold;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            color: #4A5568;
            border-bottom: 1.5px solid #CBD5E1;
            padding-bottom: 3px;
            margin-top: 15px;
            margin-bottom: 8px;
        }

        /* Tables Grid */
        .payslip-details-table td {
            border: 1px solid #E2E8F0;
            padding: 5px 8px;
            font-size: 9px;
        }
        .payslip-details-table .label-cell {
            background-color: #F8FAFC;
            color: #64748B;
            font-weight: bold;
        }
        .payslip-details-table .value-cell {
            color: #1E293B;
        }

        /* Attendance Table */
        .payslip-attendance-table th {
            background-color: #F8FAFC;
            color: #64748B;
            font-weight: bold;
            font-size: 8.5px;
            text-transform: uppercase;
            padding: 5px;
            border: 1px solid #E2E8F0;
        }
        .payslip-attendance-table td {
            border: 1px solid #E2E8F0;
            padding: 5px;
            font-size: 9.5px;
            text-align: center;
            color: #1E293B;
        }

        /* Breakdown Table */
        .payslip-breakdown-table th {
            background-color: #F1F5F9;
            color: #1E293B;
            font-weight: bold;
            font-size: 9px;
            text-transform: uppercase;
            padding: 5px 8px;
            border: 1px solid #CBD5E1;
        }
        .payslip-breakdown-table td {
            border: 1px solid #E2E8F0;
            padding: 4px 8px;
            font-size: 9px;
            color: #334155;
        }
        .amount-cell {
            text-align: right;
        }
        .total-row {
            background-color: #F8FAFC;
            font-weight: bold;
        }
        .total-row td {
            border-top: 1.5px solid #94A3B8 !important;
            color: #1E293B !important;
        }

        /* Net Pay Box */
        .payslip-netpay-box {
            background-color: #F8FAFC;
            border: 1px solid #CBD5E1;
            border-radius: 4px;
        }
        .netpay-title {
            font-size: 8px;
            font-weight: bold;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            color: #475569;
        }
        .netpay-amount {
            font-size: 16px;
            font-weight: bold;
            color: #1E3A8A;
        }
        .netpay-words {
            font-size: 9px;
            font-weight: bold;
            color: #334155;
            margin-top: 2px;
        }

        /* Employer Contributions Table */
        .payslip-employer-table th {
            background-color: #F8FAFC;
            color: #64748B;
            font-weight: bold;
            font-size: 8.5px;
            text-transform: uppercase;
            padding: 5px;
            border: 1px solid #E2E8F0;
        }
        .payslip-employer-table td {
            border: 1px solid #E2E8F0;
            padding: 4px 8px;
            font-size: 9px;
            color: #334155;
        }

        /* Badges */
        .regime-badge, .status-badge {
            text-transform: uppercase;
            font-weight: bold;
            font-size: 8.5px;
        }
    </style>
</head>
<body>
    @include('indianpayroll::payroll_run.partials.payslip_body')
</body>
</html>
