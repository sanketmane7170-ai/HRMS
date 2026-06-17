@component('mail::message')
# Hello {{ $name }},

You are receiving this email because you requested a password reset for your Onboarding Portal account.

Your One-Time Password (OTP) is:

@component('mail::panel')
# {{ $otp }}
@endcomponent

This code will expire in 10 minutes.

If you did not request a password reset, no further action is required.

Thanks,<br>
{{ config('app.name') }} HR Team
@endcomponent
