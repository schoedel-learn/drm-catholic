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
        Schema::create('jurisdictions', function (Blueprint $table) {
            $table->uuid('id')->primary();

            $table->string('name');
            $table->string('type'); // diocese|archdiocese|eparchy|archeparchy
            $table->string('province')->default('');
            $table->string('state');
            $table->string('city');
            $table->date('established')->nullable();

            $table->string('bishop')->nullable();
            $table->string('website')->nullable();
            $table->string('email')->nullable();
            $table->string('phone')->nullable();

            $table->jsonb('address')->nullable();

            $table->boolean('is_external')->default(false);
            $table->boolean('locked')->default(false);

            $table->timestamps();

            $table->index(['type', 'state']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('jurisdictions');
    }
};
