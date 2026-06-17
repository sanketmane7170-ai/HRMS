<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('recruitment_applications', function (Blueprint $table) {
            if (!Schema::hasColumn('recruitment_applications', 'candidate_email')) {
                $table->string('candidate_email')->nullable()->after('user_id');
                $table->string('candidate_name')->nullable()->after('candidate_email');
                $table->string('candidate_phone')->nullable()->after('candidate_name');
                $table->string('linkedin_url')->nullable()->after('candidate_phone');
                $table->decimal('score', 5, 2)->nullable()->after('linkedin_url')
                    ->comment('Application score out of 100');

                $table->index('candidate_email');
            }
        });
    }

    public function down(): void
    {
        Schema::table('recruitment_applications', function (Blueprint $table) {

            // Drop index only if exists
            $indexes = DB::select("
                SHOW INDEX FROM recruitment_applications 
                WHERE Key_name = 'recruitment_applications_candidate_email_index'
            ");

            if (!empty($indexes)) {
                $table->dropIndex('recruitment_applications_candidate_email_index');
            }

            // Drop columns only if they exist
            $columns = [
                'candidate_email',
                'candidate_name',
                'candidate_phone',
                'linkedin_url',
                'score',
            ];

            foreach ($columns as $column) {
                if (Schema::hasColumn('recruitment_applications', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
};
