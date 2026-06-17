<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('e_m_p_air_tickets', function (Blueprint $table) {
            $table->decimal('total_amount', 10, 2)->nullable()->after('quantity'); // adjust position
            $table->text('details')->nullable()->after('total_amount');
            $table->enum('status', ['Approved', 'Rejected', 'Pending'])->default('Pending')->after('details');
        });
    }

    public function down(): void
    {
        Schema::table('e_m_p_air_tickets', function (Blueprint $table) {
            $table->dropColumn(['total_amount', 'details', 'status']);
        });
    }
};
