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
        Schema::create('parishes', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('diocese_id');
            $table->string('name');

            $table->string('pastor')->nullable();
            $table->jsonb('address');
            $table->string('phone')->nullable();
            $table->string('email')->nullable();
            $table->string('website')->nullable();
            $table->jsonb('mass_schedule')->nullable();

            $table->timestamps();

            $table->foreign('diocese_id')->references('id')->on('jurisdictions');
            $table->index(['diocese_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('parishes');
    }
};
