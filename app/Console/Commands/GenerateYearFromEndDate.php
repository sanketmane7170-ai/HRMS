<?php
namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use Modules\Leave\Entities\Leave;

class GenerateYearFromEndDate extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'generate:year';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'This command is used for generate year in leave_balance table like it is an hotfix';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('[' . now() . '] Year generation started.');
        $records = Leave::all();
        Log::info('generate:year', ["records" => $records]);

        foreach ($records as $record) {
            $this->info('[' . now() . '] record: ' . json_encode($record));

            $year = date('Y', strtotime($record->end_date));
            $record->update(['year' => $year]);
        }

        $this->info('Year generation completed successfully!');
    }
}
