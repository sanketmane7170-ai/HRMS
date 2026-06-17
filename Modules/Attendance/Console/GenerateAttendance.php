<?php

namespace Modules\Attendance\Console;

use App\Models\User;
use Illuminate\Console\Command;
use Modules\Attendance\Traits\AttendanceHelper;
use Symfony\Component\Console\Input\InputOption;

class GenerateAttendance extends Command
{
    use AttendanceHelper;
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $name = 'attendance:generate';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Generate Attendance of entered date , if date is not specified that current date attendance is generated';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Get the console command arguments.
     *
     * @return array
     */
    protected function getArguments()
    {
        return [
            [
                'date',
                InputOption::VALUE_OPTIONAL,
                "Specific date for which the attendance need to generate"
            ]
        ];
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $date = $this->argument('date') ? now()->parse($this->argument('date')[0])->toDateString() : now()->toDateString();
        $query = User::whereHas('roles', function ($query) {
            return $query->whereNotIn('name', [
        User::ROLE_ADMIN,
        User::ROLE_SUPER_ADMIN,
    ]);
        })->where('status',User::STATUS_ACTIVE);
        $progressBar = $this->output->createProgressBar($query->count());
        $progressBar->start();
        $query->chunk(100, function ($employees) use ($date, $progressBar) {
            $isholiday = self::isTodayHoliday($date);
            $isWeekend = self::isWeekend($date);
            foreach ($employees as $employee) {
                $this->logEmployeeAttendance($employee, $date, $isholiday, $isWeekend);
                $progressBar->advance();
            }
        });
        $progressBar->finish();
    }
}
