<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Spatie\Permission\Models\Permission;

class MakePermisson extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'permission-make';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create Permission in the data base';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $module = $this->ask("Please Enter Module Name?");
        $modules = [$module];
        $commonPermission = ["Manage", "Create", "Edit", 'Delete'];
        foreach ($modules as $module) {
            foreach ($commonPermission as $cp) {
                $name = "$cp $module";
                $permission = Permission::create([
                    "name" => $name
                ]);
            }
        }

        $this->info("Permission create for the $module");
    }
}
