<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\User;
use Modules\Attendance\Entities\Checkin;
use Modules\Attendance\Enums\CheckinType;
use Modules\Attendance\Entities\CheckinsLogs;
use Illuminate\Support\Facades\Log;

use Exception;

class ManualCheckInCheckOut extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'run:manualCheckInCheckOut';

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
        // $usersMissingCheckOut = User::whereHas('checkins', function ($query) {
        //     $query->where('date', now()->subDays(1)->toDateString())
        //           ->where('type', CheckinType::IN);
        // })
        // ->whereDoesntHave('checkins', function ($query) {
        //     $query->where('date', now()->subDays(1)->toDateString())
        //           ->where('type', CheckinType::OUT);
        // })
        // ->get();

        $this->info('[' . now() . '] manualCheckInCheckOut started.');
        $usersMissingCheckOut = User::whereDoesntHave('roles', function ($query) {
            return  $query->whereIn('name', [User::ROLE_ADMIN,User::ROLE_SUPER_ADMIN]);
        })->where('status',User::STATUS_ACTIVE)->get();

        Log::info('manualCheckInCheckOut', ["usersMissingCheckOut" => $usersMissingCheckOut]);
       
        foreach ($usersMissingCheckOut as $user) {
            $this->info('[' . now() . '] user: ' . json_encode($user));

            try {
                $checkin = $user->checkins()
                ->where('date', now()->subDays(1)->toDateString())
                ->where('face_attendance',0)
                ->orderByDesc('id')
                ->first();
                if ($checkin !== null && $checkin->type == CheckinType::IN->value) {
                    $checkout = $user->checkins()->create([
                        'date' => now()->subDays(1)->toDateString(),
                        'time' => now()->subMinutes(5)->toTimeString(),
                        'type' => CheckinType::OUT,
                        'is_auto_update' => 1,
                    ]);
            
                    $this->info("User {$user->id} manually checked out successfully.");
            
                    $type = CheckinType::IN;
                    $checkin = $user->checkins()->create([
                        'date' => now()->toDateString(),
                        'time' => now()->toTimeString(),
                        'type' => $type,
                        'is_auto_update' => 1,
                    ]);
            
                    CheckinsLogs::create([
                        'user_id' => $user->id,
                        'date' => now()->toDateString(),
                        'comment' => "Cronjob: Auto Checkout by {$checkout->date} {$checkout->time} and Again Checkin by {$checkin->date} {$checkin->time}"
                    ]);
            
                    $this->info("User {$user->id} manually checked in successfully with a new date.");
                }
            } catch (Exception $e) {
                throw new Exception($e->getMessage());
            }   
        }
        $this->info('[' . now() . '] manualCheckInCheckOut ended.');
    }
}
