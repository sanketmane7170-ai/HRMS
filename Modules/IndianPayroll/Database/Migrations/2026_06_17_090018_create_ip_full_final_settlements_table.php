<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        if (! Schema::hasTable('ip_full_final_settlements')) {
            Schema::create('ip_full_final_settlements', function (Blueprint $table) {
                $table->id();
                $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
                $table->date('last_working_day');

                $table->decimal('pending_salary_amount', 12, 2)->default(0);
                $table->decimal('gratuity_amount', 12, 2)->default(0);
                $table->decimal('gratuity_taxable_amount', 12, 2)->default(0);
                $table->decimal('leave_encashment_amount', 12, 2)->default(0);
                $table->decimal('leave_encashment_taxable_amount', 12, 2)->default(0);
                $table->decimal('notice_pay_recovery', 12, 2)->default(0); // negative deduction if employee leaves without serving full notice
                $table->decimal('other_deductions', 12, 2)->default(0);
                $table->decimal('final_tds', 12, 2)->default(0);
                $table->decimal('net_payable', 12, 2)->default(0);

                $table->enum('status', ['draft', 'approved', 'paid'])->default('draft');
                $table->foreignId('approved_by')->nullable()->constrained('users')->onDelete('set null');
                $table->timestamp('approved_at')->nullable();
                $table->timestamps();

                $table->unique('user_id', 'ip_ffs_user_unique'); // one settlement per employee exit
            });
        }
    }

    public function down()
    {
        Schema::dropIfExists('ip_full_final_settlements');
    }
};
