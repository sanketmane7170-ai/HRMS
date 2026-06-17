<?php

namespace Modules\Resignation\Services;

use Modules\Resignation\Entities\Resignation;
use Modules\Resignation\Entities\ResignationAction;
use Modules\Resignation\Entities\EmployeeNoticePeriod;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use Exception;
use App\Models\User;
use Modules\Resignation\Notifications\ResignationNotification;
use Illuminate\Support\Facades\Notification;
use App\Models\offBoarding;
use App\Traits\File;

class ResignationService
{
    use File;

    public function applyResignation($data, $user)
    {
        // Author signature name: Sanket
        // Block Admin from applying for resignation
        if ($user->hasRole(['admin', 'Admin', 'Super Admin', 'superadmin'])) {
            throw new Exception("Administrators are not permitted to apply for resignation.");
        }

        // BUG-RES-005 Fix: Wrap in transaction to prevent race condition - Author: Sanket
        return DB::transaction(function () use ($data, $user) {
            // Check if already has a pending resignation (inside transaction)
            if (Resignation::where('employee_id', $user->id)->whereIn('status', ['pending', 'under_review', 'on_hold'])->exists()) {
                throw new Exception("You already have a pending resignation.");
            }

            // Determine Manager (Reporting Manager)
            $managerId = $user->report_to_id ?? null;

            $resignation = Resignation::create([
                'employee_id' => $user->id,
                'manager_id' => $managerId,
                'applied_date' => now(),
                'preferred_last_working_date' => $data['preferred_last_working_date'] ?? null,
                'notice_period_days' => $data['notice_period_days'] ?? 0,
                'reason' => $data['reason'],
                'comments' => $data['comments'] ?? null,
                'status' => 'pending',
                'signed_document' => $data['signed_document'] ?? null
            ]);

            // BUG-RES-013 Fix: Queue notifications - Author: Sanket
            // Triggers for Notifications
            if ($resignation->manager_id) {
                $manager = User::find($resignation->manager_id);
                if ($manager) {
                    dispatch(function() use ($manager, $resignation) {
                        $manager->notify(new ResignationNotification($resignation, 'submitted'));
                    })->afterResponse();
                }
            }

            // Notify HR Admins
            $hrAdmins = User::role(['admin', 'hr'])->get();
            dispatch(function() use ($hrAdmins, $resignation) {
                Notification::send($hrAdmins, new ResignationNotification($resignation, 'submitted'));
            })->afterResponse();

            return $resignation;
        });
    }

    public function withdrawResignation($id, $user)
    {
        $resignation = Resignation::where('id', $id)->where('employee_id', $user->id)->firstOrFail();

        if ($resignation->status === 'approved' || $resignation->status === 'completed' || $resignation->status === 'rejected') {
            throw new Exception("Cannot withdraw resignation in {$resignation->status} status.");
        }

        $resignation->status = 'withdrawn';
        $resignation->save();

        // Notify HR Admins & Manager
        if ($resignation->manager_id) {
            $manager = User::find($resignation->manager_id);
            if ($manager) {
                $manager->notify(new ResignationNotification($resignation, 'withdrawn'));
            }
        }
        $hrAdmins = User::role(['admin', 'hr'])->get();
        Notification::send($hrAdmins, new ResignationNotification($resignation, 'withdrawn'));

        return $resignation;
    }

    public function takeAction($id, $data, $user)
    {
        $resignation = Resignation::findOrFail($id);
        $actionType = $data['action_type']; // approve, reject, hold

        DB::transaction(function () use ($resignation, $data, $user, $actionType) {
            
            // Create Action Log
            ResignationAction::create([
                'resignation_id' => $resignation->id,
                'action_by' => $user->id,
                'action_role' => $user->roles->first()->name ?? 'unknown', // Simplistic role fetch
                'action_type' => $actionType,
                'comments' => $data['comments'] ?? null,
            ]);

            // Update Resignation Status
            if ($actionType === 'approve') {
                $resignation->status = 'approved';
                $resignation->approved_last_working_date = $data['approved_last_working_date'] ?? $resignation->preferred_last_working_date;
                
                // Trigger Notice Period Logic
                $approvalDate = Carbon::now();
                $noticePeriodDays = $resignation->notice_period_days;
                $endDate = $resignation->approved_last_working_date 
                           ? Carbon::parse($resignation->approved_last_working_date) 
                           : $approvalDate->copy()->addDays($noticePeriodDays);

                if (!$resignation->approved_last_working_date) {
                    $resignation->approved_last_working_date = $endDate;
                }

                EmployeeNoticePeriod::create([
                    'employee_id' => $resignation->employee_id,
                    'resignation_id' => $resignation->id,
                    'start_date' => $approvalDate,
                    'end_date' => $endDate,
                    'status' => 'active',
                    'remarks' => $data['comments'] ?? 'Resignation Approved'
                ]);

                // Notify Employee
                $resignation->employee->notify(new ResignationNotification($resignation, 'approved', ['comments' => $data['comments'] ?? '']));

                // Sync with Offboarding
                offBoarding::updateOrCreate(
                    ['user_id' => $resignation->employee_id],
                    [
                        'departure_date' => $resignation->approved_last_working_date,
                        'resignation_reason' => $resignation->reason,
                        'departure_reason_id' => 6 // Default: Resignation with notice
                    ]
                );

            } elseif ($actionType === 'reject') {
                $resignation->status = 'rejected';
                // Notify Employee
                $resignation->employee->notify(new ResignationNotification($resignation, 'rejected', ['comments' => $data['comments'] ?? '']));
            } elseif ($actionType === 'hold') {
                $resignation->status = 'on_hold';
                // Notify Employee
                $resignation->employee->notify(new ResignationNotification($resignation, 'hold', ['comments' => $data['comments'] ?? '']));
            } elseif ($actionType === 'waive') {
                // Waive Notice Period
                $resignation->notice_period_days = 0;
                $resignation->approved_last_working_date = Carbon::now(); // End today
                
                // Update Notice Period Record if exists
                $notice = EmployeeNoticePeriod::where('resignation_id', $resignation->id)->first();
                if($notice) {
                    $notice->end_date = Carbon::now();
                    $notice->status = 'waived';
                    $notice->remarks = $data['comments'] ?? 'Notice Waived';
                    $notice->save();
                } else {
                     // Create one if not exists (unlikely if approved first, but possible if direct waive)
                     EmployeeNoticePeriod::create([
                        'employee_id' => $resignation->employee_id,
                        'resignation_id' => $resignation->id,
                        'start_date' => Carbon::now(),
                        'end_date' => Carbon::now(),
                        'status' => 'waived',
                        'remarks' => $data['comments'] ?? 'Notice Waived'
                    ]);
                }

                // Notify Employee & HR
                $resignation->employee->notify(new ResignationNotification($resignation, 'waived', ['comments' => $data['comments'] ?? '']));
                $hrAdmins = User::role(['admin', 'hr'])->get();
                Notification::send($hrAdmins, new ResignationNotification($resignation, 'waived', ['comments' => $data['comments'] ?? '']));
                
            } elseif ($actionType === 'complete') {
                $resignation->status = 'completed';
                
                // Fallback: If not approved before completion, set default dates
                if (!$resignation->approved_last_working_date) {
                    $resignation->approved_last_working_date = $resignation->preferred_last_working_date ?? Carbon::now();
                }

                // Notify Employee & HR
                $resignation->employee->notify(new ResignationNotification($resignation, 'completed', ['comments' => $data['comments'] ?? '']));
                $hrAdmins = User::role(['admin', 'hr'])->get();
                Notification::send($hrAdmins, new ResignationNotification($resignation, 'completed', ['comments' => $data['comments'] ?? '']));
                
                // Logic to update UserSettlement or User status could go here
                // e.g. User::find($resignation->employee_id)->update(['status' => 'inactive']);
            } elseif ($actionType === 'update') {
                // Just update the date and comments without changing status
                if (isset($data['approved_last_working_date'])) {
                    $resignation->approved_last_working_date = $data['approved_last_working_date'];
                    
                    // Sync with Notice Period if exists
                    $notice = EmployeeNoticePeriod::where('resignation_id', $resignation->id)->first();
                    if ($notice) {
                        $notice->end_date = Carbon::parse($data['approved_last_working_date']);
                        $notice->save();
                    }

                    // Sync with Offboarding if exists
                    $offboard = offBoarding::where('user_id', $resignation->employee_id)->first();
                    if ($offboard) {
                        $offboard->departure_date = $resignation->approved_last_working_date;
                        $offboard->save();
                    }
                }
                
                // Update comments if provided
                if (isset($data['comments'])) {
                    $resignation->comments = $data['comments'];
                }
            }

            $resignation->save();
        });

        // Author signature name: Sanket
        return $resignation;
    }

    public function getResignationsForUser($user)
    {
        return Resignation::with(['actions.user', 'noticePeriod'])->where('employee_id', $user->id)->latest()->get();
    }

    public function getResignationsForManager($user)
    {
        // If user is Admin, HR, or CEO, they should see all resignations even in this view
        if ($user->hasRole(['admin', 'hr', 'CEO', 'Director'])) {
            return $this->getAllResignations();
        }

        // Otherwise, only show direct reports (Manager view)
        return Resignation::with(['employee.department', 'noticePeriod'])->where('manager_id', $user->id)->latest()->get();
    }
    
    public function getAllResignations()
    {
        $resignations = Resignation::with([
            'employee.department', 
            'employee.designation', 
            'employee.workDetail',
            'manager', 
            'noticePeriod'
        ])->latest()->get();

        // Enrich data for DataTable
        $resignations->each(function($r) {
            // Designation
            $r->designation_name = $r->employee->designation->name ?? ($r->employee->designation->title ?? '-');
            
            // Location
            $r->office_location = $r->employee->workDetail->location ?? ($r->employee->office ?? '-');

            // Actual Reporting Manager (From UserWorkDetail)
            $managerIds = $r->employee->workDetail->report_to_ids ?? [];
            if (is_string($managerIds)) {
                $decoded = json_decode($managerIds, true);
                if (is_array($decoded)) $managerIds = $decoded;
            }
            // Handle single ID case if legacy
            if (!is_array($managerIds) && !empty($managerIds)) {
                $managerIds = [$managerIds];
            }

            if (!empty($managerIds)) {
                $managers = User::whereIn('id', $managerIds)->pluck('name')->toArray();
                $r->reporting_manager_name = implode(', ', $managers);
            } else {
                $r->reporting_manager_name = $r->manager->name ?? '-'; // Fallback to assigned manager
            }
        });

        return $resignations;
    }
}
