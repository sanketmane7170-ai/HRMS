@component('mail::message')
# {{ $emailType }}

Dear **{{ $email_user->name }}**,

We hope this message finds you well. This email is regarding **{{ $emailType }}**. Find the attachment for details.

If you have any questions or need further assistance, feel free to reply to this email or contact our support team.

Thank you for your time and continued support.

Best regards,<br>
**{{ config('app.name', 'WorkPilot') }} Support**
@endcomponent
