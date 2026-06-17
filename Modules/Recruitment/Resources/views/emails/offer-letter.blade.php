<!DOCTYPE html>
<html>
<head>
    <title>Job Offer</title>
</head>
<body>
    <h1>Job Offer Details</h1>
    <p>Dear {{ $offer->application->candidate_name }},</p>
    <p>We are pleased to offer you the position of <strong>{{ $offer->job->title }}</strong> at our company.</p>
    <p>Please find the offer details in the portal.</p>
    <p>Best Regards,<br>HR Team</p>
</body>
</html>
