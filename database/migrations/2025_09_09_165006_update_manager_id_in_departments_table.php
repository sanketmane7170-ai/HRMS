<?php
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // Check if foreign key exists and drop it
        $fk = DB::select("
            SELECT CONSTRAINT_NAME 
            FROM information_schema.KEY_COLUMN_USAGE 
            WHERE TABLE_NAME = 'departments' 
              AND CONSTRAINT_SCHEMA = DATABASE()
              AND COLUMN_NAME = 'manager_id'
              AND REFERENCED_TABLE_NAME IS NOT NULL
        ");

        if (!empty($fk)) {
            $constraint = $fk[0]->CONSTRAINT_NAME;
            DB::statement("ALTER TABLE departments DROP FOREIGN KEY `$constraint`");
        }

        // Drop column if it exists
        if (Schema::hasColumn('departments', 'manager_id')) {
            Schema::table('departments', function (Blueprint $table) {
                $table->dropColumn('manager_id');
            });
        }

        // Recreate as string
        Schema::table('departments', function (Blueprint $table) {
            $table->string('manager_id')->nullable()->after('slug');
        });
    }

    public function down(): void
    {
        // Remove string manager_id
        if (Schema::hasColumn('departments', 'manager_id')) {
            Schema::table('departments', function (Blueprint $table) {
                $table->dropColumn('manager_id');
            });
        }

        // Restore bigint + FK
        Schema::table('departments', function (Blueprint $table) {
            $table->bigInteger('manager_id')->unsigned()->nullable()->after('slug');
            $table->foreign('manager_id')
                ->references('id')
                ->on('users')
                ->onDelete('set null');
        });
    }
};
