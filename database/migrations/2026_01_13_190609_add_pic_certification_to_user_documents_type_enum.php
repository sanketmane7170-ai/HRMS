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
        Schema::table('user_documents', function (Blueprint $table) {
            DB::statement("
                ALTER TABLE user_documents 
                MODIFY COLUMN `type` 
                ENUM('passport','visa','labor_card_no','emirates_id','eid_front','eid_back','insurance','education','license','contract','cv','health_card','offer_letter','etisalat_id','du_id','other','pic_certification')
            ");
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('user_documents', function (Blueprint $table) {
            //
        });
    }
};
