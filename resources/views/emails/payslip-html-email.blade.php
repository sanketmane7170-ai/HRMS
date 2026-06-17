@component('mail::message')
# Your Payslip

Dear **{{ $user->name }}**,

Please find your payslip details below.

<div style="background: #fff; border: 1px solid #e8e5ef; padding: 20px; border-radius: 4px; overflow-x: auto;">
{!! $template !!}
</div>

Best regards,<br>
**{{ config('app.name', 'WorkPilot') }} Payroll**
@endcomponent
