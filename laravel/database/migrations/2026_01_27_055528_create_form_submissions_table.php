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
        Schema::create('form_submissions', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('form_id');
            $table->uuid('entity_id')->nullable(); // Linked Contact or Organization
            $table->string('submitter_email')->nullable(); // For self-identification
            $table->string('submitter_name')->nullable();
            $table->jsonb('data'); // Submitted field values
            $table->timestamp('submitted_at');
            $table->string('ip_address', 45)->nullable();
            $table->timestamps();

            $table->foreign('form_id')->references('id')->on('forms')->onDelete('cascade');
            $table->index('form_id');
            $table->index('submitter_email');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('form_submissions');
    }
};
