<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('calendar_events', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->text('description')->nullable();
            $table->dateTime('start_at');
            $table->dateTime('end_at')->nullable();
            $table->boolean('all_day')->default(true);
            $table->string('location')->nullable();
            $table->string('category')->default('parish'); // sacramental, formation, parish, diocesan, staff
            $table->string('color', 7)->nullable();        // hex override e.g. #4F46E5
            $table->foreignId('created_by')->constrained('users')->cascadeOnDelete();
            $table->timestamps();

            $table->index(['start_at', 'end_at']);
            $table->index('category');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('calendar_events');
    }
};
