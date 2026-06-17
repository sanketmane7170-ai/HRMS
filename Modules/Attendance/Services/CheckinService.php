<?php

namespace Modules\Attendance\Services;

use Exception;
use Modules\Attendance\Entities\Checkin;
use Modules\Attendance\Enums\CheckinType;
use Illuminate\Support\Facades\Log;
use Modules\Shift\Entities\UsersShift;
use Carbon\Carbon;
use Modules\Attendance\Entities\Holiday;
use Modules\Leave\Entities\Leave;
use Modules\Leave\Entities\LeaveType;
use Modules\Leave\Entities\LeaveBalance;
use App\Models\User;
use Illuminate\Support\Facades\Http;


class CheckinService
{

    public function performCheckInCheckOut($branch_id = null): Checkin
    {
        try {
            Log::info('performCheckInCheckOut', array("branch_id" => $branch_id));

            Log::info('performCheckInCheckOut', array("performCheckInCheckOut" => "performCheckInCheckOut function in"));
            if (config('attendance.multi_checkins_allowed')) {
                $checkin = $this->multiCheckInCheckOut($branch_id);
                Log::info('performCheckInCheckOut', array("multi_checkins_allowed" => $checkin));
            } else {
                $checkin = $this->singleCheckInCheckOut($branch_id);
                Log::info('performCheckInCheckOut', array("single_checkins_allowed" => $checkin));
            }
            Log::info('performCheckInCheckOut', array("performCheckInCheckOut" => "performCheckInCheckOut function out"));
            return $checkin;
        } catch (Exception $e) {
            throw new Exception($e->getMessage());
        }
    }

    /**
     * Allow uset to have multiple checkin and checkout events
     */

    private function getShiftDeduction($user_id)
    {
        $lateComment = [];
        return $lateComment;

        $deductionRules = [
            ['minutes' => 60, 'deduction' => 3],
            ['minutes' => 30, 'deduction' => 2],
            ['minutes' => 15, 'deduction' => 1],
        ];


        $userShifts = UsersShift::where('user_id', $user_id)
            ->whereDate('assigned_for_date', Carbon::today()->toDateString())
            ->orderBy('id', 'desc')
            ->first();
        // $shiftStart = $userShifts->shift_schedule_information->shift_start;
        if (!$userShifts) {
            return $lateComment;
        }
        if (!$userShifts->shift_schedule_information) {
            return $lateComment;
        }
        $shiftStart = $userShifts->shift_schedule_information->shift_start;
        $arrivalTime = date('H:i:s');
        $shiftDateTime = Carbon::today()->setTimeFromTimeString($shiftStart);
        $arrivalDateTime = Carbon::today()->setTimeFromTimeString($arrivalTime);
        $ded = 0;
        $minutesLate = $arrivalDateTime->diffInMinutes($shiftDateTime);
        foreach ($deductionRules as $rule) {
            if ($minutesLate >= $rule['minutes']) {
                $ded = $rule['deduction'];
                break;
            }
        }
        $dedTime = $this->deductHoursFromTime(date('H:i:s'), $ded);
        if ($ded != 0) {
            $lateComment['ded'] = $ded;
            $lateComment['deductedTime'] = $dedTime;
            $lateComment['actualTime'] = date('H:i:s');
        }
        Log::info(json_encode($lateComment));
        return $lateComment;
    }

    private function multiCheckInCheckOut($branch_id = null): Checkin
    {
        Log::info('multiCheckInCheckOut', array("branch_id" => $branch_id));

        Log::info('multiCheckInCheckOut', array("multiCheckInCheckOut" => "multiCheckInCheckOut function in"));
        $type = CheckinType::IN;
        $record = Checkin::my()->where([
            //'date' => now()->toDateString(),
            'user_id' => auth()->id()
        ])->orderByDesc('id')->limit(1)->first();


        // if ($record) {
        //     if (($record->type == CheckinType::IN->value) || ($record->type == CheckinType::LATE->value)) {
        //         $type = CheckinType::OUT;
        //     }
        // }
        // $lateComment = $this->getShiftDeduction(auth()->id());
        // $shiftTime = date('H:i:s');
        // if ((count($lateComment) != 0) && ($type == CheckinType::IN)) {
        //     $shiftTime = $lateComment['deductedTime'];
        //     $type =  CheckinType::IN;
        //     Log::info('create::'.json_encode($lateComment));
        //     $checkin = Checkin::create([
        //         'user_id' => auth()->id(),
        //         'date' => now()->toDateString(),
        //         'time' => $shiftTime,
        //         'type' => $type,
        //         'latecomment' => json_encode($lateComment)
        //     ]);
        // } else {
        //     $checkin = Checkin::create([
        //         'user_id' => auth()->id(),
        //         'date' => now()->toDateString(),
        //         'time' => $shiftTime,
        //         'type' => $type
        //     ]);
        // }

        if ($record) {
            if ($record->type == CheckinType::IN->value) {
                $type = CheckinType::OUT;
                Log::info('multiCheckInCheckOut-126', array("type" => $type));
            }
        }
        $user_id = auth()->id();
        $location_parameter = User::select('department_id', 'longitude as user_longitude', 'latitude as user_latitude')->where('id', $user_id)->first();
        $user_lat = "";
        $user_lng = "";
        $location = "";
        if ($location_parameter) {
            $user_lat = floatval($location_parameter->user_latitude);
            $user_lng = floatval($location_parameter->user_longitude);
            try {
                // $response = Http::get("https://maps.googleapis.com/maps/api/geocode/json", [
                //     'latlng' => "$user_lat,$user_lng", // Single string with a comma
                //     'key' => env('GOOGLE_MAPS_API_KEY'),
                // ]);
                // if ($response->successful()) {
                //     $data = $response->json();
                //     if (isset($data['results'][0])) {
                //         $location =  $data['results'][0]['formatted_address']; // Get the full address
                //         Log::info('multiCheckInCheckOut-145', array("location" => $location));
                //     }
                // }
            } catch (Exception $e) {
                // Log the exception for debugging
                Log::error("Error fetching location: " . $e->getMessage());
            }
        }

        $checkin = Checkin::create([
            'user_id' => auth()->id(),
            'date' => now()->toDateString(),
            'time' => date('H:i:s'),
            'type' => $type,
            'location' => $location,
            'longitude' => $user_lng,
            'latitude' => $user_lat,
            'branch_id' => $branch_id
        ]);
        Log::info('multiCheckInCheckOut', array("checkin" => $checkin));
        if ($type->value == 'in') {
            $record = Checkin::my()->where([
                'date' => now()->toDateString(),
                'type' => 'in',
                'user_id' => auth()->id()
            ])->get();
            Log::info('multiCheckInCheckOut', array("record" => $record));
            if ($type->value == 'in') {
                // if today is holoday than add PH leave
                // $user_id = auth()->id();
                // $date = Carbon::now()->toDateString();
                // $holiday = Holiday::whereDate('start_date', '<=', $date)->whereDate('end_date', '>=', $date)->first();
                // if($holiday){
                //     $is_phleave = LeaveType::where('name','like', '%PH%')->first();
                //     if($is_phleave){
                //         $isleaveBL = LeaveBalance::where([['year',Carbon::now()->year],['user_id',$user_id],['leave_type_id',$is_phleave->id]])->first();
                //         if($isleaveBL){
                //             if ($isleaveBL->is_add_ph_leave != $date) {
                //                 $isleaveBL->update([
                //                     'available' => $isleaveBL->available + 1,
                //                     'is_add_ph_leave' => $date
                //                 ]);
                //             }
                //         } else {
                //             $total_leaves = $is_phleave->days;
                //             $user = User::find($user_id);
                //             $joining_date = $user->workDetail?->joining_date->toDateString();
                //             if (Carbon::parse($joining_date)->isCurrentYear()) {
                //                 $month = Carbon::parse($joining_date)->format('m');
                //                 $leaveTotal = $total_leaves / 12;
                //                 $totalmonth = 12 - ($month-1);
                //                 $total_leaves = floor($leaveTotal * $totalmonth);
                //             }
                //             $addleaveBL = LeaveBalance::create([
                //                 'year' => Carbon::now()->year,
                //                 'available' => $total_leaves + 1,
                //                 'user_id' => $user_id,
                //                 'leave_type_id' => $is_phleave->id,
                //                 'is_add_ph_leave' => $date
                //             ]);
                //         }
                //     } else {
                //         $leaveType = LeaveType::create([
                //             'name' => 'PH',
                //             'days' => 0,
                //             'no_of_leaves' => 0,
                //             'is_paid' => 0,
                //             'is_recurring' => 0,
                //             'type' => 'working',
                //         ]);

                //         $addleaveBL = LeaveBalance::create([
                //             'year' => Carbon::now()->year,
                //             'available' => 1,
                //             'user_id' => $user_id,
                //             'leave_type_id' => $leaveType->id,
                //         ]);
                //     }
                // }
                // end
            }
        }

        Log::info('multiCheckInCheckOut', array("multiCheckInCheckOut" => "multiCheckInCheckOut function out"));
        return $checkin;
    }

    /**
     * Only allowed the user to have only single check in and check out
     */
    private function singleCheckInCheckOut($branch_id = null)
    {
        Log::info('singleCheckInCheckOut', array("branch_id" => $branch_id));
        Log::info('singleCheckInCheckOut', array("singleCheckInCheckOut" => "singleCheckInCheckOut function in"));
        $checkinExist = Checkin::my()->where([
            'date' => now()->toDateString(),
            'type' => CheckinType::IN
        ])->exists();
        $lateComment = $this->getShiftDeduction(auth()->id());
        $shiftTime = date('H:i:s');
        $type = CheckinType::IN;
        if (count($lateComment) != 0) {
            $shiftTime = $lateComment['deductedTime'];
            $type = CheckinType::IN;
        }
        if (!$checkinExist) {
            $event = Checkin::create([
                'user_id' => auth()->id(),
                'date' => now()->toDateString(),
                'time' => $shiftTime,
                'type' => $type,
                'lateComment' => json_encode($lateComment),
                'branch_id' => $branch_id
            ]);
            Log::info('singleCheckInCheckOut', array("event" => $event));
            // if today is holoday than add PH leave
            // $user_id = auth()->id();
            // $date = Carbon::now()->toDateString();
            // $holiday = Holiday::whereDate('start_date', '<=', $date)->whereDate('end_date', '>=', $date)->first();
            // if($holiday){
            //     $is_phleave = LeaveType::where('name','like', '%PH%')->first();
            //     if($is_phleave){
            //         $isleaveBL = LeaveBalance::where([['year',Carbon::now()->year],['user_id',$user_id],['leave_type_id',$is_phleave->id]])->first();
            //         if($isleaveBL){
            //             if (Carbon::parse($isleaveBL->is_add_ph_leave)->toDateString() != $date) {
            //                 $isleaveBL->update([
            //                     'available' => $isleaveBL->available + 1,
            //                     'is_add_ph_leave' => $date
            //                 ]);
            //             }
            //         } else {
            //             $total_leaves = $is_phleave->days;
            //             $user = User::find($user_id);
            //             $joining_date = $user->workDetail?->joining_date->toDateString();
            //             if (Carbon::parse($joining_date)->isCurrentYear()) {
            //                 $month = Carbon::parse($joining_date)->format('m');
            //                 $leaveTotal = $total_leaves / 12;
            //                 $totalmonth = 12 - ($month-1);
            //                 $total_leaves = floor($leaveTotal * $totalmonth);
            //             }
            //             $addleaveBL = LeaveBalance::create([
            //                 'year' => Carbon::now()->year,
            //                 'available' => $total_leaves + 1,
            //                 'user_id' => $user_id,
            //                 'leave_type_id' => $is_phleave->id,
            //                 'is_add_ph_leave' => $date
            //             ]);
            //         }
            //     } else {
            //         $leaveType = LeaveType::create([
            //             'name' => 'PH',
            //             'days' => 0,
            //             'no_of_leaves' => 0,
            //             'is_paid' => 0,
            //             'is_recurring' => 0,
            //             'type' => 'working',
            //         ]);

            //         $addleaveBL = LeaveBalance::create([
            //             'year' => Carbon::now()->year,
            //             'available' => 1,
            //             'user_id' => $user_id,
            //             'leave_type_id' => $leaveType->id,
            //         ]);
            //     }
            // }
            // end
        } else {
            $checkOutExist = Checkin::my()->where([
                'date' => now()->toDateString(),
                'type' => CheckinType::OUT
            ])->exists();
            Log::info('singleCheckInCheckOut', array("checkOutExist" => $checkOutExist));
            if (!$checkOutExist) {
                $event = Checkin::create([
                    'user_id' => auth()->id(),
                    'date' => now()->toDateString(),
                    'time' => date('H:i:s'),
                    'type' => CheckinType::OUT,
                    'branch_id' => $branch_id
                ]);
                Log::info('singleCheckInCheckOut', array("not checkOutExist event" => $event));
            } else {
                throw new Exception(__trans('you_already_have_clocked_out'));
            }
        }
        Log::info('singleCheckInCheckOut', array("singleCheckInCheckOut" => "singleCheckInCheckOut function out"));
        return $event;
    }

    private function deductHoursFromTime($time, $hoursToDeduct)
    {
        // Convert the input time to a Carbon object
        $timeObj = Carbon::createFromFormat('H:i:s', $time);

        // Deduct the specified number of hours
        $deductedTimeObj = $timeObj->addHours($hoursToDeduct);

        // Format the deducted time as 'H:i:s'
        $deductedTime = $deductedTimeObj->format('H:i:s');

        return $deductedTime;
    }
}
