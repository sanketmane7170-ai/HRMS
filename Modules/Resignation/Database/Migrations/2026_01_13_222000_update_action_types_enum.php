<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        // Modifying ENUM is database specific and can be tricky. 
        // For MySQL, we use raw statement or change column.
        // Since we are in early dev, we can just use DB::statement for MySQL
        if (Schema::hasTable('resignation_actions')) {
            DB::statement("ALTER TABLE resignation_actions CHANGE COLUMN action_type action_type ENUM('approve', 'reject', 'hold', 'comment', 'recommend', 'waive', 'complete') NOT NULL");
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        // Reverting to original list
        DB::statement("ALTER TABLE resignation_actions CHANGE COLUMN action_type action_type ENUM('approve', 'reject', 'hold', 'comment', 'recommend') NOT NULL");
    }
};
