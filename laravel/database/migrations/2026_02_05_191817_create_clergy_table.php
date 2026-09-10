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
        Schema::create('clergy', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('contact_id')->unique(); // One-to-one with contact

            // Status
            $table->enum('status', ['active', 'retired', 'leave', 'suspended', 'deceased'])->default('active');

            // Ordination info
            $table->date('ordination_date')->nullable();
            $table->enum('ordination_type', ['priest', 'deacon_permanent', 'deacon_transitional', 'bishop'])->nullable();
            $table->string('ordination_diocese')->nullable(); // Where ordained

            // Canonical status
            $table->string('incardination_status')->nullable(); // Incardinated, ExtraOrdinarian, etc.
            $table->string('incardination_diocese')->nullable();
            $table->date('incardination_date')->nullable();

            // Faculties and sacraments
            $table->jsonb('faculties')->nullable(); // {confession: true, marriage: true, ...}
            $table->jsonb('languages')->nullable(); // Languages for ministry

            // Additional info
            $table->text('bio')->nullable();
            $table->string('photo_url')->nullable();
            $table->date('birth_date')->nullable();

            $table->timestamps();

            $table->foreign('contact_id')->references('id')->on('contacts')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('clergy');
    }
};
