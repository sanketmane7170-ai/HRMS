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
            <h1>Welcome to MOM Digital!</h1>
        </div>
        <div class="content">
            <h2>Hi {{ $user->name }},</h2>
            <p>We are thrilled to have you join our team! To ensure a smooth start, we have prepared a digital onboarding journey for you.</p>
            <p>Please log in to your candidate portal to upload your documents and track your visa progress.</p>
            <center><a href="{{ $portalLink }}" class="btn">Start Your Journey</a></center>
            <p>Username: {{ $user->email }}<br>Password: (The one you set or received separately)</p>
        </div>
        <div class="footer">
            &copy; {{ date('Y') }} MOM Digital LLC. All rights reserved.
        </div>
    </div>
</body>
</html>
