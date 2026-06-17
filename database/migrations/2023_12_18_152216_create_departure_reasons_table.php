<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use App\Models\DepartureReason;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('departure_reasons', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->timestamps();
        });
        
        $this->insertionOfdata();
    }

    /**
     * Insert Static Data.
     */

    private function insertionOfdata(){
        $reasons = ['End of contract','Resignation with notice','Resignation without notice','Termination'];
        
        foreach ($reasons as $reason) {
            DepartureReason::create(['name' => $reason]);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('departure_reasons');
    }
};
