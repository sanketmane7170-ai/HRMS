<!DOCTYPE html>
<html>
<head>
    <style>
        body { font-family: 'Arial', sans-serif; background-color: #FAF9F6; margin: 0; padding: 0; }
        .container { max-width: 600px; margin: 40px auto; background: #ffffff; border-radius: 16px; overflow: hidden; box-shadow: 0 4px 15px rgba(0,0,0,0.05); }
        .header { background: #d9534f; color: #ffffff; padding: 30px; text-align: center; }
        .content { padding: 40px; color: #333333; line-height: 1.6; }
        .btn { display: inline-block; background-color: #FFC062; color: #050505; padding: 12px 30px; border-radius: 50px; text-decoration: none; font-weight: bold; margin-top: 20px; }
        .footer { background-color: #f1f1f1; text-align: center; padding: 20px; font-size: 12px; color: #888; }
        .warning { color: #d9534f; font-weight: bold; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>OHC Expiry Alert</h1>
        </div>
        <div class="content">
            <h2>Attention, {{ $user->name }}!</h2>
            <p>This is an automated reminder that your <span class="warning">Occupational Health Card (OHC)</span> is expiring in <strong>{{ $days }} days</strong>.</p>
            <p>Since this is a mandatory requirement for food industry compliance, please schedule your medical renewal immediately.</p>
            <center><a href="{{ route('portal.index') }}" class="btn">Check Portal</a></center>
        </div>
        <div class="footer">
            &copy; {{ date('Y') }} MOM Digital LLC. All rights reserved.
        </div>
    </div>
</body>
</html>
