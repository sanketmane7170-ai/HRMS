@component('mail::message')
# Appreciation

Dear **{{ $user->name }}**,

{!! $message !!}

Best regards,<br>
**{{ config('app.name', 'WorkPilot') }} Support**
@endcomponent
