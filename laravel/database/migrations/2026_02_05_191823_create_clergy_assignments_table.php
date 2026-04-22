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
        Schema::create('clergy_assignments', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('clergy_id');
            $table->uuid('organization_id');

            // Assignment details
            $table->string('role');              // Pastor, Parochial Vicar, Administrator, Deacon
            $table->string('canonical_role')->nullable(); // Parochus, Vicarius Paroecialis, Administrator

            // Dates
            $table->date('start_date');
            $table->date('end_date')->nullable(); // Null = current assignment
            $table->date('effective_date')->nullable(); // Canonical effective date

            // Type
            $table->boolean('is_primary')->default(false);  // Primary assignment
            $table->boolean('is_residence')->default(false); // Lives at this location

            // Status
            $table->enum('status', ['active', 'pending', 'completed', 'revoked'])->default('active');

            // Documents
            $table->string('decree_number')->nullable();
            $table->text('notes')->nullable();

            $table->timestamps();

            $table->foreign('clergy_id')->references('id')->on('clergy')->onDelete('cascade');
            $table->foreign('organization_id')->references('id')->on('organizations')->onDelete('cascade');

            $table->index(['clergy_id', 'status']);
            $table->index(['organization_id', 'status']);
            $table->index(['end_date', 'status']); // For finding current assignments
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('clergy_assignments');
    }
};
