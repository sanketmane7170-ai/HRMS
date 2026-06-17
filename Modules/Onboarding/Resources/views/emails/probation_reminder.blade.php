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
        .info-box { background: #f8f9fa; padding: 20px; border-left: 4px solid #FFC062; margin: 20px 0; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>Probation Review Due</h1>
        </div>
        <div class="content">
            <h2>Hello, {{ $manager->name }}</h2>
            <p>This is a reminder that the probation review for <strong>{{ $employee->name }}</strong> is now due.</p>
            
            <div class="info-box">
                <strong>Employee:</strong> {{ $employee->name }}<br>
                <strong>Employee ID:</strong> {{ $employee->employee_id }}<br>
                <strong>Department:</strong> {{ $employee->department ? $employee->department->name : 'N/A' }}<br>
                <strong>Due Date:</strong> {{ $review->scheduled_date->format('d M Y') }}
            </div>

            <p>Please complete the review form in the HR portal to confirm their status or request an extension.</p>
            
            <center><a href="{{ route('onboarding.probation.index') }}" class="btn">Complete Review Form</a></center>
        </div>
        <div class="footer">
            &copy; {{ date('Y') }} MOM Digital LLC. All rights reserved.
        </div>
    </div>
</body>
</html>
