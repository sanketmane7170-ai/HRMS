<?php

namespace App\Observers;

use App\Models\User;
use App\Notifications\User\WelcomeNotification;

class UserObserver
{
    /**
     * Handle the User "creating" event.
     */
    public function creating(User $user): void
    {
        if (!$user->username) {
            $user->username = User::generateUserName($user->name);
        }
    }

    /**
     * Handle the User "created" event.
     */
    public function created(User $user)
    {
        if ($user->employee_id == NULL) {
            // $user->employee_id = config('project.emp_code_prefix') . sprintf("%04d", $user->id);
            // $user->employee_id = ($user->department && $user->department->short_name)
            //     ? $user->department->short_name . sprintf("%04d", $user->id)
            //     : config('project.emp_code_prefix') . sprintf("%04d", $user->id);
            $shortName   = $user->department->short_name ?? null;
            $startNumber = $user->department->start_number ?? 0;
            $prefix = "";
            $nextEmployeeId = "";
            if ($shortName) {
                // Case 1: Prefix from department short_name
                $prefix = $shortName;
                $prefixLength = strlen($prefix);

                $result = \App\Models\User::selectRaw("
            LEFT(employee_id, ?) as branch_code,
            MAX(CAST(SUBSTRING(employee_id, ?+1) AS UNSIGNED)) as max_number
        ", [$prefixLength, $prefixLength])
                    ->where('employee_id', 'like', $prefix . '%')
                    ->groupBy('branch_code')
                    ->first();
                if (empty($result)) {
                    $nextNumber =  $startNumber;
                    $nextEmployeeId = $prefix . sprintf("%04d", $nextNumber);
                } else {
                    $nextNumber = ($result->max_number ?? $startNumber) + 1;
                    $nextEmployeeId = $prefix . sprintf("%04d", $nextNumber);
                }
            } else if ($startNumber) {
                // Case 2: Prefix from branch_id
                // dd($startNumber);

                $result = \App\Models\User::selectRaw("
            MAX(CAST(employee_id AS UNSIGNED)) as max_number
        ")
                    // ->where('department_id', $user->department->id)
                    ->first();
                $nextNumber = ($result->max_number ?? $startNumber) + 1;
                // $nextEmployeeId = $prefix . sprintf("%04d", $nextNumber);
                $nextEmployeeId = (int) $nextNumber;;
            }
            if (!empty($result) && $result->max_number == 0) {
                // $nextEmployeeId = sprintf("%04d", $startNumber);
                $nextEmployeeId = (int) $startNumber;;
            }
            // dd($nextEmployeeId);
            if ($nextEmployeeId) {
                $user->employee_id = $nextEmployeeId;
            } else {
                $user->employee_id = config('project.emp_code_prefix') . sprintf("%04d", $user->id);
            }

            // if ($shortName && $startNumber) {
            //     // Case 3: Both exist
            //     $user->employee_id = $shortName . sprintf("%04d", $startNumber + $user->id);
            // } elseif ($shortName) {
            //     // Case 1: Only short_name
            //     $user->employee_id = $shortName . sprintf("%04d", $user->id);
            // } elseif ($startNumber) {
            //     // Case 2: Only start_number
            //     $user->employee_id = sprintf("%04d", $startNumber + $user->id);
            // } else {
            //     // Case 4: Neither exists → use project prefix
            //     $user->employee_id = config('project.emp_code_prefix') . sprintf("%04d", $user->id);
            // }


            $user->save();
        }
        // $delay = now()->addMinutes(1);
        // if (app()->environment('production')) {
        //     $user->notify((new WelcomeNotification)->delay($delay));
        // }
    }

    /**
     * Handle the User "deleting" event.
     */
    public function deleting(User $user)
    {
        // TODO  Delete all realation data with the employee Delete
    }
}
