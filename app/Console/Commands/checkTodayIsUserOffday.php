<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Shifts;
use App\Models\User;
use Modules\Attendance\Entities\Attendance;
use Carbon\Carbon;
use Modules\Shift\Entities\UsersShift;
use Modules\Attendance\Enums\AttendanceStatus;
use App\Models\ShiftSchedule;
use Modules\Leave\Entities\Leave;
use Modules\Attendance\Entities\Holiday;
use App\Models\Setting;
use Exception;
use Modules\Announcement\Entities\Announcement;
use Modules\Announcement\Entities\AnnouncementType;
use Google\Client as GoogleClient;
use GuzzleHttp\Client;

class checkTodayIsUserOffday extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:check-today-is-user-offday';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'to check today is user offday';

    /**
     * Execute the console command.
     */
    public function handle()
    {

        $this->info('[' . now() . '] User offday details checking started.');
        $users = User::query()
                    ->whereDoesntHave('roles', function ($query) {
                        $query->whereIn('name', [User::ROLE_ADMIN, User::ROLE_SUPER_ADMIN]);
                    })->where('status',User::STATUS_ACTIVE)
                    ->get();
        $year = date('Y');
        $month = date('m');
        $date = Carbon::now()->toDateString();
        $yesterday = Carbon::yesterday()->toDateString();

        foreach($users as $user){
            $this->info('[' . now() . '] user: ' . json_encode($user));
            
            // check absent
            $todayisleave = Leave::where([['user_id', $user->id],['status','approved']])
                        ->whereDate('start_date', '<=', $yesterday)
                        ->whereDate('end_date', '>=', $yesterday)
                        ->first();
            $todayisholiday = Holiday::whereDate('start_date', '<=', $yesterday)
                        ->whereDate('end_date', '>=', $yesterday)
                        ->first();
            $attendance = Attendance::where(
                [
                    'user_id' => $user->id,
                    'date' => $yesterday
                ]
            )->first();
            $nextusershift = UsersShift::where([['user_id',$user->id],['assigned_for_date',$yesterday]])->get();
            $todayisweekend = false;
            foreach($nextusershift as $shift){
                $shiftschdata = ShiftSchedule::where('id',$shift->schedule_id)->first();
                $shiftdata = Shifts::where('id',$shiftschdata->shift_id)->first();
                if($shiftdata && $shiftdata->is_weekend==1){
                    $todayisweekend = true;
                }
            }
            if(!$todayisleave && !$todayisholiday && !$attendance && $todayisweekend==false){
                $admin = User::whereHas('roles', function ($query) {
                    $query->whereIn('name', ['admin', 'super-admin']);
                })->first();
                $absentstatus = AttendanceStatus::Absent;
                $abattendance = Attendance::create(
                    [
                        'user_id' => $user->id,
                        'date' => $yesterday,
                        'status' => $absentstatus,
                        'created_by_id' => $admin->id,
                    ]
                );
            }
            if(count($nextusershift) > 0 && $shiftschdata){
                // auto late attendance
                if(!$todayisleave && !$todayisholiday && $attendance && $todayisweekend==false){
                    $shiftstart = $shiftschdata->shift_start;
                    $attendanceckin = $attendance->clock_in;
                    if($shiftstart != null && $attendanceckin != null){
                        $shiftstarttime = Carbon::createFromFormat('H:i:s', $shiftstart);
                        $attendanceckintime = Carbon::createFromFormat('H:i:s', $attendanceckin);
                        $maxLateMinute = Setting::where('key', 'maximum_late_come_minute')->value('value');
                        $lateMinutes = (new Carbon($attendanceckintime))->diffInMinutes(new Carbon($shiftstarttime), true);
                        if($lateMinutes > $maxLateMinute){
                            if ($attendanceckintime->greaterThan($shiftstarttime)) {
                                $attendance->status = AttendanceStatus::Late;
                                $attendance->save();
                            }
                        }
                    }
                }

                $maxEarlyOutMinute = (int) Setting::where('key', 'maximum_early_out_minute')->value('value');
                if($maxEarlyOutMinute != null && $maxEarlyOutMinute > 0){
                    // auto early out attendance
                    if(!$todayisleave && !$todayisholiday && $attendance && $todayisweekend==false){
                        $shiftend = $shiftschdata->shift_end;
                        $shiftstart = $shiftschdata->shift_start;
                        $attendanceckout = $attendance->clock_out;
                        if($shiftend != null && $attendanceckout != null){
                            // $shiftendtime = Carbon::createFromFormat('H:i:s', $shiftend);
                            // $attendanceckouttime = Carbon::createFromFormat('H:i:s', $attendanceckout);
                            // $maxEarlyOutMinute = Setting::where('key', 'maximum_early_out_minute')->value('value');
                            // $earlyOutMinutes = (new Carbon($shiftendtime))->diffInMinutes(new Carbon($attendanceckouttime), true);
                            // if($attendanceckouttime->lessThan($shiftendtime)){
                            //     if($earlyOutMinutes > $maxEarlyOutMinute){
                            //         $attendance->status = AttendanceStatus::EarlyOut;
                            //         $attendance->save();
                            //     }
                            // }

                            $shiftDate = Carbon::parse($attendance->date);

                            // Create Carbon instances with date context
                            $shiftStartTime = Carbon::createFromFormat('Y-m-d H:i:s', $shiftDate->format('Y-m-d') . ' ' . $shiftstart);
                            $shiftEndTime = Carbon::createFromFormat('Y-m-d H:i:s', $shiftDate->format('Y-m-d') . ' ' . $shiftend);

                            // If shift end is before shift start, it's a next-day shift
                            if ($shiftEndTime->lessThan($shiftStartTime)) {
                                $shiftEndTime->addDay();
                            }

                            // Attach proper date to checkout time
                            $checkoutDateTime = Carbon::createFromFormat('Y-m-d H:i:s', $shiftDate->format('Y-m-d') . ' ' . $attendanceckout);
                            if ($checkoutDateTime->lessThan($shiftStartTime)) {
                                // Assume checkout happened next day
                                $checkoutDateTime->addDay();
                            }

                            // Fetch max early out threshold
                            $maxEarlyOutMinute = (int) Setting::where('key', 'maximum_early_out_minute')->value('value');

                            // Check for early out
                            if ($checkoutDateTime->lessThan($shiftEndTime)) {
                                $earlyOutMinutes = $shiftEndTime->diffInMinutes($checkoutDateTime);
                                
                                if ($earlyOutMinutes > $maxEarlyOutMinute) {
                                    $attendance->status = AttendanceStatus::EarlyOut;
                                    $attendance->save();
                                }
                            }
                        }
                    }
                }
            }
            $usershift = UsersShift::where([['user_id',$user->id],['assigned_for_date',$date]])->get();
            foreach($usershift as $shift){
                $shiftschdata = ShiftSchedule::where('id',$shift->schedule_id)->first();
                $shiftdata = Shifts::where('id',$shiftschdata->shift_id)->first();
                if($shiftdata && $shiftdata->is_weekend==1){
                    $attendance = Attendance::firstOrNew(
                        [
                            'user_id' => $user->id,
                            'date' => $date
                        ]
                    );

                    $weekendstatus = AttendanceStatus::Weekend;
                    $admin = User::whereHas('roles', function ($query) {
                                $query->whereIn('name', ['admin', 'super-admin']);
                            })
                            ->first();
                    $attendance->clock_in = '00:00';
                    $attendance->clock_out = '00:00';
                    $attendance->status = $weekendstatus;
                    $attendance->remark = 'today is user weekend day is system added.';
                    $attendance->created_by_id = $admin->id;
                    $attendance->clockout_date = $date;
                    $attendance->date = $date;
                    $attendance->save();

                }
            }
        }

        // Fetch users with work anniversaries today
        $newJoiners = User::where('status',User::STATUS_ACTIVE)->with('workDetail')
                        ->whereHas('workDetail', function ($query) use ($yesterday) {
                            $query->whereDate('joining_date', '=', $yesterday);
                        })
                        
                        ->get();
        $workannouncementType = AnnouncementType::Where('name', 'like', '%Work Anniversary Announcement%')->first();
        if(!$workannouncementType){
            $workannouncement = AnnouncementType::create([
                'name' => 'New Joiner Announcement',
                'color' => '#a474ec'
            ]);
        }

        if($newJoiners->isNotEmpty()){
            foreach ($newJoiners as $newJoin) {
                $alreadyExists = Announcement::where('is_added', $newJoin->id)
                                                ->where('announcement_type_id', $workannouncementType ? $workannouncementType->id : null)
                                                ->whereDate('start_at', '<=', Carbon::today())
                                                ->whereDate('end_at', '>=', Carbon::today())
                                                ->exists();
                if (!$alreadyExists) {
                    $url = 'assets/backend/img/icon-user.svg';
                    if($newJoin->profile_image != null){
                        $url = $newJoin->profile_image;
                    }
                    $body = '<div class="card" style="box-shadow: 0 4px 8px 0 rgba(0,0,0,0.2);transition: 0.3s;width: 40%;background: #1b255945;position: relative;left: 30%;">
                                <div class="container" style="padding: 2px 16px;">
                                    <h4 style="text-align: center;">Welcome</h4>
                                    <p style="text-align: center;">To The Team</p>
                                </div>
                                <div class="container" style="padding: 2px 16px;">
                                    <img src="'.$url.'" alt="Avatar" style="width: 100%;height: 250px;">
                                </div>
                                <div class="container" style="padding: 2px 16px;">
                                    <h4 style="text-align: center;"><b>'.$newJoin->name.'</b></h4> 
                                    <p style="text-align: center;"> as </p><h5 style="text-align: center;">'.$newJoin->designation->name.'</h5> 
                                </div>
                            </div>';
                    // Create an announcement for each user with a birthday
                    Announcement::create([
                        'body' => $body,//'<p>Today, '.$newJoin->name.' joined the team. Welcome aboard!</p><p><br /></p><div style="text-align: center;" __rte_selected_block=""><img style="max-width: 80%;" src="'.$url.'"></div>',
                        'start_at' => Carbon::now(),
                        'end_at' => Carbon::now()->addWeek(),
                        'user_id' => null,
                        'is_added' => $newJoin->id,
                        'announcement_type_id' => $workannouncementType ? $workannouncementType->id : $workannouncement->id,
                    ]);
                    
                    if($newJoin->ftoken != null){
                        $serviceAccountFile = base_path('WorkPilot-b588f-44a1ea5aa896.json');
                        $client = new GoogleClient();
                        $client->setAuthConfig($serviceAccountFile);
                        $client->addScope('https://www.googleapis.com/auth/firebase.messaging');
                        $accessTokenArray = $client->fetchAccessTokenWithAssertion();
                        $accessToken =  $accessTokenArray['access_token'] ?? null;
        
                        $url = 'https://fcm.googleapis.com/v1/projects/WorkPilot-b588f/messages:send';
                        $headers = [
                            'Authorization' => 'Bearer ' . $accessToken,
                            'Content-Type' => 'application/json',
                        ];
                        $data = [
                            'message' => [
                                'token' => $newJoin->ftoken,
                                'notification' => [
                                    'title' => 'Reminder',
                                    'body' => 'Today, '.$newJoin->name.' joined the team. Welcome aboard!',
                                ],
                            ],
                        ];
                        try {
                            $httpClient =  new Client();
                            $response = $httpClient->post($url, [
                                'headers' => $headers,
                                'json' => $data,
                                'curl' => [
                                    CURLOPT_SSL_VERIFYPEER => false,
                                    CURLOPT_SSL_VERIFYHOST => false,
                                ],
                            ]);
                        } catch (Exception $e) {
                            // \Log::error("Failed to send notification to workuser ID {$workuser->id}: " . $e->getMessage());
                            continue;
                        } catch (\Exception $e) {
                            // \Log::error("An error occurred for workuser ID {$workuser->id}: " . $e->getMessage());
                            continue;
                        }
                    }
                }
                sleep(10);
            }
        }
        $this->info('[' . now() . '] User offday details check successfully.');
        $this->info('[' . now() . '] New user joining details fetched successfully.');

    }
}
