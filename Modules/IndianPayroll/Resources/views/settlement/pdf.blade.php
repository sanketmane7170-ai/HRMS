<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Full &amp; Final Settlement</title>
    <style>
        body { font-family: DejaVu Sans, sans-serif; font-size: 12px; color: #222; }
    </style>
</head>
<body>
    <h2>{{ config('app.name') }}</h2>
    <p><strong>{{ __trans('full_and_final_settlement_statement') }}</strong></p>
    @include('indianpayroll::settlement.partials.settlement_body')
</body>
</html>
