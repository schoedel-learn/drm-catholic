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
        Schema::create('entity_types', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('jurisdiction_id');

            // Identity
            $table->string('name');            // "Clergy", "School", "Religious Sister"
            $table->string('slug');            // "clergy", "school", "religious_sister"

            // Base entity type - what this extends
            $table->enum('base_entity', ['contact', 'organization', 'standalone'])->default('contact');

            // Display
            $table->string('icon')->nullable();       // Lucide icon name
            $table->string('color')->nullable();      // Tailwind color class
            $table->text('description')->nullable();

            // Configuration
            $table->jsonb('default_fields')->nullable();  // Pre-configured custom fields for new entities
            $table->jsonb('required_fields')->nullable(); // Fields that must be filled

            // System protection
            $table->boolean('is_system')->default(false); // Protect built-in types from deletion
            $table->boolean('is_active')->default(true);

            $table->timestamps();

            $table->foreign('jurisdiction_id')->references('id')->on('jurisdictions')->onDelete('cascade');
            $table->unique(['jurisdiction_id', 'slug']);
            $table->index(['jurisdiction_id', 'base_entity']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('entity_types');
    }
};
