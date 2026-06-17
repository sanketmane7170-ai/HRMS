@component('mail::message')
# Acknowledgement

Dear **{{ $user->name }}**,

{!! $message !!}

Best regards,<br>
**{{ config('app.name', 'WorkPilot') }} Support**
@endcomponent
