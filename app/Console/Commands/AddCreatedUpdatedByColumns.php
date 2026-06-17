<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;

class AddCreatedUpdatedByColumns extends Command
{
    protected $signature = 'db:add-created-updated-by';
    protected $description = 'Ensure all tables have created_by and updated_by columns';

    public function handle()
    {
        $dbName = DB::getDatabaseName();
        $tables = DB::select('SHOW TABLES');
        $key = 'Tables_in_' . $dbName;

        $skippedTables = [
            'migrations',
            'failed_jobs',
            'personal_access_tokens',
            'password_resets',
            'model_has_permissions',
            'model_has_roles',
            'role_has_permissions',
            'password_reset_tokens',
        ];

        foreach ($tables as $table) {
            $tableName = $table->$key;

            if (in_array($tableName, $skippedTables)) {
                continue;
            }

            $addColumns = [];

            if (!Schema::hasColumn($tableName, 'created_by')) {
                $addColumns[] = 'created_by';
            }

            if (!Schema::hasColumn($tableName, 'updated_by')) {
                $addColumns[] = 'updated_by';
            }

            if (!empty($addColumns)) {
                Schema::table($tableName, function (Blueprint $table) use ($addColumns) {
                    if (in_array('created_by', $addColumns)) {
                        $table->unsignedBigInteger('created_by')->nullable()->after('id');
                    }
                    if (in_array('updated_by', $addColumns)) {
                        $table->unsignedBigInteger('updated_by')->nullable()->after('created_by');
                    }
                });

                $this->info("✅ Added columns in {$tableName}: " . implode(', ', $addColumns));
            }
        }

        $this->info('🎯 Check complete. All tables now have created_by and updated_by columns.');
    }
}
