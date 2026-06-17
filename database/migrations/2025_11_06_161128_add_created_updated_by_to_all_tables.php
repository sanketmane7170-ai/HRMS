<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
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
            // $tableName = $table->$key;
            $tableName = array_values((array)$table)[0];

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
                Schema::table($tableName, function (Blueprint $table) use ($tableName, $addColumns) {
                    if (in_array('created_by', $addColumns)) {
                        $table->unsignedBigInteger('created_by')->nullable()->after('id');
                    }
                    if (in_array('updated_by', $addColumns)) {
                        $table->unsignedBigInteger('updated_by')->nullable()->after('created_by');
                    }
                });
                echo "✅ Added columns in {$tableName}: " . implode(', ', $addColumns) . "\n";
            }
        }
    }

    public function down(): void
    {
        $dbName = DB::getDatabaseName();
        $tables = DB::select('SHOW TABLES');
        $key = 'Tables_in_' . $dbName;

        foreach ($tables as $table) {
            // $tableName = $table->$key;
            $tableName = array_values((array)$table)[0];

            if (Schema::hasColumn($tableName, 'created_by') || Schema::hasColumn($tableName, 'updated_by')) {
                Schema::table($tableName, function (Blueprint $table) use ($tableName) {
                    if (Schema::hasColumn($tableName, 'created_by')) {
                        $table->dropColumn('created_by');
                    }
                    if (Schema::hasColumn($tableName, 'updated_by')) {
                        $table->dropColumn('updated_by');
                    }
                });
                echo "🧹 Dropped columns in {$tableName}\n";
            }
        }
    }
};
