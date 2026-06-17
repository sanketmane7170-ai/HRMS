<!DOCTYPE html>
<html>
<head>
    <title>Certificate of Completion</title>
    <style>
        body {
            font-family: 'Georgia', serif;
            text-align: center;
            background-color: #f9f9f9;
        }
        .certificate {
            border: 10px solid #6c757d;
            padding: 50px;
            margin: 50px auto;
            width: 80%;
            background: white;
        }
        .company-name {
            font-size: 22px;
            font-weight: bold;
            color: #343a40;
            margin-bottom: 20px;
        }
        h1 {
            font-size: 48px;
            margin-bottom: 0;
        }
        p {
            font-size: 20px;
        }
        .score {
            font-size: 26px;
            color: green;
            font-weight: bold;
        }
        .print-btn {
            margin-top: 20px;
        }
    </style>
</head>
<body>
    <div class="certificate">
        <div class="company-name">{{ getSetting('site_title') }}</div>

        <h1>Certificate of Completion</h1>
        <p>This is to certify that</p>
        <h2>{{ auth()->user()->name }}</h2>
        <p>has successfully completed the training</p>
        <h3>"{{ $training->title }}"</h3>
        <p>with a score of</p>
        <div class="score">{{ $score }}%</div>
        <p>Issued on: {{ now()->format('d M, Y') }}</p>

        <button onclick="window.print()" class="print-btn">Print Certificate</button>
    </div>
</body>
</html>
