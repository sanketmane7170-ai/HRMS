<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        \Illuminate\Support\Facades\DB::statement("ALTER TABLE user_documents MODIFY COLUMN type ENUM('passport', 'visa', 'labor_card_no', 'emirates_id', 'eid_front', 'eid_back', 'insurance', 'education', 'license', 'other')");
        
        Schema::table('user_documents', function (Blueprint $table) {
            $table->string('place_of_issue')->after('type')->nullable(true);
            $table->string('country_name')->after('place_of_issue')->nullable(true);
            $table->date('issue_date')->after('serial_number')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('user_documents', function (Blueprint $table) {
            $table->enum('type', ['passport', 'visa', 'eid_front', 'eid_back', 'insurance', 'education', 'license', 'other'])->change();
            $table->dropColumn('place_of_issue');
            $table->dropColumn('country_name');
            $table->dropColumn('issue_date');
        });
    }
};
