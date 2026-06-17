<?php
namespace Modules\Payroll\Entities;


use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\User;

class AdvanceRequest extends Model
{
    use HasFactory;

    // Sanket - Added requested_date and approved_date fields for tracking when requests are made and approved
    // requested_date: Auto-filled when user submits request (current system date)
    // approved_date: Auto-filled when admin approves request (current system date)
    protected $fillable = [
        'reference_number',
        'type',
        'reason',
        'amount',
        'instalments',
        'start_month',
        'status',
        'approved_amount',
        'loan_months',
        'installment_amount',
        'installments_paid',
        'installments_pending',
        'user_id',
        'loan_mode',
        'approved_date',        // Sanket - Date when request is approved by admin
        'rejected_date',        // Sanket - Date when request is rejected by admin
        'rejection_reason',     // Sanket - Reason when request is rejected by admin
        'requested_date',       // Sanket - Date when request is created by user
    ];

    protected $dates = ['start_month'];

    // Sanket - Cast new date fields as Carbon date instances for proper formatting
    // This ensures the dates are properly handled as Carbon objects instead of strings
    protected $casts = [
        'requested_date' => 'date',     // Sanket - Cast to Carbon date for proper formatting
        'approved_date' => 'date',      // Sanket - Cast to Carbon date for proper formatting
        'rejected_date' => 'date',      // Sanket - Cast to Carbon date for proper formatting
    ];

    // Sanket - Accessor for formatted start_month to match other date fields (DD/MM/YYYY format)
    public function getFormattedStartMonthAttribute()
    {
        if (!$this->start_month) {
            return '';
        }
        
        try {
            // Sanket - Format start_month as DD/MM/YYYY to match requested_date and approved_date
            if ($this->start_month instanceof \Carbon\Carbon) {
                return $this->start_month->format('d/m/Y');
            }
            return \Carbon\Carbon::parse($this->start_month)->format('d/m/Y');
        } catch (\Exception $e) {
            // Sanket - Return empty string on any parsing errors
            return '';
        }
    }

    // Sanket - Accessor to format requested_date as DD/MM/YYYY for display in UI
    // This is used in DataTables and forms to show user-friendly date format
    // Returns empty string if date is null/invalid to prevent errors
    public function getFormattedRequestedDateAttribute()
    {
        if (!$this->requested_date) {
            return '';
        }
        
        try {
            // Sanket - Handle both Carbon instance and string date for robustness
            // This prevents "Call to member function format() on string" errors
            if ($this->requested_date instanceof \Carbon\Carbon) {
                return $this->requested_date->format('d/m/Y');
            }
            return \Carbon\Carbon::parse($this->requested_date)->format('d/m/Y');
        } catch (\Exception $e) {
            // Sanket - Return empty string on any parsing errors to prevent crashes
            return '';
        }
    }

    // Sanket - Accessor to format approved_date as DD/MM/YYYY for display in UI
    // Shows when the request was approved by admin/approver
    // Returns empty string if status is not 'approved' or date is invalid
    public function getFormattedApprovedDateAttribute()
    {
        // Only show approved date if status is actually approved
        if ($this->status !== 'approved' || !$this->approved_date) {
            return '';
        }
        
        try {
            // Sanket - Handle both Carbon instance and string date for robustness
            if ($this->approved_date instanceof \Carbon\Carbon) {
                return $this->approved_date->format('d/m/Y');
            }
            return \Carbon\Carbon::parse($this->approved_date)->format('d/m/Y');
        } catch (\Exception $e) {
            // Sanket - Return empty string on any parsing errors
            return '';
        }
    }

    // Sanket - Accessor to format rejected_date as DD/MM/YYYY for display in UI
    // Shows when the request was rejected by admin/approver
    // Returns empty string if not rejected or date is invalid
    public function getFormattedRejectedDateAttribute()
    {
        if (!$this->rejected_date) {
            return '';
        }
        
        try {
            // Sanket - Handle both Carbon instance and string date for robustness
            if ($this->rejected_date instanceof \Carbon\Carbon) {
                return $this->rejected_date->format('d/m/Y');
            }
            return \Carbon\Carbon::parse($this->rejected_date)->format('d/m/Y');
        } catch (\Exception $e) {
            // Sanket - Return empty string on any parsing errors
            return '';
        }
    }

    // Sanket - Accessor to get appropriate action date based on status
    // Returns approved date if approved, rejected date if rejected, empty if pending
    public function getActionDateAttribute()
    {
        if ($this->status === 'approved' && $this->approved_date) {
            return $this->formatted_approved_date;
        } elseif ($this->status === 'rejected' && $this->rejected_date) {
            return $this->formatted_rejected_date;
        }
        return '';
    }

    // Relationship to User
    public function user()
    {
        return $this->belongsTo(User::class);
    }
}