<?php
namespace App\Console\Commands;

use App\Models\User;
use App\Observers\ShiftObserver;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class RunShiftObserver extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'command:shift_observer';

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
        $this->info('[' . now() . '] command:shift_observer started.');

        $users = User::whereDoesntHave('roles', function ($query) {
            return  $query->whereIn('name', [User::ROLE_ADMIN,User::ROLE_SUPER_ADMIN]);
        })->where('status',User::STATUS_ACTIVE)->get();
        $shiftObserver = new ShiftObserver();
        // \DB::enableQueryLog();
        // $users = User::whereDoesntHave('roles', function ($query) {
        //     $query->whereIn('name', [User::ROLE_ADMIN,User::ROLE_SUPER_ADMIN]);
        // })
        //     ->where('status', User::STATUS_ACTIVE)
        //     ->whereHas('assigned_shifts', function ($query) {
        //         $query->whereDate('assigned_for_date', date("Y-m-d"));
        //     })
        //     ->get();
        // Log::info('command:shift_observer', ["getQueryLog" => \DB::getQueryLog()]);
        Log::info('command:shift_observer', ["users" => $users]);

        foreach ($users as $user) {
            $this->info('[' . now() . '] user: ' . json_encode($user));

            $shiftObserver->updated($user);
        }
        $this->info('[' . now() . '] command:shift_observer ended.');

    }
}
