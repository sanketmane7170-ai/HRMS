<?php

namespace App\Console\Commands;

use App\Notifications\User\ProbationEndListNotification;
use App\Notifications\User\ProbationEndTodayNotification;
use Illuminate\Console\Command;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Log;


class ProbationEnding extends Command implements ShouldQueue
{

    private $daysBeforeNotification = 40;
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'probation:end-notification';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Send notification to hr and manager for probation ending in next 40 days';

    /**
     * Execute the console command.
     */
    public function handle()
    {

        $this->info('[' . now() . '] probation:end-notification started.');


        $hrs = getRoleUsers(['hr']);
        Log::info('probation:end-notification', ["hrs" => $hrs]);


        $query = getProbationEndQuery($this->daysBeforeNotification);
        $query->select('id', 'employee_id', 'department_id', 'name', 'email')->with([
            'department' => ['manager']
        ]);
        $data = $query->get();
        Log::info('probation:end-notification', ["data" => $data]);
        
        $userProbationEndingToday = $data->filter(function ($user) {
            return $user->workDetail->probation_end_date->format('Y-m-d') == now()->toDateString();
        });
        Log::info('probation:end-notification', ["userProbationEndingToday" => $userProbationEndingToday]);

        // send email to hr for user completing probation today
        foreach ($userProbationEndingToday as $user) {
            $this->info('[' . now() . '] user: ' . json_encode($user));

            Notification::send($hrs, new ProbationEndTodayNotification($user));
        }

        //// Map records to get desired field only
        $records = $data->map(function ($user) {
            return [
                'name' => $user->name,
                'employee_id' => $user->employee_id,
                'department' => $user->department?->name ?? 'NA',
                'manager' => $user->department->manager->name,
                'probation_end_date' => $user->workDetail->probation_end_date->format(config('project.date_format'))
            ];
        });

        if ($data->count() > 0) {
            Notification::send($hrs, new ProbationEndListNotification(($records)));
        }
        $this->info('[' . now() . '] probation:end-notification ended.');

    }
}
