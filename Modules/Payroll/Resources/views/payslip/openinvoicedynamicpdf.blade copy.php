<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">

    <style>
        body {
            font-family: DejaVu Sans, sans-serif;
            margin: 0;
            padding: 0;
            background: #fff;
            font-size: 13px;
        }

        .payslip-container {
            width: 100%;
            max-width: 800px;
            margin: 0 auto;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            table-layout: fixed;
            margin-left: auto !important;
            margin-right: auto !important;
        }

        table[align] {
            margin-left: auto !important;
            margin-right: auto !important;
            float: none !important;
        }

        table, th, td {
            border: 1px solid #000;
        }

        th, td {
            padding: 6px 8px;
            word-wrap: break-word;
        }

        .center-text {
            text-align: center !important;
        }

        {!! $css ?? '' !!}
    </style>
</head>

<body>
    <div class="payslip-container" style="text-align:center;">
        {!! $template !!}
    </div>
</body>

</html>
