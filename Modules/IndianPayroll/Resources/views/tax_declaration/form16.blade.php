<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Form 16 - {{ $financialYear }}</title>
    <style>
        body { font-family: DejaVu Sans, sans-serif; font-size: 12px; color: #222; }
        table { width: 100%; border-collapse: collapse; margin-bottom: 12px; }
        td, th { border: 1px solid #999; padding: 6px; }
        h2 { margin-bottom: 0; }
        .text-right { text-align: right; }
    </style>
</head>
<body>
    <h2>{{ config('app.name') }}</h2>
    <p><strong>Form 16 — Part B (Annual Tax Computation Statement)</strong><br>
    {{ __trans('financial_year') }}: {{ $financialYear }} | {{ __trans('employee') }}: {{ $user->name }} | {{ __trans('regime') }}: {{ ucfirst($result->regime) }}</p>

    <table>
        <tr><td>{{ __trans('gross_taxable_income') }}</td><td class="text-right">{{ number_format($result->grossTaxableIncome, 2) }}</td></tr>
        <tr><td>{{ __trans('exemptions_and_deductions') }}</td><td class="text-right">{{ number_format($result->totalExemptionsAndDeductions, 2) }}</td></tr>
        <tr><td><strong>{{ __trans('net_taxable_income') }}</strong></td><td class="text-right"><strong>{{ number_format($result->netTaxableIncome, 2) }}</strong></td></tr>
        <tr><td>{{ __trans('tax_before_rebate') }}</td><td class="text-right">{{ number_format($result->taxBeforeRebate, 2) }}</td></tr>
        <tr><td>{{ __trans('rebate_87a') }}</td><td class="text-right">-{{ number_format($result->rebate87A, 2) }}</td></tr>
        <tr><td>{{ __trans('surcharge') }}</td><td class="text-right">{{ number_format($result->surcharge, 2) }}</td></tr>
        <tr><td>{{ __trans('health_and_education_cess') }}</td><td class="text-right">{{ number_format($result->cess, 2) }}</td></tr>
        <tr><td><strong>{{ __trans('total_tax_liability') }}</strong></td><td class="text-right"><strong>{{ number_format($result->annualTaxLiability, 2) }}</strong></td></tr>
    </table>

    <p><strong>Part A — Quarterly TDS Deposited</strong></p>
    <table>
        <tr><th>Q1 (Apr-Jun)</th><th>Q2 (Jul-Sep)</th><th>Q3 (Oct-Dec)</th><th>Q4 (Jan-Mar)</th><th>Total</th></tr>
        <tr>
            <td class="text-right">{{ number_format($quarterlyTds[1], 2) }}</td>
            <td class="text-right">{{ number_format($quarterlyTds[2], 2) }}</td>
            <td class="text-right">{{ number_format($quarterlyTds[3], 2) }}</td>
            <td class="text-right">{{ number_format($quarterlyTds[4], 2) }}</td>
            <td class="text-right"><strong>{{ number_format(array_sum($quarterlyTds), 2) }}</strong></td>
        </tr>
    </table>

    <p style="font-size:10px; color:#666;">{{ __trans('form16_disclaimer_computed_statement_not_government_signed_certificate') }}</p>
</body>
</html>
