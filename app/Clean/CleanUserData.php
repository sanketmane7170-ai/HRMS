<?php

namespace App\Clean;

use App\Models\User;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\UserProfile;
use Modules\Payroll\Entities\UserSalary;
use Modules\Payroll\Entities\UserSalaryAllowance;
use Modules\Payroll\Entities\UserOvertime;
use Modules\Payroll\Entities\UserDeduction;
use Modules\Document\Entities\DocumentRequest;
use App\Models\UserBankDetail;
use App\Models\UserDependent;
use App\Models\UserDocument;
use Modules\Warning\Entities\UserWarning;
use App\Models\UserWorkDetail;
use Modules\Attendance\Entities\Attendance;
use Modules\Asset\Entities\AssetAssignment;
use Modules\Attendance\Entities\Checkin;
use App\Models\UserShift;
use Modules\Attendance\Entities\LocationVisits;
use Modules\Attendance\Entities\Visitin;
use Modules\Attendance\Entities\Breakin;
use Modules\Shift\Entities\UsersShift;
use Modules\Payroll\Entities\UserPaySlip;
use App\Models\PreviousLeaveBalance;
use Modules\Leave\Entities\Leave;
use Modules\Leave\Entities\LeaveBalance;

class CleanUserData
{

    /**
     * delete a user data from every foreign key based tables
     */
    public function delete($user_id)
    {
        DB::beginTransaction();
        try {
            $query_string = "where('user_id',$user_id)->delete();";
            
            // List of model classes to delete records from
            $models = [
                UserProfile::class, UserSalary::class, UserPaySlip::class, UserSalaryAllowance::class, UserOvertime::class,
                UserDeduction::class, DocumentRequest::class, LeaveBalance::class, Leave::class,
                UserBankDetail::class, UserDependent::class, UserDocument::class, UserWarning::class,
                UserWorkDetail::class, Attendance::class, AssetAssignment::class, Checkin::class, Breakin::class,
                UserShift::class, LocationVisits::class, Visitin::class, UsersShift::class, PreviousLeaveBalance::class
            ];

            foreach($models as $model){
                $model::where('user_id',$user_id)->delete();
            }
            User::where('id',$user_id)->delete();    
            
            DB::commit();
        } catch (Exception $e) {
            DB::rollBack();
            throw new Exception($e->getMessage());
        }
    }
}
