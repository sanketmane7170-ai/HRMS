@component('mail::message')
# Job Offer - {{ $offer->position }}

Dear {{ $candidateName }},

We are delighted to extend an offer of employment for the position of **{{ $offer->position }}** at {{ config('app.name') }}.

## Offer Details

<table style="width: 100%; border-collapse: collapse; margin: 20px 0;">
    <tr>
        <td style="padding: 10px; border-bottom: 1px solid #ddd;"><strong>Position:</strong></td>
        <td style="padding: 10px; border-bottom: 1px solid #ddd;">{{ $offer->position }}</td>
    </tr>
    <tr>
        <td style="padding: 10px; border-bottom: 1px solid #ddd;"><strong>Department:</strong></td>
        <td style="padding: 10px; border-bottom: 1px solid #ddd;">{{ $offer->department ?? 'N/A' }}</td>
    </tr>
    <tr>
        <td style="padding: 10px; border-bottom: 1px solid #ddd;"><strong>Salary:</strong></td>
        <td style="padding: 10px; border-bottom: 1px solid #ddd;">{{ $currency }} {{ $salary }} {{ $offer->salary_type }}</td>
    </tr>
    <tr>
        <td style="padding: 10px; border-bottom: 1px solid #ddd;"><strong>Start Date:</strong></td>
        <td style="padding: 10px; border-bottom: 1px solid #ddd;">{{ $joiningDate }}</td>
    </tr>
    <tr>
        <td style="padding: 10px; border-bottom: 1px solid #ddd;"><strong>Response Deadline:</strong></td>
        <td style="padding: 10px; border-bottom: 1px solid #ddd;">{{ $responseDeadline }}</td>
    </tr>
</table>

@if($offer->terms_conditions)
## Terms & Conditions

{!! nl2br(e($offer->terms_conditions)) !!}
@endif

@if($offer->notes)
## Additional Information

{!! nl2br(e($offer->notes)) !!}
@endif

## Next Steps

1. Review the attached offer letter carefully
2. If you accept, please respond by {{ $responseDeadline }}
3. Contact HR if you have any questions
4. Complete pre-joining formalities upon acceptance

@component('mail::button', ['url' => route('recruitment.offers.show', $offer->id)])
View Offer Details
@endcomponent

We are excited about the possibility of you joining our team and look forward to your positive response.

**Important:** This offer is contingent upon successful completion of background verification and reference checks.

If you have any questions or need clarification, please don't hesitate to contact our HR department.

Best regards,  
**{{ config('app.name') }} HR Team**

---

*This is an official offer letter. Please keep this email for your records.*
@endcomponent
