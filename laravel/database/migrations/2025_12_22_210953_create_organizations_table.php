<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('organizations', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('jurisdiction_id');
            $table->string('name');

            $table->uuid('leader_id')->nullable(); // Pastor, Director, Principal
            // Note: We don't constrain leader_id yet to avoid circular dependency issues during seed/creation, 
            // or we can add constraint in a separate migration.

            $table->jsonb('address')->nullable();
            $table->string('phone')->nullable();
            $table->string('email')->nullable();
            $table->string('website')->nullable();

            $table->jsonb('mass_schedule')->nullable();
            $table->jsonb('custom_data')->nullable(); // For dynamic fields

            // Google Places Integration
            $table->string('google_place_id')->nullable()->unique();
            $table->string('google_formatted_address')->nullable();
            $table->string('google_maps_url')->nullable();
            $table->decimal('google_lat', 10, 7)->nullable();
            $table->decimal('google_lng', 10, 7)->nullable();

            $table->timestamps();

            $table->foreign('jurisdiction_id')->references('id')->on('jurisdictions')->onDelete('cascade');
            $table->index(['jurisdiction_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('organizations');
    }
};
