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
use Modules\Leave\Entities\LeaveBalance;
use Modules\Leave\Entities\Leave;
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

