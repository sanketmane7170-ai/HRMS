<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Shifts;
use App\Models\User;
use Carbon\Carbon;
use Modules\Shift\Entities\UsersShift;
use Modules\Attendance\Enums\AttendanceStatus;
use App\Models\ShiftSchedule;
use Modules\Attendance\Entities\Holiday;
use App\Models\Setting;
use Modules\Attendance\Entities\Attendance;
use Illuminate\Validation\Rules\Enum;
use Modules\Warning\Entities\UserWarning;
use Modules\Warning\Enums\WarningType;
use Illuminate\Support\Facades\Validator;
use App\Services\FirebaseService;
use App\Mail\WarningEmail;
use Illuminate\Support\Facades\Mail;
use Modules\Leave\Entities\Leave;

class AutoAddUserAttendanceWarnings extends Command
{
    protected $signature = 'app:auto-add-user-attendance-warnings';
   
    protected $description = 'auto-add-user-attendance-warnings';

    public function handle()
    {
        $is_enabled = Setting::where('key', 'auto_attendance_user_warnings')->value('value');
        if($is_enabled == 'true') {
        
            $fcmService = new FirebaseService();
            $users = User::query()
                        ->whereDoesntHave('roles', function ($query) {
                            $query->whereIn('name', [User::ROLE_ADMIN, User::ROLE_SUPER_ADMIN]);
                        })->where('status',User::STATUS_ACTIVE)
                        ->get();
            
            foreach($users as $user){
                $this->info('[' . now() . '] add-user-attendance-warnings for user: ' . $user->id);

                $startDate = Carbon::now()->startOfMonth()->toDateString();
                $endDate = Carbon::now()->toDateString();

                $usershift = UsersShift::where('user_id', $user->id)
                            ->whereBetween('assigned_for_date', [$startDate, $endDate])
                            ->get();
                $attendanceWarning = 0;
                
                foreach($usershift as $shift){

                    $todayisweekend = false;
                
                    $date = Carbon::parse($shift->assigned_for_date)->toDateString();
                    $shiftschdata = ShiftSchedule::where('id',$shift->schedule_id)->first();
                    $shiftdata = Shifts::where('id',$shiftschdata->shift_id)->first();

                    // check absent
                    $todayisleave = Leave::where([['user_id', $user->id],['status','approved']])
                                ->whereDate('start_date', '<=', $date)
                                ->whereDate('end_date', '>=', $date)
                                ->first();
                    $todayisholiday = Holiday::whereDate('start_date', '<=', $date)
                                ->whereDate('end_date', '>=', $date)
                                ->first();
                    $absentstatus = AttendanceStatus::Absent;
                    $attendance = Attendance::where(
                        [
                            'user_id' => $user->id,
                            'status' => $absentstatus,
                            'date' => $date
                        ]
                    )->first();

                    if($shiftdata && $shiftdata->is_weekend==1){
                        $todayisweekend = true;
                    }

                    if(!$todayisleave && !$todayisholiday && $attendance && $todayisweekend==false){
                        $attendanceWarning++;
                    }
                }
                
                if($attendanceWarning > 2){
                    $isadded = UserWarning::where('user_id', $user->id)
                                ->where('type', WarningType::ATTENDANCE_ISSUE)
                                ->whereMonth('date', Carbon::now()->month)
                                ->whereYear('date', Carbon::now()->year)
                                ->first();
                    if(!$isadded){
                        $data = [
                            'user_id' => $user->id,
                            'date' => Carbon::now()->toDateTimeString(),
                            'type' => WarningType::ATTENDANCE_ISSUE,
                            'detail' => '<p>Dear ' . $user->name . ',</p><p>Kindly see warning for the missing attendance</p>',
                            'ack_datetime' => null,
                        ];
                        $userWarning = UserWarning::create($data);
                        if (filter_var($userWarning->user->profile->personal_email, FILTER_VALIDATE_EMAIL)) {
                            try {
                                if (isset($documentPath)) {
                                    Mail::to($userWarning->user->profile->personal_email)->send(new WarningEmail($userWarning, $documentPath));
                                } else {
                                    Mail::to($userWarning->user->profile->personal_email)->send(new WarningEmail($userWarning));
                                }
                            } catch (Exception $e) {
                            }
                            $response = getSuccessResponse(createFlashMessage('Warning', 'raised'));
                        } else {
                            $response['error'] = 'Invalid recipient email address.';
                        }

                        if (env("FIREBASE_SERVER_KEY")) {
                            $user_data = User::find($user->id);
                            if (isset($user->id) && $user->id > 0) {
                                $get = $fcmService->sendFcmMessage($user_data->ftoken, 'Warning', 'New Warning raised', 7);
                            }
                        }
                    }
                }
            }
        }
    }
}
