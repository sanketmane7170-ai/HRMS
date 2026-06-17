<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use Modules\Asset\Entities\Asset;
use Modules\Asset\Enums\AssetStatus;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('assets', function (Blueprint $table) {
            $table->id();
            $table->string('unique_id')->unique();
            $table->string('model');
            $table->string('description')->nullable();
            $table->enum('status', [AssetStatus::Available->value, AssetStatus::Assigned->value, AssetStatus::Damaged->value])->default(AssetStatus::Available->value);
            $table->foreignId('asset_type_id')->constrained()->onDelete('restrict');
            $table->foreignId('asset_manufacturer_id')->constrained()->onDelete('restrict');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('assets');
    }
};
