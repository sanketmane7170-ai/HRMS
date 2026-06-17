<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasColumn('user_work_details', 'mol_location')) {

            // Get column type (assuming it was string)
            DB::statement("
                ALTER TABLE user_work_details 
                CHANGE mol_location mol_number VARCHAR(255) NULL
            ");

        } elseif (!Schema::hasColumn('user_work_details', 'mol_number')) {

            Schema::table('user_work_details', function (Blueprint $table) {
                $table->string('mol_number')->nullable()->after('accommodation_location');
            });

        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('user_work_details', 'mol_number')) {

            DB::statement("
                ALTER TABLE user_work_details 
                CHANGE mol_number mol_location VARCHAR(255) NULL
            ");

        }
    }
};