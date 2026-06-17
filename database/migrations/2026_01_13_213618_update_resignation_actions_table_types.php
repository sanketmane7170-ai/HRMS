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
        if (Schema::hasTable('resignation_actions')) {
            if (Schema::hasColumn('resignation_actions', 'action_type')) {
                \Illuminate\Support\Facades\DB::statement("ALTER TABLE resignation_actions MODIFY COLUMN action_type VARCHAR(255)");
            }
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('resignation_actions', function (Blueprint $table) {
            $table->enum('action_type', ['approve', 'reject', 'hold', 'comment', 'recommend'])->change();
        });
    }
};
