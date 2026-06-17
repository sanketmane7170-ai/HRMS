<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\File;
use Nwidart\Modules\Facades\Module;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {

        // ✅ Create upload folders
        $path = public_path('uploads');
        if (!File::exists($path)) {
            File::makeDirectory($path, 0777, true, true);
        }
        File::deleteDirectory(public_path('uploads/temp'));
        File::makeDirectory(public_path('uploads/temp'), 0777, true, true);
        File::deleteDirectory(public_path('uploads/mpdf'));
        File::makeDirectory(public_path('uploads/mpdf'), 0777, true, true);

        // ✅ MANUAL seeders first (priority)
        $manualSeeders = [
            CountrySeeder::class,
            RoleSeeder::class,
            PermissionSeeder::class,
            SettingSeeder::class,
            DepartmentSeeder::class,
            DefaultUserSeeder::class,
            UserSeeder::class,
            UserImportPermissionSeeder::class,
            LocationSettingSeeder::class,
            EmployeerSettingSeeder::class,
            PayrollPermissionSeeder::class,
            ShiftPermissionSeeder::class,
            RosterPermissionSeeder::class,
            UserSalaryEndOfServicePermissionSeeder::class,
            FileManagerPermissionSeeder::class,
            EditUpdateLeavePermission::class,
            LeaveProbationSettingSeeder::class,
            DashboardPermission::class,
            UpdateLeaveBalancesTableSeeder::class,
            IncrementsSeeder::class,
            IssuedDocumentsSeeder::class,
            ReportsSeeder::class,
            DepartureReasonSeeder::class,
        ];

        foreach ($manualSeeders as $seeder) {
            // if (class_exists($seeder)) {
            //     $this->call($seeder);
            // }
            try {
                if (class_exists($seeder)) {
                    $this->call($seeder);
                }
                $this->command->info("$seeder — ✅ DONE");
            } catch (\Throwable $e) {
                $this->command->error("$seeder — ❌ Failed: " . $e->getMessage());
                continue; // move to next seeder
            }
        }

        // $seedersPath = database_path('seeders');

        // foreach (glob($seedersPath . '/*Seeder.php') as $file) {

        //     $class = pathinfo($file, PATHINFO_FILENAME);
        //     $classWithNamespace = "Database\\Seeders\\$class";

        //     if ($class !== 'DatabaseSeeder' && class_exists($classWithNamespace)) {
        //         try {
        //             $this->call($classWithNamespace);
        //             $this->command->info("$class ✅ Completed");
        //         } catch (\Throwable $e) {
        //             $this->command->error("$class ❌ Failed: " . $e->getMessage());
        //             continue;
        //         }
        //     }
        // }
        // ✅ AUTO LOAD ALL SEEDERS IN `database/seeders` (even if file name not ending with Seeder.php)
        $seedersPath = database_path('seeders');

        foreach (glob($seedersPath . '/*.php') as $file) {
            $class = pathinfo($file, PATHINFO_FILENAME);
            $classWithNamespace = "Database\\Seeders\\$class";

            // Skip self and already included manual seeders
            if ($class === 'DatabaseSeeder' || in_array($classWithNamespace, $manualSeeders)) {
                continue;
            }

            try {
                if (class_exists($classWithNamespace)) {
                    $this->call($classWithNamespace);
                    $this->command->info("$classWithNamespace — ✅ DONE");
                } else {
                    $this->command->warn("$classWithNamespace — ⚠️ Class not found (check namespace or filename).");
                }
            } catch (\Throwable $e) {
                $this->command->error("$classWithNamespace — ❌ Failed: " . $e->getMessage());
                continue;
            }
        }



        // ✅ AUTO LOAD SEEDERS IN `database/seeders` (except DatabaseSeeder)
        foreach (glob(database_path('seeders/*Seeder.php')) as $file) {
            $class = pathinfo($file, PATHINFO_FILENAME);

            if ($class !== 'DatabaseSeeder' && class_exists($class) && !in_array($class, $manualSeeders)) {
                // $this->call($class);
                try {
                    if (class_exists($class)) {
                        $this->call($class);
                    }
                    $this->command->info("$class — ✅ DONE");
                } catch (\Throwable $e) {
                    $this->command->error("$class — ❌ Failed: " . $e->getMessage());
                    continue; // move to next seeder
                }
            }
        }

        // ✅ AUTO LOAD MODULE SEEDERS
        foreach (Module::all() as $module) {
            $seederPath = module_path($module->getName(), 'Database/Seeders');

            if (!is_dir($seederPath))
                continue;

            foreach (glob($seederPath . '/*Seeder.php') as $file) {
                $class = pathinfo($file, PATHINFO_FILENAME);
                $namespace = "\\Modules\\{$module->getName()}\\Database\\Seeders\\{$class}";

                // if (class_exists($namespace)) {
                //     $this->call($namespace);
                // }
                try {
                    if (class_exists($namespace)) {
                        $this->call($namespace);
                    }
                    $this->command->info("$namespace — ✅ DONE");
                } catch (\Throwable $e) {
                    $this->command->error("$namespace — ❌ Failed: " . $e->getMessage());
                    continue; // move to next seeder
                }
            }
        }

        // Artisan::call('db:add-created-updated-by');
        // ✅ Ensure all tables have created_by / updated_by columns after seeding
        try {
            $this->command->info('🧩 Checking all tables for created_by / updated_by columns...');
            Artisan::call('db:add-created-updated-by');

            // Show the command output in the seeder console
            $output = Artisan::output();
            $this->command->info($output ?: '✅ No changes needed.');

            $this->command->info('✅ All tables verified successfully.');
        } catch (\Throwable $e) {
            $this->command->error('❌ Failed to ensure created_by/updated_by columns: ' . $e->getMessage());
        }
    }
}
