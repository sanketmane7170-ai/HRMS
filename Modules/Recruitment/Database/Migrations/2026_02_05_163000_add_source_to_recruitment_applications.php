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
        Schema::table('recruitment_applications', function (Blueprint $blueprint) {
            if (!Schema::hasColumn('recruitment_applications', 'source')) {
                $blueprint->string('source')->nullable()->after('candidate_email')->comment('Application source: referral, linkedin, etc.');
            }
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('recruitment_applications', function (Blueprint $blueprint) {
            if (Schema::hasColumn('recruitment_applications', 'source')) {
                $blueprint->dropColumn('source');
            }
        });
    }
};
