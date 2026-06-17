<?php

namespace Modules\Onboarding\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Modules\Onboarding\Entities\VisaProcess;
use Modules\Onboarding\Entities\ComplianceRecord;
use App\Models\User;
use Illuminate\Support\Facades\Auth;

class SecureFileController extends Controller
{
    /**
     * Download a secure document.
     * Route: /onboarding/secure/download/{type}/{id}/{field}
     */
    public function download(Request $request, $type, $id, $field)
    {
        // 1. Resolve Model
        $model = null;
        $file_path = null;
        $owner_id = null;

        if ($type == 'visa') {
            $model = VisaProcess::findOrFail($id);
            $owner_id = $model->user_id;

            // Whitelist allowed fields to prevent arbitrary access
            $allowed = [
                'mohre_offer_file', 'mohre_contract_file', 'labor_card_number', 
                'entry_permit_file', 'medical_result_file', 'eid_application_form',
                'eid_card_file', 'residency_visa_file', 'insurance_card_file'
            ];
            
            if (!in_array($field, $allowed)) abort(403, 'Invalid field requested.');
            $file_path = $model->$field;

        } elseif ($type == 'compliance') {
            $model = ComplianceRecord::findOrFail($id);
            $owner_id = $model->user_id;
            
            if ($field != 'ohc_file') abort(403);
            $file_path = $model->$field;

        } elseif ($type == 'user_document') {
            $model = \App\Models\UserDocument::findOrFail($id);
            $owner_id = $model->user_id;
            
            // Field is always 'path' for this model, but we accept 'path' or generic 'file'
            $file_path = $model->path;
        }

        if (!$file_path) abort(404, 'File not found.');

        // 2. Authorization Check (Sanket - ONB-SEC-001)
        $currentUser = Auth::user();

        // Allow: 
        // a) The owner of the file (Candidate themselves)
        // b) Admin/HR with 'Manage Onboarding' permission (full access)
        // c) HR with 'view-onboarding-tracker' permission (read-only, but must be assigned)
        
        $isOwner = $currentUser->id == $owner_id;
        $hasFullAccess = $currentUser->can('Manage Onboarding') || $currentUser->hasRole(['admin', 'hr']);
        $hasViewAccess = $currentUser->can('view-onboarding-tracker');

        if (!$isOwner && !$hasFullAccess && !$hasViewAccess) {
            abort(403, 'Unauthorized access to this document.');
        }

        // 3. Serve File
        // Check if file exists in 'local' (private) storage
        if (!Storage::disk('local')->exists($file_path)) {
             // Fallback to 'public' if we are in transition period (backward compatibility)
             if (Storage::disk('public')->exists($file_path)) {
                 return Storage::disk('public')->download($file_path);
             }
             abort(404, 'Physical file missing.');
        }

        return Storage::disk('local')->download($file_path);
    }
}
