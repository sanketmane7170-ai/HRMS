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
        if (!Schema::hasTable('resignations')) {
            Schema::create('resignations', function (Blueprint $table) {
                $table->id();
                $table->foreignId('employee_id')->constrained('users')->onDelete('cascade');
                $table->foreignId('manager_id')->nullable()->constrained('users')->onDelete('set null'); // Reporting manager at the time of resignation
                $table->date('applied_date');
                $table->date('preferred_last_working_date')->nullable();
                $table->date('approved_last_working_date')->nullable();
                $table->integer('notice_period_days')->default(0);
                $table->text('reason'); // Resignation reason
                $table->text('comments')->nullable(); // Additional comments by employee
                $table->enum('status', [
                    'pending',          // Just applied, waiting for manager review
                    'under_review',     // Manager viewed/acknowledged
                    'approved',         // Approved by Admin/HR
                    'rejected',         // Rejected by Admin/HR
                    'on_hold',          // Put on hold
                    'withdrawn',        // Withdrawn by Employee
                    'completed'         // Exit process complete
                ])->default('pending');
                $table->timestamps();
            });
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('resignations');
    }
};
