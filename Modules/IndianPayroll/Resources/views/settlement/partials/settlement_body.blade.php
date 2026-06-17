<table style="width:100%; border-collapse: collapse;" border="1" cellpadding="6">
    <tr><td colspan="2"><strong>{{ $settlement->user->name }}</strong></td></tr>
    <tr><td>{{ __trans('last_working_day') }}</td><td>{{ $settlement->last_working_day->format('d-M-Y') }}</td></tr>
    <tr><td>{{ __trans('pending_salary') }}</td><td>{{ number_format($settlement->pending_salary_amount, 2) }}</td></tr>
    <tr><td>{{ __trans('gratuity') }} ({{ __trans('taxable') }}: {{ number_format($settlement->gratuity_taxable_amount, 2) }})</td><td>{{ number_format($settlement->gratuity_amount, 2) }}</td></tr>
    <tr><td>{{ __trans('leave_encashment') }} ({{ __trans('taxable') }}: {{ number_format($settlement->leave_encashment_taxable_amount, 2) }})</td><td>{{ number_format($settlement->leave_encashment_amount, 2) }}</td></tr>
    <tr><td>{{ __trans('notice_pay_recovery') }}</td><td>-{{ number_format($settlement->notice_pay_recovery, 2) }}</td></tr>
    <tr><td>{{ __trans('asset_recovery') }}</td><td>-{{ number_format($settlement->asset_recovery ?? 0, 2) }}</td></tr>
    <tr><td>{{ __trans('loan_recovery') }}</td><td>-{{ number_format($settlement->loan_recovery ?? 0, 2) }}</td></tr>
    <tr><td>{{ __trans('other_deductions') }}</td><td>-{{ number_format($settlement->other_deductions, 2) }}</td></tr>
    <tr><td>{{ __trans('final_tds') }}</td><td>-{{ number_format($settlement->final_tds, 2) }}</td></tr>
    <tr><td><strong>{{ __trans('net_payable') }}</strong></td><td><strong>{{ number_format($settlement->net_payable, 2) }}</strong></td></tr>
</table>
