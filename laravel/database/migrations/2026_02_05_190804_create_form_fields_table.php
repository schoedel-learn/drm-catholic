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
        Schema::create('form_fields', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('form_id');

            // Field type and identity
            $table->string('type'); // text, textarea, select, checkbox, radio, date, file, email, phone, number
            $table->string('key'); // Unique identifier within form
            $table->string('label');
            $table->text('placeholder')->nullable();
            $table->text('help_text')->nullable();

            // Configuration
            $table->jsonb('options')->nullable(); // For select/radio: [{value, label}]
            $table->jsonb('validation')->nullable(); // {required, min, max, pattern, minLength, maxLength}
            $table->jsonb('conditional')->nullable(); // {field_key, operator, value} - show when condition met

            // CRM Integration
            $table->string('crm_mapping')->nullable(); // e.g., "contact.email", "contact.phone", "custom.field_key"

            // Ordering
            $table->integer('order')->default(0);

            $table->timestamps();

            $table->foreign('form_id')->references('id')->on('forms')->onDelete('cascade');
            $table->index(['form_id', 'order']);
            $table->unique(['form_id', 'key']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('form_fields');
    }
};
