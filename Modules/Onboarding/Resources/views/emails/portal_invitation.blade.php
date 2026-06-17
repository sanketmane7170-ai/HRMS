<!DOCTYPE html>
<html>
<head>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <style>
        body { font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif; background-color: #f4f7f6; margin: 0; padding: 0; color: #333; }
        .container { max-width: 600px; margin: 40px auto; background: #ffffff; border-radius: 8px; overflow: hidden; box-shadow: 0 4px 6px rgba(0,0,0,0.05); }
        .header { background: #2c3e50; padding: 30px; text-align: center; }
        .header h1 { margin: 0; color: #ffffff; font-size: 24px; font-weight: 300; }
        .content { padding: 40px; }
        .welcome-text { font-size: 18px; line-height: 1.6; margin-bottom: 25px; color: #555; }
        .credentials-box { background-color: #f8f9fa; border: 1px solid #e9ecef; border-radius: 6px; padding: 20px; margin: 25px 0; }
        .credential-item { margin-bottom: 10px; font-size: 14px; }
        .credential-item:last-child { margin-bottom: 0; }
        .credential-label { font-weight: bold; color: #7f8c8d; width: 140px; display: inline-block; }
        .credential-value { color: #2c3e50; font-family: monospace; font-size: 15px; }
        .btn-container { text-align: center; margin-top: 30px; margin-bottom: 20px; }
        .btn { display: inline-block; background-color: #3498db; color: #ffffff; padding: 14px 35px; border-radius: 4px; text-decoration: none; font-weight: bold; font-size: 16px; transition: background-color 0.3s; }
        .btn:hover { background-color: #2980b9; }
        .note { font-size: 13px; color: #95a5a6; font-style: italic; margin-top: 30px; border-top: 1px solid #eee; padding-top: 20px; }
        .footer { background-color: #ecf0f1; text-align: center; padding: 20px; font-size: 12px; color: #7f8c8d; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>Welcome to the Team! 👋</h1>
        </div>
        <div class="content">
            <p class="welcome-text">Hi {{ $name }},</p>
            <p class="welcome-text">We are thrilled to have you join <strong>{{ getSetting('site_title') }}</strong>. To ensure a smooth start, we've set up a dedicated onboarding portal for you.</p>
            <p style="margin-bottom: 5px;">Please use the credentials below to log in and complete your profile setup:</p>
            
            <div class="credentials-box">
                <div class="credential-item">
                    <span class="credential-label">Portal URL:</span>
                    <a href="{{ $url }}" style="color: #3498db; text-decoration: none;">{{ $url }}</a>
                </div>
                <div class="credential-item">
                    <span class="credential-label">Email / Username:</span>
                    <span class="credential-value">{{ $email }}</span>
                </div>
                <div class="credential-item">
                    <span class="credential-label">Temporary Password:</span>
                    <span class="credential-value">{{ $password }}</span>
                </div>
            </div>

            <div class="btn-container">
                <a href="{{ $url }}" class="btn">Access Your Portal</a>
            </div>

            <div class="note">
                <strong>Security Notice:</strong> For your security, please change your password immediately after your first login. Do not share these credentials with anyone.
            </div>
        </div>
        <div class="footer">
            &copy; {{ date('Y') }} {{ config('app.name') }}. All rights reserved.<br>
            If you have any questions, please contact the HR department.
        </div>
    </div>
</body>
</html>
