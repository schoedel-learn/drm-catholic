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
        Schema::table('contacts', function (Blueprint $table) {
            $table->uuid('entity_type_id')->nullable()->after('role');
            $table->foreign('entity_type_id')->references('id')->on('entity_types')->onDelete('set null');
            $table->index('entity_type_id');
        });

        Schema::table('organizations', function (Blueprint $table) {
            $table->uuid('entity_type_id')->nullable()->after('jurisdiction_id');
            $table->foreign('entity_type_id')->references('id')->on('entity_types')->onDelete('set null');
            $table->index('entity_type_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('contacts', function (Blueprint $table) {
            $table->dropForeign(['entity_type_id']);
            $table->dropColumn('entity_type_id');
        });

        Schema::table('organizations', function (Blueprint $table) {
            $table->dropForeign(['entity_type_id']);
            $table->dropColumn('entity_type_id');
        });
    }
};
