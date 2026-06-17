<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Artisan;
use Symfony\Component\Console\Helper\ProgressBar;

class DefaultScripts extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'run:default-scripts';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'This Command is used for Run for some default operation when we init any new server';

    protected $commands = [
        'migrate:fresh' => 'Running fresh migrations...',
        'db:seed --class=DatabaseSeeder' => 'Seeding database...',
        'module:seed Leave' => 'Seeding module: Leave...',
        'module:seed Attendance' => 'Seeding module: Attendance...',
        'module:seed Announcement' => 'Seeding module: Announcement...',
        'module:seed Asset' => 'Seeding module: Asset...',
        'module:seed Document' => 'Seeding module: Document...',
    ];

    // public function __construct()
    // {
    //     parent::__construct();
    // }

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $totalCommands = count($this->commands);
        $currentCommandIndex = 1;

        $this->output->progressStart($totalCommands);

        foreach ($this->commands as $command => $description) {
            $this->info($description);
            Artisan::call($command);
            // Artisan::call($command, ['--force' => true]);

            $progress = ($currentCommandIndex / $totalCommands) * 100;
            $this->line("Progress: " . number_format($progress, 2) . "%");

            $this->output->progressAdvance();
            $currentCommandIndex++;
        }

        $this->output->progressFinish();

        $this->info('All custom commands have been executed.');
    }
}
