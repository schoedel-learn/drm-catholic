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
        Schema::create('forms', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('jurisdiction_id');
            $table->string('name');
            $table->text('description')->nullable();
            $table->enum('entity_type', ['contact', 'organization'])->default('contact');
            $table->jsonb('fields'); // Array of custom_field keys to include
            $table->string('token', 64)->unique(); // Secure public URL token
            $table->timestamp('expires_at')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->foreign('jurisdiction_id')->references('id')->on('jurisdictions')->onDelete('cascade');
            $table->index(['token', 'is_active']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('forms');
    }
};
