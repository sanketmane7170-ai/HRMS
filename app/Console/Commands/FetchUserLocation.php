<?php
namespace App\Console\Commands;

use App\Models\Department;
use App\Models\Setting;
use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use Modules\Attendance\Entities\Checkin;
use Modules\Attendance\Enums\CheckinType;

class FetchUserLocation extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:fetch-user-location';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('[' . now() . '] User fetch-user-location started.');
        $setting = Setting::select('key', 'value')->whereIn('key', ['radius', 'longitude', 'latitude'])->get();
        Log::info('setting', ["setting" => $setting]);
        $company = [];
        foreach ($setting as $result) {
            if ($result->key == 'radius') {
                $company['radius'] = $result->value;
            } else if ($result->key == 'latitude') {
                $company['latitude'] = $result->value;
            } else {
                $company['longitude'] = $result->value;
            }
        }
        // $this->info("{$user->id} Out Side getdistance Condition.");
        Log::info('company', $company);
        $users = User::select('id', 'longitude as user_longitude', 'latitude as user_latitude', 'updated_at', 'status')->whereNotNull('longitude')->whereNotNull('latitude')->whereNotIn('name', [User::ROLE_ADMIN, User::ROLE_SUPER_ADMIN])->where('status', User::STATUS_ACTIVE)->get();
        foreach ($users as $user) {
            $this->info('[' . now() . '] user: ' . json_encode($user));

            Log::info('handle', ["user" => $user]);

            $user_lat          = $user->user_latitude;
            $user_lng          = $user->user_longitude;
            $unit              = "M"; //M = miles
            $department_id     = User::where('id', $user->id)->pluck('department_id')->first();
            $branch            = Department::where('id', $department_id)->first();
            $getdistance       = $this->distanceInMeters(floatval($branch->latitude), floatval($branch->longitude), floatval($user_lat), floatval($user_lng));
            $getdistance       = str_replace(',', '', $getdistance);
            $getdistance_float = (float) $getdistance;
            Log::info('handle-getdistance_float', ["getdistance_float" => $getdistance_float]);

            $radius = $branch->login_radius;
            Log::info('handle-branch_radius', ["branch_radius" => $branch->login_radius]);

            $record = Checkin::where([
                // 'date' => now()->toDateString(),
                'user_id' => $user->id,
            ])->orderByDesc('id')->limit(1)->first();
            Log::info('handle-check_in_record', ["check_in_record" => $record]);

            $lastUpdatedTime = $user->updated_at;
            /* Auto-Logout if User Account Deactivated From Database */
            if ($user->status === 'in-active') {
                $user->tokens()->delete();
            }

            $is_rider = isset($user->workDetail) ? $user->workDetail->is_rider : 0;
            Log::info('handle', ["is_rider" => $is_rider]);

            if (($getdistance_float > $radius && $is_rider == 0)) {

                $type = CheckinType::IN;
                if ($record) {
                    if ($record->type == CheckinType::IN->value && $record->face_attendance == 0) {
                        $type    = CheckinType::OUT;
                        $checkin = Checkin::create([
                            'user_id'         => $user->id,
                            'date'            => now()->toDateString(),
                            'time'            => date('H:i:s'),
                            'type'            => $type,
                            'latecomment'     => 'AUTO_RADIUSOUT-1',
                            'checkout_reason' => 'OUT OF RADIUS',
                            'is_auto_update'  => 1,
                        ]);
                        // $user->tokens()->delete();
                        $this->info("User {$user->id} logged out successfully and access tokens revoked.");
                    }
                }
                $this->info("{$user->id} Out Side Record Condition.");
            }
            // if((Carbon::now()->diffInMinutes($lastUpdatedTime) > 30)) {
            //     logger()->info($user->id ."|CHECKOUT");
            //     $type = CheckinType::IN;

            //     if ($record) {

            //         if ($record->type == CheckinType::IN->value) {

            //             $type = CheckinType::OUT;
            //             $checkin = Checkin::create([
            //                 'user_id' => $user->id,
            //                 'date' => now()->toDateString(),
            //                 'time' => date('H:i:s'),
            //                 'type' => $type,
            //                 'latecomment' => 'AUTO_TIMEOUT',
            //             ]);
            //             logger()->info($checkin);
            //             // $user->tokens()->delete();
            //             $this->info("User {$user->id} logged out successfully and access tokens revoked.");
            //         }
            //     }
            //     $this->info("{$user->id} Out Side Record Condition.");
            // }
            //CHECKIN
            // if(($getdistance_float <= $radius) && (Carbon::now()->diffInMinutes($lastUpdatedTime) < 5)) {
            //     $type = CheckinType::OUT;
            //     // $record = Checkin::where([
            //     //     'date' => now()->toDateString(),
            //     //     'user_id' => $user->id
            //     // ])->orderByDesc('id')->limit(1)->first();
            //     logger()->info($user->id ."|CHECKIN");
            //     if ($record) {
            //         if ($record->type == CheckinType::OUT->value) {
            //             $type = CheckinType::IN;
            //             $checkin = Checkin::create([
            //                 'user_id' => $user->id,
            //                 'date' => now()->toDateString(),
            //                 'time' => date('H:i:s'),
            //                 'type' => $type
            //             ]);
            //             logger()->info($checkin);
            //             // $user->tokens()->delete();
            //             $this->info("User {$user->id} logged in successfully and access tokens revoked.");
            //         }
            //     }else{
            //         $type = CheckinType::IN;
            //             $checkin = Checkin::create([
            //                 'user_id' => $user->id,
            //                 'date' => now()->toDateString(),
            //                 'time' => date('H:i:s'),
            //                 'type' => $type
            //             ]);
            //             logger()->info($checkin);
            //             // $user->tokens()->delete();
            //             $this->info("User {$user->id} logged in successfully and access tokens revoked.");
            //     }
            //     $this->info("{$user->id} In Side Record Condition.");
            // }
            $this->info("{$user->id} Out Side getdistance Condition.");
        }
        $this->info('[' . now() . '] User fetch-user-location ended.');

    }

    public function distanceInMeters($lat1, $lon1, $lat2, $lon2)
    {
        $theta      = $lon1 - $lon2;
        $dist       = sin(deg2rad($lat1)) * sin(deg2rad($lat2)) + cos(deg2rad($lat1)) * cos(deg2rad($lat2)) * cos(deg2rad($theta));
        $dist       = acos($dist);
        $dist       = rad2deg($dist);
        $miles      = $dist * 60 * 1.1515;
        $kilometers = $miles * 1.609344;
        $meters     = $kilometers * 1000;

        return number_format((float) $meters, 2, '.', '');
    }
}
