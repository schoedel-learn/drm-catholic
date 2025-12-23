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
        Schema::table('parishes', function (Blueprint $table) {
            $table->string('google_place_id')->nullable();
            $table->string('google_formatted_address')->nullable();
            $table->string('google_maps_url')->nullable();
            $table->decimal('google_lat', 10, 7)->nullable();
            $table->decimal('google_lng', 10, 7)->nullable();

            $table->unique('google_place_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('parishes', function (Blueprint $table) {
            $table->dropUnique(['google_place_id']);
            $table->dropColumn([
                'google_place_id',
                'google_formatted_address',
                'google_maps_url',
                'google_lat',
                'google_lng',
            ]);
        });
    }
};
