<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
    {
        DB::statement("ALTER TABLE attendances MODIFY COLUMN status ENUM('present','absent','leave','holiday','weekend','late','sickleave','earlyout') COLLATE utf8mb4_unicode_ci NOT NULL");
    }

    public function down()
    {
        DB::statement("ALTER TABLE attendances MODIFY COLUMN status ENUM('present','absent','leave','holiday','weekend','late','sickleave') COLLATE utf8mb4_unicode_ci NOT NULL");
    }
};
