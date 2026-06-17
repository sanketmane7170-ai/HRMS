<?php
namespace Modules\Api\Http\Controllers\Attendance;

use App\Models\Department;
use App\Models\Setting;
use App\Models\User;
use App\Models\UserShift;
use Carbon\Carbon;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Modules\Api\Transformers\TimelineResource;
use Modules\Attendance\Entities\Attendance;
use Modules\Attendance\Entities\Breakin;
use Modules\Attendance\Entities\Checkin;
use Modules\Attendance\Entities\Holiday;
use Modules\Attendance\Enums\CheckinType;
use Modules\Attendance\Services\CheckinService;
use Modules\Leave\Entities\LeaveBalance;
use Modules\Leave\Entities\LeaveType;
use Modules\Shift\Entities\UsersShift;

/**
 * @group 3. Attendance
 */
class CheckinController extends Controller
{
    /**
     * Handle Request for the checkin page data of loggedin user
     *
     * @authenticated
     * @response status=200 scenario="Data Load Successfully"{
     *    "success": true,
     *    "message": "Checkin data loaded successfully",
     *    "data": {
     *       "total_worked_today_in_hours": 6,
     *       "total_worked_this_week_in_hours": 6,
     *       "is_currently_check_in": false,
     * }
     * @response status=401 scenario="Unauthenticated" {
     *     "message": "Unauthenticated."
     * }
     */
    public function __construct()
    {
        $setting  = Setting::select('key', 'value')->whereIn('key', ['radius', 'longitude', 'latitude'])->get();
        $location = [];
        foreach ($setting as $result) {
            if ($result->key == 'radius') {
                $location['radius'] = $result->value;
            } else if ($result->key == 'latitude') {
                $location['latitude'] = $result->value;
            } else {
                $location['longitude'] = $result->value;
            }
        }
        $this->company_radius    = $location['radius'];
        $this->company_longitude = floatval($location['longitude']);
        $this->company_latitude  = floatval($location['latitude']);
    }

    public function index(Request $request)
    {

        Log::info('CheckinController', ["index" => $request]);
        $totalWorkedToday    = '00:00';
        $totalWorkedThisWeek = '00:00';

        $totalWorkedTodayMinutes = Attendance::my()->where('date', now()->toDateString())
            ->sum('total_worked') ?? 0;
        $totalWorkedToday = $this->timeConversion($totalWorkedTodayMinutes);

        //Old Logic
        // $totalWorkedToday = Attendance::my()->where('date', now()->toDateString())
        //     ->firstOrNew()->getTotalWorkedInHours();
        // $totalWorkedToday = self::timeConversion($totalWorkedToday);

        $totalWorkedThisWeekMinutes = Attendance::my()->where('date', '>=', now()->startOfWeek())
            ->sum('total_worked') ?? 0;
        $totalWorkedThisWeek = $this->timeConversion($totalWorkedThisWeekMinutes);
        $user_id             = auth()->id();
        $department_id       = User::where('id', $user_id)->pluck('department_id')->first();
        $branch              = Department::where('id', $department_id)->first();
        $data                = [
            'total_worked_today_in_hours'     => $totalWorkedToday,
            // 'total_worked_this_week_in_hours' => round($totalWorkedThisWeek, 2),
            'total_worked_this_week_in_hours' => $totalWorkedThisWeek,
            'is_currently_check_in'           => ! isUserCheckedIn(auth()->id(), \Modules\Attendance\Enums\CheckinType::OUT),
            'is_currently_break_in'           => ! isUserBreakedIn(auth()->id(), \Modules\Attendance\Enums\BreakinType::OUT),
            'is_currently_visit_in'           => ! isUserVisitedIn(auth()->id(), \Modules\Attendance\Enums\VisitinType::OUT),
            //'radius' => $branch->login_radius,
            //'latitude' => floatval($branch->latitude),
            //'longitude' => floatval($branch->longitude)
        ];
        Log::info('CheckinController', ["data" => $data]);

        return response()->success(__trans('checkin_data_loaded_successfully'), $data);
    }

    private function timeConversion($timeInMinutes)
    {
        $hours   = floor($timeInMinutes / 60);
        $minutes = $timeInMinutes % 60;

        return sprintf('%02d:%02d', $hours, $minutes);
    }

    /**
     * Handle logged in User Checkin and checkout request
     *
     * @authenticated
     *
     * @response status=200 scenario="When User has checked out"{
     *    "success": true,
     *    "message": "You has been clock out successfully",
     *    "data": {
     *       "is_currently_check_in": false,
     * }
     *
     * @response status=200 scenario="When User has checked in"{
     *    "success": true,
     *    "message": "You has been clock in successfully",
     *    "data": {
     *       "is_currently_check_in": true,
     * }
     *
     * @response status=401 scenario="Unauthenticated" {
     *     "message": "Unauthenticated."
     * }
     */
    public function handleMultiCheckIns(CheckinService $checkinService)
    {
        Log::info('handleMultiCheckIns', ["handleMultiCheckIns" => "handleMultiCheckIns function in"]);
        Log::info('handleMultiCheckIns', ["checkinService" => $checkinService]);
        $user_id            = auth()->id();
        $location_parameter = User::select('department_id', 'longitude as user_longitude', 'latitude as user_latitude')->where('id', $user_id)->first();
        $branch             = Department::where('id', $location_parameter->department_id)->first();
        // $company_lat = $this->company_latitude;
        // $company_lng = $this->company_longitude;
        $company_lat = floatval($branch->latitude);
        $company_lng = floatval($branch->longitude);

        $user_lat = floatval($location_parameter->user_latitude);
        $user_lng = floatval($location_parameter->user_longitude);

        $unit = "M"; //M = miles
                     // $getdistance = $this->distanceInMeters($company_lat, $company_lng, $user_lat, $user_lng);
        $getdistance = $this->distanceInMeters2($company_lat, $company_lng, $user_lat, $user_lng);

        $getdistance       = str_replace(',', '', $getdistance);
        $getdistance_float = (float) $getdistance;
        // $radius = $this->company_radius;
        $radius = $branch->login_radius;
        if ($getdistance_float <= $radius) {
            $checkin = $checkinService->performCheckInCheckOut();
            $data    = [
                'is_currently_check_in' => isUserCheckedIn(auth()->id()),
                //'required_radius' => $radius,
                'user_radius'           => $getdistance,
                'status'                => 'Under Radius',
                'message'               => 'You are under your company radius',
            ];

            return response()->success(createFlashMessage('you', 'Clock ' . $checkin->type->name), $data);
        } else {
            $type   = CheckinType::IN;
            $record = Checkin::my()->where([
                'date'    => now()->toDateString(),
                'user_id' => auth()->id(),
            ])->orderByDesc('id')->limit(1)->first();

            if ($record) {
                if ($record->type == CheckinType::IN->value) {
                    $type    = CheckinType::OUT;
                    $checkin = Checkin::create([
                        'user_id' => auth()->id(),
                        'date'    => now()->toDateString(),
                        'time'    => date('H:i:s'),
                        'type'    => $type,
                    ]);
                }
            }

            $data = [
                'is_currently_check_in' => false,
                //'required_radius' => $radius,
                'user_radius'           => $getdistance,
                'status'                => 'Out Side Radius',
                'message'               => 'You are outside login radius',
            ];
            Log::info('handleMultiCheckIns', ["data" => $data]);
            Log::info('handleMultiCheckIns', ["handleMultiCheckIns" => "handleMultiCheckIns function out"]);
            return response()->success('', $data);
        }
    }

    public function handleMultiCheckInswithlocation(Request $request, CheckinService $checkinService)
    {

        Log::info('handleMultiCheckInswithlocation', ["request" => $request]);
        Log::info('handleMultiCheckInswithlocation', ["checkinService" => $checkinService]);

        $validator = Validator::make($request->all(), [
            'longitude' => ['required', 'string'],
            'latitude'  => ['required', 'string'],
        ]);

        Log::info('handleMultiCheckInswithlocation', ["validator" => $validator]);
        if ($validator->fails()) {
            return response()->error(__trans('validation_failed'), $validator->errors());
        }
        if (isset($request->longitude) && isset($request->latitude)) {
            try {
                // $data = $validator->validated();
                $data['longitude'] = $request->longitude;
                $data['latitude']  = $request->latitude;
                $data['user_id']   = auth()->id();
                auth()->user()->update($data);
                Log::info('updateuserlocation', ["data" => $data]);
            } catch (Exception $e) {
                return response()->error($e->getMessage());
            }
        }
        $user_id            = auth()->id();
        $location_parameter = User::select('department_id', 'longitude as user_longitude', 'latitude as user_latitude')->where('id', $user_id)->first();
        $branch             = Department::where('id', $location_parameter->department_id)->first();
        // $company_lat = $this->company_latitude;
        // $company_lng = $this->company_longitude;
        $company_lat = floatval($branch->latitude);
        $company_lng = floatval($branch->longitude);
        $user_lat    = floatval($location_parameter->user_latitude);
        $user_lng    = floatval($location_parameter->user_longitude);
        Log::info('handleMultiCheckInswithlocation', ["location_parameter" => $location_parameter]);
        $unit = "M"; //M = miles
                     // $getdistance = $this->distanceInMeters($company_lat, $company_lng, $user_lat, $user_lng);
        $getdistance = $this->distanceInMeters2($company_lat, $company_lng, $user_lat, $user_lng);
        Log::info('handleMultiCheckInswithlocation', ["getdistance" => $getdistance]);
        $getdistance       = str_replace(',', '', $getdistance);
        $getdistance_float = (float) $getdistance;
        // $radius = $this->company_radius;
        $radius   = $branch->login_radius;
        $user     = auth()->user();
        $is_rider = isset($user->workDetail) ? $user->workDetail->is_rider : 0;
        if ($getdistance_float < $request->radius) {
            $data = [
                'is_currently_check_in' => isUserCheckedIn(auth()->id()),
                //'required_radius' => $radius,
                'user_radius'           => $getdistance,
                'status'                => 'Under Radius',
                'message'               => 'You are under your company radius',
            ];
            return response()->success(createFlashMessage('you', 'Clock ' . $request->type), $data);
        }
        if ($getdistance_float <= $radius || $is_rider == 1) {
            $checkin = $checkinService->performCheckInCheckOut();
            $data    = [
                'is_currently_check_in' => isUserCheckedIn(auth()->id()),
                //'required_radius' => $radius,
                'user_radius'           => $getdistance,
                'status'                => 'Under Radius',
                'message'               => 'You are under your company radius',
            ];
            // if today is holoday than add PH leave
            $date    = Carbon::now()->toDateString();
            $holiday = Holiday::whereDate('start_date', '<=', $date)->whereDate('end_date', '>=', $date)->first();
            if ($holiday) {
                $is_phleave = LeaveType::where('name', 'like', '%PH%')->first();
                if ($is_phleave) {
                    $isleaveBL = LeaveBalance::where([['year', Carbon::now()->year], ['user_id', $user_id], ['leave_type_id', $is_phleave->id]])->first();
                    if ($isleaveBL) {
                        $isleaveBL->update([
                            'available' => $isleaveBL->available + 1,
                        ]);
                    } else {
                        $total_leaves = $is_phleave->days;
                        $user         = User::find($user_id);
                        $joining_date = $user->workDetail?->joining_date->toDateString();
                        if (Carbon::parse($joining_date)->isCurrentYear()) {
                            $month        = Carbon::parse($joining_date)->format('m');
                            $leaveTotal   = $total_leaves / 12;
                            $totalmonth   = 12 - ($month - 1);
                            $total_leaves = floor($leaveTotal * $totalmonth);
                        }
                        $addleaveBL = LeaveBalance::create([
                            'year'          => Carbon::now()->year,
                            'available'     => $total_leaves + 1,
                            'user_id'       => $user_id,
                            'leave_type_id' => $is_phleave->id,
                        ]);
                    }
                } else {
                    $leaveType = LeaveType::create([
                        'name'         => 'PH',
                        'days'         => 0,
                        'no_of_leaves' => 0,
                        'is_paid'      => 0,
                        'is_recurring' => 0,
                        'type'         => 'working',
                    ]);

                    $addleaveBL = LeaveBalance::create([
                        'year'          => Carbon::now()->year,
                        'available'     => 1,
                        'user_id'       => $user_id,
                        'leave_type_id' => $leaveType->id,
                    ]);
                }
            }
            // end
            return response()->success(createFlashMessage('you', 'Clock ' . $checkin->type->name), $data);
        } else {
            $type   = CheckinType::IN;
            $record = Checkin::my()->where([
                'date'    => now()->toDateString(),
                'user_id' => auth()->id(),
            ])->orderByDesc('id')->limit(1)->first();
            if ($record) {
                if ($record->type == CheckinType::IN->value) {
                    $type    = CheckinType::OUT;
                    $checkin = Checkin::create([
                        'user_id' => auth()->id(),
                        'date'    => now()->toDateString(),
                        'time'    => date('H:i:s'),
                        'type'    => $type,
                    ]);
                }
            }

            $data = [
                'is_currently_check_in' => false,
                //'required_radius' => $radius,
                'user_radius'           => $getdistance,
                'status'                => 'Out Side Radius',
                'message'               => 'You are outside login radius',
            ];
            Log::info('handleMultiCheckInswithlocation', ["data" => $data]);
            return response()->success('', $data);
        }
    }

    // public function getAttendanceByID($month)
    // {
    //     // $requests = AttendanceResource::collection(Attendance::my()->whereMonth('date',$month)->get());
    //     // Changes BY CLIENT THEN API UPDATED 12-MARCH-2024
    //     //$checkInOut_data = Checkin::my()->whereMonth('date',$month)->whereNotIn('is_auto_update', ['1'])->get();
    //     // Again Client changed condition 11-06-2024
    //     $checkInOut_data = Checkin::my()->whereMonth('date', $month)->get();
    //     foreach ($checkInOut_data as $data) {
    //         if ($data->type == 'in') {
    //             $data['action'] = 'Clock In';
    //         } else {
    //             $data['action'] = 'Clock Out';
    //         }
    //     }
    //     $breakInOut_data = Breakin::my()->whereMonth('date', $month)->get();
    //     foreach ($breakInOut_data as $data) {
    //         if ($data->type == 'in') {
    //             $data['action'] = 'Break In';
    //         } else {
    //             $data['action'] = 'Break Out';
    //         }
    //     }
    //     // $mergedData = $checkInOut_data->merge($breakInOut_data);
    //     // $requests = TimelineResource::collection($mergedData);
    //     $mergedData = $checkInOut_data->merge($breakInOut_data);
    //     $sortedMergedData = $mergedData->sortBy('date');
    //     $requests = TimelineResource::collection($sortedMergedData);
    //     return response()->success(__trans('user_attendance_list_fetched_successfully'), $requests);
    // }
    public function getAttendanceByID(Request $request, $month = null, $year = null)
    {
        $month = $month ?? now()->month;
        $year  = $year ?? now()->year;

        $checkInOut_data = Checkin::my()
            ->whereMonth('date', $month)
            ->whereYear('date', $year)
            ->get();

        foreach ($checkInOut_data as $data) {
            $data['action'] = $data->type === 'in' ? 'Clock In' : 'Clock Out';
        }

        $breakInOut_data = Breakin::my()
            ->whereMonth('date', $month)
            ->whereYear('date', $year)
            ->get();

        foreach ($breakInOut_data as $data) {
            $data['action'] = $data->type === 'in' ? 'Break In' : 'Break Out';
        }

        $mergedData = $checkInOut_data->merge($breakInOut_data)
            ->sortBy('date');

        $requests = TimelineResource::collection($mergedData);

        return response()->success(
            __trans('user_attendance_list_fetched_successfully'),
            $requests
        );
    }

    public function distance($lat1, $lon1, $lat2, $lon2, $unit)
    {
        if (($lat1 == $lat2) && ($lon1 == $lon2)) {
            return 0;
        } else {
            $theta = $lon1 - $lon2;
            $dist  = sin(deg2rad($lat1)) * sin(deg2rad($lat2)) + cos(deg2rad($lat1)) * cos(deg2rad($lat2)) * cos(deg2rad($theta));
            $dist  = acos($dist);
            $dist  = rad2deg($dist);
            $miles = $dist * 60 * 1.1515;
            $unit  = strtoupper($unit);

            if ($unit == "K") {
                return ($miles * 1.609344);
            } else if ($unit == "N") {
                return ($miles * 0.8684);
            } else {
                return $miles;
            }
        }
    }

    public function distanceInMeters($lat1, $lon1, $lat2, $lon2)
    {
        $theta      = $lon1 - $lon2;
        $dist       = sin(($lat1)) * sin(deg2rad($lat2)) + cos(deg2rad($lat1)) * cos(deg2rad($lat2)) * cos(deg2rad($theta));
        $dist       = acos($dist);
        $dist       = rad2deg($dist);
        $miles      = $dist * 60 * 1.1515;
        $kilometers = $miles * 1.609344;
        $meters     = $kilometers * 1000;

        return number_format((float) $meters, 2, '.', '');
    }

    public function haversine($lat1, $lon1, $lat2, $lon2)
    {
        // Convert latitude and longitude from degrees to radians
        $lat1 = deg2rad($lat1);
        $lon1 = deg2rad($lon1);
        $lat2 = deg2rad($lat2);
        $lon2 = deg2rad($lon2);

        // Haversine formula
        $dlat     = $lat2 - $lat1;
        $dlon     = $lon2 - $lon1;
        $a        = sin($dlat / 2) ** 2 + cos($lat1) * cos($lat2) * sin($dlon / 2) ** 2;
        $c        = 2 * atan2(sqrt($a), sqrt(1 - $a));
        $distance = 6371 * $c; // Radius of Earth in kilometers

        return $distance;
    }
    public function distanceInMeters2($company_lat, $company_lon, $user_lat, $user_lon)
    {
        return number_format($this->haversine($company_lat, $company_lon, $user_lat, $user_lon) * 1000, 2);
    }

    public function checkShiftTiming()
    {
        $user_id   = auth()->id();
        $userShift = UserShift::select('shift_start', 'shift_end', 'user_id')->where('user_id', $user_id)->first();
    }

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
        if (! $userShifts) {
            return $lateComment;
        }
        if (! $userShifts->shift_schedule_information) {
            return $lateComment;
        }
        $shiftStart      = $userShifts->shift_schedule_information->shift_start;
        $arrivalTime     = date('H:i:s');
        $shiftDateTime   = Carbon::today()->setTimeFromTimeString($shiftStart);
        $arrivalDateTime = Carbon::today()->setTimeFromTimeString($arrivalTime);
        $ded             = 0;
        $minutesLate     = $arrivalDateTime->diffInMinutes($shiftDateTime);
        foreach ($deductionRules as $rule) {
            if ($minutesLate >= $rule['minutes']) {
                $ded = $rule['deduction'];
                break;
            }
        }
        $dedTime = $this->deductHoursFromTime(date('H:i:s'), $ded);
        if ($ded != 0) {
            $lateComment['ded']          = $ded;
            $lateComment['deductedTime'] = $dedTime;
            $lateComment['actualTime']   = date('H:i:s');
        }
        Log::info(json_encode($lateComment));
        return $lateComment;
    }
}
