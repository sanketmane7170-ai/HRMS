@extends('layouts.backend')

@push('css')
<style>
    /* Premium Payslip Screen Styles */
    .payslip-wrapper {
        padding: 20px 0;
        background-color: #f4f7fa;
    }
    html.dark .payslip-wrapper {
        background-color: #0f172a;
    }
    
    .payslip-card {
        background: #ffffff;
        border: 1px solid #eef1f6;
        border-radius: 12px;
        box-shadow: 0 8px 30px rgba(0, 0, 0, 0.04);
        padding: 40px;
        max-width: 900px;
        margin: 0 auto 30px auto;
        font-family: 'Plus Jakarta Sans', -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif;
        color: #2d3748;
        transition: all 0.3s ease;
    }
    
    html.dark .payslip-card {
        background: #1e293b;
        border-color: #334155;
        box-shadow: 0 10px 30px rgba(0, 0, 0, 0.15);
        color: #cbd5e1;
    }

    /* Branding & Header */
    .company-logo-section {
        border-left: 4px solid #1e3a8a;
        padding-left: 15px;
    }
    html.dark .company-logo-section {
        border-left-color: #3b82f6;
    }
    .company-name {
        font-size: 24px;
        font-weight: 800;
        color: #1a202c;
        display: block;
        letter-spacing: -0.5px;
        line-height: 1.2;
    }
    html.dark .company-name {
        color: #f8fafc;
    }
    .company-sub {
        font-size: 12px;
        font-weight: 600;
        color: #718096;
        text-transform: uppercase;
        letter-spacing: 1px;
        margin-top: 4px;
    }
    html.dark .company-sub {
        color: #94a3b8;
    }
    .payslip-title {
        font-size: 28px;
        font-weight: 800;
        color: #1e3a8a;
        letter-spacing: 0.5px;
        line-height: 1.1;
    }
    html.dark .payslip-title {
        color: #3b82f6;
    }
    .payslip-period {
        font-size: 14px;
        font-weight: 600;
        color: #4a5568;
        margin-top: 5px;
    }
    html.dark .payslip-period {
        color: #94a3b8;
    }

    /* Section Sub-titles */
    .section-title {
        font-size: 11px;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 1px;
        color: #718096;
        border-bottom: 2px solid #edf2f7;
        padding-bottom: 6px;
        margin: 25px 0 12px 0;
    }
    html.dark .section-title {
        color: #94a3b8;
        border-bottom-color: #334155;
    }

    /* Grid Details Tables */
    .payslip-details-table,
    .payslip-attendance-table,
    .payslip-breakdown-table,
    .payslip-employer-table {
        margin-bottom: 20px;
    }
    .payslip-details-table td {
        padding: 10px 12px;
        border: 1px solid #edf2f7;
        font-size: 13px;
    }
    html.dark .payslip-details-table td {
        border-color: #334155;
    }
    .payslip-details-table .label-cell {
        background-color: #f7fafc;
        color: #718096;
        font-weight: 600;
        text-align: left;
    }
    html.dark .payslip-details-table .label-cell {
        background-color: #0f172a;
        color: #94a3b8;
    }
    .payslip-details-table .value-cell {
        color: #2d3748;
    }
    html.dark .payslip-details-table .value-cell {
        color: #e2e8f0;
    }

    /* Attendance Table */
    .payslip-attendance-table th {
        background-color: #f7fafc;
        color: #718096;
        font-weight: 700;
        font-size: 11px;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        padding: 8px 10px;
        border: 1px solid #edf2f7;
    }
    html.dark .payslip-attendance-table th {
        background-color: #0f172a;
        color: #94a3b8;
        border-color: #334155;
    }
    .payslip-attendance-table td {
        padding: 10px;
        border: 1px solid #edf2f7;
        font-size: 13px;
        color: #2d3748;
    }
    html.dark .payslip-attendance-table td {
        border-color: #334155;
        color: #e2e8f0;
    }

    /* Breakdown Tables */
    .payslip-breakdown-table th {
        background-color: #edf2f7;
        color: #2d3748;
        font-weight: 700;
        font-size: 12px;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        padding: 10px 12px;
        border: 1px solid #e2e8f0;
    }
    html.dark .payslip-breakdown-table th {
        background-color: #334155;
        color: #f1f5f9;
        border-color: #475569;
    }
    .payslip-breakdown-table td {
        padding: 8px 12px;
        border: 1px solid #edf2f7;
        font-size: 13px;
        color: #4a5568;
    }
    html.dark .payslip-breakdown-table td {
        border-color: #334155;
        color: #cbd5e1;
    }
    .payslip-breakdown-table td.amount-cell {
        font-variant-numeric: tabular-nums;
        font-weight: 500;
    }
    .total-row {
        background-color: #f7fafc;
        font-weight: 700;
    }
    html.dark .total-row {
        background-color: #0f172a;
        color: #f8fafc;
    }
    .total-row td {
        border-top: 2px solid #cbd5e1 !important;
        color: #1a202c !important;
    }
    html.dark .total-row td {
        border-top-color: #475569 !important;
        color: #ffffff !important;
    }

    /* Employer Contributions Table */
    .payslip-employer-table th {
        background-color: #f7fafc;
        color: #718096;
        font-weight: 700;
        font-size: 11px;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        padding: 8px 10px;
        border: 1px solid #edf2f7;
    }
    html.dark .payslip-employer-table th {
        background-color: #0f172a;
        color: #94a3b8;
        border-color: #334155;
    }
    .payslip-employer-table td {
        padding: 8px 12px;
        border: 1px solid #edf2f7;
        font-size: 13px;
        color: #4a5568;
    }
    html.dark .payslip-employer-table td {
        border-color: #334155;
        color: #cbd5e1;
    }

    /* Net Pay Gradient Box */
    .payslip-netpay-box {
        background: linear-gradient(135deg, #1b365d, #2a4b7c);
        border-radius: 8px;
        color: #ffffff;
        box-shadow: 0 4px 15px rgba(27, 54, 93, 0.15);
    }
    html.dark .payslip-netpay-box {
        background: linear-gradient(135deg, #0f172a, #1e3a8a);
        box-shadow: 0 4px 15px rgba(0, 0, 0, 0.3);
    }
    .netpay-title {
        font-size: 10px;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 1.5px;
        color: rgba(255, 255, 255, 0.7);
    }
    .netpay-amount {
        font-size: 26px;
        font-weight: 800;
        color: #ffffff;
        letter-spacing: -0.5px;
    }
    .netpay-words {
        font-size: 11px;
        font-weight: 500;
        color: rgba(255, 255, 255, 0.85);
        margin-top: 3px;
        line-height: 1.3;
    }

    /* Badges */
    .regime-badge {
        background: #edf2f7;
        color: #4a5568;
        padding: 3px 8px;
        border-radius: 4px;
        font-size: 11px;
        font-weight: 700;
    }
    html.dark .regime-badge {
        background: #0f172a;
        color: #cbd5e1;
    }
    .status-badge {
        padding: 3px 8px;
        border-radius: 4px;
        font-size: 11px;
        font-weight: 700;
    }
    .status-draft { background: #fee2e2; color: #991b1b; }
    .status-computed { background: #fef3c7; color: #92400e; }
    .status-approved { background: #d1fae5; color: #065f46; }
    .status-locked { background: #e0f2fe; color: #075985; }
    
    html.dark .status-draft { background: #7f1d1d; color: #fee2e2; }
    html.dark .status-computed { background: #78350f; color: #fef3c7; }
    html.dark .status-approved { background: #064e3b; color: #d1fae5; }
    html.dark .status-locked { background: #0c4a6e; color: #e0f2fe; }

    /* Interactive Print Layout overrides */
    @media print {
        body * {
            visibility: hidden;
        }
        .payslip-card, .payslip-card * {
            visibility: visible;
        }
        .payslip-card {
            position: absolute;
            left: 0;
            top: 0;
            width: 100%;
            max-width: 100%;
            padding: 0;
            border: none;
            box-shadow: none;
            background: #ffffff !important;
            color: #000000 !important;
        }
        .payslip-netpay-box {
            background: #f1f5f9 !important;
            color: #000000 !important;
            border: 1px solid #cbd5e1 !important;
            box-shadow: none !important;
        }
        .netpay-title {
            color: #475569 !important;
        }
        .netpay-amount {
            color: #000000 !important;
        }
        .netpay-words {
            color: #334155 !important;
        }
        .label-cell {
            background-color: #f8fafc !important;
            color: #334155 !important;
            -webkit-print-color-adjust: exact;
            print-color-adjust: exact;
        }
        .total-row {
            background-color: #f8fafc !important;
            -webkit-print-color-adjust: exact;
            print-color-adjust: exact;
        }
        /* Hide action page header in printing */
        .page-header {
            display: none !important;
        }
    }
</style>
@endpush

@section('content')
<div class="page-wrapper">
    <div class="content container-fluid">
        <!-- Interactive Page Action Header -->
        <div class="page-header d-print-none">
            <div class="row align-items-center">
                <div class="col">
                    <h3 class="page-title">{{ __trans('payslip') }} — {{ $payslip->user->name }}</h3>
                </div>
                <div class="col-auto">
                    <button onclick="window.print()" class="btn btn-secondary mr-2">
                        <i class="fa fa-print"></i> {{ __trans('print') }}
                    </button>
                    <a href="{{ route('backend.indian-payroll.payslips.download', $payslip) }}" class="btn btn-primary">
                        <i class="fa fa-download"></i> {{ __trans('download_pdf') }}
                    </a>
                </div>
            </div>
        </div>

        <!-- Payslip Card Container -->
        <div class="payslip-wrapper">
            <div class="payslip-card">
                @include('indianpayroll::payroll_run.partials.payslip_body')
            </div>
        </div>
    </div>
</div>
@endsection
