<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('air_ticket_details', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id')->nullable()->index();
            $table->string('title')->nullable();
            $table->decimal('percentage', 10, 2)->nullable();
            $table->integer('qty')->default(1);
            // add other fields your app needs, eg:
            // $table->string('pnr')->nullable();
            $table->timestamps();

            // optional foreign key to users table:
            // $table->foreign('user_id')->references('id')->on('users')->onDelete('set null');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('air_ticket_details');
    }
};
