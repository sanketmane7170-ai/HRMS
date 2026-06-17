<?php

namespace Modules\Onboarding\Entities;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use App\Models\User;

class VisaProcess extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'mohre_contract_status', // pending, drafted, signed
        'mohre_contract_file',
        'work_permit_status', // pending, applied, rejected, approved
        'work_permit_file',
        'entry_permit_status', // pending, issued
        'entry_permit_file',
        'status_change_completed', // boolean
        'medical_status', // pending, scheduled, fit, unfit
        'medical_appointment_date',
        'medical_result_file',
        'insurance_status', // pending, active
        'insurance_card_file',
        'residency_visa_status', // pending, stamped
        'residency_visa_file',
        'visa_expiry_date',
        // Granular Details
        'mohre_offer_file', 'labor_card_number',
        'entry_permit_number', 'uid_number', 'visa_place_of_issue',
        'medical_center_name', 'medical_type',
        'eid_application_form', 'eid_biometrics_date', 'eid_status', 'eid_card_file',
        'residency_file_number'
    ];

    protected $casts = [
        'status_change_completed' => 'boolean',
        'medical_appointment_date' => 'date',
        'visa_expiry_date' => 'date',
        'eid_biometrics_date' => 'date',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
