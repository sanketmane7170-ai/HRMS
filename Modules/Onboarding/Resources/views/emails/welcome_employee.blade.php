<!DOCTYPE html>
<html>
<head>
    <title>Welcome to the Team</title>
</head>
<body>
    <h2>Welcome Aboard, {{ $user->name }}!</h2>
    <p>We are thrilled to announce that your onboarding process is legally complete.</p>
    <p>You have been officially converted to a full-time employee in our system.</p>
    
    <p><strong>Your Employee ID:</strong> {{ $user->employee_id }}</p>
    <p><strong>Department:</strong> {{ $user->department->name ?? 'N/A' }}</p>

    <p>You can now log in to the main Employee Portal using your existing credentials.</p>
    
    <p><a href="{{ route('login') }}">Login to Employee Portal</a></p>

    <p>Best Regards,<br>HR Team</p>
</body>
</html>
