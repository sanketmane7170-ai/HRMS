<!DOCTYPE html>
<html>
<head>
    <style>
        body { font-family: 'Arial', sans-serif; background-color: #FAF9F6; margin: 0; padding: 0; }
        .container { max-width: 600px; margin: 40px auto; background: #ffffff; border-radius: 16px; overflow: hidden; box-shadow: 0 4px 15px rgba(0,0,0,0.05); }
        .header { background: #050505; color: #ffffff; padding: 30px; text-align: center; }
        .content { padding: 40px; color: #333333; line-height: 1.6; }
        .btn { display: inline-block; background-color: #FFC062; color: #050505; padding: 12px 30px; border-radius: 50px; text-decoration: none; font-weight: bold; margin-top: 20px; }
        .footer { background-color: #f1f1f1; text-align: center; padding: 20px; font-size: 12px; color: #888; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>Medical Appointment Scheduled</h1>
        </div>
        <div class="content">
            <h2>Hello {{ $user->name }},</h2>
            <p>Your Medical Fitness test has been scheduled.</p>
            <p><strong>Date:</strong> {{ \Carbon\Carbon::parse($appointmentDate)->format('d M, Y') }}</p>
            <p>Please log in to the portal to download your appointment slip and see the center location.</p>
            <center><a href="{{ $portalLink }}" class="btn">Download Slip</a></center>
            <p>Please remember to bring your original Passport and Entry Visa copy.</p>
        </div>
        <div class="footer">
            &copy; {{ date('Y') }} MOM Digital LLC. All rights reserved.
        </div>
    </div>
</body>
</html>
