<?php

use Modules\Attendance\Entities\Checkin;
use Modules\Attendance\Enums\CheckinType;
use Modules\Attendance\Entities\Breakin;
use Modules\Attendance\Enums\BreakinType;
use Modules\Attendance\Entities\Visitin;
use Modules\Attendance\Enums\VisitinType;
use Modules\Attendance\Entities\LocationVisits;
use App\Models\Setting;
use App\Models\User;

if (!function_exists('isUserCheckedIn')) {
    function isUserCheckedIn($user_id, $type = CheckinType::IN)
    {
        if (config('attendance.multi_checkins_allowed')) {
            $checkin = false;
            $lastCheckin = Checkin::select('id', 'type')->where([
                //'date' => now()->toDateString(),
                'user_id' => $user_id,
            ])->limit(1)->orderByDesc('id')->first();
            if ($lastCheckin) {
                if (($lastCheckin->type == $type->value)) {
                    $checkin = true;
                }
            } else {
                $checkin = true;
            }
        } else {
            $checkin = Checkin::where([
                'date' => now()->toDateString(),
                'user_id' => $user_id,
                'type' => $type
            ])->exists();
        }
        return $checkin;
    }
}

if (!function_exists('isUserBreakedIn')) {
    function isUserBreakedIn($user_id, $type = BreakinType::IN)
    {
        if (config('attendance.multi_breakins_allowed')) {
            $breakin = false;
            $lastBreakin = Breakin::select('id', 'type')->where([
                'date' => now()->toDateString(),
                'user_id' => $user_id,
            ])->limit(1)->orderByDesc('id')->first();
            if ($lastBreakin) {
                if (($lastBreakin->type == $type->value)) {
                    $breakin = true;
                }
            } else {
                $breakin = true;
            }
        } else {
            $breakin = Breakin::where([
                'date' => now()->toDateString(),
                'user_id' => $user_id,
                'type' => $type
            ])->exists();
        }
        return $breakin;
    }
}

if (!function_exists('isUserVisitedIn')) {
    function isUserVisitedIn($user_id, $type = VisitinType::IN)
    {
        $visitRestrictedRadius = Setting::where('key','radius')->value('value');
        $user_location_parameter = User::select('longitude as user_longitude','latitude as user_latitude')->where('id',$user_id)->first();
        $unit = "M"; //M = miles
        if (config('attendance.multi_visitins_allowed')) {
            $visitin = false;
            $lastVisitin = Visitin::select('id', 'type','location_id')->where([
                'date' => now()->toDateString(),
                'user_id' => $user_id,
            ])->limit(1)->orderByDesc('id')->first();
            
            // Auto Visit Out Based On Radius
            // if ($lastVisitin !== null && $lastVisitin->type === "start") {
            //     // If Type is Start means User already started visit option so we will check it is under radius or not
            //     visitOutBasedOnRadius($lastVisitin->location_id, $user_location_parameter, $visitRestrictedRadius, $user_id);
            // }
            if ($lastVisitin) {
                if (($lastVisitin->type == $type->value)) {
                    $visitin = true;
                }
            } else {
                $visitin = true;
            }
        } else {
            $visitin = Visitin::where([
                'date' => now()->toDateString(),
                'user_id' => $user_id,
                'type' => $type
            ])->exists();
        }
        return $visitin;
    }

    function visitOutBasedOnRadius($location_id,$user_location_parameter,$visitRestrictedRadius,$user_id) {
        $visit_location_parameter = LocationVisits::select('longitude as visit_longitude','latitude as visit_latitude')->where('id',$location_id)->first();
        $getdistance = distanceInMeters(
            floatval($visit_location_parameter->visit_latitude),
            floatval($visit_location_parameter->visit_longitude),
            floatval($user_location_parameter->user_latitude),
            floatval($user_location_parameter->user_longitude),
        );
        if(floatval($getdistance) >= floatval($visitRestrictedRadius)){
            // Need to Visit Out User
            $type = VisitinType::OUT;
            LocationVisits::where('id',$location_id)->update(['visit_out'=>date('H:i:s'),'status' => 1]);
            $visitin = Visitin::create([
                'user_id' => $user_id,
                'date' => now()->toDateString(),
                'time' => date('H:i:s'),
                'type' => $type,
                'location_id' => $location_id
            ]);
        }
    }

    function distanceInMeters($lat1, $lon1, $lat2, $lon2) {
        $theta = $lon1 - $lon2;
        $dist = sin(deg2rad($lat1)) * sin(deg2rad($lat2)) + cos(deg2rad($lat1)) * cos(deg2rad($lat2)) * cos(deg2rad($theta));
        $dist = acos($dist);
        $dist = rad2deg($dist);
        $miles = $dist * 60 * 1.1515;
        $kilometers = $miles * 1.609344;
        $meters = $kilometers * 1000;
        
        return number_format((float)$meters, 2, '.', '');
    }  
}