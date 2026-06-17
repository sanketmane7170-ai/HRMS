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
        Schema::table('interviews', function (Blueprint $table) {
            if (! Schema::hasColumn('interviews', 'round')) { // Check if the column doesn't already exist
                $table->integer('round')->default(1)->after('application_id');
            }
            if (! Schema::hasColumn('interviews', 'overall_rating')) { // Check if the column doesn't already exist
                $table->integer('overall_rating')->nullable()->after('completed_at');
            }
            if (! Schema::hasColumn('interviews', 'score')) { // Check if the column doesn't already exist
                $table->decimal('score', 5, 2)->nullable()->after('overall_rating');
            }
            if (! Schema::hasIndex('interviews', ['application_id', 'round'])) { // Check if the index doesn't already exist
                                                                                    // Add index for efficient querying of rounds
                $table->index(['application_id', 'round']);
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('interviews', function (Blueprint $table) {
            $table->dropIndex(['application_id', 'round']);
            $table->dropColumn(['round', 'overall_rating', 'score']);
        });
    }
};
