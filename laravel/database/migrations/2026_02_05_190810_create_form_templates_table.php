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
        Schema::create('form_templates', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('jurisdiction_id');
            $table->string('name');
            $table->text('description')->nullable();
            $table->enum('entity_type', ['contact', 'organization'])->default('contact');
            $table->boolean('is_default')->default(false);
            $table->timestamps();

            $table->foreign('jurisdiction_id')->references('id')->on('jurisdictions')->onDelete('cascade');
            $table->index('jurisdiction_id');
        });

        // Add template_id reference to forms table
        Schema::table('forms', function (Blueprint $table) {
            $table->uuid('template_id')->nullable()->after('jurisdiction_id');
            $table->foreign('template_id')->references('id')->on('form_templates')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('forms', function (Blueprint $table) {
            $table->dropForeign(['template_id']);
            $table->dropColumn('template_id');
        });

        Schema::dropIfExists('form_templates');
    }
};
