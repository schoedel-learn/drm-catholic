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
        Schema::create('custom_fields', function (Blueprint $table) {
            $table->id();
            $table->foreignUuid('jurisdiction_id')->constrained('jurisdictions')->onDelete('cascade'); // Scope to Tenant (Diocese)
            $table->string('entity_type')->default('contact'); // contact, organization
            $table->string('label'); // e.g. "Ordination Date"
            $table->string('key'); // e.g. "ordination_date" (slugified version)
            $table->string('type'); // e.g. "text", "date", "number", "select"
            $table->json('options')->nullable(); // For "select" type options
            $table->boolean('required')->default(false);
            $table->integer('order')->default(0); // For display order
            $table->timestamps();

            $table->unique(['jurisdiction_id', 'key', 'entity_type']); // Keys must be unique within a tenant AND entity type
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('custom_fields');
    }
};
