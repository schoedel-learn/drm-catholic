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
        Schema::create('contacts', function (Blueprint $table) {
            $table->uuid('id')->primary();

            $table->string('first_name');
            $table->string('last_name');
            $table->string('title')->nullable();
            $table->string('role');

            // Intentionally NOT foreign-keyed: contacts may reference missing dioceses/parishes.
            $table->uuid('diocese_id')->nullable();
            $table->uuid('parish_id')->nullable();

            $table->string('email')->nullable();
            $table->string('phone')->nullable();
            $table->text('notes')->nullable();

            $table->timestamps();

            $table->index(['diocese_id']);
            $table->index(['parish_id']);
            $table->index(['role']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('contacts');
    }
};
