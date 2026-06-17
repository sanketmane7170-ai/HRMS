<!DOCTYPE html>
<html>


<head>
    <meta charset="UTF-8">
    <title>Certificate of Performance</title>
    <style>
    body {
        font-family: 'Georgia', serif;
        text-align: center;
        background: #fefefe;
        padding: 60px;
        border: 1px solid #1a237e;
    }
    body {
        font-family: 'Georgia', serif;
        text-align: center;
        background: #fefefe;
        padding: 60px;
        border: 1px solid #1a237e;
    }

    .certificate-container {
        /* border: 4px double #3f51b5; */
        padding: 50px;
    }
    .certificate-container {
        /* border: 4px double #3f51b5; */
        padding: 50px;
    }

    .title {
        font-size: 36px;
        font-weight: bold;
        color: #1a237e;
        margin-bottom: 10px;
    }
    .title {
        font-size: 36px;
        font-weight: bold;
        color: #1a237e;
        margin-bottom: 10px;
    }

    .subtitle {
        font-size: 20px;
        color: #555;
        margin-bottom: 40px;
    }
    .subtitle {
        font-size: 20px;
        color: #555;
        margin-bottom: 40px;
    }

    .recipient {
        font-size: 28px;
        font-weight: bold;
        margin: 20px 0;
        color: #000;
    }
    .recipient {
        font-size: 28px;
        font-weight: bold;
        margin: 20px 0;
        color: #000;
    }

    .content {
        font-size: 18px;
        color: #444;
        margin-bottom: 40px;
    }
    .content {
        font-size: 18px;
        color: #444;
        margin-bottom: 40px;
    }

    .footer {
        margin-top: 40px;
        display: flex;
        justify-content: space-between;
    }
    .footer {
        margin-top: 40px;
        display: flex;
        justify-content: space-between;
    }

    .footer .left,
    .footer .right {
        width: 45%;
        text-align: center;
    }
    .footer .left,
    .footer .right {
        width: 45%;
        text-align: center;
    }

    .signature-line {
        border-top: 1px solid #000;
        width: 60%;
        margin: 0 auto 5px auto;
    }
    .signature-line {
        border-top: 1px solid #000;
        width: 60%;
        margin: 0 auto 5px auto;
    }

    .logo {
        position: absolute;
        top: 50px;
        left: 50px;
        height: 80px;
    }
    .logo {
        position: absolute;
        top: 50px;
        left: 50px;
        height: 80px;
    }

    .date {
        font-size: 16px;
        color: #777;
        margin-top: 20px;
    }
    .date {
        font-size: 16px;
        color: #777;
        margin-top: 20px;
    }
    </style>
</head>

<body>
    <div class="certificate-container">
        <img src="{{getLogo()}}" class="logo" alt="Logo">

        <div class="title">Certificate of Performance</div>
        <div class="subtitle">This is to acknowledge the successful completion of the appraisal process</div>

        <div class="recipient">{{ $appraisal->employee->name }}</div>
        <p><strong>Appraisal Date:</strong>
            {{ $appraisal->appraisal_date?->format('d M Y') }}
        </p>

        <div class="content">
            has successfully completed the appraisal for the period<br>

            <strong>{{__trans($appraisal->period) }}</strong> with an overall score of<br>
            <strong>
                {{ round($appraisal->criteria->sum(fn($c) => $c->score * $c->weight) / max($appraisal->criteria->sum('weight'), 1), 2) }}
            </strong>
        </div>

        <div class="date">
            Date of Issue: {{ \Carbon\Carbon::now()->format('F d, Y') }}
        </div>

        <div class="footer">
            <div class="left">
                <div class="signature-line"></div>
                <strong>HR Manager</strong>
            </div>
            <div class="right">
                <div class="signature-line"></div>
                <strong>Appraisal Authority</strong>
            </div>
        </div>
    </div>
</body>

</html>